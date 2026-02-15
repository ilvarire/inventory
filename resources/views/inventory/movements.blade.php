@extends('layouts.app')

@section('title', 'Material Movement History')
@section('page-title', 'Movement History')

@section('content')
    <div x-data="movementHistoryData({{ $id }})">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('inventory.show', $id) }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Material Details
            </a>
        </div>

        <!-- Material Info Card -->
        <div x-show="!loading && material.name"
            class="mb-6 rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="material.name"></h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                SKU: <span x-text="material.sku"></span> | Current Quantity: <span x-text="material.quantity"></span> <span
                    x-text="material.unit"></span>
            </p>
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

        <!-- Movement History Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="px-4 py-6 md:px-6 xl:px-7.5">
                <h4 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Movement History
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">
                                Date
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Type
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Quantity
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Balance After
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Reference
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                User
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Notes
                            </th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                    Actions
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="movements.length === 0">
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No movement history found
                                </td>
                            </tr>
                        </template>

                        <template x-for="movement in movements" :key="movement.id">
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="text-sm text-gray-900 dark:text-white"
                                        x-text="formatDate(movement.created_at)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="{
                                                                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': movement
                                                                        .type === 'in',
                                                                    'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': movement.type ===
                                                                        'out',
                                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': movement
                                                                        .type === 'adjustment'
                                                                }" x-text="movement.movement_type_label">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="font-medium" :class="{
                                                                    'text-green-600 dark:text-green-400': movement.type === 'in',
                                                                    'text-red-600 dark:text-red-400': movement.type === 'out',
                                                                    'text-blue-600 dark:text-blue-400': movement.type === 'adjustment'
                                                                }">
                                        <span x-text="movement.type === 'out' ? '-' : '+'"></span>
                                        <span x-text="movement.quantity"></span>
                                        <span x-text="material.unit"></span>
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <span x-text="movement.balance_after"></span>
                                        <span x-text="material.unit"></span>
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-900 dark:text-white"
                                        x-text="movement.reference_type || 'N/A'"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"
                                        x-text="movement.reference_id ? '#' + movement.reference_id : ''"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-900 dark:text-white"
                                        x-text="movement.user?.name || 'System'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400" x-text="movement.notes || '-'"></p>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-2">
                                            <button @click="openEditModal(movement)"
                                                class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                                title="Edit Movement">
                                                <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="confirmDelete(movement)"
                                                class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                                title="Delete Movement">
                                                <svg class="mr-1 h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="pagination.total > pagination.per_page"
                class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-800 sm:px-6">
                <div class="flex flex-1 justify-between sm:hidden">
                    <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Previous
                    </button>
                    <button @click="changePage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
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
                            <button @click="changePage(pagination.current_page - 1)"
                                :disabled="pagination.current_page === 1"
                                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button @click="changePage(pagination.current_page + 1)"
                                :disabled="pagination.current_page === pagination.last_page"
                                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 disabled:opacity-50 dark:ring-gray-700 dark:hover:bg-gray-800">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
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
    </div>

    @push('scripts')
        @if(auth()->user()->isAdmin())
            <!-- Edit Movement Modal -->
            <div x-show="editModal.open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50"
                @keydown.escape.window="editModal.open = false">
                <div @click.outside="editModal.open = false"
                    class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Movement</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Movement Type</label>
                            <select x-model="editModal.movement_type"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="procurement">Procurement</option>
                                <option value="issue_to_chef">Issued to Chef</option>
                                <option value="return_to_store">Return to Store</option>
                                <option value="waste">Waste</option>
                                <option value="sale">Sale</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                            <input type="number" step="0.01" min="0.01" x-model="editModal.quantity"
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">From Location</label>
                                <input type="text" x-model="editModal.from_location"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">To Location</label>
                                <input type="text" x-model="editModal.to_location"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <div x-show="editModal.error"
                        class="mt-3 rounded bg-red-50 p-2 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400"
                        x-text="editModal.error"></div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="editModal.open = false"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                        <button @click="saveMovement()" :disabled="editModal.saving"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:opacity-50">
                            <span x-show="!editModal.saving">Save Changes</span>
                            <span x-show="editModal.saving">Saving...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div x-show="deleteModal.open" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50"
                @keydown.escape.window="deleteModal.open = false">
                <div @click.outside="deleteModal.open = false"
                    class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Delete Movement</h3>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Are you sure you want to delete this movement? This will
                        reverse the stock effect and cannot be undone.</p>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                        <span x-text="deleteModal.movement?.movement_type_label"></span> —
                        <span x-text="deleteModal.movement?.quantity"></span> <span x-text="material.unit"></span>
                    </p>

                    <div x-show="deleteModal.error"
                        class="mt-3 rounded bg-red-50 p-2 text-sm text-red-600 dark:bg-red-900/20 dark:text-red-400"
                        x-text="deleteModal.error"></div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="deleteModal.open = false"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                        <button @click="deleteMovement()" :disabled="deleteModal.deleting"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                            <span x-show="!deleteModal.deleting">Delete</span>
                            <span x-show="deleteModal.deleting">Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <script>
            function movementHistoryData(materialId) {
                return {
                    loading: true,
                    error: '',
                    material: {},
                    movements: [],
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: 20,
                        total: 0,
                        from: 0,
                        to: 0
                    },
                    editModal: {
                        open: false,
                        saving: false,
                        error: '',
                        id: null,
                        movement_type: '',
                        quantity: 0,
                        from_location: '',
                        to_location: '',
                    },
                    deleteModal: {
                        open: false,
                        deleting: false,
                        error: '',
                        movement: null,
                    },

                    async init() {
                        await this.fetchMaterial();
                        await this.fetchMovements();
                    },

                    async fetchMaterial() {
                        try {
                            const response = await API.get(`/inventory/${materialId}`);

                            // Map the API response to the format expected by the view
                            this.material = {
                                id: response.material.id,
                                name: response.material.name,
                                sku: response.material.id, // Using ID as SKU
                                unit: response.material.unit,
                                quantity: response.current_stock
                            };
                        } catch (error) {
                            console.error('Material fetch error:', error);
                        }
                    },

                    async fetchMovements(page = 1) {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get(`/inventory/${materialId}/movements?page=${page}`);

                            // Helper to classify movement types
                            const classifyType = (movementType) => {
                                const inTypes = ['procurement', 'return_to_store'];
                                const outTypes = ['issue_to_chef', 'waste', 'sale'];
                                if (inTypes.includes(movementType)) return 'in';
                                if (outTypes.includes(movementType)) return 'out';
                                return 'adjustment';
                            };

                            // Helper to get a readable label from movement_type
                            const formatMovementType = (movementType) => {
                                const labels = {
                                    'procurement': 'Procurement',
                                    'issue_to_chef': 'Issued to Chef',
                                    'return_to_store': 'Return to Store',
                                    'waste': 'Waste',
                                    'sale': 'Sale',
                                    'adjustment': 'Adjustment',
                                    'prepared_to_manager': 'Prepared to Manager'
                                };
                                return labels[movementType] || movementType;
                            };

                            // Movements are ordered desc (newest first) from API
                            // Compute running balance: start from current stock and work backwards
                            const rawMovements = (response.data || []);
                            let runningBalance = this.material.quantity || 0;

                            // For the first page, runningBalance = current stock
                            // For subsequent pages, we'd need the balance from the previous page
                            // For simplicity, we compute balance_after per movement on the current page
                            // Since movements are desc, the first movement's balance_after = current stock
                            // Then we add back outgoing / subtract incoming as we go backwards
                            this.movements = rawMovements.map((movement, index) => {
                                const type = classifyType(movement.movement_type);
                                const qty = parseFloat(movement.quantity);

                                // For the first (newest) entry, balance_after = current stock
                                // For each subsequent (older) entry, reverse the effect of the previous movement
                                let balanceAfter;
                                if (index === 0) {
                                    balanceAfter = runningBalance;
                                } else {
                                    // Reverse the previous movement to get this movement's balance
                                    const prevMovement = rawMovements[index - 1];
                                    const prevType = classifyType(prevMovement.movement_type);
                                    const prevQty = parseFloat(prevMovement.quantity);

                                    if (prevType === 'in') {
                                        runningBalance -= prevQty; // undo the incoming
                                    } else if (prevType === 'out') {
                                        runningBalance += prevQty; // undo the outgoing
                                    }
                                    balanceAfter = runningBalance;
                                }

                                return {
                                    id: movement.id,
                                    type: type,
                                    movement_type_raw: movement.movement_type,
                                    movement_type_label: formatMovementType(movement.movement_type),
                                    quantity: qty,
                                    balance_after: parseFloat(balanceAfter).toFixed(2),
                                    reference_type: formatMovementType(movement.movement_type),
                                    reference_id: movement.reference_id,
                                    from_location: movement.from_location || '',
                                    to_location: movement.to_location || '',
                                    user: movement.performer,
                                    notes: movement.from_location && movement.to_location
                                        ? `${movement.from_location} → ${movement.to_location}`
                                        : (movement.from_location || movement.to_location || '-'),
                                    created_at: movement.created_at
                                };
                            });

                            this.pagination = {
                                current_page: response.current_page,
                                last_page: response.last_page,
                                per_page: response.per_page,
                                total: response.total,
                                from: response.from,
                                to: response.to
                            };
                        } catch (error) {
                            console.error('Movements fetch error:', error);
                            this.error = error.message || 'Failed to load movement history';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async changePage(page) {
                        if (page >= 1 && page <= this.pagination.last_page) {
                            await this.fetchMovements(page);
                        }
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },

                    openEditModal(movement) {
                        this.editModal = {
                            open: true,
                            saving: false,
                            error: '',
                            id: movement.id,
                            movement_type: movement.movement_type_raw,
                            quantity: movement.quantity,
                            from_location: movement.from_location,
                            to_location: movement.to_location,
                        };
                    },

                    async saveMovement() {
                        this.editModal.saving = true;
                        this.editModal.error = '';
                        try {
                            await API.put(`/inventory/movements/${this.editModal.id}`, {
                                quantity: parseFloat(this.editModal.quantity),
                                movement_type: this.editModal.movement_type,
                                from_location: this.editModal.from_location || null,
                                to_location: this.editModal.to_location || null,
                            });
                            this.editModal.open = false;
                            // Refresh data
                            await this.fetchMaterial();
                            await this.fetchMovements(this.pagination.current_page);
                        } catch (error) {
                            this.editModal.error = error.response?.data?.message || error.message || 'Failed to update movement';
                        } finally {
                            this.editModal.saving = false;
                        }
                    },

                    confirmDelete(movement) {
                        this.deleteModal = {
                            open: true,
                            deleting: false,
                            error: '',
                            movement: movement,
                        };
                    },

                    async deleteMovement() {
                        this.deleteModal.deleting = true;
                        this.deleteModal.error = '';
                        try {
                            await API.delete(`/inventory/movements/${this.deleteModal.movement.id}`);
                            this.deleteModal.open = false;
                            // Refresh data
                            await this.fetchMaterial();
                            await this.fetchMovements(this.pagination.current_page);
                        } catch (error) {
                            this.deleteModal.error = error.response?.data?.message || error.message || 'Failed to delete movement';
                        } finally {
                            this.deleteModal.deleting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection