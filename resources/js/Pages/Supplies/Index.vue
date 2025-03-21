<template>
    <Head title="Supplies" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Supplies
            </h2>
        </template>

        <div class="">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                @click="activeTab = 'supplies'"
                                :class="[
                                    activeTab === 'supplies'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Supplies
                            </button>
                            <button
                                @click="activeTab = 'suppliers'"
                                :class="[
                                    activeTab === 'suppliers'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Suppliers
                            </button>
                        </nav>
                    </div>

                    <!-- Supplies Tab -->
                    <div v-if="activeTab === 'supplies'">
                        <!-- Filters -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <InputLabel for="search" value="Search" />
                                    <TextInput
                                        id="search"
                                        type="text"
                                        v-model="supplyFilters.search"
                                        class="mt-1 block w-full"
                                        placeholder="Search supplies..."
                                        @keyup.enter="getSupplies"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="warehouse_filter" value="Warehouse" />
                                    <SelectInput
                                        id="warehouse_filter"
                                        v-model="supplyFilters.warehouse_id"
                                        :options="warehouseOptions"
                                        class="mt-1 block w-full"
                                        placeholder="All warehouses"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="date_from" value="Date From" />
                                    <TextInput
                                        id="date_from"
                                        type="date"
                                        v-model="supplyFilters.date_from"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="date_to" value="Date To" />
                                    <TextInput
                                        id="date_to"
                                        type="date"
                                        v-model="supplyFilters.date_to"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <PrimaryButton @click="getSupplies">
                                        Filter
                                    </PrimaryButton>
                                    <SecondaryButton class="ml-2" @click="resetSupplyFilters">
                                        Reset
                                    </SecondaryButton>
                                </div>
                                <PrimaryButton @click="openCreateSupplyModal">
                                    Add Supply
                                </PrimaryButton>
                            </div>
                        </div>

                        <!-- Supplies Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplier
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Warehouse
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Price
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="supply in supplies.data" :key="supply.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ supply.product.name }}
                                            </div>
                                            <div v-if="supply.batch_number" class="text-xs text-gray-500">
                                                Batch: {{ supply.batch_number }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.supplier.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ supply.warehouse.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supply.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ supply.unit_price }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ supply.total_price }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(supply.supply_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                href="#"
                                                class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                @click.prevent="openEditSupplyModal(supply)"
                                            >
                                                Edit
                                            </Link>
                                            <Link
                                                href="#"
                                                class="text-red-600 hover:text-red-900"
                                                @click.prevent="confirmDeleteSupply(supply)"
                                            >
                                                Delete
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="supplies.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No supplies found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="supplies.links" class="mt-4" />
                    </div>

                    <!-- Suppliers Tab -->
                    <div v-if="activeTab === 'suppliers'">
                        <!-- Filters -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="supplier_search" value="Search" />
                                    <TextInput
                                        id="supplier_search"
                                        type="text"
                                        v-model="supplierFilters.search"
                                        class="mt-1 block w-full"
                                        placeholder="Search suppliers..."
                                        @keyup.enter="getSuppliers"
                                    />
                                </div>
                                <div>
                                    <InputLabel for="active_filter" value="Status" />
                                    <SelectInput
                                        id="active_filter"
                                        v-model="supplierFilters.active"
                                        :options="[
                                            { value: '', label: 'All' },
                                            { value: '1', label: 'Active' },
                                            { value: '0', label: 'Inactive' }
                                        ]"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <PrimaryButton @click="getSuppliers">
                                        Filter
                                    </PrimaryButton>
                                    <SecondaryButton class="ml-2" @click="resetSupplierFilters">
                                        Reset
                                    </SecondaryButton>
                                </div>
                                <PrimaryButton @click="openCreateSupplierModal">
                                    Add Supplier
                                </PrimaryButton>
                            </div>
                        </div>

                        {{ suppliers.data }}

                        <!-- Suppliers Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact Person
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Phone
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplies
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.contact_person || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.email || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.phone || '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ supplier.supplies_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    supplier.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800',
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                                ]"
                                            >
                                                {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                href="#"
                                                class="text-indigo-600 hover:text-indigo-900 mr-2"
                                                @click.prevent="openEditSupplierModal(supplier)"
                                            >
                                                Edit
                                            </Link>
                                            <Link
                                                href="#"
                                                class="text-red-600 hover:text-red-900"
                                                @click.prevent="confirmDeleteSupplier(supplier)"
                                            >
                                                Delete
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            No suppliers found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="suppliers.links" class="mt-4" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Create Supply Modal -->
    <Modal :show="createSupplyModal" @close="closeCreateSupplyModal" maxWidth="7xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Add New Supply
            </h2>
            
            <form @submit.prevent="submitCreateSupply">
                <!-- Common Fields -->
                <div class="mb-4">
                    <InputLabel for="supplier_id" value="Supplier" />
                    <SelectInput
                        id="supplier_id"
                        v-model="supplyForm.supplier_id"
                        :options="props.suppliers.data"
                        class="mt-1 block w-full"
                        placeholder="Select supplier"
                        required
                        :disabled="isSubmitting"
                    />
                    <InputError :message="supplyForm.errors.supplier_id" class="mt-2" />
                </div>

                <div class="mb-4">
                    <InputLabel for="warehouse_id" value="Warehouse" />
                    <SelectInput
                        id="warehouse_id"
                        v-model="supplyForm.warehouse_id"
                        :options="props.warehouses"
                        class="mt-1 block w-full"
                        placeholder="Select warehouse"
                        required
                        :disabled="isSubmitting"
                    />
                    <InputError :message="supplyForm.errors.warehouse_id" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <InputLabel for="invoice_number" value="Invoice Number" />
                        <TextInput
                            id="invoice_number"
                            type="text"
                            v-model="supplyForm.invoice_number"
                            class="mt-1 block w-full"
                            placeholder="Enter invoice number"
                            :disabled="isSubmitting"
                        />
                        <InputError :message="supplyForm.errors.invoice_number" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="supply_date" value="Supply Date" />
                        <TextInput
                            id="supply_date"
                            type="date"
                            v-model="supplyForm.supply_date"
                            class="mt-1 block w-full"
                            required
                            :disabled="isSubmitting"
                        />
                        <InputError :message="supplyForm.errors.supply_date" class="mt-2" />
                    </div>
                </div>

                <!-- Product Items -->
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-md font-medium text-gray-700">Products</h3>
                        <SecondaryButton type="button" @click="addProductRow" :disabled="isSubmitting">
                            Add Product
                        </SecondaryButton>
                    </div>

                    <div v-if="supplyForm.products.length === 0" class="text-center py-4 bg-gray-50 rounded-md">
                        <p class="text-gray-500">No products added. Click "Add Product" to add products to this supply.</p>
                    </div>

                    <div v-for="(product, index) in supplyForm.products" :key="index" class="border rounded-md p-4 mb-3 bg-gray-50">
                        <div class="flex justify-between mb-2">
                            <h4 class="font-medium">Product {{ index + 1 }}</h4>
                            <button 
                                type="button" 
                                @click="removeProductRow(index)" 
                                class="text-red-600 hover:text-red-800"
                                :disabled="isSubmitting"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                            <div>
                                <InputLabel :for="`product_id_${index}`" value="Product" />
                                <SelectInput
                                    :id="`product_id_${index}`"
                                    v-model="product.product_id"
                                    :options="props.products"
                                    class="mt-1 block w-full"
                                    placeholder="Select product"
                                    required
                                    :disabled="isSubmitting"
                                />
                                <InputError :message="getProductError(index, 'product_id')" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`batch_number_${index}`" value="Batch Number" />
                                <TextInput
                                    :id="`batch_number_${index}`"
                                    type="text"
                                    v-model="product.batch_number"
                                    class="mt-1 block w-full"
                                    placeholder="Enter batch number"
                                    :disabled="isSubmitting"
                                />
                                <InputError :message="getProductError(index, 'batch_number')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                            <div>
                                <InputLabel :for="`quantity_${index}`" value="Quantity" />
                                <TextInput
                                    :id="`quantity_${index}`"
                                    type="number"
                                    v-model="product.quantity"
                                    class="mt-1 block w-full"
                                    placeholder="Enter quantity"
                                    min="1"
                                    required
                                    :disabled="isSubmitting"
                                    @input="calculateTotalPrice(index)"
                                />
                                <InputError :message="getProductError(index, 'quantity')" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`unit_price_${index}`" value="Unit Price" />
                                <TextInput
                                    :id="`unit_price_${index}`"
                                    type="number"
                                    v-model="product.unit_price"
                                    class="mt-1 block w-full"
                                    placeholder="Enter unit price"
                                    min="0"
                                    step="0.01"
                                    required
                                    :disabled="isSubmitting"
                                    @input="calculateTotalPrice(index)"
                                />
                                <InputError :message="getProductError(index, 'unit_price')" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`total_price_${index}`" value="Total Price" />
                                <TextInput
                                    :id="`total_price_${index}`"
                                    type="number"
                                    v-model="product.total_price"
                                    class="mt-1 block w-full bg-gray-100"
                                    readonly
                                    :disabled="isSubmitting"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel :for="`manufacturing_date_${index}`" value="Manufacturing Date" />
                                <TextInput
                                    :id="`manufacturing_date_${index}`"
                                    type="date"
                                    v-model="product.manufacturing_date"
                                    class="mt-1 block w-full"
                                    :disabled="isSubmitting"
                                />
                                <InputError :message="getProductError(index, 'manufacturing_date')" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel :for="`expiry_date_${index}`" value="Expiry Date" />
                                <TextInput
                                    :id="`expiry_date_${index}`"
                                    type="date"
                                    v-model="product.expiry_date"
                                    class="mt-1 block w-full"
                                    :disabled="isSubmitting"
                                />
                                <InputError :message="getProductError(index, 'expiry_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <InputLabel for="notes" value="Notes" />
                    <TextareaInput
                        id="notes"
                        v-model="supplyForm.notes"
                        class="mt-1 block w-full"
                        rows="3"
                        placeholder="Enter any additional notes about this supply"
                        :disabled="isSubmitting"
                    />
                    <InputError :message="supplyForm.errors.notes" class="mt-2" />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <SecondaryButton @click="closeCreateSupplyModal" class="mr-3" :disabled="isSubmitting">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }" :disabled="isSubmitting || supplyForm.products.length === 0">
                        <span v-if="isSubmitting" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                        <span v-else>Add Supply</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

    <!-- Edit Supplier Modal -->
    <Modal :show="editSupplierModal" @close="closeEditSupplierModal">
        <!-- ... -->
    </Modal>

    <!-- Edit Supply Modal -->
    <Modal :show="editSupplyModal" @close="closeEditSupplyModal">
        <!-- ... -->
    </Modal>
    <!-- ... -->
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import TextareaInput from '@/Components/TextareaInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

