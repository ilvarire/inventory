@extends('layouts.app')

@section('title', 'Production Details')
@section('page-title', 'Production Details')

@section('content')
    <div x-data="productionDetailsData({{ $id }})">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('production.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Production Logs
            </a>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Production Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Production Info -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">
                            Production Log <span x-text="'#' + production.id"></span>
                        </h3>
                    </div>

                    <div class="p-7">
                        <div class="mb-6 grid grid-cols-2 gap-5">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Recipe</p>
                                <p class="mt-1 text-lg font-medium text-gray-900 dark:text-white"
                                    x-text="production.recipe_version?.recipe?.name"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Production Date</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(production.production_date)"></p>
                            </div>
                        </div>

                        <!-- Yield Comparison -->
                        <div class="mb-6 grid grid-cols-3 gap-4 rounded border border-gray-200 p-4 dark:border-gray-800">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Expected Yield</p>
                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
                                    x-text="production.recipe_version?.recipe?.expected_yield + ' ' + (production.recipe_version?.recipe?.yield_unit || '')">
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Actual Yield</p>
                                <p class="mt-1 text-xl font-bold text-brand-500"
                                    x-text="production.quantity_produced + ' ' + (production.recipeVersion?.recipe?.yield_unit || '')">
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Variance</p>
                                <p class="mt-1 text-xl font-bold" :class="{
                                                    'text-green-500': (production.variance || 0) >= 0,
                                                    'text-red-500': (production.variance || 0) < 0
                                                }"
                                    x-text="((production.variance || 0) >= 0 ? '+' : '') + (production.variance || 0) + ' ' + (production.recipe_version?.recipe?.yield_unit || '')">
                                </p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div x-show="production.notes" class="border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="production.notes"></p>
                        </div>
                    </div>
                </div>

                <!-- Ingredients Used -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Materials Consumed</h3>
                    </div>

                    <div class="p-7">
                        <div class="space-y-2">
                            <template x-for="ingredient in (production.recipe_version?.items || [])" :key="ingredient.id">
                                <div
                                    class="flex items-center justify-between rounded border border-gray-200 p-3 dark:border-gray-800">
                                    <span class="text-gray-900 dark:text-white"
                                        x-text="ingredient.raw_material?.name"></span>
                                    <span class="font-medium text-gray-900 dark:text-white"
                                        x-text="ingredient.quantity_required + ' ' + (ingredient.raw_material?.unit || '')"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="space-y-6">
                <!-- Summary -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Summary</h3>
                    </div>

                    <div class="p-7 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                x-text="production.section?.name || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Produced By</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                x-text="production.chef?.name || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Logged On</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white"
                                x-text="formatDate(production.created_at)">
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function productionDetailsData(productionId) {
                return {
                    loading: true,
                    error: '',
                    production: {},

                    async init() {
                        await this.fetchProduction();
                    },

                    async fetchProduction() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get(`/productions/${productionId}`);
                            this.production = response.production || response.data?.production || response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load production details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    get variance() {
                        return this.production.variance || 0;
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection