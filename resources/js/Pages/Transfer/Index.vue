<template>
    <AuthenticatedLayout title="Optimize Your Transfers" description="Moving Supplies, Bridging needs"
        img="/assets/images/transfer.png">

            <!-- Header Section -->
            <div class="flex flex-col mb-6 space-y-6">
                <!-- Buttons First -->
                <div class="flex items-center space-x-4 justify-end">
                    <!-- New Transfer -->
                    <button @click="router.visit(route('transfers.create'))"
                        class="inline-flex items-center rounded-2xl px-4 py-2 border border-transparent text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        New Transfer
                    </button>
                </div>

                <!-- Filters Section -->
                <div class="mb-4">
                    <div class="grid grid-cols-4 gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" v-model="search"
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-2xl w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Search a Transfer">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Facility Selector -->
                        <div>
                            <Multiselect v-model="filters.selected_facility" :options="props.facilities"
                                :searchable="true" :allow-empty="true" :show-labels="false"
                                placeholder="All Facilities" label="name" track-by="id"
                                class="rounded-2xl" @input="updateFacilityFilter">
                                <template #singleLabel="{ option }">
                                    <span class="multiselect__single">
                                        {{ option ? option.name : 'All Facilities' }}
                                    </span>
                                </template>
                            </Multiselect>
                        </div>

                        <!-- Warehouse Selector -->
                        <div>
                            <Multiselect v-model="filters.selected_warehouses" :options="props.warehouses"
                                :multiple="true" :close-on-select="false" :clear-on-select="false"
                                :preserve-search="true" placeholder="All Warehouses" label="name" track-by="id"
                                class="rounded-2xl" :preselect-first="false" @input="updateWarehouseFilter">
                                <template #selection="{ values, search, isOpen }">
                                    <span class="multiselect__single" v-if="values.length && !isOpen">
                                        {{ values.length === 1 ? values[0].name : `${values.length} warehouses selected` }}
                                    </span>
                                </template>
                            </Multiselect>
                        </div>

                        <!-- Location Selector -->
                        <div>
                            <Multiselect v-model="filters.selected_locations" :options="props.locations"
                                :multiple="true" :close-on-select="false" :clear-on-select="false"
                                :preserve-search="true" placeholder="All Locations" label="location" track-by="id"
                                class="rounded-2xl" :preselect-first="false" @input="updateLocationFilter">
                                <template #selection="{ values, search, isOpen }">
                                    <span class="multiselect__single" v-if="values.length && !isOpen">
                                        {{ values.length === 1 ? values[0].location : `${values.length} locations selected` }}
                                    </span>
                                </template>
                            </Multiselect>
                        </div>
                    </div>
                    
                    <!-- Date Range Row -->
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <!-- From Date -->
                        <div class="relative">
                            <input type="date" v-model="filters.date_from"
                                class="pl-10 pr-2 py-2 border border-gray-300 rounded-2xl w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="From Date">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- To Date -->
                        <div class="relative">
                            <input type="date" v-model="filters.date_to"
                                class="pl-10 pr-2 py-2 border border-gray-300 rounded-2xl w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="To Date">
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
                            class="whitespace-nowrap py-4 px-3 border-b-4 font-bold text-xs" :class="[
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
            <div class="grid grid-cols-12 gap-1">
                <!-- Table Section (9 cols) -->
                <div class="col-span-9">
                    <div class="max-w-full overflow-auto">
                        <table class="min-w-full">
                            <thead style="background-color: #EEF1F8; border-top-right-radius: 50px; border-top-left-radius: 3xl;">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        Transfer ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        From
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        To
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        Items
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-[#6C75B8] uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="">
                                <tr v-if="props.transfers.length === 0">
                                    <td colspan="7" class="text-center text-gray-500 py-4">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <p class="mt-2 text-xs font-medium">No transfer data available</p>
                                            <p class="mt-1 text-xs">Create a new transfer or adjust your filters to see results</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="transfer in props.transfers" :key="transfer.id" class="hover:bg-gray-50 border-b border-gray-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        <Link :href="route('transfers.show', transfer.id)" class="hover:underline">
                                        {{ transfer.transferID }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ new Date(transfer.transfer_date).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ transfer.from_warehouse?.name || transfer.from_facility?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ transfer.to_warehouse?.name || transfer.to_facility?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ transfer.items_count }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
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
                <div class="col-span-3">
                    <div class="bg-white mb-4">
                        <h3 class="text-xs text-black mb-4">Transfer Statistics</h3>
                        <div class="flex justify-between gap-3">
                            <!-- Pending -->
                            <div class="flex flex-col items-center">
                                <div class="h-[320px] w-10 bg-amber-50 rounded-2xl relative overflow-hidden shadow-md">
                                    <div class="absolute top-0 inset-x-0 flex justify-center pt-2">
                                        <img src="/assets/images/pending_small.png" class="h-8 w-8 object-contain" alt="Pending" />
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-amber-500 to-amber-400 transition-all duration-500"
                                        :style="{ height: props.statistics.pending.percentage + '%' }">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-xs tracking-wide">
                                            {{ props.statistics.pending.percentage }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-medium text-gray-900">Pending</span>
                                    <div class="text-xs font-semibold text-gray-700">{{ props.statistics.pending.count }}</div>
                                </div>
                            </div>

                            <!-- Approved -->
                            <div class="flex flex-col items-center">
                                <div class="h-[320px] w-10 bg-blue-50 rounded-2xl relative overflow-hidden shadow-md">
                                    <div class="absolute top-0 inset-x-0 flex justify-center pt-2">
                                        <img src="/assets/images/approved_small.png" class="h-8 w-8 object-contain" alt="Approved" />
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-blue-600 to-blue-400 transition-all duration-500"
                                        :style="{ height: props.statistics.approved.percentage + '%' }">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-xs tracking-wide">
                                            {{ props.statistics.approved.percentage }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-medium text-gray-900">Approved</span>
                                    <div class="text-xs font-semibold text-gray-700">{{ props.statistics.approved.count }}</div>
                                </div>
                            </div>

                            <!-- In Process -->
                            <div class="flex flex-col items-center">
                                <div class="h-[320px] w-10 bg-slate-50 rounded-2xl relative overflow-hidden shadow-md">
                                    <div class="absolute top-0 inset-x-0 flex justify-center pt-2">
                                        <img src="/assets/images/inprocess.png" class="h-8 w-8 object-contain" alt="In Process" />
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-600 to-slate-400 transition-all duration-500"
                                        :style="{ height: props.statistics.in_process.percentage + '%' }">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-xs tracking-wide">
                                            {{ props.statistics.in_process.percentage }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-medium text-gray-900">In Process</span>
                                    <div class="text-xs font-semibold text-gray-700">{{ props.statistics.in_process.count }}</div>
                                </div>
                            </div>

                            <!-- Dispatched -->
                            <div class="flex flex-col items-center">
                                <div class="h-[320px] w-10 bg-purple-50 rounded-2xl relative overflow-hidden shadow-md">
                                    <div class="absolute top-0 inset-x-0 flex justify-center pt-2">
                                        <img src="/assets/images/dispatch.png" class="h-8 w-8 object-contain" alt="Dispatched" />
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-purple-600 to-purple-400 transition-all duration-500"
                                        :style="{ height: (props.statistics.dispatched?.percentage || 0) + '%' }">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-xs tracking-wide">
                                            {{ props.statistics.dispatched?.percentage || 0 }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-medium text-gray-900">Dispatched</span>
                                    <div class="text-xs font-semibold text-gray-700">{{ props.statistics.dispatched?.count || 0 }}</div>
                                </div>
                            </div>

                            <!-- Received -->
                            <div class="flex flex-col items-center">
                                <div class="h-[320px] w-10 bg-emerald-50 rounded-2xl relative overflow-hidden shadow-md">
                                    <div class="absolute top-0 inset-x-0 flex justify-center pt-2">
                                        <img src="/assets/images/received.png" class="h-8 w-8 object-contain" alt="Received" />
                                    </div>
                                    <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-emerald-600 to-emerald-400 transition-all duration-500"
                                        :style="{ height: (props.statistics.received?.percentage || 0) + '%' }">
                                        <div
                                            class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-bold text-xs tracking-wide">
                                            {{ props.statistics.received?.percentage || 0 }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-xs font-medium text-gray-900">Received</span>
                                    <div class="text-xs font-semibold text-gray-700">{{ props.statistics.received?.count || 0 }}</div>
                                </div>
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
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            facility_id: null,
            warehouse_id: null,
            location_id: null,
            date_from: null,
            date_to: null,
            tab: 'all'
        })
    }
});

const currentTab = ref('all');

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

const search = ref(props.filters.search);


</script>
