<template>
    <AuthenticatedLayout title="Optimize Your Transfers" description="Moving Supplies, Bridging needs"
        img="/assets/images/transfer.png">
        <div class="mb-[80px]">

            <!-- Header Section -->
            <div class="flex flex-col mb-6 space-y-6">
                <!-- Buttons First -->
                <div class="flex items-center space-x-4 justify-end">
                    <!-- New Transfer -->
                    <button @click="router.visit(route('transfers.create'))"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        New Transfer
                    </button>
                    
                    <!-- Back Orders -->
                    <Link :href="route('transfers.back-order')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v10H5V5z"
                                clip-rule="evenodd" />
                        </svg>
                        Back Orders
                    </Link>
                </div>

                <!-- Filters Second -->
                <div class="flex items-center justify-between w-full">
                    <!-- Search -->
                    <div class="relative flex-grow max-w-xs">
                        <input type="text" v-model="filters.search"
                            class="pl-10 pr-4 py-2 border border-gray-300 w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search a Transfer">
                    </div>

                    <!-- Facility Selector -->
                    <div class="relative w-[16%]">
                        <div class="flex items-center">
                            <Multiselect v-model="filters.selected_facility" :options="props.facilities"
                                :searchable="true" :allow-empty="true" :show-labels="false"
                                placeholder="All Facilities" label="name" track-by="id"
                                class="pl-10 w-full" @input="updateFacilityFilter">
                                <template #singleLabel="{ option }">
                                    <span class="multiselect__single">
                                        {{ option ? option.name : 'All Facilities' }}
                                    </span>
                                </template>
                            </Multiselect>
                        </div>
                    </div>

                    <!-- Warehouse Selector -->
                    <div class="relative w-[16%]">
                        <Multiselect v-model="filters.selected_warehouses" :options="props.warehouses"
                            :multiple="true" :close-on-select="false" :clear-on-select="false"
                            :preserve-search="true" placeholder="All Warehouses" label="name" track-by="id"
                            class="pl-10 w-full" :preselect-first="false" @input="updateWarehouseFilter">
                            <template #selection="{ values, search, isOpen }">
                                <span class="multiselect__single" v-if="values.length && !isOpen">
                                    {{ values.length === 1 ? values[0].name : `${values.length} warehouses selected`
                                    }}
                                </span>
                            </template>
                        </Multiselect>
                    </div>

                    <!-- Location Selector -->
                    <div class="relative w-[16%]">
                        <Multiselect v-model="filters.selected_locations" :options="props.locations"
                            :multiple="true" :close-on-select="false" :clear-on-select="false"
                            :preserve-search="true" placeholder="All Locations" label="location" track-by="id"
                            class="pl-10 w-full" :preselect-first="false" @input="updateLocationFilter">
                            <template #selection="{ values, search, isOpen }">
                                    <span class="multiselect__single" v-if="values.length && !isOpen">
                                        {{ values.length === 1 ? values[0].location : `${values.length} locations
                                        selected` }}
                                    </span>
                                </template>
                            </Multiselect>
                    </div>

                    <!-- Date Range Selector -->
                    <div class="flex items-center space-x-2 w-[30%]">
                        <div class="relative flex-1">
                            <input type="date" v-model="filters.date_from"
                                class="pl-10 pr-2 py-2 border border-gray-300 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <span class="text-gray-500 mx-1">to</span>
                        <div class="relative flex-1">
                            <input type="date" v-model="filters.date_to"
                                class="pl-10 pr-2 py-2 border border-gray-300 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button v-for="tab in statusTabs" :key="tab.value" @click="currentTab = tab.value"
                            class="whitespace-nowrap py-4 px-3 border-b-4 font-bold text-sm" :class="[
                                currentTab === tab.value ?
                                    'border-green-500 text-green-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-12 gap-2">
                <!-- Table Section (9 cols) -->
                <div class="col-span-9">
                    <div class="shadow overflow-x-auto max-w-full">
                        <table class="min-w-full border border-black divide-y divide-black overflow-hidden border-collapse">
                            <thead class="bg-gray-50 border-b border-black">
                                <tr>
                                    <th scope="col"
                                        class="text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Transfer ID
                                    </th>
                                    <th scope="col"
                                        class="text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Transfer Date
                                    </th>
                                    <th scope="col"
                                        class="text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Transferred From
                                    </th>
                                    <th scope="col"
                                        class="text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Transferred To
                                    </th>
                                    <th scope="col"
                                        class="text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Number of Items
                                    </th>
                                    <th scope="col"
                                        class="min-w-[300px] text-left text-xs font-medium text-gray-500 uppercase border border-black p-2">
                                        Current Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="border-b border-black">
                                <tr v-if="filteredTransfers.length === 0">
                                    <td colspan="7" class="text-center text-gray-500 border border-black p-2">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <p class="mt-2 text-lg font-medium">No transfer data available</p>
                                            <p class="mt-1 text-sm">Create a new transfer or adjust your filters to see results</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="transfer in filteredTransfers" :key="transfer.id" class="hover:bg-gray-50 border-b border-black">
                                    <td class="whitespace-nowrap text-sm font-medium text-gray-900 border border-black p-2">
                                        <Link :href="route('transfers.show', transfer.id)">
                                        {{ transfer.transferID }}
                                        </Link>
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-gray-500 border border-black p-2">
                                        {{ new Date(transfer.transfer_date).toLocaleDateString() }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-gray-500 border border-black p-2">
                                        {{ transfer.from_warehouse?.name || transfer.from_facility?.name }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-gray-500 border border-black p-2">
                                        {{ transfer.to_warehouse?.name || transfer.to_facility?.name }}
                                    </td>
                                    <td class="whitespace-nowrap text-sm text-gray-500 border border-black p-2">
                                        {{ transfer.items_count }}
                                    </td>
                                    <td class="text-sm text-gray-500 text-left border border-black p-2">
                                        <div class="flex items-center gap-2">
                                            <!-- Status Progress Icons - Only show actions taken -->
                                            <div class="flex items-center gap-2">
                                                <!-- Show status progression up to current status - icons with labels -->
                                                <!-- Always show pending as it's the initial state -->
                                                <div class="flex items-center gap-1">
                                                    <img src="/assets/images/pending.png" class="w-8 h-8" alt="Pending" title="Pending" />
                                                </div>
                                                
                                                <!-- Show approved if status is approved or further -->
                                                <template v-if="['approved', 'in_process', 'dispatched', 'transferred', 'delivered', 'received'].includes(transfer.status?.toLowerCase())">
                                                    <div class="flex items-center gap-1">
                                                        <img src="/assets/images/approved.png" class="w-8 h-8" alt="Approved" title="Approved" />
                                                    </div>
                                                </template>
                                                
                                                <!-- Show processed if status is in_process or further -->
                                                <template v-if="['in_process', 'dispatched', 'transferred', 'delivered', 'received'].includes(transfer.status?.toLowerCase())">
                                                    <div class="flex items-center gap-1">
                                                        <img src="/assets/images/inprocess.png" class="w-8 h-8" alt="Processed" title="Processed" />
                                                    </div>
                                                </template>
                                                
                                                <!-- Show dispatched if status is dispatched or further -->
                                                <template v-if="['dispatched', 'transferred', 'delivered', 'received'].includes(transfer.status?.toLowerCase())">
                                                    <div class="flex items-center gap-1">
                                                        <img src="/assets/images/dispatch.png" class="w-8 h-8" alt="Dispatched" title="Dispatched" />
                                                    </div>
                                                </template>
                                                
                                                <!-- Transfer icon removed as requested -->
                                                
                                                <!-- Show received if status is received -->
                                                <template v-if="['received'].includes(transfer.status?.toLowerCase())">
                                                    <div class="flex items-center gap-1">
                                                        <img src="/assets/images/received.png" class="w-8 h-8" alt="Received" title="Received" />
                                                    </div>
                                                </template>
                                                
                                                <!-- Show rejected if status is rejected (special case) -->
                                                <template v-if="transfer.status?.toLowerCase() === 'rejected'">
                                                    <div class="flex items-center gap-1">
                                                        <img src="/assets/images/rejected.png" class="w-8 h-8" alt="Rejected" title="Rejected" />
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Statistics Section (3 cols) -->
                <div class="col-span-3 p-6">
                    <div class="flex justify-between gap-2">
                        <!-- Pending -->
                        <div class="flex flex-col items-center">
                            <div class="h-[350px] w-14 bg-amber-50 rounded-2xl relative overflow-hidden px-2 shadow-md">
                                <img src="/assets/images/pending_small.png" class="h-10 w-10 object-contain" alt="Pending" />
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-amber-500 to-amber-400 transition-all duration-500"
                                    :style="{ height: props.statistics.pending.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-sm tracking-wide">
                                        {{ props.statistics.pending.percentage }}%
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Pending</span>
                        </div>

                        <!-- Approved -->
                        <div class="flex flex-col items-center">
                            <div class="h-[350px] w-14 bg-blue-100 rounded-2xl relative overflow-hidden px-2 shadow-md">
                                <img src="/assets/images/approved_small.png" class="h-10 w-10 object-contain" alt="Approved" />
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-blue-600 to-blue-400 transition-all duration-500"
                                    :style="{ height: props.statistics.approved.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-sm tracking-wide">
                                        {{ props.statistics.approved.percentage }}%
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Approved</span>
                        </div>

                        <!-- In Process -->
                        <div class="flex flex-col items-center">
                            <div class="h-[350px] w-14 bg-slate-100 rounded-2xl relative overflow-hidden px-2 shadow-md">
                                <img src="/assets/images/inprocess.png" class="h-10 w-10 object-contain" alt="In Process" />
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-600 to-slate-500 transition-all duration-500"
                                    :style="{ height: props.statistics.in_process.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-sm tracking-wide">
                                        {{ props.statistics.in_process.percentage }}%
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">In Process</span>
                        </div>

                        <!-- Dispatched -->
                        <div class="flex flex-col items-center">
                            <div class="h-[350px] w-14 bg-purple-100 rounded-2xl relative overflow-hidden px-2 shadow-md">
                                <img src="/assets/images/dispatch.png" class="h-10 w-10 object-contain" alt="Dispatched" />
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-purple-600 to-purple-400 transition-all duration-500"
                                    :style="{ height: (props.statistics.dispatched?.percentage || 0) + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-sm tracking-wide">
                                        {{ props.statistics.dispatched?.percentage || 0 }}%
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Dispatched</span>
                        </div>

                        <!-- Received -->
                        <div class="flex flex-col items-center">
                            <div class="h-[350px] w-14 bg-emerald-100 rounded-2xl relative overflow-hidden px-2 shadow-md">
                                <img src="/assets/images/received.png" class="h-10 w-10 object-contain" alt="Received" />
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all duration-500"
                                    :style="{ height: (props.statistics.received?.percentage || 0) + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-sm tracking-wide">
                                        {{ props.statistics.received?.percentage || 0 }}%
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Received</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { router, Link, usePage } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

// In Vue 3 with script setup, components are automatically registered when imported

const props = defineProps({
    transfers: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({
            approved: { count: 0, percentage: 0 },
            pending: { count: 0, percentage: 0 },
            in_process: { count: 0, percentage: 0 },
            transferred: { count: 0, percentage: 0 },
            rejected: { count: 0, percentage: 0 }
        })
    },
    facilities: {
        type: Array,
        default: () => []
    },
    warehouses: {
        type: Array,
        default: () => []
    },
    locations: {
        type: Array,
        default: () => []
    }
});

const currentTab = ref('all');
const selectedTransfers = ref([]);
const isAllSelected = computed(() => selectedTransfers.value.length === filteredTransfers.value.length && filteredTransfers.value.length > 0);

// Filter state
const filters = ref({
    ...props.filters,
    selected_facility: null,
    selected_warehouses: [],
    selected_locations: []
});

// Initialize multiselect values if filters exist
onMounted(() => {
    // Initialize facility filter
    if (props.filters && props.filters.facility_id) {
        const facilityId = props.filters.facility_id.toString();
        filters.value.selected_facility = props.facilities.find(facility =>
            facility.id.toString() === facilityId);
    }

    // Initialize warehouse filter
    if (props.filters && props.filters.warehouse_id) {
        const warehouseIds = props.filters.warehouse_id.toString().split(',');
        filters.value.selected_warehouses = props.warehouses.filter(warehouse =>
            warehouseIds.includes(warehouse.id.toString()));
    }

    // Initialize location filter
    if (props.filters && props.filters.location_id) {
        const locationIds = props.filters.location_id.toString().split(',');
        filters.value.selected_locations = props.locations.filter(location =>
            locationIds.includes(location.id.toString()));
    }
});

// Methods to handle filter updates
const updateFacilityFilter = () => {
    // Update the facility_id based on the selected facility object
    if (filters.value.selected_facility) {
        filters.value.facility_id = filters.value.selected_facility.id.toString();
    } else {
        filters.value.facility_id = '';
    }
    
    // Force immediate URL update
    // Create a params object with only non-empty values
    const params = {};
    
    // Add all non-empty filter values
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.facility_id) params.facility_id = filters.value.facility_id;
    if (filters.value.warehouse_id) params.warehouse_id = filters.value.warehouse_id;
    if (filters.value.location_id) params.location_id = filters.value.location_id;
    if (filters.value.date_from) params.date_from = filters.value.date_from;
    if (filters.value.date_to) params.date_to = filters.value.date_to;
    
    // Include tab if not 'all'
    if (currentTab.value !== 'all') params.tab = currentTab.value;
    
    // Update the URL immediately
    router.get(
        route('transfers.index'),
        params,
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['transfers']
        }
    );
};

