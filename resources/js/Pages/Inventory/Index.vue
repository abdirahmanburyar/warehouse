<script setup>
import {
    ref,
    watch,
    computed,
    onMounted,
    onBeforeUnmount,
} from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import moment from "moment";
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    inventories: Object,
    products: Array,
    dosage: Array,
    category: Array,
    warehouses: Array,
    filters: Object,
    inventoryStatusCounts: Object,
});

const toast = useToast();

// Search and filter states
const search = ref(props.filters.search);
const location = ref(props.filters.location);
const dosage = ref(props.filters.dosage);
const category = ref(props.filters.category);
const warehouse = ref(props.filters.warehouse);
const per_page = ref(props.filters.per_page || 25);
const loadedLocation = ref([]);
const isSubmitting = ref(false);

// Modal states
const showAddModal = ref(false);
const showLegend = ref(false);

// Upload states
const showUploadModal = ref(false);
const selectedFile = ref(null);
const fileInput = ref(null);
const isUploading = ref(false);
const uploadProgress = ref(0);
const uploadResults = ref(null);
const importId = ref(null);

async function loadLocations() {
    if (!warehouse.value) {
        loadedLocation.value = [];
        location.value = '';
        return;
    };
    await axios
        .post(route("invetnories.getLocations"), {
            warehouse: warehouse.value,
        })
        .then((response) => {
            loadedLocation.value = response.data;
        })
        .catch((error) => {
            toast.error(error.response.data);
        });
}

// Form states
const form = ref({
    product_id: null,
    product: null,
    warehouse: "",
    quantity: 0,
    manufacturing_date: "",
    batch_number: "",
    location: "",
    notes: "",
    is_active: true,
});

// Set up real-time inventory updates
let echoChannel = null;

onMounted(() => {
    // Listen for inventory updates
    echoChannel = window.Echo.channel("inventory").listen(
        ".refresh",
        (data) => {
            console.log("[PUSHER-DEBUG] Received inventory update:", data);
            applyFilters();
        }
    );

    // Listen for import progress updates
    if (importId.value) {
        Echo.private(`import-progress.${importId.value}`)
            .listen('.ImportProgressUpdated', (e) => {
                console.log('Import progress:', e);
                uploadProgress.value = e.progress || 0;
                if (e.completed) {
                    isUploading.value = false;
                    uploadResults.value = e;
                    toast.success('Import completed successfully!');
                    applyFilters();
                }
            });
    }
});

onBeforeUnmount(() => {
    // Clean up Echo listeners when component is unmounted
    if (echoChannel) {
        echoChannel.stopListening(".refresh");
    }
});

// Apply filters
const applyFilters = () => {
    const query = {};
    // Add all filter values to query object
    if (search.value) query.search = search.value;
    if (location.value) query.location = location.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (dosage.value) query.dosage = dosage.value;
    if (category.value) query.category = category.value;

    // Always include per_page in query if it exists
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("inventories.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "inventories",
            "products",
            "warehouses",
            "inventoryStatusCounts",
            "locations",
            "dosage",
            "category",
        ],
    });
};

// Watch for changes in search input
watch(
    [
        () => search.value,
        () => location.value,
        () => per_page.value,
        () => warehouse.value,
        () => dosage.value,
        () => category.value,
        () => props.filters.page,
    ],
    () => {
        applyFilters();
    }
);

// Upload modal functions
const openUploadModal = () => {
    showUploadModal.value = true;
};

