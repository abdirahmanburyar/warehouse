<!-- supplies.packing-list.location.store -->

<template>
    <AuthenticatedLayout>
        <Link :href="route('supplies.locations')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Locations
        </Link>
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input 
                        type="text" 
                        id="location" 
                        v-model="form.location" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required
                    >
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <Link 
                        :href="route('supplies.locations')" :disabled="processing"
                        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        Exit
                    </Link>
                    <button 
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                        :disabled="processing"
                    >
                        {{ processing ? 'Saving..' : 'Save'}}
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
    location: {
        required: true,
        type: Object
    }
});

const form = ref({
    id: props.location.id,
    location: props.location.location
});

const processing = ref(false);

const submit = async () => {
    processing.value = true;
    await axios.post(route('supplies.packing-list.location.store'), form.value)
        .then(response => {
            processing.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Location saved',
                text: 'Your location has been saved'
            })
            .then(() => {
                router.get(route('supplies.locations'));
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