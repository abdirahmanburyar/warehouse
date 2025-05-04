<template>

    <Head title="Purchase Orders" />
    <AuthenticatedLayout title="Manage Your Purchase Orders" description="Ensuring an Optimcal Flow of Essentials Resource"
        img="/assets/images/supplies.png">
        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="text-gray-900">
                <!-- Top Actions Bar -->
                <div class="flex items-center justify-between px-2 mb-6">
                    <h1>Purchase Orders</h1>
                    <div>
                        <Link :href="route('supplies.purchase_order')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Add new Supply
                        </Link>
                        <button @click="router.get(route('supplies.purchase_order'))"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create new Purchase order
                        </button>
                        <div class="relative inline-block text-left z-20" ref="dropdownRef">
                            <div>
                                <button type="button"
                                    class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700 transition-colors duration-200 inline-flex items-center gap-2"
                                    id="options-menu" :aria-expanded="showDropdown" aria-haspopup="true"
                                    @click.stop="toggleDropdown">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    List of Suppliers
                                </button>
                            </div>
                            <transition enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div v-show="showDropdown"
                                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                    role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                    <div class="py-1">
                                        <a href="#" @click.prevent="openCreateSupplierModal(); showDropdown = false"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                            role="menuitem">
                                            Create New Supplier
                                        </a>
                                        <a href="#" @click.prevent="showDropdown = false"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                            role="menuitem">
                                            Suppliers
                                        </a>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- Search and Delete Bar -->
                <div class="flex justify-between items-center mb-4">
                    <div class="relative">
                        <input type="text" v-model="search" @input="debouncedSearch"
                            placeholder="Search by PO number, supplier or status..."
                            class="pl-10 pr-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-80" />
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Purchase Orders Table with Fixed Header -->
                <div class="relative">
                    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        PO Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Items
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="po in purchaseOrders.data" :key="po.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ po.po_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ po.supplier?.name || 'No supplier' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ po.items?.length || 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ formatCurrency(po.items?.reduce((sum, item) => sum + (item.total_cost || 0), 0) || 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="{
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                            'bg-green-100 text-green-800': po.status === 'completed',
                                            'bg-yellow-100 text-yellow-800': po.status === 'pending',
                                            'bg-blue-100 text-blue-800': po.status === 'processing'
                                        }">
                                            {{ po.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <Link :href="route('supplies.editPO', po.id)"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            Edit
                                        </Link>
                                        <button @click="confirmDelete(po)"
                                            class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!purchaseOrders?.data?.length">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No purchase orders found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="purchaseOrders.meta.total > purchaseOrders.meta.per_page" class="mt-4 sticky bottom-0 bg-white pt-4 border-t">
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
                                    <Link
                                        v-for="(link, i) in purchaseOrders.meta.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-4 py-2 text-sm border rounded-md"
                                        :class="{
                                            'bg-indigo-600 text-white': link.active,
                                            'text-gray-700 hover:bg-gray-50': !link.active,
                                            'opacity-50 cursor-not-allowed': !link.url
                                        }"
                                        :disabled="!link.url"
                                    />
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Swal from 'sweetalert2';
import axios from 'axios';
import Pagination from '@/Components/Pagination.vue';

const toast = useToast();

const props = defineProps({
    purchaseOrders: {
        type: Object,
        required: true,
        default: () => ({
            data: [],
            meta: { links: [] }
        })
    },
    filters: Object
});

const search = ref(props.filters?.search || '');

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

const confirmDelete = async (po) => {
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
        deletePO(po);
    }
};

const deletePO = async (po) => {
    try {
        await axios.delete(route('supplier.deletePO', po.id));
        toast.success('Purchase order deleted successfully');
        router.reload();
    } catch (error) {
        toast.error(error.response?.data || 'Failed to delete purchase order');
    }
};

const debouncedSearch = () => {
    router.get(route('supplies.index'), { search: search.value }, {
        preserveState: true,
        preserveScroll: true
    });
};

watch([
    () => search.value
], () => {
    debouncedSearch();
});
</script>