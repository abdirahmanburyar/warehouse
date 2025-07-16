<template>
    <AuthenticatedLayout title="Tracertable Items Dashboard" description="Track and monitor tracertable items across warehouses and facilities">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <!-- Dashboard Icon -->
                        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Tracertable Items Dashboard</h1>
                    </div>
                    <p class="text-gray-600 text-sm">Monitor and track tracertable items across warehouses and facilities</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Tracertable Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Tracertable Items</p>
                        <p class="text-3xl font-bold text-blue-600">{{ summary.totalItems || 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Warehouse Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Warehouse Items</p>
                        <p class="text-3xl font-bold text-green-600">{{ summary.warehouseItems || 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Facility Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Facility Items</p>
                        <p class="text-3xl font-bold text-purple-600">{{ summary.facilityItems || 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Quantity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Quantity</p>
                        <p class="text-3xl font-bold text-orange-600">{{ summary.totalQuantity || 0 }}</p>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-14 0h14"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <div class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Warehouse Tracertable Items</span>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Warehouse Content -->
            <div class="space-y-6">
                <!-- Inventory Report Options -->
                <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Inventory Report Type:</label>
                        <select v-model="warehouseDataType" @change="handleInventoryData" class="block w-48 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="beginning_balance">Beginning Balance</option>
                            <option value="received_quantity">Quantity Received</option>
                            <option value="issued_quantity">Quantity Issued</option>
                            <option value="closing_balance">Closing Balance</option>
                        </select>
                        <label class="text-sm font-medium text-gray-700">Month:</label>
                        <select v-model="selectedMonth" @change="handleInventoryData" class="block w-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
                        </select>
                    </div>
                </div>

                <!-- Inventory Chart -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Tracertable Items Inventory Overview</h3>
                    <div class="h-80 relative">
                        <!-- Loading State -->
                        <div v-if="isLoadingInventoryChart" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <span class="text-gray-600">Loading inventory data...</span>
                            </div>
                        </div>
                        
                        <!-- Error State -->
                        <div v-else-if="inventoryChartError" class="absolute inset-0 flex items-center justify-center bg-red-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-red-600 font-medium">{{ inventoryChartError }}</div>
                                <button @click="handleInventoryData" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    Retry
                                </button>
                            </div>
                        </div>
                        
                        <!-- Chart -->
                        <div v-else class="h-full">
                            <Bar :data="inventoryChartData" :options="inventoryChartOptions" />
                        </div>
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
                                    <tr v-for="item in warehouseItems" :key="item.id" class="hover:bg-gray-50">
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import Chart from 'chart.js/auto';
import { Bar } from 'vue-chartjs';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            totalItems: 0,
            warehouseItems: 0,
            facilityItems: 0,
            totalQuantity: 0
        })
    },
    warehouseItems: {
        type: Array,
        default: () => []
    },
    facilityItems: {
        type: Array,
        default: () => []
    },
    facilities: {
        type: Array,
        default: () => []
    },
    warehouseChartData: {
        type: Object,
        default: () => ({})
    },
    facilityChartData: {
        type: Object,
        default: () => ({})
    }
});

const activeTab = ref('warehouse');
const warehouseChart = ref(null);
let warehouseChartInstance = null;

// New inventory report chart functionality
const warehouseDataType = ref('beginning_balance');
const months = Array.from({ length: 12 }, (_, i) =>
  dayjs().subtract(i, 'month').format('YYYY-MM')
);
const selectedMonth = ref(months[0]);

// Chart data state for inventory reports
const inventoryChartData = ref({
    labels: [],
    datasets: [{
        label: 'Quantity',
        data: [],
        backgroundColor: [],
        borderColor: [],
        borderWidth: 2
    }]
});

const isLoadingInventoryChart = ref(false);
const inventoryChartError = ref(null);

// Watch for changes to reload inventory chart data
watch([
    () => warehouseDataType.value,
    () => selectedMonth.value
], () => {
    handleInventoryData();
});

// Load inventory data from the tracertable items API
async function handleInventoryData() {
    if (activeTab.value !== 'warehouse') return;
    
    isLoadingInventoryChart.value = true;
    inventoryChartError.value = null;
    
    const query = {
        type: warehouseDataType.value || 'beginning_balance',
        month: selectedMonth.value
    };
    
    try {
        const response = await axios.post(route('dashboard.warehouse.tracert-items'), query);
        
        if (response.data.success) {
            updateInventoryChartData(response.data.chartData);
        } else {
            inventoryChartError.value = response.data.message || 'Failed to load inventory chart data';
            setEmptyInventoryChart();
        }
    } catch (error) {
        console.error('Error fetching tracertable inventory data:', error);
        inventoryChartError.value = 'Network error occurred while loading inventory data';
        setEmptyInventoryChart();
    } finally {
        isLoadingInventoryChart.value = false;
    }
}

// Update inventory chart data
function updateInventoryChartData(chartData) {
    if (!chartData || !chartData.labels || !chartData.data) {
        setEmptyInventoryChart();
        return;
    }

    inventoryChartData.value = {
        labels: chartData.labels,
        datasets: [{
            label: getDataTypeLabel(warehouseDataType.value),
            data: chartData.data,
            backgroundColor: chartData.backgroundColors || generateColors(chartData.data.length, true),
            borderColor: chartData.borderColors || generateColors(chartData.data.length, false),
            borderWidth: 2
        }]
    };
}

// Set empty inventory chart
function setEmptyInventoryChart() {
    inventoryChartData.value = {
        labels: ['No Data Available'],
        datasets: [{
            label: 'Quantity',
            data: [0],
            backgroundColor: ['rgba(156, 163, 175, 0.8)'],
            borderColor: ['rgba(156, 163, 175, 1)'],
            borderWidth: 2
        }]
    };
}

// Get human-readable label for data type
function getDataTypeLabel(type) {
    const labels = {
        'beginning_balance': 'Beginning Balance',
        'received_quantity': 'Quantity Received',
        'issued_quantity': 'Quantity Issued',
        'closing_balance': 'Closing Balance'
    };
    return labels[type] || 'Quantity';
}

// Generate colors for chart
function generateColors(count, isBackground = true) {
    const baseColors = [
        'rgba(59, 130, 246, ' + (isBackground ? '0.8)' : '1)'), // Blue
        'rgba(16, 185, 129, ' + (isBackground ? '0.8)' : '1)'), // Green
        'rgba(245, 158, 11, ' + (isBackground ? '0.8)' : '1)'), // Yellow
        'rgba(239, 68, 68, ' + (isBackground ? '0.8)' : '1)'),   // Red
        'rgba(147, 51, 234, ' + (isBackground ? '0.8)' : '1)'), // Purple
        'rgba(236, 72, 153, ' + (isBackground ? '0.8)' : '1)'), // Pink
        'rgba(14, 165, 233, ' + (isBackground ? '0.8)' : '1)'), // Sky
        'rgba(34, 197, 94, ' + (isBackground ? '0.8)' : '1)'),  // Emerald
        'rgba(168, 85, 247, ' + (isBackground ? '0.8)' : '1)'), // Violet
        'rgba(251, 191, 36, ' + (isBackground ? '0.8)' : '1)')  // Amber
    ];
    
    const colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
    }
    return colors;
}

