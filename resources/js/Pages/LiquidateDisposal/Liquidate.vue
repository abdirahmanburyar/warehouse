<script setup lang="ts">
import Tab from './Tab.vue';
import ActionModal from '@/Components/ActionModal.vue';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import moment from 'moment';
import Swal from 'sweetalert2';
import axios from 'axios';
import { TailwindPagination } from 'laravel-vue-pagination';

const isLoading = ref(false);
const toast = useToast();
const showLiquidateModal = ref(false);
const selectedItem = ref(null);

const props = defineProps({
    liquidates: Object,
    filters: Object,
});

// Method to open the liquidate modal
const openLiquidateModal = (item) => {
    selectedItem.value = item;
    showLiquidateModal.value = true;
};

const isReviewing = ref(false);
const reviewLiquidation = (id) => {
    if (!id) return;    
    isReviewing.value = true;
    Swal.fire({
        title: 'Review Liquidation?',
        text: 'Are you sure you want to review this liquidation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, review it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isReviewing.value = true;
            await axios.get(route('liquidate-disposal.liquidates.review', id))
                .then((response) => {
                    isReviewing.value = false;
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
                isReviewing.value = false;
                console.error('Error reviewing liquidation:', error);
                toast.error('An error occurred while reviewing the liquidation');
            });
        } else {
            isReviewing.value = false;
        }
    });
};

