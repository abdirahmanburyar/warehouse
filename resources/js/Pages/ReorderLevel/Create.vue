<template>
    <AuthenticatedLayout title="Create Reorder Level" description="Add a new reorder level for inventory management" img="/assets/images/products.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Reorder Level
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Add New Reorder Level</h3>
                            <p class="text-sm text-gray-600 mt-1">Create a new reorder level for inventory management</p>
                        </div>

                        <!-- Multiple Items Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Items List -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-lg font-medium text-gray-900">Reorder Level Items</h4>
                                    <button
                                        type="button"
                                        @click="addItem"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Item
                                    </button>
                                </div>

                                <!-- Items Container -->
                                <div class="space-y-4">
                                    <div
                                        v-for="(item, index) in form.items"
                                        :key="index"
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                                    >
                                        <div class="flex justify-between items-start mb-4">
                                            <h5 class="text-sm font-medium text-gray-900">Item {{ index + 1 }}</h5>
                                            <button
                                                v-if="form.items.length > 1"
                                                type="button"
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800 p-1"
                                                title="Remove item"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <!-- Product Selection -->
                                            <div>
                                                <label :for="'product_id_' + index" class="block text-sm font-medium text-gray-700">
                                                    Product *
                                                </label>
                                                <Multiselect
                                                    :id="'product_id_' + index"
                                                    v-model="item.product_id"
                                                    :options="products"
                                                    :searchable="true"
                                                    :close-on-select="true"
                                                    :show-labels="false"
                                                    track-by="id"
                                                    label="name"
                                                    placeholder="Select a product"
                                                    :class="{ 'border-red-500': errors[`items.${index}.product_id`] }"
                                                >
                                                    <template slot="option" slot-scope="props">
                                                        <div class="option__desc">
                                                            <span class="option__title">{{ props.option.name }}</span>
                                                            <span class="option__small">ID: {{ props.option.productID }}</span>
                                                        </div>
                                                    </template>
                                                    <template slot="singleLabel" slot-scope="props">
                                                        <div class="option__desc">
                                                            <span class="option__title">{{ props.option.name }}</span>
                                                            <span class="option__small">ID: {{ props.option.productID }}</span>
                                                        </div>
                                                    </template>
                                                </Multiselect>
                                                <p v-if="errors[`items.${index}.product_id`]" class="mt-1 text-sm text-red-600">
                                                    {{ errors[`items.${index}.product_id`][0] }}
                                                </p>
                                            </div>

                                            <!-- AMC Field -->
                                            <div>
                                                <label :for="'amc_' + index" class="block text-sm font-medium text-gray-700">
                                                    Average Monthly Consumption (AMC) *
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input
                                                        :id="'amc_' + index"
                                                        v-model.number="item.amc"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        class="block w-full pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                        :class="{ 'border-red-500': errors[`items.${index}.amc`] }"
                                                        placeholder="0.00"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">units</span>
                                                    </div>
                                                </div>
                                                <p v-if="errors[`items.${index}.amc`]" class="mt-1 text-sm text-red-600">
                                                    {{ errors[`items.${index}.amc`][0] }}
                                                </p>
                                            </div>

                                            <!-- Lead Time Field -->
                                            <div>
                                                <label :for="'lead_time_' + index" class="block text-sm font-medium text-gray-700">
                                                    Lead Time (Days) *
                                                </label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input
                                                        :id="'lead_time_' + index"
                                                        v-model.number="item.lead_time"
                                                        type="number"
                                                        min="1"
                                                        class="block w-full pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                        :class="{ 'border-red-500': errors[`items.${index}.lead_time`] }"
                                                        placeholder="5"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">days</span>
                                                    </div>
                                                </div>
                                                <p v-if="errors[`items.${index}.lead_time`]" class="mt-1 text-sm text-red-600">
                                                    {{ errors[`items.${index}.lead_time`][0] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Calculated Reorder Level for this item -->
                                        <div class="mt-3 bg-white p-3 rounded border">
                                            <div class="text-sm text-gray-600">Calculated Reorder Level:</div>
                                            <div class="text-lg font-bold text-indigo-600">
                                                {{ calculateReorderLevel(item) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                Formula: {{ item.amc || 0 }} × {{ item.lead_time || 0 }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('reorder-levels.index')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                                >
                                    <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ processing ? 'Creating...' : 'Create Reorder Level' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import axios from 'axios';
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

const props = defineProps({
    products: Array,
    errors: Object
});

const form = ref({
    items: [
        {
            product_id: null,
            amc: 0,
            lead_time: 5
        }
    ]
});

const errors = ref({});
const processing = ref(false);

const calculateReorderLevel = (item) => {
    const amc = parseFloat(item.amc) || 0;
    const leadTime = parseInt(item.lead_time) || 0;
    return (amc * leadTime).toFixed(2);
};

const addItem = () => {
    form.value.items.push({
        product_id: null,
        amc: 0,
        lead_time: 5
    });
};

const removeItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

const submit = async () => {
    processing.value = true;
    errors.value = {};
    
    try {
        // Prepare data for submission - extract product IDs
        const submitData = {
            items: form.value.items.map(item => ({
                product_id: item.product_id ? item.product_id.id : null,
                amc: item.amc,
                lead_time: item.lead_time
            }))
        };
        
        const response = await axios.post(route('reorder-levels.store'), submitData);
        
        // Redirect to index page with success message
        window.location.href = route('reorder-levels.index') + '?success=Reorder levels created successfully.';
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error creating reorder levels:', error);
        }
    } finally {
        processing.value = false;
    }
};
</script> 