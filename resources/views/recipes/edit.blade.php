@extends('layouts.app')

@section('title', 'Edit Recipe')
@section('page-title', 'Edit Recipe')

@section('content')
    <div x-data="editRecipeData({{ $id }})">
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

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Edit Recipe
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitRecipe">
                    <!-- Loading State -->
                    <div x-show="initialLoading" class="flex items-center justify-center py-12">
                        <div
                            class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
                        </div>
                    </div>

                    <div x-show="!initialLoading">
                        <!-- Recipe Name -->
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Recipe Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="formData.name" required placeholder="Enter recipe name"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>

                        <!-- Section & Yield -->
                        <div class="mb-5.5 grid grid-cols-1 gap-5.5 md:grid-cols-3">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Section <span class="text-red-500">*</span>
                                </label>
                                @if(auth()->user()->isChef())
                                    <input type="text" :value="userSectionName" readonly
                                        class="w-full rounded border border-gray-300 bg-gray-100 px-5 py-3 text-gray-900 outline-none cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                    <input type="hidden" x-model="formData.section_id" />
                                @else
                                    <select x-model="formData.section_id" required
                                        class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                        <option value="">Select Section</option>
                                        <template x-for="section in sections" :key="section.id">
                                            <option :value="section.id" x-text="section.name"></option>
                                        </template>
                                    </select>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Expected Yield <span class="text-red-500">*</span>
                                </label>
                                <input type="number" x-model="formData.expected_yield" required min="1" step="0.01"
                                    placeholder="e.g., 50"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Yield Unit <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="formData.yield_unit" required placeholder="e.g., pieces, kg"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Selling Price (₦) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" x-model="formData.selling_price" required min="0" step="0.01"
                                    placeholder="Enter selling price per unit"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>

                        <!-- Profitability Analysis -->
                        <div class="mb-5.5 rounded-sm border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50"
                            x-show="calculateProfit().totalCost > 0">
                            <h4 class="mb-3 font-medium text-gray-900 dark:text-white">Profitability Estimator</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Material Cost</p>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                        x-text="'₦' + calculateProfit().totalCost.toFixed(2)"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Cost Per Unit</p>
                                    <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                        x-text="'₦' + calculateProfit().costPerUnit.toFixed(2)"></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Estimated Profit/Loss</p>
                                    <p class="mt-1 font-bold"
                                        :class="calculateProfit().profit >= 0 ? 'text-green-500' : 'text-red-500'"
                                        x-text="'₦' + calculateProfit().profit.toFixed(2) + (calculateProfit().profit >= 0 ? ' (Profit)' : ' (Loss)')">
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Description
                            </label>
                            <textarea x-model="formData.description" rows="3" placeholder="Brief description of the recipe"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        </div>

                        <!-- Ingredients Section -->
                        <div class="mb-5.5">
                            <div class="mb-3 flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Ingredients <span class="text-red-500">*</span>
                                </label>
                                <button type="button" @click="addIngredient"
                                    class="text-sm text-brand-500 hover:text-brand-600">
                                    + Add Ingredient
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(ingredient, index) in formData.ingredients" :key="index">
                                    <div class="w-full flex flex-row gap-3 items-center justify-center">
                                        <div class="w-1/2">
                                            <select x-model="ingredient.raw_material_id" required
                                                class="w-full rounded border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Select Material</option>
                                                <template x-for="material in materials" :key="material.id">
                                                    <option :value="material.id" x-text="material.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="w-1/6">
                                            <input type="number" x-model="ingredient.quantity" required min="0.01"
                                                step="0.01" placeholder="Quantity"
                                                class="w-full rounded border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                        </div>
                                        <div class="w-1/6">
                                            <input type="text" :value="getUnit(ingredient.raw_material_id)" readonly
                                                placeholder="Unit"
                                                class="w-full rounded border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-900 outline-none cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                        </div>
                                        <div class="w-1/6 flex items-center">
                                            <button type="button" @click="removeIngredient(index)"
                                                x-show="formData.ingredients.length > 1"
                                                class="text-red-500 hover:text-red-600">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-5.5 mt-3">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Instructions
                            </label>
                            <textarea x-model="formData.instructions" rows="5"
                                placeholder="Step-by-step cooking instructions..."
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        </div>

                        <!-- Error Message -->
                        <div x-show="error"
                            class="mb-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                            <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-4">
                            <a href="{{ route('recipes.index') }}"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                Cancel
                            </a>
                            <button type="submit" :disabled="submitting"
                                class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">Update Recipe</span>
                                <span x-show="submitting">Updating...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editRecipeData(recipeId) {
                return {
                    initialLoading: true,
                    submitting: false,
                    error: '',
                    sections: [],
                    materials: [],
                    userSectionId: {{ auth()->user()->section_id ?? 'null' }},
                    userSectionName: '{{ auth()->user()->section->name ?? "" }}',
                    formData: {
                        name: '',
                        section_id: '',
                        description: '',
                        expected_yield: '',
                        yield_unit: '',
                        selling_price: '',
                        instructions: '',
                        ingredients: [{
                            raw_material_id: '',
                            quantity: ''
                        }]
                    },

                    async init() {
                        await Promise.all([
                            this.fetchSections(),
                            this.fetchMaterials(),
                            this.fetchRecipe()
                        ]);
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchMaterials() {
                        try {
                            const response = await API.get('/raw-materials-list');
                            this.materials = response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch materials:', error);
                        }
                    },

                    async fetchRecipe() {
                        try {
                            const recipe = await API.get(`/recipes/${recipeId}`);
                            this.formData.name = recipe.name;
                            this.formData.section_id = recipe.section_id;
                            this.formData.description = recipe.description;
                            this.formData.expected_yield = recipe.expected_yield;
                            this.formData.yield_unit = recipe.yield_unit;
                            this.formData.selling_price = recipe.selling_price;
                            this.formData.instructions = recipe.instructions;

                            // Load ingredients from latest version
                            if (recipe.versions && recipe.versions.length > 0 && recipe.versions[0].items) {
                                this.formData.ingredients = recipe.versions[0].items.map(item => ({
                                    raw_material_id: item.raw_material_id,
                                    quantity: item.quantity_required
                                }));
                            }

                            this.initialLoading = false;
                        } catch (error) {
                            console.error('Fetch recipe error:', error);
                            this.error = 'Failed to load recipe details';
                            this.initialLoading = false;
                        }
                    },

                    addIngredient() {
                        this.formData.ingredients.push({
                            raw_material_id: '',
                            quantity: ''
                        });
                    },

                    removeIngredient(index) {
                        this.formData.ingredients.splice(index, 1);
                    },

                    getUnit(materialId) {
                        if (!materialId) return '';
                        const material = this.materials.find(m => m.id == materialId);
                        return material ? material.unit : '';
                    },

                    async submitRecipe() {
                        this.submitting = true;
                        this.error = '';

                        try {
                            const response = await API.put(`/recipes/${recipeId}`, this.formData);
                            window.location.href = '/recipes/' + recipeId;
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to update recipe';
                            this.submitting = false;
                        }
                    },

                    getMaterialCost(materialId) {
                        const material = this.materials.find(m => m.id == materialId);
                        return material ? parseFloat(material.unit_cost || 0) : 0;
                    },

                    calculateProfit() {
                        let totalCost = 0;

                        this.formData.ingredients.forEach(ingredient => {
                            const qty = parseFloat(ingredient.quantity || 0);
                            const cost = this.getMaterialCost(ingredient.raw_material_id);
                            totalCost += qty * cost;
                        });

                        const yieldAmount = parseFloat(this.formData.expected_yield || 1);
                        const costPerUnit = totalCost / (yieldAmount || 1);
                        const sellingPrice = parseFloat(this.formData.selling_price || 0);
                        const profit = sellingPrice - costPerUnit;

                        return {
                            totalCost,
                            costPerUnit,
                            profit
                        };
                    }
                }
            }
        </script>
    @endpush
@endsection