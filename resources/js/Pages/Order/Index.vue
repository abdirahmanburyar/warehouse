<template>
    <AuthenticatedLayout title="Orders" description="Manage Your Orders" img="/assets/images/order.png">
        <div class="flex">
            <!-- Left Sidebar -->
            <div class="w-64 min-h-screen bg-white shadow-lg">
                <div class="p-4">
                    <h3 class="mb-4 text-lg font-semibold text-gray-700">
                        Order Numbers
                    </h3>
                    <nav class="space-y-2">
                        <button v-for="order in props.orders.data" :key="order.id" @click="loadItems(order.id)" :class="[
                            'w-full px-4 py-2 text-left rounded-lg transition-colors',
                            selectedOrder?.id === order.id
                                ? 'bg-blue-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]">
                            {{ order.order_number }}
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 p-6">
                <div class="mb-6">
                    <div class="flex space-x-2">
                        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id" :class="[
                            'px-4 py-2 rounded-lg transition-colors',
                            activeTab === tab.id
                                ? 'bg-blue-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100 bg-white',
                        ]">
                            {{ tab.name }}
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-4">
                        <div v-if="selectedOrder" class="space-y-4">
                            <div class="flex flex-col text-lg font-medium">
                                <span>Order #{{
                                    selectedOrder.order_number
                                }}</span>
                                <span class="text-gray-500">{{
                                    selectedOrder.status
                                    }}</span>
                                <span class="text-gray-500">{{
                                    selectedOrder.facility?.name
                                    }}</span>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-medium text-gray-700">
                                    Order Items:
                                </h3>
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left">
                                                Product
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Quantity
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                In hand
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Outstanding Orders
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Status
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="isLoadingItems">
                                            <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                                                Loading items...
                                            </td>
                                        </tr>
                                        <tr v-for="item in items" :key="item.id">
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                {{ item.product.name }}
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                {{
                                                    formatNumbers(item.quantity)
                                                }}
                                            </td>

                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                {{
                                                    formatNumbers(
                                                        item.inventory_quantity
                                                    )
                                                }}
                                            </td>

                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <button @click.prevent="
                                                    showOutstanding(item)
                                                    "
                                                    class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800 hover:underline focus:outline-none">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View
                                                </button>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <span :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full',
                                                    getStatusClass(item.status),
                                                ]">
                                                    {{ item.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="flex space-x-2">
                                                    <button @click.prevent="editOrderItem(item)"
                                                        class="inline-flex items-center px-2 py-1 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit
                                                    </button>

                                                    <button v-if="
                                                        canChangeStatus(
                                                            item,
                                                            'approved'
                                                        )
                                                    " @click="
                                                            updateStatus(
                                                                item,
                                                                'approved'
                                                            )
                                                            "
                                                        class="px-2 py-1 text-xs font-medium text-white bg-green-500 rounded hover:bg-green-600">
                                                        Approve
                                                    </button>
                                                    <button v-if="
                                                        canChangeStatus(
                                                            item,
                                                            'rejected'
                                                        )
                                                    " @click="
                                                            updateStatus(
                                                                item,
                                                                'rejected'
                                                            )
                                                            "
                                                        class="px-2 py-1 text-xs font-medium text-white bg-red-500 rounded hover:bg-red-600">
                                                        Reject
                                                    </button>
                                                    <button v-if="
                                                        canChangeStatus(
                                                            item,
                                                            'in processing'
                                                        )
                                                    " @click="
                                                            updateStatus(
                                                                item,
                                                                'in processing'
                                                            )
                                                            "
                                                        class="px-2 py-1 text-xs font-medium text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                                        Process
                                                    </button>
                                                    <button v-if="
                                                        canChangeStatus(
                                                            item,
                                                            'dispatched'
                                                        )
                                                    " @click="
                                                            updateStatus(
                                                                item,
                                                                'dispatched'
                                                            )
                                                            "
                                                        class="px-2 py-1 text-xs font-medium text-white bg-purple-500 rounded hover:bg-purple-600">
                                                        Dispatch
                                                    </button>
                                                    <button v-if="
                                                        canChangeStatus(
                                                            item,
                                                            'delivered'
                                                        )
                                                    " @click="
                                                            updateStatus(
                                                                item,
                                                                'delivered'
                                                            )
                                                            "
                                                        class="px-2 py-1 text-xs font-medium text-white bg-gray-500 rounded hover:bg-gray-600">
                                                        Deliver
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="
                                            !isLoadingItems &&
                                            items.length == 0
                                        ">
                                            <td colspan="6"
                                                class="px-4 py-2 text-center border-t border-b border-gray-300 text-muted-foreground">
                                                No items found
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else class="py-4 text-center text-gray-500">
                            Select an order to view details
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Edit modal -->
        <div v-if="showOrderItemModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-x-hidden overflow-y-auto outline-none focus:outline-none">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="relative w-full max-w-md p-6 mx-auto my-8 bg-white rounded-lg shadow-xl">
                <div class="flex items-start justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Edit Order Item
                    </h3>
                    <button class="text-gray-400 hover:text-gray-500" @click="showOrderItemModal = false">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateOrderItem">
                    <div class="mb-4">
                        <label for="quantity" class="block text-sm font-medium text-gray-700">
                            Quantity
                        </label>
                        <input id="quantity" v-model="form.quantity" type="number" min="1"
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required />
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            @click="showOrderItemModal = false" :disabled="isSubmitting">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="flex items-center">
                                <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Updating...
                            </span>
                            <span v-else>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Slide Over Panel for Outstanding Orders -->
        <div v-if="showOutstandingPanel" class="fixed inset-0 z-50 overflow-hidden" @click.self="closeOutstandingPanel">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="relative w-96">
                        <div class="flex flex-col h-full bg-white shadow-xl">
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
                                    <div class="flex items-center h-7">
                                        <button @click="closeOutstandingPanel"
                                            class="text-gray-400 hover:text-gray-500">
                                            <span class="sr-only">Close panel</span>
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 overflow-y-auto">
                                <div class="p-4">
                                    <div v-if="isLoadingOutstanding" class="flex justify-center py-4">
                                        <div class="w-8 h-8 border-b-2 border-gray-900 rounded-full animate-spin"></div>
                                    </div>
                                    <div v-else>
                                        <div v-if="outstandingOrders.length > 0" class="space-y-4">
                                            <div v-for="(
order, index
                                                ) in outstandingOrders" :key="index"
                                                class="p-4 bg-white border border-gray-200 rounded-lg">
                                                <div class="space-y-2">
                                                    <div class="flex justify-between">
                                                        <span class="font-medium">{{
                                                            order.facility
                                                        }}</span>
                                                        <span class="text-gray-500">{{
                                                            order.order_type
                                                        }}</span>
                                                    </div>
                                                    <div class="text-lg font-semibold text-blue-600">
                                                        Quantity:
                                                        {{ order.quantity }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="py-4 text-center text-gray-500">
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
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    orders: Object,
});

