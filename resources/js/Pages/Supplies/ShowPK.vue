<template>
    <AuthenticatedLayout>
        <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Supplies
        </Link>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Packing List</h3>
        <div class="overflow-hidden mb-[100px]">
            <div class="text-gray-900">
                <table class="min-w-full border border-black">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Packing List #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Confirmed At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Avg Lead Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-black">
                        <tr v-for="(group, packing_list_number) in props.packing_list" :key="packing_list_number">
                            <td class="px-6 py-4 border border-black">{{ packing_list_number }}</td>
                            <td class="px-6 py-4 border border-black">{{ moment(group.confirmed_at).format('LLL') }}</td>
                            <td class="px-6 py-4 border border-black">${{ group.total_cost_sum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</td>
                            <td class="px-6 py-4 border border-black">{{ group.avg_lead_time }}</td>
                            <td class="px-6 py-4 border border-black">
                                <Link :href="route('supplies.packing-list.edit', packing_list_number)" class="text-indigo-600 hover:text-indigo-900">
                                    View Details
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import moment from "moment";

const props = defineProps({
    packing_list: {
        required: true,
        type: Object
    }
});
</script>