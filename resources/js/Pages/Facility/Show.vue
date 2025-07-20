<template>
    <AuthenticatedLayout title="Despence Details" description="View Despence details" img="/assets/images/despence.jpg">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">{{ facility.name }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="mt-1">{{ facility.email }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Phone</p>
                    <p class="mt-1">{{ facility.phone }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">District</p>
                    <p class="mt-1">{{ facility.district }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Address</p>
                    <p class="mt-1">{{ facility.address }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Facility Type</p>
                    <p class="mt-1">{{ facility.facility_type }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Cold Storage</p>
                    <p class="mt-1">
                        <span :class="facility.has_cold_storage ? 'text-green-600' : 'text-red-600'">
                            {{ facility.has_cold_storage ? 'Available' : 'Not Available' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    facility: Object
});
</script>
