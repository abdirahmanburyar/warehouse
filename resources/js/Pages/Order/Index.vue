<template>
    <AuthenticatedLayout title="Orders" description="Manage Your Orders" img="/assets/images/supplies.png">
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
                <!-- Bulk Actions Panel -->
                <div v-if="selectedItems.length > 0" class="mb-6">
                    <div class="p-4 rounded-lg bg-blue-600 shadow-lg">
                        <div class="flex items-center justify-between flex-wrap">
                            <div class="w-0 flex-1 flex items-center">
                                <span class="flex p-2 rounded-lg bg-blue-800">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                <p class="ml-3 font-medium text-white truncate">
                                    <span class="md:hidden">
                                        {{ selectedItems.length }} items selected
                                    </span>
                                    <span class="hidden md:inline">
                                        {{ selectedItems.length }} items selected for bulk action
                                    </span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                <div class="flex items-center space-x-2">
                                    <select v-model="bulkAction"
                                        class="rounded-md border-gray-300 py-2 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <option value="">Select action</option>
                                        <option v-for="action in availableBulkActions" :key="action.value"
                                            :value="action.value">
                                            {{ action.label }}
                                        </option>
                                    </select>
                                    <button @click="applyBulkAction" :disabled="!bulkAction || isSubmittingBulk"
                                        class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                        <span v-if="isSubmittingBulk" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            Processing...
                                        </span>
                                        <span v-else>Apply</span>
                                    </button>
                                    <button @click="clearSelection"
                                        class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-800 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button v-for="tab in tabs" :key="tab.id" @click="changeTab(tab.id)" :class="[
                            'flex items-center px-4 py-2 rounded-lg transition-colors',
                            activeTab === tab.id
                                ? 'bg-blue-500 text-white'
                                : 'text-gray-600 hover:bg-gray-100 bg-white',
                        ]">
                            <svg v-if="tab.icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <!-- View List Icon -->
                                <path v-if="tab.icon === 'view-list'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                <!-- Clock Icon -->
                                <path v-if="tab.icon === 'clock'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                <!-- Check Icon -->
                                <path v-if="tab.icon === 'check'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M5 13l4 4L19 7"></path>
                                <!-- Refresh Icon -->
                                <path v-if="tab.icon === 'refresh'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                                <!-- Truck Icon -->
                                <path v-if="tab.icon === 'truck'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0">
                                </path>
                                <!-- Check Circle Icon -->
                                <path v-if="tab.icon === 'check-circle'" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ tab.name }}
                            <span v-if="getStatusCount(tab.id)" class="ml-2 px-2 py-0.5 text-xs rounded-full"
                                :class="getStatusCountClass(tab.id)">
                                {{ getStatusCount(tab.id) }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-4">
                        <div v-if="isTabLoading" class="space-y-4">
                            <!-- Skeleton loader for order details -->
                            <div class="flex flex-col space-y-2">
                                <div class="h-6 bg-gray-200 rounded w-1/4 animate-pulse"></div>
                                <div class="h-5 bg-gray-200 rounded w-1/3 animate-pulse"></div>
                                <div class="h-5 bg-gray-200 rounded w-1/5 animate-pulse"></div>
                            </div>
                            <!-- Skeleton loader for table -->
                            <div class="space-y-2">
                                <div class="h-6 bg-gray-200 rounded w-1/6 animate-pulse"></div>
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-4 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-20 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-14 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-32 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                                            </th>
                                            <th class="px-4 py-2 text-left">
                                                <div class="h-4 bg-gray-200 rounded w-20 animate-pulse"></div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- 5 skeleton rows -->
                                        <tr v-for="i in 5" :key="i">
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-4 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-32 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-14 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-16 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="h-4 bg-gray-200 rounded w-20 animate-pulse"></div>
                                            </td>
                                            <td class="px-4 py-2 border-t border-b border-gray-300">
                                                <div class="flex space-x-2">
                                                    <div class="h-6 bg-gray-200 rounded w-16 animate-pulse"></div>
                                                    <div class="h-6 bg-gray-200 rounded w-16 animate-pulse"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else>
                            <div v-if="selectedOrder" class="space-y-4">
                                <div class="flex flex-col text-lg font-medium">
                                    <span>Order #{{ selectedOrder.order_number }}</span>
                                    <span class="text-gray-500">{{ selectedOrder.status }}</span>
                                    <span class="text-gray-500">{{ selectedOrder.facility?.name }}</span>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="font-medium text-gray-700">
                                        Order Items:
                                    </h3>
                                    <div v-if="filteredItems.length === 0 && !isLoadingItems"
                                        class="flex flex-col items-center justify-center py-12">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                        <p class="mt-4 text-gray-500">No items found in this status</p>
                                    </div>
                                    <table v-else class="w-full">
                                        <thead>
                                            <tr>
                                                <th class="px-4 py-2 text-left">
                                                    <input type="checkbox"
                                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        v-model="selectAll" @change="toggleSelectAll" />
                                                </th>
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
                                                <td colspan="7" class="px-4 py-2 text-center text-gray-500">
                                                    Loading items...
                                                </td>
                                            </tr>
                                            <tr v-for="item in filteredItems" :key="item.id">
                                                <td class="px-4 py-2 border-t border-b border-gray-300">
                                                    <input type="checkbox"
                                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        v-model="selectedItems" :value="item.id"
                                                        :disabled="!canSelectForBulkAction(item)" />
                                                </td>
                                                <td class="px-4 py-2 border-t border-b border-gray-300">
                                                    {{ item.product.name }}
                                                </td>
                                                <td class="px-4 py-2 border-t border-b border-gray-300">
                                                    {{ formatNumbers(item.quantity) }}
                                                </td>

                                                <td class="px-4 py-2 border-t border-b border-gray-300">
                                                    {{ formatNumbers(item.inventory_quantity) }}
                                                </td>

                                                <td class="px-4 py-2 border-t border-b border-gray-300">
                                                    <button @click.prevent="showOutstanding(item)"
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
                                                            v-if="item.status == 'pending'"
                                                            class="inline-flex items-center px-2 py-1 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            Edit
                                                        </button>

                                                        <button v-if="canChangeStatus(item, 'approved')"
                                                            @click="updateStatus(item, 'approved')"
                                                            class="px-2 py-1 text-xs font-medium text-white bg-green-500 rounded hover:bg-green-600">
                                                            Approve
                                                        </button>
                                                        <button v-if="canChangeStatus(item, 'in process')"
                                                            @click="updateStatus(item, 'in process')"
                                                            class="px-2 py-1 text-xs font-medium text-white bg-yellow-500 rounded hover:bg-yellow-600">
                                                            Process
                                                        </button>
                                                        <button v-if="canChangeStatus(item, 'dispatched')"
                                                            @click="updateStatus(item, 'dispatched')"
                                                            class="px-2 py-1 text-xs font-medium text-white bg-purple-500 rounded hover:bg-purple-600">
                                                            Dispatch
                                                        </button>
                                                        <button v-if="canChangeStatus(item, 'delivered')"
                                                            @click="updateStatus(item, 'delivered')"
                                                            class="px-2 py-1 text-xs font-medium text-white bg-gray-500 rounded hover:bg-gray-600">
                                                            Delivered
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="!isLoadingItems && items.length == 0">
                                                <td colspan="7"
                                                    class="px-4 py-2 text-center border-t border-b border-gray-300 text-muted-foreground">
                                                    No items found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else>
                                Select an order to view details
                            </div>
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
                        <svg class="w-6 h-6" fill="none" stroke="currentColor">
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
                                            <div v-for="(order, index) in outstandingOrders" :key="index"
                                                class="p-4 bg-white border border-gray-200 rounded-lg">
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
import Swal from 'sweetalert2';

const toast = useToast();

const props = defineProps({
    orders: Object,
});

const tabs = [
    { id: "all", name: "All Orders", icon: "view-list" },
    { id: "pending", name: "Pending", icon: "clock" },
    { id: "approved", name: "Approved", icon: "check" },
    { id: "in process", name: "In Process", icon: "refresh" },
    { id: "dispatched", name: "Dispatched", icon: "truck" },
    { id: "delivered", name: "Delivered", icon: "check-circle" },
];

const STORAGE_KEY = "selectedOrderId";
const activeTab = ref("all");
const selectedOrder = ref(null);
const items = ref([]);
const isLoadingItems = ref(false);
const isTabLoading = ref(false);
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

const selectedItems = ref([]);
const selectAll = ref(false);
const bulkAction = ref('');
const isSubmittingBulk = ref(false);

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
        'in process': 'bg-yellow-200 text-yellow-800',
        'dispatched': 'bg-purple-200 text-purple-800',
        'delivered': 'bg-gray-200 text-gray-800'
    };

    return statusClasses[status] || 'bg-gray-200 text-gray-800';
}

