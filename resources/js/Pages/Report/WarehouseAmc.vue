<template>
    <Head title="Warehouse AMC Report" />
    <AuthenticatedLayout title="Warehouse AMC Report" description="View and analyze warehouse Average Monthly Consumption data"
        img="/assets/images/products.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Warehouse AMC Report
            </h2>
        </template>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header with Export Button -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Warehouse AMC Management</h3>
                        <p class="text-sm text-gray-600 mt-1">View and analyze Average Monthly Consumption data for warehouse products</p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="exportData"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export CSV
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash.success"
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600">Total Products</p>
                                <p class="text-2xl font-bold text-blue-900">{{ summary.total_products }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600">Total Consumption</p>
                                <p class="text-2xl font-bold text-green-900">{{ formatNumber(summary.total_consumption) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-600">Average Consumption</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ formatNumber(summary.average_consumption) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-600">Highest Consumption</p>
                                <p class="text-2xl font-bold text-red-900">{{ formatNumber(summary.highest_consumption) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-600">Lowest Consumption</p>
                                <p class="text-2xl font-bold text-purple-900">{{ formatNumber(summary.lowest_consumption) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input id="search" v-model="search" type="text"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Search products, categories, or dosages..." />
                        </div>
                    </div>
                    
                    <div class="sm:w-48">
                        <label for="category" class="sr-only">Category</label>
                        <select id="category" v-model="category" @change="applyFilters"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div class="sm:w-48">
                        <label for="dosage" class="sr-only">Dosage</label>
                        <select id="dosage" v-model="dosage" @change="applyFilters"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Dosages</option>
                            <option v-for="dos in dosages" :key="dos.id" :value="dos.id">{{ dos.name }}</option>
                        </select>
                    </div>

                    <div class="sm:w-48">
                        <label for="year" class="sr-only">Year</label>
                        <select id="year" v-model="year" @change="applyFilters"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Years</option>
                            <option v-for="yr in years" :key="yr" :value="yr">{{ yr }}</option>
                        </select>
                    </div>

                    <div class="sm:w-48">
                        <label for="per_page" class="sr-only">Items per page</label>
                        <select id="per_page" v-model="per_page" @change="props.filters.page = 1; applyFilters()"
                            class="block w-full border border-gray-300 rounded-md py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>

                <!-- Clear Filters Button -->
                <div class="mb-4 flex justify-end">
                    <button @click="clearFilters"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear Filters
                    </button>
                </div>

                <!-- Pivot Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 sticky left-0 bg-gray-50 z-10"
                                    @click="sortBy('name')">
                                    <div class="flex items-center">
                                        Item
                                        <svg v-if="sortField === 'name'" class="ml-1 h-4 w-4 text-gray-400"
                                            :class="{ 'rotate-180': sortDirection === 'desc' }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sortBy('category_name')">
                                    <div class="flex items-center">
                                        Category
                                        <svg v-if="sortField === 'category_name'" class="ml-1 h-4 w-4 text-gray-400"
                                            :class="{ 'rotate-180': sortDirection === 'desc' }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    @click="sortBy('dosage_name')">
                                    <div class="flex items-center">
                                        Dosage Form
                                        <svg v-if="sortField === 'dosage_name'" class="ml-1 h-4 w-4 text-gray-400"
                                            :class="{ 'rotate-180': sortDirection === 'desc' }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </div>
                                </th>
                                <!-- Dynamic Month Columns -->
                                <th v-for="monthYear in monthYears" :key="monthYear"
                                    class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider min-w-[100px]">
                                    {{ formatMonthYear(monthYear) }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-if="pivotData && pivotData.length > 0">
                                <tr v-for="product in pivotData" :key="product.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white z-10">
                                        {{ product.name }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.category }}
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ product.dosage }}
                                    </td>
                                    <!-- Dynamic Month Data -->
                                    <td v-for="monthYear in monthYears" :key="monthYear"
                                        class="px-3 py-4 whitespace-nowrap text-sm text-center text-gray-900 font-medium">
                                        {{ formatNumber(product.months[monthYear] || 0) }}
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td :colspan="3 + monthYears.length" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No warehouse AMC data found.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        <span v-if="products && products.meta">
                            Showing {{ products.meta.from }} to {{ products.meta.to }} of {{ products.meta.total }} results
                        </span>
                    </div>
                    <TailwindPagination
                        :data="products"
                        @pagination-change-page="getResults"
                        :limit="2"
                    />
                </div>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Consuming Products</h3>
                <div class="space-y-3">
                    <div v-for="(product, index) in topProducts" :key="product.product_id" 
                         class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <span class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                {{ index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ product.product?.name || 'Unknown Product' }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-blue-600">
                            {{ formatNumber(product.total_consumption) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Consumption Trend Chart -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Consumption Trend ({{ new Date().getFullYear() }})</h3>
                <div class="space-y-3">
                    <div v-for="trend in consumptionTrend" :key="trend.month_year" 
                         class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-900">
                            {{ formatMonthYear(trend.month_year) }}
                        </span>
                        <span class="text-sm font-semibold text-green-600">
                            {{ formatNumber(trend.total_consumption) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { TailwindPagination } from "laravel-vue-pagination";
import { useToast } from 'vue-toastification';
const toast = useToast();

const props = defineProps({
    products: Object,
    pivotData: Array,
    monthYears: Array,
    filters: Object,
    categories: Array,
    dosages: Array,
    years: Array,
    months: Array,
    summary: Object,
    topProducts: Array,
    consumptionTrend: Array,
});

// Reactive variables
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const dosage = ref(props.filters.dosage || '');
const year = ref(props.filters.year || '');
const per_page = ref(props.filters.per_page || 25);
const sortField = ref(props.filters.sort || 'name');
const sortDirection = ref(props.filters.direction || 'asc');

// Watch for changes and apply filters
watch([search, category, dosage, year], () => {
    props.filters.page = 1;
    applyFilters();
}, { deep: true });

// Methods
const applyFilters = () => {
    router.get(route('reports.warehouse-amc'), {
        search: search.value,
        category: category.value,
        dosage: dosage.value,
        year: year.value,
        sort: sortField.value,
        direction: sortDirection.value,
        per_page: per_page.value,
        page: props.filters.page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    search.value = '';
    category.value = '';
    dosage.value = '';
    year.value = '';
    sortField.value = 'name';
    sortDirection.value = 'asc';
    props.filters.page = 1;
    applyFilters();
    toast.success('Filters cleared!');
};

const sortBy = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFilters();
};

const getResults = (page) => {
    props.filters.page = page;
    applyFilters();
};

const exportData = () => {
    const params = new URLSearchParams({
        search: search.value,
        category: category.value,
        dosage: dosage.value,
        year: year.value,
    });

    window.open(route('reports.warehouse-amc.export') + '?' + params.toString(), '_blank');
};

// Utility functions
const formatNumber = (num) => {
    if (num === null || num === undefined) return '0';
    return new Intl.NumberFormat('en-US').format(num);
};

const formatMonthYear = (monthYear) => {
    if (!monthYear) return 'N/A';
    const [year, month] = monthYear.split('-');
    const date = new Date(parseInt(year), parseInt(month) - 1);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
    });
};
</script>
