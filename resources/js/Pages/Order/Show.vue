<template>
    <AuthenticatedLayout title="Orders Management" description="Track and manage all facility orders"
        img="/assets/images/tracking.png">
        <!-- Order Header -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-3">
                        Order #{{ props.order.order_number }}
                    </h1>
                    <div class="mt-2 text-sm text-gray-600">
                        <p>Facility: {{ props.order.facility?.name }}</p>
                        <p>Order Type: {{ props.order.order_type }}</p>
                        <p>Order Date: {{ formatDate(props.order.order_date) }}</p>
                        <p>Expected by {{ formatDate(props.order.expected_date) }}</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span :class="[
                        'px-3 py-1 text-sm rounded-full',
                        getStatusClass(props.order.status)
                    ]">
                        {{ props.order.status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Outstanding Items Slide Over -->
        <TransitionRoot as="template" :show="loadOutstanding">
            <Dialog as="div" class="relative z-[10000]">
                <TransitionChild
                    as="template"
                    enter="ease-in-out duration-500"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in-out duration-500"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                            <TransitionChild
                                as="template"
                                enter="transform transition ease-in-out duration-500 sm:duration-700"
                                enter-from="translate-x-full"
                                enter-to="translate-x-0"
                                leave="transform transition ease-in-out duration-500 sm:duration-700"
                                leave-from="translate-x-0"
                                leave-to="translate-x-full"
                            >
                                <DialogPanel class="pointer-events-auto w-screen max-w-md">
                                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                        <div class="px-4 py-6 sm:px-6">
                                            <div class="flex items-start justify-between">
                                                <DialogTitle class="text-base font-semibold leading-6 text-gray-900">
                                                    Outstanding Items
                                                </DialogTitle>
                                                <div class="ml-3 flex h-7 items-center">
                                                    <button
                                                        type="button"
                                                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                                        @click="closeOutstanding()"
                                                    >
                                                        <span class="sr-only">Close panel</span>
                                                        <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative mt-6 flex-1 px-4 sm:px-6">
                                            <div v-if="outstandingItems.length === 0" class="text-center py-10">
                                                <p class="text-gray-500">No outstanding items found.</p>
                                            </div>
                                            <div v-else class="space-y-4">
                                                <div v-for="item in outstandingItems" :key="item.id" class="bg-white border rounded-lg p-4">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <h3 class="text-sm font-medium text-gray-900">{{ item.product }}</h3>
                                                            <p class="text-sm text-gray-500">{{ item.facility }}</p>
                                                            <div class="flex justify-between">
                                                                <div>
                                                                    <h3 class="text-sm font-medium text-gray-900">Quantity</h3>
                                                                    <p class="text-sm text-gray-500">{{ item.quantity }}</p>
                                                                </div>
                                                                <div>
                                                                    <h3 class="text-sm font-medium text-gray-900">Status</h3>
                                                                    <span :class="[
                                                                        'px-2 py-1 text-xs rounded-full',
                                                                        getStatusClass(item.status)
                                                                    ]">
                                                                        {{ item.status }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
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

        <!-- Warehouse Selection Modal -->
        <TransitionRoot appear :show="isWarehouseModalOpen" as="template">
            <Dialog as="div" @close="closeWarehouseModal" class="relative z-10">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0"
                    enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                    Select Warehouse for Dispatch
                                </DialogTitle>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700">Warehouse</label>
                                    <select v-model="selectedWarehouse"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="">Select a warehouse</option>
                                        <option v-for="warehouse in warehouses" :key="warehouse.id"
                                            :value="warehouse.id">
                                            {{ warehouse.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <button type="button"
                                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        @click="closeWarehouseModal">
                                        Cancel
                                    </button>
                                    <button type="button"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        @click="handleWarehouseSelection">
                                        Proceed
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Tabs and Items Table -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <button v-for="tab in tabs" :key="tab.key" @click="handleStatusClick(tab.status)" :class="[
                        'px-6 py-3 text-sm font-medium',
                        selectedStatus === tab.key
                            ? 'border-b-2 border-blue-500 text-blue-600'
                            : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]">
                        {{ tab.label }}
                        <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs">
                            {{ getItemCountByStatus(tab.status) }}
                        </span>
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-4 flex-1">
                        <input type="text" v-model="search" placeholder="Search items..."
                            class="px-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <!-- Bulk Actions -->
                    <div v-if="selectedItems.length > 0" class="flex items-center gap-2 ml-4">
                        <span class="text-sm text-gray-600">{{ selectedItems.length }} selected</span>
                        <Menu as="div" class="relative inline-block text-left">
                            <MenuButton :disabled="isSubmitting"
                                class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                :class="[
                                    isSubmitting
                                        ? 'bg-gray-400 cursor-not-allowed'
                                        : 'bg-gray-600 hover:bg-gray-700'
                                ]">
                                <span class="flex items-center">
                                    <span v-if="isSubmitting" class="mr-2">Processing...</span>
                                    <span v-else>Bulk Actions</span>
                                    <ChevronDownIcon class="w-5 h-5 ml-2 -mr-1" aria-hidden="true" />
                                </span>
                            </MenuButton>
                            <transition enter-active-class="transition duration-100 ease-out"
                                enter-from-class="transform scale-95 opacity-0"
                                enter-to-class="transform scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="transform scale-100 opacity-100"
                                leave-to-class="transform scale-95 opacity-0">
                                <MenuItems v-if="!isSubmitting"
                                    class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-10">
                                    <div class="py-1">
                                        <MenuItem v-if="actionCounts.pending > 0" v-slot="{ active }">
                                        <button @click="handleBulkAction('approved', 'Approve')" :class="[
                                            active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                            'group flex items-center justify-between w-full px-4 py-2 text-sm'
                                        ]" :disabled="isSubmitting">
                                            <div class="flex items-center">
                                                <CheckCircleIcon class="mr-3 h-5 w-5 text-blue-500"
                                                    aria-hidden="true" />
                                                <span>Approve Items</span>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ actionCounts.pending }}
                                            </span>
                                        </button>
                                        </MenuItem>
                                        <MenuItem v-if="actionCounts.approved > 0" v-slot="{ active }">
                                        <button @click="handleBulkAction('in process', 'Process')" :class="[
                                            active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                            'group flex items-center justify-between w-full px-4 py-2 text-sm'
                                        ]" :disabled="isSubmitting">
                                            <div class="flex items-center">
                                                <ClockIcon class="mr-3 h-5 w-5 text-indigo-500" aria-hidden="true" />
                                                <span>Process Items</span>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ actionCounts.approved }}
                                            </span>
                                        </button>
                                        </MenuItem>
                                        <MenuItem v-if="actionCounts.in_process > 0" v-slot="{ active }">
                                        <button @click="handleBulkAction('dispatched', 'Dispatch')" :class="[
                                            active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                            'group flex items-center justify-between w-full px-4 py-2 text-sm'
                                        ]" :disabled="isSubmitting">
                                            <div class="flex items-center">
                                                <TruckIcon class="mr-3 h-5 w-5 text-purple-500" aria-hidden="true" />
                                                <span>Dispatch Items</span>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ actionCounts.in_process }}
                                            </span>
                                        </button>
                                        </MenuItem>
                                        <MenuItem v-if="actionCounts.dispatched > 0" v-slot="{ active }">
                                        <button @click="handleBulkAction('delivered', 'Deliver')" :class="[
                                            active ? 'bg-gray-100 text-gray-900' : 'text-gray-700',
                                            'group flex items-center justify-between w-full px-4 py-2 text-sm'
                                        ]" :disabled="isSubmitting">
                                            <div class="flex items-center">
                                                <CheckBadgeIcon class="mr-3 h-5 w-5 text-green-500"
                                                    aria-hidden="true" />
                                                <span>Deliver Items</span>
                                            </div>
                                            <span
                                                class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ actionCounts.dispatched }}
                                            </span>
                                        </button>
                                        </MenuItem>
                                        <div v-if="Object.values(actionCounts).every(count => count === 0)"
                                            class="px-4 py-2 text-sm text-gray-500">
                                            No actions available for selected items
                                        </div>
                                    </div>
                                </MenuItems>
                            </transition>
                        </Menu>
                        <div v-if="isSubmitting" class="flex items-center">
                            <svg class="animate-spin h-5 w-5 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-sm text-gray-500">Processing...</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" :checked="isAllSelected"
                                        @change="toggleSelectAll($event.target.checked)"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    QOO</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Warehouse</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Outstanding Quantity</th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in filteredItems" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input type="checkbox" v-model="selectedItems" :value="item.id"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                </td>
                                <td class="px-6 py-4">{{ item.product.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ item.quantity_on_order }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ item.warehouse?.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500"><a href="#"
                                        @click="outstanding(item.id)">View</a></td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        getStatusClass(item.status)
                                    ]">
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex gap-2">
                                        <button
                                            class="px-3 py-1 text-xs text-white bg-gray-600 hover:bg-gray-700 rounded"
                                            @click="openEditModal(item)" v-if="item.status === 'pending'">
                                            Edit
                                        </button>
                                        <button v-for="action in getAvailableActions(item)" :key="action.action"
                                            @click="handleItemAction(item.id, action.action, action.label)" 
                                            :class="[
                                                'px-3 py-1 rounded-md text-white text-xs flex items-center gap-1',
                                                action.class
                                            ]"
                                            :disabled="loadingStatus[item.id]">
                                            <svg v-if="loadingStatus[item.id]" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
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

        <!-- Edit Quantity Modal -->
        <TransitionRoot appear :show="isEditModalOpen" as="template">
            <Dialog as="div" @close="closeEditModal" class="relative z-[10000]">
                <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0"
                    enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4 text-center">
                        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100" leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
                            <DialogPanel
                                class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                                    Edit Item Quantity
                                </DialogTitle>

                                <div class="mt-4">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700">Product</label>
                                        <div class="mt-1 text-sm text-gray-900">{{ selectedItem?.product?.name }}</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="quantity"
                                            class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" id="quantity" v-model="editQuantity" min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                                        <p v-if="quantityError" class="mt-1 text-sm text-red-600">{{ quantityError }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button"
                                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        @click="closeEditModal">
                                        Cancel
                                    </button>
                                    <button type="button"
                                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        @click="updateItemQuantity" :disabled="isSubmitting">
                                        <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import {
    ChevronDownIcon,
    CheckCircleIcon,
    ClockIcon,
    TruckIcon,
    CheckBadgeIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    order: Object,
    warehouses: Array
});

