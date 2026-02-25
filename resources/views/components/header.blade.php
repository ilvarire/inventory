@php
    // Get authenticated user with relationships
    $user = auth()->user()->load(['role', 'section']);
@endphp

<header class="sticky top-0 z-999 flex w-full bg-white drop-shadow-1 dark:bg-gray-900 dark:drop-shadow-none">
    <div class="w-full flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Hamburger Toggle -->
            <button
                class="z-99999 block rounded-sm border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:hidden"
                @click.stop="sidebarToggle = !sidebarToggle">
                <svg class="hidden fill-current xl:block" width="16" height="12" viewBox="0 0 16 12" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.583252 1C0.583252 0.585788 0.919038 0.25 1.33325 0.25H14.6666C15.0808 0.25 15.4166 0.585786 15.4166 1C15.4166 1.41421 15.0808 1.75 14.6666 1.75L1.33325 1.75C0.919038 1.75 0.583252 1.41422 0.583252 1ZM0.583252 11C0.583252 10.5858 0.919038 10.25 1.33325 10.25L14.6666 10.25C15.0808 10.25 15.4166 10.5858 15.4166 11C15.4166 11.4142 15.0808 11.75 14.6666 11.75L1.33325 11.75C0.919038 11.75 0.583252 11.4142 0.583252 11ZM1.33325 5.25C0.919038 5.25 0.583252 5.58579 0.583252 6C0.583252 6.41421 0.919038 6.75 1.33325 6.75L7.99992 6.75C8.41413 6.75 8.74992 6.41421 8.74992 6C8.74992 5.58579 8.41413 5.25 7.99992 5.25L1.33325 5.25Z"
                        fill="" />
                </svg>

                <svg :class="sidebarToggle ? 'hidden' : 'block xl:hidden'"
                    class="fill-current dark:fill-white xl:hidden" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12.75 12C12.75 11.5858 12.4142 11.25 12 11.25L4 11.25Z"
                        fill="" />
                </svg>

                <!-- cross icon -->
                <svg :class="sidebarToggle ? 'block xl:hidden' : 'hidden'" class="fill-current" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
                        fill="" />
                </svg>
            </button>

            <!-- Page Title -->
            <div class="hidden sm:block">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3 2xsm:gap-7">
            <!-- Notification Menu Area -->
            <div class="relative" x-data="notificationsData()" x-init="init()">
                <button
                    class="relative flex h-8.5 w-8.5 items-center justify-center rounded-full border border-gray-200 bg-white hover:text-brand-500 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    @click.prevent="toggleDropdown" @click.outside="dropdownOpen = false">
                    <span
                        class="absolute -top-1 -right-1.5 z-1 h-2 w-2 rounded-full bg-red-500 border border-white dark:border-gray-900"
                        x-show="unreadCount > 0">
                        <span
                            class="absolute -z-1 inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-75"></span>
                    </span>

                    <svg class="fill-current duration-300 ease-in-out" width="18" height="18" viewBox="0 0 18 18"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M16.1999 14.9343L15.6374 14.0624C15.5249 13.8937 15.4687 13.7249 15.4687 13.528V7.67803C15.4687 6.01865 14.7655 4.47178 13.4718 3.31865C12.4312 2.39053 11.0811 1.7999 9.64678 1.6874V1.1249C9.64678 0.787402 9.36553 0.478027 8.9999 0.478027C8.6624 0.478027 8.35303 0.759277 8.35303 1.1249V1.6874C6.91865 1.7999 5.56865 2.39053 4.52803 3.31865C3.23428 4.47178 2.53115 6.01865 2.53115 7.67803V13.528C2.53115 13.7249 2.4749 13.8937 2.3624 14.0624L1.7999 14.9343C1.6874 15.1312 1.63115 15.328 1.63115 15.553C1.63115 15.8343 1.74365 16.0874 1.96865 16.2843C2.19365 16.4812 2.50303 16.5937 2.8124 16.5937H15.1874C15.4968 16.5937 15.8062 16.4812 16.0312 16.2843C16.2562 16.0874 16.3687 15.8343 16.3687 15.553C16.3687 15.328 16.3124 15.1312 16.1999 14.9343ZM8.9999 17.6905C9.75928 17.6905 10.4343 17.2687 10.828 16.65H7.1999C7.56553 17.2687 8.24053 17.6905 8.9999 17.6905Z"
                            fill="" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="dropdownOpen" x-transition
                    class="absolute mt-3 -right-6 flex h-90 w-75 flex-col rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900"
                    style="display: none;">
                    <div class="px-5 py-3 gap-3 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
                        <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notifications</h5>
                        <button @click="markAllRead" class="text-xs text-brand-500 hover:text-brand-600 w-24">Mark
                            all
                            read</button>
                    </div>

                    <ul class="flex flex-col overflow-y-auto h-auto max-h-64">
                        <template x-for="notification in notifications" :key="notification.id">
                            <li>
                                <a class="flex flex-col gap-2.5 border-t border-gray-200 px-4.5 py-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                    href="#" @click.prevent="onNotificationClick(notification)">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm text-gray-800 dark:text-white" x-text="notification.message">
                                        </p>
                                    </div>
                                    <p class="text-xs text-gray-500" x-text="formatDate(notification.created_at)"></p>
                                </a>
                            </li>
                        </template>
                        <li x-show="notifications.length === 0" class="px-4.5 py-3 text-center text-sm text-gray-500">
                            No new notifications
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <button
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800"
                @click.prevent="darkMode = !darkMode">
                <svg class="hidden fill-white dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z"
                        fill="" />
                </svg>
                <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"
                        fill="" />
                </svg>
            </button>

            <!-- User Dropdown -->
            <div class="relative" x-data="{ dropdownOpen: false }">
                <button @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false"
                    class="flex items-center gap-3">
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            {{ $user->role->name ?? 'User' }} @if($user->section) - {{ $user->section->name }} @endif
                        </span>
                    </span>

                    <span
                        class="h-10 w-10 rounded-full bg-brand-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </span>

                    <svg class="hidden fill-current sm:block" width="12" height="8" viewBox="0 0 12 8" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M0.410765 0.910734C0.736202 0.585297 1.26384 0.585297 1.58928 0.910734L6.00002 5.32148L10.4108 0.910734C10.7362 0.585297 11.2638 0.585297 11.5893 0.910734C11.9147 1.23617 11.9147 1.76381 11.5893 2.08924L6.58928 7.08924C6.26384 7.41468 5.7362 7.41468 5.41077 7.08924L0.410765 2.08924C0.0853277 1.76381 0.0853277 1.23617 0.410765 0.910734Z"
                            fill="" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="dropdownOpen" x-transition
                    class="absolute right-0 mt-4 flex w-62.5 flex-col rounded-sm border border-gray-200 bg-white shadow-default dark:border-gray-800 dark:bg-gray-900">
                    <ul class="flex flex-col overflow-y-auto border-b border-gray-200 dark:border-gray-800">
                        <li>
                            <a href="{{ route('profile.index') }}"
                                class="flex items-center gap-3.5 px-6 py-4 text-sm font-medium duration-300 ease-in-out text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 lg:text-base">
                                <svg class="fill-current" width="22" height="22" viewBox="0 0 22 22" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M11 9.62499C8.42188 9.62499 6.35938 7.59687 6.35938 5.12187C6.35938 2.64687 8.42188 0.618744 11 0.618744C13.5781 0.618744 15.6406 2.64687 15.6406 5.12187C15.6406 7.59687 13.5781 9.62499 11 9.62499ZM11 2.16562C9.28125 2.16562 7.90625 3.50624 7.90625 5.12187C7.90625 6.73749 9.28125 8.07812 11 8.07812C12.7188 8.07812 14.0938 6.73749 14.0938 5.12187C14.0938 3.50624 12.7188 2.16562 11 2.16562Z"
                                        fill="" />
                                    <path
                                        d="M17.7719 21.4156H4.2281C3.5406 21.4156 2.9906 20.8656 2.9906 20.1781V17.0844C2.9906 13.7156 5.7406 10.9656 9.10935 10.9656H12.925C16.2937 10.9656 19.0437 13.7156 19.0437 17.0844V20.1781C19.0094 20.8312 18.4594 21.4156 17.7719 21.4156ZM4.53748 19.8687H17.4969V17.0844C17.4969 14.575 15.4344 12.5125 12.925 12.5125H9.07498C6.5656 12.5125 4.50311 14.575 4.50311 17.0844V19.8687H4.53748Z"
                                        fill="" />
                                </svg>
                                My Profile
                            </a>
                        </li>
                    </ul>

                    <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="flex items-center gap-3.5 px-6 py-4 text-sm font-medium duration-300 ease-in-out text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800 lg:text-base">
                        <svg class="fill-current" width="22" height="22" viewBox="0 0 22 22" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M15.5375 0.618744H11.6531C10.7594 0.618744 10.0031 1.37499 10.0031 2.26874V4.64062C10.0031 5.05312 10.3469 5.39687 10.7594 5.39687C11.1719 5.39687 11.55 5.05312 11.55 4.64062V2.23437C11.55 2.16562 11.5844 2.13124 11.6531 2.13124H15.5375C16.3625 2.13124 17.0156 2.78437 17.0156 3.60937V18.3562C17.0156 19.1812 16.3625 19.8344 15.5375 19.8344H11.6531C11.5844 19.8344 11.55 19.8 11.55 19.7312V17.3594C11.55 16.9469 11.2062 16.6031 10.7594 16.6031C10.3125 16.6031 10.0031 16.9469 10.0031 17.3594V19.7312C10.0031 20.625 10.7594 21.3812 11.6531 21.3812H15.5375C17.2219 21.3812 18.5625 20.0062 18.5625 18.3562V3.64374C18.5625 1.95937 17.1875 0.618744 15.5375 0.618744Z"
                                fill="" />
                            <path
                                d="M6.05001 11.7563H12.2031C12.6156 11.7563 12.9594 11.4125 12.9594 11C12.9594 10.5875 12.6156 10.2438 12.2031 10.2438H6.08439L8.21564 8.07813C8.52501 7.76875 8.52501 7.2875 8.21564 6.97812C7.90626 6.66875 7.42501 6.66875 7.11564 6.97812L3.67814 10.4844C3.36876 10.7938 3.36876 11.275 3.67814 11.5844L7.11564 15.0906C7.25314 15.2281 7.45939 15.3312 7.66564 15.3312C7.87189 15.3312 8.04376 15.2625 8.21564 15.125C8.52501 14.8156 8.52501 14.3344 8.21564 14.025L6.05001 11.7563Z"
                                fill="" />
                        </svg>
                        Log Out
                    </button>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        function notificationsData() {
            return {
                dropdownOpen: false,
                unreadCount: 0,
                notifications: [],

                async init() {
                    await this.fetchUnreadCount();
                    // Poll every 30 seconds
                    setInterval(() => this.fetchUnreadCount(), 30000);
                },

                async toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                    if (this.dropdownOpen) {
                        await this.fetchNotifications();
                    }
                },

                async fetchUnreadCount() {
                    try {
                        const response = await API.get('/notifications/unread-count');
                        this.unreadCount = response.count;
                    } catch (error) {
                        console.error('Failed to fetch unread count:', error);
                    }
                },

                async fetchNotifications() {
                    try {
                        const response = await API.get('/notifications'); // Assumes API returns { data: [...] } for pagination
                        this.notifications = response.data || response;
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    }
                },

                async onNotificationClick(notification) {
                    // 1. Remove from list immediately (since we only show unread)
                    this.notifications = this.notifications.filter(n => n.id !== notification.id);
                    this.unreadCount = Math.max(0, this.unreadCount - 1);

                    // 2. Mark as read on the server
                    try {
                        await API.post(`/notifications/${notification.id}/read`);
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }

                    // 3. Navigate if there is a URL
                    if (notification.action_url) {
                        window.location.href = notification.action_url;
                    }
                },

                async markAsRead(notification) {
                    if (notification.read_at) return;
                    try {
                        notification.read_at = new Date().toISOString();
                        await API.post(`/notifications/${notification.id}/read`);
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                },

                async markAllRead() {
                    try {
                        await API.post('/notifications/mark-all-read');
                        this.notifications.forEach(n => n.read_at = new Date().toISOString());
                        this.unreadCount = 0;
                    } catch (error) {
                        console.error('Failed to mark all as read:', error);
                    }
                },

                formatDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const now = new Date();
                    const diff = (now - date) / 1000; // seconds

                    if (diff < 60) return 'Just now';
                    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';

                    return date.toLocaleDateString();
                }
            }
        }
    </script>
@endpush