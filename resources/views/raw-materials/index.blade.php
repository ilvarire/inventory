@extends('layouts.app')

@section('title', 'Raw Materials')
@section('page-title', 'Raw Materials Management')

@section('content')
    <div x-data="rawMaterialsData()">
        <!-- Header with Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Raw Materials
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage all raw materials in the system
                </p>
            </div>

            @can('create', App\Models\RawMaterial::class)
                <button @click="openCreateModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Material
                </button>
            @endcan
        </div>

        <!-- Filters -->
        <div
            class="mb-6 rounded-sm border border-gray-200 bg-white p-4 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Search
                    </label>
                    <input type="text" x-model="filters.search" @input.debounce.500ms="fetchMaterials()"
                        placeholder="Search by name..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:placeholder:text-gray-500" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Category
                    </label>
                    <select x-model="filters.category" @change="fetchMaterials()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="">All Categories</option>
                        <template x-for="category in categories" :key="category">
                            <option :value="category" x-text="category"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Sort By
                    </label>
                    <select x-model="filters.sort_by" @change="fetchMaterials()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="name">Name</option>
                        <option value="category">Category</option>
                        <option value="created_at">Date Added</option>
                    </select>
                </div>
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

        <!-- Materials Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="px-4 py-6 md:px-6 xl:px-7.5">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Materials List (<span x-text="materials.length"></span>)
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">
                                Name
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Category
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Unit
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Min Qty
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Reorder Qty
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Supplier
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="material in materials" :key="material.id">
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <h5 class="font-medium text-gray-900 dark:text-white" x-text="material.name"></h5>
                                </td>
                                <td class="px-4 py-5">
                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200"
                                        x-text="material.category || 'N/A'"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="material.unit"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="material.min_quantity"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="material.reorder_quantity"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                        x-text="material.preferred_supplier_id || 'No supplier'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        @if(in_array(auth()->user()->role->name, ['Admin', 'Manager', 'Store Keeper']))
                                            <button @click="openEditModal(material)" class="text-brand-500 hover:text-brand-600"
                                                title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        @endif

                                        @if(in_array(auth()->user()->role->name, ['Admin', 'Manager']))
                                            <button @click="confirmDelete(material)" class="text-red-500 hover:text-red-600"
                                                title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr x-show="materials.length === 0 && !loading">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No raw materials found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div x-show="showModal" x-cloak @click.self="closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900" @click.stop>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="modalTitle"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm()">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.name" required
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                            <p x-show="formErrors.name" class="mt-1 text-sm text-red-600" x-text="formErrors.name"></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Unit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="form.unit" required placeholder="e.g., kg, liter, piece, dozen"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Common units: kg, liter, piece, gram,
                                ml, dozen, box, bag</p>
                            <p x-show="formErrors.unit" class="mt-1 text-sm text-red-600" x-text="formErrors.unit"></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Category
                            </label>
                            <input type="text" x-model="form.category"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Minimum Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" x-model="form.min_quantity" required min="0"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                            <p x-show="formErrors.min_quantity" class="mt-1 text-sm text-red-600"
                                x-text="formErrors.min_quantity"></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Reorder Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" x-model="form.reorder_quantity" required min="0"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                            <p x-show="formErrors.reorder_quantity" class="mt-1 text-sm text-red-600"
                                x-text="formErrors.reorder_quantity"></p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Preferred Supplier
                            </label>
                            <input type="text" x-model="form.preferred_supplier_id"
                                placeholder="e.g., Fresh Farms Ltd, Ocean Catch Seafood"
                                class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter the name of your preferred
                                supplier</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeModal()"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50">
                            <span x-show="!submitting" x-text="editingId ? 'Update' : 'Create'"></span>
                            <span x-show="submitting">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function rawMaterialsData() {
                return {
                    loading: true,
                    error: '',
                    materials: [],
                    categories: [],
                    filters: {
                        search: '',
                        category: '',
                        sort_by: 'name'
                    },
                    showModal: false,
                    editingId: null,
                    modalTitle: '',
                    submitting: false,
                    form: {
                        name: '',
                        unit: '',
                        category: '',
                        min_quantity: '',
                        reorder_quantity: '',
                        preferred_supplier_id: ''
                    },
                    formErrors: {},

                    async init() {
                        await Promise.all([
                            this.fetchMaterials(),
                            this.fetchCategories()
                        ]);
                    },

                    async fetchMaterials() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = {};
                            if (this.filters.search) params.search = this.filters.search;
                            if (this.filters.category) params.category = this.filters.category;
                            if (this.filters.sort_by) params.sort_by = this.filters.sort_by;

                            const response = await API.get('/raw-materials', params);
                            this.materials = response.data || response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load raw materials';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async fetchCategories() {
                        try {
                            this.categories = await API.get('/raw-materials/categories');
                        } catch (error) {
                            console.error('Categories fetch error:', error);
                        }
                    },

                    openCreateModal() {
                        this.editingId = null;
                        this.modalTitle = 'Add New Raw Material';
                        this.resetForm();
                        this.showModal = true;
                    },

                    openEditModal(material) {
                        this.editingId = material.id;
                        this.modalTitle = 'Edit Raw Material';
                        this.form = {
                            name: material.name,
                            unit: material.unit,
                            category: material.category || '',
                            min_quantity: material.min_quantity,
                            reorder_quantity: material.reorder_quantity,
                            preferred_supplier_id: material.preferred_supplier_id || ''
                        };
                        this.formErrors = {};
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        this.resetForm();
                    },

                    resetForm() {
                        this.form = {
                            name: '',
                            unit: '',
                            category: '',
                            min_quantity: '',
                            reorder_quantity: '',
                            preferred_supplier_id: ''
                        };
                        this.formErrors = {};
                    },

                    async submitForm() {
                        this.submitting = true;
                        this.formErrors = {};

                        try {
                            if (this.editingId) {
                                await API.put(`/raw-materials/${this.editingId}`, this.form);
                                showSuccess('Raw material updated successfully');
                            } else {
                                await API.post('/raw-materials', this.form);
                                showSuccess('Raw material created successfully');
                            }

                            this.closeModal();
                            await this.fetchMaterials();
                            await this.fetchCategories();
                        } catch (error) {
                            console.error('Submit error:', error);
                            if (error.errors) {
                                this.formErrors = error.errors;
                            } else {
                                showError(error.message || 'Failed to save raw material');
                            }
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async confirmDelete(material) {
                        if (!confirm(`Are you sure you want to delete "${material.name}"? This action cannot be undone.`)) {
                            return;
                        }

                        try {
                            await API.delete(`/raw-materials/${material.id}`);
                            showSuccess('Raw material deleted successfully');
                            await this.fetchMaterials();
                            await this.fetchCategories();
                        } catch (error) {
                            console.error('Delete error:', error);
                            showError(error.error || error.message || 'Failed to delete raw material');
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection