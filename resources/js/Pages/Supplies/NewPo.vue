<template>

    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <div class="">
            <!-- Supplier Selection -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-[400px] mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Supplier
                        </label>
                        <select v-model="form.supplier_id" @change="onSupplierChange"
                            class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select supplier name</option>
                            <option :value="s.id" v-for="s in props.suppliers">{{ s.name }}</option>
                        </select>
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
                            <p class="mt-1 text-sm text-gray-900">P.O Number #: {{ props.po_number }}</p>
                            <div class="mt-1 flex">
                                Data: <input 
                                    type="date" 
                                    v-model="po_date"
                                    class="block ml-2 border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    :min="moment().format('YYYY-MM-DD')"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="mt-8 flex-1 flex flex-col">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Barcode</th>
                                <th class="w-[150px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    Dose</th>
                                <th class="w-[100px] px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                    Qty</th>
                                <th class="w-[120px] px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                    Unit Cost</th>
                                <th class="w-[120px] px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                    Amount</th>
                                <th class="w-[40px] px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm text-gray-500 align-top pt-4">{{ index + 1 }}</td>
                                <td class="px-3 py-2 relative">
                                    <div class="relative">
                                        <input type="text" v-model="item.searchQuery" @input="filterProducts(index)"
                                            class="block w-full border-0 p-0 text-gray-900 placeholder-gray-500 focus:ring-0 sm:text-sm"
                                            placeholder="Select a product/service" autocomplete="off">
                                        <div v-if="item.searchQuery && filteredProducts[index]?.length > 0"
                                            class="absolute z-[999] mt-1 w-full bg-white shadow-xl max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm border border-gray-100">
                                            <div v-for="product in filteredProducts[index]" :key="product.id"
                                                @click="handleProductSelect(product, index)"
                                                class="cursor-pointer select-none py-2 px-4 hover:bg-gray-100 text-gray-900 text-sm">
                                                {{ product.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" v-model="item.barcode" readonly
                                        class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent"
                                        :placeholder="item.barcode || 'No barcode'">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" v-model="item.dose" readonly
                                        class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.quantity" @input="calculateTotal(index)"
                                        class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                        min="1">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" v-model="item.unit_cost" @input="calculateTotal(index)"
                                        class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                        step="0.01" min="0">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                        class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm bg-transparent">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button type="button" @click="removeItem(index)"
                                        class="text-gray-400 hover:text-red-600">
                                        <TrashIcon class="h-4 w-4" />
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
                                    <span class="font-medium text-gray-900">Subtotal</span>
                                    <span class="text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                </div>
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
                <button type="button"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button @click="submitForm" type="button"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save and Exit
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import moment from 'moment';
import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    products: Array,
    suppliers: Array,
    po_number: Number
});

const selectedSupplier = ref(null);
const form = ref({
    supplier_id: "",
    po_number: props.po_number,
    po_date: moment().format('YYYY-MM-DD'),
    items: [
        {
            product_id: null,
            searchQuery: '',
            barcode: "",
            dose: "",
            quantity: 1,
            unit_cost: 0,
            total_cost: 0
        }
    ]
});

const filteredProducts = ref([[]]);  // Initialize with one empty array for first row

function addItem() {
    form.value.items.push({
        product_id: null,
        searchQuery: '',
        barcode: "",
        dose: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
    });
    // Add a new empty array for the new row's filtered products
    filteredProducts.value.push([]);
}

function removeItem(index) {
    form.value.items.splice(index, 1);
    // Also remove the filtered products for this row
    filteredProducts.value.splice(index, 1);

    // If all items are removed, add one back
    if (form.value.items.length === 0) {
        addItem();
    }
}

function handleProductSelect(product, index) {
    form.value.items[index].product_id = product.id;
    form.value.items[index].searchQuery = product.name;
    form.value.items[index].barcode = product.barcode || '';
    form.value.items[index].dose = product.dose || '';
    form.value.items[index].unit_cost = product.unit_cost || 0;
    calculateTotal(index);

    // Clear the filtered products to hide dropdown
    filteredProducts.value[index] = [];

    // Add new item after selection
    addItem();
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
}

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

async function submitForm() {
    if (!form.value.supplier_id) {
        toast.warning('Please select a supplier');
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
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Filter out items without product_id
            const validItems = form.value.items.filter(item => item.product_id);
            if (validItems.length === 0) {
                toast.warning('Please add at least one item');
                return;
            }

            try {
                const formData = {
                    ...form.value,
                    items: validItems
                };

                const response = await axios.post(route('supplies.storePO'), formData);
                Swal.fire({
                    title: "Success!",
                    text: "Purchase order created successfully",
                    icon: "success"
                }).then(() => {
                    router.visit(route('supplies.index'));
                });

            } catch (error) {
                if (error.response?.data?.errors) {
                    // Show validation errors
                    Object.values(error.response.data.errors).forEach(errors => {
                        errors.forEach(error => toast.error(error));
                    });
                } else {
                    toast.error(error.response?.data || 'Failed to create purchase order');
                }
                console.error('Error:', error.response?.data);
            }
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
async function onSupplierChange(e) {
    isLoading.value = true;
    let value = e.target.value;
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

function filterProducts(index) {
    const query = form.value.items[index].searchQuery?.toLowerCase() || '';
    if (!query) {
        filteredProducts.value[index] = [];
        return;
    }
    filteredProducts.value[index] = props.products.filter(p =>
        p.name.toLowerCase().includes(query)
    );
}

// const po_date = ref(moment().format('YYYY-MM-DD'));
</script>

<style>
.multiselect-option.is-pointed {
    color: white;
    background: #4f46e5;
}

.multiselect-option.is-selected {
    color: white;
    background: #4f46e5;
}

.multiselect-option {
    padding: 8px 12px;
}

.multiselect-single-label {
    padding: 4px 0;
}

.multiselect.is-active {
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    border-color: #4f46e5;
}

.multiselect {
    min-height: 42px;
}

.multiselect-no-options {
    padding: 8px 12px;
    color: #6b7280;
}

.product-select {
    width: 100%;
    --ms-tag-bg: #4f46e5;
    --ms-tag-color: #ffffff;
}

select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
    height: 38px;
}

select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 1px #4f46e5;
}

.relative {
    position: relative;
}

.absolute {
    position: absolute;
}

.z-50 {
    z-index: 50;
}

.shadow-xl {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

tbody {
    position: relative;
}

tr {
    position: relative;
}

td {
    position: relative;
}
</style>