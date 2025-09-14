<script setup>
import { ref, watch, computed, onUnmounted } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useToast } from "vue-toastification";
import moment from "moment";

const toast = useToast();

const props = defineProps({
    nonApprovedInventories: Array,
    selectedInventory: Object,
    categories: Array,
    dosages: Array,
    filters: Object,
});

// Selected inventory state
const selectedInventoryId = ref(props.filters?.inventory_id || '');
const search = ref(props.filters?.search || '');
const category_id = ref(props.filters?.category_id || '');
const dosage_id = ref(props.filters?.dosage_id || '');

const isLoading = ref(false);
const filterTimeout = ref(null);

// File upload state
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadFile = ref(null);
const showUploadModal = ref(false);
const importId = ref(null);
const progressInterval = ref(null);
const uploadResults = ref(null);
const fileInput = ref(null);

// Apply filters with debouncing
const applyFilters = () => {
    if (filterTimeout.value) {
        clearTimeout(filterTimeout.value);
    }

    filterTimeout.value = setTimeout(() => {
        const query = {};
        
        if (selectedInventoryId.value && selectedInventoryId.value !== '') query.inventory_id = selectedInventoryId.value;
        if (search.value && search.value.trim()) query.search = search.value.trim();
        if (category_id.value && category_id.value !== '') query.category_id = category_id.value;
        if (dosage_id.value && dosage_id.value !== '') query.dosage_id = dosage_id.value;

        isLoading.value = true;

        router.get(route("inventories.moh-inventory.index"), query, {
            preserveState: true,
            preserveScroll: true,
            only: ["nonApprovedInventories", "selectedInventory", "categories", "dosages"],
            onFinish: () => {
                isLoading.value = false;
            },
        });
    }, 300);
};

// Watch for changes in filter values
watch([selectedInventoryId, search, category_id, dosage_id], applyFilters);

// Clear filters
const clearFilters = () => {
    selectedInventoryId.value = '';
    search.value = '';
    category_id.value = '';
    dosage_id.value = '';
};

// File upload methods
const openUploadModal = () => {
    showUploadModal.value = true;
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    uploadFile.value = null;
    uploadProgress.value = 0;
    uploadResults.value = null;
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

    // Check file type - allow .xlsx and .csv
    const fileExtension = "." + file.name.split(".").pop().toLowerCase();
    const validExtensions = [".xlsx", ".csv"];

    if (!validExtensions.includes(fileExtension)) {
        toast.error(
            "Invalid file type. Please upload an Excel file (.xlsx) or CSV file (.csv)"
        );
        event.target.value = null; // Clear the file input
        uploadFile.value = null;
        return;
    }

    // Check file size (max 5MB)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        toast.error("File is too large. Maximum file size is 5MB.");
        event.target.value = null;
        uploadFile.value = null;
        return;
    }

    uploadFile.value = file;
};

const removeSelectedFile = () => {
    uploadFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const uploadExcelFile = async () => {
    if (!uploadFile.value) {
        toast.error('Please select a file to upload');
        return;
    }

    const formData = new FormData();
    formData.append('file', uploadFile.value);
    
    // If a MOH inventory is selected, include its ID
    if (selectedInventoryId.value) {
        formData.append('moh_inventory_id', selectedInventoryId.value);
    }

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const response = await fetch(route('inventories.moh-inventory.import'), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        const data = await response.json();

        if (data.success) {
            toast.success(data.message);
            importId.value = data.import_id;
            
            // Start polling for progress
            startProgressPolling();
            
            // Close modal
            showUploadModal.value = false;
            uploadFile.value = null;
            
            // Refresh the page after a short delay to show new data
            setTimeout(() => {
                applyFilters();
            }, 2000);
        } else {
            toast.error(data.message);
            // Show detailed error message
            if (data.message.includes('not found in database')) {
                toast.error('Import failed: ' + data.message + ' Please add the missing items to the database first.');
            }
        }
    } catch (error) {
        console.error('Upload error:', error);
        toast.error('Upload failed. Please try again.');
    } finally {
        isUploading.value = false;
    }
};

const startProgressPolling = () => {
    if (progressInterval.value) {
        clearInterval(progressInterval.value);
    }

    progressInterval.value = setInterval(async () => {
        try {
            const response = await fetch(`${route('inventories.moh-inventory.import-progress')}?import_id=${importId.value}`);
            const data = await response.json();

            if (data.success) {
                uploadProgress.value = data.progress;
                
                if (data.completed) {
                    clearInterval(progressInterval.value);
                    toast.success('Import completed successfully!');
                    uploadProgress.value = 0;
                    importId.value = null;
                }
            }
        } catch (error) {
            console.error('Progress polling error:', error);
            clearInterval(progressInterval.value);
        }
    }, 1000);
};

const cancelUpload = () => {
    showUploadModal.value = false;
    uploadFile.value = null;
    isUploading.value = false;
    uploadProgress.value = 0;
    
    if (progressInterval.value) {
        clearInterval(progressInterval.value);
        progressInterval.value = null;
    }
};

// Download template function
const downloadTemplate = () => {
    // Create a CSV format that Excel can open properly
    const headers = ['Item', 'Category', 'UoM', 'Source', 'Quantity', 'Batch No', 'Expiry Date', 'Location', 'Warehouse', 'Unit Cost'];
    
    // Create sample data
    const sampleData = [
        ['Acetylsalicylic acid (aspirin)75mg tab', 'Drugs', 'Pcs', 'UNICEF', '2300', 'ADF345342', '20/02/2028', 'W1-R1-C1-R1', 'Nugal Regional Main Warehouse', '0.4']
    ];
    
    // Combine headers and sample data
    const csvContent = [headers, ...sampleData]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n');
    
    // Create and download the file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'moh_inventory_template.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Format date helper
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return moment(dateString).format('MMM DD, YYYY HH:mm');
};

// Cleanup timeout on unmount
onUnmounted(() => {
    if (filterTimeout.value) {
        clearTimeout(filterTimeout.value);
    }
    if (progressInterval.value) {
        clearInterval(progressInterval.value);
    }
});

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount || 0);
};

