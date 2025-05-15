<template>
    <Head title="Suppliers List" />
    <AuthenticatedLayout>
        <!-- Back Button -->
        <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to  Home
        </Link>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-[60px]">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Suppliers</h2>
                    <Link :href="route('supplies.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New Supplier
                    </Link>
                </div>
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Search suppliers..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Status:</label>
                        <select 
                            v-model="statusFilter" 
                            class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-300px)]">
                    <table class="min-w-full border border-black divide-y divide-black">
                        <thead class="bg-gray-50 sticky top-0 z-10">
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
                        <tr v-for="supplier in filteredSuppliers" :key="supplier.id" class="hover:bg-gray-50">
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
                                <span :class="[supplier.status == 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                   {{ supplier.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap border border-black text-sm font-medium">
                                <Link :href="route('supplies.suppliers.edit', supplier.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </Link>
                                <button class="text-red-600 hover:text-red-900 inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const search = ref('');
const statusFilter = ref('all');

const props = defineProps({
    suppliers: {
        required: true,
        type: Array
    }
});

const filteredSuppliers = computed(() => {
    return props.suppliers.filter(supplier => {
        const matchesSearch = 
            supplier.name.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.contact_person.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.email.toLowerCase().includes(search.value.toLowerCase()) ||
            supplier.phone.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus = 
            statusFilter.value === 'all' ||
            (statusFilter.value === 'active' && supplier.is_active) ||
            (statusFilter.value === 'inactive' && !supplier.is_active);

        return matchesSearch && matchesStatus;
    });
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>