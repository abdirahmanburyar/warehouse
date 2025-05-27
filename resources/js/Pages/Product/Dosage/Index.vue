<template>
    <AuthenticatedLayout
        title="Dosage Forms"
        description="Manage product dosage forms"
    >
        <div class="flex justify-between items-center">
            <div>
                <Link :href="route('products.index')" class="inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Products
                </Link>

                <h2 class="font-semibold text-xl text-black leading-tight">Dosage Forms</h2>
            </div>
            <Link
                :href="route('products.dosages.create')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
        <div class="flex justify-between items-center mt-4">
            <input
                v-model="search"
                type="text"
                placeholder="Search dosage forms..."
                class="w-[300px]"
            />
            <select
                v-model="perPage"
                class="w-[200px]"
            >
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <div class="py-6">
            <div v-if="!dosages.data.length" class="text-center py-12 bg-white rounded-lg shadow">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No dosage forms</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new dosage form.</p>
                <div class="mt-6">
                    <Link :href="route('products.dosages.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        New Dosage Form
                    </Link>
                </div>
            </div>
            <table v-else class="min-w-full">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 border border-black">
                            Name
                        </th>
                        <th class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 border border-black">
                            Created At
                        </th>
                        <th class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:text-gray-700 border border-black">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border border-black">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="dosage in dosages.data" :key="dosage.id" class="hover:bg-gray-50">
                        <td
                            class="px-6 py-4 border border-black"
                        >
                            <div class="text-sm font-medium text-gray-900">
                                {{ dosage.name }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-sm text-gray-500 border border-black"
                        >
                            {{ moment(dosage.created_at).format("DD/MM/YYYY") }}
                        </td>
                        <td
                            class="px-6 py-4 text-sm text-gray-500 border border-black"
                        >
                        <span 
                                :class="{
                                    'bg-green-100 text-green-800': dosage.is_active,
                                    'bg-red-100 text-red-800': !dosage.is_active
                                }"
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                            >
                                {{ dosage.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 text-left text-sm font-medium border border-black"
                        >
                            <div
                                class="flex items-center justify-start space-x-4"
                            >
                                <Link
                                    :href="
                                        route('products.dosages.edit', {
                                            dosage: dosage.id,
                                        })
                                    "
                                    class="text-indigo-600 hover:text-indigo-900 inline-flex items-center"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 mr-1"
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
                                        'opacity-50 cursor-wait':
                                            loadingDosages.has(dosage.id),
                                        'bg-gray-200': !dosage.is_active,
                                        'bg-green-500': dosage.is_active,
                                    }"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    :disabled="loadingDosages.has(dosage.id)"
                                >
                                    <span
                                        :class="{
                                            'translate-x-5': dosage.is_active,
                                            'translate-x-0': !dosage.is_active,
                                            'bg-gray-400 animate-pulse':
                                                loadingDosages.has(dosage.id),
                                        }"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    ></span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-4 flex justify-end">
                <TailwindPagination :data="dosages" @pagination-change-page="getResults" />
            </div>
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
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";
import moment from "moment";
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
const perPage = ref(props.filters.per_page || "10");

watch([
    () => search.value,
    () => perPage.value,
    () => props.filters.page
], () => {
    updateRoute();
})

function updateRoute() {
    const query = {};

    if (search.value) query.search = search.value;
    if (perPage.value) query.per_page = perPage.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("products.dosages.index"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
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
