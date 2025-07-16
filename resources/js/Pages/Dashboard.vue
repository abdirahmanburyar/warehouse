<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { Bar } from 'vue-chartjs';
import dayjs from 'dayjs';
import axios from 'axios';

// Register the datalabels plugin
Chart.register(ChartDataLabels);

const props = defineProps({
    dashboardData: {
        type: Object,
        required: true,
        default: () => ({ summary: [] })
    },
    loadSuppliers: {
        type: Array
    },
    orderCard: {
        type: Object,
        required: true,
        default: () => ({ filter: 'PO', counts: { PKL: 0, PO: 0, BO: 0 } })
    },
    productCategoryCard: {
        type: Object,
        required: true,
        default: () => ({ Drugs: 0, Consumable: 0, Lab: 0 })
    },
    transferReceivedCard: {
        type: Number,
        required: true,
        default: 0
    },
    warehouseCountCard: {
        type: Number,
        required: true,
        default: 0
    },
    orderStats: {
        type: Object,
        required: true,
        default: () => ({
            pending: 0, reviewed: 0, approved: 0, in_process: 0, dispatched: 0, received: 0, rejected: 0
        })
    },
    totalApprovedPOCost: {
        type: Number,
        required: true,
        default: 0
    },
    fulfillmentData: {
        type: Array,
        required: true,
        default: () => []
    },
    overallFulfillment: {
        type: Number,
        required: true,
        default: 0
    },
    suppliers: {
        type: Array,
        required: true,
        default: () => []
    },
    ordersDelayedCount: {
        type: Number,
        required: true,
        default: 0
    },
    issuedMonths: Array,
    selectedIssuedMonth: String,
    issuedData: Array,
    warehouseChartData: {
        type: Array,
        required: true,
        default: () => []
    },
    inventoryStatusCounts: {
        type: Array,
        required: false,
        default: () => []
    },
});

function getCount(abbr) {
    const found = props.dashboardData.summary.find(item => item.label === abbr);
    return found ? found.value : 0;
}

// Date filters
const dateFrom = ref(dayjs().startOf('month').format('YYYY-MM-DD'));
const dateTo = ref(dayjs().endOf('month').format('YYYY-MM-DD'));

// Order type filter - convert to object format for Multiselect
const orderTypeOptions = [
    { value: 'PO', label: 'Purchase Order (Approved)' },
    { value: 'PKL', label: 'Packing List' },
    { value: 'BO', label: 'Back Order' }
];

const selectedOrderType = ref(orderTypeOptions.find(opt => opt.value === props.orderCard.filter) || orderTypeOptions[0]);

const orderCounts = computed(() => props.orderCard.counts);
const orderLabels = {
    PO: 'Purchase Order (Approved)',
    PKL: 'Packing List',
    BO: 'Back Order'
};

// Get selected order type value
const selectedOrderTypeValue = computed(() => {
    if (typeof selectedOrderType.value === 'object' && selectedOrderType.value !== null) {
        return selectedOrderType.value.value;
    }
    return selectedOrderType.value;
});

const totalOrders = computed(() =>
    props.orderStats.pending +
    props.orderStats.reviewed +
    props.orderStats.approved +
    props.orderStats.in_process +
    props.orderStats.dispatched +
    props.orderStats.received +
    props.orderStats.rejected +
    props.orderStats.delivered
);

// Tab functionality
const activeTab = ref('warehouse');

const supplierOptions = computed(() => [
  { label: 'All Suppliers', value: '' },
  ...(props.loadSuppliers || []).map(s => ({ label: s, value: s }))
]);
const selectedSupplier = ref(supplierOptions.value[0]);

const filteredFulfillment = computed(() => {
    let selectedValue;
    
    // Handle both object and string values from Multiselect
    if (typeof selectedSupplier.value === 'object' && selectedSupplier.value !== null) {
        selectedValue = selectedSupplier.value.value;
    } else {
        selectedValue = selectedSupplier.value;
    }
    
    if (selectedValue === '' || !selectedValue) { // Changed from 'all' to ''
        return props.overallFulfillment || 0;
    }
    
    const supplierData = props.fulfillmentData.find(item => item.supplier_name === selectedValue);
    return supplierData ? supplierData.fulfillment_percentage : 0;
});

const selectedSupplierLabel = computed(() => {
    let selectedValue;
    
    // Handle both object and string values from Multiselect
    if (typeof selectedSupplier.value === 'object' && selectedSupplier.value !== null) {
        selectedValue = selectedSupplier.value.value;
    } else {
        selectedValue = selectedSupplier.value;
    }
    
    if (selectedValue === '' || !selectedValue) { // Changed from 'all' to ''
        return 'All Suppliers';
    }
    return selectedValue;
});

// Filtered total cost based on supplier selection
const filteredTotalCost = computed(() => {
    let selectedValue;
    
    // Handle both object and string values from Multiselect
    if (typeof selectedSupplier.value === 'object' && selectedSupplier.value !== null) {
        selectedValue = selectedSupplier.value.value;
    } else {
        selectedValue = selectedSupplier.value;
    }
    
    if (selectedValue === '' || !selectedValue) {
        return props.totalApprovedPOCost || 0;
    }
    
    // This would need to be implemented based on your backend data structure
    // For now, we'll return the original value
    // You should filter the total cost based on the selected supplier
    return props.totalApprovedPOCost || 0;
});

// Watch for date changes and emit events to parent component
watch([dateFrom, dateTo], ([newDateFrom, newDateTo]) => {
    // You can emit events here to notify the parent component about date changes
    // This will allow the backend to recalculate the total cost based on the date range
    console.log('Date range changed:', { from: newDateFrom, to: newDateTo });
    
    // Example: You might want to emit an event to the parent component
    // emit('dateRangeChanged', { from: newDateFrom, to: newDateTo });
}, { deep: true });

// Watch for order type changes
watch(selectedOrderType, (newValue) => {
    console.log('Order type changed:', newValue);
    // You can emit events here to notify the parent component about order type changes
}, { deep: true });

// Watch for supplier changes
watch(selectedSupplier, (newValue) => {
    console.log('Supplier changed:', newValue);
    // You can emit events here to notify the parent component about supplier changes
}, { deep: true });

// Method to handle filter changes and update data
const handleFilterChange = () => {
    const filters = {
        dateFrom: dateFrom.value,
        dateTo: dateTo.value,
        orderType: selectedOrderTypeValue.value,
        supplier: getSelectedSupplierValue()
    };
    
    console.log('Filters changed:', filters);
    
    // Make an API call to update the dashboard data based on filters
    router.get('/dashboard', { 
        data: filters,
        preserveState: true,
        preserveScroll: true
    });
};

const warehouseDataType = ref('beginning_balance');
// Available data types: 'beginning_balance','received_quantity','issued_quantity','closing_balance'

// Chart data state
const warehouseChartData = ref([]);
const chartCount = ref(0);

// Computed property to group charts into rows of 3
const chartRows = computed(() => {
    const rows = [];
    for (let i = 0; i < warehouseChartData.value.length; i += 3) {
        rows.push(warehouseChartData.value.slice(i, i + 3));
    }
    return rows;
});

const isLoadingChart = ref(false);
const chartError = ref(null);

const months = Array.from({ length: 12 }, (_, i) =>
  dayjs().subtract(i, 'month').format('YYYY-MM')
);
const issuedMonth = ref(months[1]); // Use previous month as default to match backend

watch([
    () => warehouseDataType.value,
    () => issuedMonth.value
], () => {
    handleTracertItems();
});

// Get human-readable label for data type
function getTypeLabel(type) {
    const labels = {
        'beginning_balance': 'Beginning Balance',
        'received_quantity': 'Quantity Received',
        'issued_quantity': 'Quantity Issued', 
        'closing_balance': 'Closing Balance'
    };
    return labels[type] || 'Quantity';
}

