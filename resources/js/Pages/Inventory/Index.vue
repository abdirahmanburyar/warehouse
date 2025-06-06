<script setup>
import {
    ref,
    watch,
    computed,
    reactive,
    onMounted,
    onBeforeUnmount,
} from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import Echo from "laravel-echo";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import { useToast } from "vue-toastification";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import moment from "moment";
import Swal from "sweetalert2";
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    inventories: Object,
    products: Array,
    dosage: Array,
    category: Array,
    warehouses: Array,
    filters: Object,
    inventoryStatusCounts: Array,
});

const toast = useToast();

// Search and filter states
const search = ref(props.filters.search || "");
const location = ref(props.filters.location || "");
const dosage = ref(props.filters.dosage || "");
const category = ref(props.filters.category || "");
const warehouse = ref(props.filters.warehouse || "");
const per_page = ref(props.filters.per_page || 6);
const loadedLocation = ref([]);
const isSubmitting = ref(false);

// Modal states
const showAddModal = ref(false);

watch([() => warehouse.value], () => {
    if (warehouse.value == null) {
        location.value = null;
        return;
    }
    loadLocations();
});
async function loadLocations() {
    console.log("warehouse:", warehouse.value);
    await axios
        .post(route("invetnories.getLocations"), {
            warehouse: warehouse.value,
        })
        .then((response) => {
            console.log(response.data);
            loadedLocation.value = response.data;
        })
        .catch((error) => {
            console.log(error);
            toast.error(error.response.data);
        });
}

// Form states
const form = ref({
    product_id: null,
    product: null,
    warehouse: "",
    quantity: 0,
    reorder_level: 10,
    manufacturing_date: "",
    expiry_date: "",
    batch_number: "",
    location: "",
    notes: "",
    is_active: true,
});

// File upload states
const uploadingFile = ref(false);
const uploadProgress = ref(0);
const showUploadModal = ref(false);
const uploadResults = ref(null);

const formErrors = ref({});

// Create reactive copy of inventory data for real-time updates
const currentInventories = reactive({
    data: [...props.inventories.data],
    meta: { ...props.inventories.meta },
});

// Add a watcher to update currentInventories when props.inventories changes
watch(
    () => props.inventories,
    (newInventories) => {
        console.log(
            "[PUSHER-DEBUG] Props updated, syncing with currentInventories"
        );
        currentInventories.data = [...newInventories.data];
        currentInventories.meta = { ...newInventories.meta };
    },
    { deep: true }
);

// Set up real-time inventory updates
let echoChannel = null;

onMounted(() => {
    // Listen for inventory updates
    echoChannel = window.Echo.channel("inventory").listen(
        ".refresh",
        (data) => {
            console.log("[PUSHER-DEBUG] Received inventory update:", data);

            // Show notification about the update
            toast.info(
                `Inventory updated: ${data.inventory.quantity} units of product #${data.inventory.product_id} received from backorder`
            );

            // If the current warehouse matches the updated inventory's warehouse, refresh the data
            if (
                !warehouse.value ||
                warehouse.value == data.inventory.warehouse_id
            ) {
                applyFilters();
            }
        }
    );
});

onBeforeUnmount(() => {
    // Clean up Echo listeners when component is unmounted
    if (echoChannel) {
        echoChannel.stopListening(".refresh");
    }
});

