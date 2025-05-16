<template>
    <AuthenticatedLayout>
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Settings</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- System Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">System Configuration</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Inventory Settings</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Stock Thresholds</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Reorder Levels</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Expiration Limits</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">Data & Backup Settings</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Automatic Backup</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Data Import and Export Options</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">User & Access Management</h3>
                            <ul class="space-y-2">
                                <li><Link :href="route('settings.roles.index')" class="text-gray-600 hover:text-indigo-600">Role-Based Access</Link></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Permissions</a></li>
                                <li><Link :href="route('settings.users.index')" class="text-gray-600 hover:text-indigo-600">Manage Users</Link></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Audit Trials</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Preferences -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">User Preferences</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Account Information</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Profile Details</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Password Management</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">Regional Settings</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Time Zone</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Date & Time Format</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">Interface Customization</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Light/Dark Mode</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Default View</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications & Alerts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4 border-b pb-2">Notifications & Alerts</h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium mb-2">Inventory Alerts</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Low Stock Alerts</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Out of Stock Alerts</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Transfer Alerts</a></li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium mb-2">Order & Delivery Notifications</h3>
                            <ul class="space-y-2">
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Order Status Updates</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Pending Approvals</a></li>
                                <li><a href="#" class="text-gray-600 hover:text-indigo-600">Delivery Delays</a></li>
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
</script>