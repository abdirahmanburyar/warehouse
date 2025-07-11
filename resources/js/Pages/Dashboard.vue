<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import ProductCategoriesChart from '@/Components/Charts/ProductCategoriesChart.vue';
import Chart from 'chart.js/auto';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
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
    },
    tracertableData: {
        type: Object,
        default: () => ({
            summary: {
                totalItems: 0,
                warehouseItems: 0,
                facilityItems: 0,
                totalQuantity: 0
            },
            warehouseItems: [],
            facilityItems: [],
            facilities: [],
            warehouseChartData: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                data: [0, 0, 0]
            },
            facilityChartData: {
                labels: [],
                data: []
            }
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

// Tracertable Items Dashboard
const activeTab = ref('warehouse');
const selectedFacility = ref(null);
const warehouseChart = ref(null);
const facilityChart = ref(null);
let warehouseChartInstance = null;
let facilityChartInstance = null;

// Initialize warehouse chart
const initWarehouseChart = () => {
    if (warehouseChartInstance) {
        warehouseChartInstance.destroy();
    }

    if (!warehouseChart.value) return;

    const ctx = warehouseChart.value.getContext('2d');
    warehouseChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.tracertableData.warehouseChartData.labels || ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: props.tracertableData.warehouseChartData.data || [0, 0, 0],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(251, 191, 36, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            }
        }
    });
};

// Initialize facility chart
const initFacilityChart = () => {
    if (facilityChartInstance) {
        facilityChartInstance.destroy();
    }

    if (!facilityChart.value) return;

    // Use filtered data for the chart
    const chartData = selectedFacility.value 
        ? filteredFacilityItems.value 
        : props.tracertableData.facilityItems || [];
    
    // Group by facility and sum quantities
    const facilityData = chartData.reduce((acc, item) => {
        acc[item.facility_name] = (acc[item.facility_name] || 0) + item.available_quantity;
        return acc;
    }, {});
    
    const sortedData = Object.entries(facilityData)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 10); // Limit to top 10

    const ctx = facilityChart.value.getContext('2d');
    facilityChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: sortedData.map(([name]) => name),
            datasets: [{
                label: 'Available Quantity',
                data: sortedData.map(([, quantity]) => quantity),
                backgroundColor: 'rgba(147, 51, 234, 0.8)',
                borderColor: 'rgba(147, 51, 234, 1)',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(0, 0, 0, 0.7)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(0, 0, 0, 0.7)'
                    }
                }
            }
        }
    });
};

// Watch for tab changes to reinitialize charts
watch(activeTab, (newTab) => {
    if (newTab === 'warehouse') {
        setTimeout(() => {
            if (warehouseChart.value) {
                initWarehouseChart();
            }
        }, 100);
    } else if (newTab === 'facilities') {
        setTimeout(() => {
            if (facilityChart.value) {
                initFacilityChart();
            }
        }, 100);
    }
});

// Watch for facility selection changes
watch(selectedFacility, () => {
    if (activeTab.value === 'facilities' && facilityChart.value) {
        setTimeout(() => {
            initFacilityChart();
        }, 100);
    }
});

onMounted(() => {
    // Initialize charts after component is mounted
    setTimeout(() => {
        if (warehouseChart.value) {
            initWarehouseChart();
        }
        if (facilityChart.value) {
            initFacilityChart();
        }
    }, 100);
});

// Computed property to filter facility items based on selected facility
const filteredFacilityItems = computed(() => {
    if (!selectedFacility.value) {
        return props.tracertableData.facilityItems || [];
    }
    
    return (props.tracertableData.facilityItems || []).filter(item => {
        // Find the facility by name and check if it matches the selected facility
        const facility = props.tracertableData.facilities.find(f => f.name === item.facility_name);
        return facility && facility.id === selectedFacility.value.value;
    });
});
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

            <!-- Tracertable Items Dashboard Section -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Tracertable Items Dashboard</h3>
                            <p class="text-sm text-gray-500">Monitor and track tracertable items across warehouses and facilities</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards for Tracertable Items -->
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Total Tracertable Items -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Tracertable Items</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ tracertableData.summary.totalItems || 0 }}</p>
                                </div>
                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Warehouse Items -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Warehouse Items</p>
                                    <p class="text-2xl font-bold text-green-600">{{ tracertableData.summary.warehouseItems || 0 }}</p>
                                </div>
                                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Facility Items -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Facility Items</p>
                                    <p class="text-2xl font-bold text-purple-600">{{ tracertableData.summary.facilityItems || 0 }}</p>
                                </div>
                                <div class="flex items-center justify-center w-10 h-10 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Total Quantity -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Total Quantity</p>
                                    <p class="text-2xl font-bold text-orange-600">{{ tracertableData.summary.totalQuantity || 0 }}</p>
                                </div>
                                <div class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-lg">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="activeTab = 'warehouse'"
                            :class="[
                                activeTab === 'warehouse'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200'
                            ]"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span>Warehouse Items</span>
                            </div>
                        </button>
                        <button
                            @click="activeTab = 'facilities'"
                            :class="[
                                activeTab === 'facilities'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200'
                            ]"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Facility Items</span>
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Warehouse Tab -->
                    <div v-if="activeTab === 'warehouse'" class="space-y-6">
                        <!-- Warehouse Chart -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Warehouse Inventory Overview</h3>
                            <div class="h-80">
                                <canvas ref="warehouseChart" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- Warehouse Items Table -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Warehouse Tracertable Items</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in tracertableData.warehouseItems" :key="item.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ item.product_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.category_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ item.available_quantity }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    item.available_quantity > 10 ? 'bg-green-100 text-green-800' : 
                                                    item.available_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                                                ]">
                                                    {{ item.available_quantity > 10 ? 'In Stock' : item.available_quantity > 0 ? 'Low Stock' : 'Out of Stock' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="tracertableData.warehouseItems.length === 0">
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No warehouse tracertable items found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Facilities Tab -->
                    <div v-if="activeTab === 'facilities'" class="space-y-6">
                        <!-- Facility Filter -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center space-x-4">
                                <label class="text-sm font-medium text-gray-700">Filter by Facility:</label>
                                <Multiselect
                                    v-model="selectedFacility"
                                    :options="tracertableData.facilities.map(f => ({ value: f.id, label: f.name }))"
                                    placeholder="Select a facility"
                                    track-by="value"
                                    label="label"
                                    class="w-full"
                                ></Multiselect>
                            </div>
                        </div>

                        <!-- Facility Chart -->
                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Facility Inventory Overview</h3>
                            <div class="h-80">
                                <canvas ref="facilityChart" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- Facility Items Table -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Facility Tracertable Items</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in filteredFacilityItems" :key="item.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ item.product_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.facility_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ item.available_quantity }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    item.available_quantity > 10 ? 'bg-green-100 text-green-800' : 
                                                    item.available_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                                                ]">
                                                    {{ item.available_quantity > 10 ? 'In Stock' : item.available_quantity > 0 ? 'Low Stock' : 'Out of Stock' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredFacilityItems.length === 0">
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No facility tracertable items found</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