const updateWarehouseFilter = () => {
    if (filters.value.selected_warehouses && filters.value.selected_warehouses.length > 0) {
        filters.value.warehouse_id = filters.value.selected_warehouses.map(w => w.id).join(',');
    } else {
        filters.value.warehouse_id = '';
    }
};

const updateLocationFilter = () => {
    if (filters.value.selected_locations && filters.value.selected_locations.length > 0) {
        filters.value.location_id = filters.value.selected_locations.map(l => l.id).join(',');
    } else {
        filters.value.location_id = '';
    }
};

// Watch for filter changes and update URL
watch(filters, (newFilters) => {
    // Create a params object with only non-empty values
    const params = {};
    
    // Only add filter values that are not empty
    if (newFilters.search) params.search = newFilters.search;
    if (newFilters.facility_id) params.facility_id = newFilters.facility_id;
    if (newFilters.warehouse_id) params.warehouse_id = newFilters.warehouse_id;
    if (newFilters.location_id) params.location_id = newFilters.location_id;
    if (newFilters.date_from) params.date_from = newFilters.date_from;
    if (newFilters.date_to) params.date_to = newFilters.date_to;
    
    // Only include the tab parameter if it's not 'all'
    if (currentTab.value !== 'all') params.tab = currentTab.value;
    
    router.get(
        route('transfers.index'),
        params,
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['transfers']
        }
    );
}, { deep: true });

