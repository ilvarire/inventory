@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expense Management')

@section('content')
    <div x-data="expensesData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    Business Expenses
                </h2>
            </div>
            @if(auth()->check() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                <div>
                    <a href="{{ route('expenses.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        Log Expense
                    </a>
                </div>
            @endif
        </div>

        <!-- Summary Card -->
        <div class="mb-6">
            <div
                class="rounded-sm border border-gray-200 bg-white px-7.5 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-md font-bold text-red-500" x-text="formatCurrency(summary.total_expenses)">
                            ₦0.00
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</span>
                    </div>
                    <div class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg class="fill-red-500" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M11 0.171875C4.92188 0.171875 0 5.09375 0 11.1719C0 17.25 4.92188 22.1719 11 22.1719C17.0781 22.1719 22 17.25 22 11.1719C22 5.09375 17.0781 0.171875 11 0.171875ZM11 20.6219C5.78125 20.6219 1.55 16.3906 1.55 11.1719C1.55 5.95312 5.78125 1.72188 11 1.72188C16.2188 1.72188 20.45 5.95312 20.45 11.1719C20.45 16.3906 16.2188 20.6219 11 20.6219Z"
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
                <select x-model="filters.category"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All Categories</option>
                    <option value="utilities">Utilities</option>
                    <option value="salaries">Salaries</option>
                    <option value="rent">Rent</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="marketing">Marketing</option>
                    <option value="supplies">Supplies</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button @click="fetchExpenses" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
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

        <!-- Expenses Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">ID</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Date</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Category</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Description</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Amount</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="expense in expenses" :key="expense.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="'#' + expense.id"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="formatDate(expense.expense_date)">
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize" :class="{
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': expense.type === 'utilities',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': expense.type === 'salaries',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300': expense.type === 'rent',
                                            'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300': expense.type === 'maintenance',
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': expense.type === 'marketing',
                                            'bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-300': expense.type === 'supplies',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300': expense.type === 'other'
                                        }" x-text="expense.type">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="expense.description"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-red-500" x-text="formatCurrency(expense.amount)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="'/expenses/' + expense.id" class="text-brand-500 hover:text-brand-600">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="expenses.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No expenses found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function expensesData() {
                return {
                    loading: true,
                    error: '',
                    expenses: [],
                    summary: {
                        total_expenses: 0
                    },
                    filters: {
                        start_date: '',
                        end_date: '',
                        category: ''
                    },

                    async init() {
                        // Set default date range (current month)
                        const today = new Date();
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

                        this.filters.start_date = firstDay.toISOString().split('T')[0];
                        this.filters.end_date = today.toISOString().split('T')[0];

                        await this.fetchExpenses();
                    },

                    async fetchExpenses() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                            if (this.filters.category) params.append('type', this.filters.category);

                            const response = await API.get('/expenses?' + params.toString());
                            this.expenses = response.data?.data || response.data || [];

                            // Calculate summary
                            this.summary.total_expenses = this.expenses.reduce((sum, expense) => sum + parseFloat(
                                expense.amount || 0), 0);
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load expenses';
                        } finally {
                            this.loading = false;
                        }
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
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection