<template>
  <AuthenticatedLayout :title="`Transfer - ${props.transfer.transferID}`">
    <!-- Transfer Header -->
    <div v-if="props.error">
      {{ props.error }}
    </div>
    <div v-else>
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Transfer ID: {{ props.transfer.transferID }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ formatDate(props.transfer.transfer_date) }}</p>
          </div>
          <div class="flex items-center space-x-4">
            <span :class="[statusClasses[props.transfer.status]]" class="flex items-center text-lg font-bold px-4 py-2">
              <!-- Status Icon -->
              <span class="mr-3">
                <!-- Pending Icon -->
                <img v-if="props.transfer.status === 'pending'" src="/assets/images/pending.svg" class="w-6 h-6"
                  alt="Pending" />

                <!-- Approved Icon -->
                <img v-else-if="props.transfer.status === 'approved'" src="/assets/images/approved.png" class="w-6 h-6"
                  alt="Approved" />

                <!-- In Process Icon -->
                <img v-else-if="props.transfer.status === 'in_process'" src="/assets/images/inprocess.png"
                  class="w-6 h-6" alt="In Process" />

                <!-- Dispatched Icon -->
                <img v-else-if="props.transfer.status === 'dispatched'" src="/assets/images/dispatch.png"
                  class="w-6 h-6" alt="Dispatched" />

                <!-- Delivered Icon -->
                <img v-else-if="props.transfer.status === 'delivered'" src="/assets/images/delivery.png" class="w-6 h-6"
                  alt="Delivered" />

                <!-- Received Icon -->
                <img v-else-if="props.transfer.status === 'received'" src="/assets/images/received.png" class="w-6 h-6"
                  alt="Received" />

                <!-- Rejected Icon -->
                <svg v-else-if="props.transfer.status === 'rejected'" class="w-6 h-6 text-red-700" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
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
          <div v-if="!props.transfer.from_warehouse && !props.transfer.from_facility"
            class="text-sm text-gray-500 italic">
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
      </div>

      <!-- Status Stage Timeline -->
      <div class="px-6 py-4 mb-6">
        <div class="relative">
          <!-- Timeline Track Background -->
          <div class="absolute top-8 left-0 right-0 h-2 bg-gray-200 z-0"></div>

          <!-- Timeline Progress - green progress line -->
          <div class="absolute top-8 left-0 h-2 bg-green-500 z-0 transition-all duration-500 ease-in-out" :style="{
            width: `${(statusOrder.indexOf(props.transfer.status) / (statusOrder.length - 1)) * 100}%`
          }"></div>

          <!-- Timeline Steps -->
          <div class="relative flex justify-between">
            <!-- Pending -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('pending') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/pending.svg" class="w-9 h-9 z-10" alt="Pending" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('pending'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('pending')
              }">Pending</span>
            </div>

            <!-- Approved -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('approved') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/approved.png" class="w-9 h-9 z-10" alt="Approved"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('approved') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('approved'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('approved'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('approved')
              }">Approved</span>
            </div>

            <!-- In Process -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/inprocess.png" class="w-9 h-9 z-10" alt="Processed"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('in_process'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('in_process')
              }">Processed</span>
            </div>

            <!-- Dispatched -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('transferred') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/dispatch.png" class="w-9 h-9 z-10" alt="Dispatched"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('transferred') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('transferred'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('transferred'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('transferred')
              }">Dispatched</span>
            </div>

            <!-- Delivered -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/received.png" class="w-9 h-9 z-10" alt="Delivered"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('received'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('received'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('received')
              }">Delivered</span>
            </div>

            <!-- Received -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
                :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('fully_received') ? 'border-orange-500' : 'border-gray-300'">
                <img src="/assets/images/received.png" class="w-9 h-9 z-10"
                  :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('fully_received') ? '' : 'opacity-40'"
                  alt="Received" />
              </div>
              <span class="mt-2 text-xs font-bold" :class="{
                'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('fully_received'),
                'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('fully_received'),
                'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('fully_received')
              }">Received</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transfer Items Table -->
      <h2 class="text-lg font-medium text-gray-900">Transfer Items</h2>
      <table class="min-w-full border border-gray-200 divide-y divide-gray-200">
        <thead>
          <tr class="bg-gray-50">
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[300px] min-w-[300px] max-w-[300px]">
              Items</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[100px]">
              UOM</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[150px]">
              Item Information</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[100px]">
              Available quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[100px]">
              Quantity to release</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  border border-black w-[100px]">
              Action</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in props.transfer.items" :key="item.id"
            class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-6 py-4 text-sm border border-black">
              <div class="flex flex-col">
                <span class="font-medium">{{ item.product?.name || 'Unknown Product' }}</span>
                <span class="text-xs text-gray-500">ID: {{ item.product_id }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-sm border border-black">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                {{ item.uom || 'N/A' }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm border border-black">
              <div class="text-sm bg-gray-50">
                Batch Numner: {{ item.barcode }}
              </div>
              <div class="text-sm bg-gray-50">
                Barcode: {{ item.barcode }}
              </div>
              <div class="flex items-center text-sm">
                Expiry Date: {{ formatDate(item.expire_date) }}
              </div>
            </td>
            <td class="px-6 py-4 text-sm border border-black">
              {{ item.available_quantity }}
            </td>
            <td class="px-6 py-4 text-sm border border-black">
              {{ item.quantity }}
            </td>
            <td class="px-6 py-4 text-sm border border-black">
              <Link :href="route('transfers.destroy', props.transfer.id)" method="delete" class="text-red-500 hover:text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 10-2 0v6a1 1 0 10-2 0V4h-3.382a1 1 0 00-.894 1.553 1 1 0 00.447 1.894l1.742 1.742 1.742-1.742a1 1 0 00.894-1.553zM5.556 5.556A.75.75 0 016 6v6a.75.75 0 01-1.5 0V6a.75.75 0 01.556-.556zm7.442 0a.75.75 0 01-.556.556v6a.75.75 0 01-1.5 0V6a.75.75 0 01.556-.556zm0 8a.75.75 0 01-1.5 0V6a.75.75 0 011.5 0z" clip-rule="evenodd" />
                </svg>
              </Link>
            </td>
          </tr>
          <tr v-if="!props.transfer.items || props.transfer.items.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
              No items found in this transfer.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Approval Actions Section -->
    <div class="px-6 py-4 mb-6 mt-8 border-t border-gray-200 flex flex-col justify-center items-center">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Transfer Status Actions</h2>
      <div class="flex flex-wrap gap-4">
        <!-- Pending status indicator -->
        <div class="relative">
          <button 
            :class="[
              props.transfer.status === 'pending' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
              statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
            ]"  class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
            <img src="/assets/images/pending.svg" class="w-5 h-5 mr-2" alt="Pending" />
            <span class="text-sm font-bold text-white">Pending</span>
          </button>
          <div v-if="props.transfer.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <!-- Approve button -->
        <div class="relative">
          <a :href="route('transfers.approve', props.transfer.id)" v-if="props.transfer.status === 'pending'" 
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
            <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
            <span class="text-sm font-bold text-white">Approve</span>
          </a>
          <button v-else
            :class="[
              statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
            ]"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
            <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
            <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending') ? 'Approved' : 'Approve' }}</span>
          </button>
          <div v-if="props.transfer.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <!-- Process button -->
        <div class="relative">
          <a :href="route('transfers.in-process', props.transfer.id)" v-if="props.transfer.status === 'approved'" 
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
            <img src="/assets/images/inprocess.png" class="w-5 h-5 mr-2" alt="Process" />
            <span class="text-sm font-bold text-white">Process</span>
          </a>
          <button v-else
            :class="[
              statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('approved') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
            ]"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
            <img src="/assets/images/inprocess.png" class="w-5 h-5 mr-2" alt="Process" />
            <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('approved') ? 'Processed' : 'Process' }}</span>
          </button>
          <div v-if="props.transfer.status === 'approved'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <!-- Dispatch button -->
        <div class="relative">
          <a :href="route('transfers.dispatch', props.transfer.id)" v-if="props.transfer.status === 'in_process'" 
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
            <img src="/assets/images/dispatch.png" class="w-5 h-5 mr-2" alt="Dispatch" />
            <span class="text-sm font-bold text-white">Dispatch</span>
          </a>
          <button v-else
            :class="[
              statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
            ]"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
            <img src="/assets/images/dispatch.png" class="w-5 h-5 mr-2" alt="Dispatch" />
            <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process') ? 'Dispatched' : 'Dispatch' }}</span>
          </button>
          <div v-if="props.transfer.status === 'in_process'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <!-- Complete Transfer button -->
        <div class="relative">
          <a :href="route('transfers.complete', props.transfer.id)" v-if="props.transfer.status === 'dispatched'" 
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
            <img src="/assets/images/received.png" class="w-5 h-5 mr-2" alt="Complete Transfer" />
            <span class="text-sm font-bold text-white">Complete Transfer</span>
          </a>
          <button v-else
            :class="[
              statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('dispatched') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
            ]"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
            <img src="/assets/images/received.png" class="w-5 h-5 mr-2" alt="Complete Transfer" />
            <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('dispatched') ? 'Completed' : 'Complete Transfer' }}</span>
          </button>
          <div v-if="props.transfer.status === 'dispatched'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
        </div>

        <!-- Reject button (only available for pending status) -->
        <div class="relative" v-if="props.transfer.status === 'pending'">
          <a :href="route('transfers.reject', props.transfer.id)" 
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-red-600 hover:bg-red-700 min-w-[160px]">
            <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="text-sm font-bold text-white">Reject</span>
          </a>
        </div>

        <!-- Status indicator for rejected status -->
        <div v-if="props.transfer.status === 'rejected'" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-100 text-red-800 min-w-[160px]">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <span class="text-sm font-bold">Rejected</span>
        </div>

        <!-- Status indicator for received status -->
        <div v-if="props.transfer.status === 'received'" 
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-green-100 text-green-800 min-w-[160px]">
          <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9,16.17 L4.83,12 L3.41,13.41 L9,19 L21,7 L19.59,5.59 L9,16.17 Z" fill="currentColor" />
          </svg>
          <span class="text-sm font-bold">Completed</span>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';