async function handleTracertItems() {
    isLoadingChart.value = true;
    chartError.value = null;
    
    const query = {};
    if (warehouseDataType.value){
        query.type = warehouseDataType.value;
    } else {
        query.type = 'beginning_balance';
    }
    if (issuedMonth.value){
        query.month = issuedMonth.value;
    }

    try {
        const response = await axios.post(route('dashboard.warehouse.tracert-items'), query);
        console.log('API Response:', response.data);
        
        if (response.data.success && response.data.chartData && response.data.chartData.charts) {
            // Handle successful response with multiple charts
            const charts = response.data.chartData.charts;
            warehouseChartData.value = charts.map(chart => ({
                id: chart.id,
                labels: chart.labels || ['No Data'],
                datasets: [{
                    label: getTypeLabel(warehouseDataType.value),
                    data: chart.data || [0],
                    backgroundColor: chart.backgroundColors || ['rgba(156, 163, 175, 0.8)'],
                    borderColor: chart.borderColors || ['rgba(156, 163, 175, 1)'],
                    borderWidth: 2
                }]
            }));
            chartCount.value = response.data.chartData.totalCharts;
            chartError.value = null;
        } else {
            // Handle API success but no data
            chartError.value = response.data.message || 'No data available for the selected period';
            warehouseChartData.value = [{
                id: 1,
                labels: ['No Data'],
                datasets: [{
                    label: 'Quantity',
                    data: [0],
                    backgroundColor: ['rgba(156, 163, 175, 0.8)'],
                    borderColor: ['rgba(156, 163, 175, 1)'],
                    borderWidth: 2
                }]
            }];
            chartCount.value = 1;
        }
    } catch (error) {
        console.error('Error fetching tracert items:', error);
        chartError.value = error.response?.data?.message || 'Network error occurred while loading data';
        // Set empty chart data on error
        warehouseChartData.value = [{
            id: 1,
            labels: ['Error'],
            datasets: [{
                label: 'Quantity',
                data: [0],
                backgroundColor: ['rgba(239, 68, 68, 0.8)'],
                borderColor: ['rgba(239, 68, 68, 1)'],
                borderWidth: 2
            }]
        }];
        chartCount.value = 1;
    } finally {
        isLoadingChart.value = false;
    }
}

