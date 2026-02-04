@extends('layouts.app')

@section('title', 'Log Production')
@section('page-title', 'Log Production')

@section('content')
    <div x-data="logProductionData()">
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

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Log Production Batch
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitProduction">
                    <!-- Recipe Selection -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Recipe <span class="text-red-500">*</span>
                        </label>
                        <div x-data="{
                                    open: false,
                                    search: '',
                                    filteredRecipes: [],
                                    init() {
                                        this.filteredRecipes = recipes;
                                        this.$watch('recipes', value => {
                                            this.filteredRecipes = value;
                                            // If ID is already set (e.g. from URL), set the name
                                            if (formData.recipe_id) {
                                                const selected = value.find(r => r.id == formData.recipe_id);
                                                if (selected) this.search = selected.name;
                                            }
                                        });
                                    },
                                    filterRecipes() {
                                        if (this.search === '') {
                                            this.filteredRecipes = recipes;
                                        } else {
                                            this.filteredRecipes = recipes.filter(r => 
                                                r.name.toLowerCase().includes(this.search.toLowerCase())
                                            );
                                        }
                                    },
                                    selectRecipe(recipe) {
                                        formData.recipe_id = recipe.id;
                                        this.search = recipe.name;
                                        this.open = false;
                                        updateExpectedYield();
                                    },
                                    handleClickOutside() {
                                        this.open = false;
                                        const selected = recipes.find(r => r.id == formData.recipe_id);
                                        if (selected) {
                                            this.search = selected.name;
                                        } else {
                                            this.search = '';
                                        }
                                    }
                                }" class="relative" @click.outside="handleClickOutside()">
                            <input type="text" x-model="search" @input="filterRecipes(); open = true" @click="open = true"
                                @focus="open = true" placeholder="Search recipe..."
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                            <div x-show="open"
                                class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                style="display: none;">
                                <template x-for="recipe in filteredRecipes" :key="recipe.id">
                                    <div @click="selectRecipe(recipe)"
                                        class="cursor-pointer px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-200">
                                        <span x-text="recipe.name"></span>
                                        <span class="text-xs text-gray-500 ml-1"
                                            x-text="'(Yield: ' + recipe.expected_yield + ' ' + recipe.yield_unit + ')'"></span>
                                    </div>
                                </template>
                                <div x-show="filteredRecipes.length === 0" class="px-4 py-2 text-sm text-gray-500">
                                    No results found
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expected Yield Display -->
                    <div x-show="selectedRecipe"
                        class="mb-3 rounded border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            Expected Yield: <span class="font-medium"
                                x-text="selectedRecipe?.expected_yield + ' ' + (selectedRecipe?.yield_unit || '')"></span>
                        </p>
                    </div>

                    <!-- Production Date & Actual Yield -->
                    <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Production Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" x-model="formData.production_date" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Actual Yield/amount collected <span class="text-red-500">*</span>
                            </label>
                            <input type="number" x-model="formData.actual_yield" required min="0.01" step="0.01"
                                placeholder="Enter actual yield"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                    </div>

                    <!-- Variance Display -->
                    <div x-show="formData.actual_yield && selectedRecipe" class="mb-5.5">
                        <div class="rounded border p-4" :class="{
                                                                                            'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20': variance >= 0,
                                                                                            'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20': variance < 0
                                                                                        }">
                            <p class="text-sm" :class="{
                                                                                                'text-green-800 dark:text-green-200': variance >= 0,
                                                                                                'text-red-800 dark:text-red-200': variance < 0
                                                                                            }">
                                Variance: <span class="font-medium"
                                    x-text="(variance >= 0 ? '+' : '') + variance + ' ' + (selectedRecipe?.yield_unit || '')"></span>
                                <span x-show="variance < 0"> - Please explain the shortfall in notes</span>
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Notes
                        </label>
                        <textarea x-model="formData.notes" rows="4"
                            placeholder="Add any notes about this production batch (e.g., reasons for variance)..."
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mb-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('production.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Log Production</span>
                            <span x-show="loading">Logging...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function logProductionData() {
                return {
                    loading: false,
                    error: '',
                    recipes: [],
                    formData: {
                        recipe_id: '',
                        production_date: '',
                        actual_yield: '',
                        notes: ''
                    },

                    async init() {
                        // Set default date to today
                        this.formData.production_date = new Date().toISOString().split('T')[0];

                        // Check for pre-selected recipe from URL
                        const urlParams = new URLSearchParams(window.location.search);
                        const recipeId = urlParams.get('recipe');

                        await this.fetchRecipes();

                        if (recipeId) {
                            this.formData.recipe_id = recipeId;
                            this.updateExpectedYield();
                        }
                    },

                    async fetchRecipes() {
                        try {
                            const response = await API.get('/recipes');
                            // Handle both paginated and non-paginated responses
                            this.recipes = response.data?.data ? response.data.data : (response.data || []);
                        } catch (error) {
                            console.error('Failed to fetch recipes:', error);
                        }
                    },

                    updateExpectedYield() {
                        // This will trigger the computed property
                    },

                    get selectedRecipe() {
                        return this.recipes.find(r => r.id == this.formData.recipe_id);
                    },

                    get variance() {
                        if (!this.formData.actual_yield || !this.selectedRecipe) return 0;
                        return parseFloat(this.formData.actual_yield) - parseFloat(this.selectedRecipe.expected_yield || 0);
                    },

                    async submitProduction() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.post('/productions', this.formData);
                            window.location.href = '/production/' + (response.data?.id || response.id);
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to log production';
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection