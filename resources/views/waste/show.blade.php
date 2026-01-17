@extends('layouts.app')

@section('title', 'Waste Details')
@section('page-title', 'Waste Details')

@section('content')
    <div x-data="wasteDetailsData({{ $id }})">
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

        <!-- Waste Details -->
        <div x-show="!loading && !error" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Waste Info Card -->
                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-900 dark:text-white">
                                Waste Log <span x-text="'#' + waste.id"></span>
                            </h3>
                            <span :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': waste.status === 'pending',
                                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': waste.status === 'approved',
                                        'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': waste.status === 'rejected'
                                    }" class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize"
                                x-text="waste.status">
                            </span>
                        </div>
                    </div>

                    <div class="p-7">
                        <div class="grid grid-cols-2 gap-5">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Reported By</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white" x-text="waste.user?.name"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="waste.section?.name || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"
                                    x-text="formatDate(waste.waste_date)"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Reason</p>
                                <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize" :class="{
                                            'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300': waste.reason === 'spoilage',
                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': waste.reason === 'damage',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': waste.reason === 'expiry',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300': waste.reason === 'other'
                                        }" x-text="waste.reason">
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Material</p>
                            <p class="mt-1 text-lg font-medium text-gray-900 dark:text-white"
                                x-text="waste.raw_material?.name"></p>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-5">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Quantity Wasted</p>
                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white"
                                    x-text="waste.quantity + ' ' + (waste.raw_material?.unit || '')"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Waste Cost</p>
                                <p class="mt-1 text-xl font-bold text-red-500" x-text="formatCurrency(waste.cost)"></p>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="waste.notes"></p>
                        </div>

                        <div x-show="waste.rejection_reason"
                            class="mt-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                            <p class="text-sm text-red-500">Rejection Reason</p>
                            <p class="mt-1 text-gray-900 dark:text-white" x-text="waste.rejection_reason"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- Action Buttons -->
                @php
                    $user = json_decode(json_encode(session('user')));
                    $userRole = $user->role->name ?? 'Guest';
                @endphp

                <div
                    class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-900 dark:text-white">Actions</h3>
                    </div>

                    <div class="p-7 space-y-3">
                        <!-- Approve Button (Manager/Admin only, Pending status) -->
                        @if(in_array($userRole, ['Manager', 'Admin']))
                            <button x-show="waste.status === 'pending'" @click="approveWaste" :disabled="actionLoading"
                                class="w-full rounded-md bg-green-500 px-4 py-3 text-white hover:bg-green-600 disabled:opacity-50">
                                <span x-show="!actionLoading">Approve Waste</span>
                                <span x-show="actionLoading">Processing...</span>
                            </button>

                            <button x-show="waste.status === 'pending'" @click="showRejectModal = true"
                                :disabled="actionLoading"
                                class="w-full rounded-md bg-red-500 px-4 py-3 text-white hover:bg-red-600 disabled:opacity-50">
                                Reject Waste
                            </button>
                        @endif

                        <!-- Print Button -->
                        <button @click="window.print()"
                            class="w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Print Log
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
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                                    <svg class="h-4 w-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Reported</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatDate(waste.created_at)"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="'by ' + (waste.user?.name || 'N/A')"></p>
                                </div>
                            </div>

                            <div x-show="waste.approved_at" class="flex gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Approved</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="formatDate(waste.approved_at)"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"
                                        x-text="'by ' + (waste.approved_by_user?.name || 'N/A')"></p>
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
                <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Reject Waste Log</h3>
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
                    <button @click="rejectWaste" :disabled="!rejectionReason || actionLoading"
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
            function wasteDetailsData(wasteId) {
                return {
                    loading: true,
                    error: '',
                    actionLoading: false,
                    waste: {},
                    showRejectModal: false,
                    rejectionReason: '',

                    async init() {
                        await this.fetchWaste();
                    },

                    async fetchWaste() {
                        this.loading = true;
                        this.error = '';

                        try {
                            this.waste = await API.get(`/waste/${wasteId}`);
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load waste details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async approveWaste() {
                        if (!confirm('Are you sure you want to approve this waste log?')) return;

                        this.actionLoading = true;
                        try {
                            await API.post(`/waste/${wasteId}/approve`);
                            await this.fetchWaste();
                        } catch (error) {
                            alert(error.message || 'Failed to approve waste');
                        } finally {
                            this.actionLoading = false;
                        }
                    },

                    async rejectWaste() {
                        this.actionLoading = true;
                        try {
                            await API.post(`/waste/${wasteId}/reject`, {
                                rejection_reason: this.rejectionReason
                            });
                            this.showRejectModal = false;
                            this.rejectionReason = '';
                            await this.fetchWaste();
                        } catch (error) {
                            alert(error.message || 'Failed to reject waste');
                        } finally {
                            this.actionLoading = false;
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