// Watch for tab changes
watch(currentTab, (newTab) => {
    // Create a params object with only non-empty values
    const params = {};
    
    // Only add filter values that are not empty
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.facility_id) params.facility_id = filters.value.facility_id;
    if (filters.value.warehouse_id) params.warehouse_id = filters.value.warehouse_id;
    if (filters.value.location_id) params.location_id = filters.value.location_id;
    if (filters.value.date_from) params.date_from = filters.value.date_from;
    if (filters.value.date_to) params.date_to = filters.value.date_to;
    
    // Only add tab if it's not 'all'
    if (newTab !== 'all') params.tab = newTab;
    
    router.get(
        route('transfers.index'),
        params,
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
            only: ['transfers']
        }
    );
});

// Status configuration
const statusTabs = [
    { value: 'all', label: 'All Transfers', color: 'blue' },
    { value: 'pending', label: 'Pending Approval', color: 'yellow' },
    { value: 'approved', label: 'Approved', color: 'green' },
    { value: 'in_process', label: 'In Process', color: 'blue' },
    { value: 'dispatched', label: 'Dispatched', color: 'purple' },
    { value: 'received', label: 'Received', color: 'gray' },
    { value: 'rejected', label: 'Rejected', color: 'red' },
];

const filteredTransfers = computed(() => {
    // Start with all transfers - no facility-based filtering
    let filtered = props.transfers;
    
    // Only filter by tab if not 'all'
    if (currentTab.value !== 'all') {
        filtered = filtered.filter(transfer => {
            const status = transfer.status?.toLowerCase() || '';
            return status === currentTab.value;
        });
    }

    // Apply search filter
    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        filtered = filtered.filter(transfer => {
            return (
                transfer.transferID?.toLowerCase().includes(searchTerm) ||
                transfer.fromFacility?.name?.toLowerCase().includes(searchTerm) ||
                transfer.toFacility?.name?.toLowerCase().includes(searchTerm) ||
                transfer.fromWarehouse?.name?.toLowerCase().includes(searchTerm) ||
                transfer.toWarehouse?.name?.toLowerCase().includes(searchTerm)
            );
        });
    }

    // Apply facility filter (supports multiple selections)
    if (filters.value.facility_id) {
        const facilityIds = filters.value.facility_id.split(',').map(id => parseInt(id));
        filtered = filtered.filter(transfer => {
            return (
                facilityIds.includes(transfer.from_facility_id) ||
                facilityIds.includes(transfer.to_facility_id)
            );
        });
    }

    // Apply warehouse filter (supports multiple selections)
    if (filters.value.warehouse_id) {
        const warehouseIds = filters.value.warehouse_id.split(',').map(id => parseInt(id));
        filtered = filtered.filter(transfer => {
            return (
                warehouseIds.includes(transfer.from_warehouse_id) ||
                warehouseIds.includes(transfer.to_warehouse_id)
            );
        });
    }

    // Apply location filter (supports multiple selections)
    if (filters.value.location_id) {
        const locationIds = filters.value.location_id.split(',').map(id => parseInt(id));
        filtered = filtered.filter(transfer => {
            if (transfer.items && transfer.items.length > 0) {
                return transfer.items.some(item => locationIds.includes(item.location_id));
            }
            return false;
        });
    }

    // Apply date range filter
    if (filters.value.date_from) {
        const fromDate = new Date(filters.value.date_from);
        filtered = filtered.filter(transfer => {
            const transferDate = new Date(transfer.transfer_date);
            return transferDate >= fromDate;
        });
    }

    if (filters.value.date_to) {
        const toDate = new Date(filters.value.date_to);
        // Set time to end of day
        toDate.setHours(23, 59, 59, 999);
        filtered = filtered.filter(transfer => {
            const transferDate = new Date(transfer.transfer_date);
            return transferDate <= toDate;
        });
    }

    return filtered;
});

