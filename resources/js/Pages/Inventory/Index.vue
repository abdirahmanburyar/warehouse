<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InventoryStatusIcons from '@/Components/InventoryStatusIcons.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const props = defineProps({
    inventories: Object,
    products: Array,
    warehouses: Array,
    filters: Object,
    inventoryStatusCounts: Array,
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
const perPage = ref(props.filters.per_page || 10);
const isSubmitting = ref(false);

// Modal states
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const inventoryToDelete = ref(null);

// Form states
const form = ref({
    product_id: '',
    warehouse_id: '',
    quantity: "0",
    reorder_level: "10",
    manufacturing_date: '',
    expiry_date: '',
    batch_number: '',
    location: '',
    notes: '',
    is_active: true,
});

const formErrors = ref({});

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
            replace: true,
        }
    );
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
    applyFilters();
});

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
        product_id: '',
        warehouse_id: '',
        quantity: "0",
        reorder_level: "10",
        manufacturing_date: '',
        expiry_date: '',
        batch_number: '',
        location: '',
        notes: '',
        is_active: true,
    };
    showAddModal.value = true;
};

// Submit form
const submitForm = async () => {
    isSubmitting.value = true;
    await axios.post(route('inventories.store'), form.value)
        .then(() => {
            showAddModal.value = false;
            toast('Inventory item added successfully', 'success');
            isSubmitting.value = false;
            applyFilters();
        })
        .catch((errors) => {
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
    return new Date(date).toLocaleDateString();
};

// Check if inventory is low
const isLowStock = (inventory) => {
    return inventory.quantity <= inventory.reorder_level;
};

// Check if product is expiring soon (within 30 days)
const isExpiringSoon = (inventory) => {
    if (!inventory.expiry_date) return false;
    const expiryDate = new Date(inventory.expiry_date);
    const today = new Date();
    const diffTime = expiryDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 30 && diffDays > 0;
};

// Check if product is expired
const isExpired = (inventory) => {
    if (!inventory.expiry_date) return false;
    const expiryDate = new Date(inventory.expiry_date);
    const today = new Date();
    return expiryDate < today;

    // Computed properties for inventory status counts
    const inStockCount = computed(() => {
        return props.inventories.data.filter(item => item.quantity > item.reorder_level && item.is_active).length;
    });

    const lowStockCount = computed(() => {
        return props.inventories.data.filter(item =>
            item.quantity > 0 &&
            item.quantity <= item.reorder_level &&
            item.is_active
        ).length;
    });

    const outOfStockCount = computed(() => {
        return props.inventories.data.filter(item => item.quantity === 0 && item.is_active).length;
    });

    const expiredCount = computed(() => {
        return props.inventories.data.filter(item => isExpired(item) && item.is_active).length;
    });
};

// Edit inventory item
const showEditModal = ref(false);


function editInventory(inventory) {
    form.value = {
        id: inventory.id,
        product_id: inventory.product_id,
        warehouse_id: inventory.warehouse_id,
        quantity: inventory.quantity,
        reorder_level: inventory.reorder_level,
        manufacturing_date: inventory.manufacturing_date,
        expiry_date: inventory.expiry_date,
        batch_number: inventory.batch_number,
        location: inventory.location,
        notes: inventory.notes,
        is_active: inventory.is_active,
    };
    showAddModal.value = true;
    showEditModal.value = true;
}
</script>

<template>

    <Head title="Inventory Management" />

    <AuthenticatedLayout>
        <h2 class="text-2xl font-semibold leading-tight text-gray-800">Inventory Management</h2>

        <div class="overflow-auto bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Search and Filters -->
                <div class="mb-6 flex flex-wrap items-center gap-4">
                    <div class="flex-grow">
                        <TextInput v-model="search" type="text" class="w-full"
                            placeholder="Search by product name, SKU, or barcode" @keyup.enter="applyFilters" />
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="w-48">
                            <select v-model="productId"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                @change="applyFilters">
                                <option value="">All Products</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

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

                        <div class="w-48">
                            <TextInput v-model="location" type="text" class="w-full" placeholder="Filter by location"
                                @keyup.enter="applyFilters" />
                        </div>

                        <div class="w-48">
                            <TextInput v-model="batchNumber" type="text" class="w-full"
                                placeholder="Filter by batch number" @keyup.enter="applyFilters" />
                        </div>

                        <SecondaryButton @click="resetFilters">Reset</SecondaryButton>
                    </div>
                </div>

                <!-- Date Range Filters -->
                <div class="mb-6 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span>Expiry Date:</span>
                        <TextInput v-model="expiryDateFrom" type="date" class="w-40" @change="applyFilters" />
                        <span>to</span>
                        <TextInput v-model="expiryDateTo" type="date" class="w-40" @change="applyFilters" />
                    </div>
                </div>

                <!-- Add Button -->
                <div class="mb-4 flex justify-between">
                    <div>
                        <select v-model="perPage"
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            @change="applyFilters">
                            <option :value="10">10 per page</option>
                            <option :value="25">25 per page</option>
                            <option :value="50">50 per page</option>
                            <option :value="100">100 per page</option>
                        </select>
                    </div>
                    <PrimaryButton @click="addInventory">Add Inventory Item</PrimaryButton>
                </div>

               <div class="flex">
                 <!-- Inventory Table -->
                 <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    @click="sort('id')">
                                    SN
                                    <span v-if="sortField === 'id'">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Product
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Warehouse
                                </th>
                                <th class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    @click="sort('quantity')">
                                    In Stock
                                    <span v-if="sortField === 'quantity'">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th class="cursor-pointer px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    @click="sort('expiry_date')">
                                    Expiry Date
                                    <span v-if="sortField === 'expiry_date'">
                                        {{ sortDirection === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Batch/Location
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(inventory, i) in inventories.data" :key="inventory.id">
                                <td class="whitespace-nowrap px-6 py-4">
                                    {{ i + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div v-if="inventory.product">
                                        <div class="font-medium text-gray-900">{{ inventory.product.name }}</div>
                                        <div class="text-sm text-gray-500">SKU: {{ inventory.product.sku }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ inventory.product.category ? inventory.product.category.name : 'No Category' }} /
                                            {{ inventory.product.dosage ? inventory.product.dosage.name : 'No Dosage' }}
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-gray-500">Product not found</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div v-if="inventory.warehouse">
                                        {{ inventory.warehouse.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500">Warehouse not found</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
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
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div :class="{
                                        'text-sm': true,
                                        'text-red-600': isExpired(inventory),
                                        'text-orange-500': isExpiringSoon(inventory) && !isExpired(inventory),
                                        'text-gray-900': !isExpiringSoon(inventory) && !isExpired(inventory),
                                    }">
                                        {{ formatDate(inventory.expiry_date) }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Mfg: {{ formatDate(inventory.manufacturing_date) }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        Batch: {{ inventory.batch_number || 'N/A' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Location: {{ inventory.location || 'N/A' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="{
                                        'inline-flex rounded-full px-2 text-xs font-semibold leading-5': true,
                                        'bg-green-100 text-green-800': inventory.is_active,
                                        'bg-red-100 text-red-800': !inventory.is_active,
                                    }">
                                        {{ inventory.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                    <div class="flex space-x-2">
                                        <button @click="confirmDelete(inventory)" class="flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        <button @click="editInventory(inventory)" class="flex items-center space-x-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="inventories.data.length === 0">
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No inventory items found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 p-3">
                    <InventoryStatusIcons :statusCounts="inventoryStatusCounts" />
                </div>
               </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="inventories.meta.links" />
                </div>
            </div>
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
                            <InputError :message="formErrors.warehouse_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel for="product_id" value="Product" />
                            <select id="product_id" v-model="form.product_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select Product</option>
                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }} ({{ product.sku }})
                                </option>
                            </select>
                            <InputError :message="formErrors.product_id" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="quantity" value="Quantity" />
                                <TextInput id="quantity" v-model="form.quantity" type="number" class="mt-1 block w-full"
                                    min="0" />
                                <InputError :message="formErrors.quantity" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="reorder_level" value="Reorder Level" />
                                <TextInput id="reorder_level" v-model="form.reorder_level" type="number"
                                    class="mt-1 block w-full" min="0" />
                                <InputError :message="formErrors.reorder_level" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="manufacturing_date" value="Manufacturing Date" />
                                <TextInput id="manufacturing_date" v-model="form.manufacturing_date" type="date"
                                    class="mt-1 block w-full" />
                                <InputError :message="formErrors.manufacturing_date" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="expiry_date" value="Expiry Date" />
                                <TextInput id="expiry_date" v-model="form.expiry_date" type="date"
                                    class="mt-1 block w-full" />
                                <InputError :message="formErrors.expiry_date" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <InputLabel for="batch_number" value="Batch Number" />
                                <TextInput id="batch_number" v-model="form.batch_number" type="text"
                                    class="mt-1 block w-full" />
                                <InputError :message="formErrors.batch_number" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="location" value="Location" />
                                <TextInput id="location" v-model="form.location" type="text"
                                    class="mt-1 block w-full" />
                                <InputError :message="formErrors.location" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="notes" value="Notes" />
                            <textarea id="notes" v-model="form.notes"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                rows="3"></textarea>
                            <InputError :message="formErrors.notes" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input id="is_active" v-model="form.is_active" type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                            <InputError :message="formErrors.is_active" class="mt-2" />
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

    </AuthenticatedLayout>
</template>