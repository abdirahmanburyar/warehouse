<template>
    <AuthenticatedLayout title="Edit Dosage Form" description="Edit dosage form" img="/assets/images/products.png">
        <div class="mb-6">
            <Link :href="route('products.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Products
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Dosage Form</h2>
                    <p class="text-sm text-gray-600 mt-1">Update the dosage form information</p>
                </div>
            </div>
        </div>

        <div class="mb-[100px]">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Dosage Form Information</h3>
                <p class="text-sm text-gray-600 mt-1">Modify the dosage form details</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="name" value="Dosage Form Name" class="text-sm font-medium text-gray-700 mb-2" />
                        <TextInput
                            id="name"
                            type="text"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Enter dosage form name"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <Link
                        :href="route('products.dosages.index')"
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    >
                        <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Updating...' : 'Update Dosage Form' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInput from '@/Components/TextInput.vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    dosage: {
        type: Object,
        required: true
    }
});

const form = ref({
    id: props.dosage.data.id,
    name: props.dosage.data.name
});

const isSubmitting = ref(false);

const submit = async () => {
    isSubmitting.value = true;
    await axios.post(route('products.dosages.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.get(route('products.dosages.index'));
            });            
        })
        .catch((error) => {
            isSubmitting.value = false;
            console.log(error);
            toast.error(error.response.data);
        });
};
</script>
