<template>
    <div class="app-container">
        <!-- Permission changes are now handled globally in app.js -->
        <!-- Sidebar -->
        <div :class="['sidebar', { 'sidebar-open': sidebarOpen }]" >
            <div class="white-box" style="border-color: white;">
                <Link :href="route('dashboard')" class="logo-container flex justify-between">
                <img src="/assets/images/moh.png" class="moh-logo" style="height: 50px" />
                <img src="/assets/images/psi.jpg" class="psi-logo" style="height: 50px" />
                </Link>

            </div>
            <div class="sidebar-menu">
                <Link :href="route('dashboard')" class="menu-item" :class="{ active: route().current('dashboard') }"
                    style="margin-top: 75px;" @click="setCurrentPage('dashboard')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('dashboard')" src="/assets/images/dashboard-b.png"
                            class="dashboard-icon" style="height: 24px" />
                        <img v-else src="/assets/images/dashboard-w.png" class="dashboard-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Dashboard</span>
                </div>
                </Link>
                <Link v-if="$page.props.auth.can.order_view" :href="route('orders.index')" class="menu-item" :class="{ active: route().current('orders.*') }"
                    @click="setCurrentPage('orders')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('orders.*')" src="/assets/images/tracking-b.png" class="order-icon"
                            style="height: 24px" />
                        <img v-else src="/assets/images/tracking-w.png" class="order-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Orders</span>
                </div>
                </Link>

                <Link v-if="$page.props.auth.can.transfer_view" :href="route('transfers.index')" class="menu-item"
                    :class="{ active: route().current('transfers.*') }" @click="setCurrentPage('transfers')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('transfers.*')" src="/assets/images/transfer-b.png"
                            class="transfer-icon" style="height: 24px" />
                        <img v-else src="/assets/images/transfer-w.png" class="transfer-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Transfers</span>
                </div> 
                </Link>

                <Link v-if="$page.props.auth.can.product_view" :href="route('products.index')" class="menu-item"
                    :class="{ active: route().current('products.*') }" @click="setCurrentPage('products')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('products.*')" src="/assets/images/product-b.png"
                            class="product-icon" style="height: 24px" />
                        <img v-else src="/assets/images/product-w.png" class="product-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Product List</span>
                </div>
                </Link>


                <Link v-if="$page.props.auth.can.inventory_view" :href="route('inventories.index')" class="menu-item"
                    :class="{ active: route().current('inventories.*') }" @click="setCurrentPage('inventories')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('inventories.*')" src="/assets/images/inventory-b.png"
                            class="inventory-icon" style="height: 24px" />
                        <img v-else src="/assets/images/inventory-w.png" class="inventory-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Inventory</span>
                </div>
                </Link>

                <Link v-if="$page.props.auth.can.inventory_view" :href="route('expired.index')" class="menu-item" :class="{ active: route().current('expired.*') }"
                    @click="setCurrentPage('expired')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('expired.*')" src="/assets/images/expire-b.png" class="expire-icon"
                            style="height: 24px" />
                        <img v-else src="/assets/images/expire-w.png" class="expire-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Expires</span>
                </div>
                </Link>

                <!-- Liquidate and disposals -->
                <Link v-if="$page.props.auth.can.liquidate_view" :href="route('liquidate-disposal.liquidates')" class="menu-item"
                    :class="{ active: route().current('liquidate-disposal.*') }" @click="setCurrentPage('liquidate-disposal')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('liquidate-disposal.*')" src="/assets/images/liquidate-disposal-b.png"
                            class="liquidate-disposal-icon" style="height: 24px" />
                        <img v-else src="/assets/images/liquidate-disposal-w.png" class="liquidate-disposal-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Liquidate & Disposal</span>
                </div>
                </Link>
                

                <Link v-if="$page.props.auth.can.supply_view" :href="route('supplies.index')" class="menu-item"
                    :class="{ active: route().current('supplies.*') }" @click="setCurrentPage('supplies')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('supplies.*')" src="/assets/images/supplier-b.png"
                            class="supplies-icon" style="height: 24px" />
                        <img v-else src="/assets/images/supplier-w.png" class="supplies-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Supplies</span>
                </div>
                </Link>
                <Link v-if="$page.props.auth.can.report_view" :href="route('reports.index')" class="menu-item"
                    :class="{ active: route().current('reports.*') }" @click="setCurrentPage('reports')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('reports.*')" src="/assets/images/facility-b.png"
                            class="facility-icon" style="height: 24px" />
                        <img v-else src="/assets/images/facility-w.png" class="facility-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Reports</span>
                </div>
                </Link>

                <Link v-if="$page.props.auth.can.facility_view" :href="route('facilities.index')" class="menu-item"
                    :class="{ active: route().current('facilities.*') }" @click="setCurrentPage('facilities')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('facilities.*')" src="/assets/images/facility-b.png"
                            class="facility-icon" style="height: 24px" />
                        <img v-else src="/assets/images/facility-w.png" class="facility-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Facilities</span>
                </div>
                </Link>

                <Link v-if="$page.props.auth.can.asset_view" :href="route('assets.index')" class="menu-item" :class="{ active: route().current('assets.*') }"
                    @click="setCurrentPage('assets')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('assets.*')" src="/assets/images/assets-b.png" class="assets-icon"
                            style="height: 24px" />
                        <img v-else src="/assets/images/assets-w.png" class="assets-icon" style="height: 24px" />
                    </div>
                    <span class="menu-text">Assets</span>
                </div>
                </Link>

                <Link v-if="$page.props.auth.can.settings_view" :href="route('settings.index')" class="menu-item"
                    :class="{ active: route().current('settings.*') }" @click="setCurrentPage('settings')">
                <div class="menu-content">
                    <div class="menu-icon">
                        <img v-if="route().current('settings.*')" src="/assets/images/setting-b.png"
                            class="setting-icon" style="height: 24px" />
                        <img v-else src="/assets/images/setting-w.png" class="setting-icon" style="height: 24px" />
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
                        <!-- <div class="flex flex-col"> -->
                            <button @click="toggleSidebar" class="back-button">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                    <path v-if="sidebarOpen"
                                        d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"
                                        fill="currentColor" />
                                    <path v-else d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                            <div class="inventory-text">
                                <h1>{{ title }}</h1>
                                <h3 class="text-black text-lg">"{{ description }}"</h3>
                            </div>
                        <!-- </div> -->
                        <div v-if="img">
                            <img :src="img" alt="Inventory illustration" class="svg-image" heigth="50" />
                        </div>
                    </div>
                    <div class="user-section">
                        <div class="flex flex-row">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <span>A</span>
                                </div>
                                <div class="user-details">
                                    <span class="user-role">Pharmaceutical Manager</span>
                                    <span class="user-name">{{ $page.props.auth.user?.name }}</span>
                                    <span class="user-name">{{ $page.props.warehouse?.name }}</span>
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
                        <!-- <img src="/assets/images/head_web.gif" alt="Inventory illustration" class="svg-image" /> -->
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex flex-col">
                <div class="flex-1">
                    <slot />
                </div>
                <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200">
                    <div class="container mx-auto py-2">
                        <div class="flex justify-center items-center gap-4">
                            <img src="/assets/images/vista.png" alt="Vista" class="w-[80px]" />
                            <span class="flex items-center text-gray-400">|</span>
                            <span class="flex items-center text-gray-600">Copyright 2025 Vista. All rights
                                reserved.</span>
                            <span class="flex items-center text-gray-400">|</span>
                            <span class="flex items-center text-gray-600 hover:text-gray-800 cursor-pointer">Terms of
                                Use</span>
                            <span class="flex items-center text-gray-400">|</span>
                            <span
                                class="flex items-center text-gray-600 hover:text-gray-800 cursor-pointer">Privacy</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        default: ''
    },
    img: {
        type: String,
        default: '/assets/images/head_web.gif'
    }
});

