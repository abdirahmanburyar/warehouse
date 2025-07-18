<template>
    <AuthenticatedLayout title="Edit Reorder Level" description="Update reorder level settings for inventory management" img="/assets/images/products.png">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Reorder Level
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Edit Reorder Level</h3>
                            <p class="text-sm text-gray-600 mt-1">Update reorder level settings for inventory management</p>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Product Selection -->
                            <div>
                                <label for="product_id" class="block text-sm font-medium text-gray-700">
                                    Product *
                                </label>
                                <Multiselect
                                    id="product_id"
                                    v-model="form.product_id"
                                    :options="products"
                                    :searchable="true"
                                    :close-on-select="true"
                                    :show-labels="false"
                                    track-by="id"
                                    label="name"
                                    placeholder="Select a product"
                                    :class="{ 'border-red-500': errors.product_id }"
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
                                <p v-if="errors.product_id" class="mt-1 text-sm text-red-600">
                                    {{ errors.product_id[0] }}
                                </p>
                            </div>

                            <!-- AMC Field -->
                            <div>
                                <label for="amc" class="block text-sm font-medium text-gray-700">
                                    Average Monthly Consumption (AMC) *
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input
                                        id="amc"
                                        v-model.number="form.amc"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="block w-full pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': errors.amc }"
                                        placeholder="0.00"
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">units</span>
                                    </div>
                                </div>
                                <p v-if="errors.amc" class="mt-1 text-sm text-red-600">
                                    {{ errors.amc[0] }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    The average monthly consumption of this product
                                </p>
                            </div>

                            <!-- Lead Time Field -->
                            <div>
                                <label for="lead_time" class="block text-sm font-medium text-gray-700">
                                    Lead Time (Days) *
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input
                                        id="lead_time"
                                        v-model.number="form.lead_time"
                                        type="number"
                                        min="1"
                                        class="block w-full pr-12 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': errors.lead_time }"
                                        placeholder="5"
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">days</span>
                                    </div>
                                </div>
                                <p v-if="errors.lead_time" class="mt-1 text-sm text-red-600">
                                    {{ errors.lead_time[0] }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Default is 5 days (150 days ÷ 30)
                                </p>
                            </div>

                            <!-- Calculated Reorder Level -->
                            <div class="bg-gray-50 p-4 rounded-md">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Calculated Reorder Level
                                </label>
                                <div class="text-2xl font-bold text-indigo-600">
                                    {{ calculatedReorderLevel }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Formula: AMC × Lead Time = {{ form.amc || 0 }} × {{ form.lead_time || 0 }}
                                </p>
                            </div>

                            <!-- Current Values Display -->
                            <div class="bg-blue-50 p-4 rounded-md">
                                <h4 class="text-sm font-medium text-blue-900 mb-2">Current Values</h4>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-700">Product:</span>
                                        <span class="ml-2 text-blue-900">{{ reorderLevel.product?.name || 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">AMC:</span>
                                        <span class="ml-2 text-blue-900">{{ reorderLevel.amc }}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Lead Time:</span>
                                        <span class="ml-2 text-blue-900">{{ reorderLevel.lead_time }} days</span>
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
                                    {{ processing ? 'Updating...' : 'Update Reorder Level' }}
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
    reorderLevel: Object,
    products: Array,
    errors: Object
});

const form = ref({
    product_id: props.reorderLevel.product_id,
    amc: props.reorderLevel.amc,
    lead_time: props.reorderLevel.lead_time
});

const errors = ref({});
const processing = ref(false);

const calculatedReorderLevel = computed(() => {
    const amc = parseFloat(form.value.amc) || 0;
    const leadTime = parseInt(form.value.lead_time) || 0;
    return (amc * leadTime).toFixed(2);
});

const submit = async () => {
    processing.value = true;
    errors.value = {};
    
    try {
        const response = await axios.put(route('reorder-levels.update', props.reorderLevel.id), form.value);
        
        // Redirect to index page with success message
        window.location.href = route('reorder-levels.index') + '?success=Reorder level updated successfully.';
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Error updating reorder level:', error);
        }
    } finally {
        processing.value = false;
    }
};
</script> 