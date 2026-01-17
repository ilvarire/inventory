@extends('layouts.app')

@section('title', 'Procurement')
@section('page-title', 'Procurement Management')

@section('content')
    <div x-data="procurementData()">
        <!-- Header with Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Procurements
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage supplier purchases and stock intake
                </p>
            </div>

            @if(in_array(auth()->user()->role->name ?? '', ['Admin', 'Procurement', 'Manager']))
                <a href="{{ route('procurement.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Procurement
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div
            class="mb-6 rounded-sm border border-gray-200 bg-white p-4 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Search
                    </label>
                    <input type="text" x-model="filters.search" @input.debounce.500ms="fetchProcurements()"
                        placeholder="Search reference or supplier..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:placeholder:text-gray-500" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status
                    </label>
                    <select x-model="filters.status" @change="fetchProcurements()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Date Range
                    </label>
                    <input type="date" x-model="filters.date" @change="fetchProcurements()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                </div>
            </div>
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

        <!-- Procurement Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">
                                Reference / Date
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Supplier
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Total Cost
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Status
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Created By
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="procurement in procurements" :key="procurement.id">
                            <tr
                                class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <h5 class="font-medium text-brand-500 dark:text-brand-400">
                                        <a :href="`/procurement/${procurement.id}`"
                                            x-text="procurement.reference_number"></a>
                                    </h5>
                                    <p class="text-sm text-gray-500" x-text="formatDate(procurement.procurement_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white font-medium"
                                        x-text="procurement.supplier?.name || 'N/A'"></p>
                                    <p class="text-xs text-gray-500"
                                        x-text="procurement.supplier?.rating ? 'Rating: ' + procurement.supplier.rating + '/5' : ''">
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white font-medium"
                                        x-text="formatCurrency(procurement.total_cost)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': procurement.status === 'completed',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': procurement.status === 'pending',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': procurement.status === 'cancelled'
                                        }" x-text="capitalize(procurement.status)"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                        x-text="procurement.user?.name || 'Unknown'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="`/procurement/${procurement.id}`"
                                            class="text-brand-500 hover:text-brand-600" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr x-show="procurements.length === 0 && !loading">
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No procurements found</p>
                                    <a href="{{ route('procurement.create') }}"
                                        class="mt-2 text-brand-500 hover:underline">Create your first procurement</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function procurementData() {
                return {
                    loading: true,
                    error: '',
                    procurements: [],
                    filters: {
                        search: '',
                        status: '',
                        date: ''
                    },

                    async init() {
                        await this.fetchProcurements();
                    },

                    async fetchProcurements() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = {};
                            if (this.filters.search) params.search = this.filters.search;
                            if (this.filters.status) params.status = this.filters.status;
                            if (this.filters.date) params.date = this.filters.date;

                            const response = await API.get('/procurements', params);
                            this.procurements = response.data || response;
                        } catch (error) {
                            console.error('Procurement fetch error:', error);
                            this.error = error.message || 'Failed to load procurements';
                            showError(this.error);
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

                    formatDate(date) {
                        if (!date) return 'N/A';
                        return new Date(date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    },

                    capitalize(string) {
                        if (!string) return '';
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                }
            }
        </script>
    @endpush
@endsection