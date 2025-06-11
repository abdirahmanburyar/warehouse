<template>
  <AuthenticatedLayout title="Optimize Your Transfers" description="Moving Supplies, Bridging needs"
  img="/assets/images/transfer.png">    <!-- Transfer Header -->
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
               <img v-if="props.transfer.status === 'pending'" src="/assets/images/pending.png" class="w-6 h-6"
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
       <div class="bg-gray-50 rounded-lg p-4 space-y-3">
         <h2 class="text-lg font-medium text-gray-900">From Location Details</h2>
         
         <!-- From Warehouse Details -->
         <div v-if="props.transfer.from_warehouse" class="space-y-2">
           <div class="flex items-center">
             <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
             <span class="text-sm font-medium text-gray-800">Warehouse: {{ props.transfer.from_warehouse.name }}</span>
           </div>
           <div class="ml-7 space-y-1">
             <div v-if="props.transfer.from_warehouse.code" class="text-sm text-gray-600">Code: {{ props.transfer.from_warehouse.code }}</div>
             <div v-if="props.transfer.from_warehouse.address" class="text-sm text-gray-600">Address: {{ props.transfer.from_warehouse.address }}</div>
             <div v-if="props.transfer.from_warehouse.district" class="text-sm text-gray-600">District: {{ props.transfer.from_warehouse.district }}</div>
             <div v-if="props.transfer.from_warehouse.city" class="text-sm text-gray-600">City: {{ props.transfer.from_warehouse.city }}</div>
             <div v-if="props.transfer.from_warehouse.manager_name" class="text-sm text-gray-600">Manager: {{ props.transfer.from_warehouse.manager_name }}</div>
             <div v-if="props.transfer.from_warehouse.manager_phone" class="text-sm text-gray-600">Phone: {{ props.transfer.from_warehouse.manager_phone }}</div>
           </div>
         </div>
         
         <!-- From Facility Details -->
         <div v-if="props.transfer.from_facility" class="space-y-2">
           <div class="flex items-center">
             <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
             <span class="text-sm font-medium text-gray-800">Facility: {{ props.transfer.from_facility.name }}</span>
           </div>
           <div class="ml-7 space-y-1">
             <div v-if="props.transfer.from_facility.facility_type" class="text-sm text-gray-600">Type: {{ props.transfer.from_facility.facility_type }}</div>
             <div v-if="props.transfer.from_facility.address" class="text-sm text-gray-600">Address: {{ props.transfer.from_facility.address }}</div>
             <div v-if="props.transfer.from_facility.district" class="text-sm text-gray-600">District: {{ props.transfer.from_facility.district }}</div>
             <div v-if="props.transfer.from_facility.phone" class="text-sm text-gray-600">Phone: {{ props.transfer.from_facility.phone }}</div>
             <div v-if="props.transfer.from_facility.email" class="text-sm text-gray-600">Email: {{ props.transfer.from_facility.email }}</div>
             <div v-if="props.transfer.from_facility.has_cold_storage !== undefined" class="text-sm text-gray-600">
               Cold Storage: <span :class="props.transfer.from_facility.has_cold_storage ? 'text-green-600' : 'text-red-600'">
                 {{ props.transfer.from_facility.has_cold_storage ? 'Yes' : 'No' }}
               </span>
             </div>
           </div>
         </div>
         
         <div v-if="!props.transfer.from_warehouse && !props.transfer.from_facility"
           class="text-sm text-gray-500 italic">
           No source location information available
         </div>
       </div>

       <!-- To Location Information -->
       <div class="bg-gray-50 rounded-lg p-4 space-y-3">
         <h2 class="text-lg font-medium text-gray-900">To Location Details</h2>
         
         <!-- To Warehouse Details -->
         <div v-if="props.transfer.to_warehouse" class="space-y-2">
           <div class="flex items-center">
             <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
             <span class="text-sm font-medium text-gray-800">Warehouse: {{ props.transfer.to_warehouse.name }}</span>
           </div>
           <div class="ml-7 space-y-1">
             <div v-if="props.transfer.to_warehouse.code" class="text-sm text-gray-600">Code: {{ props.transfer.to_warehouse.code }}</div>
             <div v-if="props.transfer.to_warehouse.address" class="text-sm text-gray-600">Address: {{ props.transfer.to_warehouse.address }}</div>
             <div v-if="props.transfer.to_warehouse.district" class="text-sm text-gray-600">District: {{ props.transfer.to_warehouse.district }}</div>
             <div v-if="props.transfer.to_warehouse.city" class="text-sm text-gray-600">City: {{ props.transfer.to_warehouse.city }}</div>
             <div v-if="props.transfer.to_warehouse.manager_name" class="text-sm text-gray-600">Manager: {{ props.transfer.to_warehouse.manager_name }}</div>
             <div v-if="props.transfer.to_warehouse.manager_phone" class="text-sm text-gray-600">Phone: {{ props.transfer.to_warehouse.manager_phone }}</div>
           </div>
         </div>
         
         <!-- To Facility Details -->
         <div v-if="props.transfer.to_facility" class="space-y-2">
           <div class="flex items-center">
             <BuildingOfficeIcon class="h-5 w-5 text-gray-400 mr-2" />
             <span class="text-sm font-medium text-gray-800">Facility: {{ props.transfer.to_facility.name }}</span>
           </div>
           <div class="ml-7 space-y-1">
             <div v-if="props.transfer.to_facility.facility_type" class="text-sm text-gray-600">Type: {{ props.transfer.to_facility.facility_type }}</div>
             <div v-if="props.transfer.to_facility.address" class="text-sm text-gray-600">Address: {{ props.transfer.to_facility.address }}</div>
             <div v-if="props.transfer.to_facility.district" class="text-sm text-gray-600">District: {{ props.transfer.to_facility.district }}</div>
             <div v-if="props.transfer.to_facility.phone" class="text-sm text-gray-600">Phone: {{ props.transfer.to_facility.phone }}</div>
             <div v-if="props.transfer.to_facility.email" class="text-sm text-gray-600">Email: {{ props.transfer.to_facility.email }}</div>
             <div v-if="props.transfer.to_facility.has_cold_storage !== undefined" class="text-sm text-gray-600">
               Cold Storage: <span :class="props.transfer.to_facility.has_cold_storage ? 'text-green-600' : 'text-red-600'">
                 {{ props.transfer.to_facility.has_cold_storage ? 'Yes' : 'No' }}
               </span>
             </div>
           </div>
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
               <img src="/assets/images/pending.png" class="w-9 h-9 z-10" alt="Pending" />
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
               <img src="/assets/images/inprocess.png" class="w-9 h-9 z-10" alt="In Process"
                 :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('in_process') ? '' : 'opacity-40'" />
             </div>
             <span class="mt-2 text-xs font-bold" :class="{
               'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process'),
               'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('in_process'),
               'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('in_process')
             }">In Process</span>
           </div>

           <!-- Dispatched -->
           <div class="flex flex-col items-center">
             <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
               :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('dispatched') ? 'border-orange-500' : 'border-gray-300'">
               <img src="/assets/images/dispatch.png" class="w-9 h-9 z-10" alt="Dispatched"
                 :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('dispatched') ? '' : 'opacity-40'" />
             </div>
             <span class="mt-2 text-xs font-bold" :class="{
               'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('dispatched'),
               'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('dispatched'),
               'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('dispatched')
             }">Dispatched</span>
           </div>

           <!-- Received -->
           <div class="flex flex-col items-center">
             <div class="w-16 h-16 rounded-full flex items-center justify-center z-10 relative bg-white border-4"
               :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? 'border-orange-500' : 'border-gray-300'">
               <img src="/assets/images/received.png" class="w-9 h-9 z-10" alt="Received"
                 :class="statusOrder.indexOf(props.transfer.status) >= statusOrder.indexOf('received') ? '' : 'opacity-40'" />
             </div>
             <span class="mt-2 text-xs font-bold" :class="{
               'text-green-600': statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('received'),
               'text-gray-700': statusOrder.indexOf(props.transfer.status) === statusOrder.indexOf('received'),
               'text-gray-400': statusOrder.indexOf(props.transfer.status) < statusOrder.indexOf('received')
             }">Received</span>
           </div>
         </div>
       </div>
     </div>

     <!-- Transfer Items Table -->
     <h2 class="text-lg font-medium text-gray-900">Transfer Items</h2>
     <table class="min-w-full rounded-3xl">
      <thead style="background-color: #EEF1F8;">
         <tr class="bg-gray-50">
           <th
             class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-[300px] min-w-[300px] max-w-[300px]">
             Items</th>
           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  w-[100px]">
             UOM</th>
           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  w-[150px]">
             Item Information</th>
           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  w-[100px]">
             Quantity to release</th>
           <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase  w-[100px]">
             Action</th>
         </tr>
       </thead>
       <tbody class="bg-white divide-y divide-gray-200">
         <tr v-for="item in props.transfer.items" :key="item.id"
           class="hover:bg-gray-50 transition-colors duration-150">
           <td class="px-6 py-4 text-sm">
             <div class="flex flex-col">
               <span class="font-medium">{{ item.product?.name || 'Unknown Product' }}</span>
               <span class="text-xs text-gray-500">ID: {{ item.product?.productID }}</span>
             </div>
           </td>
           <td class="px-6 py-4 text-sm">
             <span
               class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
               {{ item.uom || 'N/A' }}
             </span>
           </td>
           <td class="px-6 py-4 text-sm">
             <div class="text-sm bg-gray-50">
               Batch Number: {{ item.batch_number }}
             </div>
             <div class="text-sm bg-gray-50">
               Barcode: {{ item.barcode }}
             </div>
             <div class="flex items-center text-sm">
               Expiry Date: {{ formatDate(item.expire_date) }}
             </div>
           </td>
           <td class="px-6 py-4 text-sm">
             <input type="number" v-model="item.quantity" min="1"
             :disabled="currentUserWarehouse.id != props.transfer.from_warehouse_id || props.transfer.status == 'dispatched' || props.transfer.status == 'received'"
             @keyup.enter="updateTransferItemQuantity(item)"
              required class="w-full rounded-3xl"/>
              <p v-if="isItemUpdating[item.id]" class="text-xs text-gray-500">Updating...</p>
             <label>Quantity received</label>
             <input 
               type="number" 
               v-model="item.received_quantity" 
               :disabled="!props.transfer.to_warehouse_id || props.transfer.status != 'dispatched'"
               min="0" 
               :max="item.quantity" 
               @input="validateReceivedQuantity(item)" 
               required 
               class="w-full rounded-3xl"
             />
             <!-- v-if="currentUserWarehouse.id == props.transfer.to_warehouse_id && (props.transfer.status === 'dispatched' || props.transfer.status === 'received') && 
                     (item.quantity > (item.received_quantity || 0) || (item.backorders && item.backorders.length > 0))"  -->
             <button
               @click="openBackOrderModal(item)" 
               class="mt-2 p-3 bg-yellow-500 text-black rounded-3xl hover:bg-yellow-600 text-sm w-full"
             >
               {{ item.backorders && item.backorders.length > 0 ? 'View/Edit Back Order' : 'Back Order' }}
             </button>
             <span v-if="error?.[item.id]" class="text-red-500 text-xs mt-2">{{ error?.[item.id] }}</span>
           </td>
           <td class="px-6 py-4 text-sm">
            <span v-if="isDeleting[item.id]" class="text-red-500 text-xs mt-2">Deleting...</span>
            <button v-else @click="confirmDelete(item.id)" class="text-red-600 hover:text-red-900">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round"
                         stroke-width="2"
                         d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                 </svg>
             </button>
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
           <img src="/assets/images/pending.png" class="w-8 h-8 mr-2" alt="Pending" />
           <span class="text-sm font-bold text-white">Pending</span>
         </button>
         <div v-if="props.transfer.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
       </div>

       <!-- Approve button -->
       <div class="relative">
        <button @click="changeStatus(props.transfer.id, 'approved')" v-if="props.transfer.status === 'pending' && $page.props.auth.can.transfer_approve" 
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
           <img src="/assets/images/approved.png" class="w-8 h-8 mr-2" alt="Process" />
           <span class="text-sm font-bold text-white">Approve</span>
         </button>
         <button v-else
           :class="[
             statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
           ]"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
           <img src="/assets/images/approved.png" class="w-8 h-8 mr-2" alt="Approve" />
           <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending') ? 'Approved' : 'Waiting to be Approved' }}</span>
         </button>
         <div v-if="props.transfer.status === 'pending'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
       </div>

       <!-- Process button -->
       <div class="relative">
         <button @click="changeStatus(props.transfer.id, 'in_process')" v-if="props.transfer.status === 'approved'  && $page.props.auth.can.transfer_in_process" 
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
           <img src="/assets/images/inprocess.png" class="w-8 h-8 mr-2" alt="Process" />
           <span class="text-sm font-bold text-white">Process</span>
         </button>
         <button v-else
           :class="[
             statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('approved') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
           ]"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
           <img src="/assets/images/inprocess.png" class="w-8 h-8 mr-2" alt="Process" />
           <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('approved') ? 'Processed' : 'Process' }}</span>
         </button>
         <div v-if="props.transfer.status === 'approved'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
       </div>

       <!-- Dispatch button -->
       <div class="relative">
         <button @click="changeStatus(props.transfer.id, 'dispatched')" v-if="props.transfer.status === 'in_process' && $page.props.auth.can.transfer_dispatch" 
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
           <img src="/assets/images/dispatch.png" class="w-8 h-8 mr-2" alt="Dispatch" />
           <span class="text-sm font-bold text-white">Dispatch</span>
         </button>
         <button v-else
           :class="[
             statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
           ]"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
           <img src="/assets/images/dispatch.png" class="w-8 h-8 mr-2" alt="Dispatch" />
           <span class="text-sm font-bold text-white">{{ statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('in_process') ? 'Dispatched' : 'Dispatch' }}</span>
         </button>
         <div v-if="props.transfer.status === 'in_process'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
       </div>

       <div class="relative">
         <button @click="receiveTransfer(props.transfer.id)" v-if="props.transfer.status === 'dispatched' && (props.transfer?.to_warehouse_id && $page.props.auth.can.transfer_receive)" 
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-[#f59e0b] hover:bg-[#d97706] min-w-[160px]">
           <img src="/assets/images/dispatch.png" class="w-8 h-8 mr-2" alt="Dispatch" />
           <span class="text-sm font-bold text-white">Received</span>
         </button>
         <button v-else
           :class="[
             statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('dispatched') ? 'bg-[#55c5ff]' : 'bg-gray-300 cursor-not-allowed'
           ]"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]" disabled>
           <img src="/assets/images/received.png" class="w-8 h-8 mr-2" alt="Dispatch" />
           <span class="text-sm font-bold text-white">
             {{props.transfer.to_warehouse_id != currentUserWarehouse.id && props.transfer.status === 'dispatched' ? 'Waiting to be Received' : 'Received'}}
           </span>
         </button>
         <div v-if="props.transfer.status === 'dispatched'" class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
       </div>

       <!-- Reject button (only available for pending status) -->
       <div class="relative" v-if="props.transfer.status === 'pending'">
         <button @click="changeStatus(props.transfer.id, 'rejected')" 
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-red-600 hover:bg-red-700 min-w-[160px]">
           <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
           </svg>
           <span class="text-sm font-bold text-white">Reject</span>
         </button>
       </div>

       <!-- Status indicator for rejected status -->
       <div v-if="props.transfer.status === 'rejected'" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-100 text-red-800 min-w-[160px]">
         <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
         </svg>
         <span class="text-sm font-bold">Rejected</span>
       </div>

     </div>
   </div>
 <!-- Back Order Modal -->
 <Modal
   :show="showBackOrderModal"
   @close="attemptCloseModal"
   maxWidth="2xl"
 >
   <div v-if="showIncompleteBackOrderModal" class="mb-6">
     <div class="flex items-center mb-4">
       <div class="rounded-full bg-yellow-100 p-3 mr-3">
         <svg
           class="w-6 h-6 text-yellow-600"
           fill="none"
           stroke="currentColor"
           viewBox="0 0 24 24"
         >
           <path
             stroke-linecap="round"
             stroke-linejoin="round"
             stroke-width="2"
             d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
           />
         </svg>
       </div>
       <h2 class="text-lg font-medium text-gray-900">
         Incomplete Back Orders
       </h2>
     </div>

     <div class="mb-4 p-4 bg-yellow-50 rounded-md">
       <p class="text-sm text-yellow-700 mb-2">
         You have started creating back orders but have not allocated all
         missing quantities.
       </p>
       <p class="text-sm text-yellow-700">
         Do you want to discard these incomplete back orders and close the
         dialog?
       </p>
     </div>

     <div class="flex justify-end space-x-3">
       <button
         @click="showIncompleteBackOrderModal = false"
         class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
       >
         Continue Editing
       </button>
       <button
         @click="discardAndClose"
         class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
       >
         Discard & Close
       </button>
     </div>
   </div>

   <div v-else class="p-6">
     <div class="mb-4">
       <div class="flex justify-between items-center">
         <h2 class="text-lg font-medium text-gray-900">
           Back Order for {{ selectedItem?.product?.name }}
         </h2>
       </div>
     </div>

     <div class="bg-white rounded-lg p-4 mb-4 shadow-sm">
       <div class="mb-4">
         <div class="flex justify-between items-center">
           <p class="text-sm font-medium text-gray-700">
             Missing Quantity (Back Order):
           </p>
           <p
             class="text-lg font-bold"
             :class="
               missingQuantity > 0
                 ? 'text-red-600'
                 : 'text-green-600'
             "
           >
             {{ missingQuantity }}
           </p>
         </div>
         <p class="text-xs text-gray-500 mt-1">
           This is the difference between quantity to ship and received quantity
         </p>
       </div>

       <div class="mt-3">
         <div class="flex justify-between items-center">
           <p class="text-sm font-medium text-gray-600">
             Existing Back Orders:
           </p>
           <p class="text-sm font-bold text-gray-800">
             {{ totalExistingDifferences }}
           </p>
         </div>
         <div class="flex justify-between items-center mt-1">
           <p class="text-sm font-medium text-yellow-800">
             Remaining to Allocate:
           </p>
           <p
             class="text-sm font-bold"
             :class="
               remainingToAllocate > 0
                 ? 'text-red-600'
                 : 'text-green-600'
             "
           >
             {{ remainingToAllocate }}
           </p>
         </div>
       </div>
     </div>

     <!-- Batch-specific back orders -->
     <div class="mb-4">
       <h3 class="text-md font-medium text-gray-700 mb-2">
         Batch Information
       </h3>
       <div class="bg-gray-50 p-3 rounded mb-4">
         <p class="text-sm text-gray-500 mb-2">
           Please allocate the missing quantity ({{ missingQuantity }}) and specify the problem type.
         </p>
         <p class="text-sm text-gray-500">
           Back orders represent the difference between quantity to ship and received quantity.
         </p>
       </div>

       <!-- Back order form -->
       <div class="border rounded-md p-3 mb-3">
         <div class="flex items-center justify-between mb-2">
           <div class="font-medium text-gray-700">
             {{ selectedItem?.product?.name }}
             <span class="ml-2 text-sm text-gray-500">({{ selectedItem?.quantity }} units)</span>
           </div>
           <div>
             <button
               @click="addBackOrder"
               class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
               :disabled="!canAddMoreBackOrders || isSaving || props.transfer.status === 'received'"
             >
               Add Issue
             </button>
           </div>
         </div>

         <!-- Back order rows -->
         <span v-if="message" class="text-sm text-red-600">{{ message }}</span>
         <div v-if="backOrders.length > 0" class="mt-3">
          <table class="min-w-full rounded-3xl">
            <thead style="background-color: #EEF1F8;">
               <tr>
                 <th class="px-2 py-1 text-left text-xs font-medium text-gray-500">
                   Issue Type
                 </th>
                 <th class="px-2 py-1 text-left text-xs font-medium text-gray-500">
                   Quantity
                 </th>
                 <th class="px-2 py-1 text-left text-xs font-medium text-gray-500">
                   Notes
                 </th>
                 <th class="px-2 py-1 text-left text-xs font-medium text-gray-500">
                   Actions
                 </th>
               </tr>
             </thead>
             <tbody class="bg-white divide-y divide-gray-200">
               <tr
                 v-for="(row, rowIndex) in backOrders"
                 :key="rowIndex"
                 class="hover:bg-gray-50"
               >
                 <td class="px-2 py-1">
                   <select
                     v-model="row.type"
                     class="text-sm w-full border-gray-300 rounded-md shadow-sm"
                     :disabled="props.transfer.status === 'received'"
                   >
                     <option
                       v-for="type in [
                         'Missing',
                         'Damaged',
                         'Expired',
                         'Lost',
                       ]"
                       :key="type"
                       :value="type"
                     >
                       {{ type }}
                     </option>
                   </select>
                 </td>
                 <td class="px-2 py-1">
                   <input
                     type="number"
                     v-model="row.quantity"
                     class="text-sm w-full border-gray-300 rounded-md shadow-sm"
                     min="1"
                     :max="missingQuantity"
                     @input="validateBackOrderQuantities"
                     :disabled="props.transfer.status === 'received'"
                   />
                 </td>
                 <td class="px-2 py-1">
                   <input
                     type="text"
                     v-model="row.notes"
                     class="text-sm w-full border-gray-300 rounded-md shadow-sm"
                     placeholder="Optional notes"
                     :disabled="props.transfer.status === 'received'"
                   />
                 </td>
                 <td class="px-2 py-1 text-center">
                   <button
                     @click="removeBackOrder(rowIndex)"
                     class="text-red-500 hover:text-red-700"
                     :disabled="isSaving || props.transfer.status === 'received'"
                   >
                     <svg
                       xmlns="http://www.w3.org/2000/svg"
                       class="h-4 w-4"
                       fill="none"
                       viewBox="0 0 24 24"
                       stroke="currentColor"
                     >
                       <path
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         stroke-width="2"
                         d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                       />
                     </svg>
                   </button>
                 </td>
               </tr>
             </tbody>
           </table>

           <div class="mt-2">
             <div
               v-if="
                 missingQuantity > 0 &&
                 totalBackOrderQuantity < missingQuantity
               "
               class="text-xs text-yellow-600 mt-1"
             >
               {{ missingQuantity - totalBackOrderQuantity }}
               more items need to be allocated
             </div>
             <div
               v-else-if="
                 missingQuantity > 0 &&
                 totalBackOrderQuantity > missingQuantity
               "
               class="text-xs text-red-600 mt-1"
             >
               Over-allocated by
               {{ totalBackOrderQuantity - missingQuantity }}
               items
             </div>
           </div>
         </div>
         <div v-else class="text-sm text-gray-500 text-center py-3">
           No back orders added yet. Click "Add Issue" to create one.
         </div>
       </div>
     </div>

     <div class="flex justify-end space-x-3 mt-4">
       <button
         @click="attemptCloseModal"
         class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
       >
         Exit
       </button>
       <button
         @click="saveBackOrders"
         class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
         :disabled="!isValidForSave || isSaving || props.transfer.status === 'received'"
       >
         <span v-if="isSaving" class="flex items-center">
           <svg
             class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
             xmlns="http://www.w3.org/2000/svg"
             fill="none"
             viewBox="0 0 24 24"
           >
             <circle
               class="opacity-25"
               cx="12"
               cy="12"
               r="10"
               stroke="currentColor"
               stroke-width="4"
             ></circle>
             <path
               class="opacity-75"
               fill="currentColor"
               d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
             ></path>
           </svg>
          Saving...
         </span>
         <span v-else>Save Back Orders</span>
       </button>
     </div>
   </div>
 </Modal>

 </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import Modal from '@/Components/Modal.vue';
