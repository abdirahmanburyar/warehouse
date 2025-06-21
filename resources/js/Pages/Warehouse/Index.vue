<template>
    <Head title="Warehouses" />

    <AuthenticatedLayout title="Warehouse Management" description="Warehouse" img="/assets/images/facility.png">
        <Head title="Warehouses" />        <!-- Page Header -->
        <div class="flex justify-between items-center mb-6">
            <Link
                :href="route('inventories.index')"
                class="text-gray-500 hover:text-gray-700"
            >
                <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
            </Link>
            <Link
                :href="route('inventories.warehouses.create')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                <i class="fas fa-plus mr-2"></i> Add Warehouse
            </Link>
        </div>

        <div class="grid grid-cols-4 gap-2">
            <div>
                <label for="search">Search</label>
                <input
                    type="text"
                    v-model="search"
                    placeholder="Search by name, address"
                    class="mt-1 w-full"
                />
            </div>
            <div>
                <label for="region">Region</label>
                <Multiselect
                    v-model="region"
                    :options="props.regions"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    :allow-empty="true"
                    placeholder="Select Region"
                />
            </div>
            <div>
                <label for="region">District</label>
                <Multiselect
                    v-model="district"
                    :options="props.districts"
                    :searchable="true"
                    :close-on-select="true"
                    :show-labels="false"
                    :allow-empty="true"
                    placeholder="Select District"
                />
            </div>
            <div>
                <label for="status">Status</label>
                <select v-model="status" class="mt-1 block w-full">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end mt-2">
            <select
                class="w-[200px] rounded-3xl"
                name="per_page"
                id="per_page"
                @change="props.filters.page = 1"
                v-model="per_page"
            >
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <div class="mt-3">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Name
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Address
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Region
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            District
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Manager
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(warehouse, index) in props.warehouses.data"
                        :key="warehouse.id"
                    >
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ warehouse.name }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ warehouse.address }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ warehouse.region }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ warehouse.district }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            <div class="flex flex-col">
                                <span>Name: {{ warehouse.manager_name }}</span>
                                <span
                                    >Email: {{ warehouse.manager_email }}</span
                                >
                                <span
                                    >Phone: {{ warehouse.manager_phone }}</span
                                >
                            </div>
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            <span class="capitalize" :class="warehouse.status == 'active' ? 'text-green-500' : 'text-red-200'">{{ warehouse.status }}</span>
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            <div class="flex gap-2">
                                <Link
                                    :href="
                                        route(
                                            'inventories.warehouses.edit',
                                            warehouse.id
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
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
                                    @click="confirmToggleStatus(warehouse, index)"
                                    :class="{
                                        'bg-gray-200':
                                            warehouse.status == 'inactive',
                                        'bg-green-500':
                                            warehouse.status == 'active',
                                    }"
                                    :disabled="isDoing[index]"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span
                                        :class="{
                                            'translate-x-5':
                                                warehouse.status == 'active',
                                            'translate-x-0':
                                                warehouse.status == 'inactive',
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

        <div class="mt-3 flex justify-end items-center">
            <TailwindPagination
                :data="props.warehouses"
                :limit="2"
                @pagination-change-page="getResults"
            />
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
import { ref, watch, onMounted } from "vue";
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
    const action = warehouse.status == "active" ? "deactivate" : "activate";

    Swal.fire({
        title: "Are you sure?",
        html: `<p>Do you want to ${action} ${warehouse.name}?</p>`,
        showConfirmButton: true,
        icon: undefined,
        showCancelButton: true,
        confirmButtonColor: warehouse.status == "active" ? "#d33" : "#3085d6",
        cancelButtonColor: "#6b7280",
        confirmButtonText:
            warehouse.status == "active"
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
