<template>
    <AuthenticatedLayout title="Categories" description="Manage product categories">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center bg-white rounded-lg shadow px-4 py-2">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search categories..."
                        class="bg-transparent border-0 focus:ring-0 text-sm w-64"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <Link
                    :href="route('products.categories.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Category
                </Link>
               
            </div>
        </div>

        <div class="py-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-black border border-black">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Name
                                        <SortIcon :field="'name'" :currentSort="sort_field" :direction="sort_direction" />
                                    </th>
                                    <th @click="sort('description')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Description
                                        <SortIcon :field="'description'" :currentSort="sort_field" :direction="sort_direction" />
                                    </th>
                                    <th @click="sort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Created At
                                        <SortIcon :field="'created_at'" :currentSort="sort_field" :direction="sort_direction" />
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-black">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-black">
                                <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 border-b border-black">
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-black">
                                        <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                        {{ category.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                        {{ category.created_at }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border-r border-black">
                                        <Link :href="route('products.categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button @click="confirmDelete(category)" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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

const toast = useToast();

const props = defineProps({
    categories: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const search = ref(props.filters.search || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');

function updateRoute() {
    const query = {};
    
    if (search.value) query.search = search.value;
    if (sort_field.value) query.sort_field = sort_field.value;
    if (sort_direction.value) query.sort_direction = sort_direction.value;

    router.get(route('products.categories.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['categories', 'filters']
    });
}

watch(() => search.value, () => {
    updateRoute();
});

const sort = (field) => {
    sort_field.value = field;
    sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';
    updateRoute();
};

const confirmDelete = (category) => {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('products.categories.destroy', category.id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Category has been deleted.',
                        'success'
                    );
                    updateRoute();
                },
                onError: (error) => {
                    toast.error(error);
                }
            });
        }
    });
};
</script>
