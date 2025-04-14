<template>
    <AuthenticatedLayout title="Orders" description="Manage Your Orders" img="/assets/images/order.png">
        <div class="flex">
            <!-- Left Sidebar -->
            <div class="w-64 min-h-screen bg-white shadow-lg">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Order Numbers</h3>
                    <nav class="space-y-2">
                        <button 
                            v-for="order in props.orders.data" 
                            :key="order.id"
                            @click="loadItems(order.id)"
                            :class="[
                                'w-full px-4 py-2 text-left rounded-lg transition-colors',
                                selectedOrder?.id === order.id 
                                    ? 'bg-blue-500 text-white' 
                                    : 'text-gray-600 hover:bg-gray-100'
                            ]"
                        >
                            {{ order.order_number }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-6">
                <div class="mb-6">
                    <div class="flex space-x-2">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                'px-4 py-2 rounded-lg transition-colors',
                                activeTab === tab.id 
                                    ? 'bg-blue-500 text-white' 
                                    : 'text-gray-600 hover:bg-gray-100 bg-white'
                            ]"
                        >
                            {{ tab.name }}
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-4">
                        <div v-if="selectedOrder" class="space-y-4">
                            <div class="text-lg font-medium flex flex-col">
                                <span>Order #{{ selectedOrder.order_number }}</span>
                                <span class="text-gray-500">{{ selectedOrder.status }}</span>
                                <span class="text-gray-500">{{ selectedOrder.facility?.name }}</span>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-medium text-gray-700">Order Items:</h3>
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left">Product</th>
                                            <th class="px-4 py-2 text-left">Quantity</th>
                                            <th class="px-4 py-2 text-left">Outstanding Orders</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="isLoadingItems">
                                            <td colspan="3" class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="flex items-center justify-center">
                                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-gray-900">
                                                        <span class="sr-only">Loading...</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-for="item in items" :key="item.id">
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                {{ item.product.name }}
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <button 
                                                    @click.prevent="showOutstanding(item)"
                                                    class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 hover:underline focus:outline-none"
                                                >
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="!isLoadingItems && items.length == 0">
                                            <td colspan="3" class="px-4 py-2 border-t text-center text-muted-foreground border-b border-gray-300">
                                                No items found
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500">
                            Select an order to view details
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide Over Panel for Outstanding Orders -->
        <div v-if="showOutstandingPanel" 
            class="fixed inset-0 overflow-hidden z-50"
            @click.self="closeOutstandingPanel">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                    <div class="relative w-96">
                        <div class="h-full flex flex-col bg-white shadow-xl">
                            <!-- Header -->
                            <div class="px-4 py-6 bg-gray-50 sm:px-6">
                                <div class="flex items-start justify-between space-x-3">
                                    <div class="space-y-1">
                                        <h2 class="text-lg font-medium text-gray-900">
                                            Outstanding Orders
                                        </h2>
                                        <p class="text-sm text-gray-500" v-if="selectedProduct">
                                            {{ selectedProduct.name }}
                                        </p>
                                    </div>
                                    <div class="h-7 flex items-center">
                                        <button
                                            @click="closeOutstandingPanel"
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            <span class="sr-only">Close panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto">
                                <div class="p-4">
                                    <div v-if="isLoadingOutstanding" class="flex justify-center py-4">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                                    </div>
                                    <div v-else>
                                        <div v-if="outstandingOrders.length > 0" class="space-y-4">
                                            <div v-for="(order, index) in outstandingOrders" 
                                                :key="index"
                                                class="bg-white rounded-lg border border-gray-200 p-4"
                                            >
                                                <div class="space-y-2">
                                                    <div class="flex justify-between">
                                                        <span class="font-medium">{{ order.facility }}</span>
                                                        <span class="text-gray-500">{{ order.order_type }}</span>
                                                    </div>
                                                    <div class="text-lg font-semibold text-blue-600">
                                                        Quantity: {{ order.quantity }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-4 text-gray-500">
                                            No outstanding orders found
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    orders: Object
});

const tabs = [
    { id: 'all', name: 'All Orders' },
    { id: 'approved', name: 'Approved' },
    { id: 'in-process', name: 'In Process' },
    { id: 'dispatch', name: 'Dispatch' },
    { id: 'delivery', name: 'Delivery' }
];

const STORAGE_KEY = 'selectedOrderId';
const activeTab = ref('all');
const selectedOrder = ref(null);
const items = ref([]);
const isLoadingItems = ref(false);
const isLoadingOutstanding = ref(false);
const showOutstandingPanel = ref(false);
const selectedProduct = ref(null);
const outstandingOrders = ref([]);

async function loadItems(orderId){
    const order = props.orders.data.find(o => o.id === orderId);
    selectedOrder.value = order;
    localStorage.setItem(STORAGE_KEY, orderId);
    
    items.value = [];
    isLoadingItems.value = true;

    await axios.get(route('orders.items', orderId))    
    .then((response) => {
        items.value = response.data;
        isLoadingItems.value = false;
    })
    .catch((error) => {
        items.value = [];
        isLoadingItems.value = false;
        console.error(error);
    });
}

function closeOutstandingPanel() {
    showOutstandingPanel.value = false;
    selectedProduct.value = null;
}

async function showOutstanding(item) {
    selectedProduct.value = item.product;
    outstandingOrders.value = [];
    isLoadingOutstanding.value = true;
    showOutstandingPanel.value = true;

    try {
        const response = await axios.get(route('orders.outstanding', item.product_id));
        outstandingOrders.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoadingOutstanding.value = false;
    }
}

function reloadOrders() {
    router.get(route('orders.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['orders']
    })
}

onMounted(() => {
    const savedOrderId = localStorage.getItem(STORAGE_KEY);
    if (savedOrderId && props.orders.data) {
        loadItems(parseInt(savedOrderId));
    }

    // Initialize Echo listener for OrderEvent
    window.Echo.channel('orders')
        .listen('.order-received', (e) => {
            console.log('Order event received:', e);
            reloadOrders();  
            // If an order is selected, refresh its items
            if (selectedOrder.value) {
                loadItems(selectedOrder.value.id);
            }
        });
});

onBeforeUnmount(() => {
    // Clean up Echo listener when component is destroyed
    window.Echo.leaveChannel('orders');
});
</script>
