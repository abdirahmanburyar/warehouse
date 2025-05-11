<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <div class="px-5">
            <div class="flex items-center justify-between">
                <a href="#" @click.prevent="$inertia.get(route('reports.index'))" class="flex items-center text-gray-600 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Reports
                </a>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Issued Quantity Report</h1>

            {{props.quantiteis}}
           
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    quantiteis: Array
})
</script>