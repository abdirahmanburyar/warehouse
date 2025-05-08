<template>
    <Head title="Purchase Order" />
    <AuthenticatedLayout title="Purchase Orders" description="Manage your purchase orders">
        <div class="flex flex-col">
            <!-- Supplier Selection -->
            <div class="p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Received New Supply</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-[400px] mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Supplier
                        </label>
                        <select v-model="form.supplier_id" @change="onPOChange"
                            class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select P.O Number</option>
                            <option :value="s.id" v-for="s in props.purchaseOrders">{{ s.po_number }}</option>
                        </select>
                    </div>
                    <!-- Supplier Details Card -->
                    <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded animate-pulse w-28"></div>
                        </div>
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
                    <div v-else-if="selectedPO"
                        class="grid grid-cols-1 md:grid-cols-4 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">P.O Details</h3>
                            <p class="mt-1 text-sm text-gray-900">P.O No. {{ selectedPO.po_number }}</p>
                            <p class="mt-1 text-sm text-gray-900">P.O Date: {{ moment(selectedPO.po_date).format('LL') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Supplier Details</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.name }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.contact_person }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.email }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.phone }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedPO.supplier?.address }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Purchase Order Info</h3>
                            <p class="mt-1 text-sm text-gray-900">P.L No. {{ form.packing_list_number }}</p>
                            <div class="mt-1 flex">
                                Data: <input 
                                    type="date" 
                                    v-model="form.pk_date"
                                    class="block ml-2 border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    :min="moment().format('YYYY-MM-DD')"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="p-4 border-b border-gray-200 bg-white sticky top-0 z-10">
                <h2 class="text-lg font-medium text-gray-900">Items</h2>
            </div>
            
            <div class="overflow-auto w-full">
            <!-- <div class="inline-block min-w-full align-middle"> -->
                <table class="border border-black w-max table-fixed">
                    <thead class="p-4">
                        <tr>
                            <th class="text-left text-sm text-black uppercase w-16 p-2 border border-black">SN#</th>
                            <th class="min-w-[500px] text-left text-sm text-black uppercase w-64 p-2 border border-black">Product</th>
                            <th class="min-w-[200px] text-left text-sm text-black uppercase w-32 p-2 border border-black">P.O Qty</th>
                            <th class="min-w-[200px] text-left text-sm text-black uppercase w-32 p-2 border border-black">Received Qty</th>
                            <th class="min-w-[400px] text-left text-sm text-black uppercase w-48 p-2 border border-black">Warehouse</th>
                            <th class="min-w-[500px] text-left text-sm text-black uppercase w-48 p-2 border border-black">Location</th>
                            <th class="text-left text-sm text-black uppercase w-40 p-2 border border-black">Expire date</th>
                            <th class="min-w-[200px] text-left text-sm text-black uppercase w-40 p-2 border border-black">Batch Number</th>
                            <th class="text-left text-sm text-black uppercase w-32 p-2 border border-black">Unit Cost</th>
                            <th class="text-left text-sm text-black uppercase w-32 p-2 border border-black">Total Cost</th>
                            <th class="min-w-[200px] text-left text-sm text-black uppercase w-32 p-2 border border-black">Fullfillment Rate</th>
                            <th class="text-left text-sm text-black uppercase w-32 p-2 border border-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in form.items" :key="index">
                            <td class="border border-black text-sm text-gray-900 p-2 w-16">{{ index + 1 }}</td>
                            <td class="border border-black p-2 w-64">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-900">{{item.searchQuery}}</span>
                                    <span class="text-sm text-gray-500">Barcode: {{item.barcode}}</span>
                                </div>
                            </td>
                            <td class="border border-black text-sm p-2 w-32">
                                <input type="number" v-model="item.quantity" @input="calculateTotal(index)"
                                    class="block w-full p-1 border-0"
                                    readonly
                                    min="1">
                            </td>
                            <td class="border border-black text-sm p-2 w-32">
                                <input type="number" v-model="item.received_quantity" @input="validateReceivedQuantity(index)"
                                    :readonly="item.id != null"
                                    class="block w-full p-1 border-0"
                                    min="0" :max="item.quantity">
                            </td>
                            <td class="border border-black text-sm p-2 w-48">
                                <select v-model="item.warehouse_id" class="block w-full p-1 border-0" placeholder="Select a warehouse">
                                    <option value="" disable selected="selected">Select a warehouse</option>
                                    <option :value="w.id" v-for="w in props.warehouses">{{w.name}}</option>
                                </select>
                            </td>
                            <td class="border border-black text-sm p-2">
                                <div class="absolute inset-0 w-full location-dropdown">
                                    <div @click="activeLocationDropdown = index" class="cursor-pointer">
                                        <input 
                                            type="text" 
                                            v-model="item.location"
                                            @focus="activeLocationDropdown = index"
                                            placeholder="Select a location..."
                                            class="block w-full p-0 border-0"
                                            readonly
                                        >
                                    </div>
                                    
                                    <div v-if="activeLocationDropdown === index" 
                                        class="absolute left-0 right-0 z-[999] mt-1 bg-white border rounded shadow-lg">
                                        <input 
                                            type="text" 
                                            v-model="locationSearch" 
                                            placeholder="Search locations..."
                                            class="w-full p-2 border-b text-sm focus:outline-none"
                                            @input="filterLocations"
                                            ref="searchInput"
                                        >
                                        <a 
                                            href="#" 
                                            @click.prevent="showLocationForm = true; activeLocationDropdown = null"
                                            class="block w-full text-left p-2 text-blue-600 hover:bg-gray-50 border-b text-sm"
                                        >
                                            + Add new location
                                        </a>
                                        <div class="max-h-48 overflow-y-auto py-1">
                                            <div 
                                                v-for="l in filteredLocations" 
                                                :key="l.location"
                                                @click="selectLocation(item, l.location)"
                                                class="px-3 py-1.5 hover:bg-gray-50 cursor-pointer text-sm"
                                            >
                                                {{ l.location }}
                                            </div>
                                            
                                            <div v-if="filteredLocations.length === 0" class="p-3 text-sm text-gray-500 text-center">
                                                {{ locationSearch ? 'No locations found' : 'Type to search locations' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="border border-black text-sm p-2 w-40">
                                <input type="date" v-model="item.expire_date"
                                    class="block w-full p-1 border-0">
                            </td>
                            <td class="border border-black text-sm p-2 w-40">
                                <input type="text" v-model="item.batch_number"  placeholder="Add Batch number"
                                    class="block w-full p-1 border-0">
                            </td>                                
                            <td class="border border-black text-sm p-2 w-32">
                                <input type="number" v-model="item.unit_cost"
                                    class="block w-full p-1 border-0"
                                    readonly
                                    step="0.01" min="0">
                            </td>
                            <td class="border border-black text-sm p-2 w-32">
                                <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                    class="block w-full p-1 bg-transparent border-0">
                            </td>
                            <td class="border border-black text-sm p-2 w-32">
                                {{item.fullfillment_rate}}
                            </td>
                            <td class="border border-black text-sm p-2 w-32 text-center">
                                <button type="button" v-if="item.received_quantity < item.quantity" @click="openDifferenceModal(index, item)" class="text-gray-500 hover:text-red-600 transition-colors">
                                    Back Order
                                </button>
                            </td>
                        </tr>
                        <tr v-if="form.items.length === 0">
                            <td colspan="12" class="px-3 py-4 text-center text-sm text-gray-500 border border-black">
                                No items added. Click "Add Item" to start creating your purchase order.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Difference Modal -->
            <TransitionRoot appear :show="isDifferenceModalOpen" as="template">
                <Dialog as="div" @close="closeDifferenceModal" class="relative z-10">
                    <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                        leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                        <div class="fixed inset-0 bg-black bg-opacity-25" />
                    </TransitionChild>

                    <div class="fixed inset-0 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4 text-center">
                            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                                enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                                leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                                <DialogPanel
                                    class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                        Record Differences
                                    </DialogTitle>
                                    <div class="mt-2">
                                        <div class="flex justify-between items-center mb-1">
                                            <p class="text-sm text-gray-500">
                                                Total Difference: {{ totalDifferenceQuantity }} / {{ remainingQuantity }}
                                            </p>
                                            <span :class="{
                                                'text-green-600': totalDifferenceQuantity < remainingQuantity,
                                                'text-yellow-600': totalDifferenceQuantity === remainingQuantity,
                                                'text-red-600': totalDifferenceQuantity > remainingQuantity
                                            }" class="text-sm font-medium">
                                                {{ percentageUsed }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-300"
                                                :class="{
                                                    'bg-green-500': totalDifferenceQuantity < remainingQuantity,
                                                    'bg-yellow-500': totalDifferenceQuantity === remainingQuantity,
                                                    'bg-red-500': totalDifferenceQuantity > remainingQuantity
                                                }"
                                                :style="`width: ${Math.min(100, percentageUsed)}%`">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Difference Table -->
                                    <div class="mt-4">
                                        <table class="min-w-full divide-y divide-gray-200 border border-black">
                                            <thead>
                                                <tr>
                                                    <th class="px-4 py-2 text-left border border-black">Quantity</th>
                                                    <th class="px-4 py-2 text-left border border-black">Status</th>
                                                    <th class="w-10 px-4 py-2 border border-black"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(diff, idx) in selectedItemDifferences" :key="idx" class="border-b border-black">
                                                    <td class="px-4 py-2 border-r border-black relative">
                                                        <input type="number" 
                                                            v-model="diff.quantity" 
                                                            min="0"
                                                            :max="remainingQuantity"
                                                            :readonly="diff.id != null"
                                                            @input="validateDifferenceQuantity($event, idx)"
                                                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                        <div class="absolute inset-y-0 right-0 flex items-center">
                                                            <button type="button"
                                                                class="rounded-r-md border border-l-0 px-2 focus:outline-none"
                                                                @click="incrementDifference(diff)">
                                                                <ChevronUpIcon class="h-3 w-3 text-gray-400" />
                                                            </button>
                                                            <button type="button"
                                                                class="rounded-r-md border border-l-0 px-2 focus:outline-none"
                                                                @click="decrementDifference(diff)">
                                                                <ChevronDownIcon class="h-3 w-3 text-gray-400" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2 border-r border-black">
                                                        <select :disabled="diff.id != null" v-model="diff.status" class="w-full bg-transparent border-0 p-0 focus:outline-none focus:ring-0">
                                                            <option value="" disabled hidden>Select status</option>
                                                            <option value="Expired">Expired</option>
                                                            <option value="Damaged">Damaged</option>
                                                            <option value="Missing">Missing</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button @click="removeDifference(idx)" class="text-red-500 hover:text-red-700">
                                                            <TrashIcon class="h-4 w-4" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <button @click="addDifferenceRow"
                                            :disabled="totalDifferenceQuantity >= remainingQuantity"
                                            class="mt-4 inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="{
                                                'bg-white hover:bg-gray-50': totalDifferenceQuantity < remainingQuantity,
                                                'bg-gray-100': totalDifferenceQuantity >= remainingQuantity
                                            }">
                                            <PlusIcon class="h-4 w-4 mr-1" />
                                            Add Row
                                        </button>
                                    </div>

                                    <div class="mt-6 flex justify-end space-x-3">
                                        <button @click="closeDifferenceModal"
                                            class="inline-flex justify-center rounded-md border border-transparent bg-gray-100 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-200">
                                            Cancel
                                        </button>
                                        <button type="button"
                                            class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 
                                            bg-blue-600 hover:bg-blue-700"
                                            @click="saveDifferences">Save</button>
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </Dialog>
            </TransitionRoot>

            <!-- Footer -->
            <div class="border-t border-gray-200 px-3 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                    </div>
                    <div class="w-72 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-900">Total</span>
                            <span class="text-gray-900">{{ formatCurrency(subtotal) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end space-x-3 mb-[80px]">
                <button type="button" :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button @click="submitForm" type="button" :disabled="!selectedPO | isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ isSubmitting ? 'Saving, Wait....' : 'Save and Exit'}}
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';
import moment from 'moment';
import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const toast = useToast();

const props = defineProps({
    purchaseOrders: Array,
    warehouses: Array,
    locations: Array,
    // po_number: Number
});

const selectedPO = ref(null);
const form = ref({
    supplier_id: "",
    packing_list_number: null,
    purchase_order_id: null,
    pk_date: moment().format('YYYY-MM-DD'),
    items: [{
        product_id: null,
        searchQuery: '',
        barcode: "",
        warehouse_id: null,
        expire_date: null,
        batch_number: "",
        location: "",
        quantity: 0,
        received_quantity: 0,
        fullfillment_rate: "0%",
        unit_cost: 0,
        total_cost: 0,
        differences: []
    }]
});

const filteredProducts = ref([[]]);  // Initialize with one empty array for first row

function handleProductSelect(product, index) {
    console.log(product)
    form.value.items[index].product_id = product.id;
    form.value.items[index].searchQuery = product.name;
    form.value.items[index].barcode = product.barcode || '';
    form.value.items[index].unit_cost = product.unit_cost || 0;
    calculateTotal(index);

    // Clear the filtered products to hide dropdown
    filteredProducts.value[index] = [];

    // Add new item after selection
    addItem();
}

function validateReceivedQuantity(index) {
    const item = form.value.items[index];
    if (item.received_quantity > item.quantity) {
        toast.error(`Received quantity cannot exceed ordered quantity (${item.quantity})`);
        item.received_quantity = item.quantity;
    }
    calculateTotal(index);
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.received_quantity * item.unit_cost;
}

function showDifference(item, index) {
    const remainingDiff = item.quantity - item.received_quantity;
    console.log(remainingDiff);
    const currentTotal = item.differences?.reduce((sum, diff) => sum + diff.quantity, 0) || 0;
    const maxDiff = remainingDiff - currentTotal;

    if (maxDiff <= 0) {
        toast.error('All differences have been recorded.');
        return;
    }

    const currentDiffs = item.differences?.length ? 
        `<div class="mb-4 p-3 bg-gray-50 rounded">
            <p class="font-medium text-sm text-gray-700">Current Differences:</p>
            ${item.differences.map(d => `<p class="text-sm text-gray-600">${d.quantity} units - ${d.status}</p>`).join('')}
        </div>` : '';

    Swal.fire({
        title: 'Record Difference',
        html: `
            <div class="mb-4 text-left">
                <p class="text-sm text-gray-600 mb-2">Order Quantity: ${item.quantity}</p>
                <p class="text-sm text-gray-600 mb-2">Received Quantity: ${item.received_quantity}</p>
                <p class="text-sm text-gray-600 mb-4">Total Difference: ${remainingDiff}</p>
            </div>
            ${currentDiffs}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity to Record (max: ${maxDiff})</label>
                <input type="number" id="difference-quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" min="1" max="${maxDiff}" value="${maxDiff}">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="difference-status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="damaged">Damaged</option>
                    <option value="missing">Missing</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Record',
        preConfirm: () => {
            const quantity = parseInt(document.getElementById('difference-quantity').value);
            const status = document.getElementById('difference-status').value;
            
            if (!quantity || quantity <= 0 || quantity > maxDiff) {
                Swal.showValidationMessage(`Please enter a quantity between 1 and ${maxDiff}`);
                return false;
            }
            
            return { quantity, status };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (!item.differences) {
                item.differences = [];
            }
            item.differences.push(result.value);
            
            // Check if all differences are recorded
            const newTotal = item.differences.reduce((sum, diff) => sum + diff.quantity, 0);
            if (newTotal === remainingDiff) {
                toast.success('All differences have been recorded');
            } else {
                const remaining = remainingDiff - newTotal;
                toast.success(`Difference recorded. ${remaining} units remaining to be recorded.`);
            }
        }
    });
}

function openDifferenceModal(index) {
    selectedItemIndex.value = index;
    const item = form.value.items[index];
    remainingQuantity.value = Number(item.quantity - item.received_quantity) || 0;
    selectedItemDifferences.value = item.differences || [];
    isDifferenceModalOpen.value = true;
}

// Modal state
const isDifferenceModalOpen = ref(false);
const selectedItemIndex = ref(null);
const selectedItemDifferences = ref([]);
const remainingQuantity = ref(0);

const percentageUsed = computed(() => {
    const total = Number(totalDifferenceQuantity.value) || 0;
    const remaining = Number(remainingQuantity.value) || 1; // prevent division by zero
    return Math.round((total / remaining) * 100);
});

const isExceeded = computed(() => {
    return totalDifferenceQuantity.value > remainingQuantity.value;
});

const canSave = computed(() => {
    return !isExceeded.value && 
           selectedItemDifferences.value.length > 0 && 
           selectedItemDifferences.value.every(diff => diff.status !== '') &&
           totalDifferenceQuantity.value === remainingQuantity.value;
});

function closeDifferenceModal() {
    isDifferenceModalOpen.value = false;
    selectedItemIndex.value = null;
}

function addDifferenceRow() {
    const newRow = {
        id: null,
        quantity: 1,
        status: ''
    };
    
    if (totalDifferenceQuantity.value + newRow.quantity > remainingQuantity.value) {
        toast.error(`Cannot exceed remaining quantity (${remainingQuantity.value})`);
        return;
    }
    
    selectedItemDifferences.value.push(newRow);
}

function validateDifferenceQuantity(event, index) {
    const newValue = parseInt(event.target.value) || 0;
    const currentDiff = selectedItemDifferences.value[index];
    const otherDiffsTotal = selectedItemDifferences.value.reduce((sum, diff, idx) => {
        return idx !== index ? sum + (parseInt(diff.quantity) || 0) : sum;
    }, 0);
    
    const totalWithNew = otherDiffsTotal + newValue;
    
    if (totalWithNew > remainingQuantity.value) {
        const maxAllowed = remainingQuantity.value - otherDiffsTotal;
        currentDiff.quantity = Math.max(0, maxAllowed);
        toast.error(`Total differences cannot exceed ${remainingQuantity.value}`);
    } else if (newValue < 0) {
        currentDiff.quantity = 0;
    } else {
        currentDiff.quantity = newValue;
    }
}

function incrementDifference(diff) {
    if (diff.quantity < remainingQuantity.value) {
        diff.quantity++;
    }
}

function decrementDifference(diff) {
    if (diff.quantity > 0) {
        diff.quantity--;
    }
}

function removeDifference(index) {
    selectedItemDifferences.value.splice(index, 1);
}

const totalDifferenceQuantity = computed(() => {
    return selectedItemDifferences.value.reduce((total, diff) => {
        const qty = typeof diff.quantity === 'string' ? parseInt(diff.quantity) : diff.quantity;
        return total + (Number.isFinite(qty) ? qty : 0);
    }, 0);
});

// Watch for changes in difference quantities
watch(totalDifferenceQuantity, (newTotal) => {
    if (newTotal > remainingQuantity.value) {
        toast.error(`Total difference quantity cannot exceed remaining quantity (${remainingQuantity.value})`);
    }
});

function validateQuantity(index) {
    const currentTotal = totalDifferenceQuantity.value;
    const maxQuantity = originalItemQuantity.value;
    
    if (currentTotal > maxQuantity) {
        // Calculate how much we need to reduce this item's quantity by
        const excess = currentTotal - maxQuantity;
        const currentItem = selectedItemDifferences.value[index];
        const newQuantity = Math.max(1, Number(currentItem.quantity) - excess);
        
        // Update the quantity with a slight delay to allow for the input to settle
        setTimeout(() => {
            selectedItemDifferences.value[index].quantity = newQuantity;
        }, 0);
    }
};

const originalItemQuantity = computed(() => {
    if (selectedItemIndex.value === null) return 0;
    return Number(form.value.items[selectedItemIndex.value].quantity) || 0;
});

function saveDifferences() {
    if (selectedItemIndex.value === null) return;

    if (totalDifferenceQuantity.value > remainingQuantity.value) {
        toast.error(`Total difference quantity cannot exceed remaining quantity (${remainingQuantity.value})`);
        return;
    }

    if (totalDifferenceQuantity.value < remainingQuantity.value) {
        toast.error(`You must account for all remaining quantity (${remainingQuantity.value}). Current total: ${totalDifferenceQuantity.value}`);
        return;
    }

    // Validate that all differences have a status
    const hasInvalidStatus = selectedItemDifferences.value.some(diff => !diff.status);
    if (hasInvalidStatus) {
        toast.error('Please select a status for all differences');
        return;
    }

    form.value.items[selectedItemIndex.value].differences = [...selectedItemDifferences.value];
    closeDifferenceModal();
}

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

const isSubmitting = ref(false);

const checkDiffQty = (items) => {
    if (!items || !items.length) return true;
    const total = items.reduce((sum, i) => sum + (parseInt(i.quantity) || 0), 0);
    console.log(parseInt(total) > 0);
    return parseInt(total) > 0;
}

async function submitForm() {
    if (!form.value.packing_list_number) {
        toast.warning('Packing list number must be provided');
        return;
    }

    if (!form.value.pk_date) {
        toast.warning('Please select a date');
        return;
    }

    Swal.fire({
        title: "Confirm Creation",
        text: "Are you sure you want to create this Packing list?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, create it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Filter out items without product_id
            isSubmitting.value = true;
            const validItems = form.value.items.filter(item => item.product_id);
            if (validItems.length === 0) {
                toast.warning('Please add at least one item');
                return;
            }

            console.log(form.value.items);

            const formData = {
                packing_list_number: form.value.packing_list_number,
                purchase_order_id: form.value.purchase_order_id,
                pk_date: form.value.pk_date,
                items: form.value.items.filter(item => 
                    item.warehouse_id && 
                    // ((parseInt(item.received_quantity) > 0 && !item.differences?.length) || (parseInt(item.received_quantity) == 0 && checkDiffQty(item.differences))) && 
                    item.expire_date &&
                    item.batch_number &&
                    item.location
                ).map(item => ({
                    id: item.id,
                    product_id: item.product_id,
                    warehouse_id: item.warehouse_id,
                    received_quantity: item.received_quantity,
                    quantity: item.quantity,
                    batch_number: item.batch_number,
                    expire_date: item.expire_date,
                    location: item.location,
                    unit_cost: item.unit_cost,
                    total_cost: item.total_cost,
                    differences: item.differences?.length > 0 ? item.differences.map(i => ({
                        id: i.id,
                        quantity: i.quantity,
                        status: i.status,
                    })) : []
                }))
            };

            console.log(formData);

            await axios.post(route('supplies.storePK'), formData)
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
                    console.log(error.response.data);
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

const showLocationForm = ref(false);
const activeLocationDropdown = ref(null);
const locationSearch = ref('');
const filteredLocations = ref([]);
const locationForm = ref({
    'location': ''
});

// Initialize filtered locations
onMounted(() => {
    filteredLocations.value = props.locations;
});

// Close dropdown when clicking outside
// Handle clicks outside dropdown
document.addEventListener('click', (e) => {
    const dropdowns = document.querySelectorAll('.location-dropdown');
    let clickedOutside = true;
    dropdowns.forEach(dropdown => {
        if (dropdown.contains(e.target)) {
            clickedOutside = false;
        }
    });
    if (clickedOutside) {
        activeLocationDropdown.value = null;
        locationSearch.value = '';
    }
});

// Filter locations based on search
function filterLocations() {
    const query = locationSearch.value.toLowerCase();
    filteredLocations.value = props.locations.filter(l => 
        l.location.toLowerCase().includes(query)
    );
}

function selectLocation(item, location) {
    item.location = location;
    activeLocationDropdown.value = null;
    locationSearch.value = '';
}

async function handleLocationSubmit() {
    await storeLocation();
    showLocationForm.value = false;
    locationForm.value.location = ''; // Reset form
}

async function storeLocation(){
    await axios.post(route('supplies.location-store'), locationForm)
        .then((response) => {
            console.log(response.data);
            router.get(route('supplies.packing-list'), {}, {
                preserveScroll: false,
                preserveState: false,
                only: [
                    'locations'
                ]
            })
        })
        .catch((error) => {
            console.log(error.response.data);
        });
}

const isLoading = ref(false);
async function onPOChange(e) {
    isLoading.value = true;
    selectedPO.value = null;
    console.log(e.target.value);
    const id = e.target.value;
    await axios.get(route('supplies.get-purchaseOrder', id))
        .then((response) => {
            isLoading.value = false;
            selectedPO.value = response.data;
            form.value.packing_list_number = response.data?.packing_list_number;
            form.value.purchase_order_id = response.data?.id;
            console.log(response.data);
        })
        .catch((error) => {
            isLoading.value = false;
            console.log(error.response);
        })
}

watch([
    () => selectedPO.value
], () => {
    if(selectedPO.value && selectedPO.value.items){
        form.value.items = selectedPO.value.items.map(item => ({
            ...item,
            searchQuery: item.searchQuery || '',
            barcode: item.barcode || '',
            warehouse_id: item.warehouse_id || null,
            expire_date: item.expire_date || null,
            batch_number: item.batch_number || '',
            location: item.location || '',
            quantity: item.quantity || 1,
            unit_cost: item.unit_cost || 0,
            total_cost: item.total_cost || 0
        }));
    } else {
        form.value.items = [{
            product_id: null,
            searchQuery: '',
            barcode: '',
            warehouse_id: null,
            expire_date: null,
            batch_number: '',
            location: '',
            quantity: 1,
            unit_cost: 0,
            total_cost: 0
        }];
    }
})

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
/* Custom scrollbar styles */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #666;
}
</style>