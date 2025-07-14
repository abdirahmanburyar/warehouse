<template>
    <UserAuthTab>
        <Head title="Role Management" />

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
                            <h1 class="text-3xl font-bold text-white">Role Management</h1>
                            <p class="text-purple-100 text-sm mt-1">
                                Manage system roles and permissions
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-0">
                        <Link
                            :href="route('settings.roles.create')"
                            class="inline-flex items-center px-6 py-3 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all duration-200 font-medium shadow-lg hover:shadow-xl"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add New Role
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Roles</p>
                        <p class="text-2xl font-bold text-gray-900">{{ roles.total || roles.data?.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Permissions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ permissions.length || 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ roles.data?.reduce((total, role) => total + (role.users_count || 0), 0) || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white shadow-xl rounded-2xl mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Roles</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by role name..."
                                class="pl-10 pr-4 py-3 w-full text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            />
                        </div>
                    </div>
                    
                    <!-- Permission Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Permission</label>
                        <Multiselect
                            v-model="permission"
                            :options="permissionOptions"
                            :searchable="true"
                            :show-labels="false"
                            :allow-empty="true"
                            :multiple="false"
                            placeholder="All Permissions"
                            label="name"
                            track-by="id"
                            class="text-sm"
                        />
                    </div>
                </div>
                
                <div class="w-full flex justify-end">
                    <select 
                        v-model="per_page" 
                        @change="props.filters.page = 1"
                        class="w-[200px] mt-4 flex justify-end px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                    >
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Roles Table -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Permissions
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Users
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="role in roles.data" :key="role.id" class="hover:bg-gray-50 transition-colors">
                            <!-- Role Info -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">{{ role.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ role.name }}</div>
                                        <div class="text-sm text-gray-500">{{ role.permissions?.length || 0 }} permissions</div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Permissions -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="(permission, index) in getDisplayPermissions(role)" 
                                        :key="index"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                    >
                                        {{ formatPermissionName(permission) }}
                                    </span>
                                    <span v-if="role.permissions && role.permissions.length > 5" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        +{{ role.permissions.length - 5 }} more
                                    </span>
                                    <span v-if="!role.permissions || role.permissions.length === 0" class="text-sm text-gray-500 italic">
                                        No permissions
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Users Count -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ role.users_count || 0 }} users</div>
                                <div class="text-sm text-gray-500">assigned</div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <Link 
                                        :href="route('settings.roles.edit', role.id)"
                                        class="inline-flex items-center p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Edit Role"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    
                                    <button 
                                        @click="confirmDelete(role)"
                                        class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Delete Role"
                                        :disabled="role.name === 'admin'"
                                        :class="{'opacity-50 cursor-not-allowed': role.name === 'admin'}"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="!roles.data || roles.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No roles found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new role.</p>
                <div class="mt-6">
                    <Link
                        :href="route('settings.roles.create')"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Role
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="roles.data && roles.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-end">
                    <TailwindPagination 
                        :data="roles" 
                        @pagination-change-page="getRoles"
                        :limit="2"
                    />
                </div>
            </div>
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';

const props = defineProps({
    roles: Object,
    permissions: Array,
    filters: Object
});

const toast = useToast();
const search = ref(props.filters?.search || '');
const per_page = ref(props.filters?.per_page || '10');
const permission = ref(props.filters?.permission || null);

// Format permission options for multiselect
const permissionOptions = computed(() => {
    return props.permissions.map(permission => ({
        id: permission.id,
        name: formatPermissionName(permission.name)
    }));
});

// Watch for filter changes
watch(
    () => [permission.value, search.value, props.filters.page, per_page.value],
    () => {
        applyFilters();
    });

// Apply filters
const applyFilters = () => {
    const params = {};
    
    // Only add search if it has a value
    if (search.value) {
        params.search = search.value;
    }
    
    // Only add permission if it has a value
    if (permission.value) {
        params.permission = permission.value.id || permission.value;
    }

    if (per_page.value) {
        params.per_page = per_page.value;
    }

    if (props.filters.page) {
        params.page = props.filters.page;
    }

    router.get(route('settings.roles.index'), params, {
        preserveState: true,
        preserveScroll: true,
        only: ['roles', 'permissions'],
    });
};

// Get roles for pagination
const getRoles = (page) => {
    props.filters.page = page;
};

// Get display permissions (limited to 5 for UI)
function getDisplayPermissions(role) {
    if (!role.permissions || role.permissions.length === 0) return [];
    
    return role.permissions.slice(0, 5).map(permission => {
        const parts = permission.name.split('.');
        return parts.length > 1 ? `${parts[0]}.${parts[1]}` : permission.name;
    });
}

// Format permission name for display
function formatPermissionName(name) {
    const parts = name.split('.');
    if (parts.length > 1) {
        return parts[1].replace(/-/g, ' ');
    }
    return name;
}

// Confirm delete
function confirmDelete(role) {
    if (role.name === 'admin') {
        toast.error('Cannot delete the admin role');
        return;
    }
    
    Swal.fire({
        title: 'Are you sure?',
        text: `Delete role "${role.name}"? This may affect users assigned to this role.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('settings.roles.destroy', role.id), {
                onSuccess: () => {
                    toast.success('Role deleted successfully');
                },
                onError: () => {
                    toast.error('Failed to delete role');
                }
            });
        }
    });
}
</script>
