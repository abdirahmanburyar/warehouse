<template>
    <AuthenticatedLayout>
        {{ props.transfer }}
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transfer: Object
});

</script>