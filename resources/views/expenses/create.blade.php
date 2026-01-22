@extends('layouts.app')

@section('title', 'Log Expense')
@section('page-title', 'Log Expense')

@section('content')
    <div x-data="logExpenseData()">
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

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Log Business Expense
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitExpense">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.type" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Category</option>
                                <option value="utilities">Utilities</option>
                                <option value="salaries">Salaries</option>
                                <option value="rent">Rent</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="marketing">Marketing</option>
                                <option value="supplies">Supplies</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <input type="number" x-model="formData.amount" required min="0.01" step="0.01"
                                placeholder="Enter amount"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                    </div>

                    <!-- Expense Date -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" x-model="formData.expense_date" required
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Description -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="formData.description" rows="4" required
                            placeholder="Enter expense description..."
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mt-3 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('expenses.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Log Expense</span>
                            <span x-show="loading">Logging...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function logExpenseData() {
                return {
                    loading: false,
                    error: '',
                    formData: {
                        type: '',
                        amount: '',
                        expense_date: '',
                        description: ''
                    },

                    init() {
                        // Set default date to today
                        this.formData.expense_date = new Date().toISOString().split('T')[0];
                    },

                    async submitExpense() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.post('/expenses', this.formData);

                            // Redirect to expenses list or detail page
                            const expenseId = response.data?.id || response.id;
                            window.location.href = '/expenses/' + expenseId;
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to log expense';
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection