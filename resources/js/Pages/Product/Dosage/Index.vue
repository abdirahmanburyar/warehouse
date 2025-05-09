<template>
    <AuthenticatedLayout title="Dosage Forms" description="Manage product dosage forms">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dosage Forms</h2>
            <div class="flex items-center space-x-4">
               
                <div class="flex items-center space-x-4">
                    <div class="flex items-center bg-white rounded-lg shadow px-4 py-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search dosage forms..."
                            class="bg-transparent border-0 focus:ring-0 text-sm w-64"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <select
                        v-model="perPage"
                        class="bg-white min-w-[200px] border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
                <Link
                    :href="route('products.dosages.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Dosage Form
                </Link>
            </div>
        </div>

        <div class="py-6">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="relative">
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden border border-black rounded-lg">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-black">
                                            <thead class="bg-gray-50 sticky top-0 z-10">
                                                <tr>
                                                    <th @click="sort('name')" class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black bg-gray-50">
                                                        <div class="flex items-center justify-between">
                                                            <span>Name</span>
                                                            <SortIcon :field="'name'" :currentSort="sort_field" :direction="sort_direction" />
                                                        </div>
                                                    </th>
                                                    <th @click="sort('description')" class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black bg-gray-50">
                                                        <div class="flex items-center justify-between">
                                                            <span>Description</span>
                                                            <SortIcon :field="'description'" :currentSort="sort_field" :direction="sort_direction" />
                                                        </div>
                                                    </th>
                                                    <th @click="sort('created_at')" class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black bg-gray-50">
                                                        <div class="flex items-center justify-between">
                                                            <span>Created At</span>
                                                            <SortIcon :field="'created_at'" :currentSort="sort_field" :direction="sort_direction" />
                                                        </div>
                                                    </th>
                                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-black bg-gray-50">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-black">
                                                <tr v-for="dosage in dosages.data" :key="dosage.id" class="hover:bg-gray-50 border-b border-black">
                                                    <td class="px-6 py-4 whitespace-nowrap border-r border-black">
                                                        <div class="text-sm font-medium text-gray-900">{{ dosage.name }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                                        {{ dosage.description || '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                                        {{ dosage.created_at }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border-r border-black">
                                                        <Link :href="route('products.dosages.edit', { dosage: dosage.id })" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </Link>
                                                        <button @click="deleteDosage(dosage)" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ ((dosages.meta.current_page - 1) * dosages.meta.per_page) + 1 }} to {{ Math.min(dosages.meta.current_page * dosages.meta.per_page, dosages.meta.total) }} of {{ dosages.meta.total }} results
                        </div>
                        <div class="flex items-center space-x-4">
                            <Link
                                v-if="dosages.links.prev"
                                :href="dosages.links.prev"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <span v-else class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                                Previous
                            </span>
                            <Link
                                v-if="dosages.links.next"
                                :href="dosages.links.next"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Next
                            </Link>
                            <span v-else class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed">
                                Next
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import SortIcon from '@/Components/SortIcon.vue';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import Pagination from '@/Components/Pagination.vue';
import { debounce } from 'lodash';

const toast = useToast();

const props = defineProps({
    dosages: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const search = ref(props.filters.search || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || '10');

function updateRoute() {
    const query = {};
    
    if (search.value) query.search = search.value;
    if (sort_field.value !== 'created_at') query.sort_field = sort_field.value;
    if (sort_direction.value !== 'desc') query.sort_direction = sort_direction.value;
    if (perPage.value !== '10') query.per_page = perPage.value;

    router.get(route('products.dosages.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}

watch([() => search.value, () => perPage.value ], debounce(([searchValue, perPageValue]) => {
    const query = {};
    
    if (searchValue) query.search = searchValue;
    if (sort_field.value !== 'created_at') query.sort_field = sort_field.value;
    if (sort_direction.value !== 'desc') query.sort_direction = sort_direction.value;
    if (perPageValue !== '10') query.per_page = perPageValue;

    router.get(route('products.dosages.index'), query, {
        preserveState: true,
        preserveScroll: true
    });
}, 300));

function sort(field) {
    if (field === sort_field.value) {
        sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort_field.value = field;
        sort_direction.value = 'asc';
    }
    updateRoute();
}

function deleteDosage(dosage) {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete the dosage form "${dosage.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('products.dosages.destroy', dosage.id), {
                onSuccess: () => {
                    toast.success('Dosage form deleted successfully');
                },
                onError: () => {
                    toast.error('Error deleting dosage form');
                }
            });
        }
    });
}
</script>]]>