import moment from 'moment';
import { BuildingOfficeIcon } from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';

const toast = useToast();
// Get the current user's facility ID
const currentUserWarehouse = computed(() => usePage().props.warehouse);

// Loading state
const isLoading = ref(false);

// Back order related state
const showBackOrderModal = ref(false);
const showIncompleteBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrders = ref([]);
const isSaving = ref(false);
const message = ref('');

// Functions for back order modal
const openBackOrderModal = (item) => {
  console.log(item);
 selectedItem.value = item;
 backOrders.value = [];
 showIncompleteBackOrderModal.value = false;

 // If there's no difference between quantity and received_quantity and no existing back orders, no need for back orders
 if (item.quantity <= (item.received_quantity || 0) && (!item.backorders || item.backorders.length === 0)) {
   Swal.fire({
     title: "No Back Order Needed",
     text: "All quantities have been received. No back order is necessary.",
     icon: "info",
     confirmButtonText: "OK",
   });
   return;
 }

 // If there are existing back orders, load them
 if (item.backorders && item.backorders.length > 0) {
   item.backorders.forEach((bo) => {
     backOrders.value.push({
       id: bo.id,
       inventory_allocation_id: bo.inventory_allocation_id,
       quantity: bo.quantity,
       type: bo.type,
       notes: bo.notes,
     });
   });
 }

 showBackOrderModal.value = true;
};

