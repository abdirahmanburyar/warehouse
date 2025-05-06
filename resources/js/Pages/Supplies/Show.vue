<template>
    <Head title="Purchase Order Details" />
    <AuthenticatedLayout>
            <!-- Back Button -->
            <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Purchase Orders
            </Link>

            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ props.po.po_number }}</h1>
                        <p class="text-sm text-gray-500 mt-1">Created on {{ formatDate(props.po.created_at) }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span :class="statusClass">{{ props.po.status }}</span>
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            Print PO
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">PO Date</span>
                            <span class="font-medium">{{ formatDate(props.po.po_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Items</span>
                            <span class="font-medium">{{ props.po.items.length }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Amount</span>
                            <span class="font-medium">{{ formatCurrency(calculateTotalAmount) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Supplier Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Name</span>
                            <span class="font-medium">{{ props.po.supplier.name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Contact Person</span>
                            <span class="font-medium">{{ props.po.supplier.contact_person }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Email</span>
                            <span class="font-medium">{{ props.po.supplier.email }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Phone</span>
                            <span class="font-medium">{{ props.po.supplier.phone }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Address</span>
                            <span class="font-medium text-right">{{ props.po.supplier.address }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status</span>
                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', props.po.supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                {{ props.po.supplier.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-[70px]">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-black">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black w-[40px]">SN#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">Unit Cost</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-black-500 uppercase tracking-wider border border-black">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, i) in props.po.items" :key="item.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap border border-black">{{i+1}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap border border-black">
                                    <div>
                                        <div class="text-sm font-medium text-black-900">{{ item.product.name }}</div>
                                        <div class="text-sm text-black-500">{{ item.product.barcode }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black-500 border border-black">
                                    {{ formatNumber(item.quantity) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-black-500 border border-black">
                                    {{ formatCurrency(item.unit_cost) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black-900 border border-black">
                                    {{ formatCurrency(item.total_cost) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 text-right border border-black">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border border-black">
                                    {{ formatCurrency(calculateTotalAmount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
     
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    po: {
        required: true,
        type: Object
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatNumber = (number) => {
    return number.toLocaleString('en-US');
};

const formatCurrency = (number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(number);
};

const calculateTotalAmount = computed(() => {
    return props.po.items.reduce((total, item) => total + item.total_cost, 0);
});

const statusClass = computed(() => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    };
    return `px-3 py-1 rounded-full text-sm font-medium ${classes[props.po.status] || classes.pending}`;
});
</script>