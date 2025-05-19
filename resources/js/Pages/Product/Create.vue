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
                            <Multiselect v-model="form.category" :value="form.category_id" 
                                :options="[ { id: 'new', name: '+ Add New Category', isAddNew: true}, ...props.categories.data]"
                                :searchable="true" :close-on-select="true" :show-labels="false"
                                :allow-empty="true" placeholder="Select Category" track-by="id" label="name"
                                @select="handleCategorySelect">
                                <template v-slot:option="{ option }">
                                    <div :class="{ 'add-new-option': option.isAddNew }">
                                        <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add
                                            New Category</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                        </div>

                        <!-- Dosage -->
                        <div>
                            <InputLabel for="dosage_id" value="Dosage" />
                            <Multiselect v-model="form.dosage" :value="form.dosage_id" 
                                :options="[{ id: 'new', name: '+ Add New Dosage form', isAddNew: true }, ...props.dosages.data]"
                                :searchable="true" :close-on-select="true" :show-labels="false"
                                :allow-empty="true" placeholder="Select Dosage" track-by="id" label="name"
                                @select="handleDosageSelect">
                                <template v-slot:option="{ option }">
                                    <div :class="{ 'add-new-option': option.isAddNew }">
                                        <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add
                                            New Dosage Form</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
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

                        <!-- Movement -->
                        <div>
                            <InputLabel for="movement" value="Movement" />
                            <select
                                id="movement"
                                v-model="form.movement"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Select Movement</option>
                                <option value="Fast Moving">Fast Moving</option>
                                <option value="Slow Moving">Slow Moving</option>
                            </select>
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

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

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
    movement: '',
    category_id: '',
    dosage_id: '',
    reorder_level: 0,
    category: null,
    dosage: null,
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

function handleCategorySelect(selected){
    console.log(selected);
    if(selected.isAddNew){
        form.value.category_id = "";
        form.value.category = null;
        alert('hi');
        return;
    }

    form.value.category_id = selected.id;
    form.value.category_id = selected.id;
}


function handleDosageSelect(selected){
    console.log(selected);
    if(selected.isAddNew){
        form.value.dosage_id = "";
        form.value.dosage = null;
        alert('hi');
        return;
    }

    form.value.dosage_id = selected.id;
    form.value.dosage_id = selected.id;
}


</script>
