<template>
    <AuthenticatedLayout title="Create Product">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Product</h2>
            <Link
                :href="route('products.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                Back to List
            </Link>
        </div>

        <div class="overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Product Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />
                        </div>

                        <!-- Barcode -->
                        <div>
                            <InputLabel for="barcode" value="Barcode" />
                            <TextInput
                                id="barcode"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.barcode"
                            />
                        </div>

                        <!-- Category -->
                        <div>
                            <InputLabel for="category_id" value="Category" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Select Category</option>
                                <option v-for="category in categories.data" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Dosage -->
                        <div>
                            <InputLabel for="dosage_id" value="Dosage" />
                            <select
                                id="dosage_id"
                                v-model="form.dosage_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Select Dosage</option>
                                <option v-for="dosage in dosages.data" :key="dosage.id" :value="dosage.id">
                                    {{ dosage.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Dose -->
                        <div>
                            <InputLabel for="dose" value="Dose" />
                            <TextInput
                                id="dose"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.dose"
                                required
                            />
                        </div>

                        <!-- Reorder Level -->
                        <div>
                            <InputLabel for="reorder_level" value="Reorder Level" />
                            <TextInput
                                id="reorder_level"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.reorder_level"
                            />
                        </div>

                        <!-- Status -->
                        <div class="flex items-center mt-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.is_active" />
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <PrimaryButton class="ml-4" :disabled="processing">
                            {{ processing ? "Creating..." : "Create Product"}}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { useToast } from "vue-toastification";
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = useToast();

const props = defineProps({
    categories: {
        type: Object,
        required: true,
        default: () => ({
            data: []
        })
    },
    dosages: {
        type: Object,
        required: true,
        default: () => ({
            data: []
        })
    }
});

const form = ref({
    id: null,
    name: '',
    barcode: '',
    category_id: '',
    dosage_id: '',
    dose: '',
    reorder_level: 0,
    is_active: true
});

const processing = ref(false);

const submit = async () => {
    processing.value = true;
    await axios.post(route('products.store'), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Product Created',
                text: response.data
            }).then(() => {
            processing.value = false;
                router.visit(route('products.index'))
            })
        })
        .catch((error) => {
            console.log(error.response.data);
            toast.error(error.response.data)
        })
};
</script>