const tabs = [
    { id: "all", name: "All Orders" },
    { id: "pending", name: "Pending" },
    { id: "approved", name: "Approved" },
    { id: "rejected", name: "Rejected" },
    { id: "in-processing", name: "In Processing" },
    { id: "dispatched", name: "Dispatched" },
    { id: "delivered", name: "Delivered" },
];

const STORAGE_KEY = "selectedOrderId";
const activeTab = ref("all");
const selectedOrder = ref(null);
const items = ref([]);
const isLoadingItems = ref(false);
const isLoadingOutstanding = ref(false);
const showOutstandingPanel = ref(false);
const selectedProduct = ref(null);
const outstandingOrders = ref([]);
const showOrderItemModal = ref(false);
const form = ref({
    id: null,
    quantity: null,
})
const isSubmitting = ref(false);

async function loadItems(orderId) {
    const order = props.orders.data.find((o) => o.id === orderId);
    selectedOrder.value = order;
    localStorage.setItem(STORAGE_KEY, orderId);

    items.value = [];
    isLoadingItems.value = true;

    await axios
        .get(route("orders.items", orderId))
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

function editOrderItem(item) {
    form.value.id = item.id;
    form.value.quantity = item.quantity;

    showOrderItemModal.value = true;
}

async function showOutstanding(item) {
    selectedProduct.value = item.product;
    outstandingOrders.value = [];
    isLoadingOutstanding.value = true;
    showOutstandingPanel.value = true;

    try {
        const response = await axios.get(
            route("orders.outstanding", item.product_id)
        );
        outstandingOrders.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoadingOutstanding.value = false;
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

onMounted(() => {
    const savedOrderId = localStorage.getItem(STORAGE_KEY);
    if (savedOrderId && props.orders.data) {
        loadItems(parseInt(savedOrderId));
    }

    // Initialize Echo listener for OrderEvent
    window.Echo.channel("orders").listen(".order-received", (e) => {
        console.log("Order event received:", e);
        reloadOrders();
        // If an order is selected, refresh its items
        if (selectedOrder.value) {
            loadItems(selectedOrder.value.id);
        }
    });
});

onBeforeUnmount(() => {
    // Clean up Echo listener when component is destroyed
    window.Echo.leaveChannel("orders");
});

// format numbers
function formatNumbers(n) {
    return Number(n).toLocaleString();
}

function getStatusClass(status) {
    const statusClasses = {
        'pending': 'bg-gray-200 text-gray-800',
        'approved': 'bg-green-200 text-green-800',
        'rejected': 'bg-red-200 text-red-800',
        'in processing': 'bg-yellow-200 text-yellow-800',
        'dispatched': 'bg-purple-200 text-purple-800',
        'delivered': 'bg-gray-200 text-gray-800'
    };

    return statusClasses[status] || 'bg-gray-200 text-gray-800';
}

function canChangeStatus(item, newStatus) {
    const allowedTransitions = {
        'pending': ['approved', 'rejected'],
        'approved': ['in processing'],
        'in processing': ['dispatched'],
        'dispatched': ['delivered']
    };

    return allowedTransitions[item.status]?.includes(newStatus) || false;
}

async function updateStatus(item, newStatus) {
    try {
        const response = await axios.post(route("orders.change-status"), {
            id: item.order_id,
            status: newStatus,
        });

        // Update the item status locally
        item.status = newStatus;

        // Reload the orders list to reflect the changes
        reloadOrders();
    } catch (error) {
        console.error("Error updating status:", error);
        alert("Failed to update status: " + (error.response?.data || error.message));
    }
}

async function updateOrderItem() {
    isSubmitting.value = true;
    await axios.post(route('orders.update-item'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            toast.success(response.data);
            showOrderItemModal.value = false;
            reloadOrders();
        })
        .catch((error) => {
            console.log(error);
            isSubmitting.value = false;
            toast.error(error.response?.data);
        });
}
</script>
