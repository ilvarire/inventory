@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@section('content')
    <div x-data="expenseDetailsData({{ $id }})">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('expenses.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Expenses
            </a>
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

        <!-- Expense Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Expense <span x-text="'#' + expense.id"></span>
                            </h3>
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
                        </div>
                    </div>

                    <div class="p-7">
                        <div class="mb-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Amount</p>
                            <p class="mt-1 text-3xl font-bold text-red-500" x-text="formatCurrency(expense.amount)"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(expense.expense_date)"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Logged By</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="expense.created_by?.name || 'N/A'">
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="expense.description"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                @php
                    $user = json_decode(json_encode(session('user')));
                    $userRole = $user->role->name ?? 'Guest';
                @endphp

                <!-- Action Buttons -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Actions</h3>
                    </div>

                    <div class="p-7 space-y-3">
                        <!-- Print Button -->
                        <button @click="window.print()"
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Print Expense
                        </button>

                        @if(in_array($userRole, ['Manager', 'Admin']))
                            <!-- Delete Button -->
                            <button @click="deleteExpense"
                                class="w-full rounded-md bg-red-500 px-4 py-3 text-white hover:bg-red-600">
                                Delete Expense
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Summary -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Summary</h3>
                    </div>

                    <div class="p-7 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Category</p>
                            <p class="mt-1 font-medium capitalize text-gray-900 dark:text-white" x-text="expense.type">
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Amount</p>
                            <p class="mt-1 text-xl font-bold text-red-500" x-text="formatCurrency(expense.amount)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Logged On</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(expense.created_at)">
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function expenseDetailsData(expenseId) {
                return {
                    loading: true,
                    error: '',
                    expense: {},

                    async init() {
                        await this.fetchExpense();
                    },

                    async fetchExpense() {
                        this.loading = true;
                        this.error = '';

                        try {
                            this.expense = await API.get(`/expenses/${expenseId}`);
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load expense details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async deleteExpense() {
                        if (!confirm('Are you sure you want to delete this expense? This action cannot be undone.'))
                            return;

                        try {
                            await API.delete(`/expenses/${expenseId}`);
                            window.location.href = '/expenses';
                        } catch (error) {
                            alert(error.message || 'Failed to delete expense');
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
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection