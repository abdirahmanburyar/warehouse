<template>
  <div class="mt-4">
    <div class="">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <Link
          v-for="tab in tabs"
          :key="tab.name"
          :href="tab.href"
          :class="[
            tab.current
              ? 'border-indigo-500 text-indigo-600'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
          ]"
        >
          {{ tab.name }}
        </Link>
      </nav>
    </div>
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  current: {
    type: String,
    required: true
  },
  facility: {
    type: Object,
    required: true
  }
});

const facilityId = computed(() => props.facility?.id);

const tabs = computed(() => [
  { 
    name: 'Inventory', 
    href: facilityId.value ? route('facilities.inventory', { facility: facilityId.value }) : '#', 
    current: props.current === 'inventory' 
  },
  { 
    name: 'Dispenses', 
    href: facilityId.value ? route('facilities.dispence', { facility: facilityId.value }) : '#', 
    current: props.current === 'dispence' 
  },
  { 
    name: 'Expiry', 
    href: facilityId.value ? route('facilities.expiry', { facility: facilityId.value }) : '#', 
    current: props.current === 'expiry' 
  }
]);
</script>
