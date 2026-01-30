<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onUnmounted, ref, watch } from 'vue';
import { type UserBalanceTransaction } from '@/composables/useBalance';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

type PaginationMeta = {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type TransactionsResponse = {
    data: UserBalanceTransaction[];
    meta: PaginationMeta;
    links: PaginationLink[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transaction history',
        href: '/finance/transactions/history',
    },
];

const transactions = ref<UserBalanceTransaction[]>([]);
const meta = ref<PaginationMeta | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);
const searchTerm = ref('');
const debouncedSearch = ref('');
const page = ref(1);
const sortDirection = ref<'asc' | 'desc'>('desc');
let debounceHandle: number | null = null;

const formatAmountValue = (amount: number): string => {
    return Math.abs(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatSignedAmount = (amount: number, type: string): string => {
    const normalizedType = type?.toLowerCase();
    const sign = normalizedType === 'credit' ? '+' : '-';
    return `${sign}${formatAmountValue(amount)}`;
};

const formatType = (type: string): string => {
    if (!type) {
        return 'Unknown';
    }

    return type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();
};

const formatDate = (value: string): string => {
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? value : date.toLocaleString();
};

const unwrapResponse = (
    payload: TransactionsResponse | { data: TransactionsResponse },
): TransactionsResponse => {
    if (payload && typeof payload === 'object' && 'data' in payload) {
        const inner = (payload as { data: TransactionsResponse }).data;
        if (inner && Array.isArray(inner.data)) {
            return inner;
        }
    }

    return payload as TransactionsResponse;
};

const fetchTransactions = async (): Promise<void> => {
    isLoading.value = true;
    error.value = null;

    try {
        const params = new URLSearchParams({
            sort: 'created_at',
            direction: sortDirection.value,
            page: String(page.value),
        });

        if (debouncedSearch.value) {
            params.set('search', debouncedSearch.value);
        }

        const response = await fetch(`/finance/transactions?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error(`Failed to fetch: ${response.status}`);
        }

        const payload = unwrapResponse(await response.json());
        transactions.value = payload.data ?? [];
        meta.value = payload.meta ?? null;
    } catch {
        error.value = 'Failed to load transactions';
        transactions.value = [];
        meta.value = null;
    } finally {
        isLoading.value = false;
    }
};

const goToPage = (nextPage: number): void => {
    if (!meta.value) {
        return;
    }

    if (nextPage < 1 || nextPage > meta.value.last_page) {
        return;
    }

    page.value = nextPage;
};

const toggleDateSort = (): void => {
    sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc';
    if (page.value !== 1) {
        page.value = 1;
    }
};

watch(searchTerm, (value) => {
    if (debounceHandle !== null) {
        window.clearTimeout(debounceHandle);
    }

    debounceHandle = window.setTimeout(() => {
        debouncedSearch.value = value.trim();
        if (page.value !== 1) {
            page.value = 1;
        }
    }, 300);
});

watch([debouncedSearch, page, sortDirection], () => {
    void fetchTransactions();
}, { immediate: true });

onUnmounted(() => {
    if (debounceHandle !== null) {
        window.clearTimeout(debounceHandle);
    }
});

const rangeLabel = computed(() => {
    if (!meta.value || meta.value.total === 0) {
        return 'Showing 0 results';
    }

    return `Showing ${meta.value.from ?? 0}-${meta.value.to ?? 0} of ${meta.value.total}`;
});

const canGoPrev = computed(() => (meta.value?.current_page ?? 1) > 1);
const canGoNext = computed(() => {
    if (!meta.value) {
        return false;
    }

    return meta.value.current_page < meta.value.last_page;
});

const dateSortLabel = computed(() =>
    sortDirection.value === 'desc' ? 'Desc' : 'Asc',
);
</script>

<template>
    <Head title="Transaction history" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">Transaction history</h1>
                    <p class="text-sm text-muted-foreground">
                        Search by description and review balance changes.
                    </p>
                </div>
                <div class="text-sm text-muted-foreground">
                    {{ rangeLabel }}
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <label class="text-sm font-medium" for="transaction-search">
                    Search description
                </label>
                <input
                    id="transaction-search"
                    v-model="searchTerm"
                    type="text"
                    autocomplete="off"
                    placeholder="Search transactions..."
                    class="w-full rounded-md border border-sidebar-border/70 bg-background px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                />
            </div>

            <div
                class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div v-if="isLoading" class="p-4 text-sm text-muted-foreground">
                    Loading transactions...
                </div>
                <div v-else-if="error" class="p-4 text-sm text-rose-500">
                    {{ error }}
                </div>
                <div v-else-if="transactions.length === 0" class="p-4 text-sm text-muted-foreground">
                    No transactions found.
                </div>
                <table v-else class="w-full text-sm">
                    <thead class="bg-muted/40 text-xs uppercase text-muted-foreground">
                        <tr class="text-left">
                            <th class="px-4 py-3 font-medium">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2"
                                    :aria-sort="sortDirection === 'desc' ? 'descending' : 'ascending'"
                                    @click="toggleDateSort"
                                >
                                    <span>Date</span>
                                    <span
                                        class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-semibold uppercase text-muted-foreground"
                                    >
                                        {{ dateSortLabel }}
                                    </span>
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 text-right font-medium">Amount</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Balance After
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70">
                        <tr v-for="transaction in transactions" :key="transaction.id">
                            <td class="px-4 py-3">
                                {{ formatDate(transaction.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                {{
                                    transaction.description ||
                                    'Balance transaction'
                                }}
                            </td>
                            <td class="px-4 py-3">
                                {{ formatType(transaction.type) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-semibold"
                                :class="
                                    transaction.type?.toLowerCase() === 'credit'
                                        ? 'text-emerald-600 dark:text-emerald-500'
                                        : 'text-rose-600 dark:text-rose-500'
                                "
                            >
                                {{
                                    formatSignedAmount(
                                        transaction.amount,
                                        transaction.type,
                                    )
                                }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ formatAmountValue(transaction.balance_after) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                <button
                    type="button"
                    class="rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!canGoPrev"
                    @click="goToPage((meta?.current_page ?? 1) - 1)"
                >
                    Previous
                </button>
                <span class="text-muted-foreground">
                    Page {{ meta?.current_page ?? 1 }} of {{ meta?.last_page ?? 1 }}
                </span>
                <button
                    type="button"
                    class="rounded-md border border-sidebar-border/70 px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!canGoNext"
                    @click="goToPage((meta?.current_page ?? 1) + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>
