<template>
    <UserAuthTab>
        <Head title="Create Role" />

        <!-- Header Section -->
        <div class="bg-white shadow-xl rounded-2xl mb-6">
            <div class="bg-gradient-to-r from-purple-600 to-pink-700 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-white/20 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-10 h-10 text-white">
                                <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Create New Role</h1>
                            <p class="text-purple-100 text-sm mt-1">
                                Define a new role with specific permissions
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-0">
                        <Link
                            :href="route('settings.roles.index')"
                            class="inline-flex items-center px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all duration-200 font-medium shadow-lg hover:shadow-xl"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Roles
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form @submit.prevent="submitForm" class="p-6">
                <!-- Role Name -->
                <div class="mb-6">
                    <InputLabel for="name" value="Role Name" class="text-sm font-semibold text-gray-700 mb-2" />
                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="Enter role name (e.g., Manager, Editor, Viewer)"
                        required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <!-- Permissions Section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <InputLabel value="Permissions" class="text-sm font-semibold text-gray-700" />
                        <div class="flex items-center space-x-4">
                            <button 
                                type="button" 
                                @click="selectAllPermissions"
                                class="text-sm text-purple-600 hover:text-purple-800 font-medium transition-colors"
                                :disabled="processing"
                            >
                                Select All
                            </button>
                            <span class="text-gray-400">|</span>
                            <button 
                                type="button" 
                                @click="deselectAllPermissions"
                                class="text-sm text-purple-600 hover:text-purple-800 font-medium transition-colors"
                                :disabled="processing"
                            >
                                Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Permission Groups -->
                    <div class="border border-gray-200 rounded-lg p-6 max-h-96 overflow-y-auto bg-gray-50">
                        <div v-for="(permissions, module) in groupedPermissions" :key="module" class="mb-6 last:mb-0">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-semibold text-gray-800 capitalize text-lg">{{ formatModuleName(module) }}</h4>
                                <div class="flex items-center space-x-2">
                                    <button 
                                        type="button" 
                                        @click="selectModulePermissions(module)"
                                        class="text-xs text-purple-600 hover:text-purple-800 font-medium transition-colors"
                                        :disabled="processing"
                                    >
                                        Select All
                                    </button>
                                    <span class="text-gray-400">|</span>
                                    <button 
                                        type="button" 
                                        @click="deselectModulePermissions(module)"
                                        class="text-xs text-purple-600 hover:text-purple-800 font-medium transition-colors"
                                        :disabled="processing"
                                    >
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="permission in permissions" :key="permission.id" class="flex items-start bg-white p-3 rounded-lg border border-gray-200 hover:border-purple-300 transition-colors">
                                    <div class="flex items-center h-5 mt-0.5">
                                        <input
                                            :id="`permission-${permission.id}`"
                                            type="checkbox"
                                            :value="permission.id"
                                            v-model="form.permissions"
                                            class="h-4 w-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                        />
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <label :for="`permission-${permission.id}`" class="text-sm font-medium text-gray-700 cursor-pointer">
                                            {{ formatPermissionName(permission.name) }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">{{ permission.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <Link
                        :href="route('settings.roles.index')"
                        class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                        :disabled="processing"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton 
                        type="submit"
                        :disabled="processing"
                        class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors"
                    >
                        <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ processing ? 'Creating...' : 'Create Role' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    permissions: Array
});

const toast = useToast();
const processing = ref(false);

const form = useForm({
    name: '',
    permissions: [],
});

// Group permissions by module
const groupedPermissions = computed(() => {
    const groups = {};
    
    props.permissions.forEach(permission => {
        const parts = permission.name.split('.');
        const module = parts[0];
        
        if (!groups[module]) {
            groups[module] = [];
        }
        
        groups[module].push(permission);
    });
    
    // Sort modules alphabetically
    return Object.keys(groups).sort().reduce((sorted, key) => {
        sorted[key] = groups[key];
        return sorted;
    }, {});
});

// Format module name for display
function formatModuleName(module) {
    return module.replace(/-/g, ' ');
}

// Format permission name for display
function formatPermissionName(name) {
    const parts = name.split('.');
    if (parts.length > 1) {
        return parts[1].replace(/-/g, ' ');
    }
    return name;
}

// Select all permissions
function selectAllPermissions() {
    form.permissions = props.permissions.map(permission => permission.id);
}

// Deselect all permissions
function deselectAllPermissions() {
    form.permissions = [];
}

// Select all permissions for a module
function selectModulePermissions(module) {
    const modulePermissions = groupedPermissions.value[module].map(permission => permission.id);
    form.permissions = [...new Set([...form.permissions, ...modulePermissions])];
}

// Deselect all permissions for a module
function deselectModulePermissions(module) {
    const modulePermissionIds = groupedPermissions.value[module].map(permission => permission.id);
    form.permissions = form.permissions.filter(id => !modulePermissionIds.includes(id));
}

// Submit form
function submitForm() {
    processing.value = true;
    
    form.post(route('settings.roles.store'), {
        onSuccess: () => {
            toast.success('Role created successfully');
            processing.value = false;
        },
        onError: () => {
            toast.error('Failed to create role');
            processing.value = false;
        }
    });
}
</script> 