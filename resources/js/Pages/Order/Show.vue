<template>
    <AuthenticatedLayout title="Orders" img="/assets/images/orders.png">
      <!-- Order Header -->
    <div v-if="props.error">
      {{ props.error }}
    </div>
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <Link :href="route('orders.index')">Back to orders</Link>
            <h1 class="text-2xl font-semibold text-gray-900">Order ID. {{ props.order.order_number }}</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span :class="[statusClasses[props.order.status] || statusClasses.default]" class="flex items-center text-lg font-bold px-4 py-2">
              <!-- Status Icon -->
              <span class="mr-3">
                <!-- Pending Icon -->
                <img v-if="props.order.status === 'pending'" src="/assets/images/pending.png" class="w-6 h-6"
                  alt="Pending" />

                <!-- Approved Icon -->
                <img v-else-if="props.order.status === 'approved'" src="/assets/images/approved.png" class="w-6 h-6"
                  alt="Approved" />

                <!-- In Process Icon -->
                <img v-else-if="props.order.status === 'in_process'" src="/assets/images/inprocess.png" class="w-6 h-6"
                  alt="In Process" />

                <!-- Dispatched Icon -->
                <img v-else-if="props.order.status === 'dispatched'" src="/assets/images/dispatch.png" class="w-6 h-6"
                  alt="Dispatched" />

                <!-- Received Icon -->
                <img v-else-if="props.order.status === 'received'" src="/assets/images/received.png" class="w-6 h-6"
                  alt="Received" />

                <!-- Rejected Icon -->
                <svg v-else-if="props.order.status === 'rejected'" class="w-6 h-6 text-red-700" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              {{ props.order.status.toUpperCase() }}
            </span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <!-- Facility Information -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
          <h2 class="text-lg font-medium text-gray-900">Facility Details</h2>
          <div class="flex items-center">
            <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.name }}</span>
          </div>
          <div class="flex items-center">
            <EnvelopeIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.email }}</span>
          </div>
          <div class="flex items-center">
            <PhoneIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.phone }}</span>
          </div>
          <div class="flex items-center">
            <MapPinIcon class="h-5 w-5 text-gray-400 mr-2" />
            <span class="text-sm text-gray-600">{{ props.order.facility.address }}, {{ props.order.facility.city
              }}</span>
          </div>
        </div>
        <div>
          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <h2 class="text-lg font-medium text-gray-900">Order Details</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Order Type</p>
                <p class="text-sm text-gray-900">{{ props.order.order_type }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Order Date</p>
                <p class="text-sm text-gray-900">{{ formatDate(props.order.order_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Expected Date</p>
                <p class="text-sm text-gray-900">{{ formatDate(props.order.expected_date) }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Created By</p>
                <p class="text-sm text-gray-900">{{ props.order.user.name }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Stage Timeline -->
      <div class="col-span-2 mb-6">
        <div class="relative">
          <!-- Timeline Track Background -->
          <div class="absolute top-7 left-0 right-0 h-2 bg-gray-200 z-0"></div>

          <!-- Timeline Progress -->
          <div class="absolute top-7 left-0 h-2 bg-green-500 z-0 transition-all duration-500 ease-in-out" :style="{
            width: `${(statusOrder.indexOf(props.order.status) / (statusOrder.length - 1)) * 100}%`
          }"></div>

          <!-- Timeline Steps -->
          <div class="relative flex justify-between">
            <!-- Pending -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? 'bg-white border-orange-500' : 'bg-white border-gray-200']">
                <img src="/assets/images/pending.svg" class="w-10 h-10" alt="Pending"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-3 text-lg font-bold"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('pending') ? 'text-green-600' : 'text-gray-500'">Pending</span>
            </div>

            <!-- Approved -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? 'bg-white border-orange-500' : 'bg-white border-gray-200']">
                <img src="/assets/images/approved.png" class="w-10 h-10" alt="Approved"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-3 text-lg font-bold"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('approved') ? 'text-green-600' : 'text-gray-500'">Approved</span>
            </div>

            <!-- In Process -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? 'bg-white border-orange-500' : 'bg-white border-gray-200']">
                <img src="/assets/images/inprocess.png" class="w-10 h-10" alt="In Process"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-3 text-lg font-bold"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('in_process') ? 'text-green-600' : 'text-gray-500'">In
                Process</span>
            </div>

            <!-- Dispatch -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center z-10"
                :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? 'bg-white border-orange-500' : 'bg-white border-gray-200']">
                <img src="/assets/images/dispatch.png" class="w-10 h-10" alt="Dispatch"
                  :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? '' : 'opacity-40'" />
              </div>
              <span class="mt-3 text-lg font-bold"
                :class="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('dispatched') ? 'text-green-600' : 'text-gray-500'">Dispatch</span>
            </div>

            <!-- Received -->
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 rounded-full border-4 flex items-center justify-center z-10" :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ?
                (props.order.has_back_order ? 'bg-white border-orange-500' : 'bg-green-500 border-green-600') :
                'bg-white border-gray-200']">
                <img
                  v-if="props.order.has_back_order && statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received')"
                  src="/assets/images/received.png" class="w-10 h-10" alt="Partially Received" />
                <img v-else-if="statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received')"
                  src="/assets/images/check.svg" class="w-10 h-10" alt="Completed" />
                <img v-else src="/assets/images/received.png" class="w-10 h-10 opacity-40" alt="Received" />
              </div>
              <span class="mt-3 text-lg font-bold" :class="[statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ?
                (props.order.has_back_order ? 'text-orange-600' : 'text-green-600') :
                'text-gray-500']">
                {{ statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received') ?
                  (props.order.has_back_order ? 'Partially Received' : 'Completed') : 'Received' }}
              </span>
              <button
                v-if="props.order.has_back_order && statusOrder.indexOf(props.order.status) >= statusOrder.indexOf('received')"
                @click="showBackOrderModal()"
                class="mt-1 text-xs text-orange-600 underline hover:text-orange-800 cursor-pointer">
                View Back Order
              </button>
            </div>
          </div>
        </div>
      </div>
      <!-- Order Items Table -->
      <h2 class="text-lg font-medium text-gray-900 mb-4">Order Items</h2>
      <table class="min-w-full border border-black mb-4">
        <thead>
          <tr class="bg-gray-50">
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">Item
            </th>
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">
              Facility
              Indicators</th>
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">Ordered
              Quantity</th>
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">
              Quantity to
              release</th>
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">Days
            </th>
            <th class="px-3 py-3 text-left text-sm font-medium text-gray-500 uppercase border border-black">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(item, index) in form" :key="item.id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-3 py-3 text-sm text-gray-900 border border-black">
              <div class="flex flex-col">
                <span class="font-medium text-sm">{{ item.product?.name }}</span>
              </div>
            </td>
            <td class="px-3 py-3 text-sm text-gray-900 border border-black">
              <div class="flex flex-col space-y-1 text-sm">
                <div class="flex">
                  <span class="font-medium w-12">SOH:</span>
                  <span>{{ item.soh }}</span>
                </div>
                <div class="flex">
                  <span class="font-medium w-12">AMC:</span>
                  <span>{{ item.amc || 0 }}</span>
                </div>
                <div class="flex">
                  <span class="font-medium w-12">QOO:</span>
                  <span>{{ item.quantity_on_order }}</span>
                </div>
              </div>
            </td>
            <td class="px-3 py-3 text-sm text-gray-900 border border-black">
              {{ item.quantity }}
            </td>
            <td class="px-3 py-3 text-sm text-gray-900 border border-black">
              <div class="flex justify-between items-start gap-2">
                <div class="w-[30%]">
                  <input type="number" placeholder="0" v-model="item.quantity_to_release"
                    @keydown.enter="updateQuantity(item)"
                    :readonly="isUpading || props.order.status != 'pending'"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" />
                    <span v-if="isUpading" class="text-green-500 text-md">Updating...</span>
                </div>
                <div class="border rounded-md overflow-hidden text-sm flex-1">
                  <table class="min-w-full divide-y divide-gray-200">
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="inv in item.inventory_allocations" :key="inv.id" class="hover:bg-gray-100">
                        <td class="px-2 py-1">
                          <div class="font-medium">QTY: {{ inv.allocated_quantity }}</div>
                          <div class="font-medium">Batch: {{ inv.batch_number }}</div>
                          <div class="font-medium">Barcode: {{ inv.barcode || 'N/A' }}</div>
                          <div class="font-medium">WH:{{ inv.warehouse?.name }}</div>
                          <div class="font-medium">Loc: {{ inv.location?.location }}</div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </td>
            <td class="px-3 py-3 text-sm text-gray-900 border border-black">
              <div class="w-full flex justify-center">
                <input type="number" v-model="item.no_of_days" readonly
                  class="w-[70%] rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm" />
              </div>
            </td>
            <td class="px-2 py-3 text-sm text-gray-900 w-10 border border-black">
              <div class="flex flex-col space-y-2">
                <button type="button"
                  class="inline-flex items-center justify-center w-8 h-8 bg-red-100 border border-transparent rounded text-sm text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  title="Delete">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Status Actions Section - Single row with actions and status icons -->
      <div class="mt-8 mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">Order Status Actions</h3>
        <div class="flex justify-center items-center mb-6">

          <!-- Status Action Buttons -->
          <div class="flex flex-wrap items-center justify-center gap-4">
            <!-- Pending status indicator -->
            <div class="relative">
              <button 
                :class="[
                  props.order.status === 'pending' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
                  statusOrder.indexOf(props.order.status) > statusOrder.indexOf('pending') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
                ]"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
                <img src="/assets/images/pending.png" class="w-5 h-5 mr-2" alt="Pending" />
                <span class="text-sm font-bold text-white">Pending</span>
              </button>
              <div v-if="props.order.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>
            <!-- Approve button -->
            <div class="relative">
              <button @click="changeStatus(props.order.id, 'approved')"
                :disabled="isLoading || props.order.status !== 'pending'"
                :class="[
                  props.order.status === 'pending' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
                  statusOrder.indexOf(props.order.status) > statusOrder.indexOf('pending') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
                ]"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                <svg v-if="isLoading && props.order.status === 'pending'" class="animate-spin h-5 w-5 mr-2"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <template v-else>
                  <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                  <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.order.status) > statusOrder.indexOf('pending') ? 'Approved' : 'Approve' }}</span>
                </template>
              </button>
              <div v-if="props.order.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>

            <!-- Process button -->
            <div class="relative">
              <button @click="changeStatus(props.order.id, 'in_process')"
                :disabled="isLoading || props.order.status !== 'approved'"
                :class="[
                  props.order.status === 'approved' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
                  statusOrder.indexOf(props.order.status) > statusOrder.indexOf('approved') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
                ]"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                <svg v-if="isLoading && props.order.status === 'approved'" class="animate-spin h-5 w-5 mr-2"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <template v-else>
                  <img src="/assets/images/inprocess.png" class="w-5 h-5 mr-2" alt="Process" />
                  <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.order.status) > statusOrder.indexOf('approved') ? 'Processed' : 'Process' }}</span>
                </template>
              </button>
              <div v-if="props.order.status === 'approved'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>

            <!-- Dispatch button -->
            <div class="relative">
              <button @click="changeStatus(props.order.id, 'dispatched')"
                :disabled="isLoading || props.order.status !== 'in_process'"
                :class="[
                  props.order.status === 'in_process' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
                  statusOrder.indexOf(props.order.status) > statusOrder.indexOf('in_process') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
                ]"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                <svg v-if="isLoading && props.order.status === 'in_process'" class="animate-spin h-5 w-5 mr-2"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <template v-else>
                  <img src="/assets/images/dispatch.png" class="w-5 h-5 mr-2" alt="Dispatch" />
                  <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.order.status) > statusOrder.indexOf('in_process') ? 'Dispatched' : 'Dispatch' }}</span>
                </template>
              </button>
              <div v-if="props.order.status === 'in_process'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>

            <!-- Receive button -->
            <div class="relative">
              <button @click="changeStatus(props.order.id, 'received')"
                :disabled="isLoading || props.order.status !== 'dispatched'"
                :class="[
                  props.order.status === 'dispatched' ? 'bg-[#f59e0b] hover:bg-[#d97706]' : 
                  statusOrder.indexOf(props.order.status) > statusOrder.indexOf('dispatched') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
                ]"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                <svg v-if="isLoading && props.order.status === 'dispatched'" class="animate-spin h-5 w-5 mr-2"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <template v-else>
                  <img src="/assets/images/received.png" class="w-5 h-5 mr-2" alt="Receive" />
                  <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.order.status) > statusOrder.indexOf('delivered') ? 'Received' : 'Receive' }}</span>
                </template>
              </button>
              <div v-if="props.order.status === 'dispatched'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
            </div>

            <!-- Reject button (only available for pending status) -->
            <div class="relative" v-if="props.order.status === 'pending'">
              <button @click="changeStatus(props.order.id, 'rejected')"
                :disabled="isLoading"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-red-600 hover:bg-red-700 min-w-[160px]">
                <svg v-if="isLoading" class="animate-spin h-5 w-5 mr-2"
                  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <template v-else>
                  <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  <span class="text-sm font-bold text-white">Reject</span>
                </template>
              </button>
            </div>

            <!-- Status indicator for rejected status -->
            <div v-if="props.order.status === 'rejected'" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-100 text-red-800 min-w-[160px]">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span class="text-sm font-bold">Rejected</span>
            </div>

            <!-- Status indicator for received status -->
            <div v-if="props.order.status === 'received'" 
              :class="[props.order.has_back_order ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800']" 
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg min-w-[160px]">
              <svg v-if="!props.order.has_back_order" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9,16.17 L4.83,12 L3.41,13.41 L9,19 L21,7 L19.59,5.59 L9,16.17 Z" fill="currentColor" />
              </svg>
              <span class="text-sm font-bold">{{ props.order.has_back_order ? 'Partially Received' : 'Completed' }}</span>
              <button v-if="props.order.has_back_order" @click="showBackOrderModal()" class="ml-2 underline hover:text-orange-900 focus:outline-none text-xs">
                View Back Order
              </button>
            </div>
          </div>
        </div>

      </div>
    <!-- Back Order Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[80vh] overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900">Back Order Details</h3>
          <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-4 overflow-y-auto max-h-[60vh]">
          <div class="mb-4">
            <span class="text-sm font-medium text-gray-700">Order #{{ props.order.id }}</span>
            <h2 class="text-xl font-bold text-gray-900">Back Ordered Items</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                    Item</th>
                  <th
                    class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                    Ordered Qty</th>
                  <th
                    class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                    Received Qty</th>
                  <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Back Order
                    Qty
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in backOrderItems" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">{{ item.product?.name }}</td>
                  <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">{{ item.quantity }}</td>
                  <td class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200">{{ item.received_quantity || 0 }}
                  </td>
                  <td class="px-3 py-3 text-sm text-gray-900">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                      {{ item.quantity - (item.received_quantity || 0) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-4 text-sm text-gray-500">
            <p>These items have been partially received. The remaining quantities are on back order.</p>
          </div>
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end">
          <button @click="showModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150">
            Close
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed, onMounted, ref, h } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link } from '@inertiajs/vue3';
import {
  BuildingOfficeIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon
} from '@heroicons/vue/24/outline';
import Swal from 'sweetalert2';
import axios from 'axios';
import moment from 'moment';

