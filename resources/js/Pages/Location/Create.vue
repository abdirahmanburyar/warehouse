<!-- supplies.packing-list.location.store -->

<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-4">
            <div>
                <Link :href="route('inventories.location.index')" class="text-indigo-600 hover:text-indigo-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Locations
                </Link>
                <h3 class="text-xl font-bold text-gray-900 mt-1">Create New Location</h3>
            </div>
        </div>
        
        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <input 
                            type="text" 
                            id="location" 
                            v-model="form.location" 
                            class="pl-10 block w-full rounded-full border-black shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Enter location name (e.g. Shelf A-1)"
                            required
                        >
                    </div>
                </div>
                <div>
                    <label for="warehouse_id" class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="filter-icon h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <select 
                            id="warehouse_id" 
                            v-model="form.warehouse_id" 
                            class="pl-10 block w-full rounded-full border-black shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 appearance-none bg-white"
                            required
                            placeholder="Select a warehouse"                             
                        >
                            <option value="">Select a warehouse</option>
                            <option :value="w.id" v-for="w in props.warehouses">{{ w.name }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-4 mt-8">
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-full font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                        :disabled="processing"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-if="!processing">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" v-if="processing">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ processing ? 'Saving...' : 'Save'}}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import {ref} from 'vue';
import Swal from 'sweetalert2';


const props = defineProps({
    warehouses: Array
});

const form = ref({
    id: null,
    location: '',
    warehouse_id: null
});

const processing = ref(false);

const submit = async () => {
    processing.value = true;
    await axios.post(route('inventories.location.store'), form.value)
        .then(response => {
            processing.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Location saved',
                text: 'Your location has been saved'
            })
            .then(() => {
                router.get(route('inventories.location.index'));
            })
        })
        .catch(error => {
            processing.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response.data
            });
            console.error(error);
        });
};
</script>

<style>
/* Ensure consistent styling with the index page */
input, select {
    padding-top: 8px !important;
    padding-bottom: 8px !important;
    height: 42px !important;
}

.filter-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    z-index: 1;
}

/* Add a subtle transition effect to form elements */
input, select, button {
    transition: all 0.2s ease-in-out;
}

/* Improve focus states */
input:focus, select:focus {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
}

/* Style for the form container */
form {
    background-color: #f9fafb;
    padding: 24px;
    border-radius: 1rem;
    border: 1px solid #f3f4f6;
}
</style>