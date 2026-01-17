@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('content')
    <div x-data="createUserData()">
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

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Create New User
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitUser">
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
                        <!-- Password -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" x-model="formData.password" required placeholder="Enter password"
                                class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" x-model="formData.password_confirmation" required
                                placeholder="Confirm password"
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
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Create User</span>
                            <span x-show="loading">Creating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function createUserData() {
                return {
                    loading: false,
                    error: '',
                    roles: [],
                    sections: [],
                    formData: {
                        name: '',
                        email: '',
                        password: '',
                        password_confirmation: '',
                        role_id: '',
                        section_id: ''
                    },

                    async init() {
                        await this.fetchRoles();
                        await this.fetchSections();
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

                    async submitUser() {
                        this.loading = true;
                        this.error = '';

                        // Validate passwords match
                        if (this.formData.password !== this.formData.password_confirmation) {
                            this.error = 'Passwords do not match';
                            this.loading = false;
                            return;
                        }

                        try {
                            await API.post('/users', this.formData);
                            window.location.href = '/users';
                        } catch (error) {
                            console.error('Submit error:', error);
                            this.error = error.message || 'Failed to create user';
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection