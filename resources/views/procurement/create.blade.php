@extends('layouts.app')

@section('title', 'New Procurement')
@section('page-title', 'New Procurement')

@section('content')
    <div x-data="newProcurement()">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                Log New Procurement
            </h2>
            <a href="{{ route('procurement.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                Cancel
            </a>
        </div>

        <div class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <form @submit.prevent="submit">
                <!-- Header Info -->
                <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="form.supplier_id" required placeholder="Enter supplier name"
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Purchase Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" x-model="form.purchase_date" required
                            class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-6">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Items Received</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full table-auto text-left">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="min-w-[200px] px-4 py-3 font-medium text-gray-700 dark:text-gray-400">Raw
                                        Material</th>
                                    <th class="min-w-[120px] px-4 py-3 font-medium text-gray-700 dark:text-gray-400">
                                        Quantity</th>
                                    <th class="min-w-[150px] px-4 py-3 font-medium text-gray-700 dark:text-gray-400">Unit
                                        Cost (₦)</th>
                                    <th class="min-w-[150px] px-4 py-3 font-medium text-gray-700 dark:text-gray-400">Expiry
                                        Date</th>
                                    <th class="px-4 py-3 font-medium text-gray-700 dark:text-gray-400">Total</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in form.items" :key="index">
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-4 py-3">
                                            <select x-model="item.raw_material_id" required @change="updateUnit(index)"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                                <option value="">Select Material</option>
                                                <template x-for="material in materials" :key="material.id">
                                                    <option :value="material.id" x-text="material.name"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="relative">
                                                <input type="number" x-model="item.quantity" required step="0.01" min="0.01"
                                                    class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-12 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                                <span
                                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500"
                                                    x-text="getUnit(item.raw_material_id)"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" x-model="item.unit_cost" required step="0.01" min="0"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="date" x-model="item.expiry_date"
                                                class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium"
                                            x-text="formatCurrency(item.quantity * item.unit_cost)">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" @click="removeItem(index)"
                                                class="text-red-500 hover:text-red-700" :disabled="form.items.length === 1"
                                                :class="{ 'opacity-50 cursor-not-allowed': form.items.length === 1 }">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        Total Amount:</td>
                                    <td class="px-4 py-3 font-bold text-brand-600 dark:text-brand-400"
                                        x-text="formatCurrency(calculateTotal())"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <button type="button" @click="addItem()"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item
                    </button>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit" :disabled="loading"
                        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-8 py-2.5 text-center text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-300 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Save Procurement</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function newProcurement() {
                return {
                    loading: false,
                    materials: [],
                    form: {
                        supplier_id: '',
                        purchase_date: new Date().toISOString().split('T')[0],
                        items: [
                            { raw_material_id: '', quantity: '', unit_cost: '', expiry_date: '' }
                        ]
                    },

                    async init() {
                        await Promise.all([
                            this.fetchMaterials()
                        ]);
                    },

                    async fetchMaterials() {
                        try {
                            // Fetch all materials via inventory endpoint
                            const response = await API.get('/raw-materials');
                            this.materials = response.data || response;
                        } catch (error) {
                            console.error('Failed to load materials:', error);
                            showError('Failed to load raw materials');
                        }
                    },

                    addItem() {
                        this.form.items.push({
                            raw_material_id: '',
                            quantity: '',
                            unit_cost: '',
                            expiry_date: ''
                        });
                    },

                    removeItem(index) {
                        this.form.items.splice(index, 1);
                    },

                    getUnit(materialId) {
                        const material = this.materials.find(m => m.id == materialId);
                        return material ? material.unit : '';
                    },

                    updateUnit(index) {
                        // Can be used to fetch latest price history if needed
                    },

                    calculateTotal() {
                        return this.form.items.reduce((total, item) => {
                            return total + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0));
                        }, 0);
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    async submit() {
                        this.loading = true;
                        console.log('Submitting procurement:', this.form);
                        try {
                            const response = await API.post('/procurements', this.form);
                            console.log('Procurement created successfully:', response);
                            showSuccess('Procurement created successfully!');
                            // Wait a moment before redirecting to ensure user sees the success message
                            setTimeout(() => {
                                window.location.href = "{{ route('procurement.index') }}";
                            }, 1000);
                        } catch (error) {
                            console.error('Submission error:', error);
                            showError(error.message || 'Failed to create procurement');
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection