@extends('layouts.app')

@section('title', 'Report Waste')
@section('page-title', 'Report Waste')

@section('content')
    <div x-data="reportWasteData()">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('waste.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Waste Logs
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Report Waste
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitWaste">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <!-- Section Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Section <span class="text-red-500">*</span>
                            </label>
                            @if(auth()->check() && auth()->user()->isChef())
                                <input type="text" value="{{ auth()->user()->section->name ?? 'N/A' }}" readonly
                                    class="w-full rounded border border-gray-300 bg-gray-100 px-5 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
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

                        <!-- Waste Reason -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Waste Reason <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.reason" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Reason</option>
                                <option value="spoilage">Spoilage</option>
                                <option value="damage">Damage</option>
                                <option value="expiry">Expiry</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    @php
                        $userRole = auth()->user()->role->name ?? 'Guest';
                    @endphp

                    @if(in_array($userRole, ['Procurement', 'Store Keeper', 'Admin', 'Manager']))
                        <!-- Raw Material Selection (for Procurement/Store Keeper) -->
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Raw Material <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.raw_material_id" @change="updateCost" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Material</option>
                                <template x-for="material in materials" :key="material.id">
                                    <option :value="material.id"
                                        x-text="material.name + ' (' + material.unit + ') - ₦' + parseFloat(material.unit_cost || 0).toFixed(2)">
                                    </option>
                                </template>
                            </select>
                        </div>
                    @endif

                    @if($userRole === 'Chef')
                        <!-- Prepared Inventory Selection (for Chef) -->
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Prepared Item <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.prepared_inventory_id" @change="updateCost" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Prepared Item</option>
                                <template x-for="item in preparedItems" :key="item.id">
                                    <option :value="item.id"
                                        x-text="item.item_name + ' (' + item.quantity + ' ' + item.unit + ') - ₦' + parseFloat(item.selling_price || 0).toFixed(2)">
                                    </option>
                                </template>
                            </select>
                        </div>
                    @endif

                    <!-- Quantity -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Quantity Wasted <span class="text-red-500">*</span>
                        </label>
                        <input type="number" x-model="formData.quantity" @input="updateCost" required min="0.01" step="0.01"
                            placeholder="Enter quantity"
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Estimated Cost (Read-only) -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Estimated Cost
                        </label>
                        <input type="text" :value="formatCurrency(estimatedCost)" readonly
                            class="w-full rounded border border-gray-300 bg-gray-100 px-5 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
                    </div>

                    <!-- Notes -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Notes / Explanation <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="formData.notes" rows="4" required
                            placeholder="Explain what happened and why the waste occurred..."
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mt-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('waste.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Submit Report</span>
                            <span x-show="loading">Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function reportWasteData() {
                return {
                    loading: false,
                    error: '',
                    sections: [],
                    materials: [],
                    preparedItems: [],
                    formData: {
                        section_id: '',
                        raw_material_id: '',
                        prepared_inventory_id: '',
                        quantity: '',
                        reason: '',
                        notes: ''
                    },
                    estimatedCost: 0,

                    async init() {
                        @if(auth()->check() && auth()->user()->isChef())
                            // Auto-set section for Chef users
                            this.formData.section_id = {{ auth()->user()->section_id ?? 'null' }};
                            await this.fetchPreparedItems();
                        @else
                                                    await this.fetchSections();
                            await this.fetchMaterials();
                        @endif
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
                            const response = await API.get('/raw-materials');
                            this.materials = response.data?.data || response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch materials:', error);
                        }
                    },

                    async fetchPreparedItems() {
                        try {
                            const response = await API.get('/prepared-inventory');
                            this.preparedItems = response.data?.data || response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch prepared items:', error);
                        }
                    },

                    updateCost() {
                        // For raw materials
                        if (this.formData.raw_material_id) {
                            const material = this.materials.find(m => m.id == this.formData.raw_material_id);
                            if (material && this.formData.quantity) {
                                this.estimatedCost = parseFloat(material.unit_cost || 0) * parseFloat(this.formData.quantity);
                            } else {
                                this.estimatedCost = 0;
                            }
                        }
                        // For prepared items
                        else if (this.formData.prepared_inventory_id) {
                            const item = this.preparedItems.find(i => i.id == this.formData.prepared_inventory_id);
                            if (item && this.formData.quantity) {
                                this.estimatedCost = parseFloat(item.selling_price || 0) * parseFloat(this.formData.quantity);
                            } else {
                                this.estimatedCost = 0;
                            }
                        } else {
                            this.estimatedCost = 0;
                        }
                    },

                    async submitWaste() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.post('/waste', this.formData);

                            // Redirect to the new waste log details page
                            const wasteId = response.data?.id || response.id;
                            window.location.href = '/waste/' + wasteId;
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to submit waste report';
                            this.loading = false;
                        }
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toFixed(2);
                    }
                }
            }
        </script>
    @endpush
@endsection