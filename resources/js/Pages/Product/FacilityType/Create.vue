<template>
    <AuthenticatedLayout
        title="Create Facility Type"
        description="Add a new facility type to organize your products"
        img="/assets/images/products.png"
    >
        <Head title="Create Facility Type" />

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                Products
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <Link :href="route('products.eligible.index')" class="hover:text-gray-900 transition-colors duration-200">
                Facility Types
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-900">Create</span>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Create Facility Type</h2>
            </div>

            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Facility Type Name" class="text-sm font-medium text-gray-700 mb-2" />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            placeholder="Enter facility type name"
                            required
                            :disabled="form.processing"
                        />
                        <InputError :message="errors.name" class="mt-2" />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end mt-6 space-x-3">
                    <Link
                        :href="route('products.eligible.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        :class="{ 'opacity-50 pointer-events-none': form.processing }"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Facility Type' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Swal from "sweetalert2";
import axios from "axios";

const form = useForm({
    name: '',
});

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
});

function submit() {
    axios.post(route('products.facility-types.store'), form.data())
        .then(response => {
            if (response.data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Facility Type created successfully',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = route('products.eligible.index');
                });
            } else {
                form.setError('name', response.data.errors?.name?.[0] || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error creating facility type:', error);
            if (error.response?.data?.errors) {
                Object.keys(error.response.data.errors).forEach(key => {
                    form.setError(key, error.response.data.errors[key][0]);
                });
            } else {
                form.setError('name', 'An error occurred while creating the facility type');
            }
        });
}
</script>
