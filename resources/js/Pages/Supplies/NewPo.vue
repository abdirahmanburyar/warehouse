<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 cursor-pointer">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Suppliers
        </Link>
        <div class="">
            <!-- Supplier Selection -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-[400px] mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Supplier
                        </label>
                        <Multiselect v-model="form.supplier" :value="form.supplier_id"
                            :options="props.suppliers"
                            :searchable="true" :close-on-select="true" :show-labels="false"
                            :allow-empty="true" placeholder="Select supplier" track-by="id" label="name"
                                @select="handleSupplierSelect">
                        </Multiselect>
                    </div>

                    <!-- Supplier Details Card -->
                    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-28"></div>
                        </div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-3"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-40 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-44"></div>
                        </div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-3"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-28 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-32"></div>
                        </div>
                    </div>
                    <div v-else-if="selectedSupplier"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier Details</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.name }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.contact_person }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.email }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.phone }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.address }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Purchase Order Info</h3>
                            <p class="mt-1 text-sm text-gray-900">P.O No. #: {{ form.po_number}} </p>
                            <p class="mt-1 text-sm text-gray-900">Ref. No. #: <input type="text" v-model="form.original_po_no" class="border-0"/></p>
                            <div class="mt-1 flex flex-col gap-2">
                                <div class="flex items-center">
                                    Data: <input 
                                        type="date" 
                                        v-model="form.po_date"
                                        class="border-0"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="file"
                                        ref="fileInput"
                                        @change="handleFileUpload"
                                        accept=".xlsx,.xls"
                                        class="hidden"
                                    />
                                    <button
                                        type="button"
                                        @click="$refs.fileInput.click()"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Upload Excel
                                    </button>
                                    <button
                                        type="button"
                                        @click="downloadTemplate"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    >
                                        Download Template
                                    </button>
                                    <span v-if="uploadStatus" class="text-sm text-gray-600">{{ uploadStatus }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- notes -->
            <div class="mt-8 flex-1 flex flex-col">
                <label for="notes" class="text-sm font-medium text-gray-500">Memo</label>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <textarea v-model="form.notes" class="w-full p-3 border-none resize-none" placeholder="Enter memo"></textarea>
                </div>
            </div>

            <!-- Items List -->
           <form @submit.prevent="submitForm">
            <div class="mt-8 flex-1 flex flex-col">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#
                                </th>
                                <th class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="w-[100px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Qty</th>
                                <th class="w-[100px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    UoM</th>
                                <th class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Unit Cost</th>
                                <th class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Amount</th>
                                <th class="w-[40px] px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50" :data-item-index="index">
                                <td class="px-3 py-2 text-sm text-gray-500 align-top pt-4">{{ index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <Multiselect v-model="item.product" :value="item.product_id"
                                        :options="props.products"
                                        :searchable="true" :close-on-select="true" :show-labels="false" required
                                        :allow-empty="true" placeholder="Select item" track-by="id" label="name"
                                            @select="hadleProductSelect(index, $event)">
                                    </Multiselect>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.quantity" @input="calculateTotal(index)" required
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm"
                                        min="1">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" v-model="item.uom" required
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm">
                                </td>                                
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.unit_cost" @input="calculateTotal(index)" required
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm"
                                        step="0.01" min="0">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                        class="text-gray-400 hover:text-red-600">
                                        <TrashIcon class="h-6 w-6 text-red-600 " />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td colspan="7" class="px-3 py-4 text-center text-sm text-gray-500">
                                    No items added. Click "Add Item" to start creating your purchase order.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 px-3 py-4">
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button type="button" @click="addItem"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Add Item
                                </button>
                                <button type="button" @click="form.items = []"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Clear all Items
                                </button>
                            </div>
                            <div class="w-72 space-y-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-900">Total</span>
                                    <span class="text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end space-x-3 mb-[80px]">
                <button type="button" @click="router.visit(route('supplies.index'))" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Exit
                </button>
                <button type="submit" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{  isSubmitting ? "Saving..." : "Save and Exit" }}
                </button>
            </div>
           </form>
        </div>
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
        toast.warning('Please select a supplier');
        return;
    }



    // Show confirmation dialog
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

            // Submit the form
            axios.post(route('supplies.storePO'), form.value, {
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
                toast.error(error.response?.data || 'Failed to create purchase order');
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
