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
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Results: {{ dosages.total }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dosage Status</label>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Dosage</label>
                            <div class="relative">
                                <input 
                                    v-model="search" 
                                    type="text" 
                                    placeholder="Dosage name..."
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-[80px]">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Dosage Forms List</h3>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">Show:</label>
                                <select v-model="per_page" @change="props.filters.page = 1" class="border-gray-300 rounded-md text-sm">
                                    <option value="10">10 Per Page</option>
                                    <option value="25">25 Per Page</option>
                                    <option value="50">50 Per Page</option>
                                    <option value="100">100 Per Page</option>
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
                                            @click="openProductsModal(dosage)"
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
                        <div class="bg-white w-full h-full flex flex-col shadow-2xl relative overflow-hidden" ref="modalRef">
                            <!-- Sticky Header -->
                            <div class="flex items-center justify-between px-8 py-6 border-b border-gray-200 bg-white sticky top-0 z-10">
                                <h4 class="text-xl font-semibold text-gray-900">Products with {{ selectedDosage?.name }} Dosage Form</h4>
                                <button @click="closeProductsModal" class="text-gray-400 hover:text-gray-700 text-3xl font-bold focus:outline-none absolute top-4 right-8" aria-label="Close">&times;</button>
                            </div>
                            <!-- Scrollable Content -->
                            <div class="flex-1 overflow-auto px-8 py-4 bg-gray-50">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white sticky top-0 z-5">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product ID</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="product in selectedDosage?.products || []" :key="product.id" class="hover:bg-gray-50">
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
                            :data="dosages" 
                            :limit="2"
                            @pagination-change-page="getResults"
                        />
                    </div>
                </div>

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
    dosages: Object,
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
    const query = {};
    if (status.value && status.value.length > 0) query.status = status.value;
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    router.get(route('reports.products.dosage-forms'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['dosages']
    });
};

const getResults = (page = 1) => {
    props.filters.page = page;
};

const showProductsModal = ref(false);
const selectedDosage = ref(null);

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

const openProductsModal = (dosage) => {
    selectedDosage.value = dosage;
    showProductsModal.value = true;
    setTimeout(() => {
        if (modalRef.value) modalRef.value.focus();
    }, 50);
};

const closeProductsModal = () => {
    showProductsModal.value = false;
    selectedDosage.value = null;
};

watch([
    () => status.value,
    () => search.value,
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