const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  error: String,
  products: Array
});

const showModal = ref(false);

// Function to show the back order modal
const showBackOrderModal = () => {
  showModal.value = true;
};

// Computed property to get back order items
const backOrderItems = computed(() => {
  if (!props.order.items) return [];
  return props.order.items.filter(item => {
    return item.received_quantity !== undefined &&
      item.received_quantity < item.quantity;
  });
});

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800 rounded-full font-bold',
  approved: 'bg-green-100 text-green-800 rounded-full font-bold',
  'in process': 'bg-blue-100 text-blue-800 rounded-full font-bold',
  dispatched: 'bg-purple-100 text-purple-800 rounded-full font-bold',
  received: 'bg-green-100 text-green-800 rounded-full font-bold flex items-center',
  'partially_received': 'bg-orange-100 text-orange-800 rounded-full font-bold',
  default: 'bg-gray-100 text-gray-800 rounded-full font-bold'
};

// Function to get the display status
const getDisplayStatus = (status, hasBackOrder) => {
  if (status === 'received') {
    if (hasBackOrder) {
      return 'Partially Received';
    }
    return 'Completed';
  }
  return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
};



const form = ref([]);
const isLoading = ref(false);

onMounted(() => {
  form.value = props.order.items || [];
});

const formatDate = (date) => {
  return moment(date).format('DD/MM/YYYY');
};

const statusOrder = ['pending', 'approved', 'in_process', 'dispatched', 'received'];

// update quantity
const isUpading = ref(false);
async function updateQuantity(item){
  isUpading.value = true;
  await axios.post(route('orders.update-quantity'), {
    item_id: item.id,
    quantity: item.quantity_to_release
  })
  .then((response) => {
    isUpading.value = false;
    Swal.fire({
      title: 'Success!',
      text: response.data,
      icon: 'success',
      confirmButtonText: 'OK'
    }).then(() => {
      router.get(route('orders.show', props.order.id));
    });

  })
  .catch((error) => {
    isUpading.value = false
    console.log(error);
    toast.error(error.response?.data || 'Failed to update quantity');
  });
}

// Function to change order status
const changeStatus = (orderId, newStatus) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Do you want to change the order status to ${newStatus}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, change it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      // Set loading state
      isLoading.value = true;

      await axios.post(route('orders.change-status'), {
        order_id: orderId,
        status: newStatus
      })
        .then(response => {
          // Reset loading state
          isLoading.value = false;

          Swal.fire({
            title: 'Updated!',
            text: 'Order status has been updated.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          }).then(() => {
            // Reload the page to show the updated status
            router.get(route('orders.show', props.order.id));
          });
        })
        .catch(error => {
          // Reset loading state
          isLoading.value = false;

          Swal.fire({
            title: 'Error!',
            text: error.response?.data || 'Failed to update order status',
            icon: 'error',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
          });
        });
    }
  });
};

const getStatusProgress = (currentStatus) => {
  const currentIndex = statusOrder.indexOf(currentStatus);
  return statusOrder.map((status, index) => ({
    status,
    isActive: index <= currentIndex,
    isPast: index < currentIndex
  }));
};

async function submit(item, index) {
  console.log(item)
}

const statusProgress = computed(() => getStatusProgress(props.order.status));

</script>