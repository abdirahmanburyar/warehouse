<template>
    <AuthenticatedLayout title="Back Order" description="Back Order History">
        <div class="py-6" @click="handleOutsideClick">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-orange-400 to-red-500 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Back Order History</h1>
                        <p class="text-gray-600 text-sm">Manage and track all back order activities</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input type="text" v-model="search" placeholder="Search by back order number, packing list number"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 text-sm bg-gray-50 hover:bg-white" />
                        </div>
                    </div>
                    
                    <!-- Warehouse Filter -->
                    <div>
                        <label for="warehouse" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Warehouse
                        </label>
                        <Multiselect
                            v-model="warehouse"
                            :options="props.warehouses"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Warehouse"
                            :show-labels="false"
                            class="text-sm"
                        />
                    </div>
                    
                    <!-- Facility Filter -->
                    <div>
                        <label for="facility" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Facility
                        </label>
                        <Multiselect
                            v-model="facility"
                            :options="props.facilities"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Facility"
                            :show-labels="false"
                            class="text-sm"
                        />
                    </div>
                    
                    <!-- Supplier Filter -->
                    <div>
                        <label for="supplier" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Supplier
                        </label>
                        <Multiselect
                            v-model="supplier"
                            :options="props.suppliers"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Supplier"
                            :show-labels="false"
                            class="text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Per Page and Results Section -->
            <div class="flex items-center justify-end mb-6">
                <div class="w-48">
                    <select v-model="per_page" @change="filters.page = 1"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 text-sm bg-gray-50 hover:bg-white">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Main Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">SN</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Back Order ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Packing List</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Supplier</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Reported By</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Notes & Documents</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border border-gray-200">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="(order, idx) in props.history.data" :key="order.id" class="hover:bg-indigo-50/30 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium border border-gray-200">{{ idx + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    <button class="text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-150" @click="openHistoryModal(order)">
                                        {{ order.back_order_number }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border border-gray-200">{{ formatDate(order.back_order_date) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border border-gray-200">{{ order.packing_list?.packing_list_number || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border border-gray-200">{{ order.packing_list?.purchase_order?.supplier?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 border border-gray-200">{{ order.reported_by || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 border border-gray-200">
                                    <div v-if="order.attach_documents && order.attach_documents.length">
                                        <div class="relative">
                                            <button @click.stop="toggleDropdown(order.id)" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                {{ order.attach_documents.length }} document(s)
                                            </button>
                                            <div v-if="openDropdowns.includes(order.id)" class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg">
                                                <div class="p-3">
                                                    <div v-for="(doc, i) in order.attach_documents" :key="i" class="mb-2 last:mb-0">
                                                        <a :href="doc.path" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm flex items-center">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            {{ doc.name }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div v-if="order.notes && order.notes.length > 50">
                                            <div class="relative">
                                                <button @click.stop="toggleDropdown(order.id)" class="text-gray-600 hover:text-gray-800">
                                                    {{ order.notes.substring(0, 50) }}...
                                                </button>
                                                <div v-if="openDropdowns.includes(order.id)" class="absolute z-10 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg">
                                                    <div class="p-3">
                                                        <p class="text-sm text-gray-700">{{ order.notes }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else>
                                            {{ order.notes || '-' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border border-gray-200">
                                    <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors duration-150" @click="openHistoryModal(order)">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View History
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <TailwindPagination 
                    :data="props.history" 
                    @pagination-change-page="getResults" 
                    :limit="3" 
                />
            </div>
        </div>

        <!-- Modal for BackOrderHistory -->
        <Modal :show="showModal" max-width="5xl" @close="showModal = false">
            <div class="p-6" @click="handleModalOutsideClick">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Back Order History for {{ selectedOrder?.back_order?.back_order_number || selectedOrder?.back_order_number }}</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <!-- Loading State -->
                <div v-if="isLoadingHistories" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading back order history...</span>
                </div>

                <!-- Empty State -->
                <div v-else-if="!histories.data || histories.data.length === 0" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No history found</h3>
                    <p class="mt-1 text-sm text-gray-500">No back order history data available for this item.</p>
                </div>

                <!-- History Table -->
                <table v-else class="min-w-full border border-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 text-left py-2 border border-gray-300">SN</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Item Name</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Expiry Date</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Batch Number</th>
                            <th class="px-4 text-left py-2 border border-gray-300">QTY</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Status</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Note & Attachment</th>
                            <th class="px-4 text-left py-2 border border-gray-300">Performed By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(history, idx) in histories.data" :key="history.id">
                            <td class="px-4 text-left py-2 border border-gray-300">{{ idx + 1 }}</td>
                            <td class="px-4 text-left py-2 border border-gray-300">{{ history.product?.name }}</td>
                            <td class="px-4 text-left py-2 border border-gray-300">{{ formatDate(history.expiry_date) }}</td>
                            <td class="px-4 text-left py-2 border border-gray-300">{{ history.batch_number }}</td>
                            <td class="px-4 text-left py-2 border border-gray-300">{{ history.quantity }}</td>
                            <td class="px-4 text-left py-2 border border-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(history.status)">
                                    {{ history.status }}
                                </span>
                            </td>
                            <td class="px-4 text-left py-2 border border-gray-300">
                                <div v-if="history.attach_documents && history.attach_documents.length">
                                    <div class="relative">
                                        <button @click.stop="toggleHistoryDropdown(history.id)" class="text-blue-600 underline">
                                            {{ history.attach_documents.length }} document(s)
                                        </button>
                                        <div v-if="openHistoryDropdowns.includes(history.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                            <div class="p-2">
                                                <div v-for="(doc, i) in history.attach_documents" :key="i" class="mb-1">
                                                    <a :href="doc.path" target="_blank" class="text-blue-600 underline block text-xs">{{ doc.name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <div v-if="history.note && history.note.length > 50">
                                        <div class="relative">
                                            <button @click.stop="toggleHistoryDropdown(history.id)" class="text-gray-600">
                                                {{ history.note.substring(0, 50) }}...
                                            </button>
                                            <div v-if="openHistoryDropdowns.includes(history.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                                <div class="p-2">
                                                    <p class="text-xs">{{ history.note }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        {{ history.note || '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 text-left py-2 border border-gray-300">
                                {{ history.performer?.name || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { TailwindPagination } from 'laravel-vue-pagination';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { XMarkIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps({
    history: {
        type: Object,
        required: true,
    },
    warehouses: Array,
    facilities: Array,
    suppliers: Array,
    filters:  Object,
});

const histories = ref([]);
const showModal = ref(false);
const selectedOrder = ref(null);
const openDropdowns = ref([]);
const openHistoryDropdowns = ref([]);
const isLoadingHistories = ref(false);

// Filter refs
const search = ref(props.filters?.search || '');
const warehouse = ref(props.filters?.warehouse || '');
const facility = ref(props.filters?.facility || '');
const supplier = ref(props.filters?.supplier || '');
const per_page = ref(props.filters?.per_page || 25);



// Watch for filter changes
watch([
    () => search.value,
    () => warehouse.value,
    () => facility.value,
    () => supplier.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    reloadBackOrders();
});

function getResults(page = 1) {
    props.filters.page = page;
}

function reloadBackOrders() {
    const query = {};
    if (search.value) query.search = search.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (facility.value) query.facility = facility.value;
    if (supplier.value) query.supplier = supplier.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    
    router.get(route('supplies.showBackOrder'), query, {
        preserveScroll: false,
        preserveState: false,
        only: ['history']
    });
}

function openHistoryModal(order) {
    console.log(order);
    selectedOrder.value = order;
    showModal.value = true;
    isLoadingHistories.value = true;
    fetchHistories(order.id);
}

async function fetchHistories(backOrderId) {
    isLoadingHistories.value = true;
    await axios.get(route('supplies.backOrders.histories', backOrderId))
    .then(response => {
        isLoadingHistories.value = false;
        console.log(response.data);
        histories.value = { data: response.data };
    })
    .catch(error => {
        isLoadingHistories.value = false;
        console.error('Error fetching back order histories:', error);
        histories.value = { data: [] };
    });
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
}

function statusClass(status) {
    switch (status) {
        case 'pending':
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
        case 'Processing':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
        case 'Received':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function toggleDropdown(id) {
    if (openDropdowns.value.includes(id)) {
        openDropdowns.value = openDropdowns.value.filter((i) => i !== id);
    } else {
        openDropdowns.value.push(id);
    }
}

function toggleHistoryDropdown(id) {
    if (openHistoryDropdowns.value.includes(id)) {
        openHistoryDropdowns.value = openHistoryDropdowns.value.filter((i) => i !== id);
    } else {
        openHistoryDropdowns.value.push(id);
    }
}

function handleOutsideClick() {
    // Close all main table dropdowns when clicking outside
    openDropdowns.value = [];
}

function handleModalOutsideClick() {
    // Close all modal dropdowns when clicking outside
    openHistoryDropdowns.value = [];
}
</script>