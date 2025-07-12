<script setup>
import Tab from './Tab.vue';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import moment from 'moment';
import Swal from 'sweetalert2';
import axios from 'axios';
import { TailwindPagination } from 'laravel-vue-pagination';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

const toast = useToast();

const props = defineProps({
    liquidates: Object,
    filters: Object,
    warehouses: Array,
    facilities: Array,
});

const isReviewing = ref([]);
const reviewLiquidation = (id, index) => {
    if (!id) return;
    isReviewing.value[index] = true;
    Swal.fire({
        title: 'Review Liquidation?',
        text: 'Are you sure you want to review this liquidation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, review it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isReviewing.value[index] = true;
            await axios.post(route('api.liquidate-disposal.liquidates.review', id))
                .then((response) => {
                    isReviewing.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Liquidation reviewed successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadLiquidates();
                    });
                })
                .catch((error) => {
                    isReviewing.value[index] = false;
                    console.error('Error reviewing liquidation:', error);
                    toast.error('An error occurred while reviewing the liquidation');
                });
        } else {
            isReviewing.value[index] = false;
        }
    });
};

const isApproving = ref([]);
const approveLiquidation = async (id, index) => {
    if (!id) return;
    isApproving.value[index] = true;
    Swal.fire({
        title: 'Approve Liquidation?',
        text: 'Are you sure you want to approve this liquidation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, approve it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isApproving.value[index] = true;
            await axios.post(route('api.liquidate-disposal.liquidates.approve', id))
                .then((response) => {
                    isApproving.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Liquidation approved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadLiquidates();
                    });
                })
                .catch((error) => {
                    isApproving.value[index] = false;
                    console.error('Error approving liquidation:', error);
                    toast.error('An error occurred while approving the liquidation');
                });
        } else {
            isApproving.value[index] = false;
        }
    });
};

const isRejecting = ref([]);
const rejectLiquidation = async (id, index) => {
    if (!id) return;

    try {
        const result = await Swal.fire({
            title: 'Reject Liquidation',
            icon: 'warning',
            html: '<div class="mb-3 flex flex-col"><label class="form-label">Reason for rejection</label><textarea id="rejection-reason" class="form-control" rows="3" placeholder="Enter your reason here..."></textarea></div>',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const reason = document.getElementById('rejection-reason').value;
                if (!reason.trim()) {
                    Swal.showValidationMessage('Please provide a reason for rejection');
                    return false;
                }
                return reason;
            },
            allowOutsideClick: () => !Swal.isLoading()
        });

        if (result.isConfirmed && result.value) {
            isRejecting.value[index] = true;
            await axios.post(route('api.liquidate-disposal.liquidates.reject', id), {
                reason: result.value
            });

            await Swal.fire({
                title: 'Success!',
                text: 'Liquidation rejected successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });

            // Refresh the page
            reloadLiquidates();
        }
    } catch (error) {
        console.error('Error rejecting liquidation:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to reject liquidation'
        });
    } finally {
        isRejecting.value[index] = false;
    }
};

const parseAttachments = (attachments) => {
    console.log('parseAttachments called with:', attachments);
    if (!attachments) {
        console.log('No attachments, returning empty array');
        return [];
    }
    const files = typeof attachments === 'string' ? JSON.parse(attachments) : attachments;
    console.log('Parsed files:', files);
    const result = files.map(file => ({
        name: file.name || file.path.split('/').pop(),
        url: `${file.path}`
    }));
    console.log('Final result:', result);
    return result;
};

const activeDropdown = ref(null);

const toggleDropdown = (id) => {
    console.log('Toggle dropdown clicked for ID:', id);
    console.log('Current activeDropdown:', activeDropdown.value);
    activeDropdown.value = activeDropdown.value === id ? null : id;
    console.log('New activeDropdown:', activeDropdown.value);
};

const handleClickOutside = (event) => {
    const dropdowns = document.querySelectorAll('.attachments-dropdown');
    let clickedInside = false;

    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            clickedInside = true;
        }
    });

    if (!clickedInside) {
        activeDropdown.value = null;
    }
};

const search = ref(props.filters?.search || "");
const per_page = ref(props.filters?.per_page || 25);
const warehouse = ref(props.filters?.warehouse || "");
const facility = ref(props.filters?.facility || "");
const status = ref(props.filters?.status || "");

