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
                                                 <p class="text-sm text-gray-600 mt-1">View and analyze Average Monthly Consumption data for warehouse products. Select a year to download a template with months for that specific year, upload data, and export to Excel with full formatting. <strong>Import Tip:</strong> Empty cells in uploaded files won't overwrite existing data.</p>
                    </div>
                                         <div class="flex space-x-3">
                         <div class="flex items-center space-x-2">
                             <label class="text-sm font-medium text-gray-700">Year:</label>
                             <select 
                                 v-model="templateYear" 
                                 class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                             >
                                 <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                             </select>
                             <button
                                 @click="downloadTemplate"
                                 class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8l-8-8-8 8"></path>
                                 </svg>
                                 Download Template
                             </button>
                         </div>
                        <button
                            @click="openUploadModal"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Upload Data
                        </button>
                        <button
                            @click="exportData"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="$page.props.flash.success"
                    class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ $page.props.flash.success }}
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
                        <Multiselect 
                            v-model="category" 
                            :options="categories" 
                            :multiple="false" 
                            :searchable="true" 
                            :close-on-select="true" 
                            :clear-on-select="false" 
                            :hide-selected="true" 
                            :placeholder="'All Categories'"
                            @change="applyFilters"
                            track-by="id"
                            label="name"
                        />
                    </div>

                    <div class="sm:w-48">
                        <label for="dosage" class="sr-only">Dosage</label>
                        <Multiselect 
                            v-model="dosage" 
                            :options="dosages" 
                            :multiple="false" 
                            :searchable="true" 
                            :close-on-select="true" 
                            :clear-on-select="false" 
                            :hide-selected="true" 
                            :placeholder="'All Dosages'"
                            @change="applyFilters"
                            track-by="id"
                            label="name"
                        />
                    </div>

                    <div class="sm:w-48">
                        <label for="year" class="sr-only">Year</label>
                        <Multiselect 
                            v-model="year" 
                            :options="years" 
                            :multiple="false" 
                            :searchable="true" 
                            :close-on-select="true" 
                            :clear-on-select="false" 
                            :hide-selected="true" 
                            :placeholder="'All Years'"
                            @change="applyFilters"
                        />
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

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeUploadModal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Upload Warehouse AMC Data
                        </h3>
                        <button @click="closeUploadModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                                                 <p class="text-sm text-gray-600 mb-3">
                             Upload an Excel file (.xlsx) with warehouse AMC data. Make sure the file follows the template format. 
                             <strong>Note:</strong> Empty cells will not overwrite existing data - only filled quantities will be updated.
                         </p>
                        
                        <!-- File Input -->
                        <div class="mb-4">
                            <input 
                                ref="fileInput"
                                type="file" 
                                accept=".xlsx,.xls"
                                @change="handleFileUpload"
                                class="hidden"
                            >
                            
                            <div v-if="!selectedFile" 
                                 @click="triggerFileInput" 
                                 class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-gray-400 transition-colors">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">Click to upload Excel file</p>
                                    <p class="text-xs text-gray-500 mt-1">Supports .xlsx and .xls files up to 10MB</p>
                                </div>
                            </div>
                            
                            <div v-else class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                                            <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                                        </div>
                                    </div>
                                    <button @click="removeSelectedFile" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Upload Progress -->
                        <div v-if="isUploading" class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-blue-700">{{ uploadStatus }}</span>
                                <span class="text-sm font-medium text-blue-700">{{ uploadProgress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                            </div>
                            <div v-if="importId" class="mt-2 text-xs text-gray-600">
                                Import ID: {{ importId }}
                            </div>
                        </div>
                        
                        <!-- Upload Results -->
                        <div v-if="uploadResults" class="mb-4">
                            <div v-if="uploadResults.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ uploadResults.message }}</p>
                                        <div v-if="uploadResults.warnings && uploadResults.warnings.length > 0" class="mt-2">
                                            <p class="text-sm font-medium">Warnings:</p>
                                            <ul class="text-sm mt-1 list-disc list-inside">
                                                <li v-for="warning in uploadResults.warnings" :key="warning">{{ warning }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ uploadResults.message }}</p>
                                        <div v-if="uploadResults.errors && uploadResults.errors.length > 0" class="mt-2">
                                            <p class="text-sm font-medium">Errors:</p>
                                            <ul class="text-sm mt-1 list-disc list-inside">
                                                <li v-for="error in uploadResults.errors" :key="error">{{ error }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <button 
                            @click="closeUploadModal" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button 
                            @click="uploadFile"
                            :disabled="!selectedFile || isUploading"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ isUploading ? 'Uploading...' : 'Upload' }}
                        </button>
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
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