// Add a new back order row
const addBackOrder = () => {
 if (!canAddMoreBackOrders.value) {
   message.value = "Cannot add more back orders. Maximum quantity reached.";
   setTimeout(() => {
     message.value = "";
   }, 3000);
   return;
 }

 backOrders.value.push({
   inventory_allocation_id: null,  // Will be set during save
   quantity: remainingToAllocate.value > 0 ? remainingToAllocate.value : 1,
   type: "",
   notes: "",
 });
};

// Update transfer item quantity

const isItemUpdating = ref([]);
async function updateTransferItemQuantity(item){

 if(item.quantity < 1){
   item.quantity = 1;
   return;
 }

 isItemUpdating.value[item.id] = true;

 await axios.post(route("transfers.update-item"), {
   id: item.id,
   quantity: item.quantity,
 })
 .then((response) => {
   isItemUpdating.value[item.id] = false;
   console.log(response.data);
   toast.success(response.data);
 })
 .catch((error) => {
   isItemUpdating.value[item.id] = false;
   console.error(error.response?.data);
   
   // Check if the response has a structured error with quantity
   if (error.response?.data && typeof error.response.data === 'object' && error.response.data.quantity) {
     // Reset the item quantity to the original value returned from the server
     item.quantity = error.response.data.quantity;
     // Show the error message
     toast.error(error.response.data.message || "Failed to update transfer item");
   } else {
     // Handle the old response format or other errors
     toast.error(error.response?.data || "Failed to update transfer item");
   }
 })
}

