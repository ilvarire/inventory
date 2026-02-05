@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
    <div x-data="usersData()">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-title-md2 font-bold text-gray-900 dark:text-white">
                    System Users
                </h2>
            </div>
            @php
                $userRole = auth()->user()->role->name ?? 'Guest';
            @endphp
            @if($userRole === 'Admin')
                <div>
                    <a href="{{ route('users.create') }}"
                        class="inline-flex items-center justify-center gap-2.5 rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 lg:px-8 xl:px-10">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path
                                d="M10.0001 1.66669C10.4603 1.66669 10.8334 2.03978 10.8334 2.50002V9.16669H17.5001C17.9603 9.16669 18.3334 9.53978 18.3334 10C18.3334 10.4603 17.9603 10.8334 17.5001 10.8334H10.8334V17.5C10.8334 17.9603 10.4603 18.3334 10.0001 18.3334C9.53984 18.3334 9.16675 17.9603 9.16675 17.5V10.8334H2.50008C2.03984 10.8334 1.66675 10.4603 1.66675 10C1.66675 9.53978 2.03984 9.16669 2.50008 9.16669H9.16675V2.50002C9.16675 2.03978 9.53984 1.66669 10.0001 1.66669Z"
                                fill="" />
                        </svg>
                        Add User
                    </a>
                </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-3">
            <select x-model="filters.role"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Roles</option>
                <template x-for="role in roles" :key="role.id">
                    <option :value="role.id" x-text="role.name"></option>
                </template>
            </select>

            <select x-model="filters.status"
                class="rounded border border-gray-300 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button @click="fetchUsers" class="rounded-md bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">
                Apply Filters
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

        <!-- Users Table -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50 text-left dark:bg-gray-800">
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white xl:pl-11">Name</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Email</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Role</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Section</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Status</th>
                            <th class="px-4 py-4 font-medium text-gray-900 dark:text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="user in users" :key="user.id">
                            <tr class="border-t border-gray-200 dark:border-gray-800">
                                <td class="px-4 py-5 pl-9 xl:pl-11">
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="user.name"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="user.email"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                                            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300': user.role?.name === 'Admin',
                                                                            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': user.role?.name === 'Manager',
                                                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': user.role?.name === 'Chef',
                                                                            'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300': user.role?.name === 'Procurement',
                                                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': user.role?.name === 'Store Keeper',
                                                                            'bg-pink-100 text-pink-800 dark:bg-pink-900/20 dark:text-pink-300': user.role?.name === 'Frontline Sales'
                                                                        }" x-text="user.role?.name">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <p class="text-gray-900 dark:text-white" x-text="user.section?.name || 'N/A'"></p>
                                </td>
                                <td class="px-4 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="{
                                                                            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300': user.is_active,
                                                                            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300': !user.is_active
                                                                        }" x-text="user.is_active ? 'Active' : 'Inactive'">
                                    </span>
                                </td>
                                <td class="px-4 py-5">
                                    <div class="flex items-center gap-3">
                                        <a :href="'/users/' + user.id + '/edit'"
                                            class="text-brand-500 hover:text-brand-600">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="users.length === 0" class="border-t border-gray-200 dark:border-gray-800">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No users found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function usersData() {
                return {
                    loading: true,
                    error: '',
                    users: [],
                    roles: [],
                    pagination: {},
                    filters: {
                        role: '',
                        status: ''
                    },

                    async init() {
                        await this.fetchRoles();

                        this.$watch('filters', () => {
                            this.fetchUsers(1);
                        }, { deep: true });

                        await this.fetchUsers();
                    },

                    async fetchRoles() {
                        try {
                            const response = await API.get('/users/roles');
                            this.roles = response.data?.data || response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch roles:', error);
                        }
                    },

                    async fetchUsers(page = 1) {
                        this.loading = true;
                        this.error = '';

                        try {
                            const params = new URLSearchParams();
                            params.append('page', page);

                            if (this.filters.role) params.append('role_id', this.filters.role);

                            if (this.filters.status === 'active') params.append('is_active', 'true');
                            if (this.filters.status === 'inactive') params.append('is_active', 'false');

                            const response = await API.get('/users?' + params.toString());
                            this.users = response.data || [];
                            this.pagination = response;
                        } catch (error) {
                            console.error('Fetch error:', error);
                            this.error = error.message || 'Failed to load users';
                        } finally {
                            this.loading = false;
                        }
                    },

                    changePage(page) {
                        if (page < 1 || page > this.pagination.last_page) return;
                        this.fetchUsers(page);
                    },

                    async toggleStatus(user) {
                        const action = user.is_active ? 'deactivate' : 'activate';
                        if (!confirm(`Are you sure you want to ${action} ${user.name}?`)) return;

                        try {
                            await API.patch(`/users/${user.id}/toggle-status`);
                            await this.fetchUsers();
                        } catch (error) {
                            alert(error.message || 'Failed to update user status');
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection