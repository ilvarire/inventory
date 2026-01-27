@extends('layouts.app')

@section('title', 'Prepared Products')
@section('page-title', 'Prepared Products Inventory')

@section('content')
    <div x-data="preparedProductsData()">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                Prepared Products
            </h2>
            <div class="flex gap-3">
                <button @click="fetchProducts"
                    class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <input type="text" x-model="search" placeholder="Search products..."
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

            <select x-model="filters.status"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="low_stock">Low Stock</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Products Table -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">#</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Product Name</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Quantity</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Unit</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Selling Price</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Expiry Date</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(product, index) in filteredProducts" :key="product.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="index + 1"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="product.item_name"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="product.quantity"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="product.unit"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium text-green-500" x-text="formatCurrency(product.selling_price)">
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="formatDate(product.expiry_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': getStatus(product) === 'Available',
                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': getStatus(product) === 'Low Stock',
                                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': getStatus(product) === 'Expired' || getStatus(product) === 'Sold Out'
                                                        }" x-text="getStatus(product)">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredProducts.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No prepared products found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function preparedProductsData() {
                return {
                    loading: true,
                    products: [],
                    search: '',
                    filters: {
                        status: ''
                    },

                    async init() {
                        await this.fetchProducts();
                    },

                    async fetchProducts() {
                        this.loading = true;
                        try {
                            const response = await API.get('/prepared-inventory');
                            this.products = response.data || response || [];
                        } catch (error) {
                            console.error('Fetch error:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    get filteredProducts() {
                        const filtered = this.products.filter(product => {
                            const matchesSearch = product.item_name.toLowerCase().includes(this.search.toLowerCase());
                            const status = this.getStatus(product).toLowerCase().replace(' ', '_');
                            const matchesStatus = !this.filters.status || status === this.filters.status;
                            return matchesSearch && matchesStatus;
                        });

                        // Sort by ID descending (newest first)
                        return filtered.sort((a, b) => b.id - a.id);
                    },

                    getStatus(product) {
                        if (!product.expiry_date) return 'Available';

                        const today = new Date();
                        today.setHours(0, 0, 0, 0); // Start of today

                        // Parse expiry as local date
                        let expiry;
                        if (product.expiry_date.length === 10 && product.expiry_date.includes('-')) {
                            const [year, month, day] = product.expiry_date.split('-');
                            expiry = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
                        } else {
                            expiry = new Date(product.expiry_date);
                        }
                        expiry.setHours(0, 0, 0, 0); // Start of expiry day

                        if (expiry < today) return 'Expired';
                        if (parseFloat(product.quantity) <= 0) return 'Sold Out';
                        if (parseFloat(product.quantity) <= 5) return 'Low Stock';
                        return 'Available';
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        // Check if it's a YYYY-MM-DD string
                        if (dateString.length === 10 && dateString.includes('-')) {
                            const parts = dateString.split('-');
                            const year = parseInt(parts[0]);
                            const month = parseInt(parts[1]) - 1;
                            const day = parseInt(parts[2]);
                            const date = new Date(year, month, day);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }
                        return new Date(dateString).toLocaleDateString('en-US', {
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