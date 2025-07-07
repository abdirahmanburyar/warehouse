<template>
    <Head title="New Purchase Order" />
    <AuthenticatedLayout title="New Purchase Order" description="Create a new purchase order and add items" img="/assets/images/orders.png">
        <!-- Back Navigation -->
        <Link :href="route('supplies.index')" class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition-colors duration-200 group mb-6">
            <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Purchase Orders
        </Link>

        <!-- Header Section -->
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl p-6 mb-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">New Purchase Order</h1>
                    <p class="text-gray-600 mt-1">Create a new purchase order and add items</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">Draft</div>
                </div>
            </div>
        </div>

        <!-- Supplier Selection Card -->
        <div class="bg-white rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Supplier Information
            </h2>
            <!-- Supplier Selection Row -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Supplier</label>
                <div class="max-w-md">
                    <Multiselect v-model="form.supplier" :value="form.supplier_id"
                        :options="props.suppliers"
                        :searchable="true" :close-on-select="true" :show-labels="false"
                        :allow-empty="true" placeholder="Search and select supplier..." track-by="id" label="name"
                        class="multiselect-modern"
                        @select="handleSupplierSelect">
                    </Multiselect>
                </div>
            </div>
            <!-- Supplier Details -->
            <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                </div>
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                </div>
            </div>
            <div v-else-if="selectedSupplier" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Company Details</h3>
                        <p class="text-base font-semibold text-gray-900 mt-1">{{ selectedSupplier.name }}</p>
                        <p class="text-sm text-gray-600">{{ selectedSupplier.contact_person }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Contact Information</h3>
                        <div class="mt-2 space-y-2">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ selectedSupplier.email }}
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ selectedSupplier.phone }}
                            </div>
                            <div class="flex items-start text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ selectedSupplier.address }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Order Information</h3>
                        <div class="mt-3 space-y-3">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 mr-2">P.O Number:</span>
                                <span class="text-sm font-semibold text-gray-900">{{ form.po_number }}</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 mr-2">Reference No:</span>
                                <input type="text" v-model="form.original_po_no" :disabled="form.approved_at"
                                    class="text-sm border-0 bg-transparent focus:ring-0 focus:border-b-2 focus:border-indigo-500"
                                    placeholder="Enter reference" />
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-600 mr-2">Date:</span>
                                <input type="date" v-model="form.po_date" :disabled="form.approved_at"
                                    class="text-sm border-0 bg-transparent focus:ring-0 focus:border-b-2 focus:border-indigo-500" />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <input type="file" ref="fileInput" @change="handleFileUpload" accept=".xlsx,.xls" class="hidden" />
                        <button type="button" @click="$refs.fileInput.click()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Upload Excel
                        </button>
                        <button type="button" @click="downloadTemplate"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Template
                        </button>
                        <span v-if="uploadStatus" class="text-sm text-gray-600 animate-pulse">{{ uploadStatus }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <form @submit.prevent="submitForm" novalidate>
            <div class="bg-white rounded-xl p-0 mb-4">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Order Items
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Add items to your purchase order</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-sm text-gray-600">
                                Total: <span class="font-semibold text-indigo-600">{{ formatCurrency(subtotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-12 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 350px;">Item</th>
                            <th class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                            <th class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="w-12 px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in form.items" :key="index" 
                            class="hover:bg-gray-50 transition-colors duration-150"
                            :data-item-index="index">
                            <td class="px-3 py-3 text-sm text-gray-500 align-middle">{{ index + 1 }}</td>
                            <td class="px-3 py-3" style="width: 350px;">
                                <Multiselect v-model="item.product" :value="item.product_id"
                                    :options="props.products"
                                    :searchable="true" :close-on-select="true" :show-labels="false" required
                                    :allow-empty="true" placeholder="Search and select item..." track-by="id" label="name"
                                    class="multiselect-modern"
                                    @select="hadleProductSelect(index, $event)">
                                </Multiselect>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" v-model="item.quantity" @input="calculateTotal(index)" required
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1"
                                    min="1" placeholder="Enter quantity">
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" v-model="item.uom" required
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1"
                                    placeholder="e.g. PCS">
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" v-model="item.unit_cost" @input="calculateTotal(index)" required
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1"
                                    step="0.01" min="0" placeholder="Enter cost">
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                    class="block w-full rounded-lg bg-gray-50 border-gray-200 text-sm text-gray-500 px-2 py-1"
                                    placeholder="$0.00">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button type="button" @click="removeItem(index)"
                                    class="text-gray-400 hover:text-red-600 transition-colors duration-200 p-1 rounded-lg hover:bg-red-50">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="form.items.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No items added</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding items to your purchase order.</p>
                                    <div class="mt-6">
                                        <button type="button" @click="addItem"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                            <PlusIcon class="h-5 w-5 mr-2" />
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Table Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button type="button" @click="addItem"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <PlusIcon class="h-5 w-5 mr-2 text-gray-400" />
                                Add Item
                            </button>
                            <button type="button" @click="form.items = []"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Clear All
                            </button>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(subtotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="bg-white rounded-xl mb-3">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Additional Notes
                </h3>
                <textarea v-model="form.notes" rows="3" 
                    class="w-full border-gray-300 rounded-lg focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 resize-none"
                    placeholder="Enter any additional notes or special instructions for this purchase order..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-xl p-6 mb-[80px] flex justify-end gap-3">
                <button type="button" @click="router.visit(route('supplies.index'))" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    Exit
                </button>
                <button type="submit" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isSubmitting ? "Saving..." : "Save Purchase Order" }}
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import * as XLSX from 'xlsx';
import { ref, computed } from 'vue';
import axios from 'axios';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import moment from 'moment';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    products: Array,
    suppliers: Array,
    po_number: [String, Number]
});

const selectedSupplier = ref(null);
const form = ref({
    id: null,
    supplier_id: "",
    supplier: null,
    original_po_no: "",
    notes: "",
    po_number: props.po_number,
    po_date: new Date().toISOString().split('T')[0],
    items: []
});

const fileInput = ref(null);
const uploadStatus = ref('');

async function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!form.value.supplier_id) {
        uploadStatus.value = 'Please select a supplier first';
        event.target.value = '';
        return;
    }

    uploadStatus.value = 'Creating purchase order...';

    // First create/update the purchase order
    const poData = {
        supplier_id: form.value.supplier_id,
        po_date: moment(form.value.po_date).format('YYYY-MM-DD'),
        po_number: form.value.po_number
    };

    const poResponse = await axios.post(route('purchase-orders.store'), poData);
    const purchaseOrderId = poResponse.data.id;

    // Now upload the Excel file
    uploadStatus.value = 'Uploading items...';
    const formData = new FormData();
    formData.append('file', file);
    formData.append('purchase_order_id', purchaseOrderId);
    form.value.id = purchaseOrderId;

    const response = await axios.post(route('purchase-orders.import'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then((response) => {
        // Update items with imported data
        form.value.items = response.data.map(item => ({
            id: item.id,
            product_id: item.product_id,
            product_name: item.product_name,
            quantity: item.quantity,
            unit_cost: item.unit_cost,
            total_cost: item.total_cost,
            uom: item.uom,
            product: item.product,
        }));
        // Show success message
        toast.success('Items imported successfully');

        // Clear file input
        event.target.value = '';

        // // Display the imported items
        // items.value = response.data.items;
        // totalAmount.value = response.data.total_amount;
        toast.success('Items imported successfully');
        uploadStatus.value = "";
    })
    .catch((error) => {
        console.error('Upload failed:', error);
        uploadStatus.value = 'Upload failed: ' + (error.response.data);
    });

    // Clear the file input
    event.target.value = '';
}

function hadleProductSelect(index, selected){
    form.value.items[index].product_id = selected.id;
    form.value.items[index].product = selected;
    addItem();
}

function handleSupplierSelect(selected){
    form.value.supplier_id = selected.id;
    form.value.supplier = selected;
    onSupplierChange(selected);
    addItem();
}

function addItem() {
    // Only add a new row if the last row has a valid product_id
    if (form.value.items.length > 0) {
        const lastItem = form.value.items[form.value.items.length - 1];
        if (!lastItem.product_id) {
            return;
        }
    }
    form.value.items.push({
        product_id: null,
        product: null,
        uom: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
    });
}

function removeItem(index) {
    form.value.items.splice(index, 1);

    // If all items are removed, add one back
    if (form.value.items.length === 0) {
        addItem();
    }
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
}

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

const isSubmitting = ref(false);

async function submitForm() {
    if (!form.value.supplier_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please select a supplier'
        });
        return;
    }

    // Filter out items with no product_id
    const filteredItems = form.value.items.filter(item => item.product_id);
    if (filteredItems.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please add at least one item'
        });
        return;
    }

    // Validate only items with product_id
    const invalidItems = filteredItems.filter(item => !item.uom || !item.quantity || !item.unit_cost);
    if (invalidItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please fill in all required fields for each item (Product, Quantity, and Unit Cost)'
        });
        return;
    }

    Swal.fire({
        title: "Confirm Creation",
        text: "Are you sure you want to create this purchase order?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, create it!"
    }).then((result) => {
        if (result.isConfirmed) {
            isSubmitting.value = true;
            axios.post(route('supplies.storePO'), { ...form.value, items: filteredItems }, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                isSubmitting.value = false;
                Swal.fire({
                    title: "Success!",
                    text: response.data,
                    icon: "success"
                }).then(() => {
                    router.visit(route('supplies.index'));
                });
            })
            .catch((error) => {
                console.log(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data || 'Failed to create purchase order'
                });
                isSubmitting.value = false;
            });
        }
    });
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

const isLoading = ref(false);
async function onSupplierChange(selected) {
    isLoading.value = true;
    let value = selected.id;
    if (!value) {
        selectedSupplier.value = null;
        form.value.supplier_id = null;
        isLoading.value = false;
        return;
    }

    form.value.supplier_id = value;
    const supplier = props.suppliers.find(s => s.id == value);
    selectedSupplier.value = supplier;
    setTimeout(() => isLoading.value = false, 1000);
}

function downloadTemplate() {
    // Define the template headers
    const headers = ['Item Description', 'UoM', 'Quantity', 'Category', 'Dosage Form', 'Unit Cost', 'Total Cost'];
    
    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet([headers]);
    
    // Auto-size columns
    const colWidths = headers.map(header => ({ wch: header.length + 5 }));
    ws['!cols'] = colWidths;
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Template');
    
    // Generate and download file
    XLSX.writeFile(wb, 'supplies_import_template.xlsx');
}

// const po_date = ref(moment().format('YYYY-MM-DD'));
</script>
