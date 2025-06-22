<template>
  <AuthenticatedLayout>
    <div class="container mx-auto px-4 py-6">
      <!-- Transfer Header -->
      <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <h1 class="text-2xl font-bold text-gray-800">Transfer Details</h1>
          <div class="flex items-center space-x-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium"
                  :class="statusClasses[props.transfer.status]">
              {{ props.transfer.status.replace('_', ' ').toUpperCase() }}
            </span>
          </div>
        </div>
        
        <!-- Transfer ID and Date -->
        <div class="mb-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <span class="text-sm text-gray-500">Transfer ID:</span>
              <span class="ml-2 font-semibold">#{{ props.transfer.transferID }}</span>
            </div>
            <div>
              <span class="text-sm text-gray-500">Transfer Date:</span>
              <span class="ml-2 font-semibold">{{ moment(props.transfer.transfer_date).format('DD/MM/YYYY') }}</span>
            </div>
          </div>
        </div>

        <!-- From and To Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- From Section -->
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
              From
            </h3>
            <div v-if="props.transfer.from_warehouse">
              <p class="font-semibold text-gray-800">{{ props.transfer.from_warehouse.name }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.from_warehouse.address }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.from_warehouse.district }}, {{ props.transfer.from_warehouse.region }}</p>
              <div class="mt-2 text-sm">
                <p class="text-gray-600">Manager: <span class="font-medium">{{ props.transfer.from_warehouse.manager_name }}</span></p>
                <p class="text-gray-600">Phone: <span class="font-medium">{{ props.transfer.from_warehouse.manager_phone }}</span></p>
              </div>
              <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                Warehouse
              </span>
            </div>
            <div v-else-if="props.transfer.from_facility">
              <p class="font-semibold text-gray-800">{{ props.transfer.from_facility.name }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.from_facility.address }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.from_facility.district }}, {{ props.transfer.from_facility.region }}</p>
              <div class="mt-2 text-sm">
                <p class="text-gray-600">Type: <span class="font-medium">{{ props.transfer.from_facility.facility_type }}</span></p>
                <p class="text-gray-600">Phone: <span class="font-medium">{{ props.transfer.from_facility.phone }}</span></p>
              </div>
              <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                Facility
              </span>
            </div>
          </div>

          <!-- To Section -->
          <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              To
            </h3>
            <div v-if="props.transfer.to_warehouse">
              <p class="font-semibold text-gray-800">{{ props.transfer.to_warehouse.name }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.to_warehouse.address }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.to_warehouse.district }}, {{ props.transfer.to_warehouse.region }}</p>
              <div class="mt-2 text-sm">
                <p class="text-gray-600">Manager: <span class="font-medium">{{ props.transfer.to_warehouse.manager_name }}</span></p>
                <p class="text-gray-600">Phone: <span class="font-medium">{{ props.transfer.to_warehouse.manager_phone }}</span></p>
              </div>
              <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                Warehouse
              </span>
            </div>
            <div v-else-if="props.transfer.to_facility">
              <p class="font-semibold text-gray-800">{{ props.transfer.to_facility.name }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.to_facility.address }}</p>
              <p class="text-sm text-gray-600">{{ props.transfer.to_facility.district }}, {{ props.transfer.to_facility.region }}</p>
              <div class="mt-2 text-sm">
                <p class="text-gray-600">Type: <span class="font-medium">{{ props.transfer.to_facility.facility_type }}</span></p>
                <p class="text-gray-600">Phone: <span class="font-medium">{{ props.transfer.to_facility.phone }}</span></p>
              </div>
              <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                Facility
              </span>
            </div>
          </div>
        </div>

        <!-- Transfer Timeline -->
        <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-6 text-center">Transfer Timeline</h3>
          
          <div class="flex flex-wrap justify-center items-center gap-2 md:gap-4">
            <!-- Pending -->
            <div class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('pending')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 2a1 1 0 000 2h6a1 1 0 100-2H9zM6.447 4.106A1 1 0 105.553 5.894 9.953 9.953 0 004 12c0 5.514 4.486 10 10 10s10-4.486 10-10a9.953 9.953 0 00-1.553-6.106 1 1 0 10-1.788.894A7.95 7.95 0 0122 12c0 4.411-3.589 8-8 8s-8-3.589-8-8a7.95 7.95 0 011.341-4.447 1 1 0 00-.894-1.447z"/>
                  <path d="M14 12a1 1 0 11-2 0V8a1 1 0 112 0v4z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">Pending</span>
              <div v-if="props.transfer.created_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.created_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line -->
            <div class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- Reviewed -->
            <div class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('reviewed')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">Reviewed</span>
              <div v-if="props.transfer.reviewed_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.reviewed_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line -->
            <div class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- Approved/Rejected -->
            <div class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('approved')">
                <svg v-if="props.transfer.status === 'rejected'" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg v-else class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">
                {{ props.transfer.status === 'rejected' ? 'Rejected' : 'Approved' }}
              </span>
              <div v-if="props.transfer.approved_at || props.transfer.rejected_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.approved_at || props.transfer.rejected_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line (only show if not rejected) -->
            <div v-if="props.transfer.status !== 'rejected'" class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- In Process (only show if not rejected) -->
            <div v-if="props.transfer.status !== 'rejected'" class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('in_process')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">In Process</span>
              <div v-if="props.transfer.processed_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.processed_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line -->
            <div v-if="props.transfer.status !== 'rejected'" class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- Dispatched -->
            <div v-if="props.transfer.status !== 'rejected'" class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('dispatched')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M8 17a1 1 0 102 0 1 1 0 00-2 0zM15 17a1 1 0 102 0 1 1 0 00-2 0zM3 4a1 1 0 011-1h1a1 1 0 011 1v3h4l2 3h5a1 1 0 011 1v2a3 3 0 00-3 3H9a3 3 0 00-3-3v-2H4a1 1 0 01-1-1V4z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">Dispatched</span>
              <div v-if="props.transfer.dispatched_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.dispatched_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line -->
            <div v-if="props.transfer.status !== 'rejected'" class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- Delivered -->
            <div v-if="props.transfer.status !== 'rejected'" class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('delivered')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M5 8a1 1 0 011-1h1a1 1 0 011 1v3h4l2 3h5a1 1 0 011 1v2a3 3 0 00-3 3H9a3 3 0 00-3-3v-2H4a1 1 0 01-1-1V8z"/>
                  <path d="M19 7h3l-3-3v3z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">Delivered</span>
              <div v-if="props.transfer.delivered_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.delivered_at).format('DD/MM HH:mm') }}
              </div>
            </div>

            <!-- Connecting Line -->
            <div v-if="props.transfer.status !== 'rejected'" class="hidden sm:block w-8 h-1 bg-green-400"></div>

            <!-- Received -->
            <div v-if="props.transfer.status !== 'rejected'" class="flex flex-col items-center relative">
              <div class="w-16 h-16 rounded-full flex items-center justify-center border-4"
                   :class="getTimelineStatusClass('received')">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <span class="text-sm font-medium mt-2 text-center">Received</span>
              <div v-if="props.transfer.received_at" class="text-xs text-gray-500 mt-1">
                {{ moment(props.transfer.received_at).format('DD/MM HH:mm') }}
              </div>
            </div>
          </div>
        </div>

        <!-- Transfer Items Table -->
        <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Transfer Items</h3>
          
          <div class="overflow-x-auto">
            <table class="w-full table-auto">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Product</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Batch</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Expiry</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Ordered Qty</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700" v-if="showReceivedColumn">Received Qty</th>
                  <th class="px-4 py-3 text-left text-sm font-medium text-gray-700" v-if="showActionsColumn">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in props.transfer.items" :key="item.id" class="border-t hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div>
                      <p class="font-medium text-gray-900">{{ item.product?.name }}</p>
                      <p class="text-sm text-gray-500">Code: {{ item.product?.productID }}</p>
                      <p class="text-xs text-gray-400">{{ item.product?.movement }} Movement</p>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span v-if="item.inventory_allocations?.length > 0" class="text-sm text-gray-700">
                      {{ item.inventory_allocations[0].batch_number || 'N/A' }}
                    </span>
                    <span v-else class="text-sm text-gray-400">N/A</span>
                  </td>
                  <td class="px-4 py-3">
                    <span v-if="item.inventory_allocations?.length > 0 && item.inventory_allocations[0].expiry_date" 
                          class="text-sm"
                          :class="isExpiringItem(item.inventory_allocations[0].expiry_date) ? 'text-red-600 font-medium' : 'text-gray-700'">
                      {{ moment(item.inventory_allocations[0].expiry_date).format('DD/MM/YYYY') }}
                    </span>
                    <span v-else class="text-sm text-gray-400">N/A</span>
                  </td>
                  <td class="px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ item.quantity }}</span>
                  </td>
                  <td class="px-4 py-3" v-if="showReceivedColumn">
                    <div class="flex items-center space-x-2">
                      <input
                        v-if="canEditReceivedQuantity"
                        type="number"
                        :value="item.received_quantity || 0"
                        @input="updateReceivedQuantity(item.id, $event.target.value)"
                        class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :min="0"
                        :max="item.quantity"
                      />
                      <span v-else class="text-sm font-medium" 
                            :class="getReceivedQuantityClass(item)">
                        {{ item.received_quantity || 0 }}
                      </span>
                      
                      <!-- Status indicator -->
                      <div v-if="item.received_quantity !== undefined && item.received_quantity < item.quantity" 
                           class="flex items-center">
                        <span class="w-2 h-2 bg-orange-400 rounded-full mr-1"></span>
                        <span class="text-xs text-orange-600">Partial</span>
                      </div>
                      <div v-else-if="item.received_quantity === item.quantity" 
                           class="flex items-center">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                        <span class="text-xs text-green-600">Complete</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3" v-if="showActionsColumn">
                    <div class="flex space-x-2">
                      <button
                        v-if="canCreateBackorder(item)"
                        @click="openBackOrderModal(item)"
                        class="px-3 py-1 bg-orange-500 text-white text-sm rounded hover:bg-orange-600 transition-colors"
                      >
                        Backorder
                      </button>
                      <button
                        v-if="item.backorders?.length > 0"
                        @click="viewBackorders(item)"
                        class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors"
                      >
                        View ({{ item.backorders.length }})
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary Row -->
          <div class="mt-4 pt-4 border-t bg-gray-50 rounded p-4">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-600">
                Total Items: <span class="font-medium">{{ props.transfer.items?.length || 0 }}</span>
              </div>
              <div v-if="showReceivedColumn" class="text-sm text-gray-600">
                Total Ordered: <span class="font-medium">{{ totalOrderedQuantity }}</span> | 
                Total Received: <span class="font-medium">{{ totalReceivedQuantity }}</span> |
                Completion: <span class="font-medium" :class="completionPercentage === 100 ? 'text-green-600' : 'text-orange-600'">
                  {{ completionPercentage }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import moment from 'moment';

const props = defineProps({
  transfer: {
    type: Object,
    required: true
  }
});

// Status styling
const statusClasses = computed(() => ({
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-blue-100 text-blue-800', 
  rejected: 'bg-red-100 text-red-800',
  in_process: 'bg-purple-100 text-purple-800',
  dispatched: 'bg-orange-100 text-orange-800',
  delivered: 'bg-indigo-100 text-indigo-800',
  received: 'bg-green-100 text-green-800'
}));

// Timeline status order
const statusOrder = ['pending', 'reviewed', 'approved', 'in_process', 'dispatched', 'delivered', 'received'];

// Timeline status class function
const getTimelineStatusClass = (status) => {
  const currentStatusIndex = statusOrder.indexOf(props.transfer.status);
  const statusIndex = statusOrder.indexOf(status);
  
  // Handle rejected status
  if (props.transfer.status === 'rejected') {
    if (status === 'pending') return 'bg-green-500 border-green-500 text-white'; // completed
    if (status === 'reviewed') return 'bg-green-500 border-green-500 text-white'; // completed
    if (status === 'approved') return 'bg-red-500 border-red-500 text-white'; // rejected
    return 'bg-gray-100 border-gray-300 text-gray-400'; // not applicable
  }
  
  // Normal progression
  if (statusIndex <= currentStatusIndex) {
    return 'bg-green-500 border-green-500 text-white'; // completed
  } else if (statusIndex === currentStatusIndex + 1) {
    return 'bg-orange-500 border-orange-500 text-white'; // current/next
  } else {
    return 'bg-gray-100 border-gray-300 text-gray-400'; // not reached
  }
};

// Computed properties for table functionality
const showReceivedColumn = computed(() => {
  return ['delivered', 'received'].includes(props.transfer.status);
});

const showActionsColumn = computed(() => {
  return ['delivered', 'received'].includes(props.transfer.status);
});

const canEditReceivedQuantity = computed(() => {
  return props.transfer.status === 'delivered';
});

const totalOrderedQuantity = computed(() => {
  return props.transfer.items?.reduce((total, item) => total + (item.quantity || 0), 0) || 0;
});

const totalReceivedQuantity = computed(() => {
  return props.transfer.items?.reduce((total, item) => total + (item.received_quantity || 0), 0) || 0;
});

const completionPercentage = computed(() => {
  if (totalOrderedQuantity.value === 0) return 0;
  return Math.round((totalReceivedQuantity.value / totalOrderedQuantity.value) * 100);
});

// Methods
const isExpiringItem = (expiryDate) => {
  if (!expiryDate) return false;
  const expiry = moment(expiryDate);
  const now = moment();
  const daysUntilExpiry = expiry.diff(now, 'days');
  return daysUntilExpiry <= 30; // Consider items expiring within 30 days as expiring
};

const getReceivedQuantityClass = (item) => {
  if (!item.received_quantity) return 'text-gray-500';
  if (item.received_quantity === item.quantity) return 'text-green-600';
  if (item.received_quantity < item.quantity) return 'text-orange-600';
  return 'text-gray-900';
};

const canCreateBackorder = (item) => {
  return props.transfer.status === 'delivered' && 
         (item.received_quantity || 0) < item.quantity;
};

const updateReceivedQuantity = (itemId, quantity) => {
  // TODO: Implement API call to update received quantity
  console.log('Update received quantity for item:', itemId, 'to:', quantity);
};

const openBackOrderModal = (item) => {
  // TODO: Implement backorder modal
  console.log('Open backorder modal for item:', item);
};

const viewBackorders = (item) => {
  // TODO: Implement view backorders
  console.log('View backorders for item:', item);
};
</script>
