<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="min-h-screen flex flex-col items-center justify-center bg-cover bg-center bg-no-repeat p-4"
        style="background-image: url('/assets/images/bg.png');"
    >
        <div class="w-full max-w-2xl overflow-hidden">
            <slot />
        </div>
    </div>
</template>
