<template>
    <AuthenticatedLayout title="Eligible Items" description="Manage product eligibility for facilities">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Eligible Items</h2>
            <Link
                    :href="route('products.eligible.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Eligible Item
                </Link>
        </div>
        <div class="flex items-center justify-between mt-3">
            <input
                v-model="search"
                type="text"
                placeholder="Search..."
                class="w-full"
            />
            <div class="w-[400px] ml-3">
            <Multiselect
             v-model="facilityType"
             :options="facilityTypes"
             :close-on-select="false"
             :searchable="true"
             :show-labels="true"
             placeholder="Select Facility Type"
            
         />
           </div>
            <div class="ml-3">
                <select
                 v-model="perPage"
                 class="w-[200px]"
             >
                 <option value="10">10 per page</option>
                 <option value="25">25 per page</option>
                 <option value="50">50 per page</option>
                 <option value="100">100 per page</option>
             </select>
            </div>
        </div>

        <div class="py-6">
            <table class="w-full text-sm text-left text-black">
                <thead >
                    <tr>
                        <th class="p-4 border border-black">
                            Item Name
                        </th>
                        <th class="p-4 border border-black">
                            Facility Type
                        </th>
                        <th class="p-4 border border-black">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in eligibleItems.data" :key="item.id">
                        <td class="p-4 border border-black">
                            {{ item.product?.name }}
                        </td>
                        <td class="p-4 border border-black">{{ item.facility_type }}</td>
                        <td class="p-4 border border-black">
                            <div class="flex items-center space-x-3">
                                <Link :href="route('products.eligible.edit', item.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </Link>
                                <button @click="destroy(item)" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="eligibleItems.data.length === 0">
                        <td colspan="4" class="p-4 border border-black text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                No eligible items found
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <TailwindPagination :data="eligibleItems" @pagination-change-page="getResults" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useToast } from 'vue-toastification';
import { TailwindPagination } from "laravel-vue-pagination";
import Swal from 'sweetalert2';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

const toast = useToast();

const props = defineProps({
    eligibleItems: Object,
    filters: Object
});

const facilityTypes = ["Regional Hospital", "District Hospital", "Health Centre", "Primary Health Unit"]

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || '10');
const facilityType = ref(props.filters.facility_type || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');

function getResults(page = 1){
    props.filters.page = page;
}

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (facilityType.value) query.facility_type = facilityType.value;
    if (perPage.value){
        query.page = 1;
        query.per_page = perPage.value;
    }
    if(props.filters.page) query.page = props.filters.page;

    router.get(route('products.eligible.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "eligibleItems"
        ]
    });
}

watch([
    () => search.value, 
    () => perPage.value, 
    () => props.filters.page,
    () => facilityType.value], 
    () => {
        updateRoute();
    }
);

const isDeleteing = ref(false);
function destroy(item) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isDeleteing.value = true;
            await axios.get(route('products.eligible.destroy', item.id))
                .then(() => {
                    isDeleteing.value = false;
                    toast.success('Eligible item deleted successfully');
                })
                .catch(() => {
                    isDeleteing.value = false;
                    toast.error('Error deleting eligible item');
                });
        }
    });
}
</script>