// Update chart data based on API response
function updateChartData(chartData) {
    if (!chartData || !chartData.labels || !chartData.data) {
        warehouseChartData.value = {
            labels: ['No Data'],
            datasets: [{
                label: 'Quantity',
                data: [0],
                backgroundColor: ['rgba(156, 163, 175, 0.8)'],
                borderColor: ['rgba(156, 163, 175, 1)'],
                borderWidth: 2
            }]
        };
        return;
    }

    warehouseChartData.value = {
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


const issuedChartOptions = {
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
        },
        datalabels: {
            display: true,
            anchor: 'end',
            align: 'top',
            color: '#374151',
            font: {
                weight: 'bold',
                size: 11
            },
            formatter: function(value, context) {
                return value > 0 ? value.toLocaleString() : '';
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

// Load initial data on component mount
onMounted(() => {
    handleTracertItems();
});

// Helper function to get selected supplier value
const getSelectedSupplierValue = () => {
    if (typeof selectedSupplier.value === 'object' && selectedSupplier.value !== null) {
        return selectedSupplier.value.value;
    }
    return selectedSupplier.value;
};

// Filtered computed properties based on supplier selection
const filteredProductCategoryCard = computed(() => {
    const selectedValue = getSelectedSupplierValue();
    if (selectedValue === '' || !selectedValue) {
        return props.productCategoryCard;
    }
    // This would need to be implemented based on your backend data structure
    // For now, return the original value
    return props.productCategoryCard;
});

const filteredWarehouseCountCard = computed(() => {
    const selectedValue = getSelectedSupplierValue();
    if (selectedValue === '' || !selectedValue) {
        return props.warehouseCountCard;
    }
    // This would need to be implemented based on your backend data structure
    // For now, return the original value
    return props.warehouseCountCard;
});

const filteredOrderCounts = computed(() => {
    const selectedValue = getSelectedSupplierValue();
    if (selectedValue === '' || !selectedValue) {
        return props.orderCard.counts;
    }
    // This would need to be implemented based on your backend data structure
    // For now, return the original value
    return props.orderCard.counts;
});

const filteredTransferReceivedCard = computed(() => {
    const selectedValue = getSelectedSupplierValue();
    if (selectedValue === '' || !selectedValue) {
        return props.transferReceivedCard;
    }
    // This would need to be implemented based on your backend data structure
    // For now, return the original value
    return props.transferReceivedCard;
});

const filteredOrdersDelayedCount = computed(() => {
    const selectedValue = getSelectedSupplierValue();
    if (selectedValue === '' || !selectedValue) {
        return props.ordersDelayedCount;
    }
    // This would need to be implemented based on your backend data structure
    // For now, return the original value
    return props.ordersDelayedCount;
});

const inStockCount = computed(() => props.inventoryStatusCounts.find(s => s.status === 'in_stock')?.count || 0);
const lowStockCount = computed(() => props.inventoryStatusCounts.find(s => s.status === 'low_stock')?.count || 0);
const outOfStockCount = computed(() => props.inventoryStatusCounts.find(s => s.status === 'out_of_stock')?.count || 0);
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout title="Dashboard" description="Welcome to the dashboard">
        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-2">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-x-4">
                <!-- Date From Filter -->
                <div class="flex flex-col w-full sm:w-auto">
                    <label class="text-xs font-medium text-gray-600 mb-1">Date From:</label>
                    <input
                        type="date"
                        v-model="dateFrom"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm w-full sm:w-auto"
                    />
                </div>
                <!-- Date To Filter -->
                <div class="flex flex-col w-full sm:w-auto">
                    <label class="text-xs font-medium text-gray-600 mb-1">Date To:</label>
                    <input
                        type="date"
                        v-model="dateTo"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm w-full sm:w-auto"
                    />
                </div>
                <!-- Order Type Filter -->
                <div class="flex flex-col w-full sm:w-auto">
                    <label class="text-xs font-medium text-gray-600 mb-1">Order Type:</label>
                    <Multiselect
                        v-model="selectedOrderType"
                        :options="orderTypeOptions"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        label="label"
                        track-by="value"
                        placeholder="All Order Types"
                        class="w-full sm:w-auto"
                    />
                </div>
                <!-- Supplier Filter -->
                <div class="flex flex-col w-full sm:w-auto">
                    <label class="text-xs font-medium text-gray-600 mb-1">Supplier:</label>
                    <Multiselect
                        v-model="selectedSupplier"
                        :options="supplierOptions"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        label="label"
                        track-by="value"
                        placeholder="All Suppliers"
                        class="w-full sm:w-auto"
                    />
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <!-- Product Category Card -->
            <div class="w-full sm:w-64 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-amber-400"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Categories</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">Drugs</div>
                            <div class="text-2xs font-bold text-white">{{ filteredProductCategoryCard.Drugs || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">Cons</div>
                            <div class="text-2xs font-bold text-white">{{ filteredProductCategoryCard.Consumable || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">Lab</div>
                            <div class="text-2xs font-bold text-white">{{ filteredProductCategoryCard.Lab || 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouse/Facilities Card -->
            <div class="w-full sm:w-64 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-700 to-gray-300"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">WH/FC</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">WH</div>
                            <div class="text-2xs font-bold text-white">{{ filteredWarehouseCountCard || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">HC</div>
                            <div class="text-2xs font-bold text-white">{{ getCount('HC') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">PHU</div>
                            <div class="text-2xs font-bold text-white">{{ getCount('PHU') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">RH</div>
                            <div class="text-2xs font-bold text-white">{{ getCount('RH') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">DH</div>
                            <div class="text-2xs font-bold text-white">{{ getCount('DH') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90">MT</div>
                            <div class="text-2xs font-bold text-white">5</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Card -->
            <div class="w-full sm:w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-pink-400"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Orders</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xs font-medium text-white opacity-90">{{ orderLabels[selectedOrderTypeValue] }}</div>
                        <div class="text-2xs font-bold text-white">{{ filteredOrderCounts[selectedOrderTypeValue] || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Transfers Received Card -->
            <div class="w-full sm:w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-emerald-400"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Transfers</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xs font-medium text-white opacity-90">Received</div>
                        <div class="text-2xs font-bold text-white">{{ filteredTransferReceivedCard || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Total Cost Card -->
            <div class="w-full sm:w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-700 to-gray-300"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Total Cost</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xs font-medium text-white opacity-90 mb-1">Approved PO</div>
                        <div class="text-2xs font-bold text-white">${{ (filteredTotalCost || 0).toLocaleString() }}</div>
                    </div>
                </div>
            </div>

            <!-- Fulfillment Percentage Card -->
            <div class="w-full sm:w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-pink-400"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Fulfillment</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xs font-medium text-white opacity-90 mb-1">Rate</div>
                        <div class="text-2xs font-bold text-white">{{ (filteredFulfillment || 0).toFixed(1) }}%</div>
                    </div>
                </div>
            </div>

            <!-- Orders Delayed Card -->
            <div class="w-full sm:w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-emerald-400"></div>
                <div class="relative p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-2xs font-semibold text-white">Delayed</h3>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xs font-medium text-white opacity-90 mb-1">Orders</div>
                        <div class="text-2xs font-bold text-white">{{ filteredOrdersDelayedCount }}</div>
                    </div>
                </div>
            </div>

        </div>
        <!-- Tabs, Total Cost, and Order Statistics Row -->
        <div class="flex flex-col lg:flex-row justify-between items-start gap-6">
            <!-- Tabs Section -->
            <div class="flex-1 lg:mr-8 w-full">
                <!-- Tab Navigation -->
                <div class="flex border-b border-gray-200 mb-6">
                    <button
                        @click="activeTab = 'warehouse'"
                        :class="[
                            'px-6 py-3 text-sm font-medium transition-all duration-200 relative',
                            activeTab === 'warehouse'
                                ? 'text-indigo-600 border-b-2 border-indigo-600'
                                : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Warehouse
                    </button>
                    <button
                        @click="activeTab = 'facilities'"
                        :class="[
                            'px-6 py-3 text-sm font-medium transition-all duration-200 relative',
                            activeTab === 'facilities'
                                ? 'text-indigo-600 border-b-2 border-indigo-600'
                                : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        Facilities
                    </button>
                </div>

                <!-- Tab Content -->
                <div class="min-h-[400px]">
                    <!-- Warehouse Tab -->
                    <div v-if="activeTab === 'warehouse'" class="">
                        <div class="bg-white rounded-xl shadow-lg p-1 border border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
                                <div class="flex gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                                        <input type="month" v-model="issuedMonth" class="border border-gray-300 rounded-md px-3 py-2" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Type</label>
                                        <select v-model="warehouseDataType" class="border border-gray-300 rounded-md px-3 py-2 min-w-[180px]">
                                            <option value="beginning_balance">Beginning Balance</option>
                                            <option value="received_quantity">QTY Received</option>
                                            <option value="issued_quantity">QTY Issued</option>
                                            <option value="closing_balance">Closing Balance</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Chart Container -->
                            <div class="relative" :class="chartCount > 1 ? 'min-h-96' : 'h-80'">
                                <!-- Loading State -->
                                <div v-if="isLoadingChart" class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                        <span class="text-gray-600">Loading chart data...</span>
                                    </div>
                                </div>
                                
                                <!-- Error State -->
                                <div v-else-if="chartError" class="absolute inset-0 flex items-center justify-center bg-red-50 rounded-lg">
                                    <div class="text-center">
                                        <div class="text-red-600 font-medium">{{ chartError }}</div>
                                        <button @click="handleTracertItems" class="mt-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Retry
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Charts Grid -->
                                <div v-else class="h-full">
                                    <!-- Single Chart -->
                                    <div v-if="chartCount === 1" class="h-full">
                                        <Bar :data="warehouseChartData[0]" :options="issuedChartOptions" />
                                    </div>
                                    
                                    <!-- Multiple Charts Grid - 3 charts per row -->
                                    <div v-else class="space-y-6">
                                        <div v-for="(chartRow, rowIndex) in chartRows" :key="'row-' + rowIndex" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <div v-for="chart in chartRow" :key="chart.id" class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm">
                                                <div class="h-64">
                                                    <Bar :data="chart" :options="issuedChartOptions" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Facilities Tab -->
                    <div v-if="activeTab === 'facilities'" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Facilities Data</h3>
                            <div class="text-gray-600 text-sm">
                                Facilities information will be displayed here.
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Order Statistics and Stock Status Section -->
            <div class="flex flex-col sm:flex-row items-stretch space-y-4 sm:space-y-0 sm:space-x-6 w-full lg:w-auto">
                <!-- Order Statistics -->
                <div class="flex flex-col items-end">
                    <div class="text-xs font-bold text-gray-700 mb-1 text-end">Order Status</div>
                    <div v-for="status in [
                      { key: 'pending', img: '/assets/images/pending.png', label: 'Pending' },
                      { key: 'reviewed', img: '/assets/images/review.png', label: 'Reviewed' },
                      { key: 'approved', img: '/assets/images/approved.png', label: 'Approved' },
                      { key: 'in_process', img: '/assets/images/inprocess.png', label: 'In Process' },
                      { key: 'dispatched', img: '/assets/images/dispatch.png', label: 'Dispatched' },
                      { key: 'delivered', img: '/assets/images/delivery.png', label: 'Delivered' },
                      { key: 'received', img: '/assets/images/received.png', label: 'Received' },
                      { key: 'rejected', img: '/assets/images/rejected.png', label: 'Rejected' }
                    ]" :key="status.key" class="flex flex-row items-center space-x-2 min-w-[100px]">
                      <img :src="status.img" class="w-8 h-8" :alt="status.label" :title="status.label" />
                      <div>
                        <div class="text-xs font-bold text-gray-900">{{ props.orderStats[status.key] }}</div>
                        <div class="text-[10px] text-gray-600">{{ status.label }}</div>
                      </div>
                    </div>
                </div>

                <!-- Stock Status Cards (Inventory/Index.vue style) -->
                <div class="flex flex-col items-end justify-start h-full">
                    <div class="text-xs font-bold text-gray-700 mb-1 text-end">Stock Status</div>
                    <div class="space-y-2 flex-1 flex flex-col justify-start">
                        <div class="flex items-center rounded-xl bg-green-50 p-1 shadow">
                            <img src="/assets/images/in_stock.png" class="w-[40px] h-[40px]" alt="In Stock" />
                            <div class="ml-4 flex flex-col">
                                <span class="text-sm font-bold text-green-600">{{ inStockCount }}</span>
                                <span class="ml-2 text-xs text-green-600">In Stock</span>
                            </div>
                        </div>
                        <div class="flex items-center rounded-xl bg-orange-50 p-1 shadow">
                            <img src="/assets/images/low_stock.png" class="w-[40px] h-[40px]" alt="Low Stock" />
                            <div class="ml-4 flex flex-col">
                                <span class="text-sm font-bold text-orange-600">{{ lowStockCount }}</span>
                                <span class="ml-2 text-xs text-orange-600">Low Stock</span>
                            </div>
                        </div>
                        <div class="flex items-center rounded-xl bg-red-50 p-1 shadow">
                            <img src="/assets/images/out_stock.png" class="w-[40px] h-[40px]" alt="Out of Stock" />
                            <div class="ml-4 flex flex-col">
                                <span class="text-sm font-bold text-red-600">{{ outOfStockCount }}</span>
                                <span class="ml-2 text-xs text-red-600">Out of Stock</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asset Information Section -->
        <div class="mt-6">
            <div class="text-xs font-bold text-gray-700 mb-3">Asset Information</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3">
                <!-- Asset Types -->
                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Furniture</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">24</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-purple-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">IT Equipment</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">18</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-600 to-green-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Medical Equipment</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">32</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-orange-600 to-orange-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Vehicles</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m-4-6h8m-8 0H4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">12</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-600 to-gray-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Others</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">8</div>
                        </div>
                    </div>
                </div>

                <!-- Asset Status -->
                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-emerald-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">In Use</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">156</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-600 to-yellow-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Needs Maintenance</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">23</div>
                        </div>
                    </div>
                </div>

                <div class="w-full group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-red-400"></div>
                    <div class="relative p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-2xs font-semibold text-white">Disposed</h3>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xs font-medium text-white opacity-90 mb-1">Assets</div>
                            <div class="text-2xs font-bold text-white">7</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

