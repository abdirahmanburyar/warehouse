<template>
<AuthenticatedLayout title="Physical Count Report" description="Track all physical counts" img="/assets/images/report.png">

  <div>
    {{props.products}}

    <h1>Physical Count Report</h1>
    {{props.inventories}}
  </div>

</AuthenticatedLayout>
  </template>

  <script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    inventories: Array,
    products: Array,
    categories: Array,
    filters: Object,
});

</script>