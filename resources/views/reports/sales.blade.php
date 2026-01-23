@extends('layouts.app')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('content')
    <div x-data="salesReportData()">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Sales Performance Report
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
            <button @click="exportReport"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Export CSV
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Summary Cards -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-brand-500" x-text="formatCurrency(report.total_revenue)">
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</span>
                    </div>
                    <div
                        class="flex h-11.5 w-11.5 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/20">
                        <svg class="fill-brand-500" width="22" height="22" viewBox="0 0 22 22" fill="none">
                            <path
                                d="M11 0C4.92 0 0 4.92 0 11C0 17.08 4.92 22 11 22C17.08 22 22 17.08 22 11C22 4.92 17.08 0 11 0Z"
                                fill="" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-gray-900 dark:text-white" x-text="report.total_sales"></h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-green-500" x-text="formatCurrency(report.total_profit)">
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Profit</span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-title-sm font-bold text-gray-900 dark:text-white"
                            x-text="formatCurrency(report.average_sale)">
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Sale</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Revenue Trend Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Revenue Trend</h3>
                <canvas id="revenueTrendChart"></canvas>
            </div>

            <!-- Payment Methods Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Payment Methods</h3>
                <canvas id="paymentMethodsChart"></canvas>
            </div>
        </div>

        <!-- Sales by Section -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">Sales by Section</h3>
            </div>
            <div class="p-7">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Section</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Sales Count</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Revenue</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="section in report.by_section" :key="section.section_id">
                                <tr class="border-t border-gray-200 dark:border-gray-800">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="section.section_name"></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="section.sales_count"></td>
                                    <td class="px-4 py-3 font-medium text-brand-500"
                                        x-text="formatCurrency(section.revenue)"></td>
                                    <td class="px-4 py-3 font-medium text-green-500"
                                        x-text="formatCurrency(section.profit)"></td>
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
            function salesReportData() {
                return {
                    loading: true,
                    error: '',
                    report: {
                        total_revenue: 0,
                        total_sales: 0,
                        total_profit: 0,
                        average_sale: 0,
                        by_section: [],
                        by_payment_method: [],
                        daily_revenue: []
                    },
                    filters: {
                        start_date: '',
                        end_date: ''
                    },
                    charts: {
                        revenue: null,
                        payment: null
                    },

                    async init() {
                        // Set default date range (last 30 days)
                        const today = new Date();
                        const thirtyDaysAgo = new Date(today);
                        thirtyDaysAgo.setDate(today.getDate() - 30);

                        this.filters.start_date = thirtyDaysAgo.toISOString().split('T')[0];
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

                            const response = await API.get('/reports/sales?' + params.toString());
                            this.report = response;

                            // Wait for DOM update
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
                        // Revenue Trend Chart
                        const revenueCtx = document.getElementById('revenueTrendChart');
                        if (this.charts.revenue) this.charts.revenue.destroy();

                        this.charts.revenue = new Chart(revenueCtx, {
                            type: 'line',
                            data: {
                                labels: this.report.daily_revenue?.map(d => d.date) || [],
                                datasets: [{
                                    label: 'Revenue',
                                    data: this.report.daily_revenue?.map(d => d.revenue) || [],
                                    borderColor: '#3C50E0',
                                    backgroundColor: 'rgba(60, 80, 224, 0.1)',
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });

                        // Payment Methods Chart
                        const paymentCtx = document.getElementById('paymentMethodsChart');
                        if (this.charts.payment) this.charts.payment.destroy();

                        this.charts.payment = new Chart(paymentCtx, {
                            type: 'doughnut',
                            data: {
                                labels: this.report.by_payment_method?.map(p => p.payment_method) || [],
                                datasets: [{
                                    data: this.report.by_payment_method?.map(p => p.revenue) || [],
                                    backgroundColor: ['#10B981', '#3C50E0', '#8B5CF6']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });
                    },

                    exportReport() {
                        // Simple CSV export
                        let csv = 'Section,Sales Count,Revenue,Profit\n';
                        this.report.by_section.forEach(section => {
                            csv += `${section.section_name},${section.sales_count},${section.revenue},${section.profit}\n`;
                        });

                        const blob = new Blob([csv], { type: 'text/csv' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `sales-report-${this.filters.start_date}-to-${this.filters.end_date}.csv`;
                        a.click();
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection