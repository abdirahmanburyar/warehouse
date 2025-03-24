<template>
    <div class="app-container">
        <!-- Sidebar -->
        <div :class="['sidebar', { 'sidebar-collapsed': !sidebarOpen }]">
            <div class="white-box" style="border-color: white;">
                <Link :href="route('dashboard')" class="logo-container">
                <img src="/assets/images/moh.png" class="moh-logo" style="height: 40px" />
                <img src="/assets/images/psi.jpg" class="psi-logo" style="height: 40px" />
                </Link>

            </div>
            <button @click="toggleSidebar" class="sidebar-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                    <path v-if="sidebarOpen" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" fill="currentColor" />
                    <path v-else d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" fill="currentColor" />
                </svg>
            </button>

            <div class="sidebar-menu">
                <Link :href="route('dashboard')" class="menu-item" :class="{ active: route().current('dashboard') }"
                    style="margin-top: 5.2rem;" @click="setCurrentPage('dashboard')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Dashboard</span>
                </div>
                </Link>


                <Link :href="route('approvals.index')" class="menu-item"
                    :class="{ active: route().current('approvals.*') }" @click="setCurrentPage('approvals')"
                    v-if="$page.props.auth.permissions.includes('approval.view')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Approvals</span>
                </div>
                </Link>

                <Link :href="route('warehouses.index')" class="menu-item"
                    :class="{ active: route().current('warehouses.*') }" @click="setCurrentPage('warehouses')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Warehouses</span>
                </div>
                </Link>

                <Link :href="route('products.index')" class="menu-item"
                    :class="{ active: route().current('products.*') }" @click="setCurrentPage('products')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H6c-.55 0-1-.45-1-1V6c0-.55.45-1 1-1h12c.55 0 1 .45 1 1v12c0 .55-.45 1-1 1zm-4.44-6.19l-2.35 3.02-1.56-1.88c-.2-.25-.58-.24-.78.01l-1.74 2.23c-.2.25-.2.61 0 .86.2.25.58.26.78.01l1.35-1.73 1.58 1.9c.2.25.58.24.78-.01l2.55-3.27c.2-.25.19-.61-.02-.86-.21-.25-.59-.24-.79.01z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Product List</span>
                </div>
                </Link>

                <Link :href="route('inventories.index')" class="menu-item"
                    :class="{ active: route().current('inventories.*') }" @click="setCurrentPage('inventories')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-1 18H5V9h14v11zm1-13H4V4h16v3z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Inventory</span>
                </div>
                </Link>

                <Link :href="route('expired.index')" class="menu-item" :class="{ active: route().current('expired.*') }"
                    @click="setCurrentPage('expired')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Expired</span>
                </div>
                </Link>

                <Link :href="route('supplies.index')" class="menu-item"
                    :class="{ active: route().current('supplies.*') }" @click="setCurrentPage('supplies')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Supplies</span>
                </div>
                </Link>

                <Link :href="route('settings.index')" class="menu-item"
                    :class="{ active: route().current('settings.*') }" @click="setCurrentPage('settings')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="35" height="35">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2Zm0 18.37C6.01 20.37 2 15.75 2 12C2 8.25 6.01 5.63 12 5.63C17.99 5.63 22 8.25 22 12C22 15.75 17.99 20.37 12 20.37Z"
                                fill="currentColor" />
                        </svg>
                    </div>
                    <span class="menu-text">Settings</span>
                </div>
                </Link>

            </div>
        </div>

        <!-- Main Content -->
        <div :class="['main-content', { 'main-content-expanded': !sidebarOpen }]">
            <!-- Top Navigation -->
            <div class="top-nav">
                <div class="inventory-banner">
                    <div class="flex justify-between">
                        <div class="flex flex-col">
                            <button @click="toggleSidebar" class="back-button">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                            <div class="inventory-text">
                                <h1>Manage Your Inventory</h1>
                                <p>"Keeping Essentials Ready, Every Time"</p>
                            </div>
                        </div>

                        <img src="/assets/images/10873037.webp" alt="Inventory illustration" class="svg-image" />
                    </div>
                    <div class="user-section">
                        <div class="flex flex-row">
                            <div class="notification-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                    <path
                                        d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"
                                        fill="#FFF" />
                                </svg>
                            </div>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <span>A</span>
                                </div>
                                <div class="user-details">
                                    <span class="user-role">Pharmaceutical Manager</span>
                                    <span class="user-name">{{ $page.props.auth.user?.name }}</span>
                                </div>
                            </div>
                            <button class="logout-button" @click="logout">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                        <h5>PSI</h5>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Toast Container -->
        <ToastContainer />
    </div>
</template>

<script>
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

export default {
    components: {
        Link,
        ApplicationLogo,
        ToastContainer,
        Dropdown,
        DropdownLink,
        NavLink,
        ResponsiveNavLink,
    },
    props: {
        auth: Object,
        errors: Object,
    },
    data() {
        return {
            sidebarOpen: true,
            currentPage: 'dashboard',
            userMenuOpen: false,
        };
    },
    methods: {
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },
        setCurrentPage(page) {
            this.currentPage = page;
        },
        logout() {
            this.$inertia.post(route('logout'));
        }
    },
};
</script>

