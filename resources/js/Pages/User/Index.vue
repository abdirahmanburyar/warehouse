<template>
    <UserAuthTab>
        <Head title="User Management" />

        <!-- Header with create button -->
        <div class="mb-4 flex justify-end">
            <Link
                :href="route('settings.users.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add User
            </Link>
        </div>
            
        <!-- Filters row -->
        <div class="mb-6 grid grid-cols-6 gap-3">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search users..."
                    class="pl-10 pr-4 py-2 w-full text-sm text-black border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            
            <!-- Role Filter -->
            <div>
                <Multiselect
                    v-model="filters.role_id"
                    :options="roleOptions"
                    :searchable="true"
                    :allow-empty="true"
                    :multiple="false"
                    placeholder="Filter by Role"
                    label="label"
                    track-by="value"
                    class="text-sm text-black"
                />
            </div>
            
            <!-- Warehouse Filter -->
            <div>
                <Multiselect
                    v-model="filters.warehouse_id"
                    :options="warehouseOptions"
                    :searchable="true"
                    :allow-empty="true"
                    :multiple="false"
                    placeholder="Filter by Warehouse"
                    label="label"
                    track-by="value"
                    class="text-sm text-black"
                />
            </div>
            
            <!-- Facility Filter -->
            <div>
                <Multiselect
                    v-model="filters.facility_id"
                    :options="facilityOptions"
                    :searchable="true"
                    :allow-empty="true"
                    :multiple="false"
                    placeholder="Filter by Facility"
                    label="label"
                    track-by="value"
                    class="text-sm text-black"
                />
            </div>
            
            <!-- Per Page Filter -->
            <div class="flex items-center">
                <select 
                    v-model="perPage" 
                    @change="changePerPage"
                    class="w-full text-sm text-black border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
            
            <!-- Clear Filters -->
            <div class="flex items-center">
                <button 
                    @click="clearFilters" 
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-black rounded-lg transition"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- No bulk actions needed -->

        <!-- Users Table -->
        <div class="overflow-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Username
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Warehouse
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Facility
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider border border-black text-black">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm font-medium text-black">{{ user.name }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-black">{{ user.username }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-black">{{ user.email }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <span v-for="role in user.roles" :key="role.id"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-black"
                            >
                                {{ role.name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-black">{{ user.warehouse?.name }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-black">{{ user.facility?.name }}</div>
                        </td>
                        <td class="px-6 py-4 flex gap-2 border border-black">
                            <Link 
                                :href="route('settings.users.edit', user.id)"
                                class="text-blue-600 hover:text-blue-900 inline-flex items-center"
                                title="Edit User"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            
                            <button @click="confirmToggleStatus(user)" class="relative inline-flex items-center cursor-pointer" :title="user.is_active ? 'Deactivate User' : 'Activate User'">
                                <div class="w-10 h-5 rounded-full after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all" 
                                    :class="{ 
                                        'bg-green-500 after:translate-x-full after:border-white': user.is_active, 
                                        'bg-red-500': !user.is_active 
                                    }"></div>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4 flex justify-end">
            <TailwindPagination 
                :data="users" 
                @pagination-change-page="getUsers"
                class="mt-4"
            />
        </div>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Swal from 'sweetalert2';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Link, Head } from '@inertiajs/vue3';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import '@/Components/multiselect.css';
import { TailwindPagination } from 'laravel-vue-pagination';

const props = defineProps({
    users: Object,
    roles: Array,
    warehouses: Array,
    facilities: Array,
    filters: Object,
});

const toast = useToast();
const search = ref(props.filters?.search || '');
const processing = ref(false);
const perPage = ref(props.filters?.per_page || '10');

// Filter states
const filters = ref({
    role_id: null,
    warehouse_id: null,
    facility_id: null,
});

// Initialize filters from props
const initializeFilters = () => {
    if (props.filters?.role_id) {
        const roleOption = roleOptions.value.find(option => option.value == props.filters.role_id);
        if (roleOption) filters.value.role_id = roleOption;
    }
    
    if (props.filters?.warehouse_id) {
        const warehouseOption = warehouseOptions.value.find(option => option.value == props.filters.warehouse_id);
        if (warehouseOption) filters.value.warehouse_id = warehouseOption;
    }
    
    if (props.filters?.facility_id) {
        const facilityOption = facilityOptions.value.find(option => option.value == props.filters.facility_id);
        if (facilityOption) filters.value.facility_id = facilityOption;
    }
};

// Format options for multiselect
const roleOptions = computed(() => {
    const options = props.roles.map(role => ({
        value: role.id,
        label: role.name
    }));
    return [{ value: null, label: 'All Roles' }, ...options];
});

const warehouseOptions = computed(() => {
    const options = props.warehouses.map(warehouse => ({
        value: warehouse.id,
        label: warehouse.name
    }));
    return [{ value: null, label: 'All Warehouses' }, ...options];
});

const facilityOptions = computed(() => {
    const options = props.facilities.map(facility => ({
        value: facility.id,
        label: facility.name
    }));
    return [{ value: null, label: 'All Facilities' }, ...options];
});

// Initialize filters after computed properties are defined
onMounted(() => {
    initializeFilters();
});

// Clear all filters
const clearFilters = () => {
    // Reset all filter values
    filters.value.role_id = roleOptions.value[0]; // Select 'All Roles' option
    filters.value.warehouse_id = warehouseOptions.value[0]; // Select 'All Warehouses' option
    filters.value.facility_id = facilityOptions.value[0]; // Select 'All Facilities' option
    search.value = '';
    perPage.value = '10';
    
    // Use router.visit to completely reset URL parameters
    router.visit(route('settings.users.index'), {
        preserveState: false
    });
};

// Helper function to get filter values for API calls
const getFilterParams = () => {
    // Start with base parameters
    const params = {
        per_page: perPage.value
    };
    
    // Only add search if it has a value
    if (search.value) {
        params.search = search.value;
    }
    
    // Only add role_id if it has a value and is not the 'All' option
    if (filters.value.role_id && filters.value.role_id.value !== null) {
        params.role_id = filters.value.role_id.value;
    }
    
    // Only add warehouse_id if it has a value and is not the 'All' option
    if (filters.value.warehouse_id && filters.value.warehouse_id.value !== null) {
        params.warehouse_id = filters.value.warehouse_id.value;
    }
    
    // Only add facility_id if it has a value and is not the 'All' option
    if (filters.value.facility_id && filters.value.facility_id.value !== null) {
        params.facility_id = filters.value.facility_id.value;
    }
    
    return params;
};

// Watch for filter changes
watch(
    () => [filters.value.role_id, filters.value.warehouse_id, filters.value.facility_id],
    () => {
        console.log('Filter changed:', filters.value);
        applyFilters();
    },
    { deep: true }
);

// Debounced search
let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('settings.users.index'), {
            ...getFilterParams(),
            search: value
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

// Apply filters
const applyFilters = () => {
    console.log('Applying filters:', filters.value);
    router.get(route('settings.users.index'), getFilterParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Change per page
const changePerPage = () => {
    router.get(route('settings.users.index'), {
        ...getFilterParams(),
        page: 1 // Always reset to page 1 when changing per_page
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Get users for pagination
const getUsers = (page) => {
    router.get(route('settings.users.index'), {
        ...getFilterParams(),
        page: page
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// No bulk toggle functionality needed

// Confirm toggle user status with SweetAlert
const confirmToggleStatus = (user) => {
    const newStatus = !user.is_active;
    const action = newStatus ? 'activate' : 'deactivate';
    
    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
        text: `Are you sure you want to ${action} ${user.name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: newStatus ? '#10B981' : '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: `Yes, ${action} user!`
    }).then((result) => {
        if (result.isConfirmed) {
            toggleUserStatus(user, newStatus);
        }
    });
};

// Toggle individual user status
const toggleUserStatus = async (user, newStatus) => {
    try {
        processing.value = true;
        
        const response = await axios.post(route('users.toggle-status'), {
            user_id: user.id,
            is_active: newStatus
        });
        
        if (response.data.success) {
            // Update the user status locally to avoid a full page reload
            user.is_active = newStatus;
            
            Swal.fire({
                title: 'Success!',
                text: response.data.message,
                icon: 'success',
                confirmButtonColor: '#10B981'
            });
        } else {
            throw new Error(response.data.message || 'Failed to update user status');
        }
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to update user status',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
        console.error(error);
    } finally {
        processing.value = false;
    }
};
</script>