// Remove a back order row
const removeBackOrder = async (index) => {
 const row = backOrders.value[index];
 message.value = "";
 
 // If the back order has an ID, it exists in the database and needs to be deleted
 if (row.id) {
   try {
     await axios.post(route("transfers.remove-back-order"), {
       id: row.id,
     });
     message.value = "Back order removed successfully";
     // Remove from local array
     backOrders.value.splice(index, 1);
   } catch (error) {
     message.value = error.response?.data || "Error removing back order";
     console.error(error.response?.data);
   }
 } else {
   // If it doesn't have an ID, it's only in the local array
   backOrders.value.splice(index, 1);
 }
 
 // Clear message after a delay
 setTimeout(() => {
   message.value = "";
 }, 3000);
};

// Validate back order quantities
const validateBackOrderQuantities = () => {
 // Ensure all quantities are positive numbers
 backOrders.value.forEach((row) => {
   if (row.quantity <= 0) row.quantity = 1;
 });
};

// Attempt to close the modal
const attemptCloseModal = () => {
 if (remainingToAllocate.value > 0 && totalBackOrderQuantity.value > 0) {
   // Show warning if there are unallocated quantities
   showIncompleteBackOrderModal.value = true;
 } else {
   // Close modal directly if everything is allocated or nothing has been entered
   showBackOrderModal.value = false;
   showIncompleteBackOrderModal.value = false;
 }
};

