@extends('layouts.app')

@section('title', 'Expiring Items')
@section('page-title', 'Expiring Items Alert')

@section('content')
    <div x-data="expiringData()">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Expiring Items
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Materials expiring within the next 7 days
                </p>
            </div>

            <a href="{{ route('inventory.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Inventory
            </a>
        </div>

        <!-- Alert Banner -->
        <div x-show="items.length > 0 && !loading"
            class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="font-medium text-red-800 dark:text-red-400">
                        <span x-text="items.length"></span> batch(es) expiring soon
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-500">
                        These items will expire within the next 7 days
                    </p>
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

        <!-- Expiring Items Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">
                                Material Name
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Batch ID
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Quantity
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Expiry Date
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Days Left
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Unit Cost
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in items" :key="item.id">
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <h5 class="font-medium text-gray-900 dark:text-white"
                                        x-text="item.raw_material?.name || 'N/A'"></h5>
                                    <p class="text-sm text-gray-500" x-text="'Unit: ' + (item.raw_material?.unit || 'N/A')">
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-400"
                                        x-text="'#' + item.id"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-gray-900 dark:text-white"
                                        x-text="formatQuantity(item.quantity_remaining, item.raw_material?.unit)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-600 dark:text-gray-400" x-text="formatDate(item.expiry_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': getDaysLeft(item.expiry_date) <= 2,
                                            'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400': getDaysLeft(item.expiry_date) > 2 && getDaysLeft(item.expiry_date) <= 5,
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': getDaysLeft(item.expiry_date) > 5
                                        }" x-text="getDaysLeft(item.expiry_date) + ' days'"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-600 dark:text-gray-400" x-text="formatCurrency(item.unit_cost)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="`/inventory/${item.raw_material_id}`"
                                            class="text-brand-500 hover:text-brand-600" title="View Material">
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
                        <tr x-show="items.length === 0 && !loading">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-green-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium">No items expiring soon!</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">All batches are fresh</p>
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
            function expiringData() {
                return {
                    loading: true,
                    error: '',
                    items: [],

                    async init() {
                        await this.fetchExpiring();
                    },

                    async fetchExpiring() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get('/inventory/expiring');
                            this.items = response.data || response;
                        } catch (error) {
                            console.error('Expiring items fetch error:', error);
                            this.error = error.message || 'Failed to load expiring items';
                            showError(this.error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    formatQuantity(quantity, unit) {
                        return `${parseFloat(quantity || 0).toFixed(2)} ${unit || ''}`;
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

                    getDaysLeft(expiryDate) {
                        if (!expiryDate) return 0;
                        const today = new Date();
                        const expiry = new Date(expiryDate);
                        const diffTime = expiry - today;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        return Math.max(0, diffDays);
                    }
                }
            }
        </script>
    @endpush
@endsection