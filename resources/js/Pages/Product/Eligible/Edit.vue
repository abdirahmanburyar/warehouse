<template>
    <AuthenticatedLayout title="Edit Eligible Item" description="Edit an eligible item">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Eligible Item</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="product_id" value="Product" />
                                <select
                                    id="product_id"
                                    v-model="form.product_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select a product</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <InputLabel for="facility_type" value="Facility Type" />
                                <select
                                    id="facility_type"
                                    v-model="form.facility_type"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select a facility type</option>
                                    <option value="District Hospital">District Hospital</option>
                                    <option value="Primary Health Unit">Primary Health Unit</option>
                                    <option value="Health Centre">Health Centre</option>
                                    <option value="Regional Hospital">Regional Hospital</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :disabled="processing"
                                    :href="route('products.eligible.index')"
                                    class="bg-gray-100 py-2 px-4 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    class="ml-4"
                                    :class="{ 'opacity-25': processing }"
                                    :disabled="processing"
                                >
                                    {{ processing ? "Updating..." : 'Update'}}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
import {ref} from 'vue';
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
            await axios.post(route('products.eligible.store'), form.value)
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
                    toast.error(error.response.data);
                });
        }
    });
}
</script>