// Discard and close the modal
const discardAndClose = () => {
 showBackOrderModal.value = false;
 showIncompleteBackOrderModal.value = false;
};

// Save back orders
const saveBackOrders = async () => {
 // Check if there's a mismatch between back order quantity and missing quantity
 if (totalBackOrderQuantity.value !== missingQuantity.value) {
   Swal.fire({
     title: "Cannot Save",
     text: `The total back order quantity (${totalBackOrderQuantity.value}) must exactly match the missing quantity (${missingQuantity.value}).`,
     icon: "error",
     confirmButtonText: "OK",
   });
   return;
 }

 // Check if all back orders have valid data
 const invalidRows = backOrders.value.filter(
   (row) => !row.type || row.quantity <= 0
 );

 if (invalidRows.length > 0) {
   Swal.fire({
     title: "Invalid Data",
     text: "Please ensure all back orders have a type and positive quantity.",
     icon: "error",
     confirmButtonText: "OK",
   });
   return;
 }

 isSaving.value = true;

 try {
   // Prepare data for API
   const backOrderData = {
     transfer_item_id: selectedItem.value.id,
     received_quantity: selectedItem.value.received_quantity,
     backorders: backOrders.value,
   };

   // Send to API
   const response = await axios.post(route("transfers.backorder"), backOrderData);

   // Handle success
   Swal.fire({
     title: "Success!",
     text: "Back orders have been saved successfully.",
     icon: "success",
     toast: true,
     position: "top-end",
     showConfirmButton: false,
     timer: 3000,
   });

   // Close modal and refresh data
   showBackOrderModal.value = false;
   // Reload the page to get updated data
   window.location.reload();
 } catch (error) {
   console.error(error);
   Swal.fire({
     title: "Error!",
     text: error.response?.data || "Failed to save back orders",
     icon: "error",
     toast: true,
     position: "top-end",
     showConfirmButton: false,
     timer: 3000,
   });
 } finally {
   isSaving.value = false;
 }
};

