<template>
    <AuthenticatedLayout title="Categories" description="Manage product categories">
        <div class="flex justify-between items-center mb-5">
            <div>
                <Link :href="route('products.index')" class="inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Products
                </Link>

                <h2 class="font-semibold text-xl text-black leading-tight">Categories</h2>
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
       <div class="flex justify-between mb-4">
        <input
            v-model="search"
            type="text"
            placeholder="Search categories..."
            class="w-[300px]"
        >
        <select v-model="per_page" class="w-[200px]">
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
        </select>
        </div>

        <div class="py-6">
            <div v-if="!categories.data.length" class="text-center py-12 bg-white rounded-lg shadow">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                <div class="mt-6">
                    <Link :href="route('products.categories.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Category
                    </Link>
                </div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-black">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase cursor-pointer hover:text-gray-700 border border-black">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase cursor-pointer hover:text-gray-700 border border-black">
                                Description
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase cursor-pointer hover:text-gray-700 border border-black">
                                Created At
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-black uppercase border border-black">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-black uppercase border border-black">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-black">
                        <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 border-b border-black">
                            <td class="px-6 py-4 border border-black">
                                <div class="text-sm font-medium text-black">{{ category.name }}</div>
                                </td>
                            <td class="px-6 py-4 text-sm text-black border border-black">
                                {{ category.description || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-black border border-black">
                                {{ moment(category.created_at).format('DD/MM/YYYY') }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-medium border border-black">
                            <span 
                                :class="{
                                    'bg-green-100 text-green-800': category.is_active,
                                    'bg-red-100 text-red-800': !category.is_active
                                }"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ category.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium border border-black">
                                <Link :href="route('products.categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            <button 
                                @click="confirmToggleStatus(category)" 
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-300 focus:outline-none"
                                :class="{
                                    'bg-red-600': category.is_active,
                                    'bg-green-600': !category.is_active,
                                    'opacity-50 cursor-wait': loadingCategories.has(category.id)
                                }"
                                :disabled="loadingCategories.has(category.id)"
                            >
                                <span 
                                    class="inline-block h-4 w-4 transform rounded-full transition-transform duration-300"
                                    :class="{
                                        'translate-x-6': category.is_active,
                                        'translate-x-1': !category.is_active,
                                        'bg-white': !loadingCategories.has(category.id),
                                        'bg-gray-200 animate-pulse': loadingCategories.has(category.id)
                                    }"
                                />
                            </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
           <div class="mt-4 flex justify-end">
            {{props.categories}}
            <TailwindPagination :data="categories" @page-changed="getResults" />
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
import moment from 'moment';
import { TailwindPagination } from "laravel-vue-pagination";
import axios from 'axios';

const toast = useToast();
const loadingCategories = ref(new Set());

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
const per_page = ref(props.filters.per_page || 10);

watch([() => search.value, () => per_page.value, () => props.filters.page], () => {
    updateRoute();
});

function updateRoute() {
    const query = {};
    
    if (search.value) query.search = search.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route('products.categories.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: ['categories', 'filters']
    });
}

function getResults(page = 1) {
    console.log(page);
    props.filters.page = page;
}

const confirmToggleStatus = (category) => {
    const action = category.is_active ? 'deactivate' : 'activate';
    
    Swal.fire({
        title: 'Are you sure?',
        html: `<p>Do you want to ${action} ${category.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: category.is_active ? '#d33' : '#3085d6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: category.is_active ? 'Yes, deactivate!' : 'Yes, activate!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            loadingCategories.value.add(category.id);
            try {
                await axios.get(route('products.categories.toggle-status', category.id));
                updateRoute();
                Swal.fire(
                    action === 'activate' ? 'Activated!' : 'Deactivated!',
                    `Category has been ${action}d.`,
                    'success'
                );
            } catch (error) {
                toast.error(error.response?.data || 'An error occurred');
            } finally {
                loadingCategories.value.delete(category.id);
            }
        }
    });
};

</script>
