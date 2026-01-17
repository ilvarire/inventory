@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div x-data="editUserData({{ $id }})">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('users.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Users
            </a>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
            </div>
        </div>

        <!-- Form Card -->
        <div x-show="!loading"
            class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Edit User
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitUpdate">
                    <div class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" x-model="formData.name" required placeholder="Enter full name"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" x-model="formData.email" required placeholder="user@example.com"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                    </div>

                    <div class="mt-5.5 grid grid-cols-1 gap-5.5 md:grid-cols-2">
                        <!-- Role -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select x-model="formData.role_id" required
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Role</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="role.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Section (Optional)
                            </label>
                            <select x-model="formData.section_id"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">No Section</option>
                                <template x-for="section in sections" :key="section.id">
                                    <option :value="section.id" x-text="section.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Password Reset Section -->
                    <div class="mt-5.5">
                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" x-model="resetPassword" id="resetPassword"
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            <label for="resetPassword" class="text-sm font-medium text-gray-900 dark:text-white">
                                Reset Password
                            </label>
                        </div>

                        <div x-show="resetPassword" class="grid grid-cols-1 gap-5.5 md:grid-cols-2">
                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                    New Password
                                </label>
                                <input type="password" x-model="formData.password" placeholder="Enter new password"
                                    :required="resetPassword"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                    Confirm Password
                                </label>
                                <input type="password" x-model="formData.password_confirmation"
                                    placeholder="Confirm new password" :required="resetPassword"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
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
                        <a href="{{ route('users.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="updateLoading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!updateLoading">Update User</span>
                            <span x-show="updateLoading">Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editUserData(userId) {
                return {
                    loading: true,
                    updateLoading: false,
                    error: '',
                    resetPassword: false,
                    roles: [],
                    sections: [],
                    formData: {
                        name: '',
                        email: '',
                        role_id: '',
                        section_id: '',
                        password: '',
                        password_confirmation: ''
                    },

                    async init() {
                        await Promise.all([
                            this.fetchUser(),
                            this.fetchRoles(),
                            this.fetchSections()
                        ]);
                    },

                    async fetchUser() {
                        try {
                            const user = await API.get(`/users/${userId}`);
                            this.formData.name = user.name;
                            this.formData.email = user.email;
                            this.formData.role_id = user.role_id;
                            this.formData.section_id = user.section_id || '';
                        } catch (error) {
                            console.error('Failed to fetch user:', error);
                            this.error = 'Failed to load user details';
                        } finally {
                            this.loading = false;
                        }
                    },

                    async fetchRoles() {
                        try {
                            const response = await API.get('/roles');
                            this.roles = response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch roles:', error);
                        }
                    },

                    async fetchSections() {
                        try {
                            const response = await API.get('/sections');
                            this.sections = response.data || [];
                        } catch (error) {
                            console.error('Failed to fetch sections:', error);
                        }
                    },

                    async submitUpdate() {
                        this.updateLoading = true;
                        this.error = '';

                        // Validate passwords match if resetting
                        if (this.resetPassword && this.formData.password !== this.formData.password_confirmation) {
                            this.error = 'Passwords do not match';
                            this.updateLoading = false;
                            return;
                        }

                        try {
                            const payload = {
                                name: this.formData.name,
                                email: this.formData.email,
                                role_id: this.formData.role_id,
                                section_id: this.formData.section_id || null
                            };

                            if (this.resetPassword) {
                                payload.password = this.formData.password;
                                payload.password_confirmation = this.formData.password_confirmation;
                            }

                            await API.put(`/users/${userId}`, payload);
                            window.location.href = '/users';
                        } catch (error) {
                            console.error('Update error:', error);
                            this.error = error.message || 'Failed to update user';
                            this.updateLoading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection