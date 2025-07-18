<template>
    <AuthenticatedLayout :title="'Settings'" description="Customize it as You Like" img="/assets/images/settings.png">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Settings</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- System Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">System Configuration</h2>
                    
                    <div class="space-y-6">
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">User & Access Management</h3>
                            <ul class="space-y-2">
                                <!-- <li><Link :href="route('settings.roles.index')" class="text-gray-600 hover:text-indigo-600">Role-Based Access</Link></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Permissions</a></li> -->
                                <li><Link :href="route('settings.users.index')" class="text-gray-600 hover:text-indigo-600">Manage Users</Link></li>
                                <!-- <li><a href="#" class="text-gray-600 hover:text-indigo-600">Audit Trials</a></li> -->
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">Inventory Management</h3>
                            <ul class="space-y-2">
                                <li><Link :href="route('reorder-levels.index')" class="text-gray-600 hover:text-indigo-600">Reorder Levels</Link></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logistics Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from "@inertiajs/vue3";
import { ref, onMounted } from 'vue';

</script>