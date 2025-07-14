<template>
    <Head title="Lead Time Analysis Report" />
    <AuthenticatedLayout title="Lead Time Analysis Report" description="Analysis of procurement lead times and supplier performance"
        img="/assets/images/report.png">
    
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total POs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_pos }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg Lead Time</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.avg_lead_time }} days</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Results: {{ purchaseOrders.total }}</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Supplier Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Suppliers</label>
                    <Multiselect
                        v-model="supplier_ids"
                        :options="suppliers"
                        :multiple="true"
                        :close-on-select="false"
                        :clear-on-select="false"
                        :preserve-search="true"
                        placeholder="Select suppliers..."
                        label="name"
                        track-by="id"
                        :preselect-first="false"
                    />
                </div>
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select v-model="status" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <!-- Date From Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input
                        v-model="date_from"
                        type="date"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                    />
                </div>
                <!-- Date To Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input
                        v-model="date_to"
                        type="date"
                        class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                    />
                </div>
            </div>
        </div>

        <!-- Export Button -->
        <div class="flex justify-end mb-2">
            <button @click="exportToExcel" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v16h16V4H4zm4 8h8m-4-4v8" />
                </svg>
                Export to Excel
            </button>
        </div>

        <!-- Purchase Orders Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Purchase Orders</h3>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Show:</label>
                        <select v-model="per_page" @change="filters.page = 1" class="border-gray-300 rounded-md text-sm shadow-sm">
                            <option value="25">25 Per page</option>
                            <option value="50">50 Per page</option>
                            <option value="100">100 Per page</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">PO Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">PO Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Packing List Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Lead Time (Days)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Items Count</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-if="purchaseOrders.data.length === 0" class="hover:bg-gray-50">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No purchase orders found</h3>
                                    <p class="text-gray-500 mb-4">Try adjusting your filters or search criteria</p>
                                    <button @click="clearFilters" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Clear Filters
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="po in purchaseOrders.data" :key="po.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <span class="bg-blue-100 px-2 py-1 rounded-md text-xs">{{ po.po_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ po.supplier?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(po.status)">
                                    {{ getStatusLabel(po.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-md text-xs">{{ formatDate(po.po_date) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span v-if="getEarliestPackingListDate(po)" class="bg-green-50 text-green-700 px-2 py-1 rounded-md text-xs">{{ formatDate(getEarliestPackingListDate(po)) }}</span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span v-if="getEarliestPackingListDate(po) && po.po_date" class="bg-purple-100 px-2 py-1 rounded-md text-xs">
                                    {{ calculateLeadTime(po.po_date, getEarliestPackingListDate(po)) }} days
                                </span>
                                <span v-else class="text-gray-400">—</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ po.items?.length || 0 }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-end mt-3">
                <TailwindPagination 
                    :data="purchaseOrders" 
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import * as XLSX from 'xlsx';

const props = defineProps({
    purchaseOrders: Object,
    filters: Object,
    summary: Object,
    suppliers: Array
});



const supplier_ids = ref([]);
const initializeSupplierIds = () => {
    if (props.filters.supplier_ids && Array.isArray(props.filters.supplier_ids)) {
        supplier_ids.value = props.filters.supplier_ids.map(id => {
            const supplier = props.suppliers.find(s => s.id == id);
            return supplier || { id: id, name: `Supplier ${id}` };
        });
    }
};
initializeSupplierIds();
const status = ref(props.filters.status || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const per_page = ref(props.filters.per_page || 25);

const applyFilters = () => {
    const cleanFilters = {};
    if (supplier_ids.value && supplier_ids.value.length > 0) cleanFilters.supplier_ids = supplier_ids.value.map(s => s.id);
    if (status.value) cleanFilters.status = status.value;
    if (date_from.value) cleanFilters.date_from = date_from.value;
    if (date_to.value) cleanFilters.date_to = date_to.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    if (props.filters.page) cleanFilters.page = props.filters.page;
    
    router.get(route('reports.procurement.lead-time-analysis'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['purchaseOrders', 'summary']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const getEarliestPackingListDate = (po) => {
    if (!po.packing_lists || po.packing_lists.length === 0) return null;
    const dates = po.packing_lists.map(pl => pl.pk_date).filter(date => date);
    if (dates.length === 0) return null;
    const earliestDate = new Date(Math.min(...dates.map(date => new Date(date))));
    return earliestDate.toISOString().split('T')[0]; // Return as YYYY-MM-DD format
};

const calculateLeadTime = (poDate, pkDate) => {
    if (!poDate || !pkDate) return null;
    const po = new Date(poDate);
    const pk = new Date(pkDate);
    const diffTime = Math.abs(pk - po);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-yellow-100 text-yellow-800 border border-yellow-200',
        'approved': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-green-100 text-green-800 border border-green-200',
        'rejected': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-red-100 text-red-800 border border-red-200'
    };
    return classes[status] || classes['pending'];
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    return labels[status] || 'Pending';
};

const clearFilters = () => {
    supplier_ids.value = [];
    status.value = '';
    date_from.value = '';
    date_to.value = '';
    per_page.value = 25;
    props.filters.page = 1;
};

const exportToExcel = () => {
    const data = props.purchaseOrders.data.map(po => ({
        'PO Number': po.po_number,
        'Supplier': po.supplier?.name || '',
        'Status': getStatusLabel(po.status),
        'PO Date': formatDate(po.po_date),
        'Packing List Date': getEarliestPackingListDate(po) ? formatDate(getEarliestPackingListDate(po)) : '',
        'Lead Time (Days)': getEarliestPackingListDate(po) && po.po_date ? calculateLeadTime(po.po_date, getEarliestPackingListDate(po)) : '',
        'Items Count': po.items?.length || 0
    }));
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Lead Time Analysis');
    XLSX.writeFile(wb, 'lead_time_analysis_report.xlsx');
};

// Watch for filter changes and apply them
watch([
    () => supplier_ids.value,
    () => status.value,
    () => date_from.value,
    () => date_to.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    applyFilters();
});
</script> 