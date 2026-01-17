@extends('layouts.app')

@section('title', 'Low Stock Items')
@section('page-title', 'Low Stock Alert')

@section('content')
    <div x-data="lowStockData()">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Low Stock Items
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Materials that need to be reordered
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
        <div x-show="materials.length > 0 && !loading"
            class="mb-6 rounded-lg border-l-4 border-orange-500 bg-orange-50 p-4 dark:bg-orange-900/20">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-orange-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="font-medium text-orange-800 dark:text-orange-400">
                        <span x-text="materials.length"></span> material(s) need attention
                    </p>
                    <p class="text-sm text-orange-700 dark:text-orange-500">
                        These items are at or below their reorder quantity
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

        <!-- Low Stock Table -->
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
                                Category
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Current Stock
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Min Quantity
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Reorder Qty
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Supplier
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="material in materials" :key="material.id">
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <h5 class="font-medium text-gray-900 dark:text-white" x-text="material.name"></h5>
                                    <p class="text-sm text-gray-500" x-text="'Unit: ' + material.unit"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200"
                                        x-text="material.category"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium"
                                        :class="material.current_stock <= material.min_quantity ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400'"
                                        x-text="formatQuantity(material.current_stock, material.unit)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-600 dark:text-gray-400"
                                        x-text="formatQuantity(material.min_quantity, material.unit)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-600 dark:text-gray-400"
                                        x-text="formatQuantity(material.reorder_quantity, material.unit)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                        x-text="material.supplier?.name || 'N/A'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="`/inventory/${material.id}`" class="text-brand-500 hover:text-brand-600"
                                            title="View Details">
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
                        <tr x-show="materials.length === 0 && !loading">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-green-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400 font-medium">All materials are well stocked!
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">No items need reordering at
                                        this time</p>
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
            function lowStockData() {
                return {
                    loading: true,
                    error: '',
                    materials: [],

                    async init() {
                        await this.fetchLowStock();
                    },

                    async fetchLowStock() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get('/inventory/low-stock');
                            this.materials = response.data || response;
                        } catch (error) {
                            console.error('Low stock fetch error:', error);
                            this.error = error.message || 'Failed to load low stock items';
                            showError(this.error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    formatQuantity(quantity, unit) {
                        return `${parseFloat(quantity || 0).toFixed(2)} ${unit}`;
                    }
                }
            }
        </script>
    @endpush
@endsection