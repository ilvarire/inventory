@extends('layouts.app')

@section('title', 'Inventory Health')
@section('page-title', 'Inventory Health Report')

@section('content')
    <div x-data="inventoryHealthData()">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Inventory Health
            </h2>
            <button @click="fetchReport"
                class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                Refresh Data
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-gray-900 dark:text-white" x-text="items.length"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</span>
            </div>
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-yellow-500" x-text="lowStockCount"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock Items</span>
            </div>
            <div
                class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-title-md font-bold text-green-500" x-text="okCount"></h4>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Healthy Stock</span>
            </div>
        </div>

        <!-- Filter -->
        <div class="mb-6">
            <input type="text" x-model="search" placeholder="Search materials..."
                class="w-full rounded border border-gray-300 bg-white px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Inventory Table -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Material</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Available Qty</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Min. Required</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in filteredItems" :key="index">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-4 text-gray-900 dark:text-white" x-text="item.raw_material"></td>
                                <td class="px-4 py-4 text-gray-900 dark:text-white" x-text="item.available_quantity"></td>
                                <td class="px-4 py-4 text-gray-900 dark:text-white" x-text="item.minimum_required"></td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': item.status === 'ok',
                                                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': item.status === 'reorder_required'
                                                }" x-text="item.status === 'reorder_required' ? 'Low Stock' : 'OK'">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredItems.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No items found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function inventoryHealthData() {
                return {
                    loading: true,
                    items: [],
                    search: '',

                    async init() {
                        await this.fetchReport();
                    },

                    async fetchReport() {
                        this.loading = true;
                        try {
                            const response = await API.get('/reports/inventory-health');
                            this.items = response.data || response || [];
                        } catch (error) {
                            console.error('Fetch error:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    get filteredItems() {
                        if (!this.search) return this.items;
                        return this.items.filter(i =>
                            i.raw_material.toLowerCase().includes(this.search.toLowerCase())
                        );
                    },

                    get lowStockCount() {
                        return this.items.filter(i => i.status === 'reorder_required').length;
                    },

                    get okCount() {
                        return this.items.filter(i => i.status === 'ok').length;
                    }
                }
            }
        </script>
    @endpush
@endsection