const getTabCount = (tabName) => {
    if (tabName === 'all') {
        return props.transfers.length;
    }
    return props.transfers.filter(transfer => transfer.status === tabName).length;
};

const getStatusPercentage = (status) => {
    if (!props.transfers.length) return 0;
    const count = props.transfers.filter(transfer => transfer.status === status).length;
    return Math.round((count / props.transfers.length) * 100);
};

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

const getStatusActions = (transfer) => {
    // Debug the transfer status
    console.log('Transfer status:', transfer.status);

    const actions = [];
    
    // Get current user's facility ID from the shared data
    const currentUserFacilityId = usePage().props.facility?.id;

    // Ensure we're handling the status correctly
    const status = transfer.status?.toLowerCase() || '';
    
    // Check if this transfer is to the current user's facility
    const isToCurrentUserFacility = transfer.to_facility_id === currentUserFacilityId;
    // Check if this transfer is from the current user's facility
    const isFromCurrentUserFacility = transfer.from_facility_id === currentUserFacilityId;

    // Debug facility IDs to help troubleshoot
    console.log('Current user facility ID:', currentUserFacilityId);
    console.log('Transfer from facility ID:', transfer.from_facility_id);
    console.log('Transfer to facility ID:', transfer.to_facility_id);
    console.log('Is from current user facility:', isFromCurrentUserFacility);
    console.log('Is to current user facility:', isToCurrentUserFacility);

    // Check if the user has approval permissions
    const canApprove = usePage().props.auth.user.can_approve_transfers;
    
    // Only show actions for specific statuses: pending, approved, in_process, dispatched
    // Exclude actions for: rejected, received (these are terminal states)
    if (['rejected', 'received'].includes(status)) {
        return actions; // Return empty actions array for terminal states
    }

    switch (status) {
        case 'pending':
            // Only users with approval permissions can approve/reject
            if (canApprove) {
                actions.push({ label: 'Approve', status: 'approved', color: 'green', icon: '/assets/images/approved.png' });
                actions.push({ label: 'Reject', status: 'rejected', color: 'red', icon: '/assets/images/rejected.png' });
            }
            break;
        case 'approved':
            // Only the source facility can process an approved transfer
            if (isFromCurrentUserFacility) {
                actions.push({ label: 'Process', status: 'in_process', color: 'blue', icon: '/assets/images/inprocess.png' });
            }
            break;
        case 'in_process':
            // Only the source facility can dispatch an in-process transfer
            if (isFromCurrentUserFacility) {
                actions.push({ label: 'Dispatch', status: 'dispatched', color: 'purple', icon: '/assets/images/dispatch.png' });
            }
            break;
        case 'dispatched':
            // Only the destination facility can receive a dispatched transfer
            if (isToCurrentUserFacility) {
                actions.push({ label: 'Receive', status: 'received', color: 'green', icon: '/assets/images/received.png' });
            }
            break;
    }

    // Debug the actions being returned
    console.log('Actions for transfer:', actions);

    return actions;
};

