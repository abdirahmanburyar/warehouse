<template>
    <AuthenticatedLayout :pageTitle="pageTitle" :pageDescription="pageDescription" img="/assets/images/assets.png">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Asset Details
        </h2>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/Authenticated.vue';


const props = defineProps({
    asset: Object,
    pageTitle: String,
    pageDescription: String,
});
</script>