// Toast instance
const toast = useToast();

const props = defineProps({
    supplies: Object,
    suppliers: Object,
    products: Array,
    warehouses: Array,
    supplyFilters: Object,
    supplierFilters: Object,
    activeTab: String,
});

// Active tab state
const activeTab = ref(props.activeTab || 'supplies');

// Watch for tab changes
watch(activeTab, (newTab) => {
    if (newTab === 'supplies') {
        getSupplies();
    } else if (newTab === 'suppliers') {
        getSuppliers();
    }
});

// Set up reactive state for supplies
const supplyFilters = ref({
    search: props.supplyFilters?.search || '',
    warehouse_id: props.supplyFilters?.warehouse_id || '',
    date_from: props.supplyFilters?.date_from || '',
    date_to: props.supplyFilters?.date_to || '',
});

// Set up reactive state for suppliers
const supplierFilters = ref({
    search: props.supplierFilters?.search || '',
    active: props.supplierFilters?.active || '',
});

// Methods for supplies
const getSupplies = () => {
    router.get(route('supplies.index'), { 
        ...supplyFilters.value,
        tab: 'supplies'
    }, {
        preserveState: true,
        replace: true,
    });
};

// Methods for suppliers
const getSuppliers = () => {
    router.get(route('supplies.index'), { 
        ...supplierFilters.value,
        tab: 'suppliers'
    }, {
        preserveState: true,
        replace: true,
    });
};

// Reset filters
const resetSupplyFilters = () => {
    supplyFilters.value = {
        search: '',
        warehouse_id: '',
        date_from: '',
        date_to: '',
    };
    getSupplies();
};

const resetSupplierFilters = () => {
    supplierFilters.value = {
        search: '',
        active: '',
    };
    getSuppliers();
};

// Computed properties
const productOptions = computed(() => {
    if (!props.products || !Array.isArray(props.products)) {
        return [];
    }
    return props.products.map(product => ({
        value: product.id,
        label: product.name
    }));
});

const warehouseOptions = computed(() => {
    if (!props.warehouses || !Array.isArray(props.warehouses)) {
        return [];
    }
    return props.warehouses.map(warehouse => ({
        value: warehouse.id,
        label: warehouse.name
    }));
});

const supplierOptions = computed(() => {
    if (!props.suppliers || !props.suppliers.data) {
        return [];
    }
    return props.suppliers.data.map(supplier => ({
        value: supplier.id,
        label: supplier.name
    }));
});

// Delete modal state for supplies
const deleteSupplyModal = ref(false);
const supplyToDelete = ref(null);

// Delete modal state for suppliers
const deleteSupplierModal = ref(false);
const supplierToDelete = ref(null);

const processing = ref(false);

// Create supplier modal state
const createSupplierModal = ref(false);
const isSubmitting = ref(false);
const supplierForm = ref({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    is_active: true,
    notes: '',
    errors: {},
});

