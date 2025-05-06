<template>

    <Head title="Purchase Orders" />
    <AuthenticatedLayout title="Manage Your Purchase Orders"
        description="Ensuring an Optimcal Flow of Essentials Resource" img="/assets/images/supplies.png">
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <!-- Top Actions Bar -->
                <div class="flex items-center justify-between px-2 mb-6">
                    <h1>Purchase Orders</h1>
                    <div class="flex items-center gap-2">
                        <button @click="router.get(route('supplies.packing-list'))"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Receive new Supply
                        </button>
                        <button @click="router.get(route('supplies.purchase_order'))"
                            class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create new Purchase order
                        </button>
                        <div class="relative inline-block text-left z-20" ref="dropdownRef">
                            <button type="button"
                                class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 transition-colors duration-200 inline-flex items-center gap-2"
                                id="options-menu" :aria-expanded="showDropdown" aria-haspopup="true"
                                @click.stop="toggleDropdown">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                                List of Suppliers
                            </button>
                            <transition enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div v-if="showDropdown"
                                    class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                    <div class="py-1" role="none">
                                        <a @click="navigateToCreateSupplier"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                            role="menuitem">
                                            Create Supplier
                                        </a>
                                        <a href="#" @click.prevent="openSuppliersModal"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                            role="menuitem">
                                            View Suppliers
                                        </a>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-[#F7DC6F] rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Supply Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_items }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-full">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-500 rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Cost of Supplies Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_cost) }}</p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-500 rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Average Lead Time</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.avg_lead_time }}</p>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#9333EA] rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Requested Purchase Orders</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_orders }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Delete Bar -->
                <div class="flex justify-between w-full mb-4">
                    <div class="flex-1 mr-2">
                        <label for="search" class="text-sm font-medium text-gray-700">Search</label>
                        <input type="text" v-model="search" placeholder="Search by PO number, supplier or status..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="supplier" class="text-sm font-medium text-gray-700">Supplier</label>
                        <select v-model="supplier" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Filter by Supplier</option>
                            <option :value="s.id" v-for="s in props.suppliers">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="flex-1 ml-2">
                        <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                        <select v-model="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Filter by Status</option>
                            <option value="pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Purchase Orders Table with Fixed Header -->
                <div class="relative">
                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
                        <table class="min-w-full border border-black border-t-0">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs text-center font-medium text-black-500 uppercase tracking-wider border border-black">SN#</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        PO Number
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        Supplier
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        P.O Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        Total Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-black-500 uppercase tracking-wider border border-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 border-b border-black">
                                <tr v-for="(po, i) in purchaseOrders.data" :key="po.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center text-black border border-black">{{ i + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black border border-black">
                                        <Link :href="route('supplies.po-show', po.id)">
                                            {{ po.po_number }}
                                        </Link>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black border border-black">
                                        {{ po.supplier?.name || 'No supplier' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black text-right border border-black">
                                        {{ moment(po.po_date).format('LL') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-black text-right border border-black">
                                        {{formatCurrency(po.items?.reduce((sum, item) => sum + (item.total_cost || 0),
                                        0) || 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm border border-black">
                                        <span :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-green-100 text-green-800': po.status === 'completed',
                                            'bg-yellow-100 text-yellow-800': po.status === 'pending',
                                            'bg-blue-100 text-blue-800': po.status === 'processing'
                                        }">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2 border border-black">
                                        <button @click="router.visit(route('supplies.editPO', po.id))"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(po.id)" class="text-red-600 hover:text-red-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!purchaseOrders?.data?.length">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 border border-black">
                                        
                                        No purchase orders found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="purchaseOrders.meta.total > purchaseOrders.meta.per_page"
                    class="mt-4 sticky bottom-0 bg-white pt-4 border-t">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ purchaseOrders.meta.from }}</span>
                                    to
                                    <span class="font-medium">{{ purchaseOrders.meta.to }}</span>
                                    of
                                    <span class="font-medium">{{ purchaseOrders.meta.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <div class="flex gap-x-2">
                                    <Link v-for="(link, i) in purchaseOrders.meta.links" :key="i" :href="link.url"
                                        v-html="link.label" class="px-4 py-2 text-sm border rounded-md" :class="{
                                            'bg-indigo-600 text-white': link.active,
                                            'text-gray-700 hover:bg-gray-50': !link.active,
                                            'opacity-50 cursor-not-allowed': !link.url
                                        }" :disabled="!link.url" />
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Suppliers Modal -->
                <TransitionRoot appear :show="isSuppliersModalOpen" as="template">
                    <Dialog as="div" @close="closeSuppliersModal" class="relative z-50">
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
                                        class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                                        <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 flex justify-between items-center">
                                            <span>Suppliers List</span>
                                            <Link :href="route('supplies.create')"  class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                                Add New Supplier
                                        </Link>
                                        </DialogTitle>
                                        <div class="mt-4">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Name
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Contact Person
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Email
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Phone
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Status
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Actions
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="supplier in props.suppliers" :key="supplier.id">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                {{ supplier.name }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ supplier.contact_person }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ supplier.email }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ supplier.phone }}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <span
                                                                    :class="[
                                                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                                        supplier.is_active
                                                                            ? 'bg-green-100 text-green-800'
                                                                            : 'bg-red-100 text-red-800'
                                                                    ]">
                                                                    {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <Link :href="route('suppliers.edit', supplier.id)"
                                                                    class="text-indigo-600 hover:text-indigo-900">
                                                                    Edit
                                                                </Link>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex justify-end">
                                            <button type="button"
                                                class="inline-flex justify-center rounded-md border border-transparent bg-gray-100 px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2"
                                                @click="closeSuppliersModal">
                                                Close
                                            </button>
                                        </div>
                                    </DialogPanel>
                                </TransitionChild>
                            </div>
                        </div>
                    </Dialog>
                </TransitionRoot>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Swal from 'sweetalert2';
import axios from 'axios';
import Pagination from '@/Components/Pagination.vue';
import moment from 'moment';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const toast = useToast();
const dropdownRef = ref(null);
const showDropdown = ref(false);

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
            showDropdown.value = false;
        }
    });
});

