<template>
    <UserAuthTab>
        <Head title="User Management" />

        <!-- Header with search and actions -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search users..."
                        class="pl-10 pr-4 py-2 w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-4">
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
            </div>
        </div>

        <!-- No bulk actions needed -->

        <!-- Users Table -->
        <div class="overflow-auto">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black">
                            Role
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black">
                            Warehouse
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider border border-black">
                            Facility
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider border border-black">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <span v-for="role in user.roles" :key="role.id"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                            >
                                {{ role.name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-gray-500">{{ user.warehouse?.name }}</div>
                        </td>
                        <td class="px-6 py-4 border border-black">
                            <div class="text-sm text-gray-500">{{ user.facility?.name }}</div>
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
        <Pagination :links="users.links" />
    </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import Swal from 'sweetalert2';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import Pagination from '@/Components/Pagination.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Link, Head } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    filters: Object,
});

const toast = useToast();
const search = ref(props.filters?.search || '');
const processing = ref(false);

// Debounced search
let searchTimeout;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('settings.users.index'), { search: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

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
