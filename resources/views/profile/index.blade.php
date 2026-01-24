@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    @php
        $user = auth()->user();
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Profile Info Card -->
        <div class="lg:col-span-2">
            <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                    <h3 class="font-medium text-gray-900 dark:text-white">
                        Profile Information
                    </h3>
                </div>

                <div class="p-7">
                    <div class="mb-6 flex items-center gap-4">
                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-100 text-2xl font-bold text-brand-500 dark:bg-brand-900/20">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $user->name ?? 'N/A' }}
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                {{ $user->role->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Section</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                {{ $user->section->name ?? 'Not Assigned' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-sm font-medium" @class([
                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' => $user->is_active ?? false,
                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300' => !($user->is_active ?? false),
                            ])>
                                {{ ($user->is_active ?? false) ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('profile.edit') }}"
                            class="inline-flex items-center justify-center rounded-md bg-brand-500 px-6 py-3 text-center font-medium text-white hover:bg-brand-600">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-6">
            <div class="rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-7 py-4 dark:border-gray-800">
                    <h3 class="font-medium text-gray-900 dark:text-white">Account Details</h3>
                </div>

                <div class="p-7 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">User ID</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">#{{ $user->id ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->email ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Last Login</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $user->last_login_at?->format('M d, Y H:i') ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection