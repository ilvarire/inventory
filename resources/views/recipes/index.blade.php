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

        <!-- Filters & Search -->
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <!-- Left Side: Search & Section Filter -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative">
                    <input type="text" x-model="search" placeholder="Search recipes..."
                        class="w-full rounded border border-gray-300 bg-white px-4 py-2 pl-10 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white md:w-64" />
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.16666 3.33332C5.945 3.33332 3.33333 5.945 3.33333 9.16666C3.33333 12.3883 5.945 15 9.16666 15C12.3883 15 15 12.3883 15 9.16666C15 5.945 12.3883 3.33332 9.16666 3.33332ZM1.66666 9.16666C1.66666 5.02452 5.02452 1.66666 9.16666 1.66666C13.3088 1.66666 16.6667 5.02452 16.6667 9.16666C16.6667 13.3088 13.3088 16.6667 9.16666 16.6667C5.02452 16.6667 1.66666 13.3088 1.66666 9.16666Z"
                                fill="currentColor" />
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M13.2857 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2362 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9107 18.0892L13.2857 14.4642C12.9603 14.1388 12.9603 13.6112 13.2857 13.2857Z"
                                fill="currentColor" />
                        </svg>
                    </span>
                </div>

                @if(auth()->check() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                    <select x-model="filterSection"
                        class="rounded border border-gray-300 bg-white px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Sections</option>
                        <template x-for="section in sections" :key="section.id">
                            <option :value="section.id" x-text="section.name"></option>
                        </template>
                    </select>
                @endif
            </div>

            <!-- Right Side: Sorting -->
            <div class="flex items-center gap-3">
                <select x-model="sortBy"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="created_at">Date Created</option>
                    <option value="name">Name</option>
                    <option value="expected_yield">Yield</option>
                    <option value="selling_price">Price</option>
                </select>

                <select x-model="sortOrder"
                    class="rounded border border-gray-300 bg-white px-4 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="desc">Desc</option>
                    <option value="asc">Asc</option>
                </select>
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

        <!-- Recipes Grid -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <template x-for="recipe in recipes" :key="recipe.id">
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
                            @if(!auth()->user()->isChef())
                                <a :href="'/production/create?recipe=' + recipe.id"
                                    class="grow rounded-md bg-brand-500 px-4 py-2 text-center text-sm text-white hover:bg-brand-600">
                                    Log Production
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </template>

            <div x-show="recipes.length === 0" class="col-span-full">
                <div
                    class="rounded-sm border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-gray-500 dark:text-gray-400">No recipes found matching your criteria</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div x-show="pagination.last_page > 1"
            class="mt-6 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-800">
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
            function recipesData() {
                return {
                    loading: true,
                    error: '',
                    recipes: [],
                    sections: [],
                    pagination: {},
                    filterSection: '',
                    search: '',
                    sortBy: 'created_at',
                    sortOrder: 'desc',
                    userSectionId: {{ auth()->user()->section_id ?? 'null' }},
                    isChef: {{ auth()->user()->isChef() ? 'true' : 'false' }},

                    async init() {
                        // Auto-set section filter for Chef
                        if (this.isChef && this.userSectionId) {
                            this.filterSection = this.userSectionId;
                        }

                        this.$watch('filterSection', () => {
                            this.fetchRecipes(1);
                        });

                        this.$watch('search', (value) => {
                            clearTimeout(this.searchTimeout);
                            this.searchTimeout = setTimeout(() => {
                                this.fetchRecipes(1);
                            }, 300);
                        });

                        this.$watch('sortBy', () => {
                            this.fetchRecipes(1);
                        });

                        this.$watch('sortOrder', () => {
                            this.fetchRecipes(1);
                        });

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

                    async fetchRecipes(page = 1) {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            if (this.filterSection) params.append('section_id', this.filterSection);
                            if (this.search) params.append('search', this.search);
                            params.append('sort_by', this.sortBy);
                            params.append('sort_order', this.sortOrder);
                            params.append('page', page);

                            const response = await API.get(`/recipes?${params.toString()}`);
                            this.recipes = response.data || [];
                            this.pagination = response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load recipes';
                        } finally {
                            this.loading = false;
                        }
                    },

                    changePage(page) {
                        if (page < 1 || page > this.pagination.last_page) return;
                        this.fetchRecipes(page);
                    },

                    // Removed filteredRecipes getter as server-side filtering is used
                    // Direct access to this.recipes is sufficient
                }
            }
        </script>
    @endpush
@endsection