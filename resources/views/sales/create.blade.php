@extends('layouts.app')

@section('title', 'Record Sale')
@section('page-title', 'Record Sale')

@section('content')
    <div x-data="recordSaleData()">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('sales.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Sales
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    New Sale
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitSale">
                    <div class="mb-3 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <!-- Section Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Section <span class="text-red-500">*</span>
                            </label>
                            @if(auth()->check() && auth()->user()->isSales())
                                <input type="text" :value="userSectionName" readonly
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

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-900 dark:text-white">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.payment_method" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Customer Name (Optional) -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Customer Name (Optional)
                        </label>
                        <input type="text" x-model="formData.customer_name" placeholder="Enter customer name"
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Products Section -->
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-900 dark:text-white">
                            Products <span class="text-red-500">*</span>
                        </label>

                        <div class="space-y-3">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <div class="flex gap-3">
                                    <!-- Product Select -->
                                    <div class="flex-1">
                                        <select x-model="item.recipe_id" @change="updatePrice(index)" required
                                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                            <option value="">Select Product</option>
                                            <template x-for="product in products" :key="product.id">
                                                <option :value="product.id" x-text="product.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="w-32">
                                        <input type="number" x-model="item.quantity" @input="calculateTotal" required
                                            min="1" step="1" placeholder="Qty"
                                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                    </div>

                                    <!-- Unit Price (Read-only) -->
                                    <div class="w-32">
                                        <input type="text" :value="formatCurrency(item.unit_price)" readonly
                                            placeholder="Price"
                                            class="w-full rounded border border-gray-300 bg-gray-100 px-5 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
                                    </div>

                                    <!-- Subtotal (Read-only) -->
                                    <div class="w-32">
                                        <input type="text" :value="formatCurrency(item.quantity * item.unit_price)" readonly
                                            placeholder="Total"
                                            class="w-full rounded border border-gray-300 bg-gray-100 px-5 py-3 text-gray-900 dark:border-gray-700 dark:bg-gray-700 dark:text-white" />
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
                            Add Another Product
                        </button>
                    </div>

                    <!-- Total Amount Display -->
                    <div class="mt-6 flex justify-end">
                        <div
                            class="w-full max-w-sm rounded-sm border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                    x-text="formatCurrency(totalAmount)"></span>
                            </div>
                            <div
                                class="flex items-center justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="text-xl font-bold text-brand-500" x-text="formatCurrency(totalAmount)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mt-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('sales.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading || totalAmount === 0"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Record Sale</span>
                            <span x-show="loading">Recording...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function recordSaleData() {
                return {
                    loading: false,
                    error: '',
                    sections: [],
                    products: [],
                    formData: {
                        section_id: '',
                        payment_method: '',
                        customer_name: '',
                        items: [{
                            prepared_inventory_id: '',
                            quantity: 1,
                            unit_price: 0
                        }]
                    },
                    totalAmount: 0,
                    userSectionName: '',

                    async init() {
                        await this.fetchSections();
                        await this.fetchProducts();

                        // Auto-set section for Sales users
                        @if(auth()->check() && auth()->user()->isSales())
                            this.formData.section_id = {{ auth()->user()->section_id ?? 'null' }};
                            this.userSectionName = '{{ auth()->user()->section->name ?? "N/A" }}';
                        @endif
                                                                            },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async fetchProducts() {
                        try {
                            const response = await API.get('/recipes');
                            this.products = response.data?.data || response.data || response || [];
                        } catch (error) {
                            console.error('Failed to fetch products:', error);
                        }
                    },

                    addItem() {
                        this.formData.items.push({
                            prepared_inventory_id: '',
                            quantity: 1,
                            unit_price: 0
                        });
                    },

                    removeItem(index) {
                        this.formData.items.splice(index, 1);
                        this.calculateTotal();
                    },

                    updatePrice(index) {
                        const item = this.formData.items[index];
                        const product = this.products.find(p => p.id == item.prepared_inventory_id);
                        if (product) {
                            // For now, use a default price or fetch from a pricing table
                            // Prepared inventory doesn't have selling_price, so we'll need to add it
                            item.unit_price = parseFloat(product.selling_price || 0);
                        } else {
                            item.unit_price = 0;
                        }
                        this.calculateTotal();
                    },

                    calculateTotal() {
                        this.totalAmount = this.formData.items.reduce((sum, item) => {
                            return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
                        }, 0);
                    },

                    async submitSale() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.post('/sales', this.formData);

                            // Redirect to the new sale details page
                            window.location.href = '/sales/' + response.id;
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to record sale';
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