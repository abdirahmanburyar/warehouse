<template>
    <AuthenticatedLayout title="Categories" description="Manage product categories" img="/assets/images/products.png">
        <div class="mb-6">
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                    Products
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <Link :href="route('products.categories.index')" class="hover:text-gray-900 transition-colors duration-200">
                    Categories
                </Link>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="text-gray-900">Edit</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Category</h2>
                </div>
            </div>
        </div>

        <div class="mb-[100px]">
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Category Information</h3>
                <p class="text-sm text-gray-600 mt-1">Modify the category details</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="name" value="Category Name" class="text-sm font-medium text-gray-700 mb-2" />
                        <TextInput
                            id="name"
                            type="text"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Enter category name"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <Link
                        :href="route('products.categories.index')"
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
                        {{ isSubmitting ? 'Updating...' : 'Update Category' }}
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
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
    category: Object
})

const form = ref({
    id: props.category.data.id,
    name: props.category.data.name
});

const isSubmitting = ref(false);

const submit = async () => {
    isSubmitting.value = true;
    await axios.post(route('products.categories.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Category updated successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.get(route('products.categories.index'));
            });            
        })
        .catch((error) => {
            isSubmitting.value = false;
            console.log(error);
            toast.error(error.response.data);
        });
};
</script>


