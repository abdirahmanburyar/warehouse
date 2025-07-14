<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { Bar } from 'vue-chartjs';
import dayjs from 'dayjs';

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
    userCountCard: {
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
    warehouseDataType: {
        type: String,
        required: true,
        default: 'qty_issued'
    },
    warehouseChartData: {
        type: Array,
        required: true,
        default: () => []
    }
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

// Filtered total cost based on date range
const filteredTotalCost = computed(() => {
    // This is a placeholder - you'll need to implement the actual filtering logic
    // based on your backend data structure
    // For now, we'll return the original value
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
        supplier: selectedSupplier.value?.value || '' // Changed from 'all' to ''
    };
    
    console.log('Filters changed:', filters);
    
    // Here you can make an API call to update the dashboard data based on filters
    // Example:
    // router.get('/dashboard', { 
    //     data: filters,
    //     preserveState: true,
    //     preserveScroll: true
    // });
};

const warehouseDataTypeOptions = [
  { value: 'beginning_balance', label: 'Beginning Balance (This Month)' },
  { value: 'qty_received', label: 'QTY Received (This Month)' },
  { value: 'qty_issued', label: 'QTY Issued (This Month)' },
  { value: 'closing_balance', label: 'Closing Balance (This Month)' },
];
const warehouseDataType = ref('qty_issued');
const months = Array.from({ length: 12 }, (_, i) =>
  dayjs().subtract(i, 'month').format('YYYY-MM')
);
const issuedMonth = ref(months[0]);

// Example: Replace this with real data from props or API
const allWarehouseData = {
  qty_issued: {
    [months[0]]: props.warehouseChartData,
    // Add more months as needed
  },
  // Add other types as you implement them
};

const warehouseChartData = computed(() => {
  const type = warehouseDataType.value;
  const month = issuedMonth.value;
  const data = allWarehouseData[type]?.[month] || [];
  return {
    labels: data.map(d => d.product),
    datasets: [
      {
        label: warehouseDataTypeOptions.find(opt => opt.value === type)?.label,
        backgroundColor: '#6366f1',
        data: data.map(d => d.quantity),
      },
    ],
  };
});

const issuedChartOptions = {
    responsive: true,
    plugins: {
        legend: { display: false },
        tooltip: { enabled: true },
    },
    scales: {
        y: { beginAtZero: true }
    }
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout title="Dashboard" description="Welcome to the dashboard">
        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-row items-center gap-x-4 flex-nowrap">
                <!-- Date From Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-600">Date From:</label>
                    <input
                        type="date"
                        v-model="dateFrom"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm min-w-[150px]"
                    />
                </div>
                <!-- Date To Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-600">Date To:</label>
                    <input
                        type="date"
                        v-model="dateTo"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm min-w-[150px]"
                    />
                </div>
                <!-- Order Type Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-600">Order Type:</label>
                    <Multiselect
                        v-model="selectedOrderType"
                        :options="orderTypeOptions"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        placeholder="All Order Types"
                        class="min-w-[200px]"
                    />
                </div>
                <!-- Supplier Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-medium text-gray-600">Supplier:</label>
                    <Multiselect
                        v-model="selectedSupplier"
                        :options="props.loadSuppliers"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        label="label"
                        track-by="value"
                        placeholder="All Suppliers"
                        class="min-w-[200px]"
                    />
                </div>
                <!-- Apply Filters Button -->
                <div>
                    <button
                        @click="handleFilterChange"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-row gap-3 mb-6">
            <!-- Product Category Card -->
            <div class="flex-1 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-blue-600/5"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-gray-900">List of Categories</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">Drugs</div>
                            <div class="text-sm font-bold text-blue-600">{{ productCategoryCard.Drugs || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">Consumable</div>
                            <div class="text-sm font-bold text-blue-600">{{ productCategoryCard.Consumable || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">Lab</div>
                            <div class="text-sm font-bold text-blue-600">{{ productCategoryCard.Lab || 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouse/Facilities Card -->
            <div class="flex-1 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-emerald-600/5"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-gray-900">Warehouse/Facilities</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-6 gap-1">
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">WH</div>
                            <div class="text-sm font-bold text-emerald-600">{{ warehouseCountCard || 0 }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">HC</div>
                            <div class="text-sm font-bold text-emerald-600">{{ getCount('HC') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">PHU</div>
                            <div class="text-sm font-bold text-emerald-600">{{ getCount('PHU') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">RH</div>
                            <div class="text-sm font-bold text-emerald-600">{{ getCount('RH') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">DH</div>
                            <div class="text-sm font-bold text-emerald-600">{{ getCount('DH') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600">MT</div>
                            <div class="text-sm font-bold text-emerald-600">5</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Card -->
            <div class="flex-1 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-amber-600/5"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-amber-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-gray-900">Orders</h3>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-600">{{ orderLabels[selectedOrderTypeValue] }}</div>
                        <div class="text-sm font-bold text-amber-600">{{ orderCounts[selectedOrderTypeValue] || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Transfers Received Card -->
            <div class="flex-1 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-teal-500/10 to-teal-600/5"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-gray-900">Transfers</h3>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-600">Received</div>
                        <div class="text-sm font-bold text-teal-600">{{ transferReceivedCard || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Users Card -->
            <div class="flex-1 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-indigo-600/5"></div>
                <div class="relative p-3">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                            <h3 class="text-sm font-semibold text-gray-900">Users</h3>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm font-bold text-indigo-600">{{ userCountCard || 0 }}</div>
                    </div>
                </div>
            </div>


        </div>
        <!-- Tabs, Total Cost, and Order Statistics Row -->
        <div class="flex justify-between items-start">
            <!-- Tabs Section -->
            <div class="flex-1 mr-8">
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
                    <div v-if="activeTab === 'warehouse'" class="space-y-6">
                        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
                                <div class="flex gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Months</label>
                                        <input type="month" v-model="issuedMonth" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Filters</label>
                                        <Multiselect
                                            v-model="warehouseDataType"
                                            :options="warehouseDataTypeOptions"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :show-labels="false"
                                            placeholder="Select Data Type"
                                            class="w-full"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="h-80">
                                <Bar :data="warehouseChartData" :options="issuedChartOptions" />
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

            <!-- Total Cost and Fulfillment Cards -->
            <div class="flex flex-col space-y-3 mx-4">
                <!-- Total Cost Card -->
                <div class="w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-green-600/5"></div>
                    <div class="relative p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <h3 class="text-sm font-semibold text-gray-900">Total Cost</h3>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">Approved PO</div>
                            <div class="text-base font-bold text-green-600">${{ (filteredTotalCost || 0).toLocaleString() }}</div>
                        </div>
                    </div>
                </div>

                <!-- Fulfillment Percentage Card -->
                <div class="w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/10 to-cyan-600/5"></div>
                    <div class="relative p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-cyan-500 rounded-full"></div>
                                <h3 class="text-sm font-semibold text-gray-900">Fulfillment</h3>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">Rate</div>
                            <div class="text-base font-bold text-cyan-600">{{ (filteredFulfillment || 0).toFixed(1) }}%</div>
                        </div>
                    </div>
                </div>

                <!-- Orders Delayed Card -->
                <div class="w-48 group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500/10 to-rose-600/5"></div>
                    <div class="relative p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-rose-500 rounded-full"></div>
                                <h3 class="text-sm font-semibold text-gray-900">Delayed</h3>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-xs font-medium text-gray-600 mb-1">Orders</div>
                            <div class="text-base font-bold text-rose-600">{{ ordersDelayedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Statistics Section -->
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
        </div>
    </AuthenticatedLayout>
</template>

