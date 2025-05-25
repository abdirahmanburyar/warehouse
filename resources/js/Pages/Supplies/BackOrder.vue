<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Back Orderse" description="Manage your Back orders">
        <!-- Supplier Selection -->
        <div class="">
            <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Back to Suppliers
            </Link>
            

            <div class="grid grid-cols-1 gap-6">
                <div class="w-[400px] mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select P.O
                    </label>
                    <Multiselect v-model="purchase_order"
                        :options="props.po"
                        :searchable="true" :close-on-select="true" :show-labels="false"
                        :allow-empty="true" placeholder="Select P.O Number" track-by="id" label="po_number"
                    @select="onPOChange"></Multiselect>
                </div>
            </div>
        </div>

        <!-- Back Order Stats -->        
        <div class="mb-6 mt-4" v-if="groupedItems.length > 0">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Items</div>
                            <div class="text-lg font-semibold">{{ form.length }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Missing Items</div>
                            <div class="text-lg font-semibold">{{ form.filter(item => item.status === 'Missing').length }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Damaged Items</div>
                            <div class="text-lg font-semibold">{{ form.filter(item => item.status === 'Damaged').length }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500">Received Items</div>
                            <div class="text-lg font-semibold">{{ form.filter(item => item.status === 'Received').length }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Order Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Back Order Items</h2>
                <div class="flex space-x-2">
                    <button v-if="form.length > 0" @click="exportToExcel" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export
                    </button>
                    <button @click="fetchBackOrders" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 border-b-2 border-r border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 border-b-2 border-r border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Packing List</th>
                            <th class="px-6 py-3 border-b-2 border-r border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 border-b-2 border-r border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 border-b-2 border-r border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 border-b-2 border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="isLoading">
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex justify-center">
                                    <svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Loading back orders...</p>
                            </td>
                        </tr>
                        <template v-else-if="groupedItems.length > 0">
                            <template v-for="group in groupedItems" :key="group.product_id + '-' + group.packing_list_number">
                                <tr v-for="(item, index) in group.items" :key="item.id" 
                                    class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                    <td v-if="index === 0" :rowspan="group.items.length" class="px-6 py-4 border-r border-gray-200">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900">{{ group.product_name }}</div>
                                            <div class="text-xs text-gray-500 mt-1">ID: {{ group.product || 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td v-if="index === 0" :rowspan="group.items.length" class="px-6 py-4 border-r border-gray-200">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <span class="text-sm text-gray-700">{{ group.packing_list_number }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-200">
                                        <span class="text-sm font-medium">{{ item.quantity }}</span>
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-200">
                                        <span :class="{
                                            'px-2.5 py-0.5 inline-flex text-xs leading-5 font-medium rounded-full': true,
                                            'bg-red-100 text-red-800': item.status === 'Damaged',
                                            'bg-yellow-100 text-yellow-800': item.status === 'Missing',
                                            'bg-green-100 text-green-800': item.status === 'Received',
                                            'bg-gray-100 text-gray-800': !['Damaged', 'Missing', 'Received'].includes(item.status)
                                        }">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 border-r border-gray-200 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="ml-1 text-sm text-black">{{ moment(item.created_at).format('DD/MM/YYYY HH:MM') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex space-x-2">
                                            <!-- Receive button for all items -->
                                            <button @click="openReceiveModal(item)" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded-md text-xs flex items-center transition-colors duration-150">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Receive
                                            </button>
                                            
                                            <!-- Liquidate button for missing items -->
                                            <button v-if="item.status.toLowerCase() === 'missing'" @click="liquidate(item)" class="bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded-md text-xs flex items-center transition-colors duration-150">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Liquidate
                                            </button>
                                            
                                            <!-- Dispose button for damaged and lost items -->
                                            <button v-if="['damaged', 'lost'].includes(item.status.toLowerCase())" @click="dispose(item)" class="bg-orange-600 hover:bg-orange-700 text-white py-1 px-3 rounded-md text-xs flex items-center transition-colors duration-150">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Dispose
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <tr v-else>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">No back orders found</p>
                                <p class="text-gray-400 text-sm mt-1">Select a purchase order to view its back orders</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quantity Update Modal -->
        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Update Quantity</h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Current Quantity
                    </label>
                    <input
                        type="number"
                        v-model="selectedItem.quantity"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': selectedItem.quantityError }"
                        min="0"
                        step="1"
                    >
                    <p v-if="selectedItem.quantityError" class="mt-1 text-sm text-red-600">{{ selectedItem.quantityError }}</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="selectedItem.status">
                        <option value="Missing" >Received</option>
                        <option value="Lost" >Lost</option>
                        <option value="Damaged" >Damaged</option>
                        <option value="Expired" >Expired</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeUpdateModal"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateQuantity"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :disabled="processing"
                    >
                        {{ processing ? 'Updating...' : 'Update' }}
                    </button>
                </div>
            </div>
        </Modal>
        
        <!-- File Upload Modal -->
        <FileUploadModal 
            :is-open="showFileUploadModal" 
            :title="fileUploadModalTitle"
            @close="showFileUploadModal = false"
            @submit="handleFileSubmit"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import FileUploadModal from '@/Components/FileUploadModal.vue';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import * as XLSX from 'xlsx';

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    po: Array,
    warehouses: Array,
    // suppliers: Array,
    // po_number: Number
});

const purchase_order = ref(null)

const form = ref([]);

const processing = ref(false);

const isLoading = ref(false);
const showUpdateModal = ref(false);
const selectedItem = ref({});

const openUpdateModal = (item) => {
    selectedItem.value = { ...item };
    showUpdateModal.value = true;
};

const showFileUploadModal = ref(false);
const fileUploadModalTitle = ref('');
const fileUploadAction = ref('');
const currentItem = ref(null);
const currentNote = ref('');

const liquidate = async (item) => {
    // First show the basic confirmation dialog
    const { isConfirmed, value: note } = await Swal.fire({
        title: 'Liquidate Item',
        text: `Are you sure you want to liquidate this item?`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Note (optional)',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, liquidate it!',
    });
    
    if (!isConfirmed) return;
    
    // Open the file upload modal
    currentItem.value = item;
    currentNote.value = note || '';
    fileUploadModalTitle.value = 'Attach Files for Liquidation';
    fileUploadAction.value = 'liquidate';
    showFileUploadModal.value = true;
};

// Handle file submission for liquidation
const handleFileSubmit = async (files) => {
    if (!currentItem.value) return;
    
    // Show loading state
    Swal.fire({
        title: 'Processing...',
        html: 'Please wait while we process your request',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create FormData and append files
    const formData = new FormData();
    const item = currentItem.value;
    
    // Determine which action to take
    if (fileUploadAction.value === 'liquidate') {
        formData.append('id', item.id);
        formData.append('product_id', item.product_id);
        formData.append('packing_list_id', item.packing_list_id);
        formData.append('quantity', item.quantity);
        formData.append('status', item.status);
        formData.append('note', currentNote.value);
        
        // Append files if any
        if (files && files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                formData.append(`attachments[${i}]`, files[i]);
            }
        }
        
        try {
            const response = await axios.post(route('back-order.liquidate'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            showFileUploadModal.value = false;
            
            Swal.fire({
                title: 'Liquidated!',
                text: response.data.message,
                icon: 'success'
            });
            
            fetchBackOrders(); // Refresh the data
        } catch (error) {
            showFileUploadModal.value = false;
            
            Swal.fire({
                title: 'Error',
                text: error.response?.data?.message || 'An error occurred while processing your request',
                icon: 'error'
            });
        }
    } else if (fileUploadAction.value === 'dispose') {
        formData.append('id', item.id);
        formData.append('product_id', item.product_id);
        formData.append('packing_list_id', item.packing_list_id);
        formData.append('purchase_order_id', purchase_order.value?.id);
        formData.append('quantity', item.quantity);
        formData.append('status', item.status);
        formData.append('note', currentNote.value);
        
        // Append files if any
        if (files && files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                formData.append(`attachments[${i}]`, files[i]);
            }
        }
        
        try {
            const response = await axios.post(route('back-order.dispose'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            
            showFileUploadModal.value = false;
            
            Swal.fire({
                title: 'Disposed!',
                text: response.data.message,
                icon: 'success'
            });
            
            fetchBackOrders(); // Refresh the data
        } catch (error) {
            showFileUploadModal.value = false;
            
            Swal.fire({
                title: 'Error',
                text: error.response?.data?.message || 'An error occurred while processing your request',
                icon: 'error'
            });
        }
    }
};

const dispose = async (item) => {
    // First show the basic confirmation dialog
    const { isConfirmed, value: note } = await Swal.fire({
        title: 'Dispose Item',
        text: `Are you sure you want to dispose this ${item.status.toLowerCase()} item?`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Note (optional)',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, dispose it!',
    });
    
    if (!isConfirmed) return;
    
    // Open the file upload modal
    currentItem.value = item;
    currentNote.value = note || '';
    fileUploadModalTitle.value = 'Attach Files for Disposal';
    fileUploadAction.value = 'dispose';
    showFileUploadModal.value = true;
};

const openReceiveModal = (item) => {
    Swal.fire({
        title: 'Receive Item',
        html: `
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Item: ${item.product?.name || 'Unknown'}</p>
                <p class="text-sm text-gray-600 mb-2">Total Quantity: ${item.quantity}</p>
            </div>
            <div class="mb-4">
                <label for="receive-quantity" class="block text-sm font-medium text-gray-700 text-left mb-1">Quantity to Receive:</label>
                <input type="number" id="receive-quantity" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                       min="1" max="${item.quantity}" value="${item.quantity}">
            </div>
            <div class="mb-4">
                <label for="receive-note" class="block text-sm font-medium text-gray-700 text-left mb-1">Note (optional):</label>
                <textarea id="receive-note" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" rows="2"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Receive',
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const quantity = document.getElementById('receive-quantity').value;
            const note = document.getElementById('receive-note').value;
            
            if (!quantity || quantity <= 0 || quantity > item.quantity) {
                Swal.showValidationMessage('Please enter a valid quantity (1 to ' + item.quantity + ')');
                return false;
            }
            
            return axios.post(route('back-order.receive'), {
                id: item.id,
                product_id: item.product_id,
                packing_list_id: item.packing_list_id,
                purchase_order_id: purchase_order.value?.id,
                quantity: parseInt(quantity),
                original_quantity: item.quantity,
                note: note
            })
            .then(response => {
                return response.data;
            })
            .catch(error => {
                Swal.showValidationMessage(
                    `Request failed: ${error.response?.data?.message || error.message}`
                );
                return false;
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Received!',
                text: result.value.message,
                icon: 'success'
            });
            fetchBackOrders(); // Refresh the data
        }
    });
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    selectedItem.value = {};
};

const validateFields = (item) => {
    const errors = [];
    if (!item.quantity || item.quantity <= 0) errors.push('Quantity is required and must be greater than 0');
    if (!item.status) errors.push('Status is required');
    if (!item.packing_list_id) errors.push('Packing list is required');
    if (!item.product_id) errors.push('Product is required');
    return errors;
};

const updateQuantity = async () => {
    if (!validateQuantity(selectedItem.value)) return;

    // Validate all required fields
    const errors = validateFields(selectedItem.value);
    if (errors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: errors.join('<br>'),
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }

    // Ask for confirmation
    const confirmed = await Swal.fire({
        title: 'Confirm Update',
        text: 'Are you sure you want to update this back order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'No, cancel'
    });

    if (!confirmed.isConfirmed) return;

    try {
        processing.value = true;
        await axios.post(route('supplies.backorder-status'), {
            id: selectedItem.value.id,
            packing_list_id: selectedItem.value.packing_list_id,
            product_id: selectedItem.value.product_id,
            quantity: selectedItem.value.quantity,
            status: selectedItem.value.status,
            action: 'Received'
        });
        
        toast.success('Back order updated successfully');
        await fetchBackOrders(); // Use the new function to refresh data
        closeUpdateModal();
    } catch (error) {
        console.error(error);
        toast.error(error.response?.data || 'Failed to update back order');
    } finally {
        processing.value = false;
    }
};

const validateQuantity = (item) => {
    const qty = parseFloat(item.quantity);
    
    if (isNaN(qty) || qty < 0) {
        item.quantityError = 'Quantity must be a positive number';
        return false;
    }
    
    item.quantityError = '';
    return true;
};

const groupedItems = computed(() => {
    const groups = {};
    
    if (!form.value || !Array.isArray(form.value)) return [];
    
    form.value.forEach(item => {
        if (!item.product_id) return;
        
        const key = `${item.product_id}-${item.packing_list?.packing_list_number}`;
        
        if (!groups[key]) {
            groups[key] = {
                product_id: item.product_id,
                packing_list_number: item.packing_list?.packing_list_number,
                product_name: item.product?.name,
                product_barcode: item.product?.barcode,
                items: []
            };
        }
        groups[key].items.push(item);
    });

    // Sort by product name and packing list number
    return Object.values(groups).sort((a, b) => {
        if (a.product_name === b.product_name) {
            return (a.packing_list_number || '').localeCompare(b.packing_list_number || '');
        }
        return (a.product_name || '').localeCompare(b.product_name || '');
    });
});


async function onPOChange(selected) {
    isLoading.value = true;
    form.value = [];
    
    if(!selected) {
        isLoading.value = false;
        purchase_order.value = null;
        return;
    }
    purchase_order.value = selected;
 
    await axios.get(route('supplies.get-packingList', selected.id))
        .then((response) => {
            isLoading.value = false;
            // Check if the data is nested under packingLists key
            if (response.data && response.data.packingLists) {
                form.value = response.data.packingLists;
            } else {
                form.value = response.data;
            }
            console.log(response.data);
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response.data);
        });
}

// Function to fetch back orders for the current purchase order
const fetchBackOrders = async () => {
    if (!purchase_order.value) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(route('supplies.get-packingList', purchase_order.value.id));
        // Check if the data is nested under packingLists key
        if (response.data && response.data.packingLists) {
            form.value = response.data.packingLists;
        } else {
            form.value = response.data;
        }
    } catch (error) {
        console.error('Error fetching back orders:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to refresh back order data',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        isLoading.value = false;
    }
}

// Function to export back order data to Excel
const exportToExcel = () => {
    if (!form.value || form.value.length === 0) {
        Swal.fire({
            title: 'No Data',
            text: 'There is no data to export',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    try {
        // Prepare data for export
        const exportData = form.value.map(item => ({
            'Product ID': item.product?.productID || '',
            'Product Name': item.product?.name || '',
            'Packing List': item.packing_list?.packing_list_number || '',
            'Batch Number': item.packing_list?.batch_number || '',
            'Quantity': item.quantity,
            'Status': item.status,
            'Created Date': new Date(item.created_at).toLocaleString(),
            'Expire Date': item.packing_list?.expire_date || ''
        }));
        
        // Create worksheet
        const worksheet = XLSX.utils.json_to_sheet(exportData);
        
        // Create workbook
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Back Orders');
        
        // Generate filename with current date
        const fileName = `BackOrders_${purchase_order.value.po_number}_${new Date().toISOString().split('T')[0]}.xlsx`;
        
        // Export to file
        XLSX.writeFile(workbook, fileName);
        
        toast.success('Back order data exported successfully');
    } catch (error) {
        console.error('Error exporting data:', error);
        Swal.fire({
            title: 'Export Failed',
            text: 'There was an error exporting the data',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

</script>

