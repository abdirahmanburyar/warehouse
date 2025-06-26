<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProductCategoriesChart from '@/Components/Charts/ProductCategoriesChart.vue';
import { 
    BuildingOfficeIcon, 
    UserGroupIcon, 
    DocumentTextIcon, 
    ShoppingCartIcon, 
    ArrowUpTrayIcon, 
    ArrowDownTrayIcon,
    CubeIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    dashboardData: {
        type: Object,
        required: true,
        default: () => ({
            summary: [],
            order_stats: {},
            tasks: [],
            recommended_actions: [],
            product_status: []
        })
    }
});

const orderStats = computed(() => {
    if (!props.dashboardData || !props.dashboardData.order_stats) {
        return [];
    }
    
    const stats = props.dashboardData.order_stats;
    return [
        { label: 'Pending', value: stats.pending || 0, color: 'bg-amber-500' },
        { label: 'Review', value: stats.pending_review || 0, color: 'bg-yellow-500' },
        { label: 'Approval', value: stats.pending_approval || 0, color: 'bg-orange-500' },
        { label: 'In Process', value: stats.in_process || 0, color: 'bg-blue-500' },
        { label: 'Dispatched', value: stats.dispatched || 0, color: 'bg-indigo-500' },
        { label: 'Delivered', value: stats.delivered || 0, color: 'bg-green-500' },
        { label: 'Rejected', value: stats.rejected || 0, color: 'bg-red-500' },
        { label: 'Total', value: stats.total || 0, color: 'bg-gray-500' }
    ];
});

const formatNumber = (num) => {
    return new Intl.NumberFormat().format(num);
};

// Helper methods for card icons and colors
const getIcon = (label) => {
    if (!label) return 'CubeIcon';
    
    const labelLower = label.toLowerCase();
    if (labelLower.includes('facilit')) return 'BuildingOfficeIcon';
    if (labelLower.includes('user')) return 'UserGroupIcon';
    if (labelLower.includes('order') && !labelLower.includes('purchase')) return 'DocumentTextIcon';
    if (labelLower.includes('purchase')) return 'ShoppingCartIcon';
    if (labelLower.includes('issued')) return 'ArrowUpTrayIcon';
    if (labelLower.includes('received')) return 'ArrowDownTrayIcon';
    return 'CubeIcon';
};

const getIconBgColor = (color) => {
    const colors = {
        'blue': 'bg-blue-100',
        'green': 'bg-green-100',
        'red': 'bg-red-100',
        'purple': 'bg-purple-100',
        'indigo': 'bg-indigo-100',
        'teal': 'bg-teal-100'
    };
    return colors[color] || 'bg-gray-100';
};

const getProgressBarBg = (color) => {
    const colors = {
        'blue': 'bg-blue-50',
        'green': 'bg-green-50',
        'red': 'bg-red-50',
        'purple': 'bg-purple-50',
        'indigo': 'bg-indigo-50',
        'teal': 'bg-teal-50'
    };
    return colors[color] || 'bg-gray-50';
};

const getProgressBarColor = (color) => {
    const colors = {
        'blue': 'bg-blue-500',
        'green': 'bg-green-500',
        'red': 'bg-red-500',
        'purple': 'bg-purple-500',
        'indigo': 'bg-indigo-500',
        'teal': 'bg-teal-500'
    };
    return colors[color] || 'bg-gray-500';
};
</script>

