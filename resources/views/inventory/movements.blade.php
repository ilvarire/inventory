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
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="movements.length === 0">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
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
                                                        }" x-text="movement.type.toUpperCase()">
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

                            // Map the movements data to the format expected by the view
                            this.movements = (response.data || []).map(movement => ({
                                id: movement.id,
                                type: movement.movement_type, // Map movement_type to type
                                quantity: movement.quantity,
                                balance_after: movement.balance_after,
                                reference_type: movement.reference_type,
                                reference_id: movement.reference_id,
                                user: movement.performer, // Map performer to user
                                notes: movement.notes,
                                created_at: movement.created_at
                            }));

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
                    }
                }
            }
        </script>
    @endpush
@endsection