const closeUploadModal = () => {
    showUploadModal.value = false;
    selectedFile.value = null;
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
    uploadProgress.value = 0;
    const formData = new FormData();
    formData.append("file", selectedFile.value);

    await axios.post(
        route("inventories.import"),
        formData,
        {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            },
        }
    )
    .then(response => {
        isUploading.value = false;
        importId.value = response.data.import_id;
        uploadResults.value = response.data;
        console.log('Upload response:', response.data);
        toast.dismiss(loadingToast);
        toast.success(response.data.message || "File uploaded successfully!");
        
        // Refresh inventory data
        applyFilters();
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
    // Create a CSV format that Excel can open properly
    const headers = ['Item', 'Category', 'UoM', 'Quantity', 'Batch No', 'Expiry Date', 'Location'];
    
    // Create CSV content with headers
    const csvContent = headers.join(',') + '\n';
    
    // Create blob with CSV MIME type
    const blob = new Blob([csvContent], { 
        type: 'text/csv;charset=utf-8;' 
    });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', 'inventory_import_template.csv');
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up the URL object
    URL.revokeObjectURL(url);
    
    toast.success('Template downloaded successfully! Open with Excel to use.');
};

// Submit form
const submitForm = async () => {
    isSubmitting.value = true;

    await axios
        .post(route("inventories.store"), form.value)
        .then(() => {
            showAddModal.value = false;
            toast("Inventory item added successfully", "success");
            isSubmitting.value = false;
            applyFilters();
        })
        .catch((errors) => {
            console.log(errors);
            formErrors.value = errors;
            isSubmitting.value = false;
            toast.error(errors.response.data);
        });
};

// Format date
const formatDate = (date) => {
    if (!date) return "N/A";
    return moment(date).format("DD/MM/YYYY");
};

// Check if inventory is low
const isLowStock = (inventory) => {
    return (
        inventory.quantity > 0 && inventory.quantity <= inventory.reorder_level
    );
};

// Check if inventory is out of stock
const isOutOfStock = (inventory) => {
    return inventory.quantity <= 0;
};

// Computed properties for inventory status counts
const inStockCount = computed(() => {
    const stat = Object.values(props.inventoryStatusCounts).find(
        (s) => s.status === "in_stock"
    );
    return stat.count;
});

const lowStockCount = computed(() => {
    const stat = Object.values(props.inventoryStatusCounts).find(
        (s) => s.status === "low_stock"
    );
    return stat.count;
});

const outOfStockCount = computed(() => {
    const stat = Object.values(props.inventoryStatusCounts).find(
        (s) => s.status === "out_of_stock"
    );
    return stat.count;
});

function getResults(page = 1) {
    props.filters.page = page;
}
</script>

<template>
    <Head title="Inventory Management" />

    <AuthenticatedLayout
        img="/assets/images/inventory.png"
        title="Management Your Inventory"
        description="Keeping Essentials Ready, Every Time"
    >
        <div class="mb-[100px]">
            <!-- Header & Actions -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Warehouse Inventory</h1>
                <div class="flex flex-wrap gap-2 md:gap-4 items-center">
                    <button
                        @click="openUploadModal"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 shadow-sm"
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
                    <button @click="showAddModal = true" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-sm">Add Inventory</button>
                    <Link :href="route('inventories.location.index')" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Locations List
                    </Link>
                    <Link :href="route('inventories.warehouses.index')" class="inline-flex items-center px-4 py-2 bg-blue-100 border border-blue-200 rounded-lg font-semibold text-xs text-blue-700 uppercase tracking-widest hover:bg-blue-200 focus:bg-blue-200 active:bg-blue-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Warehouses List
                    </Link>
                </div>
            </div>
            <!-- Filters Card -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-4 border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div class="col-span-1 md:col-span-2 min-w-0">
                        <input v-model="search" type="text" class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2" placeholder="Search by item name, barcode, batch number, uom" />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <Multiselect v-model="category" :options="props.category" :searchable="true" :close-on-select="true" :show-labels="false" placeholder="Select a category" :allow-empty="true" class="multiselect--with-icon w-full" />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <Multiselect v-model="dosage" :options="props.dosage" :searchable="true" :close-on-select="true" :show-labels="false" placeholder="Select a dosage form" :allow-empty="true" class="multiselect--with-icon w-full" />
                    </div>
                </div>
                <div class="flex justify-end items-center gap-4 mt-3">
                    <select v-model="per_page" class="rounded-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-[200px] mb-3" @change="props.filters.page = 1">
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                        <option value="200">200 per page</option>
                    </select>
                    <button @click="showLegend = true" class="px-2 py-2 bg-blue-100 text-blue-700 rounded-full flex items-center gap-2 hover:bg-blue-200 transition-colors border border-blue-200 mb-3" title="Icon Legend">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                    </button>
                </div>
            </div>
            <!-- Table and Sidebar (do not change table markup) -->
            <div class="lg:grid lg:grid-cols-8 lg:gap-2">
                <div class="lg:col-span-7 overflow-auto w-full">
                    <!-- Inventory Table -->
                    <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                        <thead>
                            <tr style="background-color: #F4F7FB;">
                                <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Item</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Category</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">UoM</th>
                                <th class="px-3 py-2 text-xs font-bold text-center" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" colspan="4">Item Details</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Total QTY on Hand</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Reorder Level</th>
                                <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Status</th>
                                <th class="px-3 py-2 text-xs font-bold rounded-tr-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;" rowspan="2">Actions</th>
                            </tr>
                            <tr style="background-color: #F4F7FB;">
                                <th class="px-2 py-1 text-xs font-bold border border-[#B7C6E6] text-center" style="color: #4F6FCB;">QTY</th>
                                <th class="px-2 py-1 text-xs font-bold border border-[#B7C6E6] text-center" style="color: #4F6FCB;">Batch Number</th>
                                <th class="px-2 py-1 text-xs font-bold border border-[#B7C6E6] text-center" style="color: #4F6FCB;">Expiry Date</th>
                                <th class="px-2 py-1 text-xs font-bold border border-[#B7C6E6] text-center" style="color: #4F6FCB;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="props.inventories.data.length === 0">
                                <tr>
                                    <td colspan="11" class="text-center py-8 text-gray-500 bg-gray-50">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 118 0v2m-4 4a4 4 0 01-4-4H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-2a4 4 0 01-4 4z" /></svg>
                                            <span>No inventory data found.</span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template v-else v-for="inventory in props.inventories.data" :key="inventory.id">
                                <tr v-for="(item, itemIndex) in inventory.items" :key="`${inventory.id}-${item.id}`" class="hover:bg-gray-50 transition-colors duration-150 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs font-medium text-gray-800 align-top">{{ inventory.product.name }}</td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs text-gray-700 align-top">{{ inventory.product.category.name }}</td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs text-gray-700 align-top">{{ inventory.items[0].uom }}</td>
                                    <td class="px-2 py-1 text-xs border-b border-[#B7C6E6] text-center" :class="isOutOfStock(item) ? 'text-red-600 font-medium' : 'text-gray-900'">{{ item.quantity }}</td>
                                    <td class="px-2 py-1 text-xs border-b border-[#B7C6E6] text-center" :class="isOutOfStock(item) ? 'text-red-600 font-medium' : 'text-gray-900'">{{ item.batch_number }}</td>
                                    <td class="px-2 py-1 text-xs border-b border-[#B7C6E6] text-center" :class="isOutOfStock(item) ? 'text-red-600 font-medium' : 'text-gray-900'">{{ formatDate(item.expiry_date) }}</td>
                                    <td class="px-2 py-1 text-xs border-b border-[#B7C6E6] text-center">
                                        <div class="flex items-center justify-center">
                                            <div v-if="isOutOfStock(item)" class="mr-1">
                                                <img src="/assets/images/out_stock.png" title="Out of Stock" class="w-5 h-5" alt="Out of Stock" />
                                            </div>
                                        </div>
                                    </td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs text-gray-800 align-top">{{ inventory.items.reduce((sum, item) => sum + item.quantity, 0) }}</td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs text-gray-800 align-top">{{ inventory.reorder_level }}</td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-2 text-xs text-gray-800 align-top">
                                        <div class="flex items-center space-x-2">
                                            <div v-if="isLowStock(inventory)" class="flex items-center">
                                                <img
                                                    src="/assets/images/low_stock.png"
                                                    title="Low Stock"
                                                    class="w-6 h-6"
                                                    alt="Low Stock"
                                                />
                                            </div>

                                            <div v-if="isOutOfStock(inventory)" class="flex items-center">
                                                <img
                                                    src="/assets/images/out_stock.png"
                                                    title="Out of Stock"
                                                    class="w-6 h-6"
                                                    alt="Out of Stock"
                                                />
                                            </div>
                                            <div
                                                v-if="!isLowStock(inventory) && !isOutOfStock(inventory)"
                                                class="flex items-center"
                                            >
                                                <img
                                                    src="/assets/images/in_stock.png"
                                                    title="In Stock"
                                                    class="w-6 h-6"
                                                    alt="In Stock"
                                                />
                                            </div>
                                        </div>
                                    </td>
                                    <td v-if="itemIndex === 0" :rowspan="inventory.items.length" class="px-3 py-4 whitespace-nowrap text-xs font-medium align-top">
                                        <div class="flex items-center space-x-3">
                                            <div v-if="isLowStock(inventory)">
                                                <img
                                                    src="/assets/images/reorder_status.png"
                                                    alt="Reorder Status"
                                                    class="w-6 h-6"
                                                    title="Reorder Status"
                                                />
                                            </div>
                                            <Link
                                                :href="route('supplies.purchase_order')"
                                                v-if="inventory.quantity > inventory.reorder_level"
                                                class="p-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-full"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                                    />
                                                </svg>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="mt-2 flex justify-end">
                        <TailwindPagination
                            :data="props.inventories"
                            @pagination-change-page="getResults"
                            :limit="2"
                        />
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-0 z-10 shadow-sm">
                        <div class="space-y-4">
                            <div class="flex items-center rounded-xl bg-green-50 p-1 shadow">
                                <img src="/assets/images/in_stock.png" class="w-[40px] h-[40px]" alt="In Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-sm font-bold text-green-600">{{ inStockCount }}</span>
                                    <span class="ml-2 text-xs text-green-600">In Stock</span>
                                </div>
                            </div>
                            <div class="flex items-center rounded-xl bg-orange-50 p-1 shadow">
                                <img src="/assets/images/low_stock.png" class="w-[40px] h-[40px]" alt="Low Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-sm font-bold text-orange-600">{{ lowStockCount }}</span>
                                    <span class="ml-2 text-xs text-orange-600">Low Stock</span>
                                </div>
                            </div>
                            <div class="flex items-center rounded-xl bg-red-50 p-1 shadow">
                                <img src="/assets/images/out_stock.png" class="w-[40px] h-[40px]" alt="Out of Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-sm font-bold text-red-600">{{ outOfStockCount }}</span>
                                    <span class="ml-2 text-xs text-red-600">Out of Stock</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals: Add Inventory, Upload Progress, Icon Legend -->
        <!-- Add Inventory Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-8 rounded-xl shadow-xl bg-white">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Add New Inventory Item</h2>
                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                            <Multiselect v-model="form.product" :options="props.products" :searchable="true" :close-on-select="true" :show-labels="false" placeholder="Select a product" track-by="id" label="name" class="multiselect--with-icon w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                            <input v-model="form.quantity" type="number" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <input v-model="form.batch_number" type="text" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturing Date</label>
                            <input v-model="form.manufacturing_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <SecondaryButton @click="showAddModal = false">Cancel</SecondaryButton>
                        <button type="submit" :disabled="isSubmitting" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50">{{ isSubmitting ? 'Adding...' : 'Add Item' }}</button>
                    </div>
                </form>
            </div>
        </Modal>
        <!-- Excel Upload Modal -->
        <div
            v-if="showUploadModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click="closeUploadModal"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Upload Inventory</h3>
                        <p class="text-sm text-gray-500 mt-1">Import inventory items from Excel file</p>
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
                                    Download our template to see the correct format for uploading inventory items.
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

                    <!-- Upload Progress -->
                    <div v-if="isUploading" class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Upload Progress</h4>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
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
                                <p v-if="uploadResults.completed_at">Completed at: {{ formatDate(uploadResults.completed_at) }}</p>
                            </div>
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
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 border border-transparent rounded-lg font-medium text-sm text-white hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200"
                        :disabled="!selectedFile || isUploading"
                    >
                        <svg v-if="isUploading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        {{ isUploading ? 'Uploading...' : 'Upload File' }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Slideover for Icon Legend -->
        <transition name="slide">
          <div v-if="showLegend" class="fixed inset-0 z-50 flex justify-end">
            <div class="fixed inset-0 bg-black bg-opacity-30 transition-opacity" @click="showLegend = false"></div>
            <div class="relative w-full max-w-sm bg-white shadow-xl h-full flex flex-col p-6 overflow-y-auto rounded-l-xl">
              <button @click="showLegend = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
              </button>
              <h2 class="text-lg font-bold text-blue-700 mb-6 mt-2">Icon Legend</h2>
              <ul class="space-y-5">
                <li class="flex items-center gap-4">
                  <img src="/assets/images/in_stock.png" class="w-10 h-10" alt="In Stock" />
                  <div>
                    <div class="font-semibold text-green-700">In Stock</div>
                    <div class="text-xs text-gray-500">Indicates items that are sufficiently stocked.</div>
                  </div>
                </li>
                <li class="flex items-center gap-4">
                  <img src="/assets/images/low_stock.png" class="w-10 h-10" alt="Low Stock" />
                  <div>
                    <div class="font-semibold text-orange-600">Low Stock</div>
                    <div class="text-xs text-gray-500">Indicates items that are below the reorder level.</div>
                  </div>
                </li>
                <li class="flex items-center gap-4">
                  <img src="/assets/images/out_stock.png" class="w-10 h-10" alt="Out of Stock" />
                  <div>
                    <div class="font-semibold text-red-600">Out of Stock</div>
                    <div class="text-xs text-gray-500">Indicates items that are completely out of stock.</div>
                  </div>
                </li>
                <li class="flex items-center gap-4">
                  <img src="/assets/images/reorder_status.png" class="w-10 h-10" alt="Reorder Status" />
                  <div>
                    <div class="font-semibold text-blue-600">Reorder Status</div>
                    <div class="text-xs text-gray-500">Indicates that a reorder is recommended for this item.</div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </transition>
    </AuthenticatedLayout>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(100%);
}
.slide-enter-to, .slide-leave-from {
  transform: translateX(0);
}
</style>