// Watch for product changes to update product_id
watch(
    () => form.value.product,
    (newProduct) => {
        if (newProduct && newProduct.id) {
            form.value.product_id = newProduct.id;
        } else {
            form.value.product_id = null;
        }
    },
    { deep: true }
);

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
    if(props.filters.page) query.page = props.filters.page;
    
    router.get(route("inventories.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "inventories",
            "products",
            "warehouses",
            "filters",
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

// Handle Excel file upload
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Reset the file input
    event.target.value = "";

    // Validate file type
    const validTypes = [
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "text/csv",
    ];
    if (!validTypes.includes(file.type)) {
        toast.error("Please upload a valid Excel file (.xlsx, .xls, .csv)");
        return;
    }

    // Validate file size (max 10MB)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        toast.error("File size exceeds the maximum limit of 10MB");
        return;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append("file", file);

    // Show upload modal
    uploadingFile.value = true;
    uploadProgress.value = 0;
    showUploadModal.value = true;

    // Upload file
    axios
        .post(route("inventories.import"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            },
        })
        .then((response) => {
            uploadingFile.value = false;
            uploadResults.value = response.data;
            toast.success(response.data.message);

            // Refresh inventory data
            applyFilters();
        })
        .catch((error) => {
            uploadingFile.value = false;
            toast.error(
                error.response?.data?.message || "Failed to upload file"
            );
            console.error("Upload error:", error);
        });
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

// Check if product is expiring soon (within 30 days)
const isExpiringSoon = (inventory) => {
    if (!inventory.expiry_date) return false;
    const expiryDate = moment(inventory.expiry_date);
    const today = moment();
    const diffDays = expiryDate.diff(today, "days");
    return diffDays <= 30 && diffDays > 0;
};

// Check if product is expired
const isExpired = (inventory) => {
    if (!inventory.expiry_date) return false;
    const expiryDate = moment(inventory.expiry_date);
    const today = moment();
    return expiryDate.isBefore(today);
};

// Computed properties for inventory status counts
const inStockCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(
        (s) => s.status === "in_stock"
    );
    return stat.count;
});

const lowStockCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(
        (s) => s.status === "low_stock"
    );
    return stat.count;
});

const outOfStockCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(
        (s) => s.status === "out_of_stock"
    );
    return stat.count;
});

const expiredCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(
        (s) => s.status === "expired"
    );
    return stat.count;
});

// Edit inventory item
const showEditModal = ref(false);

