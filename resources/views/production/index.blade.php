@extends('layouts.app')

@section('title', 'Production Logs')
@section('page-title', 'Production Management')

@section('content')
    <div x-data="productionLogsData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    Production Logs
                </h2>
            </div>
            @php
                $user = json_decode(json_encode(session('user')));
                $userRole = $user->role->name ?? 'Guest';
            @endphp
            @if(in_array($userRole, ['Chef', 'Admin']))
                <div>
                    <a href="{{ route('production.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        Log Production
                    </a>
                </div>
            @endif
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
            <button @click="fetchLogs" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Apply Filters
            </button>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Production Logs Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">ID</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Date</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Recipe</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Section</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Expected</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actual</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Variance</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="log in logs" :key="log.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="'#' + log.id"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="formatDate(log.production_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="log.recipe?.name"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="log.section?.name"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white"
                                        x-text="log.recipe?.expected_yield + ' ' + (log.recipe?.yield_unit || '')"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-gray-900 dark:text-white"
                                        x-text="log.actual_yield + ' ' + (log.recipe?.yield_unit || '')"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': log.actual_yield >= log.recipe?.expected_yield,
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': log.actual_yield < log.recipe?.expected_yield
                                            }"
                                        x-text="((log.actual_yield - log.recipe?.expected_yield) >= 0 ? '+' : '') + (log.actual_yield - log.recipe?.expected_yield)">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <a :href="'/production/' + log.id" class="text-brand-500 hover:text-brand-600">
                                        View
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="logs.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No production logs found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function productionLogsData() {
                return {
                    loading: true,
                    error: '',
                    logs: [],
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

                        await this.fetchSections();
                        await this.fetchLogs();
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchLogs() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filters.start_date) params.append('start_date', this.filters.start_date);
                            if (this.filters.end_date) params.append('end_date', this.filters.end_date);
                            if (this.filters.section_id) params.append('section_id', this.filters.section_id);

                            const response = await API.get('/production?' + params.toString());
                            this.logs = response.data || [];
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load production logs';
                        } finally {
                            this.loading = false;
                        }
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