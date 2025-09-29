<!-- supplies.packing-list.location.store -->

<template>
    <AuthenticatedLayout
        title="Locations Management"
        description="Manage Locations"
        img="/assets/images/location.png"
    >
        <div class="flex justify-between items-center mb-4">
            <div>
                <Link
                    :href="route('inventories.location.index')"
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
                    Back to Locations
                </Link>
                <h3 class="text-xl font-bold text-gray-900 mt-1">
                    Edit Location
                </h3>
            </div>
        </div>
        <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label
                        for="location"
                        class="block text-sm font-medium text-gray-700"
                        >Location</label
                    >
                    <input
                        type="text"
                        id="location"
                        v-model="form.location"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        required
                    />
                </div>
                <div>
                    <label
                        for="warehouse"
                        class="block text-sm font-medium text-gray-700"
                        >Warehouse</label
                    >
                    <Multiselect
                        v-model="form.warehouse"
                        :options="props.warehouses"
                        placeholder="Select a warehouse"
                        required
                    />
                </div>
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        :href="route('inventories.location.index')"
                        :disabled="processing"
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        Exit
                    </Link>
                    <button
                        type="submit"
                        :disabled="processing || !$page.props.auth.can.warehouse_manage"
                        class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        {{ processing ? "Saving.." : "Save" }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

const props = defineProps({
    location: {
        required: true,
        type: Object,
    },
    warehouses: Array,
});

const form = ref({
    id: props.location.id,
    location: props.location.location,
    warehouse: props.location.warehouse,
});

const processing = ref(false);

const submit = async () => {
    processing.value = true;
    await axios
        .post(route("inventories.location.store"), form.value)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                icon: "success",
                title: "Location saved",
                text: "Your location has been saved",
            }).then(() => {
                router.get(route("inventories.location.index"));
            });
        })
        .catch((error) => {
            processing.value = false;
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response.data,
            });
            console.error(error);
        });
};
</script>
