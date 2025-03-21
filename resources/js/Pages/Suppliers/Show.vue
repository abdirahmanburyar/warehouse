<template>
    <Head title="Supplier Details" />
    <AuthenticatedLayout>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Details</h2>
            <div class="flex space-x-2">
                <Link :href="route('supplies.index', { tab: 'suppliers' })" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Back to Suppliers
                </Link>
                <Link :href="route('suppliers.edit', supplier.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Edit Supplier
                </Link>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Basic Information -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Supplier Name</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.name }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Contact Person</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.contact_person || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Email</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.email || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Phone</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.phone || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Status</p>
                                    <p class="mt-1">
                                        <span :class="`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
                                            {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Address</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.address || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">City</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.city || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">State/Province</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.state || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Postal Code</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.postal_code || '—' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Country</p>
                                    <p class="mt-1 text-sm text-gray-900">{{ supplier.country || '—' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="bg-gray-50 p-6 rounded-lg mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                            <p class="text-sm text-gray-900">{{ supplier.notes || 'No notes available.' }}</p>
                        </div>
                        
                        <!-- Related Supplies -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Related Supplies</h3>
                                <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    {{ supplier.supplies_count }} {{ supplier.supplies_count === 1 ? 'Supply' : 'Supplies' }}
                                </span>
                            </div>
                            
                            <div v-if="supplier.supplies && supplier.supplies.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Product
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Quantity
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total Price
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Supply Date
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="supply in supplier.supplies" :key="supply.id" class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ supply.product ? supply.product.name : '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ supply.quantity }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    ${{ supply.total_price }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ formatDate(supply.supply_date) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link :href="route('supplies.show', supply.id)" class="text-indigo-600 hover:text-indigo-900">
                                                        View
                                                    </Link>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500">
                                No supplies found for this supplier.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    supplier: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};
</script>
