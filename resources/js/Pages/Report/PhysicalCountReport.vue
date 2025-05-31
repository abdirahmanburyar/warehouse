<template>
    <AuthenticatedLayout title="Physical Count Report" description="Inventory Verification Tool" img="/assets/images/report.png">
        {{ props.physicalCountReport }}
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';
import Swal from "sweetalert2";
import axios from "axios";

const props = defineProps({
    physicalCountReport: Array
});

const isSubmitting = ref(false);


</script>
