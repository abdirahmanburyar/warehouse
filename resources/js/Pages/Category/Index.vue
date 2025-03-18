
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
                                            <div class="flex items-center">
                                                ID
                                                <SortIcon
                                                    :field="'id'"
                                                    :sort-field="filters.sort_field"
                                                    :sort-direction="filters.sort_direction"
                                                />
                                            </div>
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('name')">
                                            <div class="flex items-center">
                                                Name
                                                <SortIcon
                                                    :field="'name'"
                                                    :sort-field="filters.sort_field"
                                                    :sort-direction="filters.sort_direction"
                                                />
                                            </div>
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200">
                                            Description
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('is_active')">
                                            <div class="flex items-center">
                                                Status
                                                <SortIcon
                                                    :field="'is_active'"
                                                    :sort-field="filters.sort_field"
                                                    :sort-direction="filters.sort_direction"
                                                />
                                            </div>
                                        </th>
                                        <th class="py-3 px-4 uppercase font-semibold text-sm border-b-2 border-gray-200 cursor-pointer" @click="sort('created_at')">
                                            <div class="flex items-center">
                                                Created At
                                                <SortIcon
                                                    :field="'created_at'"
                                                    :sort-field="filters.sort_field"
                                                    :sort-direction="filters.sort_direction"
                                                />
                                            </div>
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
                                        <td class="py-3 px-4 border-b border-gray-200">{{ formatDate(category.created_at) }}</td>
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

        <!-- Category Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ form.id ? 'Edit Category' : 'Create New Category' }}
                </h2>

                <div v-if="errors" class="mt-3">
                    <div v-for="(messages, field) in errors" :key="field" class="text-sm text-red-600">
                        <div v-for="(message, i) in messages" :key="i">{{ message }}</div>
                    </div>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6 mt-4">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="form.name"
                            required
                            :disabled="isSubmitted || processing"
                            placeholder="Enter category name"
                        />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <textarea
                            id="description"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition"
                            v-model="form.description"
                            :disabled="isSubmitted || processing"
                            rows="3"
                            placeholder="Enter category description"
                        ></textarea>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="is_active"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            v-model="form.is_active"
                            :disabled="isSubmitted || processing"
                        />
                        <InputLabel for="is_active" value="Active" class="ml-2" />
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                            @click="closeModal"
                            :disabled="isSubmitted || processing"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                            :disabled="isSubmitted || processing"
                        >
                            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.id ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Category
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Are you sure you want to delete this category? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2"
                        @click="closeDeleteModal"
                        :disabled="processing"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 flex items-center"
                        @click="deleteCategory"
                        :disabled="processing"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Pagination from '@/Components/Pagination.vue';
import SortIcon from '@/Components/SortIcon.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    categories: Object,
    filters: Object,
});

// Form and modal state
const showModal = ref(false);
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);
const errors = ref(null);
const processing = ref(false);
const isSubmitted = ref(false);
const search = ref(props.filters.search || '');

// Form for creating/editing categories
const form = useForm({
    id: null,
    name: '',
    description: '',
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

// Search with debounce
let searchTimeout;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    processing.value = true;
    searchTimeout = setTimeout(() => {
        const query = {};
        
        if (search.value) {
            query.search = search.value;
        }
        
        if (props.filters.sort_field) {
            query.sort_field = props.filters.sort_field;
        }
        
        if (props.filters.sort_direction) {
            query.sort_direction = props.filters.sort_direction;
        }
        
        router.get(route('categories.index'), query, { 
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
            }
        });
    }, 300);
};

// Sort table columns
const sort = (field) => {
    processing.value = true;
    let direction = 'asc';
    
    // If already sorting by this field, toggle direction
    if (props.filters.sort_field === field) {
        direction = props.filters.sort_direction === 'asc' ? 'desc' : 'asc';
    }
    
    const query = {
        sort_field: field,
        sort_direction: direction
    };
    
    if (search.value) {
        query.search = search.value;
    }
    
    router.get(route('categories.index'), query, { 
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        }
    });
};

// Open modal for create/edit
const openModal = (category = null) => {
    errors.value = null;
    isSubmitted.value = false;
    
    if (category) {
        form.id = category.id;
        form.name = category.name;
        form.description = category.description || '';
        form.is_active = category.is_active;
    } else {
        form.reset();
        form.is_active = true;
    }
    
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
};

// Submit form
const submitForm = () => {
    isSubmitted.value = true;
    processing.value = true;
    errors.value = null;
    
    axios.post(route('categories.store'), form.data())
        .then(response => {
            if (response.data.success) {
                toast.success(response.data.message);
                closeModal();
                reloadCategories();
            } else {
                toast.error(response.data.message || 'An error occurred');
            }
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.errors) {
                errors.value = error.response.data.errors;
                toast.error('Please correct the errors in the form');
            } else {
                toast.error('An error occurred while saving the category');
            }
        })
        .finally(() => {
            processing.value = false;
            isSubmitted.value = false;
        });
};

// Delete category
const confirmDelete = (category) => {
    categoryToDelete.value = category;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};

const deleteCategory = () => {
    if (!categoryToDelete.value) return;
    
    processing.value = true;
    
    axios.delete(route('categories.destroy', categoryToDelete.value.id))
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

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};
</script>

<style scoped>
/* Add any custom styles here */
</style>