// Edit supplier modal state
const editSupplierModal = ref(false);
const supplierToEdit = ref(null);

// Create supply modal state
const createSupplyModal = ref(false);
const editSupplyModal = ref(false);
const supplyToEdit = ref(null);
const supplyForm = ref({
    supplier_id: '',
    warehouse_id: '',
    invoice_number: '',
    supply_date: new Date().toISOString().substr(0, 10),
    notes: '',
    products: [],
    errors: {},
});

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Open create supply modal
const openCreateSupplyModal = () => {
    resetSupplyForm();
    addProductRow(); // Add one product row by default
    createSupplyModal.value = true;
};

// Close create supply modal
const closeCreateSupplyModal = () => {
    createSupplyModal.value = false;
    resetSupplyForm();
};

// Reset supply form
const resetSupplyForm = () => {
    supplyForm.value = {
        supplier_id: '',
        warehouse_id: '',
        invoice_number: '',
        supply_date: new Date().toISOString().substr(0, 10),
        notes: '',
        products: [],
        errors: {},
    };
};

// Add product row
const addProductRow = () => {
    supplyForm.value.products.push({
        product_id: '',
        quantity: 1,
        unit_price: 0,
        total_price: 0,
        batch_number: '',
        manufacturing_date: '',
        expiry_date: '',
    });
};

// Remove product row
const removeProductRow = (index) => {
    supplyForm.value.products.splice(index, 1);
};

// Calculate total price for a product
const calculateTotalPrice = (index) => {
    const product = supplyForm.value.products[index];
    if (product && product.quantity && product.unit_price) {
        product.total_price = (parseFloat(product.quantity) * parseFloat(product.unit_price)).toFixed(2);
    } else {
        product.total_price = 0;
    }
};

// Get product error
const getProductError = (index, field) => {
    const errors = supplyForm.value.errors;
    if (errors && errors[`products.${index}.${field}`]) {
        return errors[`products.${index}.${field}`];
    }
    return null;
};

