<template>
    <AuthenticatedLayout title="Create Eligible Item" description="Create a new eligible item">
        <div class="mb-6">
            <Link :href="route('products.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Products
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Eligible Item</h2>
                    <p class="text-sm text-gray-600 mt-1">Add products to facility type eligibility</p>
                </div>
            </div>
        </div>

        <div class="mb-[100px]">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Eligible Item Information</h3>
                <p class="text-sm text-gray-600 mt-1">Select facility types and add products</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Facility Types Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="facility_types" value="Facility Types" class="text-sm font-medium text-gray-700 mb-2" />
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
                        >
                            <template v-slot:selection="{ values, search, isOpen }">
                                <span class="multiselect__single" v-if="values.length && !isOpen">
                                    {{ values.join(', ') }}
                                </span>
                            </template>
                        </Multiselect>
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
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref, onMounted } from 'vue';
import axios from 'axios'
import Swal from 'sweetalert2'

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

const toast = useToast();

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

// Define facility type options
const facilityTypeOptions = [
    'All',
    'District Hospital',
    'Primary Health Unit',
    'Health Centre',
    'Regional Hospital'
];

const form = ref({
    products: [],
    facility_types: []
});

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
                text: response.data,
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
