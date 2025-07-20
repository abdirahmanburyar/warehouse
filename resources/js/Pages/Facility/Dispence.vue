<template>
    <AuthenticatedLayout title="Despence Details" description="View Despence details" img="/assets/images/despence.jpg">
      <div class="py-6">
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

      <Tab current="dispence" :facility="facility">
        <div class="mt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Dispenses</h3>
          <div class="space-y-4">
          </div>
        </div>
      </Tab>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Tab from './Tab.vue';

const props = defineProps({
  facility: {
    type: Object,
    required: true
  }
});
</script>
