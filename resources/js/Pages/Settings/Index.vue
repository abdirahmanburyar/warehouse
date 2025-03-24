<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Settings</h2>
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="currentTab = 'General'"
                            :class="[currentTab === 'General' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            General
                        </button>
                        <button @click="currentTab = 'users'"
                            :class="[currentTab === 'users' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Users
                        </button>
                        <button @click="currentTab = 'roles'"
                            :class="[currentTab === 'roles' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Roles
                        </button>
                    </nav>
                </div>

                <!-- Users Tab Content -->
                <div v-show="currentTab === 'General'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'General', 'opacity-0': currentTab !== 'General'}">
                    <!-- <UserIndex :users="users" :roles="roles" /> -->
                    <span>General</span>
                </div>

                <!-- Users Tab Content -->
                <div v-show="currentTab === 'users'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'users', 'opacity-0': currentTab !== 'users'}">
                    <!-- <UserIndex :users="users" :roles="roles" /> -->
                    <span>Users</span>
                </div>

                <!-- Roles Tab Content -->
                <div v-show="currentTab === 'roles'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'roles', 'opacity-0': currentTab !== 'roles'}">
                    <!-- <RolesComponent :roles="roles" :permissions="permissions" /> -->
                    <span>Roles</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    users: Object,
    roles: Array,
    permissions: Array
});

const currentTab = ref('users');
</script>