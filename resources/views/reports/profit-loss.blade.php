@extends('layouts.app')

@section('title', 'Profit & Loss')
@section('page-title', 'Profit & Loss Statement')

@section('content')
    <div x-data="profitLossData()">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Profit & Loss Statement
            </h2>
            <button @click="window.print()"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Print Report
            </button>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <input type="date" x-model="filters.start_date"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
            <input type="date" x-model="filters.end_date"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

            <select x-model="filters.section_id"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Sections</option>
                <template x-for="section in sections" :key="section.id">
                    <option :value="section.id" x-text="section.name"></option>
                </template>
            </select>
            <button @click="fetchReport" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Generate Report
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- P&L Statement -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">Financial Summary</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="formatDateRange()"></p>
            </div>

            <div class="p-7">
                <!-- Revenue Section -->
                <div class="mb-6">
                    <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Revenue</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Sales</span>
                            <span class="font-medium text-gray-900 dark:text-white"
                                x-text="formatCurrency(report.total_revenue)"></span>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">Total Revenue</span>
                            <span class="text-lg font-bold text-brand-500"
                                x-text="formatCurrency(report.total_revenue)"></span>
                        </div>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="mb-6">
                    <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Cost of Goods Sold</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Raw Materials (Procurement)</span>
                            <span class="font-medium text-gray-900 dark:text-white"
                                x-text="formatCurrency(report.material_costs)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Waste</span>
                            <span class="font-medium text-red-500" x-text="formatCurrency(report.waste_costs)"></span>
                        </div>
                    </div>
                    <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">Total COGS</span>
                            <span class="text-lg font-bold text-red-500" x-text="formatCurrency(report.total_cogs)"></span>
                        </div>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div
                    class="mb-6 rounded border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex justify-between">
                        <span class="font-medium text-green-800 dark:text-green-200">Gross Profit</span>
                        <span class="text-xl font-bold text-green-600 dark:text-green-400"
                            x-text="formatCurrency(report.gross_profit)"></span>
                    </div>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                        Margin: <span x-text="report.gross_margin + '%'"></span>
                    </p>
                </div>

                <!-- Operating Expenses -->
                <div class="mb-6">
                    <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Operating Expenses</h4>
                    <div class="space-y-2">
                        <template x-for="expense in report.expenses_by_category" :key="expense.category">
                            <div class="flex justify-between">
                                <span class="capitalize text-gray-600 dark:text-gray-400" x-text="expense.category"></span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="formatCurrency(expense.amount)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">Total Expenses</span>
                            <span class="text-lg font-bold text-red-500"
                                x-text="formatCurrency(report.total_expenses)"></span>
                        </div>
                    </div>
                </div>

                <!-- Net Profit -->
                <div class="rounded border p-4" :class="{
                                            'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20': report.net_profit >= 0,
                                            'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20': report.net_profit < 0
                                        }">
                    <div class="flex justify-between">
                        <span class="font-bold" :class="{
                                                    'text-green-800 dark:text-green-200': report.net_profit >= 0,
                                                    'text-red-800 dark:text-red-200': report.net_profit < 0
                                                }">
                            Net Profit
                        </span>
                        <span class="text-2xl font-bold" :class="{
                                                    'text-green-600 dark:text-green-400': report.net_profit >= 0,
                                                    'text-red-600 dark:text-red-400': report.net_profit < 0
                                                }" x-text="formatCurrency(report.net_profit)">
                        </span>
                    </div>
                    <p class="mt-1 text-sm" :class="{
                                                'text-green-700 dark:text-green-300': report.net_profit >= 0,
                                                'text-red-700 dark:text-red-300': report.net_profit < 0
                                            }">
                        Margin: <span x-text="report.net_margin + '%'"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function profitLossData() {
                return {
                    loading: true,
                    error: '',
                    report: {
                        total_revenue: 0,
                        material_costs: 0,
                        waste_costs: 0,
                        total_cogs: 0,
                        gross_profit: 0,
                        gross_margin: 0,
                        total_expenses: 0,
                        net_profit: 0,
                        net_margin: 0,
                        expenses_by_category: []
                    },
                    sections: [],
                    filters: {
                        start_date: '',
                        end_date: '',
                        section_id: ''
                    },

                    async init() {
                        // Set default date range (last 30 days)
                        const today = new Date();
                        const thirtyDaysAgo = new Date(today);
                        thirtyDaysAgo.setDate(today.getDate() - 30);

                        this.filters.start_date = thirtyDaysAgo.toISOString().split('T')[0];
                        this.filters.end_date = today.toISOString().split('T')[0];

                        await Promise.all([
                            this.fetchSections(),
                            this.fetchReport()
                        ]);
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchReport() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                            if (this.filters.section_id) params.append('section_id', this.filters.section_id);

                            const response = await API.get('/reports/profit-loss?' + params.toString());
                            this.report = response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load report';
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

                    formatDateRange() {
                        if (!this.filters.start_date || !this.filters.end_date) return '';

                        const formatDate = (dateStr) => {
                            if (!dateStr) return '';
                            // Create date using local time components to avoid timezone shifts
                            const parts = dateStr.split('-');
                            const year = parseInt(parts[0]);
                            const month = parseInt(parts[1]) - 1; // Months are 0-indexed
                            const day = parseInt(parts[2]);

                            const date = new Date(year, month, day);

                            return date.toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            });
                        };

                        return `${formatDate(this.filters.start_date)} - ${formatDate(this.filters.end_date)}`;
                    }
                }
            }
        </script>
    @endpush
@endsection