<template>
    <Head title="PharmaWare Dashboard" />

    <AuthenticatedLayout title="Pharmaceutical Warehouse" description="Comprehensive Management Dashboard">
      
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Summary Card 1 -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg p-5 border border-blue-100 hover:shadow-xl transition-shadow duration-200 gap-2">
                    <div class="flex items-center gap-2">
                            <div class="p-3 rounded-lg bg-blue-100/50 backdrop-blur-sm shadow-sm">
                            <BuildingOfficeIcon class="h-6 w-6 text-blue-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-700/80">Total Facilities</p>
                            <p class="text-2xl font-bold text-blue-900">{{ dashboardData.summary[0].value || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card 2 -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-5 border border-green-100 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-green-100/50 backdrop-blur-sm shadow-sm">
                            <UserGroupIcon class="h-6 w-6 text-green-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-700/80">Total Users</p>
                            <p class="text-2xl font-bold text-green-900">{{ dashboardData.summary[1].value || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card 3 -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg p-5 border border-purple-100 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-purple-100/50 backdrop-blur-sm shadow-sm">
                            <DocumentTextIcon class="h-6 w-6 text-purple-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-700/80">Total Orders</p>
                            <p class="text-2xl font-bold text-purple-900">{{ dashboardData.summary[2].value || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card 4 -->
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl shadow-lg p-5 border border-amber-100 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-amber-100/50 backdrop-blur-sm shadow-sm">
                            <ShoppingCartIcon class="h-6 w-6 text-amber-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-amber-700/80">Purchase Orders</p>
                            <p class="text-2xl font-bold text-amber-900">{{ dashboardData.summary[3].value || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row of Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-2 mb-3">
                <!-- Summary Card 5 -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-xl shadow-lg p-5 border border-indigo-100 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-indigo-100/50 backdrop-blur-sm shadow-sm">
                            <ArrowUpTrayIcon class="h-6 w-6 text-indigo-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-indigo-700/80">Items Issued</p>
                            <p class="text-2xl font-bold text-indigo-900">{{ dashboardData.summary[4].value || 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Card 6 -->
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl shadow-lg p-5 border border-emerald-100 hover:shadow-xl transition-shadow duration-200">
                    <div class="flex items-center">
                            <div class="p-3 rounded-lg bg-emerald-100/50 backdrop-blur-sm shadow-sm">
                            <ArrowDownTrayIcon class="h-6 w-6 text-emerald-700" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-emerald-700/80">Items Received</p>
                            <p class="text-2xl font-bold text-emerald-900">{{ dashboardData.summary[5].value || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Required Section -->
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 mb-6 shadow-lg hover:shadow-xl transition-shadow duration-200">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Action Required</h3>
                <div v-if="dashboardData?.tasks?.length > 0" class="space-y-3">
                    <div v-for="(task, index) in dashboardData.tasks" :key="index" class="flex items-center justify-between gap-2 bg-white p-3 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2">
                            <div :class="`bg-${task.color}-100 p-2 rounded-full mr-3`">
                                <component :is="getIcon(task.type)" class="h-5 w-5" :class="`text-${task.color}-600`" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ task.count }} {{ task.label }}
                                </p>
                                <p class="text-xs text-gray-500">Action required</p>
                            </div>
                        </div>
                        <Link :href="route(task.route, task.params)" 
                              class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition-colors">
                            {{ task.action }}
                        </Link>
                    </div>
                </div>
                <div v-else class="p-4 text-center text-gray-500">
                    No pending actions required
                </div>
            </div>

            <!-- Order Statistics -->
            <div class="bg-white rounded-lg shadow-lg p-5 mb-6 hover:shadow-xl transition-shadow duration-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Statistics</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    <div v-for="(stat, index) in orderStats" :key="index" class="text-center p-3 border rounded-lg hover:shadow-md transition-shadow gap-2">
                        <div class="text-2xl font-bold text-gray-800 mb-1">{{ formatNumber(stat.value) }}</div>
                        <div class="text-xs font-medium text-gray-500">{{ stat.label }}</div>
                        <div class="mt-2 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full" 
                                :class="stat.color.split(' ')[0] + '-500'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-[100px]">
                <!-- Left Column Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Recent Activities -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl shadow-lg overflow-hidden border border-slate-200 hover:shadow-xl transition-shadow duration-200">
                        <div class="px-6 py-4 bg-white/30 backdrop-blur-sm border-b border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-800">Recent Activities</h3>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-for="(task, index) in dashboardData.tasks" :key="index" class="p-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center" :class="`bg-${task.color}-50`">
                                        <template v-if="task.icon === 'document-text'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="`text-${task.color}-600`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </template>
                                        <template v-else-if="task.icon === 'check-circle'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="`text-${task.color}-600`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </template>
                                        <template v-else-if="task.icon === 'shopping-cart'">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :class="`text-${task.color}-600`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </template>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900">
                                                {{ task.count }} {{ task.label }}
                                            </h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="`bg-${task.color}-100 text-${task.color}-800`">
                                                {{ task.action }} Required
                                            </span>
                                        </div>
                                        <div class="mt-2 flex justify-end">
                                            <a :href="route(task.route, task.params)" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white" :class="`bg-${task.color}-600 hover:bg-${task.color}-700 focus:ring-${task.color}-500`">
                                                {{ task.action }} Now
                                                <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 -mr-0.5 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="Object.keys(dashboardData.tasks || {}).length === 0" class="p-6 text-center text-gray-500">
                                <p>No pending activities</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">


                    <!-- Products by Category Chart -->
                    <ProductCategoriesChart v-if="dashboardData.product_categories" :chartData="dashboardData.product_categories" class="mb-6" />

                    <!-- Quick Actions -->
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl shadow-lg p-5 border border-slate-200 hover:shadow-xl transition-shadow duration-200">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <Link :href="route('supplies.purchase_order')" class="flex flex-col items-center justify-center p-3 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="mt-1 text-xs font-medium">New PO</span>
                            </Link>
                            <Link :href="route('transfers.create')" class="flex flex-col items-center justify-center p-3 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="mt-1 text-xs font-medium">New Transfer</span>
                            </Link>
                            <Link :href="route('products.create')" class="flex flex-col items-center justify-center p-3 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="mt-1 text-xs font-medium">New Product</span>
                            </Link>
                            <Link :href="route('reports.index')" class="flex flex-col items-center justify-center p-3 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="mt-1 text-xs font-medium">Reports</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
