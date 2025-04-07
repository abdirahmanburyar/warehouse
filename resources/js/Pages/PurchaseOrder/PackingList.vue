<template>
    <AuthenticatedLayout>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex gap-1">
                        <a href="#" @click="router.visit(route('purchase-orders.index'))"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        </a>
                        <h1 class="text-2xl font-bold">Packing List</h1>
                    </div>
                    <div class="text-sm text-gray-500">
                        Status: <span :class="{
                            'px-2 py-1 rounded-full text-sm font-semibold': true,
                            'bg-yellow-100 text-yellow-800': props.purchase_order.status === 'pending',
                            'bg-green-100 text-green-800': props.purchase_order.status === 'completed'
                        }">{{ props.purchase_order.status }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-b pb-6">
                    <!-- Order Information -->
                    <div class="border rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Order Information</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">PO Number:</span>
                                <span class="font-medium">{{ props.purchase_order.po_number }}</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Order Date:</span>
                                <span class="font-medium">{{ formatDate(props.purchase_order.po_date) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Amount:</span>
                                <span class="font-medium">{{ formatAmount(props.purchase_order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Supplier Information -->
                    <div class="border rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Supplier Name:</span>
                                <span class="font-medium">{{ props.purchase_order.supplier.name }}</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Contact Person:</span>
                                <span class="font-medium">{{ props.purchase_order.supplier.contact_person || '-' }}</span>
                            </div>
                            <div class="flex justify-between pb-2 border-b">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-medium">{{ props.purchase_order.supplier.phone || '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">{{ props.purchase_order.supplier.email || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end space-x-3 w-full">
                    <div class="relative inline-block text-left">
                        <button @click="isOpen = !isOpen" type="button"
                            class="inline-flex w-full min-w-[300px] justify-between items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            {{ selectedText }}
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>

                        <div v-if="isOpen"
                            class="absolute right-0 z-10 mt-2 w-full min-w-[300px] origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                            style="max-height: calc(100vh - 300px); overflow-y: auto;">
                            <div class="p-2 sticky top-0 bg-white z-10 border-b">
                                <div class="relative">
                                    <input type="text" 
                                        v-model="searchQuery" 
                                        placeholder="Search packing lists..."
                                        class="w-full px-3 py-2 text-sm border rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                                </div>
                            </div>
                            <div class="py-1 sticky top-[50px] bg-white z-10 border-b">
                                <button @click="createPackingList(); isOpen = false"
                                    class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <i class="fas fa-plus mr-3"></i>
                                    Generate New Packing List
                                </button>
                            </div>
                            <div v-if="filteredPackingLists.length > 0" 
                                class="py-1 w-full">
                                <button v-for="packingList in filteredPackingLists"
                                    :key="packingList.id"
                                    @click="selectPackingList(packingList); isOpen = false"
                                    class="flex w-full flex-col px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    <div class="flex items-center w-full">
                                        <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                                        <div class="flex-1">
                                            <div class="font-medium">Packing List #{{ packingList.packing_list_number }}</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <div class="flex items-center space-x-4">
                                                    <span><i class="fas fa-calendar-alt mr-1"></i> {{ formatDate(packingList.packing_date) }}</span>
                                                    <span v-if="packingList.warehouse_name"><i class="fas fa-warehouse mr-1"></i> {{ packingList.warehouse_name }}</span>
                                                    <span v-if="packingList.location"><i class="fas fa-map-marker-alt mr-1"></i> {{ packingList.location }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span :class="['ml-2 shrink-0 px-2 py-0.5 text-xs rounded-full', 
                                            packingList.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800']">
                                            {{ packingList.status }}
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
            <!-- Product Items -->
            <form @submit.prevent="submitForm" class="">
                <div class="p-6 bg-white border-t border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-medium">Items</h2>
                        <div class="flex gap-2">
                            <button type="button" @click="openImportModal"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                Import Items
                            </button>
                            <button type="button" @click="addItem"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add Item
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 w-[400px] text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity</th>
                                    <th
                                        class="px-6 w-[400px] py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Received Quantity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unit Cost</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Cost</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <!-- Loading Skeleton -->
                                    <tr v-if="loadingItems" class="hover:bg-gray-50">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            Loading...
                                        </td>
                                    </tr>
                                    <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap">
                                            <div class="relative">
                                                <input type="text" v-model="item.product_name"
                                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                                    :class="{ 'bg-gray-50': item.product_id }" placeholder="Scan barcode"
                                                    @keydown.enter.prevent="handleBarcodeInput($event, index)"
                                                    :ref="el => { if (el) productInputs[index] = el }"
                                                    :disabled="item.product_id || !selectedPackingList" />
                                                <button v-if="item.product_id" type="button"
                                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                                    @click="clearProduct(index)" title="Remove product"
                                                    :disabled="!selectedPackingList">
                                                    ×
                                                </button>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <input type="number" v-model="item.quantity" min="1"
                                                @input="calculateItemTotal(item)"
                                                :disabled="!selectedPackingList"
                                                readonly
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <input type="number" v-model="item.received_quantity" min="0"
                                                @input="calculateItemTotal(item)"
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                            
                                            <select v-model="item.warehouse_id" 
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                                <option value="" selected>Select Warehouse</option>
                                                <option v-for="warehouse in props.warehouses" :key="warehouse.id" :value="warehouse.id">
                                                    {{ warehouse.name }}
                                                </option>
                                            </select>
                                            <div class="flex">
                                                <input type="text" v-model="item.location" placeholder="Location" 
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                            <input type="date" v-model="item.expiry_date" placeholder="Expiry Date" 
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100"> 
                                           
                                            </div>
                                           <div class="flex">
                                            <input type="text" v-model="item.batch_number" placeholder="Batch Number" 
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                            <input type="text" v-model="item.generic_name" placeholder="Generic Name" 
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                           </div>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <input type="number" v-model="item.unit_cost" min="0" step="0.01"
                                                @input="calculateItemTotal(item)"
                                                readonly
                                                :disabled="!selectedPackingList"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm disabled:bg-gray-100">
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <input type="number" v-model="item.total_cost" readonly
                                                class="mt-1 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm sm:text-sm">
                                        </td>
                                        <td class="whitespace-nowrap ">
                                            <button @click.prevent="removeItem(index)"
                                                class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700"
                                                :disabled="!selectedPackingList">
                                                <span class="sr-only">Remove</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button @click.prevent="addItem" type="button"
                                                class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700"
                                                :disabled="!selectedPackingList">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                v-if="item.product_id && parseFloat(item.received_quantity || 0) < parseFloat(item.quantity || 0)"
                                                @click.prevent="openBackOrderModal(item)"
                                                class="px-3 py-1 text-sm rounded-md flex items-center gap-1" :class="backOrderForm.isSubmitted ?
                                                    'bg-green-100 text-green-800 cursor-not-allowed' :
                                                    'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'"
                                                    :disabled="!selectedPackingList">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ backOrderForm.isSubmitted ? 'Back Ordered' : 'Back Order' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="loadingItems && items.length == 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            No items added yet. Click "Add Item" to add a new item.
                                        </td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-3 p-3">
                        <button type="submit" :disabled="processing || !selectedPackingList"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <span class="text-center">
                                {{ processing ? 'Saving...' : 'Save Changes' }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Back Order Modal -->
        <div v-if="showBackOrderModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="z-index: 50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">{{ existingBackOrders.length ? 'View' : 'Create' }} Back Order
                </h3>

                <!-- Existing Back Orders Section -->
                <div v-if="existingBackOrders.length > 0" class="mb-4">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Existing Back Orders</h4>
                    <div class="bg-gray-50 rounded p-3 space-y-2">
                        <div v-for="(orders, type) in groupBy(existingBackOrders, 'type')" :key="type"
                            class="flex justify-between">
                            <span class="capitalize">{{ type }}:</span>
                            <span class="font-medium">{{orders.reduce((sum, order) => sum + order.quantity, 0)}}
                                units</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Product:</span>
                        <span class="font-medium">{{ selectedItem?.product_name }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Ordered Quantity:</span>
                        <span class="font-medium">{{ selectedItem?.quantity }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Received Quantity:</span>
                        <span class="font-medium">{{ selectedItem?.received_quantity }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b">
                        <span class="text-gray-600">Total Remaining:</span>
                        <span class="font-medium text-indigo-600">
                            {{ selectedItem ? selectedItem.quantity - selectedItem.received_quantity : 0 }}
                        </span>
                    </div>

                    <!-- Back Order Form -->
                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Back Order Details</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th class="text-left text-sm font-medium text-gray-500">Type</th>
                                        <th class="text-right text-sm font-medium text-gray-500">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="(item, index) in backOrderForm.items" :key="index" class="border-b">
                                        <td class="py-2 capitalize">{{ item.type }}</td>
                                        <td class="py-2">
                                            <input type="number" v-model="item.quantity"
                                                class="w-full text-right border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                min="0" :max="selectedItem.quantity - selectedItem.received_quantity">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="closeBackOrderModal"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button @click="createBackOrder"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSubmitted || getTotalBackOrdered !== getRemainingQty || !selectedPackingList">
                        {{ isSubmitted ? 'Processing...' : 'Create Back Order' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Import Items Modal -->
        <ImportItems 
            :is-open="showImportModal"
            :purchase-order-id="props.purchase_order.id"
            @close="closeImportModal"
            @uploaded="reloadPo"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import ImportItems from './ImportItems.vue';

const toast = useToast();

const props = defineProps({
    purchase_order: Object,
    warehouses: Array
});

const selectedPackingList = ref(null);
const isOpen = ref(false);
const items = ref([]);

const form = ref({
    purchase_order_id: props.purchase_order.id,
    items: props.purchase_order.po_items || [
        {
            purchase_order_id: props.purchase_order.id,
            packing_list_id: selectedPackingList.value?.id,
            product_id: null,
            warehouse_id: null,
            location: '',
            expiry_date: null,
            batch_number: null,
            generic_name: null,
            product_name: '',
            quantity: 1,
            received_quantity: 0,
            unit_cost: 0,
            total_cost: 0
        }
    ]
});

// Load from local storage on mount
onMounted(() => {
    const storedPackingList = localStorage.getItem(`selected_packing_list_${props.purchase_order.id}`);
    console.log(JSON.parse(storedPackingList));
    if (storedPackingList) {
        selectedPackingList.value = JSON.parse(storedPackingList);
        reloadPo();
    }

    // Add visibility change event listener
    window.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    window.removeEventListener('visibilitychange', handleVisibilityChange);
});

const loadingItems = ref(false);

const handleVisibilityChange = () => {
    // Only clear when tab is closed, not when switching tabs
    if (document.visibilityState === 'hidden') {
        const unloadTime = Date.now();
        localStorage.setItem('unload_time', unloadTime);
    } else if (document.visibilityState === 'visible') {
        const unloadTime = localStorage.getItem('unload_time');
        const currentTime = Date.now();
        // If more than 1 second has passed, it was likely a tab close/reopen
        if (currentTime - unloadTime > 1000) {
            clearLocalStorage();
        }
    }
};

const clearLocalStorage = () => {
    localStorage.removeItem(`selected_packing_list_${props.purchase_order.id}`);
    localStorage.removeItem('unload_time');
};

const selectPackingList = (packingList) => {
    selectedPackingList.value = packingList;
    // Save to local storage
    localStorage.setItem(
        `selected_packing_list_${props.purchase_order.id}`, 
        JSON.stringify(packingList)
    );
    isOpen.value = false;
};

const selectedText = computed(() => {
    if (selectedPackingList.value) {
        return `Packing List #${selectedPackingList.value.packing_list_number}`;
    }
    return 'Select Packing List';
});

const calculateTotal = computed(() => {
    return form.value.items.reduce((total, item) => {
        return total + (parseFloat(item.unit_cost || 0) * parseFloat(item.received_quantity || 0));
    }, 0).toFixed(2);
});

const createPackingList = async () => {
    Swal.fire({
        title: 'Confirm',
        text: 'Are you sure you want to generate a packing list?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, generate',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('purchase-orders.packing-list.create'), {
                purchase_order_id: props.purchase_order.id
            })
                .then((response) => {
                    console.log(response.data);
                    selectedPackingList.value = response.data?.packing_list;
                    localStorage.setItem(
                        `selected_packing_list_${props.purchase_order.id}`, 
                        JSON.stringify(response.data?.packing_list)
                    );
                    Swal.fire({
                        title: 'Success',
                        text: response.data?.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    reloadPo();
                })
                .catch((error) => {
                    Swal.fire({
                        title: 'Error',
                        text: error.response.data,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    });
}

// Add modal state
const showBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrderForm = ref({
    items: [
        {
            type: 'damaged',
            quantity: 0,
            purchase_order_id: props.purchase_order.id,
            product_id: null
        },
        {
            type: 'missing',
            quantity: 0,
            purchase_order_id: props.purchase_order.id,
            product_id: null
        }
    ],
    isSubmitted: false
});
const isSubmitted = ref(false);
const existingBackOrders = ref([]);

// Back order modal functions
const fetchExistingBackOrders = async (item) => {
    try {
        const response = await axios.get(route('purchase-orders.back-orders.get', {
            purchaseOrder: props.purchase_order.id,
            productId: item.product_id
        }));
        existingBackOrders.value = response.data;
        // If there are existing back orders, set isSubmitted to true
        backOrderForm.value.isSubmitted = existingBackOrders.value.length > 0;
    } catch (error) {
        console.error('Error fetching back orders:', error);
    }
};

const openBackOrderModal = async (item) => {
    selectedItem.value = item;
    await fetchExistingBackOrders(item);
    
    // Calculate total remaining quantity
    const remainingQty = item.quantity - item.received_quantity;

    if (existingBackOrders.value.length > 0) {
        // Group existing back orders by type
        const groupedBackOrders = existingBackOrders.value.reduce((acc, order) => {
            if (!acc[order.type]) {
                acc[order.type] = 0;
            }
            acc[order.type] += order.quantity;
            return acc;
        }, {});

        // Initialize form with existing quantities
        backOrderForm.value = {
            items: [
                {
                    type: 'damaged',
                    quantity: groupedBackOrders['damaged'] || 0,
                    purchase_order_id: props.purchase_order.id,
                    product_id: selectedItem.value.product_id
                },
                {
                    type: 'missing',
                    quantity: remainingQty - (groupedBackOrders['damaged'] || 0),
                    purchase_order_id: props.purchase_order.id,
                    product_id: selectedItem.value.product_id
                }
            ],
            isSubmitted: true
        };
    } else {
        backOrderForm.value = {
            items: [
                {
                    type: 'damaged',
                    quantity: 0,
                    purchase_order_id: props.purchase_order.id,
                    product_id: selectedItem.value.product_id
                },
                {
                    type: 'missing',
                    quantity: remainingQty,
                    purchase_order_id: props.purchase_order.id,
                    product_id: selectedItem.value.product_id
                }
            ],
            isSubmitted: false
        };
    }
    showBackOrderModal.value = true;
};

watch(() => backOrderForm.value?.items?.[0]?.quantity, (newDamagedQty) => {
    if (!selectedItem.value || !backOrderForm.value?.items?.[1]) return;
    
    const remainingQty = selectedItem.value.quantity - selectedItem.value.received_quantity;
    // Ensure we're working with numbers and clamp damaged quantity
    const damaged = Math.min(Math.max(Number(newDamagedQty) || 0, 0), remainingQty);
    if (backOrderForm.value.items[0]) {
        backOrderForm.value.items[0].quantity = damaged;
    }
    // Update missing quantity
    backOrderForm.value.items[1].quantity = remainingQty - damaged;
}, { immediate: true });

const closeBackOrderModal = () => {
    showBackOrderModal.value = false;
    selectedItem.value = null;
    backOrderForm.value = {
        items: [
            {
                type: 'damaged',
                quantity: 0,
                purchase_order_id: props.purchase_order.id,
                product_id: null
            },
            {
                type: 'missing',
                quantity: 0,
                purchase_order_id: props.purchase_order.id,
                product_id: null
            }
        ],
        isSubmitted: false
    };
    existingBackOrders.value = [];
};

const getRemainingBackOrderQty = computed(() => {
    if (!selectedItem.value) return 0;
    const totalBackOrdered = backOrderForm.value.items.reduce((sum, item) => sum + Number(item.quantity), 0);
    return selectedItem.value.quantity - selectedItem.value.received_quantity - totalBackOrdered;
});

const createBackOrder = async () => {
    try {
        isSubmitted.value = true;
        const response = await axios.post(route('purchase-orders.back-orders.create', props.purchase_order.id), {
            items: backOrderForm.value.items.map(item => ({
                ...item,
                id: existingBackOrders.value.find(bo => bo.type === item.type)?.id,
                notes: item.notes || null
            }))
        });

        // Get quantities for display
        const damagedQty = backOrderForm.value.items.find(item => item.type === 'damaged')?.quantity || 0;
        const missingQty = backOrderForm.value.items.find(item => item.type === 'missing')?.quantity || 0;

        Swal.fire({
            title: 'Success!',
            html: `Back orders updated:<br>
                  Damaged: ${damagedQty} units<br>
                  Missing: ${missingQty} units`,
            icon: 'success',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });

        closeBackOrderModal();
        reloadPo();
    } catch (error) {
        console.error('Error creating back order:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Something went wrong',
            icon: 'error',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } finally {
        isSubmitted.value = false;
    }
};

function addItem() {
    if (!selectedPackingList.value) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a packing list first',
            icon: 'error',
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
        return;
    }
    form.value.items.push({
        packing_list_id: selectedPackingList.value?.id,
        purchase_order_id: props.purchase_order.id,
        product_id: null,
        warehouse_id: null,
        location: '',
        expiry_date: null,
        batch_number: null,
        generic_name: null,
        product_name: '',
        quantity: 1,
        received_quantity: 0,
        unit_cost: 0,
        total_cost: 0
    });
}

function removeItem(index) {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatAmount = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(Number(amount));
};

const isLoading = ref(false);
const productInputs = ref({});

const handleBarcodeInput = (event, index) => {
    event.preventDefault(); // Prevent form submission
    const item = form.value.items[index];

    // If this row already has a product, ignore the scan
    if (item.product_id) {
        // Clear the input value since it's readonly
        event.target.value = item.product_name;
        return;
    }

    const barcode = event.target.value.trim();
    if (!barcode) return;

    isLoading.value = true;
    axios.post(route('products.search'), {
        search: barcode
    })
        .then(response => {
            const product = response.data;
            if (!product) {
                Swal.fire({
                    icon: 'error',
                    title: 'Product not found',
                    text: 'The product with this barcode was not found',
                    showConfirmButton: false,
                    timer: 2000,
                });
                item.product_id = null;
                item.product_name = '';
                return;
            }

            // Check for different possible property names
            const productId = product.product_id;
            const productName = product.product_name;

            const isExist = form.value.items.some(item => item.product_id === productId);
            if (isExist) {
                Swal.fire({
                    icon: 'error',
                    title: 'Product already exists',
                    text: 'This product has already been added to the list',
                    showConfirmButton: false,
                    timer: 2000,
                });
                item.product_id = null;
                item.product_name = '';
                return;
            }

            // Update the item with product details
            item.product_id = productId;
            item.product_name = productName;
            item.unit_cost = product.unit_cost || product.price || 0;
            calculateItemTotal(item);

            console.log(item);

            // After successful scan, add a new row and focus it
            addItem();
            // Focus needs to happen after Vue updates the DOM
            nextTick(() => {
                const newIndex = form.value.items.length - 1;
                productInputs.value[newIndex]?.focus();
            });
        })
        .catch(error => {
            console.error('Barcode scan error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data || 'An error occurred while scanning the barcode',
                showConfirmButton: false,
                timer: 2000,
            });
            item.product_id = null;
            item.product_name = '';
        })
        .finally(() => {
            isLoading.value = false;
        });
};

const processing = ref(false);

const submitForm = async () => {
    try {
        processing.value = true;

        // Filter out empty items before submission
        const validItems = form.value.items.filter(item => item.product_id);

        // Ensure all items have the current packing list ID
        validItems.forEach(item => {
            item.packing_list_id = selectedPackingList.value.id;
        });

        console.log(validItems);

        const response = await axios.post(route('purchase-orders.packing-list.store'), {
            purchase_order_id: form.value.purchase_order_id,
            items: validItems,
            total_cost: calculateTotal.value,
            packing_list_id: selectedPackingList.value.id
        });

        toast.success('Packing list updated successfully');
        reloadPo();
    } catch (error) {
        console.error('Error submitting form:', error);
        toast.error(error.response?.data || 'Something went wrong');
    } finally {
        processing.value = false;
    }
};

// Calculate item total
const calculateItemTotal = (item) => {
    // Ensure received quantity doesn't exceed quantity
    if (parseFloat(item.received_quantity) > parseFloat(item.quantity)) {
        item.received_quantity = item.quantity;
    }
    item.total_cost = parseFloat(item.unit_cost || 0) * parseFloat(item.received_quantity || 0);
};

const clearProduct = (index) => {
    const item = form.value.items[index];
    item.packing_list_id = selectedPackingList.value?.id;
    item.purchase_order_id = props.purchase_order.id;
    item.product_id = null;
    item.warehouse_id = null;
    item.location = '';
    item.expiry_date = null;
    item.batch_number = null;
    item.generic_name = null;
    item.product_name = '';
    item.quantity = 1;
    item.received_quantity = 0;
    item.unit_cost = 0;
    item.total_cost = 0;
    calculateItemTotal(item);
    // Focus the input after clearing
    nextTick(() => {
        productInputs.value[index]?.focus();
    });
};

function reloadPo() {
    router.reload({
        only: ['purchase_order']
    });
}

function groupBy(arr, key) {
    return arr.reduce((result, value) => {
        (result[value[key]] = result[value[key]] || []).push(value);
        return result;
    }, {});
}

const getTotalBackOrdered = computed(() => {
    return backOrderForm.value.items.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
});

const getRemainingQty = computed(() => {
    if (!selectedItem.value) return 0;
    return selectedItem.value.quantity - selectedItem.value.received_quantity;
});

const searchQuery = ref('');

const filteredPackingLists = computed(() => {
    if (!props.purchase_order.packing_lists) return [];
    if (!searchQuery.value) return props.purchase_order.packing_lists;
    
    const query = searchQuery.value.toLowerCase();
    return props.purchase_order.packing_lists.filter(list => {
        return list.packing_list_number.toLowerCase().includes(query) ||
            (list.warehouse_name && list.warehouse_name.toLowerCase().includes(query)) ||
            (list.location && list.location.toLowerCase().includes(query)) ||
            list.status.toLowerCase().includes(query) ||
            formatDate(list.packing_date).toLowerCase().includes(query);
    });
});

const showImportModal = ref(false);

const openImportModal = () => {
    showImportModal.value = true;
};

const closeImportModal = () => {
    showImportModal.value = false;
};

const reloadItems = () => {
    router.reload({ only: ['items'] });
};
</script>

<style scoped>
@media print {
    .bg-white {
        background-color: white !important;
    }

    button,
    .no-print {
        display: none !important;
    }
}
</style>