const facilityOptions = computed(() => {
    const options = props.facilities?.map(name => ({
        value: name,
        label: name
    })) || [];
    return [{ value: "", label: 'All Facilities' }, ...options];
});

const statusOptions = computed(() => [
    { value: "", label: 'All Statuses' },
    { value: "pending", label: 'Pending' },
    { value: "reviewed", label: 'Reviewed' },
    { value: "approved", label: 'Approved' },
    { value: "rejected", label: 'Rejected' }
]);

watch([
    () => search.value,
    () => per_page.value,
    () => warehouse.value,
    () => facility.value,
    () => status.value,
    () => props.filters.page
], () => {
    reloadPage();
});

function reloadPage() {
    const query = {};
    if (search.value) query.search = search.value;
    if (per_page.value) {
        query.per_page = per_page.value;
    }
    if (warehouse.value) query.warehouse = warehouse.value;
    if (facility.value) query.facility = facility.value;
    if (status.value) query.status = status.value;
    if (props.filters.page) query.page = props.filters.page;
    
    router.get(route('liquidate-disposal.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['liquidates']
    });
}

function getResults(page = 1) {
    props.filters.page = page;
}

const getStatusBadge = (liquidate) => {
    if (liquidate.approved_at) {
        return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Approved', icon: '/assets/images/approve.png' };
    } else if (liquidate.rejected_at) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Rejected', icon: '/assets/images/rejected.png' };
    } else if (liquidate.reviewed_at) {
        return { class: 'bg-blue-100 text-blue-800 border-blue-200', text: 'Reviewed', icon: '/assets/images/review.png' };
    } else {
        return { class: 'bg-yellow-100 text-yellow-800 border-yellow-200', text: 'Pending', icon: '⏳' };
    }
};

const getTypeBadge = (type) => {
    const badges = {
        'Missing': { class: 'bg-orange-100 text-orange-800 border-orange-200', icon: '📦' },
        'Damaged': { class: 'bg-red-100 text-red-800 border-red-200', icon: '💥' },
        'Expired': { class: 'bg-purple-100 text-purple-800 border-purple-200', icon: '📅' },
        'Low quality': { class: 'bg-gray-100 text-gray-800 border-gray-200', icon: '⚠️' }
    };
    return badges[type] || { class: 'bg-gray-100 text-gray-800 border-gray-200', icon: '❓' };
};

// Group liquidates by back_order
const groupedLiquidates = computed(() => {
    const grouped = {};
    
    if (!props.liquidates.data) return grouped;
    
    props.liquidates.data.forEach(liquidate => {
        const backOrderKey = liquidate.back_order_id || 'no_back_order';
        
        if (!grouped[backOrderKey]) {
            grouped[backOrderKey] = {
                back_order: liquidate.back_order,
                liquidates: []
            };
        }
        
        grouped[backOrderKey].liquidates.push(liquidate);
    });
    
    return grouped;
});

// Toggle group expansion
const expandedGroups = ref(new Set());

const toggleGroup = (groupKey) => {
    if (expandedGroups.value.has(groupKey)) {
        expandedGroups.value.delete(groupKey);
    } else {
        expandedGroups.value.add(groupKey);
    }
};

