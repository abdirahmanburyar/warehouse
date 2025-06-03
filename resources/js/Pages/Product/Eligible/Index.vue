<template>
    <AuthenticatedLayout title="Eligible Items" description="Manage product eligibility for facilities">
        <!-- Import Section -->
        <div class="mb-4 p-4 bg-white shadow rounded-lg">
            <h3 class="text-lg font-medium mb-2">Import Eligible Items</h3>
            <div class="flex items-center space-x-4">
                <input
                    type="file"
                    id="importFile"
                    accept=".xlsx,.xls"
                    @change="handleFileUpload"
                    class="border rounded px-2 py-1"
                />
                <button
                    @click="submitImport"
                    :disabled="importing || !selectedFile"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                >
                    {{ importing ? 'Importing...' : 'Import' }}
                </button>
            </div>
            <div v-if="importError" class="mt-2 text-red-500">{{ importError }}</div>
            <div v-if="importSuccess" class="mt-2 text-green-500">{{ importSuccess }}</div>
            <div class="mt-2 text-sm text-gray-600">
                Upload an Excel file (.xlsx/.xls) with columns: item_description, facility_type
            </div>
        </div>
        <div class="flex justify-between items-center">
            <div>
                <Link :href="route('products.eligible.index')">Back to Product</Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Eligible Items</h2>
            </div>
            <div class="flex space-x-2">
                <!-- Import Excel Button -->
                <label for="excel-upload" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Import Excel
                </label>
                <input type="file" id="excel-upload" accept=".xlsx, .xls" class="hidden" @change="handleExcelUpload" />
                
                <!-- Create Button -->
                <Link
                    :href="route('products.eligible.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Eligible Item
                </Link>
            </div>
        </div>
        <div class="flex items-center justify-between mt-3">
            <input
                v-model="search"
                type="text"
                placeholder="Search..."
                class="w-full"
            />
            <div class="w-[400px] ml-3">
            <Multiselect
             v-model="facilityType"
             :options="facilityTypes"
             :close-on-select="false"
             :searchable="true"
             :show-labels="true"
             placeholder="Select Facility Type"
            
         />
           </div>
            <div class="ml-3">
                <select
                 v-model="perPage"
                 class="w-[200px]"
             >
                 <option value="10">10 per page</option>
                 <option value="25">25 per page</option>
                 <option value="50">50 per page</option>
                 <option value="100">100 per page</option>
             </select>
            </div>
        </div>

        <div class="py-6">
            <table class="w-full text-sm text-left text-black">
                <thead >
                    <tr>
                        <th class="p-4 border border-black">
                            Item Name
                        </th>
                        <th class="p-4 border border-black">
                            Facility Type
                        </th>
                        <th class="p-4 border border-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in eligibleItems.data" :key="item.id">
                        <td class="p-4 border border-black">
                            {{ item.product?.name }}
                        </td>
                        <td class="p-4 border border-black">{{ item.facility_type }}</td>
                        <td class="p-4 border border-black">
                            <div class="flex items-center space-x-3">
                                <Link :href="route('products.eligible.edit', item.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button @click="destroy(item)" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="eligibleItems.data.length === 0">
                        <td colspan="4" class="p-4 border border-black text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                No eligible items found
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-3 flex justify-end mb-[100px]">
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { useToast } from 'vue-toastification';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import * as XLSX from 'xlsx';

const toast = useToast();

// Import form handling
const selectedFile = ref(null);
const importing = ref(false);
const importError = ref('');
const importSuccess = ref('');

const props = defineProps({
    eligibleItems: Object,
    filters: Object
});

const handleFileUpload = (e) => {
    selectedFile.value = e.target.files[0];
};

const submitImport = async () => {
    const maxFileSize = 50 * 1024 * 1024; // 50MB
    if (!selectedFile.value) {
        importError.value = 'Please select a file to import';
        return;
    }

    if (selectedFile.value.size > maxFileSize) {
        importError.value = 'File size must be less than 50MB';
        return;
    }

    // Validate file type
    if (!['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'].includes(selectedFile.value.type)) {
        importError.value = 'Please select a file to import';
        return;
    }

    importing.value = true;
    importError.value = '';
    importSuccess.value = '';

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await axios.post(route('products.eligible.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        importSuccess.value = response.data.message;
        selectedFile.value = null;
        // Reset file input
        document.getElementById('importFile').value = '';
        toast.success('Import started successfully');
toast.info('Processing in background. This may take a few minutes for large files.');
    } catch (error) {
        console.error('Import error:', error);        
        importError.value = error.response?.data?.message || 'Import failed';
        toast.error(importError.value);
    } finally {
        importing.value = false;
    }
};

const facilityTypes = ["Regional Hospital", "District Hospital", "Health Centre", "Primary Health Unit"]

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || '10');
const facilityType = ref(props.filters.facility_type || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');

function getResults(page = 1){
    props.filters.page = page;
}

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (facilityType.value) query.facility_type = facilityType.value;
    if (perPage.value){
        query.page = 1;
        query.per_page = perPage.value;
    }
    if(props.filters.page) query.page = props.filters.page;

    router.get(route('products.eligible.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "eligibleItems"
        ]
    });
}

watch([
    () => search.value, 
    () => perPage.value, 
    () => props.filters.page,
    () => facilityType.value], 
    () => {
        updateRoute();
    }
);

const isDeleteing = ref(false);
const isImporting = ref(false);
function destroy(item) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isDeleteing.value = true;
            await axios.get(route('products.eligible.destroy', item.id))
                .then(() => {
                    isDeleteing.value = false;
                    toast.success('Eligible item deleted successfully');
                })
                .catch(() => {
                    isDeleteing.value = false;
                    toast.error('Error deleting eligible item');
                });
        }
    });
}

async function handleExcelUpload(event) {
    try {
        isImporting.value = true;
        const file = event.target.files[0];
        
        if (!file) {
            toast.error('Please select a file');
            isImporting.value = false;
            return;
        }

        // Check file type
        const fileType = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(fileType)) {
            toast.error('Please upload a valid Excel file (.xlsx or .xls)');
            isImporting.value = false;
            event.target.value = null;
            return;
        }

        // Confirm import
        const result = await Swal.fire({
            title: 'Import Eligible Items',
            text: 'Are you sure you want to import this file?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, import!'
        });

        if (!result.isConfirmed) {
            isImporting.value = false;
            event.target.value = null;
            return;
        }

        // Create FormData and append file
        const formData = new FormData();
        formData.append('file', file);

        // Send to backend
        const response = await axios.post(route('products.eligible.import'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        toast.success(response.data.message);
        toast.info('Processing in background. Page will refresh in 10 seconds.');
        
        setTimeout(() => {
            updateRoute();
        }, 10000);

    } catch (error) {
        console.error('Import error:', error);
        const errorMessage = error.response?.data?.message || error.message || 'Failed to import eligible items';
        toast.error(errorMessage);
    } finally {
        isImporting.value = false;
        event.target.value = null;
    }

}
</script>