import axios from 'axios';

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
});

// Reactive variables
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const dosage = ref(props.filters.dosage || '');
const year = ref(props.filters.year || '');
const per_page = ref(props.filters.per_page || 25);
const sortField = ref(props.filters.sort || 'name');
const sortDirection = ref(props.filters.direction || 'asc');
const templateYear = ref(new Date().getFullYear()); // Default to current year

// Upload related variables
const showUploadModal = ref(false);
const selectedFile = ref(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadStatus = ref('Uploading...');
const uploadResults = ref(null);
const fileInput = ref(null);
const importId = ref(null);
const progressInterval = ref(null);

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

// Upload Modal Functions
const openUploadModal = () => {
    showUploadModal.value = true;
    resetUploadState();
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    resetUploadState();
};

const resetUploadState = () => {
    selectedFile.value = null;
    isUploading.value = false;
    uploadProgress.value = 0;
    uploadStatus.value = 'Uploading...';
    uploadResults.value = null;
    importId.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
    
    // Clear progress interval
    if (progressInterval.value) {
        clearInterval(progressInterval.value);
        progressInterval.value = null;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    const allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'application/vnd.ms-excel', // .xls
    ];
    
    if (!allowedTypes.includes(file.type)) {
        toast.error('Invalid file type. Please upload an Excel file (.xlsx or .xls)');
        event.target.value = null;
        return;
    }

    // Validate file size (10MB max)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        toast.error('File is too large. Maximum file size is 10MB.');
        event.target.value = null;
        return;
    }

    selectedFile.value = file;
    uploadResults.value = null;
};

const removeSelectedFile = () => {
    selectedFile.value = null;
    uploadResults.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const uploadFile = async () => {
    if (!selectedFile.value) {
        toast.error('Please select a file to upload');
        return;
    }

    isUploading.value = true;
    uploadProgress.value = 0;
    uploadStatus.value = 'Uploading file...';
    uploadResults.value = null;
    importId.value = null;

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await axios.post(route('reports.warehouse-amc.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        if (response.data.success) {
            importId.value = response.data.import_id;
            uploadStatus.value = 'File uploaded, processing in background...';
            uploadProgress.value = 10;
            
            // Start polling for progress
            startProgressPolling(response.data.import_id);
            
            toast.success(response.data.message);
        } else {
            uploadResults.value = response.data;
            toast.error(response.data.message || 'Upload failed');
            isUploading.value = false;
        }

    } catch (error) {
        console.error('Upload error:', error);
        
        if (error.response && error.response.data) {
            uploadResults.value = error.response.data;
            toast.error(error.response.data.message || 'Upload failed');
        } else {
            toast.error('Upload failed. Please try again.');
            uploadResults.value = {
                success: false,
                message: 'Upload failed. Please check your connection and try again.',
            };
        }
        isUploading.value = false;
    }
};

const startProgressPolling = (id) => {
    progressInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(route('reports.warehouse-amc.import.status', { importId: id }));
            
            if (response.data.success) {
                const status = response.data.data;
                uploadProgress.value = status.progress;
                uploadStatus.value = status.message;
                
                if (status.status === 'completed') {
                    // Import completed
                    clearInterval(progressInterval.value);
                    progressInterval.value = null;
                    
                    uploadResults.value = {
                        success: true,
                        message: status.message,
                        warnings: status.errors || []
                    };
                    
                    isUploading.value = false;
                    
                    // Refresh the page data after successful import
                    setTimeout(() => {
                        applyFilters();
                    }, 2000);
                    
                    toast.success('Import completed successfully!');
                }
            }
        } catch (error) {
            console.error('Progress check error:', error);
        }
    }, 2000); // Check every 2 seconds
};

const downloadTemplate = () => {
    const url = route('reports.warehouse-amc.template') + '?year=' + templateYear.value;
    window.open(url, '_blank');
    toast.success(`Template download started for year ${templateYear.value}!`);
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

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>
