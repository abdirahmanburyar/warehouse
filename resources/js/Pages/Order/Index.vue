<template>
    <AuthenticatedLayout title="Orders" description="Manage Your Orders" img="/assets/images/supplies.png">
        <div class="flex h-[calc(100vh-64px)]">
            <!-- Left Sidebar -->
            <div class="w-80 bg-white shadow-lg border-r border-gray-200 overflow-y-auto">
                <div class="p-2">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Orders</h3>
                        <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                            {{ filteredOrders.length }} Total
                        </span>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="relative mb-6">
                        <input type="text" v-model="search" placeholder="Search orders..."
                            class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Order List -->
                    <div class="space-y-3">
                        <div v-for="order in filteredOrders" :key="order.id" 
                            @click="loadItems(order.id)"
                            class="group cursor-pointer">
                            <div :class="[
                                'rounded-lg transition-all duration-200',
                                selectedOrder?.id === order.id
                                    ? 'bg-blue-50 border-blue-500 shadow-sm'
                                    : 'hover:bg-gray-50 border-transparent',
                                'border'
                            ]">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ order.order_number }}</h4>
                                        <p class="mt-1 text-sm text-gray-500">{{ order.facility?.name }}</p>
                                    </div>
                                    <span :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        getStatusClass(order.status)
                                    ]">
                                        {{ order.status }}
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDate(order.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 bg-gray-50 overflow-y-auto">
                <!-- Bulk Actions Panel -->
                <div v-if="selectedItems.length > 0" 
                    class="sticky top-0 z-10 bg-blue-600 shadow-lg transition-all duration-300">
                    <div class="w-full mx-auto">
                        <div class="flex items-center justify-between flex-wrap p-2">
                            <div class="flex-1 flex items-center">
                                <span class="flex p-2 rounded-lg bg-blue-800">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <p class="ml-3 font-medium text-white truncate">
                                    {{ selectedItems.length }} items selected
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <select v-model="bulkAction"
                                    class="rounded-lg border-0 bg-blue-700 text-white py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-white">
                                    <option value="">Select action</option>
                                    <option v-for="action in availableBulkActions" :key="action.value"
                                        :value="action.value">
                                        {{ action.label }}
                                    </option>
                                </select>
                                <button @click="applyBulkAction" :disabled="!bulkAction || isSubmittingBulk"
                                    class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-blue-600 text-sm font-medium hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-white disabled:opacity-50">
                                    {{ isSubmittingBulk ? 'Applying...' : 'Apply' }}
                                </button>
                                <button @click="clearSelection"
                                    class="inline-flex items-center p-2 rounded-lg bg-blue-700 text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    <!-- Status Tabs -->
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-2">
                            <button v-for="tab in tabs" :key="tab.id" @click="changeTab(tab.id)" :class="[
                                'inline-flex items-center px-4 py-2 rounded-lg transition-colors text-sm font-medium',
                                activeTab === tab.id
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-white',
                            ]">
                                <svg v-if="tab.icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="tab.icon === 'list'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                    <path v-if="tab.icon === 'refresh'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    <path v-if="tab.icon === 'truck'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                {{ tab.name }}
                                <span v-if="tab.id !== 'all'" class="ml-2 px-2 py-0.5 text-xs rounded-full"
                                    :class="getStatusCountClass(tab.id)">
                                    {{ items.filter(item => item.status === tab.id).length }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div v-if="selectedOrder" class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold text-gray-900">
                                    Order Details - {{ selectedOrder.order_number }}
                                </h2>
                                <span :class="[
                                    'px-3 py-1 text-sm rounded-full',
                                    getStatusClass(selectedOrder.status)
                                ]">
                                    {{ selectedOrder.status }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="isLoadingItems" class="flex justify-center items-center p-8">
                                <div class="w-full h-8 border-b-2 border-blue-600">Loading...</div>
                            </div>
                            <div v-else>
                                <table class="w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="relative w-full px-6 ">
                                                <input type="checkbox" v-model="selectAll"
                                                    class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Product
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Inventory (SOH)
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Outstanding
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in filteredItems" :key="item.id" :class="{'bg-blue-50': selectedItems.includes(item.id)}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="checkbox" v-model="selectedItems" :value="item.id"
                                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ item.product?.name }}</div>
                                                <div class="text-sm text-gray-500">SKU: {{ item.product?.sku }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ formatNumbers(item.inventory_quantity) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ formatNumbers(item.quantity) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button @click="viewOutstanding(item)" 
                                                    class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                    View
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    statusClasses[item.status]
                                                ]">
                                                    {{ item.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex space-x-2">
                                                    <button v-if="item.status === 'pending'" @click.prevent="editOrderItem(item)"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        Edit
                                                    </button>
                                                    <button v-for="action in getAvailableActions(item)" :key="action.value"
                                                        @click="updateStatus(item.id, action.value)"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ action.label }}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div v-else class="bg-white rounded-lg shadow-sm p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No Order Selected</h3>
                        <p class="mt-1 text-sm text-gray-500">Select an order from the list to view its details.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Order Item Modal -->
        <Modal :show="showOrderItemModal" @close="showOrderItemModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Order Item
                </h2>

                <div class="mt-6">
                    <div v-if="editingItem.product" class="mb-4">
                        <h3 class="font-medium text-gray-700">{{ editingItem.product.name }}</h3>
                        <p class="text-sm text-gray-500">SKU: {{ editingItem.product.sku }}</p>
                    </div>

                    <div class="mt-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            v-model="editingItem.quantity"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :disabled="isSubmitting"
                        />
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click="showOrderItemModal = false"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click="saveOrderItem"
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Outstanding Quantities Slideover -->
        <TransitionRoot as="template" :show="showOutstandingModal">
            <Dialog as="div" class="relative z-10" @close="showOutstandingModal = false">
                <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0" enter-to="opacity-100"
                    leave="ease-in-out duration-500" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700"
                                enter-from="translate-x-full" enter-to="translate-x-0"
                                leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0"
                                leave-to="translate-x-full">
                                <DialogPanel class="pointer-events-auto relative w-96">
                                    <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                                        <div class="px-4 sm:px-6">
                                            <div class="flex items-start justify-between">
                                                <DialogTitle class="text-base font-semibold leading-6 text-gray-900">
                                                    Outstanding Quantities
                                                </DialogTitle>
                                                <div class="ml-3 flex h-7 items-center">
                                                    <button type="button"
                                                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                        @click="showOutstandingModal = false">
                                                        <span class="sr-only">Close panel</span>
                                                        <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                            <!-- Product Info -->
                                            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                                <h4 class="text-sm font-medium text-gray-900 mb-2">Product Details</h4>
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <p><strong>Name:</strong> {{ selectedItemOutstanding?.product?.name }}</p>
                                                    <p><strong>SKU:</strong> {{ selectedItemOutstanding?.product?.sku }}</p>
                                                    <p><strong>Ordered:</strong> {{ formatNumbers(selectedItemOutstanding?.quantity) }}</p>
                                                </div>
                                            </div>

                                            <!-- Outstanding List -->
                                            <div class="space-y-4">
                                                <h4 class="text-sm font-medium text-gray-900">Outstanding by Facility</h4>
                                                <div v-for="(qty, index) in outstandingQuantities" :key="index"
                                                    class="bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ qty.facility }}
                                                            </p>
                                                            <p class="text-sm text-gray-500 mt-1">
                                                                {{ qty.order_type }}
                                                            </p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ formatNumbers(qty.quantity) }}
                                                            </p>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                Outstanding
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div v-if="outstandingQuantities.length === 0" 
                                                    class="text-center text-gray-500 bg-gray-50 rounded-lg py-8">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                    </svg>
                                                    <p class="mt-2 text-sm font-medium">No Outstanding Quantities</p>
                                                    <p class="text-xs text-gray-500">All quantities have been fulfilled</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Modal from '@/Components/Modal.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