const selectedStatus = ref('pending');
const search = ref('');
const selectedItems = ref([]);
const selectedWarehouse = ref('');
const isWarehouseModalOpen = ref(false);
const pendingDispatchAction = ref(null);
const isSubmitting = ref(false);
const bulkActionInProgress = ref(null);
const loadingStatus = ref({});

const tabs = [
    { key: 'pending', label: 'Pending', status: 'pending' },
    { key: 'approved', label: 'Approved', status: 'approved' },
    { key: 'in process', label: 'In Process', status: 'in process' },
    { key: 'dispatched', label: 'Dispatched', status: 'dispatched' },
    { key: 'delivered', label: 'Delivered', status: 'delivered' }
];

const loadOutstanding = ref(false);
const outstandingItems = ref([]);
function closeOutstanding() {
    loadOutstanding.value = false;
    outstandingItems.value = [];
}

async function outstanding(id) {
    loadOutstanding.value = true;
    outstandingItems.value = [];
    await axios.get(route('orders.outstanding', id))
        .then((response) => {
            outstandingItems.value = response.data;
        })
        .catch((error) => {
            console.log(error);
            toast.error(error.response.data);
        })
}

const filteredItems = computed(() => {
    let items = props.order.items;

    // Filter by status
    items = items.filter(item => item.status === selectedStatus.value);

    // Filter by search
    if (search.value) {
        const searchTerm = search.value.toLowerCase();
        items = items.filter(item =>
            item.product.name.toLowerCase().includes(searchTerm) ||
            item.product.sku.toLowerCase().includes(searchTerm)
        );
    }

    return items;
});

