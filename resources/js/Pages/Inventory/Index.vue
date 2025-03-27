<script setup>
import { ref, watch, computed, reactive, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import moment from 'moment';
import Swal from 'sweetalert2';

const props = defineProps({
    inventories: Object,
    products: Array,
    warehouses: Array,
    filters: Object,
    inventoryStatusCounts: Array
});

const toast = useToast();

// Search and filter states
const search = ref(props.filters.search || '');
const productId = ref(props.filters.product_id || '');
const location = ref(props.filters.location || '');
const batchNumber = ref(props.filters.batch_number || '');
const expiryDateFrom = ref(props.filters.expiry_date_from || '');
const expiryDateTo = ref(props.filters.expiry_date_to || '');
const sortField = ref(props.filters.sort_field || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const warehouse_id = ref(props.filters.warehouse_id || '');
const perPage = ref(props.filters.per_page || 6);
const isSubmitting = ref(false);

// Modal states
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const inventoryToDelete = ref(null);

// Bulk delete states
const selectedItems = ref([]);
const isBulkDeleting = ref(false);
const showBulkDeleteModal = ref(false);

// Form states
const form = ref({
    product_id: null,
    product: null,
    warehouse_id: '',
    quantity: 0,
    reorder_level: 10,
    manufacturing_date: '',
    expiry_date: '',
    batch_number: '',
    location: '',
    notes: '',
    is_active: true,
});

const formErrors = ref({});

// Create reactive copy of inventory data for real-time updates
const currentInventories = reactive({
    data: [...props.inventories.data],
    meta: { ...props.inventories.meta }
});

// Add a watcher to update currentInventories when props.inventories changes
watch(() => props.inventories, (newInventories) => {
    console.log('[PUSHER-DEBUG] Props updated, syncing with currentInventories');
    currentInventories.data = [...newInventories.data];
    currentInventories.meta = { ...newInventories.meta };
}, { deep: true });

// Pusher debugging variables
const pusherStatus = ref('Not connected');
const lastEventTime = ref(null);
const pusherEvents = ref([]);
const pusherError = ref(null);
const maxEvents = 5;

// Listen for inventory changes with detailed debugging
onMounted(() => {
    window.Echo.channel('inventory')
        .listen('.refresh', (event) => {
            applyFilters();
        });
});

onBeforeUnmount(() => {
    window.Echo.leaveChannel('inventory');
});

// Watch for product changes to update product_id
watch(() => form.value.product, (newProduct) => {
    if (newProduct && newProduct.id) {
        form.value.product_id = newProduct.id;
    } else {
        form.value.product_id = null;
    }
}, { deep: true });

// Reset filters
const resetFilters = () => {
    search.value = '';
    productId.value = '';
    location.value = '';
    batchNumber.value = '';
    expiryDateFrom.value = '';
    expiryDateTo.value = '';
    warehouse_id.value = '';
    applyFilters();
};

// Apply filters
const applyFilters = () => {
    console.log('[PUSHER-DEBUG] Applying filters');
    const query = {}
    if (search.value) query.search = search.value
    if (productId.value) query.product_id = productId.value
    if (location.value) query.location = location.value
    if (batchNumber.value) query.batch_number = batchNumber.value
    if (expiryDateFrom.value) query.expiry_date_from = expiryDateFrom.value
    if (expiryDateTo.value) query.expiry_date_to = expiryDateTo.value
    if (sortField.value) query.sort_field = sortField.value
    if (sortDirection.value) query.sort_direction = sortDirection.value
    if (perPage.value) query.per_page = perPage.value
    if (warehouse_id.value) query.warehouse_id = warehouse_id.value

    router.get(
        route('inventories.index'), query,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['inventories', 'products', 'warehouses', 'filters', 'inventoryStatusCounts'],
        }
    );
};

// Debounce search
let searchTimeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

// Watch for changes in search input
watch([
    () => search.value,
    () => productId.value,
    () => location.value,
    () => batchNumber.value,
    () => expiryDateFrom.value,
    () => expiryDateTo.value,
    () => sortField.value,
    () => sortDirection.value,
    () => perPage.value,
    () => warehouse_id.value,
], () => {
    debouncedSearch();
}, { deep: true });

// Sort table
const sort = (field) => {
    sortDirection.value = sortField.value === field && sortDirection.value === 'asc' ? 'desc' : 'asc';
    sortField.value = field;
    applyFilters();
};

// Add new inventory item
const addInventory = () => {
    formErrors.value = {};
    form.value = {
        product_id: null,
        product: null,
        warehouse_id: '',
        quantity: 0,
        reorder_level: 10,
        manufacturing_date: '',
        expiry_date: '',
        batch_number: '',
        location: '',
        notes: '',
        is_active: true,
    };
    showAddModal.value = true;
    showEditModal.value = false;
};

// Submit form
const submitForm = async () => {
    isSubmitting.value = true;

    // Set product_id from the selected product object if it exists
    if (form.value.product && form.value.product.id) {
        form.value.product_id = form.value.product.id;
    }

    await axios.post(route('inventories.store'), form.value)
        .then(() => {
            showAddModal.value = false;
            toast('Inventory item added successfully', 'success');
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

// Delete inventory item
const confirmDelete = (inventory) => {
    inventoryToDelete.value = inventory;
    showDeleteModal.value = true;
};

const deleteInventory = () => {
    router.delete(route('inventories.destroy', inventoryToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            inventoryToDelete.value = null;
            addToast('Inventory item deleted successfully', 'success');
        },
    });
};

// Format date
const formatDate = (date) => {
    if (!date) return 'N/A';
    return moment(date).format('LL');
};

// Check if inventory is low
const isLowStock = (inventory) => {
    return inventory.quantity > 0 && inventory.quantity <= inventory.reorder_level;
};

// Check if inventory is out of stock
const isOutOfStock = (inventory) => {
    return inventory.quantity === 0;
};

// Check if product is expiring soon (within 30 days)
const isExpiringSoon = (inventory) => {
    if (!inventory.expiry_date) return false;
    const expiryDate = moment(inventory.expiry_date);
    const today = moment();
    const diffDays = expiryDate.diff(today, 'days');
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
    const stat = props.inventoryStatusCounts.find(s => s.status === 'in_stock');
    return stat.count;
});

const lowStockCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(s => s.status === 'low_stock');
    return stat.count;
});

const outOfStockCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(s => s.status === 'out_of_stock');
    return stat.count;
});

const expiredCount = computed(() => {
    const stat = props.inventoryStatusCounts.find(s => s.status === 'expired');
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
            product_id: inventory.product?.id
        },
        product_id: inventory.product_id,
        warehouse_id: inventory.warehouse_id,
        quantity: inventory.quantity,
        reorder_level: inventory.reorder_level,
        manufacturing_date: inventory.manufacturing_date,
        expiry_date: inventory.expiry_date,
        batch_number: inventory.batch_number,
        location: inventory.location || '',
        notes: inventory.notes || '',
        is_active: inventory.is_active,
    };
    showAddModal.value = true;
    showEditModal.value = true;
}

// Bulk delete methods
const toggleSelectAll = () => {
    if (selectedItems.value.length === currentInventories.data.length) {
        selectedItems.value = [];
    } else {
        selectedItems.value = currentInventories.data.map(item => item.id);
    }
};

const confirmBulkDelete = () => {
    if (selectedItems.value.length === 0) {
        toast.warning('Please select items to delete');
        return;
    }
    showBulkDeleteModal.value = true;
};

const bulkDelete = async () => {
    let timerInterval;
    try {
        isBulkDeleting.value = true;
        showBulkDeleteModal.value = false;

        // Show the countdown Swal
        await Swal.fire({
            title: "Deleting Items",
            html: "Processing will complete in <b></b> milliseconds.",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                const timer = Swal.getPopup().querySelector("b");
                timerInterval = setInterval(() => {
                    timer.textContent = `${Swal.getTimerLeft()}`;
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        });

        // Perform the actual delete operation
        const response = await axios.post(route('inventories.bulk'), {
            ids: selectedItems.value,
            action: 'delete'
        });
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Selected items have been deleted successfully',
            timer: 1500,
            showConfirmButton: false
        });

        selectedItems.value = [];
        applyFilters();
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred while deleting items',
            confirmButtonText: 'OK'
        });
    } finally {
        isBulkDeleting.value = false;
    }
};

