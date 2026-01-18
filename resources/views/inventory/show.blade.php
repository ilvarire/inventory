@extends('layouts.app')

@section('title', 'Material Details')
@section('page-title', 'Material Details')

@section('content')
    <div x-data="materialDetailsData({{ $id }})">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('inventory.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Inventory
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

        <!-- Material Details -->
        <div x-show="!loading && !error" class="space-y-6">
            <!-- Header Card -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7.5 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="material.name"></h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            SKU: <span x-text="material.sku"></span>
                        </p>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium" :class="{
                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': material.quantity >
                                            material.reorder_level,
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': material
                                            .quantity <= material.reorder_level && material.quantity > 0,
                                        'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': material.quantity <= 0
                                    }"
                                x-text="material.quantity > material.reorder_level ? 'In Stock' : (material.quantity > 0 ? 'Low Stock' : 'Out of Stock')">
                            </span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Category: <span class="font-medium" x-text="material.category?.name || 'N/A'"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Current Quantity -->
                <div
                    class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Quantity</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        <span x-text="material.quantity"></span>
                        <span class="text-lg font-normal text-gray-500" x-text="material.unit"></span>
                    </p>
                </div>

                <!-- Unit Cost -->
                <div
                    class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit Cost</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                        x-text="formatCurrency(material.unit_cost)">
                    </p>
                </div>

                <!-- Total Value -->
                <div
                    class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Value</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"
                        x-text="formatCurrency(material.quantity * material.unit_cost)">
                    </p>
                </div>

                <!-- Reorder Level -->
                <div
                    class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Reorder Level</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        <span x-text="material.reorder_level"></span>
                        <span class="text-lg font-normal text-gray-500" x-text="material.unit"></span>
                    </p>
                </div>
            </div>

            <!-- Additional Information -->
            <div
                class="rounded-sm border border-gray-200 bg-white p-7.5 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Additional Information</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Storage Location</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="material.storage_location || 'N/A'">
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="material.supplier || 'N/A'"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(material.updated_at)">
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(material.created_at)">
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Actions -->
            <div class="flex gap-4">
                <a href="{{ route('inventory.movements', $id) }}"
                    class="inline-flex items-center gap-2 rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    View Movement History
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function materialDetailsData(materialId) {
                return {
                    loading: true,
                    error: '',
                    material: {},

                    async init() {
                        await this.fetchMaterial();
                    },

                    async fetchMaterial() {
                        this.loading = true;
                        this.error = '';

                        try {
                            this.material = await API.get(`/materials/${materialId}`);
                        } catch (error) {
                            console.error('Material fetch error:', error);
                            this.error = error.message || 'Failed to load material details';
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

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection