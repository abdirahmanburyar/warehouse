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

        <!-- Bulk Actions -->
        <div v-show="selectedItems.length > 0" 
            class="mb-4 p-4 bg-white rounded-lg shadow-sm border border-gray-200 flex items-center justify-between">
            <span class="text-sm text-gray-700">
                {{ selectedItems.length }} user(s) selected
            </span>
            <div class="flex items-center gap-3">
                <button 
                    @click="toggleSelectedStatus(true)"
                    class="px-3 py-1 text-sm text-green-700 hover:text-green-800 hover:bg-green-50 rounded"
                >
                    Activate
                </button>
                <button 
                    @click="toggleSelectedStatus(false)"
                    class="px-3 py-1 text-sm text-red-700 hover:text-red-800 hover:bg-red-50 rounded"
                >
                    Deactivate
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="checkbox" 
                                        :checked="isAllSelected"
                                        @change="toggleAll"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </label>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Warehouse
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id" :class="{ 'bg-blue-50': selectedItems.includes(user.id) }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="inline-flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="selectedItems"
                                        :value="user.id"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-for="role in user.roles" :key="role.id"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ role.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ user.warehouse?.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="{
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                                    'bg-green-100 text-green-800': user.is_active,
                                    'bg-red-100 text-red-800': !user.is_active
                                }">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link 
                                    :href="route('settings.users.edit', user.id)"
                                    class="text-blue-600 hover:text-blue-900 mr-4"
                                >
                                    Edit
                                </Link>
                                <button 
                                    @click="confirmDelete(user)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <Pagination :links="users.links" />
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete User
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeDeleteModal" class="mr-3">
                        Cancel
                    </SecondaryButton>
                    <DangerButton 
                        @click="deleteUser"
                        :disabled="processing"
                    >
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';
import Modal from '@/Components/Modal.vue';
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
const selectedItems = ref([]);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const processing = ref(false);

// Computed property to check if all items are selected
const isAllSelected = computed(() => {
    return selectedItems.value.length === props.users.data.length;
});

// Toggle all selections
const toggleAll = () => {
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.users.data.map(user => user.id);
    }
};

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

// Bulk status toggle
const toggleSelectedStatus = async (status) => {
    if (!selectedItems.value.length) return;

    try {
        await axios.post(route('users.bulk-status'), {
            user_ids: selectedItems.value,
            is_active: status
        });

        router.reload({ only: ['users'] });
        selectedItems.value = [];
        toast.success(`Successfully ${status ? 'activated' : 'deactivated'} selected users`);
    } catch (error) {
        toast.error('Failed to update user status');
    }
};

// Delete user functions
const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    userToDelete.value = null;
};

const deleteUser = async () => {
    if (!userToDelete.value) return;

    processing.value = true;
    try {
        await router.delete(route('users.destroy', userToDelete.value.id));
        closeDeleteModal();
        toast.success('User deleted successfully');
    } catch (error) {
        toast.error('Failed to delete user');
    } finally {
        processing.value = false;
    }
};
</script>
