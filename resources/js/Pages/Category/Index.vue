
<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Categories</h2>
        </template>

        <div class="mt-3">
            <div class="max-w-full mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Search and Add Button -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
                            <div class="w-full md:w-1/3">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search by name or description..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"
                                        @input="debouncedSearch"
                                    />
                                    <div v-if="processing" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="openModal()"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 flex items-center justify-center shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 01-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Add New
                            </button>
                        </div>

                        <!-- Categories Table -->
                        <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto relative">
                            <table class="border-collapse table-auto w-full whitespace-no-wrap bg-white table-striped relative">
                                <thead>
                                    <tr class="text-left">
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('id')">
                                            SN
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('name')">
                                            Name
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('is_active')">
                                            Status
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-100 transition-colors duration-150">
                                        <td class="py-3 px-4 border-b border-gray-200">{{ category.id }}</td>
                                        <td class="py-3 px-4 border-b border-gray-200">{{ category.name }}</td>
                                        <td class="py-3 px-4 border-b border-gray-200">
                                            <div class="max-w-xs truncate">{{ category.description || 'No description' }}</div>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200">
                                            <span 
                                                :class="[
                                                    'px-2 py-1 rounded-full text-xs font-medium', 
                                                    category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                ]"
                                            >
                                                {{ category.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-200">
                                            <div class="flex items-center space-x-2">
                                                <button 
                                                    @click="openModal(category)"
                                                    class="text-blue-600 hover:text-blue-900 transition"
                                                    title="Edit"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button 
                                                    @click="confirmDelete(category)"
                                                    class="text-red-600 hover:text-red-900 transition"
                                                    title="Delete"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="categories.data.length === 0">
                                        <td colspan="6" class="py-6 text-center text-gray-500">No categories found</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <Pagination :links="categories.meta.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SortIcon from '@/Components/SortIcon.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    categories: Object,
    filters: Object,
});

// Form and modal state
const errors = ref(null);
const processing = ref(false);
const isSubmitted = ref(false);
const search = ref(props.filters.search || '');

// Form for creating/editing categories
const form = useForm({
    id: null,
    name: '',
    is_active: true,
});

// Reload categories
function reloadCategories() {
    const query = {}
    router.get(route('categories.index'), query, { 
        preserveState: true,
        preserveScroll: true,
        only: ['categories', 'filters']
    });
}


const deleteCategory = async () => {
    if (!categoryToDelete.value) return;
    
    processing.value = true;
    
    await axios.delete(route('categories.destroy', categoryToDelete.value.id))
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message);
                closeDeleteModal();
                reloadCategories();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            toast.error('An error occurred while deleting the category');
            console.error(error);
        })
        .finally(() => {
            processing.value = false;
        });
};

</script>