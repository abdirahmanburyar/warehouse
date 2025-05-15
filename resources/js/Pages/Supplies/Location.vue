<template>
    <AuthenticatedLayout>
        <Link :href="route('supplies.index')" class="flex items-center text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Supplies
        </Link>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Locations</h3>
        
        <div class="overflow-hidden">
            <div class="text-gray-900">
                <table class="min-w-full border border-black">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-full">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border border-black w-[100px]">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white border border-black">
                        <tr v-for="(l, i) in props.locations" :key="l.id">
                            <td class="px-6 py-4  border border-black">{{ i + 1 }}</td>
                            <td class="px-6 py-4  border border-black">{{ l.location }}</td>
                            <td class="px-6 py-4  border border-black text-right text-sm font-medium">
                                <Link :href="route('supplies.location.edit', l.id)" class="text-indigo-600 hover:text-indigo-900">
                                    Edit
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
    locations: {
        required: true,
        type: Array
    }
});
</script>