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

const isLoading = ref(false);
const toast = useToast();
const showDisposalModal = ref(false);
const selectedItem = ref(null);

const props = defineProps({
    disposals: Object,
    filters: Object,
    warehouses: Array,
    facilities: Array,
});

const search = ref(props.filters?.search || "");
const per_page = ref(props.filters?.per_page || 10);
const warehouse_filter = ref(props.filters?.warehouse_id || null);
const facility_filter = ref(props.filters?.facility_id || null);
const status_filter = ref(props.filters?.status || "");

// Format options for filters
const warehouseOptions = computed(() => {
    const options = props.warehouses?.map(name => ({
        value: name,
        label: name
    })) || [];
    return [{ value: null, label: 'All Warehouses' }, ...options];
});

const facilityOptions = computed(() => {
    const options = props.facilities?.map(name => ({
        value: name,
        label: name
    })) || [];
    return [{ value: null, label: 'All Facilities' }, ...options];
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
    () => warehouse_filter.value,
    () => facility_filter.value,
    () => status_filter.value,
    () => props.filters.page
], () => {
    reloadDisposals();
});

const reloadDisposals = () => {
    const query = {};
    if (search.value) {
        query.search = search.value;
    }
    if (per_page.value) {
        query.per_page = per_page.value;
        query.page = 1;
    }
    if (warehouse_filter.value) {
        query.warehouse_id = warehouse_filter.value;
    }
    if (facility_filter.value) {
        query.facility_id = facility_filter.value;
    }
    if (status_filter.value) {
        query.status = status_filter.value;
    }
    if (props.filters.page) {
        query.page = props.filters.page;
    }
    router.get(route('liquidate-disposal.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['disposals']
    });
};

// Method to open the disposal modal
const openDisposalModal = (disposal) => {
    selectedItem.value = {
        id: disposal.id,
        product: disposal.product,
        packing_list: disposal.packing_list,
        purchase_order_id: disposal.purchase_order_id,
        quantity: disposal.quantity,
        status: disposal.status,
        attachments: disposal.attachments
    };
    showDisposalModal.value = true;
};

const isReviewing = ref([]);
const reviewDisposal = (id, index) => {
    if (!id) return;
    isReviewing.value[index] = true;
    Swal.fire({
        title: 'Review Disposal?',
        text: 'Are you sure you want to review this disposal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, review it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isReviewing.value[index] = true;
            await axios.post(route('api.liquidate-disposal.disposals.review', id))
                .then((response) => {
                    isReviewing.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Disposal reviewed successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadDisposals();
                    });
                })
                .catch((error) => {
                    isReviewing.value[index] = false;
                    console.error('Error reviewing disposal:', error);
                    toast.error('An error occurred while reviewing the disposal');
                });
        } else {
            isReviewing.value[index] = false;
        }
    });
};

const isApproving = ref([]);
const approveDisposal = async (id, index) => {
    if (!id) return;
    isApproving.value[index] = true;
    Swal.fire({
        title: 'Approve Disposal?',
        text: 'Are you sure you want to approve this disposal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, approve it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isApproving.value[index] = true;
            await axios.post(route('api.liquidate-disposal.disposals.approve', id))
                .then((response) => {
                    isApproving.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Disposal approved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadDisposals();
                    });
                })
                .catch((error) => {
                    isApproving.value[index] = false;
                    console.error('Error approving disposal:', error);
                    toast.error('An error occurred while approving the disposal');
                });
        } else {
            isApproving.value[index] = false;
        }
    });
};

const isRejecting = ref([]);
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

