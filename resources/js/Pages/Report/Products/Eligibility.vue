<template>
    <Head title="Product Eligibility Report" />
    <AuthenticatedLayout title="Product Eligibility Report" description="Track product eligibility across different facility types"
        img="/assets/images/report.png">
    
            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Results: {{ eligibleItems.total }}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Facility Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facility Type</label>
                        <Multiselect
                            v-model="facility_type"
                            :options="facilityTypes"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            :show-labels="false"
                            :close-on-select="true"
                            placeholder="Select facility types..."
                            track-by="value"
                            label="label"
                            class="w-full"
                        />
                    </div>

                    <!-- Product Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                        <Multiselect
                            v-model="product_id"
                            :options="products"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            :show-labels="false"
                            :close-on-select="true"
                            placeholder="Select products..."
                            track-by="id"
                            label="name"
                            class="w-full"
                        />
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <Multiselect
                            v-model="category_id"
                            :options="categories"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            :show-labels="false"
                            :close-on-select="true"
                            placeholder="Select categories..."
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
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-700">Total Eligibilities</p>
                            <p class="text-2xl font-bold text-blue-900">{{ summary.total_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-sm border border-green-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-700">Unique Products</p>
                            <p class="text-2xl font-bold text-green-900">{{ summary.unique_products_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm border border-purple-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-700">Facility Types</p>
                            <p class="text-2xl font-bold text-purple-900">{{ summary.facility_types_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eligibility Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Product Eligibility List</h3>
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
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider w-[200px] border-b border-gray-200">Product Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Dosage Form</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Facility Type</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Created Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="item in eligibleItems.data" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    <span class="bg-gray-100 px-2 py-1 rounded-md text-xs">{{ item.product?.productID || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 w-[200px] break-words">
                                    <div class="font-medium">{{ item.product?.name || 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-md text-xs">{{ item.product?.category?.name || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-md text-xs">{{ item.product?.dosage?.name || 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm bg-purple-100 text-purple-800 border border-purple-200">
                                        {{ item.facility_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <span class="bg-gray-50 text-gray-700 px-2 py-1 rounded-md text-xs">{{ formatDate(item.created_at) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-end mt-3">
                    <TailwindPagination 
                        :data="eligibleItems" 
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
    eligibleItems: Object,
    products: Array,
    categories: Array,
    facilityTypes: Array,
    filters: Object,
    summary: Object
});

const facility_type = ref(props.filters.facility_type || [])
const product_id = ref(props.filters.product_id || [])
const category_id = ref(props.filters.category_id || [])
const search = ref(props.filters.search || '')
const per_page = ref(props.filters.per_page || 25)

const applyFilters = () => {
    // Create a clean filters object by removing empty values
    const cleanFilters = {};
    
    if (facility_type.value && facility_type.value.length > 0) cleanFilters.facility_type = facility_type.value;
    if (product_id.value && product_id.value.length > 0) cleanFilters.product_id = product_id.value;
    if (category_id.value && category_id.value.length > 0) cleanFilters.category_id = category_id.value;
    if (search.value) cleanFilters.search = search.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    if (props.filters.page) cleanFilters.page = props.filters.page;
    
    router.get(route('reports.products.eligibility'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['eligibleItems', 'summary']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

// Watch for filter changes and apply them
watch([
    () => search.value,
    () => facility_type.value,
    () => product_id.value,
    () => category_id.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    applyFilters();
});
</script> 