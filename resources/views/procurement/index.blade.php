@extends('layouts.app')

@section('title', 'Procurement')
@section('page-title', 'Procurement Management')

@section('content')
    <div x-data="procurementData()">
        <!-- Header with Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white">
                    Procurements
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage supplier purchases and stock intake
                </p>
            </div>

            @if(auth()->user()->isProcurement())
                <a href="{{ route('procurement.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Procurement
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div
            class="mb-6 rounded-sm border border-gray-200 bg-white p-4 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Search
                    </label>
                    <input type="text" x-model="filters.search" @input.debounce.500ms="fetchProcurements()"
                        placeholder="Search reference or supplier..."
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white dark:placeholder:text-gray-500" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status
                    </label>
                    <select x-model="filters.status" @change="fetchProcurements()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="received">Received</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Date Range
                    </label>
                    <input type="date" x-model="filters.date" @change="fetchProcurements()"
                        class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:text-white" />
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

        <!-- Procurement Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">
                                Reference / Date
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Supplier
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Section
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Total Cost
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Status
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Created By
                            </th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="procurement in procurements" :key="procurement.id">
                            <tr
                                class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <h5 class="font-medium text-brand-500 dark:text-brand-400">
                                        <a :href="`/procurement/${procurement.id}`"
                                            x-text="procurement.reference_number"></a>
                                    </h5>
                                    <p class="text-sm text-gray-500" x-text="formatDate(procurement.purchase_date)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white font-medium"
                                        x-text="procurement.supplier_id || 'N/A'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="procurement.section?.name || 'N/A'">
                                    </p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white font-medium"
                                        x-text="formatCurrency(procurement.total_cost)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                                                                                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': procurement.status === 'received',
                                                                                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': procurement.status === 'pending'
                                                                                                                }"
                                        x-text="capitalize(procurement.status)"></span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"
                                        x-text="procurement.user?.name || 'Unknown'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="`/procurement/${procurement.id}`"
                                            class="text-brand-500 hover:text-brand-600" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <!-- Approval buttons for pending procurements (Store Keeper/Admin only) -->
                                        @if(auth()->user()->isStoreKeeper() || auth()->user()->isAdmin())
                                            <template x-if="procurement.status === 'pending'">
                                                <div class="flex items-center gap-2">
                                                    <button @click="approveProcurement(procurement)"
                                                        :disabled="processingId === procurement.id"
                                                        class="text-green-600 hover:text-green-700 dark:text-green-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                                        title="Approve">
                                                        <svg x-show="processingId !== procurement.id" class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <svg x-show="processingId === procurement.id" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </button>
                                                    <button @click="rejectProcurement(procurement)"
                                                        :disabled="processingId === procurement.id"
                                                        class="text-red-600 hover:text-red-700 dark:text-red-400 disabled:opacity-50 disabled:cursor-not-allowed"
                                                        title="Reject">
                                                        <svg x-show="processingId !== procurement.id" class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        <svg x-show="processingId === procurement.id" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        @endif

                                        <!-- Delete button (Admin only) -->
                                        @if(auth()->user()->isAdmin())
                                            <button @click="deleteProcurement(procurement)"
                                                class="text-red-500 hover:text-red-600" title="Delete">
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
                        <tr x-show="procurements.length === 0 && !loading">
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                    <p class="text-gray-600 dark:text-gray-400">No procurements found</p>
                                    <a href="{{ route('procurement.create') }}"
                                        class="mt-2 text-brand-500 hover:underline">Create your
                                        first procurement</a>
                                </div>
                            </td>
                        </tr>
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

                            <!-- Page number display -->
                            <span
                                class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 dark:bg-gray-800 dark:text-white dark:ring-gray-700">
                                Page <span x-text="pagination.current_page"></span> of <span
                                    x-text="pagination.last_page"></span>
                            </span>

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
            function procurementData() {
                return {
                    loading: true,
                    error: '',
                    processingId: null,
                    procurements: [],
                    pagination: {
                        current_page: 1,
                        last_page: 1,
                        per_page: 15,
                        total: 0,
                        from: 0,
                        to: 0
                    },
                    filters: {
                        search: '',
                        status: '',
                        date: ''
                    },

                    async init() {
                        await this.fetchProcurements();
                    },

                    async fetchProcurements(page = 1) {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = { page };
                            if (this.filters.search) params.search = this.filters.search;
                            if (this.filters.status) params.status = this.filters.status;
                            if (this.filters.date) params.date = this.filters.date;

                            console.log('Fetching procurements with params:', params);
                            const response = await API.get('/procurements', params);
                            console.log('Procurements response:', response);

                            // Handle paginated response
                            this.procurements = response.data || [];
                            this.pagination = {
                                current_page: response.current_page || 1,
                                last_page: response.last_page || 1,
                                per_page: response.per_page || 15,
                                total: response.total || 0,
                                from: response.from || 0,
                                to: response.to || 0
                            };

                            console.log('Procurements array:', this.procurements);
                            console.log('Pagination:', this.pagination);
                        } catch (error) {
                            console.error('Procurement fetch error:', error);
                            this.error = error.message || 'Failed to load procurements';
                            showError(this.error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    async changePage(page) {
                        if (page >= 1 && page <= this.pagination.last_page) {
                            await this.fetchProcurements(page);
                        }
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';

                        // Check if it's a YYYY-MM-DD string
                        if (typeof dateString === 'string' && dateString.length === 10 && dateString.includes('-')) {
                            const parts = dateString.split('-');
                            const year = parseInt(parts[0]);
                            const month = parseInt(parts[1]) - 1;
                            const day = parseInt(parts[2]);
                            const date = new Date(year, month, day);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric'
                            });
                        }

                        return new Date(dateString).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    },

                    async approveProcurement(procurement) {
                        if (!confirm(`Approve procurement ${procurement.reference_number}?`)) return;

                        this.processingId = procurement.id;
                        try {
                            const response = await API.post(`/procurements/${procurement.id}/approve`);
                            showSuccess(response.message || 'Procurement approved successfully');
                            await this.fetchProcurements(this.pagination.current_page);
                        } catch (error) {
                            console.error('Approval error:', error);
                            showError(error.message || 'Failed to approve procurement');
                        } finally {
                            this.processingId = null;
                        }
                    },

                    async rejectProcurement(procurement) {
                        const reason = prompt(`Reject procurement ${procurement.reference_number}?\n\nPlease provide a reason:`);
                        if (!reason || reason.trim() === '') return;

                        this.processingId = procurement.id;
                        try {
                            const response = await API.post(`/procurements/${procurement.id}/reject`, {
                                rejection_reason: reason.trim()
                            });
                            showSuccess(response.message || 'Procurement rejected');
                            await this.fetchProcurements(this.pagination.current_page);
                        } catch (error) {
                            console.error('Rejection error:', error);
                            showError(error.message || 'Failed to reject procurement');
                        } finally {
                            this.processingId = null;
                        }
                    },

                    async deleteProcurement(procurement) {
                        if (!confirm(`Are you sure you want to delete procurement ${procurement.reference_number}? This action cannot be undone and will revert inventory if received.`)) return;

                        try {
                            const response = await API.delete(`/procurements/${procurement.id}`);
                            showSuccess(response.message || 'Procurement deleted successfully');
                            await this.fetchProcurements(this.pagination.current_page);
                        } catch (error) {
                            console.error('Delete error:', error);
                            showError(error.message || 'Failed to delete procurement');
                        }
                    },

                    capitalize(string) {
                        if (!string) return '';
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    }
                }
            }
        </script>
    @endpush
@endsection