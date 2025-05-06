<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <div class="px-5">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Reports</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-1">
                <!-- Row 1: Inventory and Transfer Reports -->
                <div class="border border-black rounded-md self-start">
                    <button @click="toggleSection('inventory')" class="w-full px-4 py-3 flex items-center justify-between bg-white hover:bg-gray-50">
                        <span class="text-lg font-medium text-gray-900">Inventory Reports</span>
                        <svg :class="['w-5 h-5 transform transition-transform', openSection === 'inventory' ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-show="openSection === 'inventory'" class="px-4 py-3 bg-gray-50 border-t border-black">
                        <ul class="space-y-2">
                            <li>
                                <Link :href="route('reports.stockLevelReport')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Stock Level Report</Link>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Movement History</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Inventory Valuation</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border border-black rounded-md self-start">
                    <button @click="toggleSection('transfer')" class="w-full px-4 py-3 flex items-center justify-between bg-white hover:bg-gray-50">
                        <span class="text-lg font-medium text-gray-900">Transfer Reports</span>
                        <svg :class="['w-5 h-5 transform transition-transform', openSection === 'transfer' ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-show="openSection === 'transfer'" class="px-4 py-3 bg-gray-50 border-t border-black">
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Transfer Status</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Transfer History</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Facility Transfers</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Row 2: Orders and LMIS Reports -->
                <div class="border border-black rounded-md self-start">
                    <button @click="toggleSection('orders')" class="w-full px-4 py-3 flex items-center justify-between bg-white hover:bg-gray-50">
                        <span class="text-lg font-medium text-gray-900">Orders Reports</span>
                        <svg :class="['w-5 h-5 transform transition-transform', openSection === 'orders' ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-show="openSection === 'orders'" class="px-4 py-3 bg-gray-50 border-t border-black">
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Purchase Orders</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Order Status</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Order History</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border border-black rounded-md self-start">
                    <button @click="toggleSection('lmis')" class="w-full px-4 py-3 flex items-center justify-between bg-white hover:bg-gray-50">
                        <span class="text-lg font-medium text-gray-900">LMIS Reports</span>
                        <svg :class="['w-5 h-5 transform transition-transform', openSection === 'lmis' ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-show="openSection === 'lmis'" class="px-4 py-3 bg-gray-50 border-t border-black">
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Facility Reports</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">District Reports</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">System Status</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Row 3: Expiration Reports -->
                <div class="border border-black rounded-md self-start">
                    <button @click="toggleSection('expiration')" class="w-full px-4 py-3 flex items-center justify-between bg-white hover:bg-gray-50">
                        <span class="text-lg font-medium text-gray-900">Expiration Reports</span>
                        <svg :class="['w-5 h-5 transform transition-transform', openSection === 'expiration' ? 'rotate-90' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div v-show="openSection === 'expiration'" class="px-4 py-3 bg-gray-50 border-t border-black">
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Expiring Products</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Expired Products</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Disposal Reports</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const openSection = ref(null);

const toggleSection = (section) => {
    openSection.value = openSection.value === section ? null : section;
};
</script>