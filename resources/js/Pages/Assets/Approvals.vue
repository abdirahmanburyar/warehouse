<template>
    <AuthenticatedLayout
        title="Asset Approvals"
        description="Manage asset approval requests"
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
                                    <div class="text-purple-100 text-sm">Pending</div>
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
                                placeholder="Search by asset tag, serial number..."
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
                        <div v-for="approval in props.approvals.data" :key="approval.id" 
                             class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4 mb-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-indigo-600">
                                                    <path d="M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ approval.approvable?.asset_tag || 'Unknown Asset' }}
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ approval.approvable?.serial_number || 'N/A' }} • {{ approval.approvable?.item_description || 'No description' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="{
                                                      'bg-yellow-100 text-yellow-800': approval.status === 'pending',
                                                      'bg-blue-100 text-blue-800': approval.status === 'reviewed',
                                                      'bg-green-100 text-green-800': approval.status === 'approved',
                                                      'bg-red-100 text-red-800': approval.status === 'rejected'
                                                  }">
                                                {{ approval.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                                            <dd class="text-sm text-gray-900">{{ approval.role?.name || 'N/A' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                                            <dd class="text-sm text-gray-900">{{ approval.action }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Sequence</dt>
                                            <dd class="text-sm text-gray-900">{{ approval.sequence }}</dd>
                                        </div>
                                    </div>

                                    <div v-if="approval.notes" class="mb-4">
                                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                        <dd class="text-sm text-gray-900">{{ approval.notes }}</dd>
                                    </div>

                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <div>
                                            Created by {{ approval.creator?.name || 'Unknown' }} on {{ formatDate(approval.created_at) }}
                                        </div>
                                        <div v-if="approval.reviewed_at">
                                            Reviewed on {{ formatDate(approval.reviewed_at) }}
                                            <span v-if="approval.reviewer">by {{ approval.reviewer?.name || 'Unknown' }}</span>
                                        </div>
                                        <div v-if="approval.approved_at">
                                            {{ approval.status === 'approved' ? 'Approved' : 'Rejected' }} on {{ formatDate(approval.approved_at) }}
                                            <span v-if="approval.approver">by {{ approval.approver?.name || 'Unknown' }}</span>
                                        </div>
                                    </div>
                                    

                                </div>

                                <!-- Action Buttons -->
                                <div v-if="canReview(approval)" class="ml-6 flex flex-col space-y-2">
                                    <button
                                        @click="showApprovalModal(approval, 'review')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Review
                                    </button>
                                </div>
                                
                                <!-- Approve/Reject Buttons (only show after review) -->
                                <div v-if="canApprove(approval) || canReject(approval)" class="ml-6 flex flex-col space-y-2">
                                    <button
                                        v-if="canApprove(approval)"
                                        @click="showApprovalModal(approval, 'approve')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Approve
                                    </button>
                                    <button
                                        v-if="canReject(approval)"
                                        @click="showApprovalModal(approval, 'reject')"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
                                            <path d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="props.approvals.data && props.approvals.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-end">
                        <TailwindPagination
                            :data="props.approvals"
                            @pagination-change-page="getResults"
                            :limit="2"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ modalAction === 'review' ? 'Review' : (modalAction === 'approve' ? 'Approve' : 'Reject') }} Asset
                    </h3>
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">
                            {{ selectedApproval?.approvable?.asset_tag || 'Unknown Asset' }} - {{ selectedApproval?.approvable?.serial_number || 'N/A' }}
                        </p>
                        <label for="approval-notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea
                            id="approval-notes"
                            v-model="approvalNotes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Add any notes about your decision..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="processApproval"
                            :disabled="processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50"
                            :class="modalAction === 'review' ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500' : (modalAction === 'approve' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500')"
                        >
                            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ modalAction === 'review' ? 'Review' : (modalAction === 'approve' ? 'Approve' : 'Reject') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';
import TailwindPagination from '@/Components/Pagination.vue';

const props = defineProps({
    approvals: Object,
    filters: Object,
    pendingCount: Number,
    approvedCount: Number,
});

const page = usePage();

const loading = ref(false);
const showModal = ref(false);
const selectedApproval = ref(null);
const modalAction = ref('');
const approvalNotes = ref('');
const processing = ref(false);

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
});

// Watch for filter changes and reload data
watch(filters, async (newFilters) => {
    await loadApprovals();
}, { deep: true });

async function loadApprovals() {
    loading.value = true;
    try {
        const response = await axios.get(route('assets.pending-approvals'), {
            params: filters.value
        });
        // Update the approvals data
        // In a real app, you'd update the reactive data here
    } catch (error) {
        console.error('Error loading approvals:', error);
    } finally {
        loading.value = false;
    }
}

function canReview(approval) {
    // Can review only if status is pending and user has asset_review permission
    return approval.status === 'pending' && page.props.auth.can.asset_review;
}

function canApprove(approval) {
    // Can approve only if status is reviewed and user has asset_approve permission
    return approval.status === 'reviewed' && page.props.auth.can.asset_approve;
}

function canReject(approval) {
    // Can reject only if status is reviewed and user has asset_reject permission
    return approval.status === 'reviewed' && page.props.auth.can.asset_reject;
}

function showApprovalModal(approval, action) {
    selectedApproval.value = approval;
    modalAction.value = action;
    approvalNotes.value = '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    selectedApproval.value = null;
    modalAction.value = '';
    approvalNotes.value = '';
}

async function processApproval() {
    if (!selectedApproval.value) return;

    processing.value = true;
    try {
        const response = await axios.post(route('assets.approve', selectedApproval.value.approvable.id), {
            action: modalAction.value,
            notes: approvalNotes.value
        });

        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: response.data.message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            closeModal();
            router.reload();
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data || 'Failed to process approval',
            showConfirmButton: false,
            timer: 1500
        });
    } finally {
        processing.value = false;
    }
}

function formatDate(date) {
    if (!date) return '-';
    return moment(date).format('DD/MM/YYYY HH:mm');
}

function getResults(page) {
    router.get(route('assets.approvals.index'), {
        ...filters.value,
        page: page
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

onMounted(() => {
    // Initialize any necessary data
});
</script> 