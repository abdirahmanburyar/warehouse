<template>
    <AuthenticatedLayout title="Products" description="products">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Products</h2>
            <div class="flex items-center space-x-4">
                
                <div class="space-y-4">
                    <!-- Search and Filters -->
                    <div class="flex flex-wrap gap-4 items-center">
                        <!-- Search -->
                        <div class="flex items-center bg-gray-50 rounded px-4 py-2 flex-grow md:flex-grow-0 w-full md:w-64">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name or barcode..."
                                class="bg-transparent border-0 focus:ring-0 text-sm w-full"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Category Filter -->
                        <div class="w-full md:w-auto">
                            <select
                                v-model="category_id"
                                class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories.data" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Dosage Filter -->
                        <div class="w-full md:w-auto">
                            <select
                                v-model="dosage_id"
                                class="w-full md:w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="">All Dosage Forms</option>
                                <option v-for="dosage in dosages.data" :key="dosage.id" :value="dosage.id">
                                    {{ dosage.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <Link
                    :href="route('products.categories.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z" />
                        <path d="M11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
                    </svg>
                    Categories
                </Link>
                <Link
                    :href="route('products.dosages.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M17.725 4.275a1.75 1.75 0 00-2.475 0L11 8.525V5a1 1 0 00-2 0v7a1 1 0 001 1h7a1 1 0 000-2h-3.525l4.25-4.25a1.75 1.75 0 000-2.475z" clip-rule="evenodd" />
                        <path d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 10-2 0v7H5V5h7a1 1 0 100-2H5z" />
                    </svg>
                    Dosage Forms
                </Link>
                <Link
                    :href="route('products.eligible.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1.323l-3.954 1.582A1 1 0 004 6.82v10.36a1 1 0 001.046.976l4-1.5a1 1 0 01.908 0l4 1.5A1 1 0 0015 17.18V6.82a1 1 0 00-1.046-.976L10 4.323V3a1 1 0 00-1-1zm0 2.618l4 1.6v9.464l-4-1.5V4.618zm-2 0L4 6.218v9.464l4-1.5V4.618z" clip-rule="evenodd" />
                    </svg>
                    Eligible List
                </Link>
                <Link
                    :href="route('products.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Product
                </Link>
            </div>
        </div>

        <div class="py-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-black border border-black">
                            <colgroup>
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col class="border-r border-black">
                                <col>
                            </colgroup>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="sort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Name
                                        <SortIcon :field="'name'" :currentSort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('category_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Category
                                        <SortIcon :field="'category_id'" :currentSort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('dosage_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Dosage Form
                                        <SortIcon :field="'dosage_id'" :currentSort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th @click="sort('reorder_level')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 border-r border-black">
                                        Reorder Level
                                        <SortIcon :field="'reorder_level'" :currentSort="filters.sort_field" :direction="filters.sort_direction" />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 border-b border-black">
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-black">
                                        <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-sm text-gray-500">Barcode: {{ product.barcode }}</div>
                                        <div class="text-sm text-gray-500">Dose: {{ product.dose }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                        {{ product.category?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                        {{ product.dosage?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 border-r border-black">
                                        {{ product.reorder_level }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap border-r border-black">
                                        <span :class="[product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">
                                            {{ product  }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium border-r border-black">
                                        <Link :href="route('products.edit', product.id)" class="text-indigo-600 hover:text-indigo-900 mr-3 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>                                               
                                        </Link>
                                        <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <!-- Per Page Selector -->
                        <div class="flex justify-end mb-4">
                            <select
                                v-model="perPage"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                            >
                                <option value="10">10 per page</option>
                                <option value="25">25 per page</option>
                                <option value="50">50 per page</option>
                                <option value="100">100 per page</option>
                            </select>
                        </div>

                        <!-- Pagination Info and Links -->
                        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <button
                                    @click="goToPage(props.products.meta.current_page - 1)"
                                    :disabled="props.products.meta.current_page <= 1"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >Previous</button>
                                <button
                                    @click="goToPage(props.products.meta.current_page + 1)"
                                    :disabled="props.products.meta.current_page >= props.products.meta.last_page"
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >Next</button>
                            </div>

                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{ props.products.meta.from }}</span>
                                        to
                                        <span class="font-medium">{{ props.products.meta.to }}</span>
                                        of
                                        <span class="font-medium">{{ props.products.meta.total }}</span>
                                        results
                                    </p>
                                </div>

                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <button
                                            v-for="page in totalPages"
                                            :key="page"
                                            @click="goToPage(page)"
                                            :class="[
                                                page === props.products.meta.current_page ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                                            ]"
                                        >{{ page }}</button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { defineProps, ref, defineComponent, h, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Swal from 'sweetalert2';
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    products: {
        type: Object,
        required: true
    },
    categories: {
        type: Object,
        required: true
    },
    dosages: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
});

const search = ref(props.filters.search || '');
const category_id = ref(props.filters.category_id || '');
const dosage_id = ref(props.filters.dosage_id || '');
const sort_field = ref(props.filters.sort_field || 'created_at');
const sort_direction = ref(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || '10');

function updateRoute() {
    const query = {};
    
    if (search.value) query.search = search.value;
    if (category_id.value) query.category_id = category_id.value;
    if (dosage_id.value) query.dosage_id = dosage_id.value;
    if (sort_field.value) query.sort_field = sort_field.value;
    if (sort_direction.value) query.sort_direction = sort_direction.value;
    if (perPage.value) query.per_page = perPage.value;

    router.get(route('products.index'), query, {
        preserveState: true,
        preserveScroll: true,
        only: [
            "products", "categories", "dosages",'filters'
        ]
    });
}

watch([
    () => search.value, 
    () => category_id.value, 
    () => dosage_id.value,
    () => perPage.value
], () => {
    updateRoute();
});

const sort = (field) => {
    sort_field.value = field;
    sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';
    updateRoute();
};

const goToPage = (page) => {
    const currentFilters = {
        ...props.filters,
        page: page
    };
    
    router.get(route('products.index'), currentFilters, {
        preserveState: true,
        preserveScroll: true,
        only: ['products', 'filters']
    });
};

const totalPages = computed(() => {
    if (!props.products?.meta?.last_page) return [];
    return Array.from({ length: props.products.meta.last_page }, (_, i) => i + 1);
});


const confirmDelete = (product) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete ${product.name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('products.destroy', product.id), {
                onSuccess: () => {
                    Swal.fire(
                        'Deleted!',
                        'Product has been deleted.',
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

<script>
// Sort icon component
const SortIcon = defineComponent({
    props: {
        field: String,
        currentSort: String,
        direction: String
    },
    setup(props) {
        return () => h('svg', {
            class: 'ml-1 w-3 h-3 inline',
            viewBox: '0 0 24 24',
            fill: 'none',
            stroke: 'currentColor',
            style: props.field === props.currentSort ? '' : 'display: none'
        }, [
            props.direction === 'asc'
                ? h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M5 15l7-7 7 7'
                })
                : h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M19 9l-7 7-7-7'
                })
        ])
    }
});

export default {
    components: {
        SortIcon
    }
};
</script>