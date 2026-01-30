import { ref } from 'vue';

export type UserBalanceTransaction = {
    id: number;
    type: string;
    amount: number;
    description: string | null;
    balance_after: number;
    created_at: string;
};

export type BalanceData = {
    balance: number | string;
    transactions: UserBalanceTransaction[];
};

type BalanceResponse = BalanceData | { data: BalanceData };
type TransactionsResponse =
    | { data: UserBalanceTransaction[] }
    | { data: { data: UserBalanceTransaction[] } };

type UseBalanceReturn = {
    balance: typeof balance;
    transactions: typeof transactions;
    isLoading: typeof isLoading;
    error: typeof error;
    lastUpdated: typeof lastUpdated;
    refresh: () => Promise<void>;
    startPolling: () => Promise<void>;
    stopPolling: () => void;
};

const POLL_INTERVAL_MS = 5000;

const balance = ref<number | null>(null);
const transactions = ref<UserBalanceTransaction[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);
const lastUpdated = ref<string | null>(null);

const fetchJson = async <T>(url: string): Promise<T> => {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch: ${response.status}`);
    }

    return response.json();
};

const unwrapResource = <T>(payload: T | { data: T }): T => {
    if (payload && typeof payload === 'object' && 'data' in payload) {
        return (payload as { data: T }).data;
    }

    return payload as T;
};

const unwrapTransactions = (
    payload: TransactionsResponse,
): UserBalanceTransaction[] => {
    const unwrapped = unwrapResource(payload as unknown);

    if (
        unwrapped &&
        typeof unwrapped === 'object' &&
        'data' in (unwrapped as { data?: unknown }) &&
        Array.isArray((unwrapped as { data?: unknown }).data)
    ) {
        return (unwrapped as { data: UserBalanceTransaction[] }).data;
    }

    if (Array.isArray(unwrapped)) {
        return unwrapped as UserBalanceTransaction[];
    }

    return [];
};

const fetchBalance = async (): Promise<void> => {
    const payload = await fetchJson<BalanceResponse>('/finance/balance');
    const data = unwrapResource(payload);
    const parsed = Number(data.balance);
    balance.value = Number.isFinite(parsed) ? parsed : null;
};

const fetchTransactions = async (): Promise<void> => {
    const payload = await fetchJson<TransactionsResponse>('/finance/transactions');
    transactions.value = unwrapTransactions(payload).slice(0, 5);
};

const refresh = async (): Promise<void> => {
    isLoading.value = true;
    error.value = null;

    try {
        await Promise.all([fetchBalance(), fetchTransactions()]);
        lastUpdated.value = new Date().toISOString();
    } catch {
        error.value = 'Failed to refresh balance data';
    } finally {
        isLoading.value = false;
    }
};

let pollHandle: number | null = null;

const stopPolling = (): void => {
    if (pollHandle !== null) {
        window.clearInterval(pollHandle);
        pollHandle = null;
    }
};

const startPolling = async (): Promise<void> => {
    stopPolling();
    await refresh();
    pollHandle = window.setInterval(refresh, POLL_INTERVAL_MS);
};

export const useBalance = (): UseBalanceReturn => {
    return {
        balance,
        transactions,
        isLoading,
        error,
        lastUpdated,
        refresh,
        startPolling,
        stopPolling,
    };
};