function canChangeStatus(item, newStatus) {
    const allowedTransitions = {
        'pending': ['approved'],
        'approved': ['in process'],
        'in process': ['dispatched'],
        'dispatched': ['delivered']
    };

    return allowedTransitions[item.status]?.includes(newStatus) || false;
}

async function updateStatus(item, newStatus) {
    console.log(item, newStatus);
    
    // Configure alert based on the status transition
    const statusMessages = {
        'approved': {
            title: 'Approve Order?',
            text: 'This will mark the order as approved and ready for processing.',
            confirmButtonText: 'Yes, approve it!'
        },
        'in process': {
            title: 'Start Processing?',
            text: 'This will mark the order as in processing state.',
            confirmButtonText: 'Yes, start processing!'
        },
        'dispatched': {
            title: 'Mark as Dispatched?',
            text: 'This will mark the order as dispatched from the warehouse.',
            confirmButtonText: 'Yes, dispatch it!'
        },
        'delivered': {
            title: 'Mark as Delivered?',
            text: 'This will mark the order as delivered to the destination.',
            confirmButtonText: 'Yes, mark as delivered!'
        }
    };
    
    const alertConfig = statusMessages[newStatus] || {
        title: 'Update Status?',
        text: `Change status from "${item.status}" to "${newStatus}"?`,
        confirmButtonText: 'Yes, update it!'
    };
    
    Swal.fire({
        title: alertConfig.title,
        text: alertConfig.text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: alertConfig.confirmButtonText
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Show loading indicator with progress bar
            let timerInterval;
            Swal.fire({
                title: "Updating Status...",
                html: `Changing status to <b>${newStatus}</b>`,
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
            
            try {
                const response = await axios.post(route("orders.change-status"), {
                    id: item.id,
                    status: newStatus,
                });
                
                Swal.close();
                console.log(response);
                toast.success(response.data);
                
                // Also refresh data from server
                if (selectedOrder.value) {
                    loadItems(selectedOrder.value.id);
                }
                reloadOrders();
            } catch (error) {
                Swal.close();
                console.log(error);
                toast.error(error.response?.data);
            }
        }
    });
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

// Computed property to filter items based on active tab
const filteredItems = computed(() => {
    if (activeTab.value === 'all') {
        return items.value;
    }
    return items.value.filter(item => item.status === activeTab.value);
});

// Get count of items by status for the badges
function getStatusCount(status) {
    if (status === 'all') {
        return items.value.length;
    }
    return items.value.filter(item => item.status === status).length;
}

// Get appropriate color class for status count badges
function getStatusCountClass(status) {
    const classes = {
        'all': 'bg-gray-200 text-gray-800',
        'pending': 'bg-gray-200 text-gray-800',
        'approved': 'bg-green-200 text-green-800',
        'in process': 'bg-yellow-200 text-yellow-800',
        'dispatched': 'bg-purple-200 text-purple-800',
        'delivered': 'bg-blue-200 text-blue-800'
    };

    return classes[status] || 'bg-gray-200 text-gray-800';
}

// Toggle select all items
function toggleSelectAll() {
    if (selectAll.value) {
        // Select all items that can be selected for bulk action
        selectedItems.value = filteredItems.value
            .filter(item => canSelectForBulkAction(item))
            .map(item => item.id);
    } else {
        selectedItems.value = [];
    }
}

// Clear selection
function clearSelection() {
    selectedItems.value = [];
    selectAll.value = false;
    bulkAction.value = '';
}

// Check if an item can be selected for bulk action
function canSelectForBulkAction(item) {
    // Items can be selected if they have a valid next status
    return Object.keys(allowedTransitions).includes(item.status) &&
        allowedTransitions[item.status].length > 0;
}

// Define allowed transitions (same as in the controller)
const allowedTransitions = {
    'pending': ['approved'],
    'approved': ['in process'],
    'in process': ['dispatched'],
    'dispatched': ['delivered']
};

// Get available bulk actions based on selected items
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

    // Get common next statuses
    const actions = [];

    Object.entries(statusGroups).forEach(([status, count]) => {
        if (allowedTransitions[status]) {
            allowedTransitions[status].forEach(nextStatus => {
                // Check if this action already exists
                const existingAction = actions.find(a => a.value === nextStatus);
                if (existingAction) {
                    // Update the label to include this status count
                    existingAction.label = existingAction.label.replace(
                        `(${existingAction.count})`,
                        `(${existingAction.count + count})`
                    );
                    existingAction.count += count;
                } else {
                    // Add new action
                    actions.push({
                        value: nextStatus,
                        label: `Move to ${nextStatus} (${count})`,
                        count: count
                    });
                }
            });
        }
    });

    return actions;
});

