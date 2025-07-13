<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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

const selectedOrderType = ref(props.orderCard.filter);
const orderCounts = computed(() => props.orderCard.counts);
const orderLabels = {
    PO: 'Purchase Order (Approved)',
    PKL: 'Packing List',
    BO: 'Back Order'
};

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

// Fulfillment filter
const selectedSupplier = ref('all');
const supplierOptions = computed(() => {
    const options = [
        { value: 'all', label: 'Suppliers' }
    ];
    props.fulfillmentData.forEach(item => {
        options.push({
            value: item.supplier_name,
            label: item.supplier_name
        });
    });
    return options;
});

const filteredFulfillment = computed(() => {
    const selectedValue = selectedSupplier.value?.value || selectedSupplier.value;
    
    if (selectedValue === 'all') {
        return props.overallFulfillment || 0;
    }
    const supplierData = props.fulfillmentData.find(item => item.supplier_name === selectedValue);
    return supplierData ? supplierData.fulfillment_percentage : 0;
});

const selectedSupplierLabel = computed(() => {
    if (selectedSupplier.value === 'all') {
        return 'Suppliers';
    }
    return selectedSupplier.value;
});

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
        <div class="flex flex-row justify-end gap-1">
            <!-- Product Category Card -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-pink-50 to-pink-100 rounded-2xl border border-pink-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-3 uppercase">
                    List
                </div>
                <div class="flex w-full justify-between mb-1 text-[10px]">
                    <div class="flex-1 text-center font-medium text-gray-600">Drugs</div>
                    <div class="flex-1 text-center font-medium text-gray-600">Consumable</div>
                    <div class="flex-1 text-center font-medium text-gray-600">Lab</div>
                </div>
                <div class="flex w-full justify-between text-[10px]">
                    <div class="flex-1 text-center font-bold text-pink-600 text-base">{{ productCategoryCard.Drugs || 0 }}</div>
                    <div class="flex-1 text-center font-bold text-pink-600 text-base">{{ productCategoryCard.Consumable || 0 }}</div>
                    <div class="flex-1 text-center font-bold text-pink-600 text-base">{{ productCategoryCard.Lab || 0 }}</div>
                </div>
            </div>
            <!-- Facilities Card -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-3 uppercase">
                    Facilities
                </div>
                <div class="flex w-full justify-between mb-1 text-[10px]">
                    <div class="flex-1 text-center font-medium text-gray-600">HC</div>
                    <div class="flex-1 text-center font-medium text-gray-600">PHU</div>
                    <div class="flex-1 text-center font-medium text-gray-600">RH</div>
                    <div class="flex-1 text-center font-medium text-gray-600">DH</div>
                    <div class="flex-1 text-center font-medium text-gray-600">MT</div>
                </div>
                <div class="flex w-full justify-between text-[10px]">
                    <div class="flex-1 text-center font-bold text-blue-700 text-base">{{ getCount('HC') }}</div>
                    <div class="flex-1 text-center font-bold text-blue-700 text-base">{{ getCount('PHU') }}</div>
                    <div class="flex-1 text-center font-bold text-blue-700 text-base">{{ getCount('RH') }}</div>
                    <div class="flex-1 text-center font-bold text-blue-700 text-base">{{ getCount('DH') }}</div>
                    <div class="flex-1 text-center font-bold text-blue-700 text-base">5</div>
                </div>
            </div>
            <!-- Order Card with Filter -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-green-50 to-green-100 rounded-2xl border border-green-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="mb-1 w-full">
                    <select v-model="selectedOrderType" class="w-full border-none rounded px-2 py-1 text-[10px]">
                        <option value="PO">Purchase Order</option>
                        <option value="PKL">Packing List</option>
                        <option value="BO">Back Order</option>
                    </select>
                </div>
                <div class="text-[10px] font-semibold text-center text-gray-500 tracking-widest mb-1 uppercase">
                    {{ orderLabels[selectedOrderType] }}
                </div>
                <div class="text-base font-bold text-green-700">
                    {{ orderCounts[selectedOrderType] || 0 }}
                </div>
            </div>
            <!-- Transfers Received Card -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl border border-yellow-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-1 uppercase">
                    Transfers Received
                </div>
                <div class="text-base font-bold text-yellow-700">
                    {{ transferReceivedCard || 0 }}
                </div>
            </div>
            <!-- Users Card -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl border border-purple-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-1 uppercase">
                    Users
                </div>
                <div class="text-base font-bold text-purple-700">
                    {{ userCountCard || 0 }}
                </div>
            </div>
            <!-- Warehouses Card -->
            <div class="w-full max-w-sm min-h-[120px] bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-2xl border border-indigo-100 px-5 py-2 flex flex-col items-center mb-8">
                <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-1 uppercase">
                    Warehouses
                </div>
                <div class="text-base font-bold text-indigo-700">
                    {{ warehouseCountCard || 0 }}
                </div>
            </div>
        </div>
        <!-- Tabs, Total Cost, and Order Statistics Row -->
        <div class="flex justify-between items-start -mt-4">
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
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Month</label>
                                        <Multiselect
                                            v-model="requestData"
                                            :options="['QTY Issued', 'QTY Received', 'Beggining Balance', 'Closing Balance']"
                                            :searchable="true"
                                            :close-on-select="true"
                                            :show-labels="false"
                                            placeholder="Select Month"
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
            <div class="flex flex-col space-y-4 mx-4">
                <!-- Total Cost Card -->
                <div class="w-48 min-h-[80px] bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl border border-emerald-100 px-5 py-2 flex flex-col items-center">
                    <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-1 uppercase">
                        Total Cost
                    </div>
                    <div class="text-[10px] font-semibold text-center text-gray-500 tracking-widest mb-1 uppercase">
                        Approved PO
                    </div>
                    <div class="text-base font-bold text-emerald-700">
                        ${{ (totalApprovedPOCost || 0).toLocaleString() }}
                    </div>
                </div>

                <!-- Fulfillment Percentage Card -->
                <div class="w-48 min-h-[80px] bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border border-orange-100 px-5 py-2 flex flex-col items-center">
                    <div class="mb-1 w-full">
                        <Multiselect
                            v-model="selectedSupplier"
                            :options="supplierOptions"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Suppliers"
                            track-by="value"
                            label="label"
                            class="text-xs"
                        />
                    </div>
                    <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-1 uppercase">
                        Fulfillment Rate
                    </div>
                    <div class="text-base font-bold text-orange-700">
                        {{ (filteredFulfillment || 0).toFixed(1) }}%
                    </div>
                </div>

                <!-- Orders Delayed Card -->
                <div class="w-48 min-h-[80px] bg-gradient-to-br from-red-50 to-red-100 rounded-2xl border border-red-100 px-5 py-2 flex flex-col items-center">
                    <div class="text-[10px] font-semibold text-gray-500 tracking-widest mb-3 uppercase">
                        Orders Delayed
                    </div>
                    <div class="text-base font-bold text-red-700">
                        {{ ordersDelayedCount }}
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
