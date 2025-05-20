<template>
    <AuthenticatedLayout title="Optimize Your Transfers" description="Moving Supplies, Bridging needs"
        img="/assets/images/transfer.png">
        <div class="p-6">
            <!-- Bulk Actions Panel -->
            <div v-if="selectedTransfers.length > 0"
                class="bg-gray-50 p-4 mb-6 rounded-lg flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">{{ selectedTransfers.length }} items selected</span>
                    <button @click="bulkApprove"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Approve Selected
                    </button>
                    <button @click="bulkReject"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Reject Selected
                    </button>
                </div>
                <button @click="clearSelection" class="text-sm text-gray-600 hover:text-gray-900">
                    Clear Selection
                </button>
            </div>

            <!-- Header Section -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text"
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md w-64 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search a Transfer">
                    </div>

                    <!-- Facility Selector -->
                    <div class="relative">
                        <select
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-md w-48 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                            <option>Select Facility</option>
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-4">

                    <!-- New Transfer -->
                    <button
                        @click="router.visit(route('transfers.create'))"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        New Transfer
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a v-for="tab in tabs" :key="tab.name" :href="tab.href" :class="[
                        currentTab === tab.name
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer'
                    ]" @click.prevent="currentTab = tab.name">
                        {{ tab.label }}
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Table Section (10 cols) -->
                <div class="col-span-9">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="w-8 px-6 py-3">
                                        <div class="flex items-center">
                                            <input type="checkbox" v-model="isAllSelected"
                                                @change="toggleAllTransfers"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </div>
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transfer
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transfer Date
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transferred From
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transferred To
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Number of Items
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transfer in filteredTransfers" :key="transfer.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" :value="transfer.id" v-model="selectedTransfers"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ transfer.transferID }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(transfer.transfer_date).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transfer.from_warehouse?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transfer.to_warehouse?.name || transfer.to_facility?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transfer.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <!-- Status Progress Icons -->
                                            <div class="flex items-center gap-1">
                                                <img src="/assets/images/pending.svg" class="w-12 h-12" alt="pending"
                                                    :class="{'opacity-50': !['pending', 'in_process', 'dispatched', 'delivered'].includes(transfer.status)}" />
                                                
                                                <img src="/assets/images/approved.png" class="w-12 h-12" alt="Approved"
                                                    :class="{'opacity-50': !['approved', 'in_process', 'dispatched', 'delivered'].includes(transfer.status)}" />
                                                <img src="/assets/images/inprocess.png" class="w-12 h-12" alt="In Process"
                                                    :class="{'opacity-50': !['in_process', 'dispatched', 'delivered'].includes(transfer.status)}" />
                                                <img src="/assets/images/dispatch.png" class="w-12 h-12" alt="Dispatched"
                                                    :class="{'opacity-50': !['dispatched', 'delivered'].includes(transfer.status)}" />
                                                <img src="/assets/images/delivery.png" class="w-12 h-12" alt="Delivered"
                                                    :class="{'opacity-50': transfer.status !== 'delivered'}" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex space-x-2">
                                            <!-- Approve Button -->
                                            <button v-if="transfer.status === 'pending'"
                                                @click="approveTransfer(transfer.id)"
                                                class="text-green-600 hover:text-green-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <!-- Reject Button -->
                                            <button v-if="transfer.status === 'pending'"
                                                @click="rejectTransfer(transfer.id)"
                                                class="text-red-600 hover:text-red-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <!-- Mark In Process Button -->
                                            <button v-if="transfer.status === 'approved'"
                                                @click="markInProcess(transfer.id)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <!-- Complete Transfer Button -->
                                            <button v-if="transfer.status === 'in_process'"
                                                @click="completeTransfer(transfer.id)"
                                                class="text-purple-600 hover:text-purple-900">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Statistics Section (9cols) -->
                <div class="col-span-2">
                    <div class="flex justify-between gap-5">
                        <!-- Approved -->
                        <div class="flex flex-col items-center">
                            <div class="h-[400px] w-12 bg-blue-100 rounded-2xl relative overflow-hidden">
                                <div
                                    class="absolute top-3 left-1/2 transform -translate-x-1/2 bg-blue-50 p-2 rounded-lg z-10">
                                    <img src="/assets/images/approved_small.png" alt="Approved"
                                        class="h-full w-full object-cover" />
                                </div>
                                <div class="absolute bottom-0 w-full bg-blue-500 transition-all duration-500"
                                    :style="{ height: props.statistics.approved.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-medium text-sm">
                                        {{ props.statistics.approved.percentage }}%
                                        <div class="text-xs opacity-75">{{ props.statistics.approved.stages.join(' → ')
                                        }}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Approved</span>
                        </div>

                        <!-- Pending Approval -->
                        <div class="flex flex-col items-center">
                            <div class="h-[400px] w-12 bg-orange-100 rounded-2xl relative overflow-hidden">
                                <div
                                    class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-orange-50 p-2 rounded-lg z-10">
                                    <img src="/assets/images/pending_small.png" class="h-6 w-6" alt="Pending" />
                                </div>
                                <div class="absolute bottom-0 w-full bg-orange-500 transition-all duration-500"
                                    :style="{ height: props.statistics.pending.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-medium text-sm">
                                        {{ props.statistics.pending.percentage }}%
                                        <div class="text-xs opacity-75">{{ props.statistics.pending.stages.join(' → ')
                                        }}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Pending<br>Approval</span>
                        </div>

                        <!-- In Process -->
                        <div class="flex flex-col items-center">
                            <div class="h-[400px] w-12 bg-gray-100 rounded-2xl relative overflow-hidden">
                                <div
                                    class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gray-50 p-2 rounded-lg z-10">
                                    <img src="/assets/images/inprocess.png" class="h-6 w-6" alt="In Process" />
                                </div>
                                <div class="absolute bottom-0 w-full bg-gray-500 transition-all duration-500"
                                    :style="{ height: props.statistics.in_process.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-medium text-sm">
                                        {{ props.statistics.in_process.percentage }}%
                                        <div class="text-xs opacity-75">{{ props.statistics.in_process.stages.join(' → ') }}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">In Process</span>
                        </div>

                        <!-- Transferred -->
                        <div class="flex flex-col items-center">
                            <div class="h-[400px] w-12 bg-green-100 rounded-2xl relative overflow-hidden">
                                <div
                                    class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-green-50 p-2 rounded-lg z-10">
                                    <img src="/assets/images/approved_small.png" class="h-6 w-6" alt="Transferred" />
                                </div>
                                <div class="absolute bottom-0 w-full bg-green-500 transition-all duration-500"
                                    :style="{ height: props.statistics.transferred.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-medium text-sm">
                                        {{ props.statistics.transferred.percentage }}%
                                        <div class="text-xs opacity-75">{{ props.statistics.transferred.stages.join(' → ') }}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Transferred</span>
                        </div>

                        <!-- Rejected -->
                        <div class="flex flex-col items-center">
                            <div class="h-[400px] w-12 bg-red-100 rounded-2xl relative overflow-hidden">
                                <div
                                    class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-red-50 p-2 rounded-lg z-10">
                                    <img src="/assets/images/pending_small.png" class="h-6 w-6" alt="Rejected" />
                                </div>
                                <div class="absolute bottom-0 w-full bg-red-500 transition-all duration-500"
                                    :style="{ height: props.statistics.rejected.percentage + '%' }">
                                    <div
                                        class="absolute bottom-0 left-0 right-0 text-center py-1 text-white font-medium text-sm">
                                        {{ props.statistics.rejected.percentage }}%
                                        <div class="text-xs opacity-75">{{ props.statistics.rejected.stages.join(' → ')
                                        }}</div>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2 text-xs font-medium text-gray-900">Rejected<br>Transfers</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    transfers: {
        type: Array,
        default: () => []
    },
    statistics: {
        type: Object,
        default: () => ({
            approved: { count: 0, percentage: 0 },
            pending: { count: 0, percentage: 0 },
            in_process: { count: 0, percentage: 0 },
            transferred: { count: 0, percentage: 0 },
            rejected: { count: 0, percentage: 0 }
        })
    }
});

