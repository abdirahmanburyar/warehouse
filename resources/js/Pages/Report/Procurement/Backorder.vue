<template>
    <Head title="Backorder Report" />
    <AuthenticatedLayout title="Backorder Report" description="Comprehensive analysis of backorders and fulfillment gaps"
        img="/assets/images/report.png">
    
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
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
                        <p class="text-sm font-medium text-gray-500">Total Backorders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_back_orders }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Open</p>
                        <p class="text-2xl font-bold text-green-600">{{ summary.open_count }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Closed</p>
                        <p class="text-2xl font-bold text-red-600">{{ summary.closed_count }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_items }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Quantity</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_quantity }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Results: {{ backOrders.total }}</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Removed Status Filter -->
                <!-- Source Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Source Type</label>
                    <select v-model="source_type" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">All Sources</option>
                        <option value="packing_list">Packing List</option>
                        <option value="transfer">Transfer</option>
                        <option value="order">Order</option>
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
                <!-- Search Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search backorders..."
                            class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                        />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
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

        <!-- Backorders Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Backorders List</h3>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Show:</label>
                        <select v-model="per_page" @change="props.filters.page = 1" class="border-gray-300 rounded-md text-sm shadow-sm">
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Backorder Number</th>
                            <!-- Removed Status column -->
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Source</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Total Items</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Total Quantity</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-if="backOrders.data.length === 0" class="hover:bg-gray-50">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No backorders found</h3>
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
                        <tr v-for="bo in backOrders.data" :key="bo.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <span class="bg-blue-100 px-2 py-1 rounded-md text-xs">{{ bo.back_order_number }}</span>
                            </td>
                            <!-- Removed Status cell -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-md text-xs">{{ formatDate(bo.back_order_date) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <template v-if="bo.source_type === 'order'">
                                    Order - {{ bo.order?.order_number }}
                                </template>
                                <template v-else-if="bo.source_type === 'transfer'">
                                    Transfer - {{ bo.transfer?.transferID }}
                                </template>
                                <template v-else-if="bo.source_type === 'packing_list'">
                                    Packing List - {{ bo.packing_list?.packing_list_number }}
                                </template>
                                <template v-else>
                                    {{ getSourceTypeLabel(bo.source_type) }}
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ bo.total_items || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ bo.total_quantity || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <button 
                                    @click="openItemsModal(bo)"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                >
                                    View Items
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-end mt-3">
                <TailwindPagination 
                    :data="backOrders" 
                    :limit="2"
                    @pagination-change-page="getResults"
                />
            </div>
        </div>

        <!-- Items Modal -->
        <div v-if="showItemsModal" class="fixed inset-0 bg-white z-50 overflow-hidden" @click="closeItemsModal">
            <div class="h-full w-full flex flex-col" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Backorder Differences - {{ selectedBO?.back_order_number }}
                    </h3>
                    <button @click="closeItemsModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in selectedBO?.differences || []" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.product?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.product?.category?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.product?.dosage?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ item.status }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.notes || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import * as XLSX from 'xlsx';

const props = defineProps({
    backOrders: Object,
    filters: Object,
    summary: Object,
    suppliers: Array
});

// Debug: Log props on mount
onMounted(() => {
    console.log('Backorder props:', props);
    console.log('Initial filters:', props.filters);
    console.log('Initial backOrders data:', props.backOrders.data);
});

// Removed status ref
const supplier_ids = ref([]);
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);
const source_type = ref(props.filters.source_type || '');

console.log('Initial source_type value:', source_type.value);

// Modal state
const showItemsModal = ref(false);
const selectedBO = ref(null);

const initializeSupplierIds = () => {
    if (props.filters.supplier_ids && Array.isArray(props.filters.supplier_ids)) {
        supplier_ids.value = props.filters.supplier_ids.map(id => {
            const supplier = props.suppliers.find(s => s.id == id);
            return supplier || { id: id, name: `Supplier ${id}` };
        });
    }
};
initializeSupplierIds();

const applyFilters = () => {
    const cleanFilters = {};
    // Removed status filter
    if (source_type.value) cleanFilters.source_type = source_type.value;
    if (date_from.value) cleanFilters.date_from = date_from.value;
    if (date_to.value) cleanFilters.date_to = date_to.value;
    if (search.value) cleanFilters.search = search.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    if (props.filters.page) cleanFilters.page = props.filters.page;
    
    console.log('Applying filters:', cleanFilters);
    console.log('Current source_type value:', source_type.value);
    
    router.get(route('reports.procurement.backorder'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['backOrders', 'summary']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

// Removed getStatusClass and getStatusLabel functions

const getSourceTypeLabel = (type) => {
    const map = {
        packing_list: 'Packing List',
        transfer: 'Transfer',
        order: 'Order'
    };
    return map[type] || 'Unknown';
};

const openItemsModal = (bo) => {
    selectedBO.value = bo;
    showItemsModal.value = true;
};

const closeItemsModal = () => {
    showItemsModal.value = false;
    selectedBO.value = null;
};

const clearFilters = () => {
    // Removed status.value = '';
    supplier_ids.value = [];
    date_from.value = '';
    date_to.value = '';
    search.value = '';
    per_page.value = 25;
    props.filters.page = 1;
};

const exportToExcel = () => {
    const data = props.backOrders.data.map(bo => ({
        'Backorder Number': bo.back_order_number,
        // Removed 'Status'
        'Date': formatDate(bo.back_order_date),
        'Source': 
            bo.source_type === 'order' && bo.order_number ? `Order - ${bo.order?.order_number} (ID: ${bo.order_id})` :
            bo.source_type === 'transfer' && bo.transfer_id ? `Transfer - ${bo.transferID} (ID: ${bo.transfer_id})` :
            bo.source_type === 'packing_list' && bo.packing_list_number ? `Packing List - ${bo.packing_list_number} (ID: ${bo.packing_list_id})` :
            getSourceTypeLabel(bo.source_type),
        'Total Items': bo.total_items || 0,
        'Total Quantity': bo.total_quantity || 0
    }));
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Backorders');
    XLSX.writeFile(wb, 'backorders_report.xlsx');
};

// Watch for filter changes and apply them
watch([
    () => search.value,
    // Removed () => status.value,
    () => supplier_ids.value,
    () => date_from.value,
    () => date_to.value,
    () => per_page.value,
    () => props.filters.page,
    () => source_type.value
], () => {
    applyFilters();
});

// Debug watch for source_type
watch(() => source_type.value, (newVal, oldVal) => {
    console.log('source_type changed from', oldVal, 'to', newVal);
});

// Watch for props changes (e.g., on page reload)
watch(() => props.filters, () => {
    initializeSupplierIds();
}, { deep: true });
</script> 