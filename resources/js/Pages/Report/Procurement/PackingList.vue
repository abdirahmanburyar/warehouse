<template>
    <Head title="Packing List Report" />
    <AuthenticatedLayout title="Packing List Report" description="Comprehensive analysis of packing lists and shipping activities"
        img="/assets/images/report.png">
    
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
            <!-- Total Packing Lists -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Lists</p>
                        <p class="text-2xl font-bold text-gray-900">{{ summary.total_packing_lists }}</p>
                    </div>
                </div>
            </div>

            <!-- Shipped -->
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
                        <p class="text-sm font-medium text-gray-500">Shipped</p>
                        <p class="text-2xl font-bold text-green-600">{{ summary.shipped_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ summary.pending_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Items -->
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

            <!-- Total Value -->
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
                        <p class="text-sm font-medium text-gray-500">Total Value</p>
                        <p class="text-xs font-bold text-gray-900">${{ formatCurrency(calculatedTotalValue) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">Results: {{ packingLists.total }}</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select v-model="status" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Supplier Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <Multiselect
                        v-model="supplier_ids"
                        :options="suppliers"
                        :multiple="true"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        :create-option="false"
                        placeholder="Select suppliers..."
                        track-by="id"
                        label="name"
                        class="w-full"
                        :class="{
                            'border-red-300 focus:border-red-500 focus:ring-red-500': false,
                            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500': true
                        }"
                    />
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
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by PL number, ref no, PO number..."
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

        <!-- Packing Lists Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Packing Lists</h3>
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">PL Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">PO Number</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">PL Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Items Count</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Total Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-if="packingLists.data.length === 0" class="hover:bg-gray-50">
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No packing lists found</h3>
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
                        <tr v-for="pl in packingLists.data" :key="pl.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                <div class="flex flex-col space-y-1">
                                    <span class="bg-blue-100 px-2 py-1 rounded-md text-xs">{{ pl.packing_list_number }}</span>
                                    <span v-if="pl.ref_no" class="bg-purple-100 px-2 py-1 rounded-md text-xs text-purple-700">{{ pl.ref_no }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <div class="flex flex-col space-y-1">
                                    <span class="bg-green-100 px-2 py-1 rounded-md text-xs">{{ pl.purchase_order?.po_number || 'N/A' }}</span>
                                    <span v-if="pl.purchase_order?.ref_no" class="bg-orange-100 px-2 py-1 rounded-md text-xs text-orange-700">{{ pl.purchase_order.ref_no }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ pl.purchase_order?.supplier?.name || 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusClass(pl.status)">
                                    {{ getStatusLabel(pl.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-md text-xs">{{ formatDate(pl.pk_date) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ pl.items?.length || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ formatCurrency(calculateTotalAmount(pl)) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <button 
                                    @click="openItemsModal(pl)"
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
                    :data="packingLists" 
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
                        Packing List Items - {{ selectedPL?.packing_list_number }}
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UOM</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in selectedPL?.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ item.product?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.product?.category?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.uom || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.product?.dosage?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.batch_number || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatDate(item.expire_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ formatCurrency(item.unit_cost) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ formatCurrency(item.total_cost) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.location || 'N/A' }}
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
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import * as XLSX from 'xlsx';

const props = defineProps({
    packingLists: Object,
    filters: Object,
    summary: Object,
    suppliers: Array
});

const status = ref(props.filters.status || '');
const supplier_ids = ref([]);
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');
const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);

// Initialize supplier_ids from filters
const initializeSupplierIds = () => {
    if (props.filters.supplier_ids && Array.isArray(props.filters.supplier_ids)) {
        supplier_ids.value = props.filters.supplier_ids.map(id => {
            const supplier = props.suppliers.find(s => s.id == id);
            return supplier || { id: id, name: `Supplier ${id}` };
        });
    }
};

// Initialize on component mount
initializeSupplierIds();

// Modal state
const showItemsModal = ref(false);
const selectedPL = ref(null);

const applyFilters = () => {
    // Create a clean filters object by removing empty values
    const cleanFilters = {};
    
    if (status.value) cleanFilters.status = status.value;
    if (supplier_ids.value && supplier_ids.value.length > 0) cleanFilters.supplier_ids = supplier_ids.value.map(s => s.id);
    if (date_from.value) cleanFilters.date_from = date_from.value;
    if (date_to.value) cleanFilters.date_to = date_to.value;
    if (search.value) cleanFilters.search = search.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    if (props.filters.page) cleanFilters.page = props.filters.page;
    
    router.get(route('reports.procurement.packing-list'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['packingLists', 'summary']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const formatCurrency = (amount) => {
    return parseFloat(amount || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getStatusClass = (status) => {
    const classes = {
        'pending': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-yellow-100 text-yellow-800 border border-yellow-200',
        'confirmed': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-green-100 text-green-800 border border-green-200',
        'approved': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-blue-100 text-blue-800 border border-blue-200',
        'rejected': 'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-red-100 text-red-800 border border-red-200'
    };
    return classes[status] || classes['pending'];
};

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'approved': 'Approved',
        'rejected': 'Rejected'
    };
    return labels[status] || 'Pending';
};

const openItemsModal = (pl) => {
    selectedPL.value = pl;
    showItemsModal.value = true;
};

const closeItemsModal = () => {
    showItemsModal.value = false;
    selectedPL.value = null;
};

const clearFilters = () => {
    status.value = '';
    supplier_ids.value = [];
    date_from.value = '';
    date_to.value = '';
    search.value = '';
    per_page.value = 25;
    props.filters.page = 1;
};

const calculateTotalAmount = (pl) => {
    if (!pl.items || pl.items.length === 0) return 0;
    return pl.items.reduce((total, item) => total + parseFloat(item.total_cost || 0), 0);
};

const calculatedTotalValue = computed(() => {
    if (!props.packingLists.data || props.packingLists.data.length === 0) return 0;
    return props.packingLists.data.reduce((total, pl) => total + calculateTotalAmount(pl), 0);
});

const exportToExcel = () => {
    // Prepare data for export
    const data = props.packingLists.data.map(pl => ({
        'PL Number': pl.packing_list_number + (pl.ref_no ? ` (${pl.ref_no})` : ''),
        'PO Number': (pl.purchase_order?.po_number || '') + (pl.purchase_order?.ref_no ? ` (${pl.purchase_order.ref_no})` : ''),
        'Supplier': pl.purchase_order?.supplier?.name || '',
        'Status': getStatusLabel(pl.status),
        'PL Date': formatDate(pl.pk_date),
        'Items Count': pl.items?.length || 0,
        'Total Amount': formatCurrency(calculateTotalAmount(pl))
    }));
    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Packing Lists');
    XLSX.writeFile(wb, 'packing_lists_report.xlsx');
};

// Watch for filter changes and apply them
watch([
    () => search.value,
    () => status.value,
    () => supplier_ids.value,
    () => date_from.value,
    () => date_to.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    applyFilters();
});

// Watch for props changes (e.g., on page reload)
watch(() => props.filters, () => {
    initializeSupplierIds();
}, { deep: true });
</script> 