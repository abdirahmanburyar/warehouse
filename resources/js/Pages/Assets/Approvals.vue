<template>
    <AuthenticatedLayout
        title="Asset Approvals"
        description="Review and approve pending asset requests"
        img="/assets/images/approval-header.png"
    >
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-pink-700 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white/20 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white">Asset Approvals</h1>
                                <p class="text-purple-100 text-sm mt-1">
                                    Review and approve pending asset requests
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 sm:mt-0">
                            <div class="flex items-center space-x-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white">{{ pendingCount }}</div>
                                    <div class="text-purple-100 text-sm">Pending Assets</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white">{{ totalNonApprovedItems }}</div>
                                    <div class="text-purple-100 text-sm">Items to Approve</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white">{{ approvedCount }}</div>
                                    <div class="text-purple-100 text-sm">Approved</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search by asset number, tag, serial number..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="filters.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="flex items-center mt-6">
                            <input id="for_approval" type="checkbox" v-model="filters.for_approval" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                            <label for="for_approval" class="ml-2 block text-sm text-gray-700">Only assets with items that need approval</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approvals List -->
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="p-6">
                    <div v-if="loading" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>

                    <div v-else-if="!props.approvals.data || props.approvals.data.length === 0" class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16 text-gray-400 mx-auto mb-4">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No approvals found</h3>
                        <p class="text-gray-500">There are no asset approvals matching your criteria.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div class="mb-2">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded mr-2" @change="toggleSelectionAll($event)" />
                                <button @click="bulkApprove" :disabled="selected.length===0" class="ml-2 px-3 py-1.5 text-sm rounded bg-green-600 text-white disabled:opacity-50">Bulk Approve</button>
                            </div>
                        </div>
                        
                        <div v-for="asset in props.approvals.data" :key="asset.id" 
                             class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            
                            <!-- Asset Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">Asset #{{ asset.asset_number }}</h3>
                                            <div class="text-sm text-gray-500 space-x-4">
                                                <span>Acquired: {{ formatDate(asset.acquisition_date) }}</span>
                                                <span>Fund Source: {{ asset.fundSource?.name || 'N/A' }}</span>
                                                <span>Region: {{ asset.region?.name || 'N/A' }}</span>
                                            </div>
                                            <div class="text-sm text-gray-500 space-x-4">
                                                <span>Location: {{ asset.assetLocation?.name || 'N/A' }}</span>
                                                <span v-if="asset.subLocation">Sub: {{ asset.subLocation.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Asset Status Badge -->
                                <div class="flex-shrink-0">
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                          :class="getStatusBadgeClass(asset.status)">
                                        {{ formatStatus(asset.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Asset Items -->
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Asset Items ({{ getNonApprovedItemsCount(asset.assetItems) }})</h4>
                                <div v-if="getNonApprovedItemsCount(asset.assetItems) === 0" class="text-center py-4 text-gray-500">
                                    <p>All asset items are already approved</p>
                                </div>
                                <div v-else class="space-y-2">
                                    <div v-for="item in getNonApprovedItems(asset.assetItems)" :key="item.id" 
                                         class="bg-gray-50 rounded-lg p-3 border-l-4 border-indigo-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-4">
                                                    <span class="text-sm font-medium text-gray-900">{{ item.asset_tag }}</span>
                                                    <span class="text-sm text-gray-600">{{ item.asset_name }}</span>
                                                    <span class="text-sm text-gray-500">SN: {{ item.serial_number }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span>Category: {{ item.category?.name || 'N/A' }}</span>
                                                    <span class="mx-2">•</span>
                                                    <span>Type: {{ item.type?.name || 'N/A' }}</span>
                                                    <span class="mx-2">•</span>
                                                    <span>Value: ${{ item.original_value || '0.00' }}</span>
                                                    <span v-if="item.assignee" class="mx-2">•</span>
                                                    <span v-if="item.assignee">Assignee: {{ item.assignee.name }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                                      :class="getItemStatusBadgeClass(item.status)">
                                                    {{ formatItemStatus(item.status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-4">
                                    <input type="checkbox" :value="asset.id" v-model="selected" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
                                    <span class="text-sm text-gray-500">Select for bulk action</span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <button v-if="asset.status === 'pending_approval'"
                                            @click="approveAsset(asset.id)"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                    
                                    <button v-if="asset.status === 'pending_approval'"
                                            @click="showRejectModal(asset)"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </button>
                                    
                                    <Link :href="route('assets.show', asset.id)"
                                          class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="props.approvals.links && props.approvals.links.length > 3" class="bg-white shadow-xl rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-t border-gray-200">
                    <Pagination :links="props.approvals.links" />
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModalFlag" @close="closeRejectModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Reject Asset Request</h2>
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea
                        id="rejection_reason"
                        v-model="rejectionReason"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Please provide a reason for rejection..."
                        required
                    ></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="closeRejectModal">Cancel</SecondaryButton>
                    <PrimaryButton @click="rejectAsset" :disabled="!rejectionReason.trim()" class="bg-red-600 hover:bg-red-700">
                        Reject Asset
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    approvals: Object,
    loading: Boolean,
});

const filters = ref({
    search: '',
    status: '',
    for_approval: true,
});

const selected = ref([]);
const showRejectModalFlag = ref(false);
const rejectionReason = ref('');
const assetToReject = ref(null);

const pendingCount = computed(() => {
    return props.approvals?.data?.filter(asset => asset.status === 'pending_approval').length || 0;
});

const approvedCount = computed(() => {
    return props.approvals?.data?.filter(asset => asset.status === 'approved').length || 0;
});

// Total count of non-approved items across all assets
const totalNonApprovedItems = computed(() => {
    if (!props.approvals?.data) return 0;
    return props.approvals.data.reduce((total, asset) => {
        return total + getNonApprovedItemsCount(asset.assetItems);
    }, 0);
});

const toggleSelectionAll = (event) => {
    if (event.target.checked) {
        selected.value = props.approvals.data.map(asset => asset.id);
    } else {
        selected.value = [];
    }
};

const bulkApprove = async () => {
    if (selected.value.length === 0) {
        toast.warning('Please select assets to approve');
        return;
    }

    try {
        await router.post(route('assets.bulk-approve'), {
            asset_ids: selected.value
        });
        
        toast.success(`${selected.value.length} assets approved successfully`);
        selected.value = [];
    } catch (error) {
        toast.error('Failed to approve assets');
    }
};

const approveAsset = async (assetId) => {
    try {
        await router.post(route('assets.approve-simple', assetId));
        toast.success('Asset approved successfully');
    } catch (error) {
        toast.error('Failed to approve asset');
    }
};

const showRejectModal = (asset) => {
    assetToReject.value = asset;
    showRejectModalFlag.value = true;
};

const closeRejectModal = () => {
    showRejectModalFlag.value = false;
    rejectionReason.value = '';
    assetToReject.value = null;
};

const rejectAsset = async () => {
    if (!assetToReject.value || !rejectionReason.value.trim()) {
        toast.error('Please provide a rejection reason');
        return;
    }

    try {
        await router.post(route('assets.reject-simple', assetToReject.value.id), {
            rejection_reason: rejectionReason.value
        });
        
        toast.success('Asset rejected successfully');
        closeRejectModal();
    } catch (error) {
        toast.error('Failed to reject asset');
    }
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending_approval': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'in_use': 'bg-blue-100 text-blue-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'retired': 'bg-gray-100 text-gray-800',
        'disposed': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getItemStatusBadgeClass = (status) => {
    const classes = {
        'pending_approval': 'bg-yellow-100 text-yellow-800',
        'in_use': 'bg-green-100 text-green-800',
        'maintenance': 'bg-orange-100 text-orange-800',
        'retired': 'bg-gray-100 text-gray-800',
        'disposed': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatStatus = (status) => {
    const statusMap = {
        'pending_approval': 'Pending Approval',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'in_use': 'In Use',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
    };
    return statusMap[status] || status;
};

const formatItemStatus = (status) => {
    const statusMap = {
        'pending_approval': 'Pending',
        'in_use': 'In Use',
        'maintenance': 'Maintenance',
        'retired': 'Retired',
        'disposed': 'Disposed',
    };
    return statusMap[status] || status;
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Helper function to get non-approved asset items
const getNonApprovedItems = (assetItems) => {
    if (!assetItems || !Array.isArray(assetItems)) return [];
    return assetItems.filter(item => item.status !== 'in_use' && item.status !== 'approved');
};

// Helper function to get count of non-approved asset items
const getNonApprovedItemsCount = (assetItems) => {
    return getNonApprovedItems(assetItems).length;
};

onMounted(() => {
    // Initialize filters if needed
});
</script> 