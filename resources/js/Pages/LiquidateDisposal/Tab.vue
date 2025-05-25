<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  title: string;
  activeTab: 'liquidate' | 'disposal';
}>();
</script>

<template>
  <Head :title="title" />
  <AuthenticatedLayout :title="title">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Liquidate & Disposal</h2>
      </div>
    </template>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <!-- Tabs Navigation -->
      <div class="flex border-b border-gray-200 bg-white sticky top-0 z-10">
        <Link 
          href="/liquidates" 
          :class="[
            'px-6 py-3 text-lg font-bold',
            activeTab === 'liquidate' 
              ? 'border-b-4 border-blue-500 text-blue-600' 
              : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Liquidate
        </Link>
        <Link 
          href="/disposals" 
          :class="[
            'px-6 py-3 text-lg font-bold',
            activeTab === 'disposal' 
              ? 'border-b-4 border-blue-500 text-blue-600' 
              : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Disposal
        </Link>
      </div>

      <!-- Content Section -->
      <div class="p-6">
        <slot></slot>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
