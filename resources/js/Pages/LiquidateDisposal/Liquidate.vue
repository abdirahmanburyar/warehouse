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

interface Liquidate {
    id: number;
    product_id: number;
    purchase_order_id: number | null;
    packing_list_id: number | null;
    inventory_id: number | null;
    liquidated_by: User;
    liquidated_at: string;
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
const showLiquidateModal = ref(false);
const selectedItem = ref(null);

const props = defineProps<{
    liquidates: Liquidate[];
}>();

// Method to open the liquidate modal
const openLiquidateModal = (item) => {
    selectedItem.value = item;
    showLiquidateModal.value = true;
};

// Method to handle liquidate form submission
const handleLiquidateSubmit = async (formData) => {
    isLoading.value = true;
    try {
        const response = await fetch('/api/liquidate', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (response.ok) {
            toast.success('Item liquidated successfully');
            showLiquidateModal.value = false;
            // Refresh the page to show updated data
            router.reload();
        } else {
            toast.error(result.message || 'Failed to liquidate item');
        }
    } catch (error) {
        console.error('Error liquidating item:', error);
        toast.error('An error occurred while liquidating the item');
    } finally {
        isLoading.value = false;
    }
};

// Method to approve a liquidation
const approveLiquidate = async (id: number) => {
    if (confirm('Are you sure you want to approve this liquidation?')) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/liquidates/${id}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Liquidation approved successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to approve liquidation');
            }
        } catch (error) {
            console.error('Error approving liquidation:', error);
            toast.error('An error occurred while approving the liquidation');
        } finally {
            isLoading.value = false;
        }
    }
};

// Method to reject a liquidation
const rejectLiquidate = async (id: number) => {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/liquidates/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ reason })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Liquidation rejected successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to reject liquidation');
            }
        } catch (error) {
            console.error('Error rejecting liquidation:', error);
            toast.error('An error occurred while rejecting the liquidation');
        } finally {
            isLoading.value = false;
        }
    }
};

// Method to rollback an approved liquidation
const rollbackLiquidate = async (id: number) => {
    if (confirm('Are you sure you want to rollback this liquidation? This will revert it to pending status.')) {
        isLoading.value = true;
        try {
            const response = await fetch(`/api/liquidates/${id}/rollback`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            const result = await response.json();
            
            if (response.ok) {
                toast.success('Liquidation rolled back successfully');
                // Refresh the page to show updated data
                router.reload();
            } else {
                toast.error(result.message || 'Failed to rollback liquidation');
            }
        } catch (error) {
            console.error('Error rolling back liquidation:', error);
            toast.error('An error occurred while rolling back the liquidation');
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
    <Tab title="Liquidate" activeTab="liquidate">
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <h2 class="text-xl font-semibold">Liquidation Records</h2>
        </div>

        <!-- Table Section -->
        <div class="overflow-auto">
            <table class="min-w-full border border-collapse border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">SN</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Liquidation ID</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Item</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Batch No/Barcode</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Warehouse/Location</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Expiry Date</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Liquidate QTY</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Source and Reason</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Status</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Attachments</th>
                        <th class="px-4 py-2 text-left text-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="props.liquidates.length === 0">
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">No liquidation records found</td>
                    </tr>
                    <tr v-for="(liquidate, index) in props.liquidates" :key="liquidate.id" class="border-b border-gray-300">
                        <td class="px-4 py-2 border-r border-gray-300">{{ index + 1 }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ liquidate.id }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.product ? liquidate.product.name : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.inventory?.batch_number || liquidate.packing_list?.batch_number || 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.inventory?.location || 
                               (liquidate.packing_list?.warehouse_id ? 'Warehouse ' + liquidate.packing_list.warehouse_id : 'N/A') }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.inventory?.expiry_date ? new Date(liquidate.inventory.expiry_date).toLocaleDateString() : 
                               (liquidate.packing_list?.expire_date ? new Date(liquidate.packing_list.expire_date).toLocaleDateString() : 'N/A') }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ liquidate.quantity }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="liquidate.packing_list_id">
                                Packing List ({{ liquidate.packing_list?.packing_list_number || 'PL' }})
                                <span v-if="liquidate.status" class="text-sm font-medium ml-1 px-2 py-0.5 rounded" 
                                      :class="{
                                          'bg-red-100 text-red-800': liquidate.status === 'damaged',
                                          'bg-yellow-100 text-yellow-800': liquidate.status === 'expired',
                                          'bg-blue-100 text-blue-800': liquidate.status === 'Missing',
                                          'bg-gray-100 text-gray-800': liquidate.status === 'pending'
                                      }">
                                    {{ liquidate.status.charAt(0).toUpperCase() + liquidate.status.slice(1) }}
                                </span>
                            </div>
                            <div v-else-if="liquidate.inventory_id">
                                Inventory
                                <span v-if="liquidate.status === 'expired'" class="text-sm bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded ml-1">Expired</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ liquidate.note || '' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <span 
                                :class="{
                                    'px-2 py-1 rounded text-white text-xs font-medium': true,
                                    'bg-red-500': liquidate.status === 'damaged',
                                    'bg-yellow-500': liquidate.status === 'expired',
                                    'bg-blue-500': liquidate.status === 'Missing',
                                    'bg-gray-500': liquidate.status === 'pending',
                                    'bg-green-500': liquidate.approved_by !== null
                                }"
                            >
                                {{ liquidate.approved_by !== null ? 'Approved' : 
                                   (liquidate.status ? (liquidate.status.charAt(0).toUpperCase() + liquidate.status.slice(1)) : 'N/A') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="liquidate.attachments">
                                <div v-for="(attachment, idx) in parseAttachments(liquidate.attachments)" :key="idx" class="flex items-center mb-1">
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
                                <button class="text-blue-500 hover:text-blue-700" title="View Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <!-- Show Approve button for pending records -->
                                <button 
                                    v-if="liquidate.status === 'pending' && !liquidate.approved_by" 
                                    class="text-green-500 hover:text-green-700" 
                                    title="Approve"
                                    @click="approveLiquidate(liquidate.id)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <!-- Show Reject button for pending records -->
                                <button 
                                    v-if="liquidate.status === 'pending'" 
                                    class="text-red-500 hover:text-red-700" 
                                    title="Reject"
                                    @click="rejectLiquidate(liquidate.id)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <!-- Show Rollback button for approved records -->
                                <button 
                                    v-if="liquidate.approved_by" 
                                    class="text-yellow-500 hover:text-yellow-700" 
                                    title="Rollback"
                                    @click="rollbackLiquidate(liquidate.id)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Liquidate Modal -->
    <ActionModal
        :is-open="showLiquidateModal"
        title="Liquidate Item"
        action-type="liquidate"
        :item="selectedItem"
        @close="showLiquidateModal = false"
        @submit="handleLiquidateSubmit"
    />
</Tab>
</template>