const page = usePage();
const debug = ref(false); // Set to true to see permissions debug info
const sidebarOpen = ref(true);
const currentPage = ref('dashboard');

// Setup permission change listener
onMounted(() => {
    setupPermissionChangeListener();
});

// Function to handle permission change events
const setupPermissionChangeListener = () => {
    if (!window.Echo) {
        console.warn('⚠️ Echo not available, permission change listener not set up');
        return;
    }
    
    // Get the current user ID
    const currentUserId = page.props.auth?.user?.id;
    if (!currentUserId) {
        console.warn('⚠️ User ID not available, permission change listener not set up');
        return;
    }
    
    console.log('🔄 Setting up permission change listener for user:', currentUserId);
    
    // Listen on the private user channel
    const channel = window.Echo.private(`user.${currentUserId}`);
    
    // Listen for permission change events
    channel.listen('permissions-changed', (event) => {
        console.log('🔔 Permission changed event received:', event);
        handlePermissionEvent(event);
    });

    // Also listen for the class name format
    channel.listen('GlobalPermissionChanged', (event) => {
        console.log('🔔 Permission changed event received (GlobalPermissionChanged):', event);
        handlePermissionEvent(event);
    });
};

// Function to handle the permission event
const handlePermissionEvent = (event) => {
    console.log('🔄 Permission change detected, reloading page...');
    
    toast.info('Your permissions have been updated. The page will reload to apply changes.');
    
    // Reload the page after a short delay
    setTimeout(() => {
        console.log('🔄 Reloading page now...');
        window.location.reload();
    }, 3000);
};



