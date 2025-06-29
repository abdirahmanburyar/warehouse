<template>
    <Head title="Suppliers List" />
    <AuthenticatedLayout title="Suppliers List" description="Suppliers List" img="/assets/images/orders.png">
        <!-- Back Button -->
        <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </Link>

        <div class="mb-[60px]">
            <div class="mb-5">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-sm font-semibold text-gray-900">Suppliers</h5>
                    <Link :href="route('supplies.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New Supplier
                    </Link>
                </div>
                <div class="flex space-x-4">
                    <div class="w-[300px]">
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Search suppliers..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                    <div class="w-[150px]">
                        <select 
                            v-model="status" 
                            class="w-[150px] px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <select name="per_page" v-model="per_page" @change="props.filters.page = 1" id="per_page" class="w-[150px] rounded-3xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <table class="min-w-full border border-black divide-y divide-black">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Contact Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-black bg-gray-50">Actions</th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="supplier in props.suppliers.data" :key="supplier.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap border border-black">
                            <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                            <div class="text-sm text-gray-500">Created: {{ formatDate(supplier.created_at) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border border-black text-sm text-gray-500">
                            {{ supplier.contact_person }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border border-black">
                            <div class="text-sm text-gray-900">{{ supplier.email }}</div>
                            <div class="text-sm text-gray-500">{{ supplier.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border border-black text-sm text-gray-500">
                            {{ supplier.address || 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border border-black">
                            <span :class="[supplier.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                               {{ supplier.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap border border-black text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <Link :href="route('supplies.suppliers.edit', supplier.id)" class="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>                                    
                                </Link>
                                <button
                                    @click="confirmToggleStatus(supplier)"
                                    :class="{
                                        'opacity-50 cursor-wait': loadingSuppliers.has(supplier.id),
                                        'bg-gray-200': supplier.status !== 'Active',
                                        'bg-green-500': supplier.status === 'Active'
                                    }"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    :disabled="loadingSuppliers.has(supplier.id)"
                                >
                                    <span
                                        :class="{
                                            'translate-x-5': supplier.status === 'Active',
                                            'translate-x-0': supplier.status !== 'Active',
                                            'bg-gray-400 animate-pulse': loadingSuppliers.has(supplier.id)
                                        }"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="flex justify-end mt-3">
                <TailwindPagination :data="props.suppliers" @pagination-change-page="getResult" :limit="2" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, watch } from 'vue';
import moment from 'moment';
import Swal from 'sweetalert2';
import { TailwindPagination } from 'laravel-vue-pagination';
import axios from 'axios';

const props = defineProps({
    suppliers: {
        required: true,
        type: Object
    },
    filters: Object
});
const search = ref(props.filters.search);
const per_page = ref(props.filters.per_page || 25);
const status = ref(props.filters.status || 'all');

const loadingSuppliers = ref(new Set());

watch([
    () => search.value,
    () => per_page.value,
    () => status.value,
    () => props.filters.page
], () => {
    reloadSupplier();
});

function reloadSupplier() {
    const query = {};
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (status.value) query.status = status.value;
    if (props.filters.page) query.page = props.filters.page;
    router.get(route('supplies.show'), query, { preserveState: true, preserveScroll: true, only: ['suppliers'] });
}

function getResult(page = 1) {
    props.filters.page = page;
}

const confirmToggleStatus = async (supplier) => {
    if (loadingSuppliers.value.has(supplier.id)) return;

    const result = await Swal.fire({
        title: `${supplier.status === 'Active' ? 'Deactivate' : 'Activate'} Supplier`,
        text: `Are you sure you want to ${supplier.status === 'Active' ? 'deactivate' : 'activate'} ${supplier.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    });

    if (result.isConfirmed) {
        try {
            loadingSuppliers.value.add(supplier.id);
            await axios.post(route('suppliers.toggle-status', supplier.id));
            supplier.status = supplier.status === 'Active' ? 'Inactive' : 'Active';
            Swal.fire({
                title: 'Success',
                text: `Supplier has been ${supplier.status === 'Active' ? 'activated' : 'deactivated'}.`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } catch (error) {
            console.error('Error toggling supplier status:', error);
            Swal.fire({
                title: 'Error',
                text: 'Failed to update supplier status.',
                icon: 'error'
            });
        } finally {
            loadingSuppliers.value.delete(supplier.id);
        }
    }
};

const filteredSuppliers = computed(() => {
    return props.suppliers.filter(supplier => {
        const matchesSearch = 
            supplier.name.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.contact_person.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.email.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.phone.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus = 
            statusFilter.value === 'all' ||
            (statusFilter.value === 'active' && supplier.status === 'Active') ||
            (statusFilter.value === 'inactive' && supplier.status === 'Inactive');

        return matchesSearch && matchesStatus;
    });
});

const formatDate = (dateString) => {
    return moment(dateString).format('DD/MM/YYYY');
};
</script>