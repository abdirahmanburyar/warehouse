<!-- supplies.packing-list.location.store -->

<template>
    <AuthenticatedTabs>
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
                    <Link :href="route('inventories.location.index')" class="text-indigo-600 hover:text-indigo-900">
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
    </AuthenticatedTabs>
</template>

<script setup>
import AuthenticatedTabs from '@/Layouts/AuthenticatedTabs.vue';
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