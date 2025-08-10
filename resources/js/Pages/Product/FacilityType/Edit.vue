<template>
    <Head title="Edit Facility Type" />

    <AuthenticatedLayout
        title="Edit Facility Type"
        description="Update facility type information"
        img="/assets/images/products.png"
    >

        <!-- Breadcrumb -->
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <Link :href="route('products.index')" class="hover:text-gray-900 transition-colors duration-200">
                Products
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <Link :href="route('products.eligible.index')" class="hover:text-gray-900 transition-colors duration-200">
                Facility Types
            </Link>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-900">Edit</span>
        </div>

        <div class="bg-white">
            <div class="px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Edit Facility Type</h2>
            </div>

            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <InputLabel for="name" value="Facility Type Name" class="text-sm font-medium text-gray-700 mb-2" />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 text-sm"
                            placeholder="Enter facility type name"
                            required
                            :disabled="form.processing"
                        />
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end mt-6 space-x-3">
                    <Link
                        :href="route('products.eligible.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        :class="{ 'opacity-50 pointer-events-none': form.processing }"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="isUpdating"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="isUpdating" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isUpdating ? 'Updating...' : 'Update Facility Type' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Swal from "sweetalert2";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    facilityType: {
        type: Object,
        required: true,
    },
});

const form = ref({
    id: props.facilityType.id,
    name: props.facilityType.name,
});

const isUpdating = ref(false);

async function submit() {
    isUpdating.value = true;
    await axios.post(route('products.facility-types.store'), form.value)
        .then(response => {
            isUpdating.value = false;
            Swal.fire({
                title: 'Success!',
                text: 'Facility Type updated successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                router.visit(route('products.eligible.index'));
            });
        })
        .catch(error => {
            isUpdating.value = false;
            console.log(error.response.data);
            toast.error(error.response.data);
        });
}
</script>