const isDeleting = ref([]);
function confirmDelete(id){
 Swal.fire({
   title: "Confirm Delete",
   text: "Are you sure you want to delete this transfer item? This action cannot be undone.",
   icon: "warning",
   showCancelButton: true,
   confirmButtonColor: "#3085d6",
   cancelButtonColor: "#d33",
   confirmButtonText: "Yes, delete it!",
 }).then(async (result) => {
   if (result.isConfirmed) {
     isDeleting.value[id] = true;
       // Call the delete endpoint
       await axios.get(route("transfers.items.destroy", id))
         .then((response) => {
           isDeleting.value[id] = false;
           console.log(response.data);
           Swal.fire({
             title: "Success!",
             text: "Transfer item deleted successfully!",
             icon: "success",
             confirmButtonText: "OK",
           }).then(() => {
             // Reload the page to show updated status and inventory
             window.location.reload();
           });
         })
         .catch((error) => {
           isDeleting.value[id] = false;
           console.error(error);
           Swal.fire({
             title: "Error!",
             text: error.response?.data || "Failed to delete transfer item",
             icon: "error",
             confirmButtonText: "OK",
           });
         });    
   }
 });
}

// Computed properties for back orders
const missingQuantity = computed(() => {
 if (!selectedItem.value) return 0;
 return Math.max(
   0,
   selectedItem.value.quantity - (selectedItem.value.received_quantity || 0)
 );
});

