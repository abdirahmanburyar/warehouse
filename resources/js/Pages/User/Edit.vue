<template>
    <UserAuthTab>
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Edit User</h2>
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
                    <!-- Name and Username -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <!-- Password (Optional for edit) -->
                        <div>
                            <InputLabel for="password" value="New Password (leave blank to keep current)" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirm New Password" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>

                    <!-- Roles -->
                    <div>
                        <InputLabel value="Roles" />
                        <Multiselect
                            v-model="form.roles"
                            :options="roles"
                            track-by="id"                            
                            label="name"
                            placeholder="Select roles"
                            :searchable="true"
                            :multiple="true"
                            :allow-empty="true"
                            @select="handleRoleSelect"
                            @remove="handleRoleRemove"
                            class="mt-1"
                        />
                        <p v-if="form.roles && form.roles.length > 0" class="mt-2 text-sm text-gray-600">
                            Role permissions: {{ getRolePermissionNames() }}
                        </p>
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

                    <!-- Direct Permissions -->
                    <div>
                        <div class="flex items-center justify-between">
                            <InputLabel value="Additional Permissions" />
                            <button 
                                type="button" 
                                @click="toggleAllPermissions" 
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                {{ allPermissionsSelected ? 'Deselect All' : 'Select All' }}
                            </button>
                        </div>
                        
                        <div class="mt-2 border border-gray-200 rounded-lg p-4 max-h-60 overflow-y-auto">
                            <div v-if="!availablePermissions.length" class="text-gray-500 text-center py-4">
                                No additional permissions available
                            </div>
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="permission in availablePermissions" :key="permission.id" class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input
                                            :id="`permission-${permission.id}`"
                                            type="checkbox"
                                            :value="permission.id"
                                            v-model="form.direct_permissions"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                                            {{ permission.name }}
                                        </label>
                                    </div>
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
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-600">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6 gap-4">
                    <Link :href="route('settings.users.index')" :disabled="processing" class="px-4 py-2 text-gray-700">
                        Exit
                    </Link>
                    <PrimaryButton :disabled="processing">
                        {{ processing ? 'Updating...' : 'Update User' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed } from 'vue';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
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
    user: Object,
    roles: Array,
    warehouses: Array,
    facilities: Array,
    permissions: Array,
});

const form = ref({
    id: props.user?.id || null,
    name: props.user?.name || '',
    title: props.user?.title || '',
    email: props.user?.email || '',
    username: props.user?.username || '',
    password: '',
    password_confirmation: '',
    roles: props.user?.roles || [],
    role_ids: props.user?.roles?.map(role => role.id) || [],
    warehouse: props.user?.warehouse || null,
    warehouse_id: props.user?.warehouse_id || null,
    facility: props.user?.facility || null,
    facility_id: props.user?.facility_id || null,
    direct_permissions: props.user?.permissions?.map(p => p.id) || [],
    is_active: props.user?.is_active || false,
});

// Fetch all permissions if not provided
const allPermissions = ref(props.permissions || []);
const fetchingPermissions = ref(false);

if (!props.permissions || props.permissions.length === 0) {
    fetchingPermissions.value = true;
    axios.get('/api/permissions')
        .then(response => {
            allPermissions.value = response.data;
            fetchingPermissions.value = false;
        })
        .catch(error => {
            console.error('Failed to fetch permissions:', error);
            fetchingPermissions.value = false;
        });
}

// Computed properties for permissions
const rolePermissionIds = computed(() => {
    if (!form.value.roles || form.value.roles.length === 0) return [];
    
    // Collect all permission IDs from all selected roles
    const permissionIds = [];
    form.value.roles.forEach(role => {
        if (role.permissions) {
            role.permissions.forEach(permission => {
                if (!permissionIds.includes(permission.id)) {
                    permissionIds.push(permission.id);
                }
            });
        }
    });
    
    return permissionIds;
});

const availablePermissions = computed(() => {
    return allPermissions.value;
});

const allPermissionsSelected = computed(() => {
    if (availablePermissions.value.length === 0) return false;
    return form.value.direct_permissions.length === availablePermissions.value.length;
});

// Handle role selection
function handleRoleSelect(selectedRole) {
    if (selectedRole && selectedRole.permissions) {
        // Update the role_ids array
        updateRoleIds();
    }
}

// Handle role removal
function handleRoleRemove(removedRole) {
    // Update the role_ids array
    updateRoleIds();
}

// Update the role_ids array based on selected roles
function updateRoleIds() {
    form.value.role_ids = form.value.roles.map(role => role.id);
}

// Get readable list of role permissions
function getRolePermissionNames() {
    if (!form.value.roles || form.value.roles.length === 0) {
        return 'None';
    }
    
    // Collect all unique permission names from all selected roles
    const permissionNames = new Set();
    form.value.roles.forEach(role => {
        if (role.permissions) {
            role.permissions.forEach(permission => {
                permissionNames.add(permission.name);
            });
        }
    });
    
    return Array.from(permissionNames).join(', ');
}

// Toggle all permissions
function toggleAllPermissions() {
    if (allPermissionsSelected.value) {
        form.value.direct_permissions = [];
    } else {
        form.value.direct_permissions = availablePermissions.value.map(p => p.id);
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

const submit = async () => {
    processing.value = true;

    // Prepare form data for submission
    const formData = {
        id: form.value.id,
        name: form.value.name,
        title: form.value.title,
        username: form.value.username,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        role_ids: form.value.role_ids,
        warehouse_id: form.value.warehouse_id,
        facility_id: form.value.facility_id,
        direct_permissions: form.value.direct_permissions,
        is_active: form.value.is_active
    };

    await axios.post(route('settings.users.store'), formData)
        .then((response) => {
            processing.value = false;
            Swal.fire({
                title: 'Success!',
                text: response.data,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                toast.success('User updated successfully');
                router.visit(route('settings.users.index'));
            });
        })
        .catch((error) => {
            processing.value = false;
            toast.error(error.response.data);
        });
};
</script>
