<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-4">
            <div>
                <Link :href="route('inventories.index')">Go Back to inventory</Link>
                <h3 class="text-lg font-medium text-gray-900">Locations</h3>
            </div>
            <Link :href="route('inventories.location.create')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-150 ease-in-out">
                Create New Location
            </Link>
        </div>

        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-grow min-w-[200px]">
                    <input 
                        type="text" 
                        v-model="search" 
                        placeholder="Search locations..." 
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                </div>
                <div class="w-[220px]">
                    <Multiselect 
                        v-model="warehouse" 
                        :options="warehouses" 
                        placeholder="Select Warehouse" 
                        :searchable="true"
                        :close-on-select="false" 
                        :clear-on-select="false" 
                        :preserve-search="true"
                    > </Multiselect>
                </div>
            </div>
        </div>
        
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <table class="min-w-full border border-black">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[100px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white border border-black">
                        <tr v-for="(l, i) in props.locations" :key="l.id">
                            <td class="px-6 py-4  border border-black">{{ i + 1 }}</td>
                            <td class="px-6 py-4  border border-black">{{ l.location }}</td>
                            <td class="px-6 py-4  border border-black">{{ l.warehouse?.name }}</td>
                            <td class="px-6 py-4  border border-black text-right text-sm font-medium">
                                <button @click="editLocation(l.id)" class="text-indigo-600 hover:text-indigo-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import moment from "moment";
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

const props = defineProps({
    locations: {
        required: true,
        type: Array
    },
    warehouses: Array,
    filters: Object
});

const search =  ref(props.filters?.search || '');
const warehouse =  ref(props.filters?.warehouse || [])

watch([
    () => search.value,
    () => warehouse.value,
], () => {
    getLocations();
});

function getLocations() {
    const query = {}
    if(search.value) query.search = search.value;
    if(warehouse.value) query.warehouse = warehouse.value;
    router.get(route('inventories.location.index'), query, {
        preserveState: true,
        replace: true,
        only: [
            'locations', 'warehouses'
        ]
    });
}


</script>