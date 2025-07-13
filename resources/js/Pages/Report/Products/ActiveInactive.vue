<template>
    <Head title="Active & Inactive Product Report" />
    <AuthenticatedLayout title="Active & Inactive Product Report" description="Comprehensive view of product status across the system"
        img="/assets/images/report.png">
    
            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Results: {{ products.total }}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="status" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <Multiselect
                            v-model="category_ids"
                            :options="categories"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            placeholder="Select categories..."
                            track-by="id"
                            label="name"
                            class="w-full"
                        />
                    </div>

                    <!-- Dosage Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dosage Form</label>
                        <Multiselect
                            v-model="dosage_ids"
                            :options="dosages"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select dosage forms..."
                            track-by="id"
                            label="name"
                            class="w-full"
                        />
                    </div>

                    <!-- Search Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Product name or ID..."
                                class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm pl-10"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm border border-green-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-700">Active Products</p>
                            <p class="text-2xl font-bold text-green-900">{{ activeCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-sm border border-red-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-700">Inactive Products</p>
                            <p class="text-2xl font-bold text-red-900">{{ inactiveCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-700">Total Products</p>
                            <p class="text-2xl font-bold text-blue-900">{{ totalCount }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Products List</h3>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">Show:</label>
                            <select v-model="per_page" @change="props.filters.page = 1" class="border-gray-300 rounded-md text-sm shadow-sm">
                                <option value="25">25 Per page</option>
                                <option value="50">50 Per page</option>
                                <option value="100">100 Per page</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Product ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-[200px] border-b border-gray-200">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Dosage Form</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Tracer Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <span class="bg-gray-100 px-2 py-1 rounded-md text-xs">{{ product.productID }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 w-[200px] break-words">
                                    <div class="font-medium">{{ product.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs">{{ product.category?.name || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-md text-xs">{{ product.dosage?.name || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm',
                                            product.is_active 
                                                ? 'bg-green-100 text-green-800 border border-green-200' 
                                                : 'bg-red-100 text-red-800 border border-red-200'
                                        ]"
                                    >
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-md text-xs">{{ Array.isArray(product.tracert_type) ? product.tracert_type.join(', ') : 'N/A' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-end mt-3">
                        <TailwindPagination 
                            :data="products" 
                            :limit="2"
                            @pagination-change-page="getResults"
                        />
                    </div>
            </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    products: Object,
    categories: Array,
    dosages: Array,
    filters: Object,
    summary: Object
});

const status  = ref(props.filters.status || '')
const category_ids = ref(props.filters.category_ids || [])
const dosage_ids = ref(props.filters.dosage_ids || [])
const search = ref(props.filters.search || '')
const per_page = ref(props.filters.per_page || 25)

const activeCount = computed(() => {
    return props.summary?.active_count || 0;
});

const inactiveCount = computed(() => {
    return props.summary?.inactive_count || 0;
});

const totalCount = computed(() => {
    return props.summary?.total_count || 0;
});

const applyFilters = () => {
    // Create a clean filters object by removing empty values
    const cleanFilters = {};
    
    if (status.value) cleanFilters.status = status.value;
    if (search.value) cleanFilters.search = search.value;
    if (category_ids.value && category_ids.value.length > 0) cleanFilters.category_ids = category_ids.value;
    if (dosage_ids.value && dosage_ids.value.length > 0) cleanFilters.dosage_ids = dosage_ids.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    if (props.filters.page) cleanFilters.page = props.filters.page;
    
    router.get(route('reports.products.active-inactive'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['products']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

// Watch for filter changes and apply them
watch([
    () => search.value,
    () => status.value,
    () => category_ids.value,
    () => dosage_ids.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    applyFilters();
});

</script> 