// Apply bulk action to selected items
async function applyBulkAction() {
    if (!bulkAction.value || selectedItems.value.length === 0) return;

    try {
        isSubmittingBulk.value = true;

        // Get all selected items
        const selected = filteredItems.value.filter(item => selectedItems.value.includes(item.id));

        // Group items by order
        const itemsByOrder = {};
        selected.forEach(item => {
            if (!itemsByOrder[item.order_id]) {
                itemsByOrder[item.order_id] = [];
            }
            itemsByOrder[item.order_id].push(item.id);
        });

        // Check if we need to use order-based or item-based bulk update
        if (Object.keys(itemsByOrder).length === 1 &&
            itemsByOrder[Object.keys(itemsByOrder)[0]].length ===
            items.value.filter(item => item.order_id === parseInt(Object.keys(itemsByOrder)[0])).length) {
            // All selected items belong to the same order and all items in that order are selected
            // Use order-based bulk update
            await axios.post(route("orders.bulk-change-status"), {
                order_ids: [parseInt(Object.keys(itemsByOrder)[0])],
                status: bulkAction.value
            });
        } else {
            // Use item-based bulk update
            await axios.post(route("orders.bulk-change-item-status"), {
                item_ids: selectedItems.value,
                status: bulkAction.value
            });
        }

        // Show success message
        toast.success(`Successfully updated ${selectedItems.value.length} items to ${bulkAction.value}`);

        // Clear selection and reload data
        clearSelection();
        reloadOrders();

        // If an order is selected, refresh its items
        if (selectedOrder.value) {
            loadItems(selectedOrder.value.id);
        }
    } catch (error) {
        console.error("Error applying bulk action:", error);
        toast.error("Failed to apply bulk action: " + (error.response?.data?.message || error.message));
    } finally {
        isSubmittingBulk.value = false;
    }
}

// Function to change tabs with loading state
function changeTab(tabId) {
    isTabLoading.value = true;
    activeTab.value = tabId;

    // Simulate loading delay for better UX
    setTimeout(() => {
        isTabLoading.value = false;
    }, 300);
}
</script>
