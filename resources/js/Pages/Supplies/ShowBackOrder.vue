<template>
    <AuthenticatedLayout>
        <h1>View Back order</h1>
        {{  props.history  }}
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    history: {
        required: true,
        type: Array
    }
})
</script>