const totalBackOrderQuantity = computed(() => {
 return backOrders.value.reduce(
   (total, row) => total + Number(row.quantity || 0),
   0
 );
});

const totalExistingDifferences = computed(() => {
 if (!selectedItem.value || !selectedItem.value.backorders) return 0;
 return selectedItem.value.backorders.reduce(
   (total, bo) => total + Number(bo.quantity || 0),
   0
 );
});

const remainingToAllocate = computed(() => {
 return missingQuantity.value - totalBackOrderQuantity.value;
});

const canAddMoreBackOrders = computed(() => {
 return remainingToAllocate.value > 0;
});

const isValidForSave = computed(() => {
 // Check if we have any back orders
 const hasBackOrders = backOrders.value.length > 0;

 // Check if all back orders have valid data
 const allValid = backOrders.value.every((row) => row.quantity > 0 && row.type);

 // Check if total matches the missing quantity
 const totalMatches = totalBackOrderQuantity.value === missingQuantity.value;

 return hasBackOrders && allValid && totalMatches;
});
const error = ref([]);
// Function to receive transfer and update inventory
const receiveTransfer = (transferId) => {
 // Clear previous errors
 error.value = [];
 
 Swal.fire({
   title: "Confirm Receipt",
   text: "Are you sure you want to receive this transfer? This will update inventory quantities.",
   icon: "warning",
   showCancelButton: true,
   confirmButtonColor: "#3085d6",
   cancelButtonColor: "#d33",
   confirmButtonText: "Yes, receive it!",
 }).then(async (result) => {
   if (result.isConfirmed) {
     // Set loading state
     isLoading.value = true;
     
     try {
       // Call the receiveTransfer endpoint
       const response = await axios.post(route("transfers.receiveTransfer"), {
         transfer_id: transferId,
         status: "received",
         items: props.transfer.items
       });
       
       console.log(response.data);
       isLoading.value = false;
       
       // Show success message with details
       Swal.fire({
         title: "Success!",
         text: "Transfer received successfully!",
         icon: "success",
         confirmButtonText: "OK",
       }).then(() => {
         // Reload the page to show updated status and inventory
         router.reload();
       });
     } catch (err) {
       isLoading.value = false;
       
       // Handle item-specific errors
       if (err.response?.data?.id) {
         // Create a new object to ensure reactivity
         const newErrors = {...error.value};
         newErrors[err.response.data.id] = err.response.data.message;
         error.value = newErrors;
         
         Swal.fire({
           title: "Validation Error",
           text: err.response.data.message,
           icon: "error",
           confirmButtonText: "OK",
         });
       } else {
         // Handle general errors
         Swal.fire({
           title: "Error!",
           text: err.response?.data || "Failed to receive transfer",
           icon: "error",
           confirmButtonText: "OK",
         });
       }
     }
   }
 });
};

