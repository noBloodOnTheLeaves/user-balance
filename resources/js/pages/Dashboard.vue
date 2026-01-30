<script setup lang="ts">
import { computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useBalance } from '@/composables/useBalance';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const {
    balance,
    transactions,
    isLoading,
    error,
    lastUpdated,
    startPolling,
    stopPolling,
} = useBalance();

const formatAmountValue = (amount: number): string => {
    return Math.abs(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatAmount = (amount: number | null): string => {
    if (typeof amount !== 'number') {
        return '—';
    }

    return formatAmountValue(amount);
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

const formattedBalance = computed(() => formatAmount(balance.value));
const lastUpdatedLabel = computed(() => {
    if (!lastUpdated.value) {
        return '—';
    }

    return formatDate(lastUpdated.value);
});

onMounted(() => {
    void startPolling();
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">
                                Current balance
                            </p>
                            <p class="mt-2 text-3xl font-semibold">
                                {{ formattedBalance }}
                            </p>
                        </div>
                        <div class="text-right text-xs text-muted-foreground">
                            <p>Auto-refresh</p>
                            <p>Every 5s</p>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-muted-foreground">
                        Last updated: {{ lastUpdatedLabel }}
                    </div>
                    <div v-if="error" class="mt-3 text-sm text-rose-500">
                        {{ error }}
                    </div>
                </div>

                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
                >
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm text-muted-foreground">
                            Recent transactions
                        </p>
                        <p v-if="isLoading" class="text-xs text-muted-foreground">
                            Refreshing...
                        </p>
                    </div>
                    <div class="mt-4">
                        <p
                            v-if="isLoading && transactions.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            Loading transactions...
                        </p>
                        <p
                            v-else-if="transactions.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            No recent transactions.
                        </p>
                        <ul
                            v-else
                            class="divide-y divide-sidebar-border/70"
                        >
                            <li
                                v-for="transaction in transactions"
                                :key="transaction.id"
                                class="flex items-center justify-between gap-4 py-3"
                            >
                                <div>
                                    <p class="text-sm font-medium">
                                        {{
                                            transaction.description ||
                                            'Balance transaction'
                                        }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(transaction.created_at) }}
                                        · {{ formatType(transaction.type) }}
                                    </p>
                                </div>
                                <div
                                    class="text-sm font-semibold"
                                    :class="
                                        transaction.type?.toLowerCase() ===
                                        'credit'
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
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
