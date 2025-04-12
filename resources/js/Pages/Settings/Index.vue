<template>
    <Head title="Settings" />
    <AuthenticatedLayout img="/assets/images/Setting.png" title="Manager Your Settings" description="Customize as it you like">
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
                        <button @click="currentTab = 'approval'"
                            :class="[currentTab === 'approval' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm']">
                            Approval Steps
                        </button>
                    </nav>
                </div>

                <!-- General Tab Content -->
                <div v-show="currentTab === 'General'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'General', 'opacity-0': currentTab !== 'General'}">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-medium mb-4">General Settings</h3>
                        <p class="text-gray-600">Configure your general application settings here.</p>
                    </div>
                </div>

                <!-- Users Tab Content -->
                <div v-show="currentTab === 'users'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'users', 'opacity-0': currentTab !== 'users'}">
                    <UserIndex :users="props.users" :roles="props.roles" :warehouses="props.warehouses" :filters="props.filters" />
                </div>

                <!-- Roles Tab Content -->
                <div v-show="currentTab === 'roles'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'roles', 'opacity-0': currentTab !== 'roles'}">
                    <RoleIndex :roles="props.roles" :permissions="props.permissions" :filters="props.filters" />
                </div>

                <!-- Approval Tab Content -->
                <div v-show="currentTab === 'approval'" class="transition-opacity duration-150" :class="{'opacity-100': currentTab === 'approval', 'opacity-0': currentTab !== 'approval'}">
                    <div class="p-4 bg-gray-50 rounded-lg mb-4">
                        <h3 class="text-lg font-medium mb-2">Approval Steps Configuration</h3>
                        <p class="text-gray-600">Configure approval workflows for different processes in the system. Each process can have multiple approval steps with specific roles and actions.</p>
                        <div class="mt-4 flex gap-4">
                            <button 
                                v-for="type in approvalTypes" 
                                :key="type.id"
                                @click="selectApprovalType(type)"
                                :class="[
                                    selectedApprovalType?.id === type.id 
                                        ? 'bg-indigo-100 text-indigo-700 border-indigo-300' 
                                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                    'px-4 py-2 rounded-md border text-sm font-medium'
                                ]"
                            >
                                {{ type.name }}
                            </button>
                        </div>
                    </div>
                    <ApprovalIndex 
                        :filters="props.filters" 
                        :approvals="props.approvals" 
                        :roles="props.roles"
                        :approvable-type="selectedApprovalType?.model"
                        :approvable-id="null"
                    />
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
import RoleIndex from '@/Pages/Role/Index.vue';
import ApprovalIndex from '@/Pages/Approval/Index.vue';

const props = defineProps({
    approvals: Object,
    users: Object,
    roles: Array,
    permissions: Array,
    warehouses: Array,
    filters: Object,
    activeTab: {
        type: String,
        default: 'General'
    }
});

const currentTab = ref(props.activeTab);

const approvalTypes = [
    { id: 'purchase_order_item', name: 'Purchase Order Items', model: 'App\\Models\\PurchaseOrderItem' },
    { id: 'order', name: 'Orders', model: 'App\\Models\\Order' },
    { id: 'transfer', name: 'Transfers', model: 'App\\Models\\Transfer' }
];

const selectedApprovalType = ref(approvalTypes[0]);

const selectApprovalType = (type) => {
    selectedApprovalType.value = type;
};
</script>