@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div x-data="dashboardData()">
        <!-- Stats Cards -->
        <!-- Stats Cards -->
        <div x-show="showStats" class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-6 mb-6">
            <!-- Revenue Card -->
            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/20">
                    <svg class="fill-brand-500" width="22" height="22" viewBox="0 0 22 22" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M21.1063 18.0469L19.3875 3.23126C19.2157 1.71876 17.9438 0.584381 16.3969 0.584381H5.56878C4.05628 0.584381 2.78441 1.71876 2.57816 3.23126L0.859406 18.0469C0.756281 18.9063 1.03128 19.7313 1.61566 20.3844C2.20003 21.0375 3.02816 21.3813 3.92191 21.3813H18.0157C18.8782 21.3813 19.7063 21.0031 20.325 20.3844C20.9094 19.7656 21.2094 18.9063 21.1063 18.0469ZM19.2157 19.3531C18.9407 19.6625 18.5625 19.8344 18.0157 19.8344H3.92191C3.42191 19.8344 3.01878 19.6625 2.74378 19.3531C2.46878 19.0438 2.33441 18.6313 2.40003 18.1844L4.11878 3.36876C4.19066 2.71563 4.73753 2.16876 5.56878 2.16876H16.4313C17.2282 2.16876 17.7751 2.71563 17.8469 3.36876L19.5657 18.1844C19.6313 18.6656 19.5313 19.0438 19.2157 19.3531Z"
                            fill="" />
                        <path
                            d="M14.3345 5.29375C13.922 5.39688 13.647 5.80625 13.7501 6.21562C13.7845 6.42188 13.8189 6.62813 13.8189 6.80625C13.8189 8.35313 12.547 9.625 11.0001 9.625C9.45327 9.625 8.18139 8.35313 8.18139 6.80625C8.18139 6.62813 8.21577 6.42188 8.25014 6.21562C8.35327 5.80625 8.07827 5.39688 7.66889 5.29375C7.25952 5.19063 6.85014 5.46563 6.74702 5.875C6.67514 6.1875 6.63452 6.49688 6.63452 6.80625C6.63452 9.2125 8.5939 11.1719 11.0001 11.1719C13.4064 11.1719 15.3658 9.2125 15.3658 6.80625C15.3658 6.49688 15.3251 6.1875 15.2533 5.875C15.1501 5.46563 14.7408 5.225 14.3345 5.29375Z"
                            fill="" />
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-xs font-bold text-gray-900 dark:text-white"
                            x-text="formatCurrency(stats.revenue)">
                            ₦0.00
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Today</span>
                    </div>
                </div>
            </div>

            <!-- Number Card -->
            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/20">
                    <svg class="fill-green-500" width="22" height="22" viewBox="0 0 22 22" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M11.7531 0.171875H10.2469C9.64062 0.171875 9.10938 0.703125 9.10938 1.30937V2.64062C9.10938 3.24687 9.64062 3.77812 10.2469 3.77812H11.7531C12.3594 3.77812 12.8906 3.24687 12.8906 2.64062V1.30937C12.8906 0.703125 12.3594 0.171875 11.7531 0.171875Z"
                            fill="" />
                        <path
                            d="M15.125 6.28125H6.875C6.26875 6.28125 5.7375 6.8125 5.7375 7.41875V20.5812C5.7375 21.1875 6.26875 21.7188 6.875 21.7188H15.125C15.7312 21.7188 16.2625 21.1875 16.2625 20.5812V7.41875C16.2625 6.8125 15.7312 6.28125 15.125 6.28125ZM14.7125 19.9688H7.2875V7.83125H14.7125V19.9688Z"
                            fill="" />
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-xs font-bold text-gray-900 dark:text-white" x-text="stats.sales_count">
                            0
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Number of Sales</span>
                    </div>
                </div>
            </div>

            <!-- Waste Cost Card -->
            <div
                class="rounded-sm border border-gray-200 bg-white px-6 py-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/20">
                    <svg class="fill-orange-500" width="22" height="22" viewBox="0 0 22 22" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.8094 3.02498H14.1625V2.4406C14.1625 1.40935 13.3375 0.584351 12.3062 0.584351H9.65935C8.6281 0.584351 7.8031 1.40935 7.8031 2.4406V3.02498H5.15623C4.15935 3.02498 3.33435 3.84998 3.33435 4.84685V5.8781C3.33435 6.63435 3.78123 7.2531 4.43435 7.5281L4.98435 18.9062C5.0531 20.3156 6.22185 21.4156 7.63123 21.4156H14.3C15.7093 21.4156 16.8781 20.3156 16.9469 18.9062L17.5312 7.49372C18.1844 7.21872 18.6312 6.5656 18.6312 5.84372V4.81247C18.6312 3.84998 17.8062 3.02498 16.8094 3.02498ZM9.38435 2.4406C9.38435 2.26872 9.52185 2.13122 9.69373 2.13122H12.3406C12.5125 2.13122 12.65 2.26872 12.65 2.4406V3.02498H9.41873V2.4406H9.38435ZM4.9156 4.84685C4.9156 4.70935 5.01873 4.57185 5.1906 4.57185H16.8094C16.9469 4.57185 17.0844 4.67498 17.0844 4.84685V5.8781C17.0844 6.0156 16.9812 6.1531 16.8094 6.1531H5.1906C5.0531 6.1531 4.9156 6.04998 4.9156 5.8781V4.84685V4.84685ZM14.3344 19.8687H7.6656C7.08123 19.8687 6.5656 19.4218 6.5656 18.8031L6.04998 7.6656H15.9844L15.4687 18.8031C15.4343 19.3875 14.9187 19.8687 14.3344 19.8687Z"
                            fill="" />
                        <path
                            d="M11 9.55621C10.5844 9.55621 10.2375 9.90308 10.2375 10.3187V16.7999C10.2375 17.2156 10.5844 17.5624 11 17.5624C11.4156 17.5624 11.7625 17.2156 11.7625 16.7999V10.3187C11.7625 9.90308 11.4156 9.55621 11 9.55621Z"
                            fill="" />
                        <path
                            d="M8.21558 9.5906C7.83433 9.5562 7.48745 9.90308 7.45308 10.2843L7.0687 16.7656C7.03433 17.1468 7.34683 17.4937 7.72808 17.5281C7.76245 17.5281 7.76245 17.5281 7.79683 17.5281C8.1437 17.5281 8.45620 17.2499 8.49058 16.8687L8.87495 10.3874C8.90933 10.0062 8.59683 9.65933 8.21558 9.5906Z"
                            fill="" />
                        <path
                            d="M13.7844 9.5906C13.4031 9.62498 13.0906 10.0062 13.125 10.3874L13.5094 16.8687C13.5437 17.2156 13.8562 17.5281 14.2031 17.5281C14.2375 17.5281 14.2375 17.5281 14.2719 17.5281C14.6531 17.4937 14.9656 17.1468 14.9312 16.7656L14.5469 10.2843C14.5125 9.90308 14.1656 9.5562 13.7844 9.5906Z"
                            fill="" />
                    </svg>
                </div>

                <div class="mt-4 flex items-end justify-between">
                    <div>
                        <h4 class="text-title-xs font-bold text-gray-900 dark:text-white"
                            x-text="formatCurrency(stats.waste_cost)">
                            ₦0.00
                        </h4>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Waste Cost</span>
                    </div>
                </div>
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

        <!-- Welcome Message -->
        <div x-show="!loading && !error"
            class="rounded-sm border border-gray-200 bg-white p-6 shadow-default dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Welcome back, <span x-text="user.name"></span>!
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                You are logged in as <strong x-text="user.role"></strong>
                <span x-show="user.section"> in the <strong x-text="user.section"></strong> section</span>.
            </p>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Use the sidebar to navigate through the system. The dashboard shows key metrics for your business.
            </p>
        </div>
    </div>

    @push('scripts')
        <script>
            function dashboardData() {
                return {
                    loading: true,
                    error: '',
                    showStats: false,
                    stats: {
                        revenue: 0,
                        profit: 0,
                        expenses: 0,
                        waste_cost: 0
                    },
                    user: {
                        name: '',
                        role: '',
                        section: ''
                    },

                    async init() {
                        await this.fetchDashboardData();
                    },

                    async fetchDashboardData() {
                        this.loading = true;
                        this.error = '';

                        try {
                            // Fetch user data
                            const userResponse = await API.get('/user');
                            this.user = {
                                name: userResponse.name,
                                role: userResponse.role?.name || 'User',
                                section: userResponse.section?.name || null
                            };

                            // Check if user is authorized to view stats
                            const authorizedRoles = ['Admin', 'Manager', 'Frontline Sales'];
                            if (authorizedRoles.includes(this.user.role)) {
                                // Fetch dashboard stats
                                const statsResponse = await API.get('/reports/dashboard');
                                this.stats = {
                                    revenue: statsResponse.revenue || 0,
                                    profit: statsResponse.profit || 0,
                                    sales_count: statsResponse.sales_count || 0,
                                    expenses: statsResponse.expenses || 0,
                                    waste_cost: statsResponse.waste_cost || 0
                                };
                                this.showStats = true;
                            } else {
                                this.showStats = false;
                            }

                        } catch (error) {
                            console.error('Dashboard error:', error);
                            // Only show error if it's not a 403 (Unauthorized) or if the user WAS supposed to see stats
                            if (!error.message.includes('Unauthorized') && !error.message.includes('403')) {
                                this.error = error.message || 'Failed to load dashboard data';
                            }
                        } finally {
                            this.loading = false;
                        }
                    },

                    formatCurrency(amount) {
                        return '₦' + parseFloat(amount || 0).toLocaleString('en-NG', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection