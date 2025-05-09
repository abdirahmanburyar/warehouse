<template>
    <AuthenticatedLayout title="Eligible Items" description="Manage product eligibility for facilities">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Eligible Items</h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center min-w-[300px] bg-white rounded-lg shadow px-4 py-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="border-0 w-full focus:ring-0 p-0 text-sm"
                        />
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    
                    <select
                        v-model="facilityType"
                        class="bg-white min-w-[200px] border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                    >
                        <option value="">All Facility Types</option>
                        <option value="Regional Hospital">Regional Hospital</option>
                        <option value="District Hospital">District Hospital</option>
                        <option value="Health Centre">Health Centre</option>
                        <option value="Primary Health Unit">Primary Health Unit</option>
                    </select>
                    <select
                        v-model="perPage"
                        class="bg-white min-w-[180px] border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
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
        </div>

        <div class="py-6">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 cursor-pointer" @click="sort('product.name')">
                                        Item Name
                                        <SortIcon :field="'product.name'" :sort-field="filters.sort_field" :sort-direction="filters.sort_direction" />
                                    </th>
                                    <th scope="col" class="px-6 py-3 cursor-pointer" @click="sort('facility_type')">
                                        Facility Type
                                        <SortIcon :field="'facility_type'" :sort-field="filters.sort_field" :sort-direction="filters.sort_direction" />
                                    </th>
                                    <th scope="col" class="px-6 py-3 cursor-pointer" @click="sort('is_active')">
                                        Status
                                        <SortIcon :field="'is_active'" :sort-field="filters.sort_field" :sort-direction="filters.sort_direction" />
                                    </th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in eligibleItems.data" :key="item.id" class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ item.product.name }}
                                    </td>
                                    <td class="px-6 py-4">{{ item.facility_type }}</td>
                                    <td class="px-6 py-4">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            item.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]">
                                            {{ item.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
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
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No eligible items found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="eligibleItems.meta.links"
                        :meta="eligibleItems.meta"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useToast } from 'vue-toastification';
import Pagination from '@/Components/Pagination.vue';
import SortIcon from '@/Components/SortIcon.vue';

const toast = useToast();

const props = defineProps({
    eligibleItems: Object,
    filters: Object
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || '10');
const facilityType = ref(props.filters.facility_type || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (facilityType.value) query.facility_type = facilityType.value;
    if (sort_field.value !== 'created_at') query.sort_field = sort_field.value;
    if (sort_direction.value !== 'desc') query.sort_direction = sort_direction.value;
    if (perPage.value !== '10') query.per_page = perPage.value;

    router.get(route('products.eligible.index'), query, {
        preserveState: true,
        preserveScroll: true
    });
}

watch([() => search.value, () => perPage.value, () => facilityType.value], debounce(([searchValue, perPageValue, facilityTypeValue]) => {
    const query = {};
    
    if (searchValue) query.search = searchValue;
    if (facilityTypeValue) query.facility_type = facilityTypeValue;
    if (sort_field.value !== 'created_at') query.sort_field = sort_field.value;
    if (sort_direction.value !== 'desc') query.sort_direction = sort_direction.value;
    if (perPageValue !== '10') query.per_page = perPageValue;

    router.get(route('products.eligible.index'), query, {
        preserveState: true,
        preserveScroll: true
    });
}, 300));

function sort(field) {
    if (sort_field.value === field) {
        sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort_field.value = field;
        sort_direction.value = 'asc';
    }
    updateRoute();
}

function destroy(item) {
    if (confirm('Are you sure you want to delete this eligible item?')) {
        router.delete(route('products.eligible.destroy', item.id), {
            onSuccess: () => {
                toast.success('Eligible item deleted successfully');
            },
            onError: () => {
                toast.error('Error deleting eligible item');
            }
        });
    }
}
</script>