const currentTab = ref('all');
const selectedTransfers = ref([]);
const isAllSelected = computed(() => selectedTransfers.value.length === filteredTransfers.value.length && filteredTransfers.value.length > 0);

const tabs = [
    { name: 'all', label: 'All Transfers', href: '#' },
    { name: 'pending', label: 'Pending Approval', href: '#' },
    { name: 'approved', label: 'Approved', href: '#' },
    { name: 'in_process', label: 'In Process', href: '#' },
    { name: 'transferred', label: 'Transferred', href: '#' },
    { name: 'rejected', label: 'Rejected Transfers', href: '#' },
];

const filteredTransfers = computed(() => {
    if (currentTab.value === 'all') {
        return props.transfers;
    }
    return props.transfers.filter(transfer => transfer.status === currentTab.value);
});

const getTabCount = (tabName) => {
    if (tabName === 'all') {
        return props.transfers.length;
    }
    return props.transfers.filter(transfer => transfer.status === tabName).length;
};

const getStatusPercentage = (status) => {
    if (!props.transfers.length) return 0;
    const count = props.transfers.filter(transfer => transfer.status === status).length;
    return Math.round((count / props.transfers.length) * 100);
};

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

const approveTransfer = async (id) => {
    try {
        const result = await Swal.fire({
            title: 'Approve Transfer',
            text: 'Are you sure you want to approve this transfer?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve it!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.approve', id));
            Toast.fire({
                icon: 'success',
                title: response.data.message
            });
            router.reload();
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error approving transfer'
        });
    }
};

