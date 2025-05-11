<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="flex justify-between items-center py-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ props.po.po_number }}</h1>
                    <p class="text-sm text-gray-600">Created on {{ formatDate(props.po.created_at) }}</p>
                </div>
            </div>

            <!-- Main Content with top padding to account for fixed header -->
            <!-- Supplier Information -->
            <div class="bg-white rounded-lg shadow mb-6 p-6">
                <h2 class="text-lg font-semibold mb-4">Supplier Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Supplier Name</p>
                        <p class="text-gray-900">{{ props.po.supplier.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Contact Person</p>
                        <p class="text-gray-900">{{ props.po.supplier.contact_person }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Email</p>
                        <p class="text-gray-900">{{ props.po.supplier.email }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Phone</p>
                        <p class="text-gray-900">{{ props.po.supplier.phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="item in props.po.items" :key="item.id">
                                <td class="px-6 py-4 whitespace-normal">
                                    <div class="text-sm text-gray-900">{{ item.product.name }}</div>
                                    <div class="text-sm text-gray-500">{{ item.product.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.product.barcode }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ formatNumber(item.quantity) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ formatNumber(item.unit_cost) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ formatNumber(item.total_cost) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">Total Amount:</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">${{ formatNumber(totalAmount) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    po: {
        required: true,
        type: Object
    }
});

const statusClasses = computed(() => {
    const classes = 'px-3 py-1 rounded-full text-sm font-medium';
    switch (props.po.status) {
        case 'pending':
            return `${classes} bg-yellow-100 text-yellow-800`;
        case 'approved':
            return `${classes} bg-green-100 text-green-800`;
        case 'rejected':
            return `${classes} bg-red-100 text-red-800`;
        default:
            return `${classes} bg-gray-100 text-gray-800`;
    }
});

const totalAmount = computed(() => {
    return props.po.items.reduce((sum, item) => sum + item.total_cost, 0);
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

const updateStatus = (newStatus) => {
    // TODO: Implement status update logic
    console.log(`Updating status to ${newStatus}`);
};
</script>