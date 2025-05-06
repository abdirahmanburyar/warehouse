<template>
    <AuthenticatedLayout>

        <Head title="Edit Purchase Order" />

        <div class="flex items-center mb-4">
            <Link href="#" @click="() => router.visit(route('supplies.index'))"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                </svg>
            </Link>

            <h2 class="text-2xl font-semibold">Edit Purchase Order</h2>
        </div>

        <div class="w-[400px] mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Select Supplier
            </label>
            <select v-model="form.supplier_id" @change="onSupplierChange"
                class="w-full block appearance-none py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Select supplier name</option>
                <option :value="s.id" v-for="s in props.suppliers">{{ s.name }}</option>
            </select>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading">
            <h2 class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                <div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-24 mb-3"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-28"></div>
                </div>
                <div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-32 mb-3"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-40 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-44"></div>
                </div>
                <div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-36 mb-3"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-28 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded animate-pulse w-32"></div>
                </div>
            </div>
        </div>

        <div v-else>
            <!-- Supplier Selection -->
            <div class="grid grid-cols-1 gap-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Supplier Information</h2>

                <!-- Supplier Details Card -->
                <div v-if="selectedSupplier" class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-lg p-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Supplier Details</h3>
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
                        <p class="mt-1 text-sm text-gray-900">Date: {{ moment().format('YYYY-MM-DD') }}</p>
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
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Item</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Barcode</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dose</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                QTY</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Cost</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in form.items" :key="index">
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                            <td class="px-3 py-2">
                                <div class="relative">
                                    <input type="text" v-model="item.searchQuery" @input="filterProducts(index)"
                                        @focus="filterProducts(index)"
                                        class="block w-full border-0 p-0 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                        placeholder="Search product...">
                                    <div v-if="filteredProducts[index]?.length > 0"
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base overflow-auto focus:outline-none sm:text-sm">
                                        <div v-for="product in filteredProducts[index]" :key="product.id"
                                            @click="handleProductSelect(product, index)"
                                            class="cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white">
                                            {{ product.name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" v-model="item.barcode" readonly
                                    class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent"
                                    :placeholder="item.barcode || 'No barcode'">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" v-model="item.dose" readonly
                                    class="block w-full border-0 p-0 text-gray-500 focus:ring-0 sm:text-sm bg-transparent">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" v-model="item.quantity" @input="calculateItemTotal(index)"
                                    class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                    min="1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" v-model="item.unit_cost" @input="calculateItemTotal(index)"
                                    class="block w-full border-0 p-0 text-right text-gray-900 focus:ring-0 sm:text-sm"
                                    step="0.01" min="0">
                            </td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">
                                {{ formatCurrency(item.total_cost) }}
                            </td>
                            <td class="px-3 py-2">
                                <button @click="removeItem(item.id, index)" class="text-red-600 hover:text-red-900">
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
        <div class="mt-4 flex justify-between space-x-2 mb-[100px]">
            <button @click="addItem"
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <PlusIcon class="h-4 w-4 mr-1" />
                Add Item
            </button>
            <button @click="submitForm" :disabled="isSubmitting" type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ isSubmitting ? "Updating..." : "Update and Exit" }}
            </button>
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
import moment from 'moment';
import Swal from 'sweetalert2';

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
        console.log('Loading PO data...');
        const response = await axios.get(route('supplies.getPO', props.po_id));
        const po = response.data;
        console.log('PO data:', po);

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
        console.log('Form initialized:', form.value);
        
        // Set selected supplier
        selectedSupplier.value = props.suppliers.find(s => s.id === po.supplier_id) || null;
        console.log('Selected supplier:', selectedSupplier.value);
        
        // Initialize filtered products array with the same length as items
        filteredProducts.value = Array(form.value.items.length).fill([]);
        
    } catch (error) {
        console.error('Error loading purchase order:', error);
        toast.error('Failed to load purchase order: ' + (error.response?.data?.message || error.message));
    } finally {
        isLoading.value = false;
    }
});


// const isLoading = ref(false);
async function onSupplierChange(e) {
    isLoading.value = true;
    let value = e.target.value;
    if (!value) {
        selectedSupplier.value = null;
        form.value.supplier_id = null;
        isLoading.value = false;
        return;
    }

    form.value.supplier_id = value;
    const supplier = props.suppliers.find(s => s.id == value);
    selectedSupplier.value = supplier;
    setTimeout(() => isLoading.value = false, 1000);
}


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

const removeItem = (id, index) => {
        Swal.fire({
            title: "Remove Item",
            text: "Are you sure you want to remove this item?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, remove it!"
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(route('supplies.deleteItem', id))
                    .then((response) => {
                        form.value.items.splice(index, 1);
                        filteredProducts.value.splice(index, 1);
                        toast.success(response.data);
                    })
                    .catch((error) => {
                        console.error('Error removing item:', error);
                        toast.error(error.response?.data || 'Failed to remove item');
                    });
            }
        });
};

const isSubmitting = ref(false)

const submitForm = async () => {
    if (!form.value.supplier_id) {
        toast.warning('Please select a supplier');
        return;
    }

    Swal.fire({
        title: "Confirm Update",
        text: "Are you sure you want to update this purchase order?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, update it!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            isSubmitting.value = true;
            console.log(form.value);

            await axios.put(route('supplies.updatePO', props.po_id), form.value)
                .then((response) => {
                    isSubmitting.value = false;
                    Swal.fire({
                        title: "Success!",
                        text: response.data,
                        icon: "success"
                    }).then(() => {
                        router.visit(route('supplies.index'));
                    });
                })
                .catch((error) => {
                    isSubmitting.value = false;
                    console.error('Error updating purchase order:', error);
                    toast.error(error.response?.data);
                });
        }
    });
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
