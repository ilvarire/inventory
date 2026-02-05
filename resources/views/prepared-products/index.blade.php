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
                        <template x-for="(product, index) in products" :key="product.id">
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
                        <tr x-show="products.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No prepared products found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1" class="mt-4 border-t border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-800">
            <div class="flex flex-1 justify-between sm:hidden">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Previous
                </button>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-400">
                        Showing
                        <span class="font-medium" x-text="pagination.from"></span>
                        to
                        <span class="font-medium" x-text="pagination.to"></span>
                        of
                        <span class="font-medium" x-text="pagination.total"></span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <span
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 dark:text-white dark:ring-gray-700">
                            Page <span x-text="pagination.current_page" class="mx-1"></span> of <span
                                x-text="pagination.last_page" class="mx-1"></span>
                        </span>

                        <button @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            return {
                loading: true,
                products: [],
                pagination: {},
                search: '',
                filters: {
                    status: ''
                },

                async init() {
                    this.$watch('search', () => {
                        this.fetchProducts(1);
                    });
                    this.$watch('filters.status', () => {
                        this.fetchProducts(1);
                    });
                    await this.fetchProducts();
                },

                async fetchProducts(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.search) params.append('search', this.search);
                        if (this.filters.status) params.append('status', this.filters.status);
                        params.append('page', page);

                        const response = await API.get(`/prepared-inventory?${params.toString()}`);
                        this.products = response.data || [];
                        this.pagination = response;
                    } catch (error) {
                        console.error('Fetch error:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                changePage(page) {
                    if (page < 1 || page > this.pagination.last_page) return;
                    this.fetchProducts(page);
                },

                // Removed filteredProducts as filtering is now server-side

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