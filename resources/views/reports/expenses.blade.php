@extends('layouts.app')

@section('title', 'Expense Report')
@section('page-title', 'Expense Analysis')

@section('content')
    <div x-data="expenseReportData()">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Expense Analysis Report
            </h2>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <input type="date" x-model="filters.start_date"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            <input type="date" x-model="filters.end_date"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            <button @click="fetchReport" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Generate Report
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Summary Cards -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-red-500" x-text="formatCurrency(report.total_expenses)"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</span>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-gray-900 dark:text-white" x-text="report.expense_count"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transactions</span>
            </div>
        </div>

        <!-- Charts -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Expenses by Category Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Expenses by Category</h3>
                <canvas id="expenseCategoryChart"></canvas>
            </div>

            <!-- Expenses by Section Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Expenses by Section</h3>
                <canvas id="expenseSectionChart"></canvas>
            </div>
        </div>

        <!-- Recent Expenses Table -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">Recent Expenses</h3>
            </div>
            <div class="p-7">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Date</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Category</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Description</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Amount</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="expense in report.recent_expenses" :key="expense.id">
                                <tr class="border-t border-gray-200 dark:border-gray-800">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="formatDate(expense.date)">
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="expense.category"></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="expense.description"></td>
                                    <td class="px-4 py-3 font-medium text-red-500" x-text="formatCurrency(expense.amount)">
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="expense.section">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            function expenseReportData() {
                return {
                    loading: true,
                    error: '',
                    report: {
                        total_expenses: 0,
                        expense_count: 0,
                        by_category: [],
                        by_section: [],
                        recent_expenses: []
                    },
                    filters: {
                        start_date: '',
                        end_date: ''
                    },
                    charts: {
                        category: null,
                        section: null
                    },

                    async init() {
                        // Set default date range (current month)
                        const today = new Date();
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

                        this.filters.start_date = firstDay.toISOString().split('T')[0];
                        this.filters.end_date = today.toISOString().split('T')[0];

                        await this.fetchReport();
                    },

                    async fetchReport() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);

                            const response = await API.get('/reports/expenses?' + params.toString());
                            this.report = response.data;

                            await this.$nextTick();
                            this.renderCharts();
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load report';
                        } finally {
                            this.loading = false;
                        }
                    },

                    renderCharts() {
                        // Expenses by Category Chart
                        const categoryCtx = document.getElementById('expenseCategoryChart');
                        if (this.charts.category) this.charts.category.destroy();

                        this.charts.category = new Chart(categoryCtx, {
                            type: 'doughnut',
                            data: {
                                labels: this.report.by_category?.map(c => c.category) || [],
                                datasets: [{
                                    data: this.report.by_category?.map(c => c.amount) || [],
                                    backgroundColor: [
                                        '#FCA5A5', '#FDBA74', '#FCD34D', '#86EFAC', '#93C5FD', '#A5B4FC', '#D8B4FE'
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });

                        // Expenses by Section Chart
                        const sectionCtx = document.getElementById('expenseSectionChart');
                        if (this.charts.section) this.charts.section.destroy();

                        this.charts.section = new Chart(sectionCtx, {
                            type: 'bar',
                            data: {
                                labels: this.report.by_section?.map(s => s.section_name) || [],
                                datasets: [{
                                    label: 'Expenses',
                                    data: this.report.by_section?.map(s => s.amount) || [],
                                    backgroundColor: '#3B82F6'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        // Check if it's a YYYY-MM-DD string
                        if (dateString.length === 10 && dateString.includes('-')) {
                            const parts = dateString.split('-');
                            const year = parseInt(parts[0]);
                            const month = parseInt(parts[1]) - 1;
                            const day = parseInt(parts[2]);
                            const date = new Date(year, month, day);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                        // Fallback for timestamps
                        return new Date(dateString).toLocaleDateString('en-US', {
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