@extends('layouts.app')

@section('title', 'Recipe Details')
@section('page-title', 'Recipe Details')

@section('content')
    <div x-data="recipeDetailsData({{ $id }})">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('recipes.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Recipes
            </a>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Recipe Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recipe Info -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white" x-text="recipe.name"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="recipe.section?.name"></p>
                    </div>

                    <div class="p-7">
                        <div x-show="recipe.description" class="mb-6">
                            <p class="text-gray-600 dark:text-gray-400" x-text="recipe.description"></p>
                        </div>

                        <div class="mb-6 grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Expected Yield</p>
                                <p class="mt-1 text-xl font-bold text-brand-500"
                                    x-text="recipe.expected_yield + ' ' + (recipe.yield_unit || '')"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ingredients</p>
                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
                                    x-text="(recipe.versions?.[0]?.items?.length || 0)"></p>
                            </div>
                        </div>

                        <!-- Ingredients List -->
                        <div class="mb-6">
                            <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Ingredients</h4>
                            <div class="space-y-2">
                                <template x-for="ingredient in (recipe.versions?.[0]?.items || [])" :key="ingredient.id">
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

                        <!-- Instructions -->
                        <div x-show="recipe.instructions">
                            <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Instructions</h4>
                            <p class="whitespace-pre-line text-gray-600 dark:text-gray-400" x-text="recipe.instructions">
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                @php
                    $user = json_decode(json_encode(session('user')));
                    $userRole = $user->role->name ?? 'Guest';
                @endphp

                <!-- Action Buttons -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Actions</h3>
                    </div>

                    <div class="p-7 space-y-3">
                        <!-- Log Production Button -->
                        <a :href="'/production/create?recipe=' + recipe.id"
                            class="block w-full rounded-md bg-brand-500 px-4 py-3 text-center text-white hover:bg-brand-600">
                            Log Production
                        </a>

                        @if(in_array($userRole, ['Chef', 'Admin']))
                            <!-- Delete Button -->
                            <button @click="deleteRecipe"
                                class="w-full rounded-md bg-red-500 px-4 py-3 text-white hover:bg-red-600">
                                Delete Recipe
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Summary -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Summary</h3>
                    </div>

                    <div class="p-7 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white" x-text="recipe.section?.name"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Yield</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                x-text="recipe.expected_yield + ' ' + (recipe.yield_unit || '')"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white" x-text="formatDate(recipe.created_at)">
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function recipeDetailsData(recipeId) {
                return {
                    loading: true,
                    error: '',
                    recipe: {},

                    async init() {
                        await this.fetchRecipe();
                    },

                    async fetchRecipe() {
                        this.loading = true;
                        this.error = '';

                        try {
                            this.recipe = await API.get(`/recipes/${recipeId}`);
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load recipe details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async deleteRecipe() {
                        if (!confirm('Are you sure you want to delete this recipe? This action cannot be undone.'))
                            return;

                        try {
                            await API.delete(`/recipes/${recipeId}`);
                            window.location.href = '/recipes';
                        } catch (error) {
                            alert(error.message || 'Failed to delete recipe');
                        }
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