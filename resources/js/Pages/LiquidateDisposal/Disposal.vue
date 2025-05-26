<script setup lang="ts">
import Tab from './Tab.vue';
import ActionModal from '@/Components/ActionModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import moment from 'moment';
import Swal from 'sweetalert2';
import axios from 'axios';

interface Product {
    id: number;
    name: string;
    productID?: string;
}

interface User {
    id: number;
    name: string;
    username?: string;
    email?: string;
    warehouse_id?: number;
    facility_id?: number;
}

interface Inventory {
    id: number;
    batch_number?: string;
    location?: string;
    expiry_date?: string;
}

interface PackingList {
    id: number;
    packing_list_number: string;
    batch_number?: string;
    expire_date?: string;
    location_id?: number;
    warehouse_id?: number;
}

interface Disposal {
    id: number;
    product_id: number;
    purchase_order_id: number | null;
    packing_list_id: number | null;
    inventory_id: number | null;
    disposed_by: User;
    disposed_at: string;
    quantity: number;
    status: string;
    note: string;
    approved_by: number | null;
    approved_at: string | null;
    product?: Product;
    inventory?: Inventory;
    packing_list?: PackingList;
    attachments?: string | null;
}

const isLoading = ref(false);
const toast = useToast();
const showDisposalModal = ref(false);
const selectedItem = ref(null);

const props = defineProps<{
    disposals: Disposal[];
}>();

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

// Method to handle disposal form submission
const handleDisposalSubmit = async (formData) => {
    isLoading.value = true;
    try {
        const response = await axios.post('/api/disposal', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        if (response.status === 200) {
            toast.success('Item disposed successfully');
            showDisposalModal.value = false;
            // Refresh the page to show updated data
            router.get(route('liquidate-disposal.disposals'), {
                preserveState: true,
                preserveScroll: true,
                only: ['disposals']
            });
        }
    } catch (error) {
        console.error('Error disposing item:', error);
        toast.error(error.response?.data?.message || 'An error occurred while disposing the item');
    } finally {
        isLoading.value = false;
    }
};

const isReviewing = ref(false);
const reviewDisposal = (id) => {
    console.log(id);
    if (!id) return;    
    isReviewing.value = true;
    Swal.fire({
        title: 'Review Disposal?',
        text: 'Are you sure you want to review this disposal?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, review it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isReviewing.value = true;
            await axios.get(route('liquidate-disposal.disposals.review', id))
                .then((response) => {
                    isReviewing.value = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Disposal reviewed successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        router.get(route('liquidate-disposal.disposals'), {}, {
                            preserveState: true,
                            preserveScroll: true,
                            only: ['disposals']
                        });
                    });
                })
            .catch((error) => {
                isReviewing.value = false;
                console.error('Error reviewing disposal:', error);
                toast.error('An error occurred while reviewing the disposal');
            });
        } else {
            isReviewing.value = false;
        }
    });
};

const isApproving = ref(false);
const approveDisposal = async (id) => {
    if (!id) return;
    isApproving.value = true;
    Swal.fire({
        title: 'Approve Liquidation?',
        text: 'Are you sure you want to approve this liquidation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isApproving.value = true;
            await axios.get(route('liquidate-disposal.disposals.approve', id))
                .then((response) => {
                    isApproving.value = false;
                    Swal.fire({
                        title: 'Success!',
                        text: 'Liquidation approved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        router.get(route('liquidate-disposal.disposals'), {}, {
                            preserveState: true,
                            preserveScroll: true,
                            only: ['disposals']
                        });
                    });
                })
            .catch((error) => {
                isApproving.value = false;
                console.error('Error approving liquidation:', error);
                toast.error('An error occurred while approving the liquidation');
            });
        } else {
            isApproving.value = false;
        }
    });
};

const isRejecting = ref(false);
const rejectDisposal = async (id) => {
    if (!id) return;
    
    // First prompt for rejection reason
    const { value: reason } = await Swal.fire({
        title: 'Rejection Reason',
        input: 'textarea',
        inputLabel: 'Please provide a reason for rejection',
        inputPlaceholder: 'Enter your reason here...',
        inputAttributes: {
            'aria-label': 'Rejection reason'
        },
        showCancelButton: true,
        inputValidator: (value) => {
            if (!value) {
                return 'You must provide a reason for rejection'
            }
        }
    });

    if (!reason) return; // User cancelled or didn't provide a reason

    // Then confirm the rejection
    isRejecting.value = true;
    Swal.fire({
        title: 'Confirm Rejection',
        text: 'Are you sure you want to reject this liquidation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, reject it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isRejecting.value = true;
            await axios.post(route('liquidate-disposal.liquidates.reject', id), {
                reason: reason
            })
            .then((response) => {
                isRejecting.value = false;
                Swal.fire({
                    title: 'Success!',
                    text: 'Liquidation rejected successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    router.get(route('liquidate-disposal.disposals'), {}, {
                        preserveState: true,
                        preserveScroll: true,
                        only: ['disposals']
                    });
                });
            })
            .catch((error) => {
                isRejecting.value = false;
                console.error('Error rejecting liquidation:', error);
                toast.error('An error occurred while rejecting the liquidation');
            });
        } else {
            isRejecting.value = false;
        }
    });

};

// Method to rollback an approved liquidation
/**
 * Parse JSON attachments string into an array of attachment objects
 */
const parseAttachments = (attachmentsJson: string | null) => {
    if (!attachmentsJson) return [];
    
    try {
        return typeof attachmentsJson === 'string' 
            ? JSON.parse(attachmentsJson) 
            : attachmentsJson;
    } catch (error) {
        console.error('Error parsing attachments:', error);
        return [];
    }
};

</script>

<template>
    <Tab title="Disposal" activeTab="disposal">
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <h2 class="text-xl font-semibold">Disposal Records</h2>
        </div>
        <!-- Table Section -->
        <div class="overflow-x-auto">
            <table class="min-w-full border border-collapse border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">SN</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Disposal ID</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Item</th>
                        <th class="w-[300px] px-4 py-2 border-r border-gray-300 text-left text-black">Item Info</th>
                        <th class="w-[300px] px-4 py-2 border-r border-gray-300 text-left text-black">Warehouse/Location</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Disposed At</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Note</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Attachments</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Status</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="props.disposals.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No disposal records found</td>
                    </tr>
                    <tr v-for="(disposal, index) in props.disposals" :key="disposal.id" class="border-b border-gray-300">
                        <td class="px-4 py-2 border-r border-gray-300">{{ index + 1 }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ disposal.disposal_id }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.product ? disposal.product.name : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 flex flex-col">
                            <span>Batch Number: {{ disposal.batch_number || 'N/A' }}</span>
                            <span>Barcode: {{ disposal.barcode || 'N/A' }}</span>
                            <span>Expiry Date: {{ disposal.expire_date ? moment(disposal.expire_date).format('DD/MM/YYYY') : 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 ">
                           <div class="flex flex-col">
                                <span>Warehouse: {{ disposal.packing_list?.warehouse?.name || disposal.inventory?.warehouse?.name || 'N/A' }}</span>
                                <span>Location: {{ disposal.packing_list?.location?.location || disposal.inventory?.location?.location || 'N/A' }}</span>
                           </div>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.disposed_at ? new Date(disposal.disposed_at).toLocaleDateString() : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.note || 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="parseAttachments(disposal.attachments).length > 0" class="flex flex-wrap gap-1">
                                <a 
                                    v-for="attachment in parseAttachments(disposal.attachments)" 
                                    :key="attachment.name"
                                    :href="attachment.url"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800 underline text-sm"
                                >
                                    {{ attachment.name }}
                                </a>
                            </div>
                            <span v-else class="text-gray-500 text-sm">No attachments</span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div class="flex flex-col gap-1">
                                <!-- Always show Pending -->
                                <span class="text-gray-600 text-sm">Pending</span>
                                
                                <!-- Show Reviewed if reviewed -->
                                <template v-if="disposal.reviewed_at">
                                    <span class="flex items-center text-sm">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-blue-600">Reviewed</span>
                                    </span>
                                </template>
                                
                                <!-- Show Approved/Rejected if either one is present -->
                                <template v-if="disposal.approved_at">
                                    <span class="flex items-center text-sm">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-green-600">Approved</span>
                                    </span>
                                </template>
                                <template v-if="disposal.rejected_at">
                                    <span class="flex items-center text-sm">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-red-600">Rejected</span>
                                    </span>
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="disposal.approved_at" class="text-gray-600 text-sm">
                                Closed (Approved)
                            </div>
                            <div v-else class="flex flex-col gap-2">
                                <button 
                                    v-if="!disposal.reviewed_at" 
                                    @click="reviewDisposal(disposal.id)" 
                                    :disabled="isReviewing"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm">
                                    {{isReviewing ? 'Processing...' : 'Review'}}
                                </button>
                                <!-- Show approve/reject buttons after review -->
                                <template v-if="disposal.reviewed_at && !disposal.approved_at">
                                    <div class="flex flex-col gap-2">
                                        <button 
                                            @click="approveDisposal(disposal.id)" 
                                            :disabled="isApproving"
                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm">
                                            {{isApproving ? 'Processing...' : disposal.rejected_at ? 'Approve (After Revision)' : 'Approve'}}
                                        </button>
                                        <button 
                                            v-if="!disposal.rejected_at"
                                            @click="rejectDisposal(disposal.id)"
                                            :disabled="isRejecting"
                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                            {{isRejecting ? 'Processing...' : 'Reject'}}
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>                       
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Disposal Modal -->
    <ActionModal
        :is-open="showDisposalModal"
        title="Dispose Item"
        action-type="disposal"
        :item="selectedItem"
        @close="showDisposalModal = false"
        @submit="handleDisposalSubmit"
    />
</Tab>
</template>