// TransitionRoot
import { TransitionRoot, Dialog, DialogPanel, DialogTitle, TransitionChild } from '@headlessui/vue';

const toast = useToast();

const props = defineProps({
    orders: Object,
    filters: Object,
    errors: Object,
    auth: Object,
    warehouse: Object
});

// State
const search = ref('');
const selectedOrder = ref(null);
const items = ref([]);
const selectedItems = ref([]);
const bulkAction = ref('');
const isSubmittingBulk = ref(false);
const isLoadingItems = ref(false);
const showOrderItemModal = ref(false);
const editingItem = ref({
    id: null,
    quantity: 0,
    product: null,
    order_id: null
});
const isSubmitting = ref(false);
const activeTab = ref('all');
const showOutstandingModal = ref(false);
const selectedItemOutstanding = ref(null);
const outstandingQuantities = ref([]);

// Constants
const STORAGE_KEY = 'selectedOrderId';

// Status classes
const statusClasses = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-blue-100 text-blue-800',
    'in process': 'bg-purple-100 text-purple-800',
    'dispatched': 'bg-indigo-100 text-indigo-800',
    'delivered': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800'
};

// Action button labels
const actionLabels = {
    'approved': 'Approve',
    'in process': 'Process',
    'dispatched': 'Dispatch',
    'delivered': 'Deliver'
};

