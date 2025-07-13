<template>
    <Head title="Product Category Report" />
    <AuthenticatedLayout title="Product Category Report" description="Analyze products by category with detailed breakdowns"
        img="/assets/images/report.png">
    
            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Results: {{ categories.total }}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category Status</label>
                        <Multiselect
                            v-model="status"
                            :options="statusOptions"
                            :multiple="true"
                            :searchable="true"
                            :create-option="false"
                            :show-labels="false"
                            :close-on-select="true"
                            placeholder="Select status..."
                            track-by="value"
                            label="label"
                            class="w-full"
                        />
                    </div>

                    <!-- Search Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Category</label>
                        <div class="relative">
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Category name..."
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
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
                            <p class="text-sm font-medium text-blue-700">Total Categories</p>
                            <p class="text-2xl font-bold text-blue-900">{{ summary.total_count }}</p>
                        </div>
                    </div>
                </div>

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
                            <p class="text-sm font-medium text-green-700">Active Categories</p>
                            <p class="text-2xl font-bold text-green-900">{{ summary.active_count }}</p>
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
                            <p class="text-sm font-medium text-red-700">Inactive Categories</p>
                            <p class="text-2xl font-bold text-red-900">{{ summary.inactive_count }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-sm border border-purple-200 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-700">Total Products</p>
                            <p class="text-2xl font-bold text-purple-900">{{ summary.total_products }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Categories List</h3>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-600">Show:</label>
                            <select v-model="per_page" @change="applyFilters" class="border-gray-300 rounded-md text-sm shadow-sm">
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
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Category Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Description</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Total Products</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Active Products</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Inactive Products</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ category.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ category.description || 'No description' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="[
                                            'inline-flex px-3 py-1 text-xs font-semibold rounded-full shadow-sm',
                                            category.is_active 
                                                ? 'bg-green-100 text-green-800 border border-green-200' 
                                                : 'bg-red-100 text-red-800 border border-red-200'
                                        ]"
                                    >
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ category.total_products }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    {{ category.active_products }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                    {{ category.inactive_products }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button 
                                        @click="openProductsModal(category)"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        View Products
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Modal for Products -->
                <transition name="fade">
                <div v-if="showProductsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" aria-modal="true" role="dialog" tabindex="-1" @keydown.esc="closeProductsModal">
                    <div class="bg-white w-full h-full flex flex-col shadow-2xl relative overflow-hidden">
                        <!-- Sticky Header -->
                        <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200 bg-white sticky top-0 z-10">
                            <h4 class="text-xl font-semibold text-gray-900">Products in {{ selectedCategory?.name }}</h4>
                            <button @click="closeProductsModal" class="text-gray-400 hover:text-gray-700 text-3xl font-bold focus:outline-none absolute top-4 right-8" aria-label="Close">&times;</button>
                        </div>
                        <!-- Scrollable Content -->
                        <div class="flex-1 overflow-auto px-8 py-4 bg-gray-50">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white sticky top-0 z-5">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product ID</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dosage Form</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="product in selectedCategory?.products || []" :key="product.id" class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ product.productID }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ product.name }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">{{ product.dosage?.name || 'N/A' }}</td>
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
                        <!-- Sticky Footer -->
                        <div class="flex justify-end px-8 py-6 border-t border-gray-200 bg-white sticky bottom-0 z-10">
                            <button @click="closeProductsModal" class="px-6 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 focus:outline-none">Close</button>
                        </div>
                    </div>
                </div>
                </transition>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex justify-end mt-3">
                    <TailwindPagination 
                        :data="categories" 
                        :limit="2"
                        @pagination-change-page="getResults"
                    />
                </div>
            </div>

    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    categories: Object,
    filters: Object,
    summary: Object
});

const statusOptions = [
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
];

const status = ref(props.filters.status || []);
const search = ref(props.filters.search || '');
const per_page = ref(props.filters.per_page || 25);

const showProductsModal = ref(false);
const selectedCategory = ref(null);

// Prevent background scroll when modal is open
const preventScroll = () => {
    document.body.style.overflow = showProductsModal.value ? 'hidden' : '';
};
watch(showProductsModal, preventScroll);
onMounted(preventScroll);
onUnmounted(() => { document.body.style.overflow = ''; });

// Focus trap (basic)
const modalRef = ref(null);
watch(showProductsModal, (val) => {
    if (val && modalRef.value) {
        modalRef.value.focus();
    }
});

const applyFilters = () => {
    const cleanFilters = {};
    if (status.value && status.value.length > 0) cleanFilters.status = status.value;
    if (search.value) cleanFilters.search = search.value;
    if (per_page.value && per_page.value !== 25) cleanFilters.per_page = per_page.value;
    router.get(route('reports.products.categories'), cleanFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['categories', 'summary']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const openProductsModal = (category) => {
    selectedCategory.value = category;
    showProductsModal.value = true;
    setTimeout(() => {
        if (modalRef.value) modalRef.value.focus();
    }, 50);
};

const closeProductsModal = () => {
    showProductsModal.value = false;
    selectedCategory.value = null;
};

watch([
    () => search.value,
    () => status.value,
    () => per_page.value,
    () => props.filters.page
], () => {
    applyFilters();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style> 