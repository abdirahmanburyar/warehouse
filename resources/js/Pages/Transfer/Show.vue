<template>
  <AuthenticatedLayout>
    <!-- Transfer Header -->
    <div v-if="props.error">
      {{ props.error }}
    </div>
    <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Transfer #{{ props.transfer.transferID }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(props.transfer.transfer_date) }}</p>
          </div>
          <div class="flex items-center space-x-4">
            <span :class="[statusClasses[props.transfer.status] || statusClasses.default]">
              {{ props.transfer.status.toUpperCase() }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <!-- From Location Information -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <h2 class="text-lg font-medium text-gray-900">From Location Details</h2>
          <div v-if="props.transfer.from_warehouse" class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">Warehouse: {{ props.transfer.from_warehouse.name }}</span>
          </div>
          <div v-if="props.transfer.from_facility" class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">Facility: {{ props.transfer.from_facility.name }}</span>
          </div>
          <div v-if="!props.transfer.from_warehouse && !props.transfer.from_facility" class="text-sm text-gray-500 italic">
            No source location information available
          </div>
        </div>
        
        <!-- To Location Information -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <h2 class="text-lg font-medium text-gray-900">To Location Details</h2>
          <div v-if="props.transfer.to_warehouse" class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">Warehouse: {{ props.transfer.to_warehouse.name }}</span>
          </div>
          <div v-if="props.transfer.to_facility" class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">Facility: {{ props.transfer.to_facility.name }}</span>
          </div>
          <div v-if="!props.transfer.to_warehouse && !props.transfer.to_facility" class="text-sm text-gray-500 italic">
            No destination location information available
          </div>
        </div>

        <!-- Transfer Details -->
       
      </div>

      <!-- Status Stage Timeline -->
      <div class="px-6 py-4 mb-6">
        <div class="relative">
          <!-- Timeline Track Background -->
          <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 z-0"></div>

          <!-- Timeline Progress -->
          <div class="absolute top-5 left-0 h-1 bg-orange-500 z-0 transition-all duration-500 ease-in-out" :style="{
            width: `${(statusOrder.indexOf(props.transfer.status) / (statusOrder.length - 1)) * 100}%`
          }"></div>

          <!-- Timeline Steps -->
          <div class="relative flex justify-between">
            <!-- Pending -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('pending') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/pending.svg" class="w-6 h-6" alt="Pending"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('pending') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('pending') ? 'text-orange-600' : 'text-gray-500'">Pending</span>
            </div>

            <!-- Approved -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('approved') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/approved.png" class="w-6 h-6" alt="Approved"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('approved') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('approved') ? 'text-orange-600' : 'text-gray-500'">Approved</span>
            </div>

            <!-- In Process -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/inprocess.png" class="w-6 h-6" alt="In Process"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? 'text-orange-600' : 'text-gray-500'">In Process</span>
            </div>

            <!-- Transferred -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('transferred') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/dispatch.png" class="w-6 h-6" alt="Transferred"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('transferred') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('transferred') ? 'text-orange-600' : 'text-gray-500'">Transferred</span>
            </div>

            <!-- Received -->
            <div class="flex flex-col items-center">
              <div class="w-10 h-10 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? 'bg-orange-500 border-orange-200' : 'bg-white border-gray-200']">
                <img src="/assets/images/received.png" class="w-6 h-6" alt="Received"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-sm font-medium"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? 'text-orange-600' : 'text-gray-500'">Received</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transfer Items Table -->
      <div class="px-6 py-4 border border-gray-200 mb-[90px]">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-medium text-gray-900">Transfer Items</h2>
          <div class="text-sm text-gray-500">
            Total Items: <span class="font-medium">{{ props.transfer.items.length }}</span>
          </div>
        </div>
        
        <div class="overflow-x-auto shadow-sm rounded-lg">
          <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 w-[250px]">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 w-[100px]">UOM</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 w-[100px]">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 w-[150px]">Barcode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200 w-[150px]">Batch Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">Expiry Date</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in props.transfer.items" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                  <div class="flex flex-col">
                    <span class="font-medium">{{ item.product.name }}</span>
                    <span class="text-xs text-gray-500">ID: {{ item.product_id }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ item.uom || 'N/A' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                  <span v-if="item.quantity" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ item.quantity }}
                  </span>
                  <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                    Not specified
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                  <div class="font-mono text-xs bg-gray-50 px-2 py-1 rounded">
                    {{ item.barcode }}
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ item.batch_number }}
                  </span>
                </td>              
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="flex items-center">
                    <CalendarIcon class="h-4 w-4 text-gray-400 mr-1" />
                    <span>{{ formatDate(item.expire_date) }}</span>
                  </div>
                </td>
              </tr>
              <tr v-if="!props.transfer.items || props.transfer.items.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                  No items found in this transfer.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  BuildingOfficeIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  CalendarIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  transfer: {
    type: Object,
    required: true
  },
  error: String
});

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium',
  approved: 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium',
  in_process: 'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium',
  transferred: 'bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-medium',
  received: 'bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium',
  default: 'bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium'
};

// Define the formatDate function and make it available in the template
function formatDate(date) {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Define the status order for the timeline
const statusOrder = ['pending', 'approved', 'in_process', 'transferred', 'received'];

const getStatusProgress = (currentStatus) => {
  const currentIndex = statusOrder.indexOf(currentStatus);
  return statusOrder.map((status, index) => ({
    status,
    isActive: index <= currentIndex,
    isPast: index < currentIndex
  }));
};

const statusProgress = computed(() => getStatusProgress(props.transfer.status));
</script>