const getBulkStatusActions = () => {
    // Get unique current statuses of selected transfers
    const currentStatuses = [...new Set(props.transfers
        .filter(transfer => selectedTransfers.value.includes(transfer.id))
        .map(transfer => transfer.status))];

    // If there are no selected transfers or multiple different statuses, return empty actions
    if (currentStatuses.length !== 1) {
        return [];
    }

    // Return actions based on the current status
    const currentStatus = currentStatuses[0];
    const actions = [];

    switch (currentStatus) {
        case 'pending':
            actions.push({ label: 'Approve Selected', status: 'approved', color: 'green' });
            actions.push({ label: 'Reject Selected', status: 'rejected', color: 'red' });
            break;
        case 'approved':
            actions.push({ label: 'Process Selected', status: 'in_process', color: 'blue' });
            break;
        case 'in_process':
            actions.push({ label: 'Dispatch Selected', status: 'dispatched', color: 'purple' });
            break;
        case 'dispatched':
            actions.push({ label: 'Deliver Selected', status: 'delivered', color: 'indigo' });
            break;
    }

    return actions;
};

const toggleAllTransfers = () => {
    if (isAllSelected.value) {
        selectedTransfers.value = [];
    } else {
        selectedTransfers.value = filteredTransfers.value.map(t => t.id);
    }
};

