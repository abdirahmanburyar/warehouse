<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  title: string;
  activeTab: 'liquidate' | 'disposal' | 'transferBackOrders' | 'transferLiquidates' | 'transferDisposals';
}>();
</script>

<template>
  <Head :title="title" />
  <AuthenticatedLayout :title="title" description="Liquidate & Disposal" img="/assets/images/liquidate-disposal-w.png">

    <div class="flex justify-between items-center text-xs">
      <div>
        <h2 class="font-bold text-2xl text-gray-900 leading-tight">Liquidate & Disposal Management</h2>
        <p class="text-gray-600 mt-1">Manage liquidations and disposals with comprehensive tracking</p>
      </div>
    </div>

    <div class="bg-white overflow-hidden">
      <!-- Tabs Navigation -->
      <div class="flex border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white mb-4 sticky top-0 z-10">
        <Link 
          :href="route('liquidate-disposal.liquidates')" 
          :class="[
            'px-8 py-4 text-lg font-semibold transition-all duration-200 relative',
            activeTab === 'liquidate' 
              ? 'text-blue-600 bg-white border-b-3 border-blue-500 shadow-sm' 
              : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50'
          ]"
        >
          <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Liquidations</span>
          </div>
          <div v-if="activeTab === 'liquidate'" class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-blue-500 to-blue-600 rounded-t-full"></div>
        </Link>
        <Link 
          :href="route('liquidate-disposal.disposed-items')" 
          :class="[
            'px-8 py-4 text-lg font-semibold transition-all duration-200 relative',
            activeTab === 'disposal' 
              ? 'text-red-600 bg-white border-b-3 border-red-500 shadow-sm' 
              : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50'
          ]"
        >
          <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            <span>Disposals</span>
          </div>
          <div v-if="activeTab === 'disposal'" class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-red-500 to-red-600 rounded-t-full"></div>
        </Link>
      </div>

      <!-- Content Section -->
      <div class="bg-gray-50 min-h-screen mb-[50px]">
        <slot></slot>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.border-b-3 {
  border-bottom-width: 3px;
}
</style>
