<template>
    <AuthenticatedLayout description="Expired" title="Expired" img="/assets/images/expires.png">
        <div class="p-6 relative">
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-green-500 text-green-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 pr-72">
                <div class="bg-white rounded-lg shadow overflow-hidden">

            <!-- Statistics Panel -->
            <div class="fixed right-6 top-32 w-64 space-y-3">
                    <div v-if="activeTab === 'all' || activeTab === 'year'" class="bg-orange-400 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 1 Year</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.year }} Item</div>
                                <div class="text-sm mt-1">{{ formatDate(oneYearFromNow) }}</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'six_months'" class="bg-pink-500 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expiring within next 6 months</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.six_months }} Items</div>
                                <div class="text-sm mt-1">{{ formatDate(sixMonthsFromNow) }}</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>

                    <div v-if="activeTab === 'all' || activeTab === 'expired'" class="bg-gray-600 rounded-lg p-4 text-white shadow-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium">Expired Items</h3>
                                <div class="mt-1 text-lg font-bold">{{ filteredStats.expired }} Items</div>
                            </div>
                            <img src="/assets/images/box.png" class="w-12 h-12" alt="box">
                        </div>
                    </div>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Until Expiry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Transfer</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in filteredInventories" :key="item.id" :class="{ 'bg-yellow-50': item.expiring_soon }">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ item.product_name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(item.expiry_date) }}</td>
                            <td class="px-6 py-4">
                                <div :class="{
                                    'text-sm font-medium': true,
                                    'text-red-600': item.days_until_expiry <= 30,
                                    'text-yellow-600': item.days_until_expiry <= 180 && item.days_until_expiry > 30,
                                    'text-gray-900': item.days_until_expiry > 180
                                }">
                                    {{ item.days_until_expiry }} days
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="item.expired" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Expired
                                </span>
                                <span v-else-if="item.expiring_soon" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Expiring Very Soon
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Expiring Soon
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    v-if="item.expired && !item.disposed" 
                                    @click="disposeItem(item.id)"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Dispose
                                </button>
                                <span 
                                    v-else-if="item.disposed"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                >
                                    Disposed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <template v-if="item.expired">
                                    <button
                                        class="text-red-600 hover:text-red-900"
                                        @click="disposeItem(item.id)"
                                    >
                                        Dispose
                                    </button>
                                </template>
                                <template v-else>
                                    <Link
                                        class="text-blue-600 hover:text-blue-900"
                                        :href="route('expired.transfer', item.id)"
                                    >
                                        Transfer
                                </Link>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { format, addMonths, addYears } from 'date-fns'
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import Transfer from './Transfer.vue'

const props = defineProps({
    inventories: Array
})

const activeTab = ref('all')

const tabs = [
    { id: 'all', name: 'All Items' },
    { id: 'six_months', name: 'Expiring within next 6 months' },
    { id: 'year', name: 'Expiring within next 1 Year' },
    { id: 'expired', name: 'Expired' },
]

const filteredInventories = computed(() => {
    if (activeTab.value === 'all') return props.inventories
    if (activeTab.value === 'year') return props.inventories.filter(item => !item.expired && item.days_until_expiry <= 365)
    if (activeTab.value === 'six_months') return props.inventories.filter(item => !item.expired && item.days_until_expiry <= 180)
    if (activeTab.value === 'expired') return props.inventories.filter(item => item.expired)
    return props.inventories
})

const now = new Date()
const sixMonthsFromNow = addMonths(now, 6)
const oneYearFromNow = addYears(now, 1)

const formatDate = (date) => {
    return format(new Date(date), 'MMM d, yyyy')
}

const showTransferModal = ref(false)
const selectedInventory = ref(null)

const disposeItem = async (id) => {
    const result = await Swal.fire({
        title: 'Dispose Item?',
        text: 'Are you sure you want to dispose of this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, dispose it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.post(route('expired.dispose', id));
            await Swal.fire({
                title: 'Success!',
                text: response.data.message,
                icon: 'success'
            });
            window.location.reload();
        } catch (error) {
            await Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Failed to dispose item',
                icon: 'error'
            });
        }
    }
}

const filteredStats = computed(() => {
    const yearItems = props.inventories.filter(item => !item.expired && item.days_until_expiry <= 365).length
    const sixMonthItems = props.inventories.filter(item => !item.expired && item.days_until_expiry <= 180).length
    const expiredItems = props.inventories.filter(item => item.expired).length
    const disposedItems = props.inventories.filter(item => item.disposed).length

    if (activeTab.value === 'all') {
        return {
            year: yearItems,
            six_months: sixMonthItems,
            expired: expiredItems,
            disposed: disposedItems
        }
    } else if (activeTab.value === 'year') {
        return {
            year: yearItems,
            six_months: sixMonthItems,
            expired: 0,
            disposed: 0
        }
    } else if (activeTab.value === 'six_months') {
        return {
            year: 0,
            six_months: sixMonthItems,
            expired: 0,
            disposed: 0
        }
    } else if (activeTab.value === 'expired') {
        return {
            year: 0,
            six_months: 0,
            expired: expiredItems,
            disposed: disposedItems
        }
    }
    return { year: 0, six_months: 0, expired: 0, disposed: 0 }
})
</script>