const rejectTransfer = async (id) => {
    try {
        const result = await Swal.fire({
            title: 'Reject Transfer',
            text: 'Are you sure you want to reject this transfer?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject it!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.reject', id));
            Toast.fire({
                icon: 'success',
                title: response.data.message
            });
            router.reload();
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error rejecting transfer'
        });
    }
};

const markInProcess = async (id) => {
    try {
        const result = await Swal.fire({
            title: 'Start Processing',
            text: 'Are you sure you want to start processing this transfer?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, start processing!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.inProcess', id));
            Toast.fire({
                icon: 'success',
                title: response.data.message
            });
            router.reload();
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error updating transfer status'
        });
    }
};

const completeTransfer = async (id) => {
    try {
        const result = await Swal.fire({
            title: 'Complete Transfer',
            text: 'Are you sure you want to complete this transfer? This will update the inventory at the destination.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, complete it!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.completeTransfer', id));
            Swal.fire({
                icon: 'success',
                title: response.data.message
            });
            router.reload();
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error completing transfer'
        });
    }
};

const toggleAllTransfers = () => {
    if (isAllSelected.value) {
        selectedTransfers.value = [];
    } else {
        selectedTransfers.value = filteredTransfers.value.map(t => t.id);
    }
};

const clearSelection = () => {
    selectedTransfers.value = [];
};

const bulkApprove = async () => {
    try {
        const result = await Swal.fire({
            title: 'Bulk Approve Transfers',
            text: `Are you sure you want to approve ${selectedTransfers.value.length} transfers?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, approve all!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.bulkApprove'), {
                transferIds: selectedTransfers.value
            });
            Toast.fire({
                icon: 'success',
                title: response.data.message
            });
            selectedTransfers.value = [];
            router.reload();
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error bulk approving transfers'
        });
    }
};

const bulkReject = async () => {
    try {
        const result = await Swal.fire({
            title: 'Bulk Reject Transfers',
            text: `Are you sure you want to reject ${selectedTransfers.value.length} transfers?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, reject all!'
        });

        if (result.isConfirmed) {
            const response = await axios.post(route('transfers.bulkReject'), {
                transferIds: selectedTransfers.value
            });
            Toast.fire({
                icon: 'success',
                title: response.data.message
            });
            selectedTransfers.value = [];
            router.reload();
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Error bulk rejecting transfers'
        });
    }
};
</script>
