<template>
    <AuthenticatedLayout 
        title="Order Tracking Report"
        description="Track and monitor order status, fulfillment rates, and inventory allocations across all facilities"
    >
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Order Tracking Report
            </h2>
        </template>

        <!-- Filters -->
        <div class="relative bg-white mb-2 text-xs">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center mb-5">
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1">Facility</label>
                    <Multiselect v-model="filters.facility" :options="facilities" placeholder="Select facilities..."
                        :searchable="true" :close-on-select="true" :clear-on-select="false" :preserve-search="true"
                        class="w-full" />
                </div>
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1">Status</label>
                    <select v-model="filters.status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="in_process">In Process</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="received">Received</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1">Order Date From</label>
                    <input type="date" v-model="filters.date_from"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1">Order Date To</label>
                    <input type="date" v-model="filters.date_to"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-xs font-bold mb-1">Per Page</label>
                    <select v-model="filters.per_page"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mb-2">
                <button @click="clearFilters"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold rounded shadow">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead style="background-color: #F4F7FB;">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tl-lg"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Number</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Facility</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Date</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Allocated QTY</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Received QTY</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Fulfillment</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Handled By</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tr-lg"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr v-if="!orders.data || orders.data.length === 0">
                        <td colspan="9" class="py-12">
                            <div class="flex flex-col items-center justify-center bg-gray-50 rounded-lg p-8">
                                <!-- Professional icon (clipboard/document) -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-4 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002-2h2a2 2 0 012 2M9 5v2a2 2 0 002 2h2a2 2 0 002-2V5" />
                                </svg>
                                <span class="font-bold text-black text-base">No orders found</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-for="order in orders.data" :key="order.id" class="border-b" v-else>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm font-medium text-gray-900">{{ order.order_number }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ order.facility?.name || 'N/A' }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ formatDate(order.order_date) }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <span :class="getStatusClass(order.status)"
                                class="px-2 py-1 text-xs font-medium rounded-full capitalize">
                                {{ formatStatus(order.status) }}
                            </span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ order.tracking_data?.total_allocated || 0 }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ order.tracking_data?.total_received || 0 }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    :style="{ width: (order.tracking_data?.fulfillment_percentage || 0) + '%' }"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ order.tracking_data?.fulfillment_percentage || 0 }}% Fulfilled
                            </div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ order.facility?.handledby?.name || 'N/A' }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <button 
                                @click="viewOrderDetails(order)"
                                class="text-indigo-600 hover:text-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            <TailwindPagination :data="orders" :limit="2" @pagination-change-page="getResults" />
        </div>

        <!-- Order Details Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative mx-auto p-5 border w-full h-full shadow-lg bg-white">
                <div class="h-full flex flex-col">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">
                            Order Details - {{ selectedOrder?.order_number }}
                        </h3>
                        <button 
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Facility:</span>
                                <p class="text-sm text-gray-900">{{ selectedOrder?.facility?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Order Date:</span>
                                <p class="text-sm text-gray-900">{{ formatDate(selectedOrder?.order_date) }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                <span :class="getStatusClass(selectedOrder?.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                    {{ formatStatus(selectedOrder?.status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Total Items:</span>
                                <p class="text-sm text-gray-900">{{ selectedOrder?.items?.length || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items & Allocations Table -->
                    <div class="flex-1 overflow-hidden">
                        <div class="h-full overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty to Release</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UOM</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template v-for="item in selectedOrder?.items" :key="item.id">
                                    <!-- Order Item Row (if no allocations) -->
                                    <tr v-if="!item.inventory_allocations || item.inventory_allocations.length === 0">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ item.product?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.product?.dosage?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.quantity_to_release || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.received_quantity || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.warehouse?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            N/A
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            N/A
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            N/A
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.uom || 'N/A' }}
                                        </td>
                                    </tr>
                                    
                                    <!-- Allocation Rows -->
                                    <template v-else v-for="allocation in item.inventory_allocations" :key="allocation.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ allocation.product?.name || item.product?.name || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.product?.dosage?.name || item.product?.dosage?.name || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.allocated_quantity || 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.received_quantity || 0 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.warehouse?.name || item.warehouse?.name || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.location?.location || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ allocation.batch_number || 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ formatDate(allocation.expiry_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ item.uom || allocation.uom || 'N/A' }}
                                            </td>
                                        </tr>
                                    </template>
                                </template>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="mt-4 flex justify-end">
                        <button 
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { TailwindPagination } from "laravel-vue-pagination"
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import '@/Components/multiselect.css'

const props = defineProps({
    orders: Object,
    filters: Object,
    facilities: Array
})

const showModal = ref(false)
const selectedOrder = ref(null)

const filters = reactive({
    facility: props.filters.facility || [],
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 25,
    page: props.filters.page || 1
})

// Watch for filter changes and automatically request data
watch(filters, (newFilters) => {
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries(newFilters).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );

    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true
    });
}, { deep: true });

const applyFilters = () => {
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );

    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true
    })
}

const getResults = (page) => {
    filters.page = page
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );

    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true
    })
}

const clearFilters = () => {
    Object.keys(filters).forEach(key => {
        if (key === 'per_page') {
            filters[key] = 25
        } else if (key === 'facility') {
            filters[key] = []
        } else {
            filters[key] = ''
        }
    })
    applyFilters()
}

const viewOrderDetails = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedOrder.value = null
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString()
}

const formatStatus = (status) => {
    if (!status) return 'N/A'
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        reviewed: 'bg-blue-100 text-blue-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        in_process: 'bg-purple-100 text-purple-800',
        dispatched: 'bg-indigo-100 text-indigo-800',
        received: 'bg-green-100 text-green-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
    // Initialize filters from props
    Object.assign(filters, props.filters)
    // Ensure facility is an array
    if (!Array.isArray(filters.facility)) {
        filters.facility = filters.facility ? [filters.facility] : []
    }
})
</script> 