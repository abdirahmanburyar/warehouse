<template>
    <Head title="Warehouses" />

    <AuthenticatedLayout title="Warehouse Management" description="Warehouse" img="/assets/images/facility.png">
        <div class="mb-[100px]">
            <!-- Header & Actions -->
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Warehouse Management</h1>
                <div class="flex flex-wrap gap-2 md:gap-4 items-center">
                    <Link
                        :href="route('inventories.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Inventory
                    </Link>
                    <Link
                        :href="route('inventories.warehouses.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                    >
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Warehouse
                    </Link>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-4 border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    <div class="col-span-1 md:col-span-2 min-w-0">
                        <input
                            v-model="search"
                            type="text"
                            class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Search by name, address, manager..."
                        />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <Multiselect
                            v-model="region"
                            :options="props.regions"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select Region"
                            :allow-empty="true"
                            class="multiselect--with-icon w-full"
                        />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <Multiselect
                            v-model="district"
                            :options="props.districts"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            placeholder="Select District"
                            :allow-empty="true"
                            class="multiselect--with-icon w-full"
                        />
                    </div>
                    <div class="col-span-1 min-w-0">
                        <select
                            v-model="status"
                            class="w-full rounded-lg border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end items-center gap-4 mt-3">
                    <select
                        v-model="per_page"
                        class="rounded-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-[200px] mb-3"
                        @change="props.filters.page = 1"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>

            <!-- Warehouse Table -->
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <table class="w-full overflow-hidden text-sm text-left table-sm rounded-t-lg">
                    <thead>
                        <tr style="background-color: #F4F7FB;">
                            <th class="px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Name</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Address</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Region</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">District</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Manager</th>
                            <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Status</th>
                            <th class="px-3 py-2 text-xs font-bold rounded-tr-lg" style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        <template v-if="props.warehouses.data.length === 0">
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500 bg-gray-50">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span>No warehouses found.</span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr
                                v-for="(warehouse, index) in props.warehouses.data"
                                :key="warehouse.id"
                                class="hover:bg-gray-50 transition-colors duration-150 border-b"
                                style="border-bottom: 1px solid #B7C6E6;"
                            >
                                <td class="px-3 py-2 text-xs font-medium text-gray-800 align-middle">
                                    {{ warehouse.name }}
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 align-middle">
                                    {{ warehouse.address }}
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 align-middle">
                                    {{ warehouse.region }}
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 align-middle">
                                    {{ warehouse.district }}
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ warehouse.manager_name }}</span>
                                        <span class="text-gray-500">{{ warehouse.manager_email }}</span>
                                        <span class="text-gray-500">{{ warehouse.manager_phone }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-xs align-middle">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-green-100 text-green-800': warehouse.status === 'active',
                                            'bg-red-100 text-red-800': warehouse.status === 'inactive'
                                        }"
                                    >
                                        {{ warehouse.status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs align-middle">
                                    <div class="flex items-center justify-center space-x-3">
                                        <Link
                                            :href="route('inventories.warehouses.edit', warehouse.id)"
                                            class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-white hover:bg-indigo-600 border-2 border-indigo-200 hover:border-indigo-600 rounded-lg transition-all duration-200 transform hover:scale-105"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="confirmToggleStatus(warehouse, index)"
                                            :class="{
                                                'bg-gray-200 hover:bg-gray-300': warehouse.status === 'inactive',
                                                'bg-green-500 hover:bg-green-600': warehouse.status === 'active',
                                            }"
                                            :disabled="isDoing[index]"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            <span
                                                :class="{
                                                    'translate-x-5': warehouse.status === 'active',
                                                    'translate-x-0': warehouse.status === 'inactive',
                                                }"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                            ></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                        <div class="text-sm text-gray-700 font-medium">
                            Showing <span class="font-bold text-gray-900">{{ props.warehouses.meta.from || 0 }}</span> to 
                            <span class="font-bold text-gray-900">{{ props.warehouses.meta.to || 0 }}</span> of 
                            <span class="font-bold text-gray-900">{{ props.warehouses.meta.total || 0 }}</span> warehouses
                        </div>
                        <TailwindPagination
                            :data="props.warehouses"
                            :limit="2"
                            class="flex items-center space-x-2"
                            @pagination-change-page="getResults"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import axios from "axios";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { TailwindPagination } from "laravel-vue-pagination";

const toast = useToast();

// Props
const props = defineProps({
    warehouses: Object,
    filters: Object,
    regions: Array,
    districts: Array,
});

// Reactive state
const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
const per_page = ref(props.filters?.per_page || 25);
const region = ref(props.filters.region);
const district = ref(props.filters.district);

function getResults(page) {
    props.filters.page = page;
}

// Watch for search and filter changes
watch(
    [
        () => search.value,
        () => per_page.value,
        () => status.value,
        () => region.value,
        () => district.value,
        () => props.filters.page,
    ],
    () => {
        reloadWarehouse();
    }
);

function reloadWarehouse() {
    const query = {};

    if (search.value) query.search = search.value;
    if (status.value) query.status = status.value;
    if (region.value) query.region = region.value;
    if (district.value) query.district = district.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("inventories.warehouses.index"), query, {
        preserveScroll: true,
        preserveState: true,
        only: ["warehouses"],
    });
}

const isDoing = ref([]);
const confirmToggleStatus = (warehouse, index) => {
    const action = warehouse.status === "active" ? "deactivate" : "activate";

    Swal.fire({
        title: "Are you sure?",
        html: `<p>Do you want to ${action} ${warehouse.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: warehouse.status === "active" ? "#d33" : "#3085d6",
        cancelButtonColor: "#6b7280",
        confirmButtonText:
            warehouse.status === "active"
                ? "Yes, deactivate!"
                : "Yes, activate!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            isDoing.value[index] = true;
            await axios.get(
                route("inventories.warehouses.toggle-status", warehouse.id)
            )
            .then((response) => {
                isDoing.value[index] = false;
                reloadWarehouse();
                Swal.fire(
                    action === "activate" ? "Activated!" : "Deactivated!",
                    `Warehouse has been ${action}d.`,
                    "success"
                );
            })
            .catch((error) => {
                isDoing.value[index] = false;
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.response.data,
                    confirmButtonText: "OK"
                })
            })
        }
    });
};
</script>