const isApproving = ref(false);
const approveLiquidation = async (id) => {
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
            await axios.get(route('liquidate-disposal.liquidates.approve', id))
                .then((response) => {
                    isApproving.value = false;
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
const rejectLiquidation = async (id) => {
    if (!id) return;
    
    try {
        const result = await Swal.fire({
            title: 'Reject Liquidation',
            icon: 'warning',
            html: '<div class="mb-3 flex flex-col"><label class="form-label">Reason for rejection</label><textarea id="rejection-reason" class="form-control" rows="3" placeholder="Enter your reason here..."></textarea></div>',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
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
            isRejecting.value = true;
            await axios.get(route('liquidate-disposal.liquidates.reject', id), {
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
        isRejecting.value = false;
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
const parseAttachments = (attachments) => {
    if (!attachments) return [];
    const files = typeof attachments === 'string' ? JSON.parse(attachments) : attachments;
    return files.map(file => ({
        name: file.name || file.path.split('/').pop(),
        url: `${file.path}`
    }));
};

const activeDropdown = ref(null);

const toggleDropdown = (id) => {
    activeDropdown.value = activeDropdown.value === id ? null : id;
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

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const search = ref(props.filters?.search || "");
const per_page = ref(props.filters?.per_page || 2);

watch([
    () => search.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    reloadLiquidates();
});

const reloadLiquidates = () => {
    const query = {};
    if (search.value) {
        query.search = search.value;
    }
    if (per_page.value) {
        query.per_page = per_page.value;
        props.filters.page = 1;
    }
    if (props.filters.page) {
        query.page = props.filters.page;
    }
    router.get(route('liquidate-disposal.liquidates'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['liquidates']
    });
};

function getResults(page = 1) {
    props.filters.page = page;
    reloadLiquidates();
}

</script>

<template>
    <Tab title="Liquidate" activeTab="liquidate">
        <div class="mb-6 flex flex-wrap gap-4 items-center">
            <h2 class="text-xl font-semibold">Liquidation Records</h2>
        </div>
        <div class="mb-6 flex justify-between items-center">
            <input type="text" v-model="search" placeholder="Search by [Disposal ID, Item Name, Item Barcode, Item Batch Number]..." class="w-[600px] form-control">
            <select v-model="per_page" class="w-[200px] form-select">
                <option value="2"> Per Page 2</option>
                <option value="5"> Per Page 5</option>
                <option value="10"> Per Page 10</option>
                <option value="25"> Per Page 25</option>
                <option value="50"> Per Page 50</option>
                <option value="100"> Per Page 100</option>
            </select>
        </div>
        <!-- Table Section -->
         {{props.liquidates}}
        <div class="mb-6">
            <table class="min-w-full border border-collapse border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">SN</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Liquidation ID</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Item</th>
                        <th class="w-[300px] px-4 py-2 border-r border-gray-300 text-left text-black">Item Info</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Liquidated At</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Source and Reason</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Attachments</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Status</th>
                        <th class="px-4 py-2 border-r border-gray-300 text-left text-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="props.liquidates.data.length === 0">
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No liquidation records found</td>
                    </tr>
                    <tr v-for="(liquidate, index) in props.liquidates.data" :key="liquidate.id" class="border-b border-gray-300">
                        <td class="px-4 py-2 border-r border-gray-300">{{ index + 1 }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">{{ liquidate.liquidate_id }}</td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.product ? liquidate.product.name : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300 flex flex-col">
                            <span>Batch Number: {{ liquidate.batch_number || 'N/A' }}</span>
                            <span>Barcode: {{ liquidate.barcode || 'N/A' }}</span>
                            <span>Expiry Date: {{ liquidate.expire_date ? moment(liquidate.expire_date).format('DD/MM/YYYY') : 'N/A' }}</span>
                            <span>Warehouse: {{ liquidate.packing_list?.warehouse?.name || 'N/A' }}</span>
                            <span>Location: {{ liquidate.packing_list?.location?.location || 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.liquidated_at ? new Date(liquidate.liquidated_at).toLocaleDateString() : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            {{ liquidate.note || 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div v-if="parseAttachments(liquidate.attachments).length > 0" class="relative attachments-dropdown">
                                <button 
                                    @click="toggleDropdown(liquidate.id)"
                                    class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded flex items-center gap-1 text-sm"
                                >
                                    <span>View Files ({{ parseAttachments(liquidate.attachments).length }})</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div 
                                    v-show="activeDropdown === liquidate.id"
                                    class="absolute z-10 mt-1 bg-white rounded-md shadow-lg border border-gray-200 py-1 w-48"
                                >
                                    <a 
                                        v-for="attachment in parseAttachments(liquidate.attachments)" 
                                        :key="attachment.name"
                                        :href="attachment.url"
                                        target="_blank"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                        @click="activeDropdown = null"
                                    >
                                        {{ attachment.name }}
                                    </a>
                                </div>
                            </div>
                            <span v-else class="text-gray-500 text-sm">No attachments</span>
                        </td>
                        <td class="px-4 py-2 border-r border-gray-300">
                            <div class="flex flex-col gap-1">
                                <!-- Always show Pending -->
                                <span class="text-gray-600 text-sm">Pending</span>
                                
                                <!-- Show Reviewed if reviewed -->
                                <template v-if="liquidate.reviewed_at">
                                    <span class="flex items-center text-sm">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-blue-600">Reviewed</span>
                                    </span>
                                </template>
                                
                                <!-- Show Approved/Rejected if either one is present -->
                                <template v-if="liquidate.approved_at">
                                    <span class="flex items-center text-sm">
                                        <svg class="w-3 h-3 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-green-600">Approved</span>
                                    </span>
                                </template>
                                <template v-if="liquidate.rejected_at">
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
                            <div v-if="liquidate.approved_at" class="text-gray-600 text-sm">
                                Closed (Approved)
                            </div>
                            
                            <div v-else class="flex flex-col gap-2">
                                <button 
                                    v-if="!liquidate.reviewed_at" 
                                    @click="reviewLiquidation(liquidate.id)" 
                                    :disabled="isReviewing"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm">
                                    {{isReviewing ? 'Processing...' : 'Review'}}
                                </button>
                                <!-- Show approve/reject buttons after review -->
                                <template v-if="liquidate.reviewed_at && !liquidate.approved_at">
                                    <div class="flex flex-col gap-2">
                                        <button 
                                            @click="approveLiquidation(liquidate.id)" 
                                            :disabled="isApproving"
                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm">
                                            {{isApproving ? 'Processing...' : liquidate.rejected_at ? 'Approve (After Revision)' : 'Approve'}}
                                        </button>
                                        <button 
                                            v-if="!liquidate.rejected_at"
                                            @click="rejectLiquidation(liquidate.id)"
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
        <div class="flex justify-end items-center mt-3">
            <TailwindPagination :data="props.liquidates" 
            @pagination-change-page="getResults"
        />
        </div>
    </Tab>
</template>