// Combined onMounted hook
onMounted(() => {
    // Add click outside listener for dropdowns
    document.addEventListener('click', handleClickOutside);
    
    // Expand all groups by default
    Object.keys(groupedLiquidates.value).forEach(key => {
        expandedGroups.value.add(key);
    });
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <Tab title="Liquidate" activeTab="liquidate">
        <!-- Header Section -->
        <div class="">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <!-- Liquidate Icon -->
                        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Liquidation Records</h1>
                    </div>
                    <p class="text-gray-600 text-sm">Manage and track all liquidation activities</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1.5 rounded-lg shadow-sm">
                        <div class="text-xs font-medium">Total Records</div>
                        <div class="text-lg font-bold">{{ props.liquidates.total || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white p-4 mb-4">
                <!-- First Row: Search, Warehouse, Facility, Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Search -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Records</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                v-model="search"
                                placeholder="Search by ID, item name, barcode, batch number..."
                                class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                            >
                        </div>
                    </div>
                    
                    <!-- Warehouse Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                        <Multiselect
                            v-model="warehouse"
                            :options="props.warehouses"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Warehouse"
                            :show-labels="false"
                            class="text-sm text-black"
                        />
                    </div>
                    
                    <!-- Facility Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <Multiselect
                            v-model="facility"
                            :options="facilityOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            :show-labels="false"
                            placeholder="Filter by Facility"
                            label="label"
                            track-by="value"
                            class="text-sm text-black"
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <Multiselect
                            v-model="status"
                            :options="statusOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            :show-labels="false"
                            placeholder="Filter by Status"
                            label="label"
                            track-by="value"
                            class="text-sm text-black"
                        />
                    </div>
                </div>
                
                <!-- Second Row: Per Page Filter (Right Aligned) -->
                <div class="flex justify-end">
                    <div class="w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Per Page</label>
                        <select 
                            v-model="per_page" 
                            @change="props.filters.page = 1" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="5">5 per page</option>
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Table Section -->
        <div class="bg-white border border-gray-200 overflow-hidden">
            <div v-if="props.liquidates.data.length === 0" class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No liquidation records found</h3>
                <p class="mt-2 text-gray-500">Try adjusting your search criteria or check back later.</p>
            </div>

            <div v-else class="overflow-auto">
                <table class="min-w-full border border-gray-300 table-fixed">
                    <colgroup>
                        <col class="w-24">
                        <col class="w-80">
                        <col class="w-24">
                        <col class="w-28">
                        <col class="w-28">
                        <col class="w-24">
                        <col class="w-24">
                        <col class="w-24">
                        <col class="w-32">
                    </colgroup>
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Liquidate ID
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Item
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Quantity
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Batch Number
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Expiry Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Type
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider border-r border-gray-300">
                                Attachments
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capi tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <template v-for="(group, groupKey) in groupedLiquidates" :key="groupKey">
                            <!-- Group Header Row -->
                            <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">
                                <td colspan="9" class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <button 
                                                @click="toggleGroup(groupKey)"
                                                class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 hover:bg-blue-200 transition-colors duration-150"
                                            >
                                                <svg 
                                                    class="w-4 h-4 text-blue-600 transition-transform duration-150" 
                                                    :class="{ 'rotate-90': expandedGroups.has(groupKey) }"
                                                    fill="none" 
                                                    stroke="currentColor" 
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                            
                                            <div class="flex items-center space-x-2">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-600">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div>
                                                    <h3 class="text-sm font-bold text-gray-900">
                                                        {{ groupKey === 'no_back_order' ? 'Direct Liquidations' : group.back_order?.back_order_number || 'Unknown Back Order' }}
                                                    </h3>
                                                    <p class="text-xs text-gray-600">
                                                        {{ group.liquidates.length }} liquidation{{ group.liquidates.length > 1 ? 's' : '' }}
                                                        <span v-if="group.back_order && groupKey !== 'no_back_order'" class="ml-2">
                                                            • {{ group.back_order.reported_by }}
                                                            • {{ moment(group.back_order.back_order_date).format('DD/MM/YYYY') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ group.liquidates.length }} items
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Group Items -->
                            <template v-if="expandedGroups.has(groupKey)">
                                <tr 
                                    v-for="(liquidate, index) in group.liquidates" 
                                    :key="liquidate.id"
                                    class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-300"
                                >
                            <!-- Liquidate ID -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <div class="text-sm font-bold text-gray-900">
                                    #{{ liquidate.liquidate_id }}
                                </div>
                            </td>

                            <!-- Product Information -->
                            <td class="px-4 py-3 border-r border-gray-300">
                                <div class="space-y-1">
                                    <div class="text-sm font-semibold text-gray-900 leading-tight">
                                        {{ liquidate.product ? liquidate.product.name : 'N/A' }}
                                    </div>
                                    <div class="space-y-0.5 text-xs text-gray-600">
                                        <div><span class="font-medium">Barcode:</span> {{ liquidate.barcode || 'N/A' }}</div>
                                        <div v-if="liquidate.warehouse"><span class="font-medium">Warehouse:</span> {{ liquidate.warehouse }}</div>
                                        <div v-if="liquidate.location"><span class="font-medium">Location:</span> {{ liquidate.location }}</div>
                                        <div class="text-gray-500">
                                            {{ liquidate.liquidated_at ? moment(liquidate.liquidated_at).format('DD/MM/YYYY') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Quantity -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">{{ liquidate.quantity || 'N/A' }}</span>
                                    <span class="text-gray-500 ml-1">{{ liquidate.uom || '' }}</span>
                                </div>
                            </td>

                            <!-- Batch Number -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ liquidate.batch_number || 'N/A' }}
                                </div>
                            </td>

                            <!-- Expiry Date -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ liquidate.expire_date ? moment(liquidate.expire_date).format('DD/MM/YYYY') : 'N/A' }}
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <span :class="getStatusBadge(liquidate).class" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                    <img 
                                        v-if="getStatusBadge(liquidate).icon.startsWith('/assets')" 
                                        :src="getStatusBadge(liquidate).icon" 
                                        :alt="getStatusBadge(liquidate).text"
                                        class="w-3 h-3 mr-1"
                                    >
                                    <span v-else class="mr-1">{{ getStatusBadge(liquidate).icon }}</span>
                                    {{ getStatusBadge(liquidate).text }}
                                </span>
                            </td>

                            <!-- Type Column -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <span :class="getTypeBadge(liquidate.type).class" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border">
                                    <span class="mr-1">{{ getTypeBadge(liquidate.type).icon }}</span>
                                    {{ liquidate.type || 'N/A' }}
                                </span>
                            </td>

                            <!-- Attachments Column -->
                            <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                <div class="relative" v-if="parseAttachments(liquidate.attachments).length > 0">
                                    <button 
                                        @click.stop="toggleDropdown(liquidate.id)"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                    >
                                        <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        {{ parseAttachments(liquidate.attachments).length }} file{{ parseAttachments(liquidate.attachments).length > 1 ? 's' : '' }}
                                        <svg class="w-4 h-4 ml-1 text-gray-500 transition-transform duration-150" :class="{ 'rotate-180': activeDropdown === liquidate.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div 
                                        v-if="activeDropdown === liquidate.id"
                                        class="attachments-dropdown absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                    >
                                        <div class="p-3 border-b border-gray-200">
                                            <h3 class="text-sm font-semibold text-gray-900">Attachments</h3>
                                            <p class="text-xs text-gray-500 mt-1">Click to view or download files</p>
                                        </div>
                                        <div class="max-h-60">
                                            <div 
                                                v-for="(attachment, index) in parseAttachments(liquidate.attachments)" 
                                                :key="index"
                                                class="p-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors duration-150"
                                            >
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                                                        <!-- File Icon -->
                                                        <div class="flex-shrink-0">
                                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                        
                                                        <!-- File Info -->
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate" :title="attachment.name">
                                                                {{ attachment.name }}
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ attachment.url.split('/').pop() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Action Button -->
                                                    <div class="flex items-center flex-shrink-0">
                                                        <a 
                                                            :href="attachment.url" 
                                                            target="_blank"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors duration-150"
                                                            title="Open file"
                                                        >
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                            </svg>
                                                            Open
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- No attachments -->
                                <div v-else class="text-center">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-400 bg-gray-100 rounded-md">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        No files
                                    </span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div v-if="liquidate.approved_at" class="text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Closed
                                    </span>
                                </div>

                                <div v-else class="flex flex-col space-y-2">
                                    <button 
                                        v-if="!liquidate.reviewed_at && $page.props.auth.can.liquidate_review" 
                                        @click="reviewLiquidation(liquidate.id, index)"
                                        :disabled="isReviewing[index]"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                    >
                                        <svg v-if="isReviewing[index]" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isReviewing[index] ? 'Processing...' : 'Review' }}
                                    </button>

                                    <!-- Show approve/reject buttons after review -->
                                    <template v-if="liquidate.reviewed_at && !liquidate.approved_at">
                                        <div class="flex space-x-2">
                                            <button 
                                                @click="approveLiquidation(liquidate.id, index)" 
                                                :disabled="isApproving[index]"
                                                v-if="$page.props.auth.can.liquidate_approve"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
                                            >
                                                <svg v-if="isApproving[index]" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ isApproving[index] ? 'Approving...' : (liquidate.rejected_at ? 'Re-approve' : 'Approve') }}
                                            </button>
                                            <button 
                                                v-if="!liquidate.rejected_at && $page.props.auth.can.liquidate_approve" 
                                                @click="rejectLiquidation(liquidate.id, index)"
                                                :disabled="isRejecting[index]"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 disabled:bg-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150"
                                            >
                                                <svg v-if="isRejecting[index]" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                {{ isRejecting[index] ? 'Rejecting...' : 'Reject' }}
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-4 mb-6">
            <TailwindPagination :data="props.liquidates" :limit="3" @pagination-change-page="getResults" />
        </div>
    </Tab>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.rotate-180 {
    transform: rotate(180deg);
}
</style>