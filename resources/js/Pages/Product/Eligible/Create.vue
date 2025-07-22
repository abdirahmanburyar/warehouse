<template>
    <AuthenticatedLayout title="Create Eligible Item" description="Create a new eligible item">
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                    Products
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <Link :href="route('products.eligible.index')" class="hover:text-gray-900 transition-colors duration-200">
                    Eligible Items
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="text-gray-900">Create</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Eligible Item</h2>
                </div>
            </div>
        </div>

        <div class="mb-[100px]">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Eligible Item Information</h3>
                <p class="text-sm text-gray-600 mt-1">Select facility types and add products</p>
            </div>

            <!-- Excel Upload Section -->
            <div class="mb-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-lg font-medium text-blue-900">Bulk Import Eligible Items</h4>
                        <p class="text-sm text-blue-700 mt-1">Upload multiple eligible items at once using Excel</p>
                    </div>
                    <button
                        @click="openUploadModal"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
                        :disabled="isUploading"
                    >
                        <svg v-if="!isUploading" class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <svg v-else class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isUploading ? 'Uploading...' : 'Upload Excel' }}
                    </button>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Facility Types Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="facility_types" value="Facility Types" class="text-sm font-medium text-gray-700 mb-2" />
                        <div class="flex-1">
                            <Multiselect
                                v-model="form.facility_types"
                                :options="facilityTypeOptions"
                                :multiple="true"
                                :searchable="true"
                                :close-on-select="false"
                                :clear-on-select="false"
                                :preserve-search="true"
                                :show-labels="false"
                                placeholder="Select facility types"
                                class="w-full"
                                @select="handleFacilityTypeSelect"
                            >
                                <template v-slot:selection="{ values, search, isOpen }">
                                    <span class="multiselect__single" v-if="values.length && !isOpen">
                                        {{ values.join(', ') }}
                                    </span>
                                </template>
                            </Multiselect>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Select one or more facility types</p>
                    </div>
                </div>

                <!-- Products Selection -->
                <div>
                    <InputLabel for="products" value="Products" class="text-sm font-medium text-gray-700 mb-2" />
                    <div class="space-y-4">
                        <!-- Product Rows -->
                        <div 
                            v-for="(item, index) in form.products" 
                            :key="item.id" 
                            class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200"
                        >
                            <div class="flex-1">
                                <Multiselect
                                    v-model="item.product"
                                    :value="item.product_id"
                                    :options="products"
                                    :searchable="true"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    placeholder="Search and select a product"
                                    track-by="id"
                                    label="name"
                                    class="w-full"
                                    @select="handleSelectProduct(index, $event)"
                                />
                            </div>
                            <button 
                                v-if="index > 0"
                                type="button" 
                                @click="removeProduct(index)"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                title="Remove Product"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Add Product Button -->
                        <div class="flex items-center gap-3">
                            <button 
                                type="button" 
                                @click="addProductToList"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Product
                            </button>
                            <Link
                                :href="route('products.create')"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create New Product
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <Link
                        :href="route('products.eligible.index')"
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton
                        :disabled="processing"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Creating...' : 'Create Eligible Items' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Add New Facility Type Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Add New Facility Type</h3>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="createFacilityType">
                        <div class="mb-4">
                            <InputLabel for="new_facility_type_name" value="Facility Type Name" class="text-sm font-medium text-gray-700 mb-2" />
                            <TextInput
                                id="new_facility_type_name"
                                v-model="newFacilityType.name"
                                type="text"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                                placeholder="Enter facility type name"
                                required
                                :disabled="creatingFacilityType"
                            />
                        </div>
                        
                        <div class="flex items-center justify-end space-x-3">
                            <button
                                type="button"
                                @click="closeModal"
                                :disabled="creatingFacilityType"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="creatingFacilityType"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="creatingFacilityType" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ creatingFacilityType ? 'Creating...' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Excel Upload Modal -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeUploadModal"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Upload Eligible Items</h3>
                        <p class="text-sm text-gray-500 mt-1">Import eligible items from Excel file</p>
                    </div>
                    <button
                        @click="closeUploadModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Download Template Section -->
                    <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800">Need a template?</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Download our template to see the correct format for uploading eligible items.
                                </p>
                                <button
                                    @click="downloadTemplate"
                                    class="mt-3 inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download Template
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Required Columns</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">item_description</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">facility_type</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition-colors cursor-pointer"
                            @click="triggerFileInput"
                        >
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                @change="handleFileUpload"
                                accept=".xlsx,.xls,.csv"
                            />
                            <svg class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">
                                {{ selectedFile ? 'File Selected' : 'Choose File' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ selectedFile ? selectedFile.name : 'Click to select or drag and drop file here' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                Supports .xlsx, .xls, and .csv files (max 5MB)
                            </p>
                        </div>

                        <div
                            v-if="selectedFile"
                            class="mt-4 flex items-center justify-between bg-blue-50 p-4 rounded-lg border border-blue-200"
                        >
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ selectedFile.name }}</p>
                                    <p class="text-xs text-blue-700">{{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB</p>
                                </div>
                            </div>
                            <button
                                @click.stop="removeSelectedFile"
                                class="text-red-500 hover:text-red-700 transition-colors duration-200"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button
                        @click="closeUploadModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="uploadFile"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                        :disabled="!selectedFile || isUploading"
                    >
                        <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isUploading ? 'Uploading...' : 'Upload File' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios'
import Swal from 'sweetalert2'

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import TextInput from '@/Components/TextInput.vue';
import * as XLSX from 'xlsx';

const toast = useToast();

const props = defineProps({
    products: {
        type: Array,
        required: true
    },
    facilityTypes: {
        type: Array,
        required: true
    }
});

// Define facility type options with 'All' option and database values
const facilityTypeOptions = computed(() => {
    const options = ['➕ Add new type','All', ...props.facilityTypes];
    return options;
});

const form = ref({
    products: [],
    facility_types: []
});

const showModal = ref(false);
const creatingFacilityType = ref(false);
const newFacilityType = ref({
    name: ''
});

// Excel upload variables
const showUploadModal = ref(false);
const isUploading = ref(false);
const selectedFile = ref(null);
const fileInput = ref(null);

const handleFacilityTypeSelect = (selected) => {
    if (selected === '➕ Add new type') {
        showModal.value = true;
        newFacilityType.value = { name: '' };
        // Remove the "Add new type" option from the selected values
        const index = form.value.facility_types.indexOf('➕ Add new type');
        if (index > -1) {
            form.value.facility_types.splice(index, 1);
        }
    } else if (selected === 'All') {
        // If "All" is selected, clear other selections
        form.value.facility_types = ['All'];
    } else {
        // If a specific facility type is selected, remove "All" if it exists
        const allIndex = form.value.facility_types.indexOf('All');
        if (allIndex > -1) {
            form.value.facility_types.splice(allIndex, 1);
        }
    }
};

const closeModal = () => {
    showModal.value = false;
};

const createFacilityType = async () => {
    if (!newFacilityType.value.name) {
        toast.error('Facility type name cannot be empty.');
        return;
    }

    creatingFacilityType.value = true;

    await axios.post(route('products.facility-types.store'), newFacilityType.value)
        .then((response) => {
            console.log(response.data);
            creatingFacilityType.value = false;
            form.value.facility_types.push(response.data);
            newFacilityType.value.name = response.data;
            props.facilityTypes.push(response.data);
            closeModal();
        })
        .catch((error) => {
            creatingFacilityType.value = false;

            toast.error(error.response.data);
        });
};

// Excel upload functions
const openUploadModal = () => {
    showUploadModal.value = true;
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Check file type - allow .xlsx, .xls and .csv
    const fileExtension = "." + file.name.split(".").pop().toLowerCase();
    const validExtensions = [".xlsx", ".xls", ".csv"];

    if (!validExtensions.includes(fileExtension)) {
        toast.error(
            "Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file (.csv)"
        );
        event.target.value = null; // Clear the file input
        selectedFile.value = null;
        return;
    }

    // Check file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        toast.error("File is too large. Maximum file size is 5MB.");
        event.target.value = null;
        selectedFile.value = null;
        return;
    }

    selectedFile.value = file;
};

const removeSelectedFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const uploadFile = async () => {
    if (!selectedFile.value) {
        toast.error("Please select a file to upload");
        return;
    }

    // Show loading toast
    const loadingToast = toast.info("Preparing to upload file...", {
        timeout: false,
        closeOnClick: false,
        draggable: false,
    });

    isUploading.value = true;
    const formData = new FormData();
    formData.append("file", selectedFile.value);

    await axios.post(
        route("products.eligible.import"),
        formData,
    )
    .then(response => {
        isUploading.value = false;
        console.log('Upload response:', response.data);
        closeUploadModal();
        toast.dismiss(loadingToast);
        toast.success("File uploaded successfully! Processing in background.");
        
        // Refresh the page after a short delay to show new data
        setTimeout(() => {
            router.reload();
        }, 2000);
    })
    .catch(error => {
        isUploading.value = false;
        console.error('Upload error:', error);
        closeUploadModal();
        toast.dismiss(loadingToast);
        toast.error(error.response?.data?.message || "Failed to upload file");
    });
};

// Download template function
const downloadTemplate = () => {
    // Create workbook and worksheet
    const workbook = XLSX.utils.book_new();
    
    // Define headers with proper formatting (no underscores)
    const headers = ['Item Description', 'Facility Type'];
    
    // Create worksheet data with only headers (no demo data)
    const worksheetData = [
        headers
    ];
    
    // Create worksheet
    const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Eligible Items');
    
    // Generate XLSX file
    const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
    
    // Create blob and download
    const blob = new Blob([excelBuffer], { 
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'eligible_items_import_template.xlsx');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up the URL object
    URL.revokeObjectURL(url);
    
    toast.success('Excel template downloaded successfully!');
};

// Smart addItem function - checks if product_id is not null before adding
function addProductToList() {
    // Check if there are any existing products with null product_id
    const hasEmptyProduct = form.value.products.some(item => item.product_id === null);
    
    if (hasEmptyProduct) {
        return;
    }
    
    // Add new product row
    form.value.products.push({
        product_id: null,
        product: null
    });
}

function removeProduct(index) {
    form.value.products.splice(index, 1);
}

function handleSelectProduct(index, selected) {
    if (selected) {
        form.value.products[index].product_id = selected.id;
        form.value.products[index].product = selected;
        
        // Automatically add a new product row after selection
        addProductToList();
    }
}

const processing = ref(false);

const submit = async () => {
    if (form.value.facility_types.length === 0) {
        toast.error('Please select at least one facility type');
        return;
    }
    
    // Filter out products with null or empty product_id
    const validProducts = form.value.products.filter(item => item.product_id !== null && item.product_id !== '');
    
    if (validProducts.length === 0) {
        toast.error('Please add at least one product');
        return;
    }

    processing.value = true;
    
    // Create a copy of form data with only valid products
    const submitData = {
        ...form.value,
        products: validProducts
    };
    
    await axios.post(route('products.eligible.store'), submitData)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Eligible items created successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit(route('products.eligible.index'));
            });     
        })
        .catch((error) => {
            processing.value = false;
            toast.error(error.response?.data || 'An error occurred');
        });
};

// Auto-trigger addItem on component mount
onMounted(() => {
    addProductToList();
});
</script>
