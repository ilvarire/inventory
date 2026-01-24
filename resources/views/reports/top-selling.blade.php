@extends('layouts.app')

@section('title', 'Top Selling Items')
@section('page-title', 'Sales Performance')

@section('content')
    <div x-data="topSellingData()">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Top Selling Items
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

        <!-- Charts -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Top Items by Revenue -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Top Items by Revenue</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Top Items by Quantity -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Top Items by Quantity</h3>
                <canvas id="quantityChart"></canvas>
            </div>
        </div>

        <!-- Details Table -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">Performance Details</h3>
            </div>
            <div class="p-7">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Item Name</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Units Sold</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Revenue</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Profit</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-t border-gray-200 dark:border-gray-800">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">
                                        <span class="font-medium" x-text="item.item_name"></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="item.total_sold"></td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white"
                                        x-text="formatCurrency(item.total_revenue)"></td>
                                    <td class="px-4 py-3 text-green-600 dark:text-green-400"
                                        x-text="formatCurrency(item.total_profit)"></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="item.margin + '%'"></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No sales data found for this period
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            function topSellingData() {
                return {
                    loading: true,
                    items: [],
                    filters: {
                        start_date: '',
                        end_date: ''
                    },
                    charts: {
                        revenue: null,
                        quantity: null
                    },

                    async init() {
                        const today = new Date();
                        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

                        this.filters.start_date = firstDay.toISOString().split('T')[0];
                        this.filters.end_date = today.toISOString().split('T')[0];

                        await this.fetchReport();
                    },

                    async fetchReport() {
                        this.loading = true;
                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                            params.append('limit', 15);

                            const response = await API.get('/reports/top-selling?' + params.toString());
                            this.items = response.data || response || [];

                            await this.$nextTick();
                            this.renderCharts();
                        } catch (error) {
                            console.error('Fetch error:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    renderCharts() {
                        // Top Items by Revenue
                        const revenueCtx = document.getElementById('revenueChart');
                        if (this.charts.revenue) this.charts.revenue.destroy();

                        // Sort by revenue for chart
                        const byRevenue = [...this.items].sort((a, b) => b.total_revenue - a.total_revenue).slice(0, 5);

                        this.charts.revenue = new Chart(revenueCtx, {
                            type: 'bar',
                            data: {
                                labels: byRevenue.map(i => i.item_name),
                                datasets: [{
                                    label: 'Revenue',
                                    data: byRevenue.map(i => i.total_revenue),
                                    backgroundColor: '#10B981'
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });

                        // Top Items by Quantity
                        const quantityCtx = document.getElementById('quantityChart');
                        if (this.charts.quantity) this.charts.quantity.destroy();

                        // Sort by quantity for chart
                        const byQuantity = [...this.items].sort((a, b) => b.total_sold - a.total_sold).slice(0, 5);

                        this.charts.quantity = new Chart(quantityCtx, {
                            type: 'bar',
                            data: {
                                labels: byQuantity.map(i => i.item_name),
                                datasets: [{
                                    label: 'Units Sold',
                                    data: byQuantity.map(i => i.total_sold),
                                    backgroundColor: '#3B82F6'
                                }]
                            },
                            options: {
                                indexAxis: 'y',
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
                    }
                }
            }
        </script>
    @endpush
@endsection