// Function to change transfer status
const changeStatus = (transferId, newStatus) => {
  console.log(transferId, newStatus);
  
   Swal.fire({
       title: "Are you sure?",
       text: `Do you want to change the transfer status to ${newStatus}?`,
       icon: "warning",
       showCancelButton: true,
       confirmButtonColor: "#3085d6",
       cancelButtonColor: "#d33",
       confirmButtonText: "Yes, change it!",
   }).then(async (result) => {
       if (result.isConfirmed) {
           isLoading.value = true;

           await axios
               .post(route("transfers.changeItemStatus"), {
                   transfer_id: transferId,
                   status: newStatus,
               })
               .then((response) => {
                   isLoading.value = false;
                   console.log(response.data);
                   Swal.fire({
                       title: "Updated!",
                       text: response.data || "Transfer status updated successfully",
                       icon: "success",
                       timer: 3000,
                   }).then(() => {
                       // Reload the page to show the updated status
                       router.reload();
                   });
               })
               .catch((error) => {
                   console.log(error.response);
                   isLoading.value = false;

                   Swal.fire({
                       title: "Error!",
                       text:
                           error.response.data ||
                           "Failed to update transfer status",
                       icon: "error",
                       timer: 3000,
                   });
               });
       }
   });
};

const props = defineProps({
 transfer: {
   type: Object,
   required: true
 },
 error: String
});

// Format date function
const formatDate = (date) => {
 if (!date) return 'N/A';
 return moment(date).format('DD/MM/YYYY');
};

// Validate received quantity
const validateReceivedQuantity = (item) => {
 // Ensure received quantity doesn't exceed the original quantity
 if (Number(item.received_quantity) > Number(item.quantity)) {
   item.received_quantity = item.quantity;
 }
 
 // Ensure received quantity is not negative
 if (Number(item.received_quantity) < 0) {
   item.received_quantity = 0;
 }
};

// Define the status order for the timeline
const statusOrder = ['pending', 'approved', 'in_process', 'dispatched', 'received'];

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

// Listen for transfer status changes via Echo
onMounted(() => {
  // Only set up the listener if Echo is available
  if (window.Echo) {
    window.Echo.private(`transfer.${props.transfer.id}`)
      .listen('.TransferStatusChanged', (e) => {
        console.log('Transfer status changed event received:', e);
        
        // Show a toast notification
        toast.info(`Transfer status changed from ${e.oldStatus} to ${e.newStatus}`);
        
        // Update the transfer status in the UI
        if (props.transfer.status !== e.newStatus) {
          props.transfer.status = e.newStatus;
          
          // Reload the page to get the latest data
          setTimeout(() => {
            router.reload();
          }, 2000);
        }
      });
  }
});

// Clean up the listener when component is unmounted
onUnmounted(() => {
  if (window.Echo) {
    window.Echo.leave(`transfer.${props.transfer.id}`);
  }
});
</script>