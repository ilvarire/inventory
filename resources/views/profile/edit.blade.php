@extends('layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
    @php
        $user = auth()->user();
    @endphp
    <div x-data="editProfileData()">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('profile.index') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Profile
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                <h3 class="font-medium text-gray-900 dark:text-white">
                    Edit Profile
                </h3>
            </div>

            <div class="p-7">
                <form @submit.prevent="submitUpdate">
                    <!-- Name -->
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="formData.name" required placeholder="Enter your full name"
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Email -->
                    <div class="mb-5.5">
                        <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" x-model="formData.email" required placeholder="your@email.com"
                            class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Change Password Section -->
                    <div class="mb-5.5">
                        <div class="mb-3 flex items-center gap-2">
                            <input type="checkbox" x-model="changePassword" id="changePassword"
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            <label for="changePassword" class="text-sm font-medium text-gray-900 dark:text-white">
                                Change Password
                            </label>
                        </div>

                        <div x-show="changePassword" class="space-y-5.5">
                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                    Current Password
                                </label>
                                <input type="password" x-model="formData.current_password" :required="changePassword"
                                    placeholder="Enter current password"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                    New Password
                                </label>
                                <input type="password" x-model="formData.password" :required="changePassword"
                                    placeholder="Enter new password"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div>
                                <label class="mb-3 block text-sm font-medium text-gray-900 dark:text-white">
                                    Confirm New Password
                                </label>
                                <input type="password" x-model="formData.password_confirmation" :required="changePassword"
                                    placeholder="Confirm new password"
                                    class="w-full rounded border border-gray-300 bg-transparent px-5 py-3 text-gray-900 outline-none transition focus:border-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div x-show="success"
                        class="mb-5.5 rounded-sm border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                        <p class="text-sm text-green-800 dark:text-green-200" x-text="success"></p>
                    </div>

                    <!-- Error Message -->
                    <div x-show="error"
                        class="mb-5.5 rounded-sm border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <p class="text-sm text-red-800 dark:text-red-200" x-text="error"></p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4">
                        <a href="{{ route('profile.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Update Profile</span>
                            <span x-show="loading">Updating...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editProfileData() {
                return {
                    loading: false,
                    error: '',
                    success: '',
                    changePassword: false,
                    formData: {
                        name: @json($user->name ?? ''),
                        email: @json($user->email ?? ''),
                        current_password: '',
                        password: '',
                        password_confirmation: ''
                    },

                    async submitUpdate() {
                        this.loading = true;
                        this.error = '';
                        this.success = '';

                        // Validate passwords match if changing
                        if (this.changePassword && this.formData.password !== this.formData.password_confirmation) {
                            this.error = 'New passwords do not match';
                            this.loading = false;
                            return;
                        }

                        try {
                            const payload = {
                                name: this.formData.name,
                                email: this.formData.email
                            };

                            if (this.changePassword) {
                                payload.current_password = this.formData.current_password;
                                payload.password = this.formData.password;
                                payload.password_confirmation = this.formData.password_confirmation;
                            }

                            await API.put('/profile', payload);
                            this.success = 'Profile updated successfully!';

                            // Clear password fields
                            if (this.changePassword) {
                                this.formData.current_password = '';
                                this.formData.password = '';
                                this.formData.password_confirmation = '';
                                this.changePassword = false;
                            }

                            // Redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = '/profile';
                            }, 2000);
                        } catch (error) {
                            console.error('Update error:', error);
                            this.error = error.message || 'Failed to update profile';
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection