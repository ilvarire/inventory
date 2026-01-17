@extends('layouts.app')

@section('title', 'Material Requests')
@section('page-title', 'Material Requests')

@section('content')
    <div x-data="materialRequestsData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    Material Requests
                </h2>
            </div>
            @php
                $user = json_decode(json_encode(session('user')));
                $userRole = $user->role->name ?? 'Guest';
            @endphp
            @if(in_array($userRole, ['Chef', 'Admin']))
                <div>
                    <a href="{{ route('material-requests.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        New Request
                    </a>
                </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <button @click="filterStatus = 'all'"
                :class="filterStatus === 'all' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                class="rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-gray-800">
                All
            </button>
            <button @click="filterStatus = 'pending'"
                :class="filterStatus === 'pending' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                class="rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-gray-800">
                Pending
            </button>
            <button @click="filterStatus = 'approved'"
                :class="filterStatus === 'approved' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                class="rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-gray-800">
                Approved
            </button>
            <button @click="filterStatus = 'fulfilled'"
                :class="filterStatus === 'fulfilled' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                class="rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-gray-800">
                Fulfilled
            </button>
            <button @click="filterStatus = 'rejected'"
                :class="filterStatus === 'rejected' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'"
                class="rounded-md border border-gray-200 px-4 py-2 text-sm font-medium transition hover:bg-brand-50 dark:border-gray-800 dark:hover:bg-gray-800">
                Rejected
            </button>
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

        <!-- Requests Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">Request ID</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Requester</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Section</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Items</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Date</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Status</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="request in filteredRequests" :key="request.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="'#' + request.id"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="request.user?.name"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="request.section?.name || 'N/A'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="request.items?.length + ' items'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="formatDate(request.created_at)"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': request.status === 'pending',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': request.status === 'approved',
                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': request.status === 'fulfilled',
                                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': request.status === 'rejected'
                                            }" class="inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize"
                                        x-text="request.status">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="'/material-requests/' + request.id"
                                            class="text-brand-500 hover:text-brand-600">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredRequests.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No material requests found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function materialRequestsData() {
                return {
                    loading: true,
                    error: '',
                    requests: [],
                    filterStatus: 'all',

                    async init() {
                        await this.fetchRequests();
                    },

                    async fetchRequests() {
                        this.loading = true;
                        this.error = '';

                        try {
                            const response = await API.get('/material-requests');
                            this.requests = response.data || [];
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load material requests';
                        } finally {
                            this.loading = false;
                        }
                    },

                    get filteredRequests() {
                        if (this.filterStatus === 'all') {
                            return this.requests;
                        }
                        return this.requests.filter(req => req.status === this.filterStatus);
                    },

                    formatDate(dateString) {
                        if (!dateString) return 'N/A';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection