@extends('layouts.app')

@section('title', 'Sales')
@section('page-title', 'Sales')

@section('content')
    <div x-data="salesData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    Sales Transactions
                </h2>
            </div>
            @if(auth()->check() && (auth()->user()->isSales() || auth()->user()->isAdmin()))
                <div>
                    <a href="{{ route('sales.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        Record Sale
                    </a>
                </div>
            @endif
        </div>

        <!-- Revenue Summary Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-gray-900 dark:text-white"
                            x-text="formatCurrency(summary.total_revenue)">
                            ₦0.00
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</span>
                    </div>
                    <div
                        class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20">
                        <svg class="fill-green-500" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M11 0.171875C4.92188 0.171875 0 5.09375 0 11.1719C0 17.25 4.92188 22.1719 11 22.1719C17.0781 22.1719 22 17.25 22 11.1719C22 5.09375 17.0781 0.171875 11 0.171875ZM11 20.6219C5.78125 20.6219 1.55 16.3906 1.55 11.1719C1.55 5.95312 5.78125 1.72188 11 1.72188C16.2188 1.72188 20.45 5.95312 20.45 11.1719C20.45 16.3906 16.2188 20.6219 11 20.6219Z"
                                fill="" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-gray-900 dark:text-white" x-text="summary.total_sales">0
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</span>
                    </div>
                    <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <svg class="fill-blue-500" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M21.1063 18.0469L19.3875 3.23126C19.2157 1.71876 17.9438 0.584381 16.3969 0.584381H5.56878C4.05628 0.584381 2.78441 1.71876 2.57816 3.23126L0.859406 18.0469C0.756281 18.9063 1.03128 19.7313 1.61566 20.3844C2.20003 21.0375 3.02816 21.3813 3.92191 21.3813H18.0157C18.8782 21.3813 19.7063 21.0031 20.325 20.3844C20.9094 19.7656 21.2094 18.9063 21.1063 18.0469Z"
                                fill="" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-gray-900 dark:text-white"
                            x-text="formatCurrency(summary.total_profit)">
                            ₦0.00
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Profit</span>
                    </div>
                    <div
                        class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/20">
                        <svg class="fill-brand-500" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M11.7531 0.171875H10.2469C9.64062 0.171875 9.10938 0.703125 9.10938 1.30937V2.64062C9.10938 3.24687 9.64062 3.77812 10.2469 3.77812H11.7531C12.3594 3.77812 12.8906 3.24687 12.8906 2.64062V1.30937C12.8906 0.703125 12.3594 0.171875 11.7531 0.171875Z"
                                fill="" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <div>
                <input type="date" x-model="filters.start_date"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <input type="date" x-model="filters.end_date"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            </div>
            <div>
                <select x-model="filters.payment_method"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All Payment Methods</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <button @click="fetchSales" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Apply Filters
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Error State -->
        <div x-show="error && !loading"
            class="rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
        </div>

        <!-- Sales Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">Sale ID</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Date</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Section</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Items</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Total</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Payment</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="sale in sales" :key="sale.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="'#' + sale.id"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="formatDate(sale.sale_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="sale.section?.name || 'N/A'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="sale.items?.length + ' items'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-gray-900 dark:text-white"
                                        x-text="formatCurrency(sale.total_amount)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize" :class="{
                                                                                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': sale.payment_method === 'cash',
                                                                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': sale.payment_method === 'card',
                                                                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300': sale.payment_method === 'transfer'
                                                                                                    }"
                                        x-text="sale.payment_method">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="'/sales/' + sale.id" class="text-brand-500 hover:text-brand-600">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="sales.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No sales found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1" class="mt-4 border-t border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-800">
            <div class="flex flex-1 justify-between sm:hidden">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Previous
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-400">
                        Showing
                        <span class="font-medium" x-text="pagination.from"></span>
                        to
                        <span class="font-medium" x-text="pagination.to"></span>
                        of
                        <span class="font-medium" x-text="pagination.total"></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <span
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 dark:text-white dark:ring-gray-700">
                            Page <span x-text="pagination.current_page" class="mx-1"></span> of <span
                                x-text="pagination.last_page" class="mx-1"></span>
                        </span>

                        <button @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function salesData() {
                return {
                    loading: true,
                    error: '',
                    sales: [],
                    pagination: {},
                    summary: {
                        total_revenue: 0,
                        total_sales: 0,
                        total_profit: 0
                    },
                    filters: {
                        start_date: '',
                        end_date: '',
                        payment_method: ''
                    },

                    async init() {
                        // Set default date range (last 30 days)
                        const today = new Date();
                        const thirtyDaysAgo = new Date(today);
                        thirtyDaysAgo.setDate(today.getDate() - 30);

                        this.filters.start_date = thirtyDaysAgo.toISOString().split('T')[0];
                        this.filters.end_date = today.toISOString().split('T')[0];

                        this.$watch('filters', () => {
                            this.fetchSales(1);
                        }, { deep: true });

                        await this.fetchSales();
                    },

                    async fetchSales(page = 1) {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                            if (this.filters.payment_method) params.append('payment_method', this.filters.payment_method);
                            params.append('page', page);

                            const response = await API.get('/sales?' + params.toString());

                            // Updated structure: { pagination: {...}, summary: {...} }
                            const salesData = response.pagination || {};
                            this.sales = salesData.data || [];
                            this.pagination = salesData;

                            if (response.summary) {
                                this.summary = response.summary;
                            }

                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load sales';
                        } finally {
                            this.loading = false;
                        }
                    },

                    changePage(page) {
                        if (page < 1 || page > this.pagination.last_page) return;
                        this.fetchSales(page);
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection