<script setup lang="ts">
import Tab from './Tab.vue';
import ActionModal from '@/Components/ActionModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

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
    expired_date: string | null;
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
const showDisposeModal = ref(false);
const selectedItem = ref(null);

const props = defineProps<{
    disposals: Disposal[];
}>();

// Method to open the dispose modal
const openDisposeModal = (item) => {
    selectedItem.value = item;
    showDisposeModal.value = true;
};

// Method to handle dispose form submission
const handleDisposeSubmit = async (formData) => {
    isLoading.value = true;
    try {
        const response = await fetch('/api/dispose', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            toast.success('Item disposed successfully');
            showDisposeModal.value = false;
            // Refresh the page to show updated data
            router.reload();
        } else {
            toast.error(result.message || 'Failed to dispose item');
        }
    } catch (error) {
        console.error('Error disposing item:', error);
        toast.error('An error occurred while disposing the item');
    } finally {
        isLoading.value = false;
    }
};

// Method to approve a disposal
const approveDisposal = async (id: number) => {
    if (confirm('Are you sure you want to approve this disposal?')) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/disposals/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Disposal approved successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to approve disposal');
            }
        } catch (error) {
            console.error('Error approving disposal:', error);
            toast.error('An error occurred while approving the disposal');
        } finally {
            isLoading.value = false;
        }
    }
};

// Method to reject a disposal
const rejectDisposal = async (id: number) => {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/disposals/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ reason })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Disposal rejected successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to reject disposal');
            }
        } catch (error) {
            console.error('Error rejecting disposal:', error);
            toast.error('An error occurred while rejecting the disposal');
        } finally {
            isLoading.value = false;
        }
    }
};

// Method to rollback an approved disposal
const rollbackDisposal = async (id: number) => {
    if (confirm('Are you sure you want to rollback this disposal? This will revert it to pending status.')) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/disposals/${id}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Disposal rolled back successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to rollback disposal');
            }
        } catch (error) {
            console.error('Error rolling back disposal:', error);
            toast.error('An error occurred while rolling back the disposal');
        } finally {
            isLoading.value = false;
        }
    }
};

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
        <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
            <h2 class="text-xl font-semibold">Disposal Records</h2>
            <button 
                @click="openDisposeModal(null)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                New Disposal
            </button>
        </div>

        <!-- Table Section -->
        <div class="overflow-auto">
            <table class="min-w-full border border-collapse border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">SN</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Disposal ID</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Product</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Batch No/Barcode</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Warehouse/Location</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Expiry Date</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Quantity</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Source & Reason</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Status</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Attachments</th>
                        <th class="px-4 py-2 text-left text-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="props.disposals.length === 0">
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">No disposal records found</td>
                    </tr>
                    <tr v-for="(disposal, index) in props.disposals" :key="disposal.id" class="border-b border-gray-300">
                        <td class="px-4 py-2 border-r border-gray-300">{{ index + 1 }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ disposal.id }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.product ? disposal.product.name : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.inventory?.batch_number || disposal.packing_list?.batch_number || 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.inventory?.location || 
                               (disposal.packing_list?.warehouse_id ? 'Warehouse ' + disposal.packing_list.warehouse_id : 'N/A') }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ disposal.expired_date ? new Date(disposal.expired_date).toLocaleDateString() : 
                               (disposal.inventory?.expiry_date ? new Date(disposal.inventory.expiry_date).toLocaleDateString() : 
                               (disposal.packing_list?.expire_date ? new Date(disposal.packing_list.expire_date).toLocaleDateString() : 'N/A')) }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ disposal.quantity }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="disposal.packing_list_id">
                                Packing List ({{ disposal.packing_list?.packing_list_number || 'PL' }})
                                <span v-if="disposal.status" class="text-sm font-medium ml-1 px-2 py-0.5 rounded" 
                                      :class="{
                                          'bg-red-100 text-red-800': disposal.status === 'damaged',
                                          'bg-yellow-100 text-yellow-800': disposal.status === 'expired',
                                          'bg-blue-100 text-blue-800': disposal.status === 'Missing',
                                          'bg-gray-100 text-gray-800': disposal.status === 'pending'
                                      }">
                                    {{ disposal.status.charAt(0).toUpperCase() + disposal.status.slice(1) }}
                                </span>
                            </div>
                            <div v-else-if="disposal.inventory_id">
                                Inventory
                                <span v-if="disposal.status === 'expired'" class="text-sm bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded ml-1">Expired</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ disposal.note || '' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <span 
                                :class="{
                                    'px-2 py-1 rounded text-white text-xs font-medium': true,
                                    'bg-red-500': disposal.status === 'damaged',
                                    'bg-yellow-500': disposal.status === 'expired',
                                    'bg-blue-500': disposal.status === 'Missing',
                                    'bg-gray-500': disposal.status === 'pending',
                                    'bg-green-500': disposal.approved_by !== null
                                }"
                            >
                                {{ disposal.approved_by !== null ? 'Approved' : 
                                   (disposal.status ? (disposal.status.charAt(0).toUpperCase() + disposal.status.slice(1)) : 'N/A') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="disposal.attachments">
                                <div v-for="(attachment, idx) in parseAttachments(disposal.attachments)" :key="idx" class="flex items-center mb-1">
                                    <a :href="`/storage/${attachment.path}`" target="_blank" class="text-blue-500 hover:underline flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        {{ attachment.name }}
                                    </a>
                                </div>
                            </div>
                            <span v-else>-</span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex space-x-2">
                                <button 
                                    v-if="disposal.approved_by === null"
                                    @click="approveDisposal(disposal.id)"
                                    :disabled="isLoading"
                                    class="text-green-500 hover:text-green-700 disabled:opacity-50"
                                    title="Approve"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button 
                                    v-if="disposal.approved_by === null"
                                    @click="rejectDisposal(disposal.id)"
                                    :disabled="isLoading"
                                    class="text-red-500 hover:text-red-700 disabled:opacity-50"
                                    title="Reject"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <button 
                                    v-if="disposal.approved_by !== null"
                                    @click="rollbackDisposal(disposal.id)"
                                    :disabled="isLoading"
                                    class="text-blue-500 hover:text-blue-700 disabled:opacity-50"
                                    title="Rollback"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </button>
                                <button class="text-blue-500 hover:text-blue-700 mr-2" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Dispose Modal -->
    <ActionModal
        :is-open="showDisposeModal"
        title="Dispose Item"
        action-type="dispose"
        :item="selectedItem"
        @close="showDisposeModal = false"
        @submit="handleDisposeSubmit"
    />
</Tab>
</template>