// Get total quantity for a MOH inventory
const getTotalQuantity = (mohInventory) => {
    return mohInventory.moh_inventory_items?.reduce((total, item) => {
        return total + (item.quantity || 0);
    }, 0) || 0;
};

// Get total items count for a MOH inventory
const getTotalItems = (mohInventory) => {
    return mohInventory.moh_inventory_items?.length || 0;
};

// Check if MOH inventory has review/approval status
const getStatusInfo = (mohInventory) => {
    if (mohInventory.approved_at) {
        return { status: 'approved', color: 'green', text: 'Approved' };
    } else if (mohInventory.reviewed_at) {
        return { status: 'reviewed', color: 'blue', text: 'Reviewed' };
    } else {
        return { status: 'pending', color: 'yellow', text: 'Pending' };
    }
};

// Get unique products from MOH inventory items
const getUniqueProducts = (mohInventory) => {
    const products = mohInventory.moh_inventory_items?.map(item => item.product).filter(Boolean) || [];
    const uniqueProducts = products.filter((product, index, self) => 
        index === self.findIndex(p => p.id === product.id)
    );
    return uniqueProducts;
};

// Get total cost for a MOH inventory
const getTotalCost = (mohInventory) => {
    return mohInventory.moh_inventory_items?.reduce((total, item) => {
        return total + (item.total_cost || 0);
    }, 0) || 0;
};

