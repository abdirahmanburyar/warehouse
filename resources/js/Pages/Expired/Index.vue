<template>
    <Head title="Expired" />
    <AuthenticatedLayout>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Expiration</h2>
        <div class="bg-white overflow-hidden sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="currentTab = 'all'"
                            :class="[currentTab === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            All Expiration
                        </button>
                        <button @click="currentTab = 'near'"
                            :class="[currentTab === 'near' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Near to Expiration
                        </button>
                        <button @click="currentTab = 'expired'"
                            :class="[currentTab === 'expired' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Expired
                        </button>
                        <button @click="currentTab = 'disposed'"
                            :class="[currentTab === 'disposed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Disposed
                        </button>
                    </nav>
                </div>

                <!-- Users Tab Content -->
                <div v-show="currentTab === 'all'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'all', 'opacity-0': currentTab !== 'all'}">
                    <!-- <UserIndex :users="users" :roles="roles" /> -->
                    <span>All Expired</span>
                </div>

                <!-- Users Tab Content -->
                <div v-show="currentTab === 'near'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'near', 'opacity-0': currentTab !== 'near'}">
                    <!-- <UserIndex :users="users" :roles="roles" /> -->
                    <span>Near to Expire</span>
                </div>

                <!-- Roles Tab Content -->
                <div v-show="currentTab === 'expired'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'expired', 'opacity-0': currentTab !== 'expired'}">
                    <!-- <RolesComponent :roles="roles" :permissions="permissions" /> -->
                    <span>Expired</span>
                </div>

                <!-- Roles Tab Content -->
                <div v-show="currentTab === 'disposed'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'disposed', 'opacity-0': currentTab !== 'disposed'}">
                    <!-- <RolesComponent :roles="roles" :permissions="permissions" /> -->
                    <span>Disposed</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserIndex from '@/Pages/User/Index.vue';
import RolesComponent from '@/Pages/User/Roles.vue';

defineProps({
    users: Object,
    roles: Array,
    permissions: Array
});

const currentTab = ref('all');
</script>