function editInventory(inventory) {
    form.value = {
        id: inventory.id,
        product: {
            id: inventory.product_id,
            name: inventory.product?.name,
            product_id: inventory.product?.id,
        },
        product_id: inventory.product_id,
        warehouse_id: inventory.warehouse_id,
        quantity: inventory.quantity,
        reorder_level: inventory.reorder_level,
        manufacturing_date: inventory.manufacturing_date,
        expiry_date: inventory.expiry_date,
        batch_number: inventory.batch_number,
        location: inventory.location || "",
        notes: inventory.notes || "",
        is_active: inventory.is_active,
    };
    showAddModal.value = true;
    showEditModal.value = true;
}

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
            <!-- Navigation Buttons and Heading -->
            <div class="flex justify-between items-center mb-6">
                <!-- Page Heading -->
                <h1 class="text-2xl font-bold text-gray-800">
                    Warehouse Inventory
                </h1>
                <div class="flex space-x-4">
                    <!-- Excel Upload Button -->
                    <label
                        for="excelUpload"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                        Upload Excel
                    </label>
                    <input
                        id="excelUpload"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="hidden"
                        @change="handleFileUpload"
                    />

                    <Link
                        :href="route('inventories.location.index')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        Locations List
                    </Link>
                    <Link
                        :href="route('inventories.warehouses.index')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            />
                        </svg>
                        Warehouses List
                    </Link>
                </div>
            </div>
            <!-- Search and Filters -->
            <div class="mb-1 flex justify-between flex-wrap items-center gap-4">
                <div class="flex-grow relative search-with-icon">
                    <label>Search</label>
                    <div class="relative">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="search-icon h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <TextInput
                            v-model="search"
                            type="text"
                            class="w-[400px]"
                            placeholder="Search by item name, barcode"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="w-[300px] relative">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Category</label
                        >
                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="filter-icon h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                />
                            </svg>
                            <Multiselect
                                v-model="category"
                                :options="props.category"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                placeholder="Select a category"
                                :allow-empty="true"
                                class="multiselect--with-icon multiselect--rounded"
                            >
                            </Multiselect>
                        </div>
                    </div>
                    <div class="w-[300px] relative">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Dosage Form</label
                        >
                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="filter-icon h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                />
                            </svg>
                            <Multiselect
                                v-model="dosage"
                                :options="props.dosage"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                placeholder="Select a dosage form"
                                :allow-empty="true"
                                class="multiselect--with-icon multiselect--rounded"
                            >
                            </Multiselect>
                        </div>
                    </div>
                    <div class="w-[300px] relative">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Warehouse</label
                        >
                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="filter-icon h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                            <Multiselect
                                v-model="warehouse"
                                :options="props.warehouses"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                placeholder="Select a warehouse"
                                :allow-empty="true"
                                class="multiselect--with-icon multiselect--rounded"
                            >
                            </Multiselect>
                        </div>
                    </div>
                    <div class="w-[300px] relative">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Storage Location</label
                        >
                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="filter-icon h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                            <Multiselect
                                v-model="location"
                                :options="loadedLocation"
                                :searchable="true"
                                :close-on-select="true"
                                :show-labels="false"
                                placeholder="Select a S. Location"
                                :allow-empty="true"
                                :disabled="warehouse == null"
                                class="multiselect--with-icon multiselect--rounded"
                            >
                            </Multiselect>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-3">
                <select
                    v-model="per_page"
                    class="rounded-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-[200px] mb-3"
                    @change="props.filters.page = 1"
                >
                    <option :value="6">6 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                    <option :value="200">200 per page</option>
                </select>
            </div>

            <!-- Add Button -->

            <div class="flex justify-between">
                <!-- Inventory Table -->
                <div class="overflow-auto w-full">
                    <table
                        class="min-w-full border border-gray-200 divide-y divide-gray-200 rounded-xl overflow-hidden"
                    >
                        <thead
                            style="background-color: #eef1f8"
                            class="rounded-t-xl overflow-hidden"
                        >
                            <tr>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Item Name
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Batch Number
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Quantity on Hand Per Unit
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Expiration Date
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Status/Alert
                                </th>
                                <th
                                    class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider"
                                    style="color: #636db4"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr
                                v-if="
                                    !currentInventories.data ||
                                    currentInventories.data.length === 0
                                "
                            >
                                <td colspan="10" class="px-3 py-16 text-center">
                                    <div
                                        class="flex flex-col items-center justify-center text-gray-500"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-12 w-12 mb-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                            />
                                        </svg>
                                        <span class="text-lg font-medium"
                                            >No inventory items found</span
                                        >
                                        <p class="text-sm text-gray-400 mt-1">
                                            Try adjusting your search or filters
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="inventory in currentInventories.data"
                                :key="inventory.id"
                                class="hover:bg-gray-50 border-b"
                            >
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div
                                        v-if="inventory.product"
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ inventory.product.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">
                                        Product not found
                                    </div>
                                </td>
                                <td
                                    class="px-3 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ inventory.batch_number }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            inventory.product?.category?.name ||
                                            "N/A"
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{
                                            inventory.product?.dosage?.name ||
                                            "N/A"
                                        }}
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div
                                        :class="{
                                            'text-sm font-medium': true,
                                            'text-red-600':
                                                isLowStock(inventory),
                                            'text-gray-900':
                                                !isLowStock(inventory),
                                        }"
                                    >
                                        {{ inventory.quantity }}
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div
                                        :class="{
                                            'text-sm': true,
                                            'text-red-600':
                                                isExpired(inventory),
                                            'text-orange-500':
                                                isExpiringSoon(inventory) &&
                                                !isExpired(inventory),
                                            'text-gray-900':
                                                !isExpiringSoon(inventory) &&
                                                !isExpired(inventory),
                                        }"
                                    >
                                        {{ formatDate(inventory.expiry_date) }}
                                    </div>
                                </td>
                                <td
                                    class="px-3 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ inventory.location?.location }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <div
                                            v-if="isLowStock(inventory)"
                                            class="flex items-center"
                                        >
                                            <img
                                                src="/assets/images/low_stock.png"
                                                title="Low Stock"
                                                class="w-6 h-6"
                                                alt="Low Stock"
                                            />
                                        </div>
                                        <div
                                            v-if="
                                                !isOutOfStock(inventory) &&
                                                isExpiringSoon(inventory)
                                            "
                                            class="flex items-center"
                                        >
                                            <img
                                                src="/assets/images/soon_expire.png"
                                                title="Expire soon"
                                                class="w-6 h-6"
                                                alt="Expire soon"
                                            />
                                        </div>
                                        <div
                                            v-if="isExpired(inventory)"
                                            class="flex items-center"
                                        >
                                            <img
                                                src="/assets/images/expired_stock.png"
                                                title="Expired"
                                                class="w-6 h-6"
                                                alt="Expired"
                                            />
                                        </div>
                                        <div
                                            v-if="isOutOfStock(inventory)"
                                            class="flex items-center"
                                        >
                                            <img
                                                src="/assets/images/out_stock.png"
                                                title="Out of Stock"
                                                class="w-6 h-6"
                                                alt="Out of Stock"
                                            />
                                        </div>
                                        <div
                                            v-if="
                                                !isLowStock(inventory) &&
                                                !isExpiringSoon(inventory) &&
                                                !isExpired(inventory) &&
                                                !isOutOfStock(inventory)
                                            "
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
                                <td
                                    class="px-3 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <div class="flex items-center space-x-3">
                                        <button
                                            @click="editInventory(inventory)"
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
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                />
                                            </svg>
                                        </button>
                                        <button
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
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination - Only show if we have data -->
                    <div class="mt-6 px-4 py-3 flex items-center justify-end mb-[100px]">
                        <TailwindPagination
                            :data="props.inventories"
                            @pagination-change-page="getResults"
                            :limit="2"
                        />
                    </div>
                </div>
                <div class="sticky top-0 z-10 shadow-sm p-2">
                    <div class="space-y-4">
                        <div
                            class="flex items-center p-3 rounded-lg bg-green-50"
                        >
                            <img
                                src="/assets/images/in_stock.png"
                                class="w-[60px] h-[60px]"
                                alt="In Stock"
                            />
                            <div class="ml-4 flex flex-col">
                                <span
                                    class="text-xl font-bold text-green-600"
                                    >{{ inStockCount }}</span
                                >
                                <span class="ml-2 text-xs text-green-600"
                                    >In Stock</span
                                >
                            </div>
                        </div>
                        <div
                            class="flex items-center p-3 rounded-lg bg-orange-50"
                        >
                            <img
                                src="/assets/images/low_stock.png"
                                class="w-[60px] h-[60px]"
                                alt="Low Stock"
                            />
                            <div class="ml-4 flex flex-col">
                                <span
                                    class="text-xl font-bold text-orange-600"
                                    >{{ lowStockCount }}</span
                                >
                                <span class="ml-2 text-xs text-orange-600"
                                    >Low Stock</span
                                >
                            </div>
                        </div>
                        <div class="flex items-center p-3 rounded-lg bg-red-50">
                            <img
                                src="/assets/images/out_stock.png"
                                class="w-[60px] h-[60px]"
                                alt="Out of Stock"
                            />
                            <div class="ml-4 flex flex-col">
                                <span class="text-xl font-bold text-red-600">{{
                                    outOfStockCount
                                }}</span>
                                <span class="ml-2 text-xs text-red-600"
                                    >Out of Stock</span
                                >
                            </div>
                        </div>
                        <div
                            class="flex items-center p-3 rounded-lg bg-gray-50"
                        >
                            <img
                                src="/assets/images/expired_stock.png"
                                class="w-[60px] h-[60px]"
                                alt="Expired"
                            />
                            <div class="ml-4 flex flex-col">
                                <span class="text-xl font-bold text-gray-600">{{
                                    expiredCount
                                }}</span>
                                <span class="ml-2 text-xs text-gray-600"
                                    >Expired</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Excel Upload Modal -->
        <Modal :show="showUploadModal" @close="showUploadModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Excel Import
                </h2>

                <div v-if="uploadingFile">
                    <p class="mb-4">
                        Uploading and processing your Excel file...
                    </p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div
                            class="bg-blue-600 h-2.5 rounded-full"
                            :style="{ width: uploadProgress + '%' }"
                        ></div>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ uploadProgress }}% Complete
                    </p>
                </div>

                <div v-else-if="uploadResults">
                    <div
                        class="mb-4 p-4 rounded-md"
                        :class="{
                            'bg-green-50 border border-green-200':
                                uploadResults.success,
                            'bg-red-50 border border-red-200':
                                !uploadResults.success,
                        }"
                    >
                        <p
                            class="font-medium"
                            :class="{
                                'text-green-800': uploadResults.success,
                                'text-red-800': !uploadResults.success,
                            }"
                        >
                            {{ uploadResults.message }}
                        </p>
                    </div>

                    <div
                        v-if="
                            uploadResults.errors &&
                            uploadResults.errors.length > 0
                        "
                        class="mt-4"
                    >
                        <h3 class="font-medium text-gray-900 mb-2">Errors:</h3>
                        <ul class="list-disc pl-5 space-y-1">
                            <li
                                v-for="(error, index) in uploadResults.errors"
                                :key="index"
                                class="text-sm text-red-600"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton
                            @click="
                                showUploadModal = false;
                                uploadResults = null;
                            "
                        >
                            Close
                        </SecondaryButton>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Add Inventory Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-6">
                <form @submit.prevent="submitForm">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ showEditModal ? "Edit" : "Add" }} Inventory Item
                    </h2>
                    <div class="mt-6 space-y-4">
                        <div class="w-full">
                            <InputLabel for="location" value="Location" />
                            <TextInput
                                id="location"
                                v-model="form.location"
                                type="text"
                                placeholder="Enter location"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <div>
                            <InputLabel for="notes" value="Notes" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                placeholder="Enter notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                            <label
                                for="is_active"
                                class="ml-2 block text-sm text-gray-900"
                                >Active</label
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton
                            @click="showAddModal = false"
                            :disabled="isSubmitting"
                            class="mr-2"
                            >Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="isSubmitting">{{
                            showEditModal
                                ? isSubmitting
                                    ? "Processing..."
                                    : "Update"
                                : isSubmitting
                                ? "Processing..."
                                : "Create"
                        }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<style>
/* Make all filter inputs have border radius of 50% with black borders */
.multiselect__tags {
    border-radius: 50px !important;
    border: 1px solid black !important;
}

/* Style the text input to match the rounded filter style */
input[type="text"] {
    border-radius: 50px !important;
    border: 1px solid black !important;
}

/* Style the dropdown menu to match */
.multiselect__content-wrapper {
    border-radius: 20px !important;
    border: 1px solid black !important;
    margin-top: 5px;
    overflow: hidden;
}

/* Add padding for icons in search input */
.search-with-icon {
    position: relative;
}

.search-with-icon input {
    padding-left: 40px !important;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

/* Style for filter icons */
.filter-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #6b7280;
}

.multiselect--with-icon .multiselect__tags {
    padding-left: 40px !important;
}

.multiselect--rounded .multiselect__tags {
    border-radius: 9999px !important;
    border: 1px solid black !important;
}

.multiselect--rounded .multiselect__content-wrapper {
    border-radius: 1rem !important;
    border: 1px solid black !important;
    margin-top: 5px;
    overflow: hidden;
}
</style>
