<template>
    <div class="space-y-4">
        <!-- Search and Filters -->
        <div class="flex flex-wrap gap-4">
            <div class="flex-grow relative">
                <TextInput v-model="filters.search" type="text" class="w-full" placeholder="Search by item name, barcode" />
                <button v-if="filters.search" @click="filters.search = ''; applyFilters()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Filter Dropdowns -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-[200px]">
                    <select v-model="filters.warehouse_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters">
                        <option value="">All Warehouses</option>
                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                            {{ warehouse.name }}
                        </option>
                    </select>
                </div>
                <div class="w-[200px]">
                    <select v-model="filters.category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters">
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
                <div class="w-[200px]">
                    <select v-model="filters.status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @change="applyFilters">
                        <option value="">All Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
                <SecondaryButton @click="resetFilters">Reset</SecondaryButton>
            </div>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="status in inventoryStatusCounts" :key="status.status" class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div :class="{
                        'p-3 rounded-full mr-4': true,
                        'bg-green-100': status.status === 'in_stock',
                        'bg-yellow-100': status.status === 'low_stock',
                        'bg-red-100': status.status === 'out_of_stock'
                    }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="{
                            'text-green-600': status.status === 'in_stock',
                            'text-yellow-600': status.status === 'low_stock',
                            'text-red-600': status.status === 'out_of_stock'
                        }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">{{ formatStatus(status.status) }}</div>
                        <div class="text-2xl font-semibold">{{ status.count }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Item
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Warehouse
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="!inventories.data || inventories.data.length === 0">
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span class="text-lg font-medium">No inventory items found</span>
                                <p class="text-sm text-gray-400 mt-1">Add items or adjust your filters</p>
                            </div>
                        </td>
                    </tr>
                    <tr v-for="item in inventories.data" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img v-if="item.image" :src="item.image" class="h-10 w-10 rounded-full object-cover" :alt="item.name">
                                    <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                                    <div class="text-sm text-gray-500">SKU: {{ item.sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ item.category?.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ item.warehouse?.name }}</div>
                            <div class="text-sm text-gray-500">{{ item.location?.name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                            <div class="text-sm text-gray-500">Min: {{ item.min_quantity }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="{
                                'px-2 py-1 text-xs font-medium rounded-full': true,
                                'bg-green-100 text-green-800': item.status === 'in_stock',
                                'bg-yellow-100 text-yellow-800': item.status === 'low_stock',
                                'bg-red-100 text-red-800': item.status === 'out_of_stock'
                            }">
                                {{ formatStatus(item.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <button @click="$emit('edit', item)" class="text-indigo-600 hover:text-indigo-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="$emit('delete', item)" class="text-red-600 hover:text-red-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="inventories.data && inventories.data.length > 0" class="mt-4">
            <Pagination :links="inventories.meta.links" />
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    inventories: Object,
    filters: Object,
    warehouses: Array,
    categories: Array,
    inventoryStatusCounts: Array
});

const emit = defineEmits(['add', 'edit', 'delete']);

const filters = reactive({
    search: props.filters?.search || '',
    warehouse_id: props.filters?.warehouse_id || '',
    category_id: props.filters?.category_id || '',
    status: props.filters?.status || ''
});

const formatStatus = (status) => {
    return status.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const applyFilters = () => {
    router.get(route('inventories.items'), filters, {
        preserveState: true,
        preserveScroll: true,
        only: ['inventories', 'inventoryStatusCounts']
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.warehouse_id = '';
    filters.category_id = '';
    filters.status = '';
    applyFilters();
};

watch(() => filters.search, () => {
    applyFilters();
});
</script>
