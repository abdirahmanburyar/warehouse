<template>
    <AuthenticatedTabs>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Warehouse</h2>

        <div class=" overflow-hidden sm:rounded-lg p-6 mb-[60px]">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="name" v-model="form.name" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" id="code" v-model="form.code"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea id="address" v-model="form.address" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" id="city" v-model="form.city"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input type="text" id="postal_code" v-model="form.postal_code"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Manager Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="manager_name" class="block text-sm font-medium text-gray-700">Manager Name</label>
                        <input type="text" id="manager_name" v-model="form.manager_name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="manager_email" class="block text-sm font-medium text-gray-700">Manager Email</label>
                        <input type="email" id="manager_email" v-model="form.manager_email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="manager_phone" class="block text-sm font-medium text-gray-700">Manager Phone</label>
                        <input type="tel" id="manager_phone" v-model="form.manager_phone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <!-- Warehouse Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity (m³)</label>
                        <input type="number" id="capacity" v-model="form.capacity"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" v-model="form.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="special_handling_capabilities" class="block text-sm font-medium text-gray-700">
                            Special Handling Capabilities
                        </label>
                        <textarea id="special_handling_capabilities" v-model="form.special_handling_capabilities" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes" v-model="form.notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <Link :href="route('inventories.warehouses.index')" 
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Exit
                    </Link>
                    <button type="submit"
                        :disabled="loading"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ loading ? 'Creating...' : 'Create Warehouse' }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedTabs>
</template>

<script setup>
import AuthenticatedTabs from '@/Layouts/AuthenticatedTabs.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const loading = ref(false);
const form = ref({
    name: '',
    code: '',
    address: '',
    city: '',
    postal_code: '',
    manager_name: '',
    manager_email: '',
    manager_phone: '',
    capacity: '',
    status: 'active',
    special_handling_capabilities: '',
    notes: '',
});

const submit = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route('inventories.warehouses.store'), form.value);
        Swal.fire({
            title: 'Success!',
            text: 'Warehouse created successfully',
            icon: 'success',
            confirmButtonColor: '#4F46E5',
        }).then(() => {
            window.location = route('inventories.warehouses.index');
        });
    } catch (error) {
        loading.value = false;
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'An error occurred',
            icon: 'error',
            confirmButtonColor: '#4F46E5',
        });
    }
};
</script>
