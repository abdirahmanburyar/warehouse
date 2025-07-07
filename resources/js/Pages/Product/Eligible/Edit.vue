<template>
    <AuthenticatedLayout title="Edit Eligible Item" description="Edit an eligible item">
        <div class="mb-6">
            <Link :href="route('products.eligible.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Eligible Items
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Eligible Item</h2>
                    <p class="text-sm text-gray-600 mt-1">Update product eligibility information</p>
                </div>
            </div>
        </div>

        <div class="mb-[100px]">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Eligible Item Details</h3>
                <p class="text-sm text-gray-600 mt-1">Modify the product and facility type</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Product Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="product_id" value="Product" class="text-sm font-medium text-gray-700 mb-2" />
                        <select
                            id="product_id"
                            v-model="form.product_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            required
                        >
                            <option value="">Select a product</option>
                            <option v-for="product in products" :key="product.id" :value="product.id">
                                {{ product.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="facility_type" value="Facility Type" class="text-sm font-medium text-gray-700 mb-2" />
                        <select
                            id="facility_type"
                            v-model="form.facility_type"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            required
                        >
                            <option value="">Select a facility type</option>
                            <option value="District Hospital">District Hospital</option>
                            <option value="Primary Health Unit">Primary Health Unit</option>
                            <option value="Health Centre">Health Centre</option>
                            <option value="Regional Hospital">Regional Hospital</option>
                        </select>
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
                        {{ processing ? "Updating..." : 'Update Eligible Item' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const toast = useToast();

const props = defineProps({
    eligible: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        required: true
    }
});

const form = ref({
    id: props.eligible.id,
    product_id: props.eligible.product_id,
    facility_type: props.eligible.facility_type
});

const processing = ref(false);

const submit = () => {
    Swal.fire({
        title: 'Update Eligible Item',
        text: 'Are you sure you want to update this item?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            processing.value = true;
            await axios.post(route('products.eligible.update'), form.value)
                .then((response) => {
                    processing.value = false;
                    toast.success(response.data);
                    Swal.fire(
                        'Updated!',
                        'The eligible item has been updated.',
                        'success'
                    ).then(() => {
                        router.visit(route('products.eligible.index'));
                    });                    
                })
                .catch((error) => {
                    processing.value = false;
                    console.error('Error:', error);
                    toast.error(error.response?.data || 'An error occurred');
                });
        }
    });
}
</script>
