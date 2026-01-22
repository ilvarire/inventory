@extends('layouts.app')

@section('title', 'Sale Details')
@section('page-title', 'Sale Details')

@section('content')
    <div x-data="saleDetailsData({{ $id }})">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('sales.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Sales
            </a>
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

        <!-- Sale Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Receipt Card -->
                <div id="receipt"
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Sale Receipt <span x-text="'#' + sale.id"></span>
                            </h3>
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize" :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': sale.payment_method === 'cash',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': sale.payment_method === 'card',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300': sale.payment_method === 'transfer'
                                        }" x-text="sale.payment_method">
                            </span>
                        </div>
                    </div>

                    <div class="p-7">
                        <!-- Business Info -->
                        <div class="mb-6 text-center">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Your Business Name</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Multi-Section Food Business</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Contact: +234 XXX XXX XXXX</p>
                        </div>

                        <!-- Sale Info -->
                        <div
                            class="mb-6 grid grid-cols-2 gap-4 border-t border-b border-gray-200 py-4 dark:border-gray-800">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Receipt No:</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="'#' + sale.id"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date:</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="formatDate(sale.sale_date)">
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Section:</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="sale.section?.name || 'N/A'">
                                </p>
                            </div>
                            <div x-show="sale.customer_name">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Customer:</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="sale.customer_name"></p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="mb-6">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-800">
                                        <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Item</th>
                                        <th class="pb-3 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Qty</th>
                                        <th class="pb-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Price</th>
                                        <th class="pb-3 text-right text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in sale.items" :key="item.id">
                                        <tr class="border-b border-gray-200 dark:border-gray-800">
                                            <td class="py-3 text-gray-900 dark:text-white" x-text="item.item_name"></td>
                                            <td class="py-3 text-center text-gray-900 dark:text-white"
                                                x-text="item.quantity"></td>
                                            <td class="py-3 text-right text-gray-900 dark:text-white"
                                                x-text="formatCurrency(item.unit_price)"></td>
                                            <td class="py-3 text-right font-medium text-gray-900 dark:text-white"
                                                x-text="formatCurrency(item.quantity * item.unit_price)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between border-t border-gray-200 pt-2 dark:border-gray-800">
                                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                                    <span class="text-lg font-bold text-brand-500"
                                        x-text="formatCurrency(sale.total_amount)"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                                    <span class="font-medium capitalize text-gray-900 dark:text-white"
                                        x-text="sale.payment_method"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-8 border-t border-gray-200 pt-4 text-center dark:border-gray-800">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Thank you for your business!</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500">This is a computer-generated receipt</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- Action Buttons -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Actions</h3>
                    </div>

                    <div class="p-7 space-y-3">
                        <!-- Print Receipt Button -->
                        <button @click="printReceipt"
                            class="w-full rounded-md bg-brand-500 px-4 py-3 text-white hover:bg-brand-600">
                            <div class="flex items-center justify-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Receipt
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Sale Summary -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Summary</h3>
                    </div>

                    <div class="p-7 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Items</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="sale.items?.length || 0">
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Revenue</p>
                            <p class="text-xl font-bold text-green-500" x-text="formatCurrency(sale.total_amount)"></p>
                        </div>
                        <div x-show="sale.profit">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Profit</p>
                            <p class="text-xl font-bold text-brand-500" x-text="formatCurrency(sale.profit)"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sold By</p>
                            <p class="font-medium text-gray-900 dark:text-white" x-text="sale.sales_user?.name || 'N/A'">
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function saleDetailsData(saleId) {
                return {
                    loading: true,
                    error: '',
                    sale: {},

                    async init() {
                        await this.fetchSale();
                    },

                    async fetchSale() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get(`/sales/${saleId}`);
                            this.sale = response.data?.sale || response.sale || response.data || response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load sale details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    printReceipt() {
                        window.print();
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }
            }
        </script>

        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #receipt,
                #receipt * {
                    visibility: visible;
                }

                #receipt {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
            }
        </style>
    @endpush
@endsection