<template>
    <AuthenticatedLayout :title="'Settings'" description="Customize it as You Like" img="/assets/images/settings.png">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Settings</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- System Configuration -->
            <div v-if="hasPermissionTo('permission-manage') || hasPermissionTo('system-settings')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">System Configuration</h2>
                    
                    <div class="space-y-6">
                        
                        <div v-if="hasPermissionTo('permission-manage')">
                            <h3 class="text-lg font-medium mb-2">User & Access Management</h3>
                            <ul class="space-y-2">
                                <li><Link :href="route('settings.users.index')" class="text-gray-600 hover:text-indigo-600">Manage Users</Link></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Permissions</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Audit Trials</a></li>
                            </ul>
                        </div>
                        
                        <div v-if="hasPermissionTo('system-settings')">
                            <h3 class="text-lg font-medium mb-2">Inventory Management</h3>
                            <ul class="space-y-2">
                                <li><Link :href="route('settings.reorder-levels.index')" class="text-gray-600 hover:text-indigo-600">Reorder Levels</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logistics Management -->
            <div v-if="hasPermissionTo('system-settings')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Logistics Management</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Transportation</h3>
                            <ul class="space-y-2">
                                <li><Link :href="route('settings.logistics.companies.index')" class="text-gray-600 hover:text-indigo-600">Logistic Companies</Link></li>
                                <li><Link :href="route('settings.drivers.index')" class="text-gray-600 hover:text-indigo-600">Manage Drivers</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Management -->
            <div v-if="hasPermissionTo('permission-manage')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Permission Management</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Access Control</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">User Permissions</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Role Management</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Permission Groups</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from "@inertiajs/vue3";
import { ref, onMounted } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';

// Use permissions composable
const { hasPermissionTo } = usePermissions();
</script>