@extends('layouts.app')

@section('title', 'Recipes')
@section('page-title', 'Recipe Management')

@section('content')
    <div x-data="recipesData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    Recipes
                </h2>
            </div>
            @if(auth()->check() && auth()->user()->isAdmin())
                <div>
                    <a href="{{ route('recipes.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        Create Recipe
                    </a>
                </div>
            @endif
        </div>

        <!-- Filters (Manager/Admin only) -->
        @if(auth()->check() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
            <div class="mb-6 flex flex-wrap gap-3">
                <select x-model="filterSection"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">All Sections</option>
                    <template x-for="section in sections" :key="section.id">
                        <option :value="section.id" x-text="section.name"></option>
                    </template>
                </select>
            </div>
        @endif

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

        <!-- Recipes Grid -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <template x-for="recipe in filteredRecipes" :key="recipe.id">
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white" x-text="recipe.name"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="recipe.section?.name"></p>
                    </div>

                    <div class="p-6">
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400" x-text="recipe.description"></p>

                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Ingredients</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="(recipe.items?.length || 0)">
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Yield</p>
                                <p class="font-medium text-gray-900 dark:text-white"
                                    x-text="recipe.expected_yield + ' ' + (recipe.yield_unit || '')"></p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a :href="'/recipes/' + recipe.id"
                                class="grow rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                View Details
                            </a>
                            <a :href="'/production/create?recipe=' + recipe.id"
                                class="grow rounded-md bg-brand-500 px-4 py-2 text-center text-sm text-white hover:bg-brand-600">
                                Log Production
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="filteredRecipes.length === 0" class="col-span-full">
                <div
                    class="rounded-sm border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-gray-500 dark:text-gray-400">No recipes found</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function recipesData() {
                return {
                    loading: true,
                    error: '',
                    recipes: [],
                    sections: [],
                    filterSection: '',
                    userSectionId: {{ auth()->user()->section_id ?? 'null' }},
                    isChef: {{ auth()->user()->isChef() ? 'true' : 'false' }},

                    async init() {
                        // Auto-set section filter for Chef
                        if (this.isChef && this.userSectionId) {
                            this.filterSection = this.userSectionId;
                        }

                        await this.fetchSections();
                        await this.fetchRecipes();
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchRecipes() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get('/recipes');
                            this.recipes = response.data || [];
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load recipes';
                        } finally {
                            this.loading = false;
                        }
                    },

                    get filteredRecipes() {
                        if (!this.filterSection) return this.recipes;
                        return this.recipes.filter(recipe => recipe.section_id == this.filterSection);
                    }
                }
            }
        </script>
    @endpush
@endsection