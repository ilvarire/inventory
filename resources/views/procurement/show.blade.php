@extends('layouts.app')

@section('title', 'Procurement Details')
@section('page-title', 'Procurement Details')

@section('content')
    <div x-data="procurementDetails({{ $id }})">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('procurement.index') }}"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h2 class="text-title-md2 font-semibold text-gray-900 dark:text-white"
                        x-text="procurement?.reference_number || 'Loading...'">
                    </h2>
                    <span x-show="procurement" class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': procurement?.status === 'completed',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400': procurement?.status === 'pending',
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': procurement?.status === 'cancelled'
                                            }" x-text="capitalize(procurement?.status)"></span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 ml-7">
                    Created on <span x-text="formatDate(procurement?.created_at)"></span> by <span
                        x-text="procurement?.user?.name"></span>
                </p>
            </div>

            <div class="flex gap-2">
                @if(auth()->user()->isStoreKeeper() || auth()->user()->isAdmin())
                    <template x-if="procurement?.status === 'pending'">
                        <div class="flex gap-2">
                            <button @click="approveProcurement()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white dark:text-black shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Approve
                            </button>
                            <button @click="rejectProcurement()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white dark:text-black shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reject
                            </button>
                        </div>
                    </template>
                @endif

                <button onclick="window.print()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
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

        <!-- Content -->
        <div x-show="!loading && !error && procurement" class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <!-- Main Info -->
            <div class="xl:col-span-6 flex flex-col gap-6">
                <!-- Items Table -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white">Procurement Items</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50 text-left dark:bg-gray-800">
                                    <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">Raw Material</th>
                                    <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">Quantity</th>
                                    <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">Unit Cost</th>
                                    <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">Total</th>
                                    <th class="px-6 py-4 font-medium text-gray-900 dark:text-white">Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="item in procurement?.items" :key="item.id">
                                    <tr
                                        class="border-b border-gray-200 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-gray-900 dark:text-white"
                                                x-text="item.raw_material?.name"></p>
                                            <p class="text-xs text-gray-500" x-show="item.quality_note"
                                                x-text="'Quality: ' + item.quality_note"></p>
                                            <p class="text-xs text-gray-500" x-show="item.notes"
                                                x-text="'Note: ' + item.notes"></p>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                            <span x-text="item.quantity"></span> <span x-text="item.raw_material?.unit"
                                                class="text-xs text-gray-500"></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300"
                                            x-text="formatCurrency(item.unit_cost)"></td>
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"
                                            x-text="formatCurrency(item.quantity * item.unit_cost)"></td>
                                        <td class="px-6 py-4 text-sm" :class="getExpiryClass(item.expiry_date)"
                                            x-text="formatDate(item.expiry_date)"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                        Total Cost:</td>
                                    <td class="px-6 py-4 font-bold text-brand-600 dark:text-brand-400"
                                        x-text="formatCurrency(procurement?.total_cost)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="xl:col-span-1 flex flex-col gap-6">
                <!-- Supplier Info -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white">Supplier Details</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="h-10 w-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold dark:bg-brand-900/20 dark:text-brand-400">
                                <span x-text="(procurement?.supplier?.name || 'S').charAt(0)"></span>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white" x-text="procurement?.supplier?.name">
                                </h4>
                                <p class="text-sm text-gray-500"
                                    x-text="procurement?.supplier?.contact_info || 'No contact info'"></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                <span class="text-sm text-gray-500">Purchase Date</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(procurement?.purchase_date)"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                <span class="text-sm text-gray-500">Section</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                    x-text="procurement?.section?.name || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                                <span class="text-sm text-gray-500">Reference</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white"
                                    x-text="procurement?.reference_number"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            function procurementDetails(id) {
                return {
                    loading: true,
                    error: '',
                    procurement: null,

                    async init() {
                        await this.fetchProcurement();
                    },

                    async fetchProcurement() {
                        this.loading = true;
                        try {
                            const response = await API.get(`/procurements/${id}`);
                            this.procurement = response.data || response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load procurement details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async approveProcurement() {
                        if (!confirm(`Approve procurement ${this.procurement.reference_number}?`)) return;

                        try {
                            const response = await API.post(`/procurements/${this.procurement.id}/approve`);
                            showSuccess(response.message || 'Procurement approved successfully');
                            await this.fetchProcurement();
                        } catch (error) {
                            console.error('Approval error:', error);
                            showError(error.message || 'Failed to approve procurement');
                        }
                    },

                    async rejectProcurement() {
                        const reason = prompt(`Reject procurement ${this.procurement.reference_number}?\n\nPlease provide a reason:`);
                        if (!reason || reason.trim() === '') return;

                        try {
                            const response = await API.post(`/procurements/${this.procurement.id}/reject`, {
                                rejection_reason: reason.trim()
                            });
                            showSuccess(response.message || 'Procurement rejected');
                            await this.fetchProcurement();
                        } catch (error) {
                            console.error('Rejection error:', error);
                            showError(error.message || 'Failed to reject procurement');
                        }
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    },

                    formatDate(date) {
                        if (!date) return 'N/A';
                        return new Date(date).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    },

                    capitalize(string) {
                        if (!string) return '';
                        return string.charAt(0).toUpperCase() + string.slice(1);
                    },

                    getExpiryClass(date) {
                        if (!date) return '';
                        const today = new Date();
                        const expiry = new Date(date);
                        const diffTime = expiry - today;
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                        if (diffDays < 0) return 'text-red-600 font-bold';
                        if (diffDays <= 7) return 'text-orange-500 font-medium';
                        return 'text-green-600';
                    }
                }
            }
        </script>
    @endpush
@endsection