// Inventory chart options
const inventoryChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { 
            display: false 
        },
        tooltip: { 
            enabled: true,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleColor: 'white',
            bodyColor: 'white',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            callbacks: {
                label: function(context) {
                    return context.parsed.y.toLocaleString();
                }
            }
        }
    },
    scales: {
        y: { 
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return value.toLocaleString();
                }
            }
        },
        x: {
            ticks: {
                maxRotation: 45,
                minRotation: 0
            }
        }
    }
};

// Load facility data when facility filter changes
const loadFacilityData = () => {
    console.log('Loading facility data for:', selectedFacility.value);
};

// Initialize warehouse chart (existing doughnut chart)
const initWarehouseChart = () => {
    if (warehouseChartInstance) {
        warehouseChartInstance.destroy();
    }

    const ctx = warehouseChart.value.getContext('2d');
    warehouseChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.warehouseChartData.labels || ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: props.warehouseChartData.data || [65, 20, 15],
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

    const ctx = facilityChart.value.getContext('2d');
    facilityChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.facilityChartData.labels || ['Facility 1', 'Facility 2', 'Facility 3', 'Facility 4'],
            datasets: [{
                label: 'Available Quantity',
                data: props.facilityChartData.data || [120, 85, 95, 110],
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

// Watch for tab changes to reinitialize charts and load data
watch(activeTab, (newTab) => {
    if (newTab === 'warehouse') {
        setTimeout(() => {
            if (warehouseChart.value) {
                initWarehouseChart();
            }
            // Load inventory data when switching to warehouse tab
            handleInventoryData();
        }, 100);
    }
});

onMounted(() => {
    // Initialize charts after component is mounted
    setTimeout(() => {
        if (warehouseChart.value) {
            initWarehouseChart();
        }
    }, 100);
    
    // Load initial inventory data
    handleInventoryData();
});
</script>

<style scoped>
/* Custom styles for better visual appeal */
.bg-gradient-to-r {
    background: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Smooth transitions */
.transition-shadow {
    transition: box-shadow 0.2s ease-in-out;
}

.transition-colors {
    transition: color 0.2s ease-in-out;
}

/* Hover effects */
.hover\:shadow-md:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.hover\:bg-gray-50:hover {
    background-color: rgba(249, 250, 251, 1);
}
</style> 