// Tabs configuration
const tabs = [
    { id: 'all', name: 'All Orders', icon: 'list' },
    { id: 'pending', name: 'Pending', icon: 'clock' },
    { id: 'approved', name: 'Approved', icon: 'check' },
    { id: 'in process', name: 'In Process', icon: 'refresh' },
    { id: 'dispatched', name: 'Dispatched', icon: 'truck' },
    { id: 'delivered', name: 'Delivered', icon: 'check-circle' }
];

// Allowed status transitions
const allowedTransitions = {
    'pending': ['approved'],
    'approved': ['in process'],
    'in process': ['dispatched'],
    'dispatched': ['delivered']
};

// Status messages for actions
const statusMessages = {
    'approved': {
        title: 'Approve Order?',
        text: 'This will mark the order as approved.',
        confirmButtonText: 'Yes, Approve it!'
    },
    'in process': {
        title: 'Start Processing?',
        text: 'This will mark the order as in process.',
        confirmButtonText: 'Yes, start processing!'
    },
    'dispatched': {
        title: 'Dispatch Order?',
        text: 'This will mark the order as dispatched.',
        confirmButtonText: 'Yes, dispatch it!'
    },
    'delivered': {
        title: 'Mark as Delivered?',
        text: 'This will mark the order as delivered.',
        confirmButtonText: 'Yes, mark as delivered!'
    }
};

// Bulk status messages
const bulkStatusMessages = {
    'approved': {
        title: 'Approve Orders?',
        text: 'This will mark the selected items as approved.',
        confirmButtonText: 'Yes, Approve them!'
    },
    'in process': {
        title: 'Start Processing?',
        text: 'This will mark the selected items as in process.',
        confirmButtonText: 'Yes, start processing!'
    },
    'dispatched': {
        title: 'Dispatch Orders?',
        text: 'This will mark the selected items as dispatched.',
        confirmButtonText: 'Yes, dispatch them!'
    },
    'delivered': {
        title: 'Mark as Delivered?',
        text: 'This will mark the selected items as delivered.',
        confirmButtonText: 'Yes, mark as delivered!'
    }
};

// Computed
const filteredOrders = computed(() => {
    if (!props.orders.data) return [];
    
    return props.orders.data.filter(order => {
        const searchLower = search.value.toLowerCase();
        return order.order_number.toLowerCase().includes(searchLower) ||
               order.facility?.name.toLowerCase().includes(searchLower);
    });
});

const filteredItems = computed(() => {
    if (!items.value) return [];
    
    if (activeTab.value === 'all') {
        return items.value;
    }

    return items.value.filter(item => item.status === activeTab.value);
});

const selectAll = computed({
    get: () => {
        return filteredItems.value.length > 0 && selectedItems.value.length === filteredItems.value.length;
    },
    set: (value) => {
        if (value) {
            selectedItems.value = filteredItems.value.map(item => item.id);
        } else {
            selectedItems.value = [];
        }
    }
});

const availableBulkActions = computed(() => {
    if (selectedItems.value.length === 0) return [];

    // Get all selected items
    const selected = filteredItems.value.filter(item => selectedItems.value.includes(item.id));

    // Group by status
    const statusGroups = {};
    selected.forEach(item => {
        if (!statusGroups[item.status]) {
            statusGroups[item.status] = 0;
        }
        statusGroups[item.status]++;
    });

    // Get common next statuses based on allowed transitions
    const actions = [];
    Object.entries(statusGroups).forEach(([status, count]) => {
        if (allowedTransitions[status]) {
            allowedTransitions[status].forEach(nextStatus => {
                const existingAction = actions.find(a => a.value === nextStatus);
                if (existingAction) {
                    existingAction.count += count;
                    existingAction.label = `${actionLabels[nextStatus]} (${existingAction.count})`;
                } else {
                    actions.push({
                        value: nextStatus,
                        label: `${actionLabels[nextStatus]} (${count})`,
                        count: count
                    });
                }
            });
        }
    });

    return actions;
});

// Methods
function getStatusClass(status) {
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
}

function getStatusCountClass(status) {
    return status === activeTab.value ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500';
}

function getStatusCount(status) {
    if (status === 'all') {
        return items.value.length;
    }
    return items.value.filter(item => item.status === status).length;
}