const props = defineProps({
    purchaseOrders: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            meta: {
                total: 0,
                per_page: 10,
                current_page: 1
            }
        })
    },
    filters: Object,
    suppliers: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_items: 0,
            total_cost: 0,
            avg_lead_time: '0 Months',
            pending_orders: 0
        })
    },
    suppliers: {
        required: true,
        type: Array
    }
});

const search = ref(props.filters?.search || '');
const supplier = ref(props.filters?.supplier || '')
const status = ref(props.filters?.status || '')

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

function reloadPO() {
    const query = {}
    if (search.value) query.search = search.value;
    if (supplier.value) query.supplier = supplier.value;
    if (status.value) query.status = status.value;
    router.get(route('supplies.index'), query, {
        preserveScroll: false,
        preserveState: false,
        only: [
            'purchaseOrders', 'filters'
        ]
    })
}

const confirmDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        await axios.get(route('supplies.deletePO', id))
            .then((response) => {
                toast.success(response.data);
                reloadPO();
            })
            .catch((error) => {
                console.log(error);
                toast.error(error.response.data)
            });
    }
};

const stats = computed(() => props.stats || {
    total_items: 0,
    total_cost: 0,
    avg_lead_time: '0 Months',
    pending_orders: 0
});

const isSuppliersModalOpen = ref(false)

function openSuppliersModal() {
    isSuppliersModalOpen.value = true
    showDropdown.value = false
}

function closeSuppliersModal() {
    isSuppliersModalOpen.value = false
}

const navigateToCreateSupplier = () => {
    router.get(route('supplies.create'))
    showDropdown.value = false
    isSuppliersModalOpen.value = false
}

watch([
    () => search.value,
    () => supplier.value,
    () => status.value,
], () => {
    reloadPO();
});
</script>