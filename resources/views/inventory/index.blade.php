@extends('layouts.app')

@section('title', 'Inventory')
@section('page-title', 'Raw Materials Inventory')

@section('content')
    <div x-data="inventoryData()">
        <!-- Header with Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Raw Materials
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage and track all raw materials inventory
                </p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('inventory.low-stock') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Low Stock
                </a>

                <a href="{{ route('inventory.expiring') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Expiring Soon
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="mb-6 rounded-sm border border-gray-200 bg-white p-4 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Search
                    </label>
                    <input type="text" x-model="filters.search" @input.debounce.500ms="fetchInventory()"
                        placeholder="Search materials..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:placeholder:text-gray-500" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Category
                    </label>
                    <select x-model="filters.category" @change="fetchInventory()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="">All Categories</option>
                        <option value="vegetables">Vegetables</option>
                        <option value="meat">Meat</option>
                        <option value="seafood">Seafood</option>
                        <option value="dairy">Dairy</option>
                        <option value="grains">Grains</option>
                        <option value="spices">Spices</option>
                        <option value="beverages">Beverages</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Stock Status
                    </label>
                    <select x-model="filters.stock_status" @change="fetchInventory()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="">All Status</option>
                        <option value="good">Good Stock</option>
                        <option value="low">Low Stock</option>
                        <option value="critical">Critical</option>
                    </select>
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

        <!-- Inventory Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="px-4 py-6 md:px-6 xl:px-7.5">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Materials List (<span x-text="materials.length"></span>)
                </h4>
            </div>

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
                                Min/Reorder
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Status
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
                                    <p class="text-gray-900 dark:text-white font-medium"
                                        x-text="formatQuantity(material.current_stock, material.unit)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Min: <span x-text="material.min_quantity"></span><br>
                                        Reorder: <span x-text="material.reorder_quantity"></span>
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': getStockStatus(material) === 'good',
                                            'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400': getStockStatus(material) === 'low',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': getStockStatus(material) === 'critical'
                                        }" x-text="getStockStatusText(material)"></span>
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
                                        <a :href="`/inventory/${material.id}/movements`"
                                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                            title="Movement History">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr x-show="materials.length === 0 && !loading">
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No materials found</p>
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
            function inventoryData() {
                return {
                    loading: true,
                    error: '',
                    materials: [],
                    filters: {
                        search: '',
                        category: '',
                        stock_status: ''
                    },

                    async init() {
                        await this.fetchInventory();
                    },

                    async fetchInventory() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = {};
                            if (this.filters.search) params.search = this.filters.search;
                            if (this.filters.category) params.category = this.filters.category;

                            const response = await API.get('/inventory', params);
                            this.materials = response.data || response;

                            // Apply client-side stock status filter if needed
                            if (this.filters.stock_status) {
                                this.materials = this.materials.filter(material => {
                                    const status = this.getStockStatus(material);
                                    return status === this.filters.stock_status;
                                });
                            }
                        } catch (error) {
                            console.error('Inventory fetch error:', error);
                            this.error = error.message || 'Failed to load inventory';
                            showError(this.error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    getStockStatus(material) {
                        const stock = material.current_stock || 0;
                        const min = material.min_quantity || 0;
                        const reorder = material.reorder_quantity || 0;

                        if (stock <= min) return 'critical';
                        if (stock <= reorder) return 'low';
                        return 'good';
                    },

                    getStockStatusText(material) {
                        const status = this.getStockStatus(material);
                        return {
                            'good': 'Good Stock',
                            'low': 'Low Stock',
                            'critical': 'Critical'
                        }[status];
                    },

                    formatQuantity(quantity, unit) {
                        return `${parseFloat(quantity || 0).toFixed(2)} ${unit}`;
                    }
                }
            }
        </script>
    @endpush
@endsection