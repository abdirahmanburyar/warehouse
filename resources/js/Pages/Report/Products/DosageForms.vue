<template>
    <Head title="Product Dosage Forms Report" />
    <AuthenticatedLayout title="Product Dosage Forms Report" description="Analyze products by dosage forms with detailed breakdowns"
        img="/assets/images/report.png">
        
        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Product Dosage Forms Report</h1>
                    <p class="text-gray-600">Comprehensive analysis of products organized by dosage forms with status breakdowns</p>
                </div>

                <!-- Filters Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dosage Status</label>
                            <select v-model="filters.status" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Search Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Dosage</label>
                            <input 
                                v-model="filters.search" 
                                type="text" 
                                placeholder="Dosage name..."
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex gap-3">
                            <button 
                                type="submit" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Apply Filters
                            </button>
                            <button 
                                type="button" 
                                @click="clearFilters"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Dosage Forms</p>
                                <p class="text-2xl font-bold text-gray-900">{{ totalDosages }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Dosage Forms</p>
                                <p class="text-2xl font-bold text-gray-900">{{ activeDosages }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Inactive Dosage Forms</p>
                                <p class="text-2xl font-bold text-gray-900">{{ inactiveDosages }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Products</p>
                                <p class="text-2xl font-bold text-gray-900">{{ totalProducts }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dosage Forms Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Dosage Forms List</h3>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">Show:</label>
                                <select v-model="filters.per_page" @change="applyFilters" class="border-gray-300 rounded-md text-sm">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage Form Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Products</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Products</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inactive Products</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="dosage in dosages.data" :key="dosage.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ dosage.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ dosage.description || 'No description' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                dosage.is_active 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : 'bg-red-100 text-red-800'
                                            ]"
                                        >
                                            {{ dosage.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ dosage.total_products }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        {{ dosage.active_products }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                        {{ dosage.inactive_products }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button 
                                            @click="toggleDosageDetails(dosage.id)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ expandedDosages.includes(dosage.id) ? 'Hide' : 'View' }} Products
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Expanded Dosage Details -->
                    <div v-for="dosage in dosages.data" :key="`details-${dosage.id}`" v-show="expandedDosages.includes(dosage.id)" class="border-t border-gray-200 bg-gray-50">
                        <div class="px-6 py-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Products with {{ dosage.name }} Dosage Form</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product ID</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="product in dosage.products" :key="product.id" class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ product.productID }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ product.name }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">{{ product.category?.name || 'N/A' }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <span 
                                                    :class="[
                                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                        product.is_active 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <Pagination :links="dosages.links" />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    dosages: Object,
    filters: Object
});

const filters = ref({
    status: props.filters.status || '',
    search: props.filters.search || '',
    per_page: props.filters.per_page || 25
});

const expandedDosages = ref([]);

const totalDosages = computed(() => {
    return props.dosages.total;
});

const activeDosages = computed(() => {
    return props.dosages.data.filter(dosage => dosage.is_active).length;
});

const inactiveDosages = computed(() => {
    return props.dosages.data.filter(dosage => !dosage.is_active).length;
});

const totalProducts = computed(() => {
    return props.dosages.data.reduce((sum, dosage) => sum + dosage.total_products, 0);
});

const applyFilters = () => {
    router.get(route('reports.products.dosage-forms'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        search: '',
        per_page: 25
    };
    applyFilters();
};

const toggleDosageDetails = (dosageId) => {
    const index = expandedDosages.value.indexOf(dosageId);
    if (index > -1) {
        expandedDosages.value.splice(index, 1);
    } else {
        expandedDosages.value.push(dosageId);
    }
};

// Watch for filter changes and apply them
watch(filters, () => {
    applyFilters();
}, { deep: true });
</script> 