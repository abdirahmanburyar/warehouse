<template>
    <UserAuthTab>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Create New User</h2>
            <Link
                :href="route('settings.users.index')"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Users
            </Link>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name, Title and Username -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>
                        <!-- Title -->
                        <div>
                            <InputLabel for="title" value="Title" />
                            <TextInput
                                id="title"
                                type="text"
                                v-model="form.title"
                                class="mt-1 block w-full"
                                placeholder="e.g., Manager, Supervisor, etc."
                            />
                        </div>
                        <div>
                            <InputLabel for="username" value="Username" />
                            <TextInput
                                id="username"
                                type="text"
                                v-model="form.username"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <!-- Password and Confirm Password in one row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>
                    </div>

                    <!-- Warehouse and Facility in one row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Warehouse -->
                        <div>
                            <InputLabel value="Warehouse" />
                            <Multiselect
                                v-model="form.warehouse"
                                :options="warehouses"
                                track-by="id"
                                label="name"
                                placeholder="Select warehouse"
                                :searchable="true"
                                :allow-empty="true"
                                @select="handleSelectWarehouse"
                                class="mt-1"
                            />
                        </div>
                        
                        <!-- Facility -->
                        <div>
                            <InputLabel value="Facility" />
                            <Multiselect
                                v-model="form.facility"
                                :options="facilities"
                                track-by="id"
                                label="name"
                                placeholder="Select facility"
                                :searchable="true"
                                :allow-empty="true"
                                @select="handleSelectFacility"
                                class="mt-1"
                            />
                        </div>
                    </div>
                </div>

                <!-- User Permissions -->
                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <InputLabel value="User Permissions" />
                        <button 
                            type="button" 
                            @click="toggleAllPermissions" 
                            class="text-sm text-blue-600 hover:text-blue-800"
                        >
                            {{ allPermissionsSelected ? 'Deselect All' : 'Select All' }}
                        </button>
                    </div>
                    
                    <div class="mt-2 border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                        <div v-if="!permissions.length" class="text-gray-500 text-center py-4">
                            No permissions available
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div v-for="permission in permissions" :key="permission.id" class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        :id="`permission-${permission.id}`"
                                        type="checkbox"
                                        :value="permission.id"
                                        v-model="form.permissions"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                </div>
                                <div class="ml-3 text-sm">
                                    <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                                        {{ permission.display_name || permission.name }}
                                    </label>
                                    <p v-if="permission.description" class="text-xs text-gray-500">
                                        {{ permission.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <InputLabel value="Status" />
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6 gap-4">
                    <Link :href="route('settings.users.index')" :disabled="processing" class="px-4 py-2 text-gray-700">
                        Cancel
                    </Link>
                    <PrimaryButton :disabled="processing">
                        {{ processing ? 'Creating...' : 'Create User' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';

import { useToast } from 'vue-toastification';
import axios from 'axios';
import Swal from 'sweetalert2';

const toast = useToast();
const processing = ref(false);

const props = defineProps({
    warehouses: Array,
    facilities: Array,
    permissions: {
        type: Array,
        default: () => []
    }
});

// Initialize form data
const form = ref({
    name: '',
    title: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    warehouse_id: null,
    warehouse: null,
    facility_id: null,
    facility: null,
    permissions: [],
    is_active: true
});

// Computed properties for permissions
const allPermissionsSelected = computed(() => {
    if (props.permissions.length === 0) return false;
    return form.value.permissions.length === props.permissions.length;
});

// Toggle all permissions
function toggleAllPermissions() {
    if (allPermissionsSelected.value) {
        form.value.permissions = [];
    } else {
        form.value.permissions = props.permissions.map(p => p.id);
    }
}

// Handle warehouse selection
function handleSelectWarehouse(selected) {
    if (selected) {
        form.value.warehouse_id = selected.id;
    } else {
        form.value.warehouse_id = null;
    }
}

// Handle facility selection
function handleSelectFacility(selected) {
    if (selected) {
        form.value.facility_id = selected.id;
    } else {
        form.value.facility_id = null;
    }
}

// Submit the form
const submit = async () => {
    processing.value = true;

    // Prepare form data for submission
    const formData = {
        name: form.value.name,
        title: form.value.title,
        username: form.value.username,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        warehouse_id: form.value.warehouse_id,
        facility_id: form.value.facility_id,
        permissions: form.value.permissions,
        is_active: form.value.is_active
    };

    await axios.post(route('settings.users.store'), formData)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data.message || 'User created successfully',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                toast.success('User created successfully');
                router.visit(route('settings.users.index'));
            });
        })
        .catch((error) => {
            processing.value = false;
            const errorMessage = error.response?.data?.message || error.response?.data || 'An error occurred';
            toast.error(errorMessage);
        });
};
</script>
