<template>
    <AuthenticatedLayout title="Locations Management" description="Manage Locations" img="/assets/images/location.png">
        <div class="flex justify-between items-center mb-4">
            <div>
                <Link
                    :href="route('inventories.index')"
                    class="text-indigo-600 hover:text-indigo-800 flex items-center"
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
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                    Back to Inventory
                </Link>
                <h3 class="text-xl font-bold text-gray-900 mt-1">
                    Locations Management
                </h3>
            </div>
            <Link
                :href="route('inventories.location.create')"
                class="px-4 py-2 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 transition duration-150 ease-in-out flex items-center"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Create New Location
            </Link>
        </div>

        <div class="">
            <div class="flex flex-wrap gap-6 items-start">
                <div class="w-[400px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Search</label
                    >
                    <input
                        type="text"
                        v-model="search"
                        class="w-full"
                        placeholder="Search by location name..."
                    />
                </div>
                <div class="w-[300px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Warehouse</label
                    >
                    <Multiselect
                        v-model="warehouse"
                        :options="warehouses"
                        placeholder="Select Warehouse"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        :allow-empty="true"
                    />
                </div>
            </div>
            <div class="flex justify-end mt-6 mb-3">
                <select
                    v-model="per_page"
                    @change="props.filters.page = 1"
                    class="rounded-full border-black shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-opacity-50 w-[200px] pl-3 pr-8 py-2 text-sm"
                >
                    <option :value="6">6 per page</option>
                    <option :value="25">25 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                </select>
            </div>
        </div>

        <div class="overflow-auto mb-[80px]">
            <div class="text-gray-900">
                <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Location
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Warehouse
                        </th>
                        <th
                            class="px-2 py-2 border border-black text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="props.locations.data.length === 0">
                        <td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500">
                            No locations found.
                        </td>
                    </tr>
                    <tr v-else v-for="(location, index) in props.locations.data" :key="location.id">
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ location.location }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            {{ location.warehouse }}
                        </td>
                        <td
                            class="px-2 py-2 border border-black text-sm font-medium text-gray-900"
                        >
                            <div class="flex gap-2">
                                <Link
                                    :href="
                                        route(
                                            'inventories.location.edit',
                                            location.id
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
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-end mt-3">
                <TailwindPagination
                    :data="props.locations"
                    @pagination-change-page="getResults"
                    :limit="2"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";

const props = defineProps({
    locations: {
        required: true,
        type: Object,
    },
    warehouses: Array,
    filters: Object,
});

// Create a computed reference to the locations data for easier access
const locations = computed(() => props.locations);

const search = ref(props.filters?.search || "");
const warehouse = ref(props.filters?.warehouse || []);
const per_page = ref(props.filters?.per_page || 25);

watch([() => search.value, () => warehouse.value, () => per_page.value, () => props.filters.page], () => {
    getLocations();
});

function getLocations() {
    const query = {};
    if (search.value) query.search = search.value;
    if (warehouse.value) query.warehouse = warehouse.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;

    router.get(route("inventories.location.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["locations"],
    });
}

function getResults(page = 1) {
    // Initialize query object safely
    const query = {};

    // Set the page parameter
    query.page = page;

    // Add search value if it exists
    if (search.value) query.search = search.value;

    // Add warehouse filter if it exists
    if (warehouse.value) query.warehouse = warehouse.value;

    // Add per_page parameter if it exists
    if (per_page.value) query.per_page = per_page.value;

    // Navigate to the new page with all filters preserved
    router.get(route("inventories.location.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["locations", "warehouses", "filters"],
    });
}

function editLocation(id) {
    router.get(route("inventories.location.edit", id));
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
