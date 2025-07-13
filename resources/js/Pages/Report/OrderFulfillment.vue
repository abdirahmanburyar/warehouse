<template>

    <Head title="Order Tracking Report" />
    <AuthenticatedLayout title="Order Tracking Report" description="Track order status and inventory allocations">
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
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="dispatched">Dispatched</option>
                        <option value="completed">Completed</option>
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
                    <select v-model="filters.per_page" @change="getResults(1)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end mb-2">
                <button @click="exportToExcel"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 16v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m2 4h-8m0 0l2-2m-2 2l2 2" />
                    </svg>
                    Export to Excel
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
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Handled By</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Date</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Expected Date</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Order Summary</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tr-lg"
                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Progress</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr v-if="!orders.data || orders.data.length === 0">
                        <td colspan="8" class="py-12">
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
                            <div class="text-sm text-gray-900">{{ order.facility?.name }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ order.facility?.handledby?.name || 'N/A' }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ formatDate(order.order_date) }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm text-gray-900">{{ formatDate(order.expected_date) }}</div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <span :class="getStatusClass(order.status)"
                                class="px-2 py-1 text-xs font-medium rounded-full capitalize">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b"
                            style="border-bottom: 1px solid #B7C6E6;">
                            <div class="text-sm">
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Allocated QTY:</span>
                                    <span class="text-gray-900">{{ order.tracking_data?.total_allocated ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-600">Received QTY:</span>
                                    <span class="text-gray-900">{{ order.tracking_data?.total_received ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fulfillment:</span>
                                    <span class="text-gray-900">{{ order.tracking_data?.fulfillment_percentage ?? 0 }}%</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap border-b" style="border-bottom: 1px solid #B7C6E6;">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full"
                                    :style="{ width: (order.tracking_data?.fulfillment_percentage ?? 0) + '%' }"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ order.tracking_data?.fulfillment_percentage ?? 0 }}% Fulfilled
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            <TailwindPagination :data="orders" :limit="2" @pagination-change-page="getResults" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import * as XLSX from 'xlsx';

const props = defineProps({
    orders: Object,
    facilities: Array,
    filters: Object,
});
const filters = ref({
    facility: props.filters.facility || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 25,
});

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
        preserveScroll: true,
    });
}, { deep: true });

const getResults = (page = 1) => {
    // Remove empty filters from the URL
    const cleanFilters = Object.fromEntries(
        Object.entries({ ...filters.value, page }).filter(([key, value]) => {
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            return value !== '' && value !== null && value !== undefined;
        })
    );

    router.get(route('reports.order-tracking'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
        dispatched: 'bg-blue-100 text-blue-800',
        completed: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const exportToExcel = () => {
    const exportData = props.orders.data.map(order => ({
        "Order Number": order.order_number,
        "Facility": order.facility?.name || "",
        "Handled By": order.facility?.handledby?.name || "",
        "Order Date": order.order_date,
        "Expected Date": order.expected_date,
        "Status": order.status,
        "Allocated QTY": order.tracking_data?.total_allocated,
        "Received QTY": order.tracking_data?.total_received,
        "Fulfillment (%)": order.tracking_data?.fulfillment_percentage,
    }));

    const ws = XLSX.utils.json_to_sheet(exportData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Order Tracking");
    XLSX.writeFile(wb, "order_fulfillment_efficiency_report.xlsx");
};


</script>