<style scoped>
/* Base Styles */
.app-container {
    display: flex;
    min-height: 100vh;
    background-color: #f9fafb;
}

/* Sidebar Styles */
.sidebar {
    width: 100px;
    background: linear-gradient(to bottom, #2BCA89, #2BCA89, #FA8603);
    border-right: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 50;
    padding: 0;
    border-right: none;
}

.sidebar-collapsed {
    width: 0px;
}

.white-box {
    background-color: white;
    padding: 1rem 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    position: relative;
}

.logo-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.moh-logo {
    height: 60px;
    width: auto;
    margin-bottom: 0.75rem;
    object-fit: contain;
}

.psi-logo {
    height: 45px;
    width: auto;
    object-fit: contain;
}

.sidebar-collapsed .white-box {
    padding: 0.5rem 0;
}

.sidebar-collapsed .moh-logo {
    height: 30px;
    margin-bottom: 0.25rem;
}

.sidebar-collapsed .psi-logo {
    height: 22px;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: background-color 0.3s ease;
    position: absolute;
    right: 0.25rem;
    top: 0.25rem;
}

.sidebar-toggle:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.sidebar-menu {
    display: flex;
    flex-direction: column;
    padding: 0;
    margin: 0;
    flex-grow: 1;
    overflow-y: auto;
}

.menu-item {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 0;
    position: relative;
    margin: 0;
    padding: 0;
    border-radius: 0;
    border-right-color: transparent;
}

.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.menu-item.active {
    background: white;
    /* box-shadow: 0 0 10px 1px rgba(0, 0, 0, 0.2); */
    text-align: center;
    color: #111827;
    border-radius: 0;
    margin-right: 0;
    border-right: 0;
    border-top-left-radius: 50px;
    margin: 0;
    padding: 0;
    border-bottom-left-radius: 50px;
    border-top-right-radius: -50px;
    border-bottom-right-radius: -50px;
}

.menu-item.active::after {
    display: none;
}

.menu-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.menu-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    flex-shrink: 0;
}

.sidebar-collapsed .menu-icon {
    margin-bottom: 0;
}

.menu-text {
    white-space: nowrap;
    transition: opacity 0.3s ease;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Main Content Styles */
.main-content {
    flex-grow: 1;
    margin-left: 100px;
    transition: margin-left 0.3s ease;
    display: flex;
    flex-direction: column;
}

.main-content-expanded {
    margin-left: 0px;
}

/* Top Navigation Styles */
.top-nav {
    background-color: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 40;
}

.inventory-banner {
    display: flex;
    align-items: center;
    background-color: #60a5fa;
    color: white;
    padding: 0.5rem 1.5rem;
    width: 100%;
    height: 175px;
    position: relative;
    overflow: hidden;
    border-top-left-radius: 40px;
    border-right-color: white;
}

.back-button {
    background-color: white;
    color: #333;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    cursor: pointer;
}

.inventory-text {
    z-index: 10;
}

.inventory-text h1 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.inventory-text p {
    font-size: 0.9rem;
    opacity: 0.9;
}

.inventory-image {
    position: absolute;
    right: 200px;
    height: 100%;
    display: flex;
    align-items: center;
}

.svg-image {
    height: 120px;
}

.user-section {
    display: flex;
    flex-direction: column;
    align-items: start;
    padding: 0.5rem;
    position: absolute;
    top: 0;
    right: 10px;
    height: 100%;
}

.notification-icon {
    margin-right: 1rem;
    cursor: pointer;
}

.user-info {
    display: flex;
    align-items: center;
    margin-right: 1rem;
}

.user-avatar {
    width: 36px;
    height: 36px;
    background-color: #ef4444;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 0.5rem;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-role {
    font-size: 0.7rem;
    color: white;
    opacity: 0.8;
}

.user-name {
    font-weight: 600;
    color: white;
    font-size: 0.9rem;
}

.logout-button {
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.logout-button:hover {
    background-color: #dc2626;
}

/* Page Content Styles */
main {
    padding: 1rem;
    flex-grow: 1;
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar.sidebar-collapsed {
        transform: translateX(0);
        width: 160px;
    }

    .sidebar-collapsed .logo-text,
    .sidebar-collapsed .menu-text {
        display: block;
    }

    .main-content {
        margin-left: 0 !important;
    }

    .user-info {
        display: none;
    }

    .menu-item.active {
        border-radius: 0.5rem;
        margin-right: 0.5rem;
    }

    .menu-item.active::after {
        display: none;
    }

    .menu-content {
        flex-direction: row;
        align-items: center;
    }

    .menu-icon {
        margin-bottom: 0;
        margin-right: 0.5rem;
    }

    .menu-text {
        font-size: 0.8rem;
    }
}

@media (max-width: 640px) {
    .top-nav {
        padding: 0.75rem 1rem;
    }

    .page-content {
        padding: 1rem;
    }

    .banner-title {
        font-size: 1rem;
    }

    .banner-subtitle {
        font-size: 0.75rem;
    }
}
</style>
