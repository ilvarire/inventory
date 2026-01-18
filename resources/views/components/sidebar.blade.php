@php
    // Get authenticated user with relationships
    $user = auth()->user()->load(['role', 'section']);
    $userRole = $user->role->name ?? 'Guest';
    $userSection = $user->section->name ?? null;
@endphp

<aside :class="sidebarToggle ? 'translate-x-0 xl:w-[90px]' : '-translate-x-full'"
    class="sidebar fixed top-0 left-0 z-9999 flex h-screen w-[290px] flex-col overflow-y-auto border-r border-gray-200 bg-white px-5 transition-all duration-300 xl:static xl:translate-x-0 dark:border-gray-800 dark:bg-black"
    @click.outside="sidebarToggle = false">
    <!-- SIDEBAR HEADER -->
    <div :class="sidebarToggle ? 'justify-center' : 'justify-between'"
        class="sidebar-header flex items-center gap-2 pt-8 pb-7">
        <a href="{{ route('dashboard') }}">
            <span class="logo" :class="sidebarToggle ? 'hidden' : ''">
                <img class="dark:hidden" src="{{ asset('images/logo/logo.svg') }}" alt="Logo" />
                <img class="hidden dark:block" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Logo" />
            </span>

            <img class="logo-icon" :class="sidebarToggle ? 'xl:block' : 'hidden'"
                src="{{ asset('images/logo/logo-icon.svg') }}" alt="Logo" />
        </a>
    </div>

    <div class="no-scrollbar flex flex-col overflow-y-auto duration-300 ease-linear">
        <!-- Sidebar Menu -->
        <nav x-data="{selected: $persist('Dashboard')}">
            <!-- Menu Group -->
            <div>
                <h3 class="mb-4 text-xs leading-[20px] text-gray-400 uppercase">
                    <span class="menu-group-title" :class="sidebarToggle ? 'xl:hidden' : ''">
                        MENU
                    </span>
                    <svg :class="sidebarToggle ? 'xl:block hidden' : 'hidden'"
                        class="menu-group-icon mx-auto fill-current" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                            fill="currentColor" />
                    </svg>
                </h3>

                <ul class="mb-6 flex flex-col gap-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="menu-item group {{ request()->routeIs('dashboard*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <svg class="{{ request()->routeIs('dashboard*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z"
                                    fill="currentColor" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                Dashboard
                            </span>
                        </a>
                    </li>

                    <!-- Inventory (All Roles) -->
                    <li>
                        <a href="#" @click.prevent="selected = (selected === 'Inventory' ? '':'Inventory')"
                            class="menu-item group"
                            :class="(selected === 'Inventory') || {{ request()->routeIs('inventory.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'">
                            <svg :class="(selected === 'Inventory') || {{ request()->routeIs('inventory.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 7L12 3L4 7M20 7L12 11M20 7V17L12 21M12 11L4 7M12 11V21M4 7V17L12 21"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                Inventory
                            </span>
                            <svg class="menu-item-arrow"
                                :class="[(selected === 'Inventory') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>

                        <!-- Dropdown -->
                        <div class="translate transform overflow-hidden"
                            :class="(selected === 'Inventory') ? 'block' :'hidden'">
                            <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'"
                                class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                <li>
                                    <a href="{{ route('inventory.index') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('inventory.index') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        All Materials
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('inventory.low-stock') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('inventory.low-stock') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Low Stock
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('inventory.expiring') }}"
                                        class="menu-dropdown-item group {{ request()->routeIs('inventory.expiring') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                        Expiring Items
                                    </a>
                                </li>
                                @if(in_array($userRole, ['Admin', 'Manager', 'Store Keeper']))
                                    <li>
                                        <a href="{{ route('raw-materials.index') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('raw-materials.*') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Manage Materials
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>

                    <!-- Procurement (Procurement, Admin only) -->
                    @if(in_array($userRole, ['Procurement', 'Admin']))
                        <li>
                            <a href="{{ route('procurement.index') }}"
                                class="menu-item group {{ request()->routeIs('procurement.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('procurement.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M7.7832 19.5H7.7932M16.3203 19.5H16.3303" stroke="currentColor"
                                        stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Procurement
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Material Requests -->
                    <li>
                        <a href="{{ route('material-requests.index') }}"
                            class="menu-item group {{ request()->routeIs('material-requests.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <svg class="{{ request()->routeIs('material-requests.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H18.5001C19.7427 20.75 20.7501 19.7426 20.7501 18.5V5.5C20.7501 4.25736 19.7427 3.25 18.5001 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H18.5001C18.9143 4.75 19.2501 5.08579 19.2501 5.5V18.5C19.2501 18.9142 18.9143 19.25 18.5001 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V5.5ZM6.25005 9.7143C6.25005 9.30008 6.58583 8.9643 7.00005 8.9643L17 8.96429C17.4143 8.96429 17.75 9.30008 17.75 9.71429C17.75 10.1285 17.4143 10.4643 17 10.4643L7.00005 10.4643C6.58583 10.4643 6.25005 10.1285 6.25005 9.7143ZM6.25005 14.2857C6.25005 13.8715 6.58583 13.5357 7.00005 13.5357H17C17.4143 13.5357 17.75 13.8715 17.75 14.2857C17.75 14.6999 17.4143 15.0357 17 15.0357H7.00005C6.58583 15.0357 6.25005 14.6999 6.25005 14.2857Z"
                                    fill="currentColor" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                Material Requests
                            </span>
                        </a>
                    </li>

                    <!-- Recipes (Chef, Manager, Admin) -->
                    @if(in_array($userRole, ['Chef', 'Manager', 'Admin']))
                        <li>
                            <a href="{{ route('recipes.index') }}"
                                class="menu-item group {{ request()->routeIs('recipes.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('recipes.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Recipes
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Production (Chef, Manager, Admin) -->
                    @if(in_array($userRole, ['Chef', 'Manager', 'Admin']))
                        <li>
                            <a href="{{ route('production.index') }}"
                                class="menu-item group {{ request()->routeIs('production.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('production.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Production
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Sales (Frontline Sales, Manager, Admin) -->
                    @if(in_array($userRole, ['Frontline Sales', 'Manager', 'Admin']))
                        <li>
                            <a href="{{ route('sales.index') }}"
                                class="menu-item group {{ request()->routeIs('sales.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('sales.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Sales
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Expenses (Manager, Admin only) -->
                    @if(in_array($userRole, ['Manager', 'Admin']))
                        <li>
                            <a href="{{ route('expenses.index') }}"
                                class="menu-item group {{ request()->routeIs('expenses.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('expenses.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Expenses
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Waste Management -->
                    <li>
                        <a href="{{ route('waste.index') }}"
                            class="menu-item group {{ request()->routeIs('waste.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <svg class="{{ request()->routeIs('waste.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                Waste Logs
                            </span>
                        </a>
                    </li>

                    <!-- Reports (Manager, Admin only) -->
                    @if(in_array($userRole, ['Manager', 'Admin']))
                        <li>
                            <a href="#" @click.prevent="selected = (selected === 'Reports' ? '':'Reports')"
                                class="menu-item group"
                                :class="(selected === 'Reports') || {{ request()->routeIs('reports.*') ? 'true' : 'false' }} ? 'menu-item-active' : 'menu-item-inactive'">
                                <svg :class="(selected === 'Reports') || {{ request()->routeIs('reports.*') ? 'true' : 'false' }} ? 'menu-item-icon-active' : 'menu-item-icon-inactive'"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Reports
                                </span>
                                <svg class="menu-item-arrow"
                                    :class="[(selected === 'Reports') ? 'menu-item-arrow-active' : 'menu-item-arrow-inactive', sidebarToggle ? 'xl:hidden' : '' ]"
                                    width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.39584L10.0001 12.6042L15.2084 7.39585" stroke="currentColor"
                                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>

                            <!-- Dropdown -->
                            <div class="translate transform overflow-hidden"
                                :class="(selected === 'Reports') ? 'block' :'hidden'">
                                <ul :class="sidebarToggle ? 'xl:hidden' : 'flex'"
                                    class="menu-dropdown mt-2 flex flex-col gap-1 pl-9">
                                    <li>
                                        <a href="{{ route('reports.sales') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.sales') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Sales Report
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.profit-loss') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.profit-loss') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Profit & Loss
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.waste') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.waste') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Waste Report
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.expenses') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.expenses') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Expense Report
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.inventory-health') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.inventory-health') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Inventory Health
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('reports.top-selling') }}"
                                            class="menu-dropdown-item group {{ request()->routeIs('reports.top-selling') ? 'menu-dropdown-item-active' : 'menu-dropdown-item-inactive' }}">
                                            Top Selling Items
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- User Management (Admin, Manager only) -->
                    @if(in_array($userRole, ['Admin', 'Manager']))
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="menu-item group {{ request()->routeIs('users.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                <svg class="{{ request()->routeIs('users.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
                                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                    Users
                                </span>
                            </a>
                        </li>
                    @endif

                    <!-- Profile -->
                    <li>
                        <a href="{{ route('profile.index') }}"
                            class="menu-item group {{ request()->routeIs('profile.*') ? 'menu-item-active' : 'menu-item-inactive' }}">
                            <svg class="{{ request()->routeIs('profile.*') ? 'menu-item-icon-active' : 'menu-item-icon-inactive' }}"
                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z"
                                    fill="currentColor" />
                            </svg>
                            <span class="menu-item-text" :class="sidebarToggle ? 'xl:hidden' : ''">
                                Profile
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</aside>