const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const setCurrentPage = (page) => {
    currentPage.value = page;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<style scoped>
/* Sidebar Styles */
.sidebar {
    width: 0;
    min-width: 0;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    z-index: 50;
    background: linear-gradient(to bottom, #14D399, #FF8500);
    transform: translateX(-100%);
    opacity: 0;
    visibility: hidden;
}

.sidebar-open {
    width: 130px;
    min-width: 130px;
    transform: translateX(0);
    opacity: 1;
    visibility: visible;
}

.white-box {
    background-color: white;
    /* padding: 1.5rem 0; */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    position: relative;
}

.moh-logo {
    height: 45px;
    width: auto;
    margin-bottom: 0.5rem;
    object-fit: contain;
}

.psi-logo {
    height: 35px;
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
    margin: -12px;
    flex-grow: 1;
    width: 100%;
    align-items: center;
    /* overflow-y: scroll; */
    /* scrollbar-width: none; Firefox */
    /* -ms-overflow-style: none; Internet Explorer 10+ */
}

.sidebar-menu::-webkit-scrollbar {
    display: none;
    /* WebKit */
}

.menu-item {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    margin: 0.3rem 0;
    padding: 0;
    z-index: 1;
    width: 100%;
    height: 50px;
}

.menu-item:hover {
    background-color: rgba(255, 255, 255, 0.15);
}

.menu-item.active {
    background: white;
    color: #111827;
    position: relative;
    border-top-left-radius: 25px;
    border-bottom-left-radius: 25px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    margin-right: -25px;
    padding-right: 25px;
    z-index: 5;
    height: 44px;
    display: flex;
    align-items: center;
    width: 100%;
    left: 0;
}

/* Create the curved effect for the top-right corner */
.menu-item.active::before {
    content: "";
    position: absolute;
    top: -24px;
    right: 0;
    width: 25px;
    height: 24px;
    background-color: transparent;
    border-bottom-right-radius: 20px;
    box-shadow: 10px 10px 0 0 white;
    z-index: 2;
}

/* Create the curved effect for the bottom-right corner */
.menu-item.active::after {
    content: "";
    position: absolute;
    bottom: -24px;
    right: 0;
    width: 25px;
    height: 25px;
    background-color: transparent;
    border-top-right-radius: 20px;
    box-shadow: 10px -10px 0 0 white;
    z-index: 2;
    display: block;
}

/* Ensure icon in active menu is colored correctly */
.menu-item.active .menu-icon svg {
    fill: #111827;
}

.menu-content {
    margin-left: 15px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 0;
    position: relative;
    z-index: 10;
    height: 100%;
}

.menu-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 3px;
}

.sidebar-collapsed .menu-icon {
    margin-bottom: 0;
}

.menu-text {
    white-space: nowrap;
    transition: opacity 0.3s ease;
    text-align: center;
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1;
    width: 100%;
}

/* Main Content Styles */
.main-content {
    flex-grow: 1;
    margin-left: 0;
    transition: margin-left 0.3s ease, width 0.3s ease;
    display: flex;
    flex-direction: column;
    width: 100%;
}

.main-content-expanded {
    margin-left: 0;
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
    background-color: #81C4F6;
    color: white;
    padding: 0.5rem 1.5rem;
    width: 100%;
    height: 120px;
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
    height: 110px;
    margin-left: 30px;
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
    margin-right: 2rem;
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
@media (min-width: 1025px) {
    .sidebar-open {
        transform: translateX(0);
        width: 130px;
        min-width: 130px;
        opacity: 1;
        visibility: visible;
    }

    .main-content {
        margin-left: 0;
    }

    .main-content-expanded {
        margin-left: 0;
    }
}

/* When sidebar is open, adjust the main content margin on desktop */
@media (min-width: 1025px) {
    .sidebar-open+.main-content {
        margin-left: 130px;
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

/* Adjust margin for Dashboard menu item specifically */
.menu-item[href="/"] {
    margin-top: 2rem;
}

/* Add helper class for SVG icons */
.menu-icon svg {
    width: 24px;
    height: 24px;
    fill: currentColor;
}

/* When sidebar is open, set appropriate margin */
.sidebar-open+.main-content {
    width: calc(100% - 130px);
}
</style>