// Filter inventory items based on search and filters
const filteredInventoryItems = computed(() => {
    if (!props.selectedInventory?.moh_inventory_items) return [];
    
    let items = props.selectedInventory.moh_inventory_items;
    
    // Apply search filter
    if (search.value && search.value.trim()) {
        const searchTerm = search.value.toLowerCase();
        items = items.filter(item => 
            item.product?.name?.toLowerCase().includes(searchTerm) ||
            item.product?.product_code?.toLowerCase().includes(searchTerm) ||
            item.batch_number?.toLowerCase().includes(searchTerm) ||
            item.barcode?.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply category filter
    if (category_id.value && category_id.value !== '') {
        items = items.filter(item => item.product?.category_id == category_id.value);
    }
    
    // Apply dosage filter
    if (dosage_id.value && dosage_id.value !== '') {
        items = items.filter(item => item.product?.dosage_id == dosage_id.value);
    }
    
    return items;
});
</script>

<template>
    <Head title="MOH Inventory" />

    <AuthenticatedLayout :title="'MOH Inventory'" :description="'Ministry of Health Inventory Management'">
        <div class="py-6">
            <!-- Header Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">MOH Inventory</h2>
                        <p class="text-gray-600">Select a non-approved MOH inventory to view its items</p>
                    </div>

                    <!-- MOH Inventory Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select MOH Inventory <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="selectedInventoryId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Choose a MOH inventory...</option>
                            <option v-for="inventory in nonApprovedInventories" :key="inventory.id" :value="inventory.id">
                                {{ inventory.uuid || `MOH-${inventory.id}` }} 
                                ({{ getTotalItems(inventory) }} items, {{ getTotalQuantity(inventory) }} total qty)
                                - {{ getStatusInfo(inventory).text }}
                            </option>
                        </select>
                    </div>

                    <!-- Upload Section -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Import MOH Inventory Items</h3>
                                <p class="text-sm text-gray-600">Upload an Excel file to import inventory items</p>
                            </div>
                            <div class="flex space-x-3">
                                <button
                                    @click="openUploadModal"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Upload Excel File
                                </button>
                            </div>
                        </div>
                        
                        <!-- Progress Bar (shown during upload) -->
                        <div v-if="isUploading || uploadProgress > 0" class="mt-4">
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                <span>Uploading and processing...</span>
                                <span>{{ uploadProgress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: uploadProgress + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters (only show when inventory is selected) -->
                    <div v-if="selectedInventory" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search Items</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search products, batch, barcode..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select
                                v-model="category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                            <select
                                v-model="dosage_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Dosages</option>
                                <option v-for="dosage in dosages" :key="dosage.id" :value="dosage.id">
                                    {{ dosage.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>

            <!-- Selected Inventory Details -->
            <div v-else-if="selectedInventory" class="space-y-6">
                <!-- Inventory Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Inventory Summary</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">UUID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ selectedInventory.uuid || `MOH-${selectedInventory.id}` }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ selectedInventory.date ? moment(selectedInventory.date).format('MMM DD, YYYY') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Items</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ getTotalItems(selectedInventory) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ getTotalQuantity(selectedInventory) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Cost</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ formatCurrency(getTotalCost(selectedInventory)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span 
                                        :class="`inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-${getStatusInfo(selectedInventory).color}-100 text-${getStatusInfo(selectedInventory).color}-800`"
                                    >
                                        {{ getStatusInfo(selectedInventory).text }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Items Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Inventory Items ({{ filteredInventoryItems.length }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UOM</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in filteredInventoryItems" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ item.product?.name || 'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ item.product?.product_code || 'N/A' }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ item.product?.category?.name || 'N/A' }} - {{ item.product?.dosage?.name || 'N/A' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.warehouse?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.uom || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.batch_number || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.expiry_date ? moment(item.expiry_date).format('MMM DD, YYYY') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.location || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.source || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(item.unit_cost) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(item.total_cost) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Empty State - No Inventory Selected -->
            <div v-else-if="!selectedInventory" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No MOH inventory selected</h3>
                <p class="mt-1 text-sm text-gray-500">Please select a MOH inventory from the dropdown above to view its items.</p>
            </div>

            <!-- Empty State - No Non-Approved Inventories -->
            <div v-else-if="nonApprovedInventories.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No non-approved MOH inventories</h3>
                <p class="mt-1 text-sm text-gray-500">All MOH inventories have been approved or there are no MOH inventories available.</p>
            </div>
        </div>

        <!-- Excel Upload Modal -->
        <div v-if="showUploadModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeUploadModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Upload MOH Inventory</h3>
                        <p class="text-sm text-gray-500 mt-1">Import MOH inventory items from Excel file</p>
                    </div>
                    <button @click="closeUploadModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Download Template Section -->
                    <div
                        class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-green-800">Need a template?</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Download our template to see the correct format for uploading MOH inventory items.
                                </p>
                                <button @click="downloadTemplate"
                                    class="mt-3 inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
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
                                    <span class="font-medium">Item</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Category</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">UoM</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Source</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Quantity</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Batch No</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Expiry Date</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Location</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Warehouse</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                                <li class="flex items-center">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full mr-3"></span>
                                    <span class="font-medium">Unit Cost</span>
                                    <span class="text-gray-400 ml-2">(required)</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800">
                                        <strong>Important:</strong> All products and warehouses must exist in the database before importing. The import will fail if any referenced product or warehouse is not found.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition-colors cursor-pointer"
                            @click="triggerFileInput">
                            <input type="file" ref="fileInput" class="hidden" @change="handleFileUpload"
                                accept=".xlsx,.xls,.csv" />
                            <svg class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">
                                {{ uploadFile ? 'File Selected' : 'Choose File' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ uploadFile ? uploadFile.name : 'Click to select or drag and drop file here' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">
                                Supports .xlsx, .xls, and .csv files (max 5MB)
                            </p>
                        </div>

                        <div v-if="uploadFile"
                            class="mt-4 flex items-center justify-between bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-500 mr-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ uploadFile.name }}</p>
                                    <p class="text-xs text-blue-700">{{ (uploadFile.size / 1024 / 1024).toFixed(2) }}
                                        MB</p>
                                </div>
                            </div>
                            <button @click.stop="removeSelectedFile"
                                class="text-red-500 hover:text-red-700 transition-colors duration-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div v-if="isUploading" class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Upload Progress</h4>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                :style="{ width: uploadProgress + '%' }"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">{{ uploadProgress }}% complete</p>
                    </div>

                    <!-- Upload Results -->
                    <div v-if="uploadResults && !isUploading" class="mb-6">
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <h3 class="text-sm font-medium text-green-800">Upload Results</h3>
                            <p class="text-sm text-green-700 mt-1">{{ uploadResults.message }}</p>
                            <div v-if="uploadResults.import_id" class="mt-2 text-xs text-gray-600">
                                <p>Import ID: {{ uploadResults.import_id }}</p>
                                <p v-if="uploadResults.status">Status: {{ uploadResults.status }}</p>
                                <p v-if="uploadResults.completed_at">Completed at: {{
                                    formatDate(uploadResults.completed_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button @click="closeUploadModal"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        Cancel
                    </button>
                    <button @click="uploadExcelFile"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200"
                        :disabled="!uploadFile || isUploading">
                        <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        {{ isUploading ? 'Uploading...' : 'Upload File' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
