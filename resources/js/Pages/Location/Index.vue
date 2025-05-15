<template>
    <AuthenticatedTabs>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Locations</h3>
        
        <div class="overflow-hidden mb-[80px]">
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
                                <Link :href="route('inventories.location.edit', l.id)" class="text-indigo-600 hover:text-indigo-900">
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedTabs>
</template>

<script setup>
import AuthenticatedTabs from '@/Layouts/AuthenticatedTabs.vue';
import { Link } from '@inertiajs/vue3';
import moment from "moment";

const props = defineProps({
    locations: {
        required: true,
        type: Array
    }
});
</script>