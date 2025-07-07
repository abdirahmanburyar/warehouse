<script setup>
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const toast = useToast();

const props = defineProps({
    receivedBackorders: Object,
    filters: Object,
    stats: Object,
    warehouses: Array,
    facilities: Array,
});

const isReviewing = ref([]);
const reviewReceivedBackorder = (id, index) => {
    if (!id) return;
    isReviewing.value[index] = true;
    Swal.fire({
        title: 'Review Received Back Order?',
        text: 'Are you sure you want to review this received back order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3B82F6',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, review it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isReviewing.value[index] = true;
            await axios.post(route('supplies.received-backorder.review', id))
                .then((response) => {
                    isReviewing.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Received back order reviewed successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadReceivedBackorders();
                    });
                })
                .catch((error) => {
                    isReviewing.value[index] = false;
                    console.error('Error reviewing received back order:', error);
                    toast.error('An error occurred while reviewing the received back order');
                });
        } else {
            isReviewing.value[index] = false;
        }
    });
};

const isApproving = ref([]);
const approveReceivedBackorder = async (id, index) => {
    if (!id) return;
    isApproving.value[index] = true;
    Swal.fire({
        title: 'Approve Received Back Order?',
        text: 'Are you sure you want to approve this received back order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, approve it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isApproving.value[index] = true;
            await axios.post(route('supplies.received-backorder.approve', id))
                .then((response) => {
                    isApproving.value[index] = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Received back order approved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        reloadReceivedBackorders();
                    });
                })
                .catch((error) => {
                    isApproving.value[index] = false;
                    console.error('Error approving received back order:', error);
                    toast.error('An error occurred while approving the received back order');
                });
        } else {
            isApproving.value[index] = false;
        }
    });
};