// Submit create supply
const submitCreateSupply = () => {
    isSubmitting.value = true;
    supplyForm.value.errors = {};

    axios.post(route('supplies.store'), supplyForm.value)
        .then(response => {
            toast.success('Supply created successfully!');
            closeCreateSupplyModal();
            getSupplies();
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                supplyForm.value.errors = error.response.data.errors;
                toast.error('There are errors in your form. Please check and try again.');
            } else {
                toast.error('An error occurred while creating the supply.');
            }
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};

// Open edit supply modal
const openEditSupplyModal = (supply) => {
    supplyToEdit.value = supply;
    supplyForm.value = {
        id: supply.id,
        supplier_id: supply.supplier_id,
        warehouse_id: supply.warehouse_id,
        invoice_number: supply.invoice_number || '',
        supply_date: supply.supply_date,
        notes: supply.notes || '',
        products: [{
            product_id: supply.product_id,
            quantity: supply.quantity,
            unit_price: supply.unit_price,
            total_price: supply.total_price,
            batch_number: supply.batch_number || '',
            manufacturing_date: supply.manufacturing_date || '',
            expiry_date: supply.expiry_date || '',
        }],
        errors: {},
    };
    editSupplyModal.value = true;
};

// Close edit supply modal
const closeEditSupplyModal = () => {
    editSupplyModal.value = false;
    supplyToEdit.value = null;
    resetSupplyForm();
};

// Submit edit supply
const submitEditSupply = () => {
    isSubmitting.value = true;
    supplyForm.value.errors = {};

    axios.put(route('supplies.update', supplyToEdit.value.id), supplyForm.value)
        .then(response => {
            toast.success('Supply updated successfully!');
            closeEditSupplyModal();
            getSupplies();
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                supplyForm.value.errors = error.response.data.errors;
                toast.error('There are errors in your form. Please check and try again.');
            } else {
                toast.error('An error occurred while updating the supply.');
            }
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};

// Confirm delete supply
const confirmDeleteSupply = (supply) => {
    supplyToDelete.value = supply;
    deleteSupplyModal.value = true;
};

// Close supply modal
const closeSupplyModal = () => {
    deleteSupplyModal.value = false;
    supplyToDelete.value = null;
};

// Delete supply
const deleteSupply = () => {
    processing.value = true;

    axios.delete(route('supplies.destroy', supplyToDelete.value.id))
        .then(response => {
            toast.success('Supply deleted successfully!');
            closeSupplyModal();
            getSupplies();
        })
        .catch(error => {
            toast.error('An error occurred while deleting the supply.');
        })
        .finally(() => {
            processing.value = false;
        });
};

// Open create supplier modal
const openCreateSupplierModal = () => {
    resetSupplierForm();
    createSupplierModal.value = true;
};

// Close create supplier modal
const closeCreateSupplierModal = () => {
    createSupplierModal.value = false;
    resetSupplierForm();
};

// Reset supplier form
const resetSupplierForm = () => {
    supplierForm.value = {
        name: '',
        contact_person: '',
        email: '',
        phone: '',
        address: '',
        city: '',
        state: '',
        postal_code: '',
        country: '',
        is_active: true,
        notes: '',
        errors: {},
    };
};

// Submit create supplier
const submitCreateSupplier = () => {
    isSubmitting.value = true;
    supplierForm.value.errors = {};

    axios.post(route('suppliers.store'), supplierForm.value)
        .then(response => {
            toast.success('Supplier created successfully!');
            closeCreateSupplierModal();
            getSuppliers();
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                supplierForm.value.errors = error.response.data.errors;
                toast.error('There are errors in your form. Please check and try again.');
            } else {
                toast.error('An error occurred while creating the supplier.');
            }
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};

// Open edit supplier modal
const openEditSupplierModal = (supplier) => {
    supplierToEdit.value = supplier;
    supplierForm.value = {
        name: supplier.name,
        contact_person: supplier.contact_person || '',
        email: supplier.email || '',
        phone: supplier.phone || '',
        address: supplier.address || '',
        city: supplier.city || '',
        state: supplier.state || '',
        postal_code: supplier.postal_code || '',
        country: supplier.country || '',
        is_active: supplier.is_active,
        notes: supplier.notes || '',
        errors: {},
    };
    editSupplierModal.value = true;
};

// Close edit supplier modal
const closeEditSupplierModal = () => {
    editSupplierModal.value = false;
    supplierToEdit.value = null;
    resetSupplierForm();
};

// Submit edit supplier
const submitEditSupplier = () => {
    isSubmitting.value = true;
    supplierForm.value.errors = {};

    axios.put(route('suppliers.update', supplierToEdit.value.id), supplierForm.value)
        .then(response => {
            toast.success('Supplier updated successfully!');
            closeEditSupplierModal();
            getSuppliers();
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                supplierForm.value.errors = error.response.data.errors;
                toast.error('There are errors in your form. Please check and try again.');
            } else {
                toast.error('An error occurred while updating the supplier.');
            }
        })
        .finally(() => {
            isSubmitting.value = false;
        });
};

// Confirm delete supplier
const confirmDeleteSupplier = (supplier) => {
    supplierToDelete.value = supplier;
    deleteSupplierModal.value = true;
};

// Close supplier modal
const closeSupplierModal = () => {
    deleteSupplierModal.value = false;
    supplierToDelete.value = null;
};

// Delete supplier
const deleteSupplier = () => {
    processing.value = true;

    axios.delete(route('suppliers.destroy', supplierToDelete.value.id))
        .then(response => {
            toast.success('Supplier deleted successfully!');
            closeSupplierModal();
            getSuppliers();
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                toast.error('This supplier cannot be deleted because it has associated supplies.');
            } else {
                toast.error('An error occurred while deleting the supplier.');
            }
        })
        .finally(() => {
            processing.value = false;
        });
};
</script>