function formatNumbers(n) {
    return Number(n).toLocaleString();
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getAvailableActions(item) {
    return (allowedTransitions[item.status] || []).map(status => ({
        value: status,
        label: actionLabels[status]
    }));
}

async function loadItems(orderId) {
    const order = filteredOrders.value.find((o) => o.id === orderId);
    selectedOrder.value = order;
    localStorage.setItem(STORAGE_KEY, orderId);

    items.value = [];
    isLoadingItems.value = true;

    try {
        const response = await axios.get(route("orders.items", orderId));
        items.value = response.data;
    } catch (error) {
        console.error(error);
        toast.error("Failed to load order items");
    } finally {
        isLoadingItems.value = false;
    }
}

function clearSelection() {
    selectedItems.value = [];
    bulkAction.value = '';
}

function editOrderItem(item) {
    editingItem.value = { 
        id: item.id,
        quantity: item.quantity,
        product: item.product,
        order_id: item.order_id
    };
    showOrderItemModal.value = true;
}

async function saveOrderItem() {
    if (!editingItem.value || !editingItem.value.id) return;

    try {
        isSubmitting.value = true;
        await axios.post(route("orders.update-item"), {
            id: editingItem.value.id,
            quantity: editingItem.value.quantity
        });

        toast.success("Order item updated successfully");
        showOrderItemModal.value = false;
        
        if (selectedOrder.value) {
            loadItems(selectedOrder.value.id);
        }
    } catch (error) {
        toast.error(error.response?.data || "Failed to update order item");
    } finally {
        isSubmitting.value = false;
        editingItem.value = {
            id: null,
            quantity: 0,
            product: null,
            order_id: null
        };
    }
}

async function updateStatus(id, newStatus) {
    const message = statusMessages[newStatus];
    
    Swal.fire({
        title: message.title,
        text: message.text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: message.confirmButtonText,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                const response = await axios.post(route("orders.change-item-status"), {
                    item_id: id,
                    status: newStatus
                });
                Swal.close();
                toast.success(response.data);
                reloadOrders();
            } catch (error) {
                Swal.close();
                toast.error(error.response?.data);
            }
        }
    });
}

async function applyBulkAction() {
    if (!bulkAction.value || selectedItems.value.length === 0) return;

    const message = bulkStatusMessages[bulkAction.value];
    
    Swal.fire({
        title: message.title,
        text: message.text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: message.confirmButtonText,
        showLoaderOnConfirm: true,
        preConfirm: async () => {
            try {
                isSubmittingBulk.value = true;
                const selected = filteredItems.value.filter(item => selectedItems.value.includes(item.id));
                
                // Group items by order
                const itemsByOrder = {};
                selected.forEach(item => {
                    if (!itemsByOrder[item.order_id]) {
                        itemsByOrder[item.order_id] = [];
                    }
                    itemsByOrder[item.order_id].push(item.id);
                });

                // Check if all items in an order are selected
                const orderBasedUpdate = Object.entries(itemsByOrder).length === 1 && 
                    itemsByOrder[Object.keys(itemsByOrder)[0]].length === 
                    items.value.filter(item => item.order_id === parseInt(Object.keys(itemsByOrder)[0])).length;

                let response;
                // Use item-based update
                response = await axios.post(route("orders.bulk-change-item-status"), {
                    item_ids: selectedItems.value,
                    status: bulkAction.value
                });

                Swal.close();
                toast.success(response.data);
                clearSelection();
                reloadOrders();

                if (selectedOrder.value) {
                    loadItems(selectedOrder.value.id);
                }
            } catch (error) {
                console.log(error.response);
                Swal.close();
                toast.error(error.response?.data);
            } finally {
                isSubmittingBulk.value = false;
            }
        }
    });
}

async function viewOutstanding(item) {
    selectedItemOutstanding.value = item;
    showOutstandingModal.value = true;
    
    try {
        const response = await axios.get(route('orders.outstanding', { id: item.product_id }));
        outstandingQuantities.value = response.data;
    } catch (error) {
        toast.error('Failed to load outstanding quantities');
        console.error(error);
    }
}

function reloadOrders() {
    router.get(
        route("orders.index"),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            only: ["orders"],
        }
    );
}

function changeTab(tabId) {
    activeTab.value = tabId;
}

// Lifecycle hooks
onMounted(() => {
    const savedOrderId = localStorage.getItem(STORAGE_KEY);
    if (savedOrderId && props.orders.data) {
        loadItems(parseInt(savedOrderId));
    }

    // Initialize Echo listener for OrderEvent
    window.Echo.channel("orders").listen(".order-received", (e) => {
        reloadOrders();
        if (selectedOrder.value) {
            loadItems(selectedOrder.value.id);
        }
    });
});

onBeforeUnmount(() => {
    window.Echo.leaveChannel("orders");
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
