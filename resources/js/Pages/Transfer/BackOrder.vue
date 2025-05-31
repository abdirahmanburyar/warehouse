<template>
    <AuthenticatedLayout title="Transfer Back Orders" description="Manage transfer back orders"
        img="/assets/images/transfer.png">
        <div class="mb-[80px]">
            <!-- Header Section -->
            <div class="flex flex-col mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">Transfer Back Orders</h2>
                </div>
            </div>

            <div class="mb-[100px]">
                <div v-if="backorders.length === 0" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No back orders found</h3>
                    <p class="mt-1 text-sm text-gray-500">There are currently no transfer back orders in the system.</p>
                </div>
                    
                <div v-else>
                    <table class="min-w-full border border-gray-300">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[8%]">
                                    Transfer ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-1/4">
                                    Item
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">
                                    Warehouse
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">
                                    Item Information
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">
                                    UOM
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[6%]">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[8%]">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[8%]">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="backorder in props.backorders" :key="backorder.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black border border-black">
                                    <Link :href="route('transfers.show', backorder.transfer_item?.transfer?.id)">
                                    {{ backorder.transfer_item?.transfer?.transferID }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                    {{ backorder.product?.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                    {{ backorder.transfer_item?.transfer?.to_warehouse?.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-black border border-black">
                                    <div class="flex flex-col space-y-1">
                                        <div><span class="font-semibold">Batch:</span> {{ backorder.transfer_item?.batch_number }}</div>
                                        <div><span class="font-semibold">Expiry:</span> {{ backorder.transfer_item?.expire_date ? moment(backorder.transfer_item.expire_date).format('DD/MM/YYYY') : 'N/A' }}</div>
                                        <div><span class="font-semibold">Barcode:</span> {{ backorder.transfer_item?.barcode || '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                    {{ backorder.transfer_item?.uom || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                    {{ backorder.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                    {{ backorder.type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap border border-black">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': backorder.status === 'pending',
                                            'bg-green-100 text-green-800': backorder.status === 'approved',
                                            'bg-red-100 text-red-800': backorder.status === 'rejected'
                                        }">
                                        {{ backorder.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border border-black">
                                    <button @click="viewBackorder(backorder.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                        View
                                    </button>
                                    <button @click="approveBackorder(backorder.id)" v-if="backorder.status === 'pending'" class="text-green-600 hover:text-green-900 mr-2">
                                        Approve
                                    </button>
                                    <button @click="rejectBackorder(backorder.id)" v-if="backorder.status === 'pending'" class="text-red-600 hover:text-red-900">
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';

// Define props to receive data from the controller
const props = defineProps({
    backorders: {
        type: Array,
        default: () => []
    }
});

// Create a computed property for backorders to handle possible undefined values
const backorders = computed(() => {
    return props.backorders || [];
});

// Toast notification configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Function to view backorder details
const viewBackorder = (id) => {
    // Find the backorder in the array
    const backorder = backorders.value.find(b => b.id === id);
    
    if (!backorder) {
        Toast.fire({
            icon: 'error',
            title: 'Backorder not found'
        });
        return;
    }
    
    // Show backorder details in a modal
    Swal.fire({
        title: 'Backorder Details',
        html: `
            <div class="text-left">
                <p><strong>ID:</strong> ${backorder.id}</p>
                <p><strong>Product:</strong> ${backorder.product?.name || 'N/A'}</p>
                <p><strong>Quantity:</strong> ${backorder.quantity}</p>
                <p><strong>Type:</strong> ${backorder.type}</p>
                <p><strong>Status:</strong> ${backorder.status}</p>
                <p><strong>Notes:</strong> ${backorder.notes || 'No notes'}</p>
                <p><strong>Created At:</strong> ${new Date(backorder.created_at).toLocaleString()}</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
    });
};

// Function to approve backorder
const approveBackorder = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to approve this backorder',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call API to approve backorder
            axios.post(route('transfers.backorder.approve', id))
                .then(response => {
                    Toast.fire({
                        icon: 'success',
                        title: 'Backorder approved successfully'
                    });
                    router.reload();
                })
                .catch(error => {
                    Toast.fire({
                        icon: 'error',
                        title: error.response?.data?.message || 'Failed to approve backorder'
                    });
                });
        }
    });
};

// Function to reject backorder
const rejectBackorder = (id) => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to reject this backorder',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reject it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call API to reject backorder
            axios.post(route('transfers.backorder.reject', id))
                .then(response => {
                    Toast.fire({
                        icon: 'success',
                        title: 'Backorder rejected successfully'
                    });
                    router.reload();
                })
                .catch(error => {
                    Toast.fire({
                        icon: 'error',
                        title: error.response?.data?.message || 'Failed to reject backorder'
                    });
                });
        }
    });
};
</script>
