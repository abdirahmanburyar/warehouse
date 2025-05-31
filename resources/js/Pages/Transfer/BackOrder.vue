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
                                    <button @click="receiveBackorder(backorder)" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2">
                                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Receive
                                    </button>
                                    <button v-if="backorder.type === 'Missing'" @click="liquidateBackorder(backorder.id)" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Liquidate
                                    </button>
                                    <button v-if="backorder.type === 'Damaged'" @click="disposeBackorder(backorder.id)" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Dispose
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

// Function to liquidate backorder (for Missing type)
const liquidateBackorder = (id) => {
    Swal.fire({
        title: 'Liquidate Backorder',
        text: 'Are you sure you want to liquidate this backorder?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, liquidate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call API to liquidate backorder
            axios.post(route('transfers.liquidate', id))
                .then(response => {
                    Toast.fire({
                        icon: 'success',
                        title: 'Backorder has been sent for liquidation'
                    });
                    router.reload();
                })
                .catch(error => {
                    Toast.fire({
                        icon: 'error',
                        title: error.response?.data?.message || 'Failed to liquidate backorder'
                    });
                });
        }
    });
};

// Function to dispose backorder (for Damaged type)
const disposeBackorder = (id) => {
    Swal.fire({
        title: 'Dispose Backorder',
        text: 'Are you sure you want to dispose this damaged item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, dispose it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Call API to dispose backorder
            axios.post(route('transfers.dispose', id))
                .then(response => {
                    Toast.fire({
                        icon: 'success',
                        title: 'Backorder has been sent for disposal'
                    });
                    router.reload();
                })
                .catch(error => {
                    Toast.fire({
                        icon: 'error',
                        title: error.response?.data?.message || 'Failed to dispose backorder'
                    });
                });
        }
    });
};

// Function to receive backorder (for both types)
const receiveBackorder = (backorder) => {
    
    if (!backorder) {
        Toast.fire({
            icon: 'error',
            title: 'Backorder not found'
        });
        return;
    }
    
    // Show modal with quantity input
    Swal.fire({
        title: 'Receive Backorder',
        html: `
            <div class="text-left mb-4">
                <p class="mb-2"><strong>Product:</strong> ${backorder.product?.name || 'N/A'}</p>
                <p class="mb-2"><strong>Backorder Quantity:</strong> ${backorder.quantity}</p>
                <div class="mt-4">
                    <label for="received-quantity" class="block text-sm font-medium text-gray-700 mb-1">Received Quantity:</label>
                    <input type="number" id="received-quantity" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                        min="1" max="${backorder.quantity}" value="${backorder.quantity}"
                        oninput="if(this.value > ${backorder.quantity}) this.value = ${backorder.quantity};">
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Receive',
        preConfirm: () => {
            const receivedQuantity = document.getElementById('received-quantity').value;
            if (!receivedQuantity || receivedQuantity <= 0) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
            }
            if (receivedQuantity > backorder.quantity) {
                Swal.showValidationMessage(`Received quantity cannot exceed backorder quantity (${backorder.quantity})`);
                return false;
            }
            return receivedQuantity;
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const receivedQuantity = result.value;
            
            // Show loading indicator
            Swal.fire({
                title: 'Processing...',
                html: 'Receiving backorder items, please wait.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Call API to receive backorder with quantity
            try {
                const response = await axios.post(route('transfers.receiveBackOrder'), {
                    backorder: backorder,
                    quantity: receivedQuantity
                });
                
                // Close loading indicator
                Swal.close();
                
                Toast.fire({
                    icon: 'success',
                    title: response.data.message || 'Backorder has been received successfully'
                });
                
                // Wait a moment before redirecting to ensure toast is visible
                setTimeout(() => {
                    router.get(route('transfers.back-order'));
                }, 1000);
                
            } catch (error) {
                console.error('Error receiving backorder:', error);
                
                // Close loading indicator
                Swal.close();
                
                // Handle different error response formats
                let errorMessage = 'Failed to receive backorder';
                if (error.response?.data) {
                    if (typeof error.response.data === 'string') {
                        errorMessage = error.response.data;
                    } else if (error.response.data.error) {
                        errorMessage = error.response.data.error;
                    } else if (error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                }
                
                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });
            }
        }
    });
};
</script>
