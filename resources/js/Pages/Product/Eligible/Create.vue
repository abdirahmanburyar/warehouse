<template>
    <AuthenticatedLayout title="Create Eligible Item" description="Create a new eligible item">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Eligible Item</h2>
        </template>

        <div class="py-12">
            <div class="bg-white sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="facility_types" value="Facility Types" />
                            <Multiselect
                                v-model="form.facility_types"
                                :options="facilityTypeOptions"
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
                            <p class="text-sm text-gray-500 mt-1">Select one or more facility types</p>
                        </div>
                        <div >
                            <InputLabel for="product_id" value="Items" />
                            <div class="mt-2">
                                <!-- Product Selection -->
                                <div class="flex items-center space-x-2 mb-4" v-for="(item, index) in form.products" :key="item.id">
                                    <Multiselect
                                        v-model="item.product"
                                        :value="item.product_id"
                                        :options="products"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        placeholder="Search and select a product"
                                        track-by="id"
                                        label="name"
                                        class="flex-grow"
                                        @select="handleSelectProduct(index, $event)"
                                    ></Multiselect>
                                    <button 
                                        type="button" 
                                        @click="remove(index)"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Remove
                                    </button>
                                </div>
                                
                              <div class="flex items-center gap-2">
                                <button 
                                    type="button" 
                                    @click="addProductToList"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add
                                </button>
                                <Link
                                    :href="route('products.create')"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    New
                                </Link>
                              </div>
                            </div>
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <Link
                                :href="route('products.eligible.index')" :disabled="processing"
                                class="bg-gray-100 py-2 px-4 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
                            >
                                Exit
                            </Link>
                            <PrimaryButton
                                class="ml-4"
                                :class="{ 'opacity-25': processing }"
                                :disabled="processing"
                            >
                                {{processing ? 'Creating...' : 'Create'}}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import axios from 'axios'
import Swal from 'sweetalert2'

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

const toast = useToast();

const props = defineProps({
    products: {
        type: Array,
        required: true
    }
});

// Define facility type options
const facilityTypeOptions = [
    'All',
    'Distrit Hospital',
    'Primary Health Unit',
    'Health Centre',
    'Regional Hospital'
];

const form = ref({
    products: [],
    facility_types: []
});

function remove(index){
    form.value.products.splice(index, 1);
}


function handleSelectProduct(index, selected){
    console.log(selected);  
    if(selected){
        form.value.products[index].product_id = selected.id;
        form.value.products[index].product = selected;
    }
}

function addProductToList(){
    form.value.products.push({
        product_id: null,
        product: null
    });
}

const processing = ref(false);

const submit = async () => {
    if (form.value.facility_types.length === 0) {
        toast.error('Please select at least one facility type');
        return;
    }
    
    if (form.value.products.length === 0) {
        toast.error('Please select a product');
        return;
    }
    processing.value = true;
    
    await axios.post(route('products.eligible.store'), form.value)
        .then((response) => {
            processing.value = false;
            console.log(response.data);
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit(route('products.eligible.index'));
            });     
        })
        .catch((error) => {
            console.log(error);
            toast.success(error.response.data);
        });
};
</script>
