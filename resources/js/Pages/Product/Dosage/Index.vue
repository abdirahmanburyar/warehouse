<template>
    <AuthenticatedLayout
        title="Dosage Forms"
        description="Manage product dosage forms"
        img="/assets/images/products.png"
    >
        <div class="flex justify-between items-center mb-6">
            <div>
                <Link :href="route('products.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Products
                </Link>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dosage Forms</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage product dosage forms and classifications</p>
                </div>
            </div>
            <Link
                :href="route('products.dosages.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 shadow-sm"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-2"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                        clip-rule="evenodd"
                    />
                </svg>
                Create Dosage Form
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
                                placeholder="Search dosage forms..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <select
                            v-model="perPage"
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

        <!-- Dosage Forms Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div v-if="!dosages.data.length" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No dosage forms</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new dosage form.</p>
                <div class="mt-6">
                    <Link :href="route('products.dosages.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Dosage Form
                    </Link>
                </div>
            </div>
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Name</span>
                                    <SortIcon :field="sortField" :direction="sortDirection" field-name="name" />
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 transition-colors duration-200">
                                <div class="flex items-center space-x-1">
                                    <span>Status</span>
                                    <SortIcon :field="sortField" :direction="sortDirection" field-name="is_active" />
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="dosage in dosages.data" :key="dosage.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ dosage.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        dosage.is_active 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'
                                    ]"
                                >
                                    <span :class="[
                                        'w-2 h-2 rounded-full mr-1.5',
                                        dosage.is_active ? 'bg-green-400' : 'bg-red-400'
                                    ]"></span>
                                    {{ dosage.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <Link
                                        :href="route('products.dosages.edit', { dosage: dosage.id })"
                                        class="text-blue-600 hover:text-blue-900 transition-colors duration-200 p-1 rounded-md hover:bg-blue-50"
                                        title="Edit Dosage Form"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="confirmToggleStatus(dosage)"
                                        :class="{
                                            'opacity-50 cursor-wait': loadingDosages.has(dosage.id),
                                            'bg-gray-200': !dosage.is_active,
                                            'bg-green-500': dosage.is_active,
                                        }"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        :disabled="loadingDosages.has(dosage.id)"
                                        title="Toggle Status"
                                    >
                                        <span
                                            :class="{
                                                'translate-x-5': dosage.is_active,
                                                'translate-x-0': !dosage.is_active,
                                                'bg-gray-400 animate-pulse': loadingDosages.has(dosage.id),
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
            <TailwindPagination :data="dosages" @pagination-change-page="getResults" />
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import SortIcon from "@/Components/SortIcon.vue";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import axios from "axios";
import {TailwindPagination} from "laravel-vue-pagination";

const toast = useToast();

const loadingDosages = ref(new Set());

const props = defineProps({
    dosages: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || "");
const perPage = ref(props.filters.per_page || "25");
const status = ref(props.filters.status || "");
watch([
    () => search.value,
    () => status.value,
    () => perPage.value,
    () => props.filters.page
], () => {
    updateRoute();
})

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (status.value) query.status = status.value;
    if (perPage.value) query.per_page = perPage.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("products.dosages.index"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: [
            'dosages'
        ]
    });
}

function getResults(page=1) {
    props.filters.page = page;
}

const confirmToggleStatus = (dosage) => {
    const action = dosage.is_active ? "deactivate" : "activate";

    Swal.fire({
        title: "Are you sure?",
        html: `<p>Do you want to ${action} ${dosage.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: dosage.is_active ? "#d33" : "#3085d6",
        cancelButtonColor: "#6b7280",
        confirmButtonText: dosage.is_active
            ? "Yes, deactivate!"
            : "Yes, activate!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            loadingDosages.value.add(dosage.id);
            try {
                await axios.get(
                    route("products.dosages.toggle-status", dosage.id)
                );
                updateRoute();
                Swal.fire(
                    action === "activate" ? "Activated!" : "Deactivated!",
                    `Dosage has been ${action}d.`,
                    "success"
                );
            } catch (error) {
                toast.error(error.response?.data || "An error occurred");
            } finally {
                loadingDosages.value.delete(dosage.id);
            }
        }
    });
};

function deleteDosage(dosage) {
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to delete the dosage form "${dosage.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, delete it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            await axios
                .delete(route("products.dosages.destroy", dosage.id))
                .then(() => {
                    toast.success("Dosage form deleted successfully");
                    updateRoute();
                })
                .catch(() => {
                    toast.error("Error deleting dosage form");
                });
        }
    });
}
</script>