const rejectDisposal = async (id, index) => {
    if (!id) return;

    try {
        const result = await Swal.fire({
            title: 'Reject Disposal',
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
            await axios.post(route('api.liquidate-disposal.disposals.reject', id), {
                reason: result.value
            });

            await Swal.fire({
                title: 'Success!',
                text: 'Disposal rejected successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });

            // Refresh the page
            reloadDisposals();
        }
    } catch (error) {
        console.error('Error rejecting disposal:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to reject disposal'
        });
    } finally {
        isRejecting.value[index] = false;
    }
};

// Method to rollback an approved disposal
const rollbackDisposal = async (id) => {
    if (confirm('Are you sure you want to rollback this disposal? This will revert it to pending status.')) {
        isLoading.value = true;
        try {
            const response = await axios.post(`/api/disposals/${id}/rollback`);
            
            toast.success('Disposal rolled back successfully');
            // Refresh the page to show updated data
            reloadDisposals();
        } catch (error) {
            console.error('Error rolling back disposal:', error);
            toast.error(error.response?.data?.message || 'Failed to rollback disposal');
        } finally {
            isLoading.value = false;
        }
    }
};

/**
 * Parse JSON attachments string into an array of attachment objects
 */
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

function getResults(page = 1) {
    props.filters.page = page;
}

// Group disposals by back_order
const groupedDisposals = computed(() => {
    const grouped = {};
    
    if (!props.disposals.data) return grouped;
    
    props.disposals.data.forEach(disposal => {
        const backOrderKey = disposal.back_order_id || 'no_back_order';
        
        if (!grouped[backOrderKey]) {
            grouped[backOrderKey] = {
                back_order: disposal.back_order,
                disposals: [],
                source_type: disposal.facility ? 'facility' : 'warehouse'
            };
        }
        
        grouped[backOrderKey].disposals.push(disposal);
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
    Object.keys(groupedDisposals.value).forEach(key => {
        expandedGroups.value.add(key);
    });
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const getStatusBadge = (disposal) => {
    if (disposal.approved_at) {
        return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Approved', icon: '✅' };
    } else if (disposal.rejected_at) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Rejected', icon: '❌' };
    } else if (disposal.reviewed_at) {
        return { class: 'bg-blue-100 text-blue-800 border-blue-200', text: 'Reviewed', icon: '🔍' };
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

</script>

<template>
    <Tab title="Disposal" activeTab="disposal">
        <!-- Header Section -->
        <div class="">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <!-- Disposal Icon -->
                        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-r from-red-400 to-red-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Disposal Records</h1>
                    </div>
                    <p class="text-gray-600 text-sm">Manage and track all disposal activities</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-red-400 to-red-600 text-white px-3 py-1.5 rounded-lg shadow-sm">
                        <div class="text-xs font-medium">Total Records</div>
                        <div class="text-lg font-bold">{{ props.disposals.total || 0 }}</div>
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
                            v-model="warehouse_filter"
                            :options="warehouseOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Warehouse"
                            label="label"
                            track-by="value"
                            :show-labels="false"
                            class="text-sm text-black"
                        />
                    </div>
                    
                    <!-- Facility Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <Multiselect
                            v-model="facility_filter"
                            :options="facilityOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Facility"
                            label="label"
                            track-by="value"
                            :show-labels="false"
                            class="text-sm text-black"
                        />
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <Multiselect
                            v-model="status_filter"
                            :options="statusOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="Filter by Status"
                            label="label"
                            track-by="value"
                            :show-labels="false"
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

            <!-- Modern Table Section -->
            <div class="bg-white border border-gray-200 mb-[100px]">
                <div v-if="props.disposals.data.length === 0" class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-gray-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No disposal records found</h3>
                    <p class="mt-2 text-gray-500">Try adjusting your search criteria or check back later.</p>
                </div>

                <div v-else>
                    <table class="min-w-full border border-gray-300 table-fixed">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-24">
                                Disposal ID
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-80">
                                Item
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-20">
                                Quantity
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-32">
                                Batch Number
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-28">
                                Expiry Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-20">
                                Source
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-24">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-24">
                                Type
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300 w-24">
                                Attachments
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider w-32">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template v-if="Object.keys(groupedDisposals).length === 0">
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    No disposal records found
                                </td>
                            </tr>
                        </template>
                        
                        <template v-for="(group, groupKey) in groupedDisposals" :key="groupKey">
                            <!-- Group Header -->
                            <tr class="bg-gray-50 hover:bg-gray-100 cursor-pointer" @click="toggleGroup(groupKey)">
                                <td colspan="10" class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <svg 
                                                class="w-4 h-4 transition-transform duration-200" 
                                                :class="{ 'rotate-90': expandedGroups.has(groupKey) }"
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            <div>
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-semibold text-gray-900">
                                                        {{ group.back_order?.back_order_number || 'No Back Order' }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" 
                                                          :class="group.source_type === 'facility' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                                        {{ group.source_type === 'facility' ? 'Facility' : 'Warehouse' }}
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ group.disposals.length }} disposal{{ group.disposals.length !== 1 ? 's' : '' }}
                                                    <span v-if="group.back_order?.reported_by" class="ml-2">
                                                        • {{ group.back_order.reported_by }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ group.back_order?.back_order_date ? moment(group.back_order.back_order_date).format('DD/MM/YYYY') : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Group Items -->
                            <template v-if="expandedGroups.has(groupKey)">
                                <tr v-for="(disposal, index) in group.disposals" :key="disposal.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-r border-gray-300 text-sm">
                                        {{ disposal.disposal_id }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300 text-sm">
                                        <div class="font-medium text-gray-900">{{ disposal.product?.name || 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ disposal.barcode || 'No barcode' }}</div>
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300 text-sm">
                                        {{ disposal.quantity || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300 text-sm">
                                        {{ disposal.batch_number || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300 text-sm">
                                        {{ disposal.expire_date ? moment(disposal.expire_date).format('DD/MM/YYYY') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full" 
                                              :class="disposal.facility ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                            {{ disposal.facility ? 'Facility' : 'Warehouse' }}
                                        </span>
                                        <div v-if="disposal.facility" class="text-xs text-gray-500 mt-1">
                                            {{ disposal.facility }}
                                        </div>
                                        <div v-else-if="disposal.warehouse" class="text-xs text-gray-500 mt-1">
                                            {{ disposal.warehouse }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" :class="getStatusBadge(disposal).class">
                                            <img v-if="getStatusBadge(disposal).icon && !getStatusBadge(disposal).icon.startsWith('/')" :src="getStatusBadge(disposal).icon" class="w-3 h-3 mr-1" alt="Status">
                                            <span v-else-if="getStatusBadge(disposal).icon" class="mr-1">{{ getStatusBadge(disposal).icon }}</span>
                                            {{ getStatusBadge(disposal).text }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border-r border-gray-300">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" :class="getTypeBadge(disposal.type).class">
                                            <span class="mr-1">{{ getTypeBadge(disposal.type).icon }}</span>
                                            {{ disposal.type || 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Attachments Column -->
                                    <td class="px-4 py-2 border-r border-gray-300">
                                        <div class="relative" v-if="parseAttachments(disposal.attachments).length > 0">
                                            <button 
                                                @click.stop="toggleDropdown(disposal.id)"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                            >
                                                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                                {{ parseAttachments(disposal.attachments).length }} file{{ parseAttachments(disposal.attachments).length > 1 ? 's' : '' }}
                                                <svg class="w-4 h-4 ml-1 text-gray-500 transition-transform duration-150" :class="{ 'rotate-180': activeDropdown === disposal.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div 
                                                v-if="activeDropdown === disposal.id"
                                                class="attachments-dropdown absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                            >
                                                <div class="p-3 border-b border-gray-200">
                                                    <h3 class="text-sm font-semibold text-gray-900">Attachments</h3>
                                                    <p class="text-xs text-gray-500 mt-1">Click to view or download files</p>
                                                </div>
                                                <div class="max-h-60">
                                                    <div 
                                                        v-for="(attachment, index) in parseAttachments(disposal.attachments)" 
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
                                    
                                    <td class="px-4 py-2">
                                        <div v-if="disposal.approved_at" class="text-gray-600 text-sm">
                                            Closed (Approved)
                                        </div>
                                        <div v-else class="flex flex-col gap-2">
                                            <button 
                                                v-if="!disposal.reviewed_at" 
                                                @click="reviewDisposal(disposal.id, index)"
                                                :disabled="isReviewing[index]"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition-colors"
                                            >
                                                {{ isReviewing[index] ? 'Processing...' : 'Review' }}
                                            </button>
                                            
                                            <template v-if="disposal.reviewed_at && !disposal.approved_at">
                                                <div class="flex flex-col gap-1">
                                                    <button 
                                                        @click="approveDisposal(disposal.id, index)" 
                                                        :disabled="isApproving[index]"
                                                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition-colors"
                                                    >
                                                        {{ isApproving[index] ? 'Processing...' : disposal.rejected_at ? 'Approve (After Revision)' : 'Approve'}}
                                                    </button>
                                                    <button 
                                                        v-if="!disposal.rejected_at" 
                                                        @click="rejectDisposal(disposal.id, index)"
                                                        :disabled="isRejecting[index]"
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition-colors"
                                                    >
                                                        {{ isRejecting[index] ? 'Processing...' : 'Reject' }}
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
                <TailwindPagination :data="props.disposals" :limit="3" @pagination-change-page="getResults" />
            </div>
        </div>
    </Tab>
</template>