@extends('layouts.app')

@section('title', 'Material Request Details')
@section('page-title', 'Material Request Details')

@section('content')
    <div x-data="requestDetailsData({{ $id }})">
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

        <!-- Request Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Request Info Card -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Request <span x-text="'#' + request.id"></span>
                            </h3>
                            <span :class="{
                                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': request.status === 'pending',
                                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': request.status === 'approved',
                                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': request.status === 'fulfilled',
                                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': request.status === 'rejected'
                                                            }"
                                class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize"
                                x-text="request.status">
                            </span>
                        </div>
                    </div>

                    <div class="p-7">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Requester</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="request.chef?.name || 'N/A'">
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="request.section?.name || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Request Date</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(request.created_at)"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Last Updated</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(request.updated_at)"></p>
                            </div>
                        </div>

                        <div x-show="request.notes" class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="request.notes"></p>
                        </div>

                        <div x-show="request.rejection_reason"
                            class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-red-500">Rejection Reason</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="request.rejection_reason"></p>
                        </div>
                    </div>
                </div>

                <!-- Materials List -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Requested Materials</h3>
                    </div>

                    <div class="p-7">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-800">
                                        <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Material</th>
                                        <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Quantity</th>
                                        <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">
                                            Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in request.items" :key="item.id">
                                        <tr class="border-b border-gray-200 dark:border-gray-800">
                                            <td class="py-3 text-gray-900 dark:text-white" x-text="item.raw_material?.name">
                                            </td>
                                            <td class="py-3 text-gray-900 dark:text-white" x-text="item.quantity"></td>
                                            <td class="py-3 text-gray-900 dark:text-white" x-text="item.raw_material?.unit">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- Action Buttons -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Actions</h3>
                    </div>

                    <div class="p-7 space-y-3">
                        <!-- Approve Button (Manager/Admin only, Pending status) -->
                        @if(auth()->check() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                            <button x-show="request.status === 'pending'" @click="approveRequest" :disabled="actionLoading"
                                class="w-full rounded-md bg-green-500 px-4 py-3 text-white hover:bg-green-600 disabled:opacity-50">
                                <span x-show="!actionLoading">Approve Request</span>
                                <span x-show="actionLoading">Processing...</span>
                            </button>

                            <button x-show="request.status === 'pending'" @click="showRejectModal = true"
                                :disabled="actionLoading"
                                class="w-full rounded-md bg-red-500 px-4 py-3 text-white hover:bg-red-600 disabled:opacity-50">
                                Reject Request
                            </button>
                        @endif

                        <!-- Fulfill Button (Manager/Admin only, Approved status) -->
                        @if(auth()->check() && (auth()->user()->isManager() || auth()->user()->isAdmin()))
                            <button x-show="request.status === 'approved'" @click="fulfillRequest" :disabled="actionLoading"
                                class="w-full rounded-md bg-brand-500 px-4 py-3 text-white hover:bg-brand-600 disabled:opacity-50">
                                <span x-show="!actionLoading">Mark as Fulfilled</span>
                                <span x-show="actionLoading">Processing...</span>
                            </button>
                        @endif

                        <!-- Print Button -->
                        <button @click="window.print()"
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Print Request
                        </button>
                    </div>
                </div>

                <!-- Timeline -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Timeline</h3>
                    </div>

                    <div class="p-7">
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Created</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatDate(request.created_at)"></p>
                                </div>
                            </div>

                            <div x-show="request.approved_at" class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                                    <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Approved</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatDate(request.approved_at)"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="'by ' + (request.approved_by_user?.name || 'N/A')"></p>
                                </div>
                            </div>

                            <div x-show="request.fulfilled_at" class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Fulfilled</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatDate(request.fulfilled_at)"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="'by ' + (request.fulfilled_by_user?.name || 'N/A')"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div x-show="showRejectModal" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showRejectModal = false">
            <div class="w-full max-w-md rounded-sm bg-white p-7 dark:bg-gray-900">
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Reject Request</h3>
                <div class="mb-5">
                    <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="rejectionReason" rows="4" required
                        class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showRejectModal = false"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Cancel
                    </button>
                    <button @click="rejectRequest" :disabled="!rejectionReason || actionLoading"
                        class="rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 disabled:opacity-50">
                        <span x-show="!actionLoading">Reject</span>
                        <span x-show="actionLoading">Rejecting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function requestDetailsData(requestId) {
                return {
                    loading: true,
                    error: '',
                    actionLoading: false,
                    request: {},
                    showRejectModal: false,
                    rejectionReason: '',

                    async init() {
                        await this.fetchRequest();
                    },

                    async fetchRequest() {
                        this.loading = true;
                        this.error = '';

                        try {
                            this.request = await API.get(`/material-requests/${requestId}`);
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load request details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async approveRequest() {
                        if (!confirm('Are you sure you want to approve this request?')) return;

                        this.actionLoading = true;
                        try {
                            await API.post(`/material-requests/${requestId}/approve`);
                            await this.fetchRequest();
                        } catch (error) {
                            alert(error.message || 'Failed to approve request');
                        } finally {
                            this.actionLoading = false;
                        }
                    },

                    async rejectRequest() {
                        this.actionLoading = true;
                        try {
                            await API.post(`/material-requests/${requestId}/reject`, {
                                rejection_reason: this.rejectionReason
                            });
                            this.showRejectModal = false;
                            this.rejectionReason = '';
                            await this.fetchRequest();
                        } catch (error) {
                            alert(error.message || 'Failed to reject request');
                        } finally {
                            this.actionLoading = false;
                        }
                    },

                    async fulfillRequest() {
                        if (!confirm('Are you sure you want to mark this request as fulfilled? This will deduct inventory.')) return;

                        this.actionLoading = true;
                        try {
                            await API.post(`/material-requests/${requestId}/fulfill`);
                            await this.fetchRequest();
                        } catch (error) {
                            alert(error.message || 'Failed to fulfill request');
                        } finally {
                            this.actionLoading = false;
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