const getItemCountByStatus = (status) => {
    return props.order.items.filter(item => item.status === status).length;
};

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        'in process': 'bg-indigo-100 text-indigo-800',
        dispatched: 'bg-purple-100 text-purple-800',
        delivered: 'bg-green-100 text-green-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getAvailableActions = (item) => {
    const actions = [];
    switch (item.status) {
        case 'pending':
            actions.push({ action: 'approved', label: 'Approve', class: 'bg-blue-600 hover:bg-blue-700' });
            break;
        case 'approved':
            actions.push({ action: 'in process', label: 'Process', class: 'bg-indigo-600 hover:bg-indigo-700' });
            break;
        case 'in process':
            actions.push({ action: 'dispatched', label: 'Dispatch', class: 'bg-purple-600 hover:bg-purple-700' });
            break;
        case 'dispatched':
            actions.push({ action: 'delivered', label: 'Deliver', class: 'bg-green-600 hover:bg-green-700' });
            break;
    }
    return actions;
};

async function handleItemAction(id, action, label) {
    loadingStatus.value[id] = true;
    try {
        if (action === 'dispatched') {
            const warehouseOptions = {};
            props.warehouses.forEach(warehouse => {
                warehouseOptions[warehouse.id] = warehouse.name;
            });

            const result = await Swal.fire({
                title: "Select Warehouse for Dispatch",
                input: "select",
                inputOptions: warehouseOptions,
                inputPlaceholder: 'Select a warehouse',
                showCancelButton: true,
                confirmButtonText: "Proceed with Dispatch",
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to select a warehouse!';
                    }
                }
            });

            if (result.isConfirmed) {
                await axios.post(route('orders.change-item-status'), {
                    item_id: id,
                    status: action,
                    warehouse_id: result.value
                });
                reloadItems();
                toast.success(`Item successfully dispatched`);
            }
        } else {
            const result = await Swal.fire({
                title: `Are you sure to ${label}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update'
            });

            if (result.isConfirmed) {
                await axios.post(route('orders.change-item-status'), {
                    item_id: id,
                    status: action
                });
                reloadItems();
                toast.success(`Status changed to ${label}`);
            }
        }
    } catch (error) {
        console.log(error);
        Swal.fire(error.response?.data);
    } finally {
        loadingStatus.value[id] = false;
    }
}

function reloadItems() {
    const query = {}
    router.get(route('orders.show', props.order.id), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['order']
    })
}

const handleStatusClick = (status) => {
    selectedItems.value = [];
    if (status !== selectedStatus.value) {
        selectedStatus.value = status;
        // selectedItems will be reset by the watcher
    }
};

const isAllSelected = ref(false);

const toggleSelectAll = (checked) => {
    if (checked) {
        selectedItems.value = filteredItems.value.map(item => item.id);
    } else {
        selectedItems.value = [];
    }
    isAllSelected.value = checked;
};

const toggleSelectItem = (itemId) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index === -1) {
        selectedItems.value.push(itemId);
    } else {
        selectedItems.value.splice(index, 1);
    }
    // Update selectAll based on whether all filtered items are selected
    isAllSelected.value = filteredItems.value.every(item => selectedItems.value.includes(item.id));
};

const actionCounts = computed(() => {
    const selectedItemsData = props.order.items.filter(item => selectedItems.value.includes(item.id));
    return {
        pending: selectedItemsData.filter(item => item.status === 'pending').length,
        approved: selectedItemsData.filter(item => item.status === 'approved').length,
        in_process: selectedItemsData.filter(item => item.status === 'in process').length,
        dispatched: selectedItemsData.filter(item => item.status === 'dispatched').length,
        delivered: selectedItemsData.filter(item => item.status === 'delivered').length
    };
});


const handleBulkAction = async (status, label) => {
    const count = status === 'approved' ? actionCounts.value.pending :
        status === 'in process' ? actionCounts.value.approved :
            status === 'dispatched' ? actionCounts.value.in_process :
                status === 'delivered' ? actionCounts.value.dispatched : 0;

    if (count === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No Items to Update',
            text: `No items are available to be changed to ${status} status.`
        });
        return;
    }

    let result;
    if (status === 'dispatched') {
        result = await Swal.fire({
            title: `${label} ${count} Items?`,
            html: `
                <div class="mb-4">This will change ${count} items to ${status} status</div>
                <select id="swal-warehouse" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select warehouse</option>
                    ${props.warehouses.map(w => `<option value="${w.id}">${w.name}</option>`).join('')}
                </select>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed',
            preConfirm: () => {
                const warehouseId = document.getElementById('swal-warehouse').value;
                if (!warehouseId) {
                    Swal.showValidationMessage('Please select a warehouse');
                    return false;
                }
                return warehouseId;
            }
        });

        if (!result.isConfirmed || !result.value) return;
    } else {
        result = await Swal.fire({
            title: `${label} ${count} Items?`,
            text: `This will change ${count} items to ${status} status`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed'
        });

        if (!result.isConfirmed) return;
    }

    isSubmitting.value = true;
    bulkActionInProgress.value = status;
    try {
        const data = {
            item_ids: selectedItems.value.filter(id => {
                const item = props.order.items.find(i => i.id === id);
                return (status === 'approved' && item.status === 'pending') ||
                    (status === 'in process' && item.status === 'approved') ||
                    (status === 'dispatched' && item.status === 'in process') ||
                    (status === 'delivered' && item.status === 'dispatched');
            }),
            status
        };

        if (status === 'dispatched') {
            data.warehouse_id = result.value;
        }

        await axios.post(route('orders.bulk-change-item-status'), data);

        reloadItems();
        selectedItems.value = [];
        toast.success('Items updated successfully');
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: error.response?.data || 'Failed to update items'
        });
    } finally {
        isSubmitting.value = false;
        bulkActionInProgress.value = null;
    }
};

