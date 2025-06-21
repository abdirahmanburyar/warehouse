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
                    </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dosage -->
                        <div>
                            <InputLabel for="dosage_id" value="Dosage Form" />
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

                        <!-- Movement -->
                        <div>
                            <InputLabel for="movement" value="Pattern of Product" />
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

                    <!-- Facility Types Multiselect -->
                    <div class="col-span-2">
                        <InputLabel for="facility_types" value="Applicable Facility Types" />
                        <Multiselect
                            v-model="form.facility_types"
                            :options="facilityTypes"
                            :multiple="true"
                            :searchable="true"
                            :close-on-select="false"
                            :clear-on-select="false"
                            :preserve-search="true"
                            :show-labels="false"
                            placeholder="Select facility types"
                            class="mt-1"
                        >
                            <template v-slot:selection="{ values, search, isOpen }">
                                <span class="multiselect__single" v-if="values.length && !isOpen">
                                    {{ values.join(', ') }}
                                </span>
                            </template>
                        </Multiselect>
                        <p class="text-sm text-gray-500 mt-1">Select the facility types where this product can be used</p>
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

    <!-- Category Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Category</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <InputLabel for="new-category-name" value="Category Name" />
                                    <TextInput
                                        id="new-category-name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="newCategory.name"
                                        placeholder="Create new Category"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="createNewCategory" :disabled="processing" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{processing ? 'Creating...' : 'Create'}}
                    </button>
                    <button @click="showCategoryModal = false" :disabled="processing" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dosage Modal -->
    <div v-if="showDosageModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Create New Dosage Form</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <InputLabel for="new-dosage-name" value="Dosage Form Name" />
                                    <TextInput
                                        id="new-dosage-name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="newDosage.name"
                                        placeholder="Create new Dosage"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="createNewDosage" :disabled="processing" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{processing ? 'Creating...' : 'Create'}}
                    </button>
                    <button @click="showDosageModal = false" :disabled="processing" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Exit
                    </button>
                </div>
            </div>
        </div>
    </div>
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
    movement: '',
    category_id: '',
    dosage_id: '',
    category: null,
    dosage: null,
    facility_types: [],
});

const processing = ref(false);
const showCategoryModal = ref(false);
const showDosageModal = ref(false);
const newCategory = ref({ name: '' });
const newDosage = ref({ name: '' });

const facilityTypes = ref([
    "All",
    "Health Centre",
    "Primary Health Unit",
    "District Hospital",
    "Regional Hospital",
]);

const submit = async () => {
    
    // Create a payload with the correct format
    const payload = {
        ...form.value,
        // Ensure we're sending the IDs, not the objects
        category_id: form.value.category?.id,
        dosage_id: form.value.dosage?.id
    };
    console.log(payload);
    processing.value = true;
    await axios.post(route('products.store'), payload)
    .then((response) => {
        processing.value = false;
        Swal.fire({
            icon: 'success',
            title: 'Product Created',
            text: response.data
        }).then(() => {
            router.visit(route('products.index'))
        });
    })
    .catch((error) => {
        processing.value = false;
        console.error('Error creating product:', error);
        toast.error(error.response?.data || 'Failed to create product');
    });
};

// Function to create a new category
async function createNewCategory() {
    console.log(newCategory.value);
    if (!newCategory.value.name) {
        toast.error('Category name is required');
        return;
    }
    
    try {
        processing.value = true;
        const response = await axios.post(route('products.categories.store'), newCategory.value);
        
        // Add the new category to the categories list
        const newCategoryObj = {
            id: response.data.id || response.data,  // Handle different response formats
            name: newCategory.value.name
        };
        
        props.categories.data.push(newCategoryObj);
        
        // Select the newly created category
        form.value.category = newCategoryObj;
        form.value.category_id = newCategoryObj.id;
        
        // Reset and close modal
        newCategory.value = { name: '' };
        showCategoryModal.value = false;
        
        toast.success('Category created successfully');
        processing.value = false;
    } catch (error) {
        processing.value = false;
        console.log(error);
        toast.error(error.response?.data || 'Failed to create category');
    }
}

// Function to create a new dosage form
async function createNewDosage() {
    if (!newDosage.value.name) {
        toast.error('Dosage form name is required');
        return;
    }
    
    try {
        processing.value = true;
        const response = await axios.post(route('products.dosages.store'), newDosage.value);
        
        // Add the new dosage to the dosages list
        const newDosageObj = {
            id: response.data.id || response.data,  // Handle different response formats
            name: newDosage.value.name
        };
        
        props.dosages.data.push(newDosageObj);
        
        // Select the newly created dosage
        form.value.dosage = newDosageObj;
        form.value.dosage_id = newDosageObj.id;
        
        // Reset and close modal
        newDosage.value = { name: '' };
        showDosageModal.value = false;
        
        toast.success('Dosage form created successfully');
        processing.value = false;
    } catch (error) {
        processing.value = false;
        toast.error(error.response?.data || 'Failed to create dosage form');
    }
};

function handleCategorySelect(selected){
    if(selected.isAddNew){
        form.value.category_id = "";
        form.value.category = null;
        showCategoryModal.value = true;
        return;
    }

    // Make sure we're setting both the object and the ID
    form.value.category = selected;
    form.value.category_id = selected.id;
}


function handleDosageSelect(selected){
    if(selected.isAddNew){
        form.value.dosage_id = "";
        form.value.dosage = null;
        showDosageModal.value = true;
        return;
    }

    // Make sure we're setting both the object and the ID
    form.value.dosage = selected;
    form.value.dosage_id = selected.id;
}


</script>
