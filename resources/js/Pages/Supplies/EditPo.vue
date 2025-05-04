<template>
    <AuthenticatedLayout>
        <Head title="Edit Purchase Order" />

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold">Edit Purchase Order</h2>
            <button 
                @click="submitForm"
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Update and Exit
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading purchase order...</p>
        </div>

        <div v-else>
            <!-- Supplier Selection -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Select Supplier
                        </label>
                        <select v-model="form.supplier_id" @change="onSupplierChange" class="block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select supplier name</option>
                            <option :value="s.id" v-for="s in props.suppliers">{{ s.name }}</option>
                        </select>
                    </div>

                    <!-- Supplier Details Card -->
                    <div v-if="selectedSupplier" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Company Details</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.name }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.contact_person }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Contact Information</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.email }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.phone }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ selectedSupplier.address }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Purchase Order Info</h3>
                            <p class="mt-1 text-sm text-gray-900">PO #: {{ form.po_number }}</p>
                            <p class="mt-1 text-sm text-gray-900">Date: {{ new Date().toLocaleDateString() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="mt-8 flex-1 flex flex-col">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dose</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">QTY</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            v-model="item.searchQuery"
                                            @input="filterProducts(index)"
                                            @focus="filterProducts(index)"
                                            class="block w-full border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                            placeholder="Search product..."
                                        >
                                        <div v-if="filteredProducts[index]?.length > 0" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                            <div
                                                v-for="product in filteredProducts[index]"
                                                :key="product.id"
                                                @click="handleProductSelect(product, index)"
                                                class="cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                                            >
                                                {{ product.name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        v-model="item.barcode"
                                        readonly
                                        class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent"
                                        :placeholder="item.barcode || 'No barcode'"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        v-model="item.dose"
                                        readonly
                                        class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        v-model="item.quantity"
                                        @input="calculateItemTotal(index)"
                                        class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                        min="1"
                                    >
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        v-model="item.unit_cost"
                                        @input="calculateItemTotal(index)"
                                        class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                        step="0.01"
                                        min="0"
                                    >
                                </td>
                                <td class="px-3 py-2 text-right text-sm text-gray-900">
                                    {{ formatCurrency(item.total_cost) }}
                                </td>
                                <td class="px-3 py-2">
                                    <button @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="px-3 py-2 text-right text-sm font-medium text-gray-900">Subtotal</td>
                                <td class="px-3 py-2 text-right text-sm text-gray-900">{{ formatCurrency(subtotal) }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="px-3 py-2 text-right text-sm font-medium text-gray-900">Total</td>
                                <td class="px-3 py-2 text-right text-sm text-gray-900">{{ formatCurrency(subtotal) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 flex justify-start space-x-2">
                <button 
                    @click="addItem" 
                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <PlusIcon class="h-4 w-4 mr-1" />
                    Add Item
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import { router } from '@inertiajs/vue3';

const toast = useToast();

const props = defineProps({
    products: {
        type: Array,
        required: true
    },
    suppliers: {
        type: Array,
        required: true
    },
    po_id: {
        type: [String, Number],
        required: true
    }
});

const form = ref({
    supplier_id: "",
    po_number: "",
    items: [{
        id: null,
        product_id: null,
        searchQuery: '',
        barcode: "",
        dose: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
    }]
});

const selectedSupplier = ref(null);
const filteredProducts = ref([]);
const isLoading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get(route('supplies.getPO', props.po_id));
        const po = response.data;
        
        // Initialize form with PO data
        form.value = {
            supplier_id: po.supplier_id,
            po_number: po.po_number,
            items: po.items.map(item => ({
                id: item.id,
                product_id: item.product_id,
                searchQuery: item.searchQuery || '',
                barcode: item.barcode || '',
                dose: item.dose || '',
                quantity: Number(item.quantity) || 1,
                unit_cost: Number(item.unit_cost) || 0,
                total_cost: Number(item.total_cost) || 0
            }))
        };
        
        // Set selected supplier
        selectedSupplier.value = props.suppliers.find(s => s.id === po.supplier_id) || null;
        
        // Initialize filtered products array with the same length as items
        filteredProducts.value = Array(form.value.items.length).fill([]);
        
    } catch (error) {
        console.error('Error loading purchase order:', error);
        toast.error('Failed to load purchase order: ' + (error.response?.data?.message || error.message));
    } finally {
        isLoading.value = false;
    }
});

const filterProducts = (index) => {
    const searchQuery = form.value.items[index].searchQuery.toLowerCase();
    if (!searchQuery) {
        filteredProducts.value[index] = [];
        return;
    }
    filteredProducts.value[index] = props.products.filter(product =>
        product.name.toLowerCase().includes(searchQuery) ||
        product.barcode.toLowerCase().includes(searchQuery)
    ).slice(0, 5);
};

const handleProductSelect = (product, index) => {
    form.value.items[index] = {
        ...form.value.items[index],
        product_id: product.id,
        searchQuery: product.name,
        barcode: product.barcode,
        dose: product.dose,
        unit_cost: Number(product.cost) || 0
    };
    calculateItemTotal(index);
    filteredProducts.value[index] = [];
};

const calculateItemTotal = (index) => {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
};

const addItem = () => {
    form.value.items.push({
        id: null,
        product_id: null,
        searchQuery: '',
        barcode: "",
        dose: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
    });
    filteredProducts.value.push([]);
};

const removeItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
        filteredProducts.value.splice(index, 1);
    }
};

const submitForm = async () => {
    if (!form.value.supplier_id) {
        toast.warning('Please select a supplier');
        return;
    }

    try {
        await axios.put(route('supplies.updatePO', props.po_id), form.value);
        toast.success('Purchase order updated successfully');
        router.visit(route('supplies.index'));
    } catch (error) {
        console.error('Error updating purchase order:', error);
        toast.error('Failed to update purchase order: ' + (error.response?.data?.message || error.message));
    }
};

watch(() => selectedSupplier.value, (newSupplier) => {
    if (newSupplier) {
        form.value.supplier_id = newSupplier.id;
    } else {
        form.value.supplier_id = "";
    }
});

const subtotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
};
</script>