const isEditModalOpen = ref(false);
const selectedItem = ref(null);
const editQuantity = ref(0);
const quantityError = ref('');

const openEditModal = (item) => {
    selectedItem.value = item;
    editQuantity.value = item.quantity;
    quantityError.value = '';
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedItem.value = null;
    editQuantity.value = 0;
    quantityError.value = '';
};

const updateItemQuantity = async () => {
    if (!editQuantity.value || editQuantity.value < 1) {
        quantityError.value = 'Quantity must be at least 1';
        return;
    }

    isSubmitting.value = true;
    try {
        await axios.post(route('orders.update-item'), {
            id: selectedItem.value.id,
            quantity: editQuantity.value
        });

        reloadItems();
        closeEditModal();
        Swal.fire({
            icon: 'success',
            title: 'Quantity updated successfully',
            showConfirmButton: false,
            timer: 1500
        });
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: error.response?.data || 'Failed to update quantity'
        });
    } finally {
        isSubmitting.value = false;
    }
};

const openWarehouseModal = () => {
    isWarehouseModalOpen.value = true;
};

const closeWarehouseModal = () => {
    isWarehouseModalOpen.value = false;
    selectedWarehouse.value = '';
};

const handleWarehouseSelection = async () => {
    if (!selectedWarehouse.value) {
        Swal.fire({
            icon: 'error',
            title: 'Please select a warehouse'
        });
        return;
    }

    try {
        await axios.post(route('orders.dispatch'), {
            warehouse_id: selectedWarehouse.value
        });

        reloadItems();
        closeWarehouseModal();
        Swal.fire({
            icon: 'success',
            title: 'Order dispatched successfully',
            showConfirmButton: false,
            timer: 1500
        });
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: error.response?.data || 'Failed to dispatch order'
        });
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const statusFlow = ['pending', 'approved', 'in process', 'dispatched', 'delivered'];

onMounted(() => {
    // Initialize Echo listener for OrderEvent
    window.Echo.channel("orders").listen(".order-received", (e) => {
        // reload();
        console.log(e);
    });
});

</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>