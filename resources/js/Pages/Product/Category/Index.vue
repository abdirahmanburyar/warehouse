<template>
    <AuthenticatedLayout title="Categories" description="Manage product categories" img="/assets/images/products.png">
        <div class="flex justify-between items-center mb-6">
            <div>
                <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                    <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                        Products
                    </Link>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="text-gray-900">Categories</span>
                </div>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
                </div>
            </div>
            <Link
                :href="route('products.categories.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 shadow-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Create Category
            </Link>
        </div>

        <!-- Search and Filters Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search categories..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <select 
                            v-model="per_page" 
                            @change="props.filters.page = 1"
                            class="px-3 py-2.5 w-[200px] border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="!categories.data.length" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No categories</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new category.</p>
                <div class="mt-6">
                    <Link :href="route('products.categories.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Category
                    </Link>
                </div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                    <thead>
                        <tr style="background-color: #F4F7FB;">
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Name</th>
                            <th class="px-3 py-2 text-xs font-bold text-right" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="category in categories.data"
                            :key="category.id"
                            class="hover:bg-gray-50 transition-colors duration-150 border-b"
                            style="border-bottom: 1px solid #B7C6E6;"
                        >
                            <td class="px-3 py-2">
                                <div class="text-xs font-medium text-gray-800 capitalize">
                                    {{ category.name }}
                                </div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-center">
                                <div class="flex items-center justify-end space-x-2">
                                    <Link
                                        :href="route('products.categories.edit', { category: category.id })"
                                        class="inline-flex items-center p-1.5 border border-transparent rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200"
                                        title="Edit Category"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </Link>
                                    <button
                                        @click="confirmToggleStatus(category)"
                                        :class="{
                                            'opacity-50 cursor-wait': loadingCategories.has(category.id),
                                            'bg-gray-200': !category.is_active,
                                            'bg-indigo-600': category.is_active,
                                        }"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        :disabled="loadingCategories.has(category.id)"
                                        title="Toggle Status"
                                    >
                                        <span
                                            :class="{
                                                'translate-x-5': category.is_active,
                                                'translate-x-0': !category.is_active,
                                                'bg-gray-400 animate-pulse': loadingCategories.has(category.id),
                                            }"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        ></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <TailwindPagination :limit="2" :data="categories" @pagination-change-page="getResults" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
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
const per_page = ref(props.filters.per_page || 25);

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
        only: ['categories']
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
