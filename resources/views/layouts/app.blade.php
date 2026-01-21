<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Inventory Management System</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>

<body x-data="{ 
        page: '{{ $page ?? 'dashboard' }}', 
        'loaded': true, 
        'darkMode': false, 
        'stickyMenu': false, 
        'sidebarToggle': false, 
        'scrollTop': false,
        'user': {{ json_encode(auth()->user() ?? null) }}
    }" x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}">
    <!-- Preloader -->
    <div x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent">
        </div>
    </div>

    <!-- Tailwind Safelist - Hidden div to ensure dynamic classes are included in compiled CSS -->
    <div class="hidden">
        <!-- Status badge colors -->
        <span class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300"></span>
        <span class="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300"></span>
        <span class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300"></span>
        <span class="bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300"></span>
        <!-- Button colors -->
        <button class="bg-brand-500 hover:bg-brand-600"></button>
        <button class="bg-green-500 hover:bg-green-600"></button>
        <button class="bg-red-500 hover:bg-red-600"></button>
    </div>

    <!-- Page Wrapper -->
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Content Area -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Header -->
            @include('components.header')

            <!-- Main Content -->
            <main>
                <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div
                            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg dark:bg-green-900/20 dark:border-green-800">
                            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if(session('error'))
                        <div
                            class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="{{ asset('js/api.js') }}"></script>
    <script defer src="{{ asset('js/admin.js') }}"></script>
    @stack('scripts')
</body>

</html>