const clearSelection = () => {
    selectedTransfers.value = [];
};

// Track loading state for each transfer action
const loadingActions = ref({});

const changeStatus = (transferId, newStatus) => {
    console.log('Changing status for transfer:', transferId, 'to:', newStatus);

    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to change the transfer status to ${newStatus}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, change it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            let routeName;
            switch (newStatus) {
                case 'approved':
                    routeName = 'transfers.approve';
                    break;
                case 'rejected':
                    routeName = 'transfers.reject';
                    break;
                case 'in_process':
                    routeName = 'transfers.inProcess';
                    break;
                case 'dispatched':
                    routeName = 'transfers.dispatch';
                    break;
                default:
                    Toast.fire({
                        icon: 'error',
                        title: 'Invalid status transition'
                    });
                    return;
            }

            console.log('Using route:', routeName, 'for transferId:', transferId);

            // Set loading state for this specific action
            loadingActions.value[`${transferId}_${newStatus}`] = true;

            await axios.post(route(routeName, transferId))
                .then(response => {
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message || 'Transfer status has been updated.'
                    });
                    router.reload();
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                    Toast.fire({
                        icon: 'error',
                        title: error.response?.data?.message || 'Failed to update transfer status'
                    });
                    // Clear loading state on error
                    loadingActions.value[`${transferId}_${newStatus}`] = false;
                });
        }
    });
};

// Add missing functions for approve and reject actions
const approveTransfer = (transferId) => {
    changeStatus(transferId, 'approved');
};

const rejectTransfer = (transferId) => {
    changeStatus(transferId, 'rejected');
};

const markInProcess = (transferId) => {
    changeStatus(transferId, 'in_process');
};


</script>
