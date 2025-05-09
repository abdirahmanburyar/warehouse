<template>
    <AuthenticatedLayout title="Create Eligible Item" description="Create a new eligible item">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Eligible Item</h2>
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
                                <InputError :message="form.errors.product_id" class="mt-2" />
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
                                    <option value="Health Cebtre">Health Centre</option>
                                    <option value="Regional Hospital">Regional Hospital</option>
                                </select>
                                <InputError :message="form.errors.facility_type" class="mt-2" />
                            </div>

                            <div>
                                <div class="flex items-center">
                                    <InputLabel for="is_active" value="Status" class="mr-2" />
                                    <input
                                        id="is_active"
                                        type="checkbox"
                                        v-model="form.is_active"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </div>
                                <InputError :message="form.errors.is_active" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('products.eligible.index')"
                                    class="bg-gray-100 py-2 px-4 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    class="ml-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Create
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
import { Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

const form = useForm({
    product_id: '',
    facility_type: '',
    is_active: true
});

const submit = () => {
    form.post(route('products.eligible.store'), {
        onSuccess: () => {
            toast.success('Eligible item created successfully');
        },
        onError: () => {
            toast.error('Error creating eligible item');
        }
    });
};
</script>
