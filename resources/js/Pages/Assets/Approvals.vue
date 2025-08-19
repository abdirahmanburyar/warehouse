<template>
    <AuthenticatedLayout title="Approvals" :description="props.assetItem?.asset_number || 'Select an asset'">
        <!-- Asset Selection -->
        <div class="mb-6">
            <div class="w-[300px]">
                <Multiselect
                    v-model="selectedAsset"
                    :options="props.assets"
                    placeholder="Select assets"
                    @select="handleSelectAsset"
                    class="multiselect-modern"
                    :searchable="true"
                    :show-labels="false"
                    :close-on-select="true"
                />
            </div>
        </div>

        <!-- Asset Details and Approval Interface -->
        <div v-if="props.assetItem" class="space-y-6">
            <!-- Asset Information Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Asset Details -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h2 class="text-lg font-medium text-gray-900">
                        Asset Details
                    </h2>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-sm text-gray-600">Asset #{{ props.assetItem.asset_number }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-gray-600">Acquired: {{ formatDate(props.assetItem.acquisition_date) }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm text-gray-600">Location: {{ props.assetItem.region?.name || 'N/A' }} - {{ props.assetItem.asset_location?.name || 'N/A' }} - {{ props.assetItem.sub_location?.name || 'N/A' }}</span>
                    </div>
                </div>

                <!-- Asset Metadata -->
                <div>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                        <h2 class="text-xs font-medium text-gray-900">
                            Asset Information
                        </h2>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500">
                                    Fund Source
                                </p>
                                <p class="text-xs text-gray-900">
                                    {{ props.assetItem.fund_source?.name || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset Items Table -->
            <div class="bg-white">
                <div class="px-6 py-4">
                    <h2 class="text-xs text-gray-900 mb-4">Asset Items</h2>
                    <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                        <thead>
                            <tr style="background-color: #F4F7FB;">
                                <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Item</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Serial Number</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Category</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Type</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Asset Details</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Value</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Assignee</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="getNonApprovedItems(props.assetItem.asset_items).length === 0">
                                <td colspan="8" class="px-3 py-3 text-center text-gray-500 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    All asset items are already approved
                                </td>
                            </tr>
                            <template v-else v-for="item in getNonApprovedItems(props.assetItem.asset_items)" :key="item.id">
                                <tr class="hover:bg-gray-50 transition-colors duration-150 border-b" style="border-bottom: 1px solid #F4F7FB;">
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.asset_tag }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.serial_number }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.category?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.type?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.asset_name }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">${{ item.original_value || '0.00' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">{{ item.assignee?.name || 'N/A' }}</td>
                                    <td class="px-3 py-3 text-xs text-gray-900">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getItemStatusBadgeClass(item.status)">
                                            {{ formatItemStatus(item.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Asset Status Actions -->
            <div class="bg-white">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 text-start">
                        Asset Status Actions
                    </h3>
                    <div class="flex items-start mb-6">
                        <!-- Status Action Buttons -->
                        <div class="flex flex-wrap items-center justify-center gap-4 px-1 py-2">
                            <!-- Review button -->
                            <div class="relative">
                                <div class="flex flex-col">
                                    <button @click="changeStatus(props.assetItem.id, 'reviewed', 'is_reviewing')" 
                                            :disabled="isType['is_reviewing'] || props.assetItem.status !== 'pending_approval'"
                                            :class="[
                                                props.assetItem.status === 'pending_approval'
                                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                                    : props.assetItem.status === 'reviewed' || props.assetItem.status === 'approved'
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300 cursor-not-allowed',
                                            ]" 
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                        <img src="/assets/images/review.png" class="w-5 h-5 mr-2" alt="Review" />
                                        <span class="text-sm font-bold text-white">{{
                                            props.assetItem.status === 'reviewed' || props.assetItem.status === 'approved'
                                                ? "Reviewed"
                                                : isType["is_reviewing"]
                                                    ? "Please Wait..."
                                                    : "Review"
                                        }}</span>
                                    </button>
                                    <span v-show="props.assetItem?.reviewed_at" class="text-sm text-gray-600">
                                        On {{ formatDate(props.assetItem?.reviewed_at) }}
                                    </span>
                                    <span v-show="props.assetItem?.reviewed_by" class="text-sm text-gray-600">
                                        By {{ props.assetItem?.reviewed_by?.name }}
                                    </span>
                                </div>
                                <div v-if="props.assetItem.status === 'pending_approval'"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                            </div>

                            <!-- Approved button -->
                            <div class="relative" v-if="props.assetItem.status !== 'rejected'">
                                <div class="flex flex-col">
                                    <button @click="changeStatus(props.assetItem.id, 'approved', 'is_approve')" 
                                            :disabled="isType['is_approve'] || props.assetItem.status !== 'reviewed'"
                                            :class="[
                                                props.assetItem.status === 'reviewed'
                                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                                    : props.assetItem.status === 'approved'
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300 cursor-not-allowed',
                                            ]" 
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                        <svg v-if="isType['is_approve'] && props.assetItem.status === 'reviewed'" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <template v-else>
                                            <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                                            <span class="text-sm font-bold text-white">{{
                                                props.assetItem.status === 'approved'
                                                    ? "Approved"
                                                    : isType["is_approve"] ? "Please Wait..." : "Approve"
                                            }}</span>
                                        </template>
                                    </button>
                                    <span v-show="props.assetItem?.approved_at" class="text-sm text-gray-600">
                                        On {{ formatDate(props.assetItem?.approved_at) }}
                                    </span>
                                    <span v-show="props.assetItem?.approved_by" class="text-sm text-gray-600">
                                        By {{ props.assetItem?.approved_by?.name }}
                                    </span>
                                </div>
                                <div v-if="props.assetItem.status === 'reviewed'"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                            </div>

                            <!-- Reject button -->
                            <div class="relative" v-if="props.assetItem.status === 'pending_approval' || props.assetItem.status === 'reviewed'">
                                <div class="flex flex-col">
                                    <button @click="showRejectModal()" 
                                            :disabled="isType['is_reject']"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-red-500 hover:bg-red-600">
                                        <svg v-if="isType['is_reject']" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <template v-else>
                                            <img src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Reject" />
                                            <span class="text-sm font-bold text-white">{{
                                                props.assetItem.status === 'rejected'
                                                    ? "Rejected"
                                                    : isType["is_reject"] ? "Please Wait..." : "Reject"
                                            }}</span>
                                        </template>
                                    </button>
                                    <span v-show="props.assetItem?.rejected_at" class="text-sm text-gray-600">
                                        On {{ formatDate(props.assetItem?.rejected_at) }}
                                    </span>
                                    <span v-show="props.assetItem?.rejected_by" class="text-sm text-gray-600">
                                        By {{ props.assetItem?.rejected_by?.name }}
                                    </span>
                                </div>
                                <div v-if="props.assetItem.status === 'pending_approval' || props.assetItem.status === 'reviewed'"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-red-400 rounded-full animate-pulse"></div>
                            </div>

                            <!-- Restore button -->
                            <div class="relative" v-if="props.assetItem.status === 'rejected'">
                                <div class="flex flex-col">
                                    <button @click="restoreAsset(props.assetItem.id, 'pending_approval', 'is_restore')" 
                                            :disabled="isRestoring"
                                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-green-500">
                                        <svg v-if="isRestoring" class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <template v-else>
                                            <img src="/assets/images/restore.jpg" class="w-5 h-5 mr-2" alt="Restore" />
                                            <span class="text-sm font-bold text-white">{{ isRestoring ? "Restoring..." : "Restore Asset" }}</span>
                                        </template>
                                    </button>
                                </div>
                                <div v-if="props.assetItem.status === 'rejected'"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-green-400 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                    {{ props.filters }}
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import moment from 'moment';

const toast = useToast();

const props = defineProps({
    assets: Array,
    assetItem: Object,
    filters: Object
});


const selectedAsset = ref(props.filters.selectedAsset);

const showRejectModalFlag = ref(false);
const rejectionReason = ref('');
const isType = ref({});
const isRestoring = ref(false);

function handleSelectAsset() {
    const query = {};
    if (selectedAsset.value){
        query.selectedAsset = selectedAsset.value;
    }
    router.get(route('assets.approvals.index'), query, {
        preserveScroll: true,
        preserveState: true,
        only: ['assetItem'],
    });
}

watch([
    () => props.filters.selectedAsset,
    (newVal) => {
        handleSelectAsset();
    }
])

const changeStatus = async (assetId, newStatus, type) => {
    console.log(assetId, newStatus, type);
    
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to change the asset status to ${newStatus}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, change it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Set loading state
            isType.value[type] = true;

            try {
                if (newStatus === 'reviewed') {
                    await router.post(route('assets.review', assetId));
                } else if (newStatus === 'approved') {
                    await router.post(route('assets.approve-simple', assetId));
                }
                
                toast.success(`Asset status updated to ${newStatus}`);
                
                // Reload the page to show updated status
                router.get(route('assets.approvals.index'), { asset: props.assetItem?.asset_number });
            } catch (error) {
                toast.error(`Failed to update asset status to ${newStatus}`);
            } finally {
                // Reset loading state
                isType.value[type] = false;
            }
        }
    });
};

const restoreAsset = async (assetId, newStatus, type) => {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to restore the asset?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
    })
    .then(async (result) => {
        if (result.isConfirmed) {
            isRestoring.value = true;
            
            try {
                await router.post(route('assets.restore', assetId));
                toast.success('Asset restored successfully');
                router.get(route('assets.approvals.index'), { asset: props.assetItem?.asset_number });
            } catch (error) {
                toast.error('Failed to restore asset');
            } finally {
                isRestoring.value = false;
            }
        }
    });
};

const showRejectModal = () => {
    showRejectModalFlag.value = true;
};

const closeRejectModal = () => {
    showRejectModalFlag.value = false;
    rejectionReason.value = '';
};

const rejectAsset = async () => {
    if (!props.assetItem || !rejectionReason.value.trim()) {
        toast.error('Please provide a rejection reason');
        return;
    }

    try {
        await router.post(route('assets.reject-simple', props.assetItem.id), {
            rejection_reason: rejectionReason.value
        });
        
        toast.success('Asset rejected successfully');
        closeRejectModal();
        router.get(route('assets.approvals.index'), { asset: props.assetItem?.asset_number });
    } catch (error) {
        toast.error('Failed to reject asset');
    }
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
    return moment(date).format('DD/MM/YYYY');
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
</script>
