@extends('layouts.app')

@section('title', 'New Material Request')
@section('page-title', 'New Material Request')

@section('content')
    <div x-data="createRequestData()">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('material-requests.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Requests
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Request Materials
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitRequest">
                    <!-- Section Selection -->
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                            Section <span class="text-red-500">*</span>
                        </label>
                        <select x-model="formData.section_id" required
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 active:border-brand-500 disabled:cursor-default disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-brand-500">
                            <option value="">Select Section</option>
                            <template x-for="section in sections" :key="section.id">
                                <option :value="section.id" x-text="section.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Materials Section -->
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                            Materials <span class="text-red-500">*</span>
                        </label>

                        <div class="space-y-3">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <div class="flex gap-3">
                                    <!-- Material Select -->
                                    <div class="flex-1">
                                        <select x-model="item.raw_material_id" required
                                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="">Select Material</option>
                                            <template x-for="material in materials" :key="material.id">
                                                <option :value="material.id"
                                                    x-text="material.name + ' (' + material.unit + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="w-32">
                                        <input type="number" x-model="item.quantity" required min="0.01" step="0.01"
                                            placeholder="Qty"
                                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" @click="removeItem(index)" x-show="formData.items.length > 1"
                                        class="flex h-[50px] w-[50px] items-center justify-center rounded border border-red-300 text-red-500 hover:bg-red-50 dark:border-red-800 dark:hover:bg-red-900/20">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Add Item Button -->
                        <button type="button" @click="addItem"
                            class="mt-3 inline-flex items-center gap-2 text-sm text-brand-500 hover:text-brand-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Another Material
                        </button>
                    </div>

                    <!-- Notes -->
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                            Notes / Purpose
                        </label>
                        <textarea x-model="formData.notes" rows="4" placeholder="Enter the purpose of this request..."
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 active:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mb-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('material-requests.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Submit Request</span>
                            <span x-show="loading">Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function createRequestData() {
                return {
                    loading: false,
                    error: '',
                    sections: [],
                    materials: [],
                    formData: {
                        section_id: '',
                        items: [{
                            raw_material_id: '',
                            quantity: ''
                        }],
                        notes: ''
                    },

                    async init() {
                        await this.fetchSections();
                        await this.fetchMaterials();
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchMaterials() {
                        try {
                            const response = await API.get('/inventory');
                            this.materials = response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch materials:', error);
                        }
                    },

                    addItem() {
                        this.formData.items.push({
                            raw_material_id: '',
                            quantity: ''
                        });
                    },

                    removeItem(index) {
                        this.formData.items.splice(index, 1);
                    },

                    async submitRequest() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.post('/material-requests', this.formData);

                            // Redirect to the new request details page
                            window.location.href = '/material-requests/' + response.id;
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to submit request';
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection