@extends('layouts.app')

@section('title', 'Waste Report')
@section('page-title', 'Waste Analysis Report')

@section('content')
    <div x-data="wasteReportData()">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Waste Analysis Report
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
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-red-500" x-text="formatCurrency(report.total_waste_cost)"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Waste Cost</span>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-gray-900 dark:text-white" x-text="report.total_waste_count"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Waste Incidents</span>
            </div>

            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-gray-900 dark:text-white"
                    x-text="formatCurrency(report.average_waste_cost)"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Waste Cost</span>
            </div>
        </div>

        <!-- Charts -->
        <div x-show="!loading" class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Waste by Reason Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Waste by Reason</h3>
                <canvas id="wasteReasonChart"></canvas>
            </div>

            <!-- Waste by Section Chart -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-medium text-gray-900 dark:text-white">Waste by Section</h3>
                <canvas id="wasteSectionChart"></canvas>
            </div>
        </div>

        <!-- Top Wasted Materials -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">Top Wasted Materials</h3>
            </div>
            <div class="p-7">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Material</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Quantity Wasted</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Waste Cost</th>
                                <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Incidents</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="material in report.top_wasted_materials" :key="material.material_id">
                                <tr class="border-t border-gray-200 dark:border-gray-800">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="material.material_name">
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white"
                                        x-text="material.total_quantity + ' ' + (material.unit || '')"></td>
                                    <td class="px-4 py-3 font-medium text-red-500"
                                        x-text="formatCurrency(material.total_cost)"></td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white" x-text="material.incident_count">
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
            function wasteReportData() {
                return {
                    loading: true,
                    error: '',
                    report: {
                        total_waste_cost: 0,
                        total_waste_count: 0,
                        average_waste_cost: 0,
                        by_reason: [],
                        by_section: [],
                        top_wasted_materials: []
                    },
                    filters: {
                        start_date: '',
                        end_date: ''
                    },
                    charts: {
                        reason: null,
                        section: null
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

                            const response = await API.get('/reports/waste?' + params.toString());
                            this.report = response;

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
                        // Waste by Reason Chart
                        const reasonCtx = document.getElementById('wasteReasonChart');
                        if (this.charts.reason) this.charts.reason.destroy();

                        this.charts.reason = new Chart(reasonCtx, {
                            type: 'pie',
                            data: {
                                labels: this.report.by_reason?.map(r => r.reason) || [],
                                datasets: [{
                                    data: this.report.by_reason?.map(r => r.cost) || [],
                                    backgroundColor: ['#EF4444', '#F59E0B', '#FBBF24', '#9CA3AF']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true
                            }
                        });

                        // Waste by Section Chart
                        const sectionCtx = document.getElementById('wasteSectionChart');
                        if (this.charts.section) this.charts.section.destroy();

                        this.charts.section = new Chart(sectionCtx, {
                            type: 'bar',
                            data: {
                                labels: this.report.by_section?.map(s => s.section_name) || [],
                                datasets: [{
                                    label: 'Waste Cost',
                                    data: this.report.by_section?.map(s => s.cost) || [],
                                    backgroundColor: '#EF4444'
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
                    }
                }
            }
        </script>
    @endpush
@endsection