const isRejecting = ref([]);
const rejectReceivedBackorder = async (id, index) => {
    if (!id) return;

    try {
        const result = await Swal.fire({
            title: 'Reject Received Back Order',
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
            await axios.post(route('supplies.received-backorder.reject', id), {
                rejection_reason: result.value
            });

            await Swal.fire({
                title: 'Success!',
                text: 'Received back order rejected successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            });

            reloadReceivedBackorders();
        }
    } catch (error) {
        console.error('Error rejecting received back order:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Failed to reject received back order'
        });
    } finally {
        isRejecting.value[index] = false;
    }
};

const search = ref(props.filters?.search || "");
const per_page = ref(props.filters?.per_page || 25);
const status = ref(props.filters?.status || "");
const type = ref(props.filters?.type || "");
const date_from = ref(props.filters?.date_from || "");
const date_to = ref(props.filters?.date_to || "");
const warehouse = ref(props.filters?.warehouse || "");
const facility = ref(props.filters?.facility || "");

const facilityOptions = computed(() => {
    const options = props.facilities?.map(name => ({
        value: name,
        label: name
    })) || [];
    return [{ value: "", label: 'All Facilities' }, ...options];
});

const warehouseOptions = computed(() => {
    const options = props.warehouses?.map(name => ({
        value: name,
        label: name
    })) || [];
    return [{ value: "", label: 'All Warehouses' }, ...options];
});

const statusOptions = computed(() => [
    { value: "", label: 'All Statuses' },
    { value: "pending", label: 'Pending' },
    { value: "reviewed", label: 'Reviewed' },
    { value: "approved", label: 'Approved' },
    { value: "rejected", label: 'Rejected' }
]);

const typeOptions = computed(() => [
    { value: "", label: 'All Types' },
    { value: "missing", label: 'Missing' },
    { value: "damaged", label: 'Damaged' },
    { value: "expired", label: 'Expired' },
    { value: "low quality", label: 'Low Quality' }
]);

watch([
    () => search.value,
    () => per_page.value,
    () => status.value,
    () => type.value,
    () => date_from.value,
    () => date_to.value,
    () => warehouse.value,
    () => facility.value,
    () => props.filters.page
], () => {
    reloadPage();
});

function reloadPage() {
    const query = {};
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (status.value) query.status = status.value;
    if (type.value) query.type = type.value;
    if (date_from.value) query.date_from = date_from.value;
    if (date_to.value) query.date_to = date_to.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (facility.value) query.facility = facility.value;
    if (props.filters.page) query.page = props.filters.page;
    
    router.get(route('supplies.received-backorder.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['receivedBackorders']
    });
}

function reloadReceivedBackorders() {
    router.reload({ only: ['receivedBackorders'] });
}

function getResults(page = 1) {
    props.filters.page = page;
}

const getStatusBadge = (receivedBackorder) => {
    if (receivedBackorder.approved_at) {
        return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Approved', icon: '/assets/images/approve.png' };
    } else if (receivedBackorder.rejected_at) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Rejected', icon: '/assets/images/rejected.png' };
    } else if (receivedBackorder.reviewed_at) {
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

// Group received backorders by back_order
const groupedReceivedBackorders = computed(() => {
    const grouped = {};
    
    if (!props.receivedBackorders.data) return grouped;
    
    props.receivedBackorders.data.forEach(receivedBackorder => {
        const backOrderKey = receivedBackorder.back_order_id || 'no_back_order';
        
        if (!grouped[backOrderKey]) {
            grouped[backOrderKey] = {
                back_order: receivedBackorder.back_order,
                receivedBackorders: []
            };
        }
        
        grouped[backOrderKey].receivedBackorders.push(receivedBackorder);
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

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

const formatDate = (date) => {
    return moment(date).format('DD/MM/YYYY');
};

// Combined onMounted hook
onMounted(() => {
    // Expand all groups by default
    Object.keys(groupedReceivedBackorders.value).forEach(key => {
        expandedGroups.value.add(key);
    });
    
    // Debug: Check if warehouses and facilities are being passed
    console.log('Warehouses:', props.warehouses);
    console.log('Facilities:', props.facilities);
    console.log('Warehouse Options:', warehouseOptions.value);
    console.log('Facility Options:', facilityOptions.value);
});

</script>

<template>
    <Head title="Received Back Orders" />
    <AuthenticatedLayout title="Received Back Orders"
        description="Manage and track received back orders" img="/assets/images/supplies.png">
        
        <!-- Header Section -->
        <div class="">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="flex items-center space-x-3 mb-1">
                        <!-- Received Back Order Icon -->
                        <div class="flex items-center justify-center w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Received Back Orders</h1>
                    </div>
                    <p class="text-gray-600 text-sm">Manage and track all received back order activities</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-green-400 to-blue-500 text-white px-3 py-1.5 rounded-lg shadow-sm">
                        <div class="text-xs font-medium">Total Records</div>
                        <div class="text-lg font-bold">{{ props.receivedBackorders.total || 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="bg-white p-4 mb-4">
                <!-- First Row: Search, Warehouse, Facility (3 columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
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
                            :options="warehouseOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            :show-labels="false"
                            placeholder="Filter by Warehouse"
                            label="label"
                            track-by="value"
                            @select="() => {}"
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
                            @select="() => {}"
                            class="text-sm text-black"
                        />
                    </div>
                </div>
                
                <!-- Second Row: Status, Type, Date From, Date To (4 columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
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
                    
                    <!-- Type Filter -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <Multiselect
                            v-model="type"
                            :options="typeOptions"
                            :searchable="true"
                            :allow-empty="true"
                            :multiple="false"
                            :show-labels="false"
                            placeholder="Filter by Type"
                            label="label"
                            track-by="value"
                            class="text-sm text-black"
                        />
                    </div>
                    
                    <!-- Date From -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input 
                            type="date" 
                            v-model="date_from"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                    
                    <!-- Date To -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input 
                            type="date" 
                            v-model="date_to"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>
                </div>
                
                <!-- Third Row: Per Page Filter (Right Aligned) -->
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

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Received</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.total }}</p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Pending</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.pending }}</p>
                    </div>
                    <div class="p-2 bg-yellow-50 rounded-lg">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Quantity</p>
                        <p class="text-lg font-bold text-gray-800">{{ stats.total_quantity }}</p>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-1">Total Cost</p>
                        <p class="text-lg font-bold text-gray-800">{{ formatCurrency(stats.total_cost) }}</p>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Table Section -->
        <div class="bg-white border border-gray-200 overflow-hidden">
            <div v-if="props.receivedBackorders.data.length === 0" class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-full w-full">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No received back order records found</h3>
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
                        <col class="w-28">
                        <col class="w-24">
                        <col class="w-24">
                        <col class="w-24">
                        <col class="w-32">
                    </colgroup>
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                RB ID
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Item
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Quantity
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Batch Number
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Expiry Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Received Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Type
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider border-r border-gray-300">
                                Total Cost
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 capitalize tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <template v-for="(group, groupKey) in groupedReceivedBackorders" :key="groupKey">
                            <!-- Group Header Row -->
                            <tr class="bg-gradient-to-r from-green-50 to-blue-50 border-b-2 border-green-200">
                                <td colspan="10" class="px-4 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <button 
                                                @click="toggleGroup(groupKey)"
                                                class="flex items-center justify-center w-6 h-6 rounded-full bg-green-100 hover:bg-green-200 transition-colors duration-150"
                                            >
                                                <svg 
                                                    class="w-4 h-4 text-green-600 transition-transform duration-150" 
                                                    :class="{ 'rotate-90': expandedGroups.has(groupKey) }"
                                                    fill="none" 
                                                    stroke="currentColor" 
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                            
                                            <div class="flex items-center space-x-2">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-green-500 to-blue-600">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                
                                                <div>
                                                    <h3 class="text-sm font-bold text-gray-900">
                                                        {{ groupKey === 'no_back_order' ? 'Direct Received Back Orders' : group.back_order?.back_order_number || 'Unknown Back Order' }}
                                                    </h3>
                                                    <p class="text-xs text-gray-600">
                                                        {{ group.receivedBackorders.length }} received back order{{ group.receivedBackorders.length > 1 ? 's' : '' }}
                                                        <span v-if="group.back_order && groupKey !== 'no_back_order'" class="ml-2">
                                                            • {{ group.back_order.reported_by }}
                                                            • {{ moment(group.back_order.back_order_date).format('DD/MM/YYYY') }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ group.receivedBackorders.length }} items
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Group Items -->
                            <template v-if="expandedGroups.has(groupKey)">
                                <tr 
                                    v-for="(receivedBackorder, index) in group.receivedBackorders" 
                                    :key="receivedBackorder.id"
                                    class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-300"
                                >
                                    <!-- RB ID -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm font-bold text-gray-900">
                                            #{{ receivedBackorder.received_backorder_number }}
                                        </div>
                                    </td>

                                    <!-- Product Information -->
                                    <td class="px-4 py-3 border-r border-gray-300">
                                        <div class="space-y-1">
                                            <div class="text-sm font-semibold text-gray-900 leading-tight">
                                                {{ receivedBackorder.product ? receivedBackorder.product.name : 'N/A' }}
                                            </div>
                                            <div class="space-y-0.5 text-xs text-gray-600">
                                                <div><span class="font-medium">Barcode:</span> {{ receivedBackorder.barcode || 'N/A' }}</div>
                                                <div v-if="receivedBackorder.warehouse"><span class="font-medium">Warehouse:</span> {{ receivedBackorder.warehouse }}</div>
                                                <div v-if="receivedBackorder.location"><span class="font-medium">Location:</span> {{ receivedBackorder.location }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Quantity -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm">
                                            <span class="font-semibold text-gray-900">{{ receivedBackorder.quantity || 'N/A' }}</span>
                                            <span class="text-gray-500 ml-1">{{ receivedBackorder.uom || '' }}</span>
                                        </div>
                                    </td>

                                    <!-- Batch Number -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ receivedBackorder.batch_number || 'N/A' }}
                                        </div>
                                    </td>

                                    <!-- Expiry Date -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ receivedBackorder.expire_date ? moment(receivedBackorder.expire_date).format('DD/MM/YYYY') : 'N/A' }}
                                        </div>
                                    </td>

                                    <!-- Received Date -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ receivedBackorder.received_at ? moment(receivedBackorder.received_at).format('DD/MM/YYYY') : 'N/A' }}
                                        </div>
                                    </td>

                                    <!-- Status Column -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <span :class="getStatusBadge(receivedBackorder).class" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border">
                                            <img 
                                                v-if="getStatusBadge(receivedBackorder).icon.startsWith('/assets')" 
                                                :src="getStatusBadge(receivedBackorder).icon" 
                                                :alt="getStatusBadge(receivedBackorder).text"
                                                class="w-3 h-3 mr-1"
                                            >
                                            <span v-else class="mr-1">{{ getStatusBadge(receivedBackorder).icon }}</span>
                                            {{ getStatusBadge(receivedBackorder).text }}
                                        </span>
                                    </td>

                                    <!-- Type Column -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <span :class="getTypeBadge(receivedBackorder.type).class" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border">
                                            <span class="mr-1">{{ getTypeBadge(receivedBackorder.type).icon }}</span>
                                            {{ receivedBackorder.type || 'N/A' }}
                                        </span>
                                    </td>

                                    <!-- Total Cost -->
                                    <td class="px-4 py-3 whitespace-nowrap border-r border-gray-300">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ formatCurrency(receivedBackorder.total_cost) }}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div v-if="receivedBackorder.approved_at" class="text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Closed
                                            </span>
                                        </div>

                                        <div v-else class="flex flex-col space-y-2">
                                            <button 
                                                v-if="!receivedBackorder.reviewed_at" 
                                                @click="reviewReceivedBackorder(receivedBackorder.id, index)"
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
                                            <template v-if="receivedBackorder.reviewed_at && !receivedBackorder.approved_at">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        @click="approveReceivedBackorder(receivedBackorder.id, index)" 
                                                        :disabled="isApproving[index]"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 disabled:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150"
                                                    >
                                                        <svg v-if="isApproving[index]" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        {{ isApproving[index] ? 'Approving...' : (receivedBackorder.rejected_at ? 'Re-approve' : 'Approve') }}
                                                    </button>
                                                    <button 
                                                        v-if="!receivedBackorder.rejected_at" 
                                                        @click="rejectReceivedBackorder(receivedBackorder.id, index)"
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
            <TailwindPagination :data="props.receivedBackorders" :limit="3" @pagination-change-page="getResults" />
        </div>
    </AuthenticatedLayout>
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