const echo = ref(null);
</script>

<template>

    <Head title="Inventory Management" />

    <AuthenticatedLayout>
        <div class="overflow-auto bg-white">
            <div class="text-gray-900">
                <!-- Search and Filters -->
                <div class="mb-1 flex flex-wrap items-center gap-4">
                    <div class="flex-grow relative">
                        <TextInput v-model="search" type="text" class="w-full"
                            placeholder="Search by product name, SKU, category or barcode" />
                        <button v-if="search" @click="search = ''; applyFilters()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="w-48">
                            <select v-model="warehouse_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @change="applyFilters">
                                <option value="">All Warehouses</option>
                                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                    {{ warehouse.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <TextInput v-model="expiryDateFrom" type="date" class="w-40" @change="applyFilters" />
                            <span>to</span>
                            <TextInput v-model="expiryDateTo" type="date" class="w-40" @change="applyFilters" />
                        </div>
                        <div class="w-48">
                            <TextInput v-model="location" type="text" class="w-full" placeholder="Filter by location"
                                @keyup.enter="applyFilters" />
                        </div>

                        <div class="w-48">
                            <TextInput v-model="batchNumber" type="text" class="w-full"
                                placeholder="Filter by batch number" @keyup.enter="applyFilters" />
                        </div>

                        <SecondaryButton @click="resetFilters">Reset</SecondaryButton>
                        <button @click="addInventory" 
                            class="rounded-full p-3 bg-gray-900 hover:bg-gray-800 text-white shadow-sm transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </button>
                        <select v-model="perPage"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @change="applyFilters">
                                <option :value="6">6 per page</option>
                                <option :value="25">25 per page</option>
                                <option :value="50">50 per page</option>
                                <option :value="100">100 per page</option>
                            </select>
                    </div>
                </div>

                <!-- Add Button -->


                <div class="flex justify-between">
                    <!-- Inventory Table -->
                    <div class="w-full">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r">
                                            <div class="flex items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedItems.length === currentInventories.data.length && currentInventories.data.length > 0"
                                                    @change="toggleSelectAll"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                            </div>
                                        </th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r">
                                            Product Name
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200">
                                            Category
                                        </th>
                                        <th class="cursor-pointer px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200"
                                            @click="sort('quantity')">
                                            In Stock
                                            <span v-if="sortField === 'quantity'">
                                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200"
                                            @click="sort('location')">
                                            Location
                                            <span v-if="sortField === 'location'">
                                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200"
                                            @click="sort('batch_number')">
                                            Batch Number
                                            <span v-if="sortField === 'batch_number'">
                                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th class="cursor-pointer px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200"
                                            @click="sort('expiry_date')">
                                            Expiry Date
                                            <span v-if="sortField === 'expiry_date'">
                                                {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                            </span>
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider border-r border-gray-200">
                                            Status
                                        </th>
                                        <th
                                            class="px-3 py-2 text-left text-xs font-medium text-gray-900 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="!currentInventories.data || currentInventories.data.length === 0">
                                        <td colspan="10" class="px-3 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1"
                                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                <span class="text-lg font-medium">No inventory items found</span>
                                                <p class="text-sm text-gray-400 mt-1">Try adjusting your search or
                                                    filters</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else v-for="inventory in currentInventories.data" :key="inventory.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap border-r">
                                            <div class="flex items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    v-model="selectedItems"
                                                    :value="inventory.id"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                            </div>
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            <div v-if="inventory.product">
                                                <div class="font-medium text-gray-900 relative group cursor-help">
                                                    {{ inventory.product.name }}
                                                    <!-- Tooltip -->
                                                    <div
                                                        class="absolute hidden group-hover:block z-[99] w-64 p-4 mt-1 bg-gray-800 rounded-lg shadow-lg">
                                                        <div class="text-sm text-white space-y-2">
                                                            <div class="border-b border-gray-700 pb-2">
                                                                <span class="text-gray-400">SKU:</span>
                                                                <span class="ml-2">{{ inventory.product.sku }}</span>
                                                            </div>
                                                            <div class="border-b border-gray-700 pb-2">
                                                                <span class="text-gray-400">Dosage:</span>
                                                                <span class="ml-2">{{ inventory.product.dosage ?
                                                                    inventory.product.dosage.name : 'No Dosage'
                                                                    }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-400">Barcode:</span>
                                                                <span class="ml-2">{{ inventory.product.category ?
                                                                    inventory.product.barcode : 'No Barcode' }}</span>
                                                            </div>
                                                        </div>
                                                        <!-- Arrow -->
                                                        <div
                                                            class="absolute -top-1 left-4 w-3 h-3 bg-gray-800 transform rotate-45">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500">Product not found</div>
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ inventory.product.category ? inventory.product.category.name : 'No Category' }}
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            <div :class="{
                                                'font-medium': true,
                                                'text-red-600': isLowStock(inventory),
                                                'text-gray-900': !isLowStock(inventory),
                                            }">
                                                {{ inventory.quantity }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Reorder Level: {{ inventory.reorder_level }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                                {{ inventory.location }}
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ inventory.batch_number }}
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            <div :class="{
                                                'text-sm': true,
                                                'text-red-600': isExpired(inventory),
                                                'text-orange-500': isExpiringSoon(inventory) && !isExpired(inventory),
                                                'text-gray-900': !isExpiringSoon(inventory) && !isExpired(inventory),
                                            }">
                                                {{ formatDate(inventory.expiry_date) }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(inventory.manufacturing_date) }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            <div class="flex items-center space-x-2">
                                                <div v-if="isLowStock(inventory)" class="flex items-center">
                                                    <img src="/assets/images/low_stock.png" title="Low Stock"
                                                        class="w-6 h-6" alt="Low Stock" />
                                                </div>
                                                <div v-if="!isOutOfStock(inventory) && isExpiringSoon(inventory)" class="flex items-center">
                                                    <img src="/assets/images/soon_expire.png" title="Expire soon"
                                                        class="w-6 h-6" alt="Expire soon" />
                                                </div>
                                                <div v-if="isExpired(inventory)" class="flex items-center">
                                                    <img src="/assets/images/expired_stock.png" title="Expired"
                                                        class="w-6 h-6" alt="Expired" />
                                                </div>
                                                <div v-if="isOutOfStock(inventory)" class="flex items-center">
                                                    <img src="/assets/images/out_stock.png" title="Out of Stock"
                                                        class="w-6 h-6" alt="Out of Stock" />
                                                </div>
                                                <div v-if="!isLowStock(inventory) && !isExpiringSoon(inventory) && !isExpired(inventory) && !isOutOfStock(inventory)"
                                                    class="flex items-center">
                                                    <img src="/assets/images/in_stock.png" title="In Stock"
                                                        class="w-6 h-6" alt="In Stock" />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-3">
                                                <button @click="editInventory(inventory)"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button @click="confirmDelete(inventory)"
                                                    class="text-red-600 hover:text-red-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pagination - Only show if we have data -->
                            <div v-if="currentInventories.data && currentInventories.data.length > 0" class="mt-4">
                                <Pagination :links="currentInventories.meta.links" />
                            </div>
                        </div>
                    </div>
                    <div class="sticky top-0 z-10 bg-white shadow-sm p-4">
                        <div class="space-y-4">
                            <div class="flex items-center p-3 rounded-lg bg-green-50">
                                <img src="/assets/images/in_stock.png" class="w-[70px] h-[70px]" alt="In Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-xl font-bold text-green-600">{{ inStockCount }}</span>
                                    <span class="ml-2 text-xs text-green-600">In Stock</span>
                                </div>
                            </div>
                            <div class="flex items-center p-3 rounded-lg bg-orange-50">
                                <img src="/assets/images/low_stock.png" class="w-[70px] h-[70px]" alt="Low Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-xl font-bold text-orange-600">{{ lowStockCount }}</span>
                                    <span class="ml-2 text-xs text-orange-600">Low Stock</span>
                                </div>
                            </div>
                            <div class="flex items-center p-3 rounded-lg bg-red-50">
                                <img src="/assets/images/out_stock.png" class="w-[70px] h-[70px]" alt="Out of Stock" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-xl font-bold text-red-600">{{ outOfStockCount }}</span>
                                    <span class="ml-2 text-xs text-red-600">Out of Stock</span>
                                </div>
                            </div>
                            <div class="flex items-center p-3 rounded-lg bg-gray-50">
                                <img src="/assets/images/expired_stock.png" class="w-[70px] h-[70px]" alt="Expired" />
                                <div class="ml-4 flex flex-col">
                                    <span class="text-xl font-bold text-gray-600">{{ expiredCount }}</span>
                                    <span class="ml-2 text-xs text-gray-600">Expired</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedItems.length > 0" 
            class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 flex items-center bg-white rounded-lg shadow-lg border border-gray-200 px-4 py-2 space-x-2">
            <span class="text-sm text-gray-600">{{ selectedItems.length }} items selected</span>
            <button 
                @click="confirmBulkDelete"
                class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
            </button>
        </div>

        <!-- Add Inventory Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-6">
                <form @submit.prevent="submitForm">
                    <h2 class="text-lg font-medium text-gray-900">{{ showEditModal ? 'Edit' : 'Add' }} Inventory Item
                    </h2>
                    <div class="mt-6 space-y-4">
                        <div>
                            <InputLabel for="warehouse_id" value="Warehouse" />
                            <select id="warehouse_id" v-model="form.warehouse_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Warehouse</option>
                                <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                    {{ warehouse.name }} ({{ warehouse.code }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="product_id" value="Product" />
                            <Multiselect id="product_id" v-model="form.product" :options="products" option-label="name"
                                track-by="id" label="name" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="quantity" value="Quantity" />
                                <TextInput id="quantity" v-model="form.quantity" type="number" class="mt-1 block w-full"
                                    min="0" />
                            </div>

                            <div>
                                <InputLabel for="reorder_level" value="Reorder Level" />
                                <TextInput id="reorder_level" v-model="form.reorder_level" type="number"
                                    class="mt-1 block w-full" min="0" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="manufacturing_date" value="Manufacturing Date" />
                                <TextInput id="manufacturing_date" v-model="form.manufacturing_date" type="date"
                                    class="mt-1 block w-full" />
                            </div>

                            <div>
                                <InputLabel for="expiry_date" value="Expiry Date" />
                                <TextInput id="expiry_date" v-model="form.expiry_date" type="date"
                                    class="mt-1 block w-full" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="batch_number" value="Batch Number" />
                                <TextInput id="batch_number" v-model="form.batch_number" type="text"
                                    class="mt-1 block w-full" />
                            </div>

                            <div>
                                <InputLabel for="location" value="Location" />
                                <TextInput id="location" v-model="form.location" type="text"
                                    class="mt-1 block w-full" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notes" value="Notes" />
                            <textarea id="notes" v-model="form.notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"></textarea>
                        </div>

                        <div class="flex items-center">
                            <input id="is_active" v-model="form.is_active" type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="showAddModal = false" :disabled="isSubmitting" class="mr-2">Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="isSubmitting">{{ showEditModal ? isSubmitting ?
                            'Processing...' : 'Update' : isSubmitting ? 'Processing...' : 'Create' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Inventory Item</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this inventory item? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDeleteModal = false" class="mr-2">Cancel</SecondaryButton>
                    <DangerButton @click="deleteInventory">Delete</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Bulk Delete Confirmation Modal -->
        <Modal :show="showBulkDeleteModal" @close="showBulkDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Selected Inventory Items</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete the selected inventory items? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showBulkDeleteModal = false" class="mr-2">Cancel</SecondaryButton>
                    <DangerButton @click="bulkDelete">Delete</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Add at the end of the template, just before closing AuthenticatedLayout -->
        <!-- <PusherDebugPanel :debug="true" channel="inventory" class="mb-8" /> -->
    </AuthenticatedLayout>
</template>