const props = defineProps({
  transfer: {
    type: Object,
    required: true
  },
  error: String
});

// Define the formatDate function and make it available in the template
function formatDate(date) {
  if (!date) return 'N/A';
  return moment(date).format('DD/MM/YYYY');
}

// Define the status order for the timeline
const statusOrder = ['pending', 'approved', 'in_process', 'transferred', 'received', 'fully_received'];

const getStatusProgress = (currentStatus) => {
  const currentIndex = statusOrder.indexOf(currentStatus);
  return statusOrder.map((status, index) => ({
    status,
    isActive: index <= currentIndex,
    isPast: index < currentIndex
  }));
};

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800 rounded-full font-bold',
  approved: 'bg-green-100 text-green-800 rounded-full font-bold',
  'in process': 'bg-blue-100 text-blue-800 rounded-full font-bold',
  dispatched: 'bg-purple-100 text-purple-800 rounded-full font-bold',
  delivered: 'bg-gray-100 text-gray-800 rounded-full font-bold',
  received: 'bg-green-100 text-green-800 rounded-full font-bold flex items-center',
  'partially_received': 'bg-orange-100 text-orange-800 rounded-full font-bold',
  default: 'bg-gray-100 text-gray-800 rounded-full font-bold'
};

const statusProgress = computed(() => getStatusProgress(props.transfer.status));
</script>