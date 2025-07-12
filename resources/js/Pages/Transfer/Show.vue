<template>
    <AuthenticatedLayout title="Transfer Details" description="Transfer Details" img="/assets/images/transfer.png">
        <div class="container mx-auto">
            <!-- Transfer Header -->
            <div class="mb-6 bg-white rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Transfer Details
                    </h1>
                    <div class="flex items-center space-x-4">
                        <span :class="[
                            statusClasses[props.transfer.status] ||
                            statusClasses.default,
                        ]" class="flex items-center text-xs font-bold px-4 py-2">
                            <!-- Status Icon -->
                            <span class="mr-3">
                                <!-- Pending Icon -->
                                <img v-if="props.transfer.status === 'pending'" src="/assets/images/pending.png"
                                    class="w-4 h-4" alt="Pending" />

                                <!-- reviewed Icon -->
                                <img v-else-if="
                                    props.transfer.status === 'reviewed'
                                " src="/assets/images/review.png" class="w-4 h-4" alt="Reviewed" />

                                <!-- Approved Icon -->
                                <img v-else-if="
                                    props.transfer.status === 'approved'
                                " src="/assets/images/approved.png" class="w-4 h-4" alt="Approved" />

                                <!-- In Process Icon -->
                                <img v-else-if="
                                    props.transfer.status === 'in_process'
                                " src="/assets/images/inprocess.png" class="w-4 h-4" alt="In Process" />

                                <!-- Dispatched Icon -->
                                <img v-else-if="
                                    props.transfer.status === 'dispatched'
                                " src="/assets/images/dispatch.png" class="w-4 h-4" alt="Dispatched" />

                                <!-- Received Icon -->
                                <img v-else-if="
                                    props.transfer.status === 'received'
                                " src="/assets/images/received.png" class="w-4 h-4" alt="Received" />

                                <!-- Rejected Icon -->
                                <img v-else-if="props.transfer.status === 'rejected'" src="/assets/images/rejected.png"
                                    class="w-4 h-4" alt="Rejected" />
                            </span>
                            {{ props.transfer.status.toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Transfer ID, Date, and Type -->
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Transfer ID:</span>
                            <span class="ml-2 font-semibold">#{{ props.transfer.transferID }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Transfer Date:</span>
                            <span class="ml-2 font-semibold">{{
                                moment(props.transfer.transfer_date).format(
                                    "DD/MM/YYYY"
                                )
                            }}</span>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Transfer Type:</span>
                            <span class="ml-2 font-semibold bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                {{ props.transfer.transfer_type || 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- From and To Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- From Section -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            From
                        </h3>
                        <div v-if="props.transfer.from_warehouse">
                            <p class="font-semibold text-gray-800">
                                {{ props.transfer.from_warehouse.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.from_warehouse.address }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.from_warehouse.district }},
                                {{ props.transfer.from_warehouse.region }}
                            </p>
                            <div class="mt-2 text-sm">
                                <p class="text-gray-600">
                                    Manager:
                                    <span class="font-medium">{{
                                        props.transfer.from_warehouse
                                            .manager_name
                                    }}</span>
                                </p>
                                <p class="text-gray-600">
                                    Phone:
                                    <span class="font-medium">{{
                                        props.transfer.from_warehouse
                                            .manager_phone
                                    }}</span>
                                </p>
                            </div>
                            <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                Warehouse
                            </span>
                        </div>
                        <div v-else-if="props.transfer.from_facility">
                            <p class="font-semibold text-gray-800">
                                {{ props.transfer.from_facility.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.from_facility.address }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.from_facility.district }},
                                {{ props.transfer.from_facility.region }}
                            </p>
                            <div class="mt-2 text-sm">
                                <p class="text-gray-600">
                                    Type:
                                    <span class="font-medium">{{
                                        props.transfer.from_facility
                                            .facility_type
                                    }}</span>
                                </p>
                                <p class="text-gray-600">
                                    Phone:
                                    <span class="font-medium">{{
                                        props.transfer.from_facility.phone
                                    }}</span>
                                </p>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            To
                        </h3>
                        <div v-if="props.transfer.to_warehouse">
                            <p class="font-semibold text-gray-800">
                                {{ props.transfer.to_warehouse.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.to_warehouse.address }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.to_warehouse.district }},
                                {{ props.transfer.to_warehouse.region }}
                            </p>
                            <div class="mt-2 text-sm">
                                <p class="text-gray-600">
                                    Manager:
                                    <span class="font-medium">{{
                                        props.transfer.to_warehouse.manager_name
                                    }}</span>
                                </p>
                                <p class="text-gray-600">
                                    Phone:
                                    <span class="font-medium">{{
                                        props.transfer.to_warehouse
                                            .manager_phone
                                    }}</span>
                                </p>
                            </div>
                            <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                Warehouse
                            </span>
                        </div>
                        <div v-else-if="props.transfer.to_facility">
                            <p class="font-semibold text-gray-800">
                                {{ props.transfer.to_facility.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.to_facility.address }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ props.transfer.to_facility.district }},
                                {{ props.transfer.to_facility.region }}
                            </p>
                            <div class="mt-2 text-sm">
                                <p class="text-gray-600">
                                    Type:
                                    <span class="font-medium">{{
                                        props.transfer.to_facility.facility_type
                                    }}</span>
                                </p>
                                <p class="text-gray-600">
                                    Phone:
                                    <span class="font-medium">{{
                                        props.transfer.to_facility.phone
                                    }}</span>
                                </p>
                            </div>
                            <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                Facility
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status Stage Timeline -->
                <div v-if="props.transfer.status == 'rejected'">
                    <div class="flex flex-col items-center mt-3">
                        <div
                            class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10 bg-white border-red-500">
                            <img src="/assets/images/rejected.png" class="w-7 h-7" alt="Rejected" />
                        </div>
                        <h1 class="mt-3 text-2xl text-red-600 font-bold ">Rejected</h1>
                    </div>
                </div>
                <div v-else class="col-span-2 mb-6 mt-3">
                    <div class="relative">
                        <!-- Timeline Track Background -->
                        <div class="absolute top-7 left-0 right-0 h-2 bg-gray-200 z-0"></div>

                        <!-- Timeline Progress -->
                        <div class="absolute top-7 left-0 h-2 bg-green-500 z-0 transition-all duration-500 ease-in-out"
                            :style="{
                                width: `${(statusOrder.indexOf(props.transfer.status) /
                                    (statusOrder.length - 1)) *
                                    100
                                    }%`,
                            }"></div>

                        <!-- Timeline Steps -->
                        <div class="relative flex justify-between">
                            <!-- Pending -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('pending')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/pending.png" class="w-7 h-7" alt="Pending" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('pending')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('pending')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Pending</span>
                            </div>

                            <!-- Reviewed -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('reviewed')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/review.png" class="w-7 h-7" alt="Reviewed" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('reviewed')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('reviewed')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Reviewed</span>
                            </div>

                            <!-- Approved -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('approved')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/approved.png" class="w-7 h-7" alt="Approved" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('approved')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('approved')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Approved</span>
                            </div>

                            <!-- In Process -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('in_process')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/inprocess.png" class="w-7 h-7" alt="In Process" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('in_process')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('in_process')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">In Process</span>
                            </div>

                            <!-- Dispatch -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('dispatched')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/dispatch.png" class="w-7 h-7" alt="Dispatch" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('dispatched')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('dispatched')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Dispatched</span>
                            </div>

                            <!-- Delivered -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('delivered')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/delivery.png" class="w-7 h-7" alt="Dispatch" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('delivered')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('delivered')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Delivered</span>
                            </div>

                            <!-- Received -->
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(props.transfer.status) >=
                                            statusOrder.indexOf('received')
                                            ? 'bg-white border-green-500'
                                            : 'bg-white border-gray-200',
                                    ]">
                                    <img src="/assets/images/received.png" class="w-7 h-7" alt="Received" :class="statusOrder.indexOf(props.transfer.status) >=
                                        statusOrder.indexOf('received')
                                        ? ''
                                        : 'opacity-40'
                                        " />
                                </div>
                                <span class="mt-3 text-xs font-bold" :class="statusOrder.indexOf(props.transfer.status) >=
                                    statusOrder.indexOf('received')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                                    ">Received</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Items Table -->
                <div class="mb-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">Transfer Items</h3>
                            <p class="text-gray-600 text-sm">Detailed breakdown of items being transferred</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-left table-sm">
                                <thead>
                                    <tr style="background-color: #F4F7FB;">
                                        <th class="min-w-[300px] px-3 py-2 text-xs font-bold rounded-tl-lg" style="color: #4F6FCB;" rowspan="2">
                                            Item Name
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB;" rowspan="2">
                                            Category
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB;" rowspan="2">
                                            UoM
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold text-center" style="color: #4F6FCB;" colspan="4">
                                            Item Details
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB;" rowspan="2">
                                            Total Quantity on Hand Per Unit
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB;" rowspan="2">
                                            Transfer Reason
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold" style="color: #4F6FCB;" rowspan="2">
                                            Quantity to Transfer
                                        </th>
                                        <th class="px-3 py-2 text-xs font-bold rounded-tr-lg" style="color: #4F6FCB;" rowspan="2">
                                            Received Quantity
                                        </th>
                                    </tr>
                                    <tr style="background-color: #F4F7FB;">
                                        <th class="px-2 py-1 text-xs font-bold text-center" style="color: #4F6FCB;">
                                            QTY
                                        </th>
                                        <th class="px-2 py-1 text-xs font-bold text-center" style="color: #4F6FCB;">
                                            Batch Number
                                        </th>
                                        <th class="px-2 py-1 text-xs font-bold text-center" style="color: #4F6FCB;">
                                            Expiry Date
                                        </th>
                                        <th class="px-2 py-1 text-xs font-bold text-center" style="color: #4F6FCB;">
                                            Location
                                        </th>
                                    </tr>
                                </thead>

                            <tbody>
                                <template v-for="(item, index) in form" :key="item.id">
                        <!-- Show allocations if they exist, otherwise show one row with main item data -->
                        <tr v-for="(allocation, allocIndex) in (item.inventory_allocations?.length > 0 ? item.inventory_allocations : [{}])"
                                        :key="`${item.id}-${allocIndex}`"
                                        class="hover:bg-gray-50 transition-colors duration-150 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                        <!-- Item Name -->
                                        <td v-if="allocIndex === 0" :rowspan="item.inventory_allocations?.length || 1"
                                            class="px-3 py-2 text-xs font-medium text-gray-800 align-top items-center">
                                            {{ item.product?.name || "N/A" }}
                                        </td>

                                        <!-- Category -->
                                        <td v-if="allocIndex === 0" :rowspan="item.inventory_allocations?.length || 1"
                                            class="px-3 py-2 text-xs text-gray-700 align-top items-center">
                                            {{
                                                item.product?.category?.name ||
                                                "N/A"
                                            }}
                                        </td>

                                        <!-- UoM Column -->
                                        <td v-if="allocIndex === 0" :rowspan="item.inventory_allocations?.length || 1"
                                            class="px-3 py-2 text-xs text-gray-700 align-top items-center">
                                            {{ item.inventory_allocations?.[0]?.uom || "N/A" }}
                                        </td>

                                        <!-- Quantity -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            {{
                                                allocation.allocated_quantity ||
                                                0
                                            }}
                                        </td>

                                        <!-- Batch Number -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            {{
                                                allocation.batch_number || "N/A"
                                            }}
                                        </td>

                                        <!-- Expiry Date -->
                                        <td class="px-2 py-1 text-xs border-b text-center">
                                            <span :class="{
                                                'text-red-600':
                                                    isExpiringItem(
                                                        allocation.expiry_date
                                                    ),
                                            }">
                                                {{
                                                    moment(
                                                        allocation.expiry_date
                                                    ).format("DD/MM/YYYY")
                                                }}
                                            </span>
                                        </td>

                                        <!-- Location -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            {{ allocation.location || "N/A" }}
                                        </td>

                                        <!-- Total Quantity per Unit -->
                                        <td v-if="allocIndex === 0" :rowspan="item.inventory_allocations?.length || 1"
                                            class="px-3 py-2 text-xs text-gray-800 align-top items-center">
                                            {{ item.quantity_per_unit || 0 }}
                                        </td>

                                        <!-- Transfer Reason - per allocation -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            {{ allocation.transfer_reason || "N/A" }}
                                        </td>

                                        <!-- Quantity to Transfer - per allocation -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="font-medium">{{ allocation.allocated_quantity || 0 }}</span>
                                                <!-- v-if="props.transfer.status === 'pending' && allocation.id" -->
                                                <input 
                                                    :readonly="props.transfer.status === 'approved'"
                                                    type="number" 
                                                    v-model="allocation.updated_quantity"
                                                    :placeholder="allocation.allocated_quantity || 0"
                                                    min="1"
                                                    class="w-full text-center border border-gray-300 rounded px-1 py-1 text-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                                                    @input="handleQuantityInput($event, allocation)"
                                                />
                                                <span class="text-xs text-gray-500" v-if="isUpdatingQuantity[allocation.id]">
                                                    Updating...
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Received Quantity - per allocation -->
                                        <td class="px-2 py-1 text-xs border-b text-center text-gray-900">
                                            <div class="flex flex-col items-center">
                                                <input 
                                                    type="number" 
                                                    v-model="allocation.received_quantity" 
                                                    :readonly="props.transfer.to_warehouse_id == null || props.transfer.status == 'received'"
                                                    :max="getMaxReceivedQuantity(allocation)"
                                                    min="0"
                                                    @input="validateAllocationReceivedQuantity(allocation, allocIndex)"
                                                    class="w-20 text-center border border-gray-300 rounded px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500" 
                                                />
                                                <span v-if="isReceived[allocIndex]" class="text-xs text-gray-500">Updating...</span>
                                                <span v-if="allocation.differences && allocation.differences.length > 0" class="text-xs text-orange-600">
                                                    Max: {{ getMaxReceivedQuantity(allocation) }}
                                                </span>
                                                <div v-if="allocation.differences && allocation.differences.length > 0" class="mt-1 p-1 bg-orange-50 border border-orange-200 rounded text-xs">
                                                    <span class="text-orange-700 font-medium">
                                                        {{ allocation.differences.length }} difference(s) recorded
                                                    </span>
                                                    <br>
                                                    <span class="text-orange-600">
                                                        Total: {{ allocation.differences.reduce((sum, diff) => sum + (diff.quantity || 0), 0) }} items
                                                    </span>
                                                </div>
                                                <button 
                                                    v-if="(allocation.allocated_quantity || 0) > (allocation.received_quantity || 0)"
                                                    @click="openBackOrderModal(item, allocation)"
                                                    class="text-xs text-orange-600 underline hover:text-orange-800 cursor-pointer mt-1 block">
                                                    Back Order
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                                                            </table>
                                </div>
                            </div>
                        </div>
            </div>

            <!-- dispatch information -->
            <div v-if="props.transfer.dispatch?.length > 0" 
                class="mt-8 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Dispatch Information
                    </h2>
                </div>

                <div class="bg-white rounded-lg divide-y divide-gray-200">
                    <div v-for="dispatch in props.transfer.dispatch" :key="dispatch.id" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Driver & Company Info -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Driver Information</h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <UserIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-900">{{ dispatch.driver.name }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <IdentificationIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">ID: {{ dispatch.driver.driver_id
                                                }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <PhoneIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">{{ dispatch.driver_number }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Logistics Company</h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <BuildingOfficeIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-900">{{ dispatch.logistic_company.name
                                                }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <EnvelopeIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">{{ dispatch.logistic_company.email
                                                }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <UserIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">Contact: {{
                                                dispatch.logistic_company.incharge_person }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <PhoneIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">{{
                                                dispatch.logistic_company.incharge_phone
                                                }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dispatch Details -->
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-500">Dispatch Details</h3>
                                    <div class="mt-2 space-y-2">
                                        <div class="flex items-center">
                                            <CalendarIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-900">{{
                                                moment(dispatch.dispatch_date).format('DD/MMM/YYYY') }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <TruckIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">Vehicle Plate: {{ dispatch.plate_number
                                                }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <ArchiveBoxIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">{{ dispatch.no_of_cartoons }}
                                                Cartons</span>
                                        </div>
                                        <div class="flex items-center">
                                            <ClockIcon class="w-4 h-4 text-gray-400 mr-2" />
                                            <span class="text-sm text-gray-600">Dispatched on {{
                                                moment(dispatch.created_at).format('MMMM D, YYYY h:mm A') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Dispatch Information -->
            <div class="p-4 bg-white rounded-lg"
                v-if="props.transfer.status === 'dispatched' && props.transfer.dispatch_info?.length > 0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Dispatch Information
                    </h2>
                </div>

                <div v-for="dispatch in props.transfer.dispatch_info" :key="dispatch.id" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Driver Info -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Driver Information</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <UserIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.driver.name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <PhoneIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.driver.phone }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <TruckIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.plate_number }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Info -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">Logistics Company</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <BuildingOfficeIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.logistic_company.name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <UserIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.logistic_company.incharge_person
                                            }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <PhoneIcon class="w-5 h-5 text-gray-400 mr-2" />
                                        <span class="text-gray-900">{{ dispatch.logistic_company.incharge_phone
                                            }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Dispatch Date</span>
                                <div class="flex items-center mt-1">
                                    <CalendarIcon class="w-5 h-5 text-gray-400 mr-2" />
                                    <span class="text-gray-900">{{ moment(dispatch.dispatch_date).format('DD/MM/YYYY')
                                        }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Number of Cartons</span>
                                <div class="flex items-center mt-1">
                                    <CubeIcon class="w-5 h-5 text-gray-400 mr-2" />
                                    <span class="text-gray-900">{{ dispatch.no_of_cartoons }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Created At</span>
                                <div class="flex items-center mt-1">
                                    <ClockIcon class="w-5 h-5 text-gray-400 mr-2" />
                                    <span class="text-gray-900">{{ moment(dispatch.created_at).format('DD/MM/YYYY HH: mm')
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer actions -->
            <div class="mt-8 mb-[80px] bg-white rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Transfer Status Actions
                </h3>
                <div class="flex items-start mb-6">
                    <!-- Status Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center gap-4 px-1 py-2">
                        <!-- Pending status indicator removed by the Dr Mutax -->
                        <!-- Review button -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button @click="
                                    changeStatus(
                                        props.transfer.id,
                                        'reviewed',
                                        'is_reviewing'
                                    )
                                    " :disabled="isType['is_reviewing'] ||
                                    props.transfer.status !== 'pending' ||
                                    !canReview
                                    " :class="[
                                        props.transfer.status === 'pending'
                                            ? 'bg-yellow-500 hover:bg-yellow-600'
                                            : statusOrder.indexOf(
                                                props.transfer.status
                                            ) > statusOrder.indexOf('pending')
                                                ? 'bg-green-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <img src="/assets/images/review.png" class="w-5 h-5 mr-2" alt="Review" />
                                    <span class="text-sm font-bold text-white">{{
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) > statusOrder.indexOf("pending")
                                            ? "Reviewed"
                                            : isType["is_reviewing"]
                                                ? "Please Wait..."
                                                : props.transfer.status ===
                                                    "pending" && !canReview
                                                    ? "Waiting to be reviewed"
                                                    : "Review"
                                    }}</span>
                                </button>
                                <span v-show="props.transfer?.reviewed_at" class="text-sm text-gray-600">
                                    On {{ moment(props.transfer?.reviewed_at).format("DD/MM/YYYY HH:mm") }}
                                </span>
                                <span v-show="props.transfer?.reviewed_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.reviewed_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.transfer.status === 'pending'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Approved button -->
                        <div class="relative" v-if="props.transfer.status !== 'rejected'">
                            <div class="flex flex-col">
                                <button @click="
                                    changeStatus(
                                        props.transfer.id,
                                        'approved',
                                        'is_approve'
                                    )
                                    " :disabled="isType['is_approve'] ||
                                    props.transfer.status !== 'reviewed' ||
                                    !canApprove
                                    " :class="[
                                        props.transfer.status == 'reviewed'
                                            ? 'bg-yellow-500 hover:bg-yellow-600'
                                            : statusOrder.indexOf(
                                                props.transfer.status
                                            ) >
                                                statusOrder.indexOf('reviewed')
                                                ? 'bg-green-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="
                                        isLoading &&
                                        props.transfer.status === 'reviewed'
                                    " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <template v-else>
                                        <img src="/assets/images/approved.png" class="w-5 h-5 mr-2" alt="Approve" />
                                        <span class="text-sm font-bold text-white">{{
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >
                                                statusOrder.indexOf("reviewed")
                                                ? "Approved"
                                                : isType["is_approve"]
                                                    ? "Please Wait..."
                                                    : props.transfer.status ===
                                                        "reviewed" &&
                                                        !canApprove
                                                        ? "Waiting to be approved"
                                                        : "Approve"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.approved_at" class="text-sm text-gray-600">
                                    On {{ moment(props.transfer?.approved_at).format("DD/MM/YYYY HH:mm") }}
                                </span>
                                <span v-show="props.transfer?.approved_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.approved_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.transfer.status === 'reviewed'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Process button -->
                        <div class="relative" v-if="props.transfer.status !== 'rejected'">
                            <div class="flex flex-col">
                                <button @click="
                                    changeStatus(
                                        props.transfer.id,
                                        'in_process',
                                        'is_process'
                                    )
                                    " :disabled="isType['is_process'] ||
                                    props.transfer.status !== 'approved' ||
                                    !canDispatch
                                    " :class="[
                                        props.transfer.status === 'approved'
                                            ? 'bg-yellow-500 hover:bg-yellow-600'
                                            : statusOrder.indexOf(
                                                props.transfer.status
                                            ) >
                                                statusOrder.indexOf('approved')
                                                ? 'bg-green-500'
                                                : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="
                                        isType['is_process'] &&
                                        props.transfer.status == 'approved'
                                    " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <template v-else>
                                        <img src="/assets/images/inprocess.png" class="w-5 h-5 mr-2" alt="Process" />
                                        <span class="text-sm font-bold text-white">{{
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >
                                                statusOrder.indexOf("approved")
                                                ? "Processed"
                                                : isType["is_process"]
                                                    ? "Please Wait..."
                                                    : props.transfer.status ===
                                                        "approved" &&
                                                        !canDispatch
                                                        ? "Waiting to be processed"
                                                        : "Process"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.processed_at" class="text-sm text-gray-600">
                                    On {{ moment(props.transfer?.processed_at).format("DD/MM/YYYY HH:mm") }}
                                </span>
                                <span v-show="props.transfer?.processed_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.processed_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.transfer.status === 'approved'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Dispatch button -->
                        <div class="relative" v-if="props.transfer.status !== 'rejected'">
                            <div class="flex flex-col">
                                <button @click="showDispatchForm = true" :disabled="isType['is_dispatch'] ||
                                    props.transfer.status !==
                                    'in_process' ||
                                    !canDispatch
                                    " :class="[
                                    props.transfer.status === 'in_process'
                                        ? 'bg-yellow-500 hover:bg-yellow-600'
                                        : statusOrder.indexOf(
                                            props.transfer.status
                                        ) >
                                            statusOrder.indexOf('in_process')
                                            ? 'bg-green-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="
                                        isType['is_dispatch'] &&
                                        props.transfer.status ===
                                        'in_process'
                                    " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <template v-else>
                                        <img src="/assets/images/dispatch.png" class="w-5 h-5 mr-2" alt="Dispatch" />
                                        <span class="text-sm font-bold text-white">{{
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >
                                                statusOrder.indexOf(
                                                    "in_process"
                                                )
                                                ? "Dispatched"
                                                : isType["is_dispatch"]
                                                    ? "Please Wait..."
                                                    : props.transfer.status ===
                                                        "in_process" &&
                                                        !canDispatch
                                                        ? "Waiting to be dispatched"
                                                        : "Dispatch"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.dispatched_at" class="text-sm text-gray-600">
                                    On {{ moment(props.transfer?.dispatched_at).format("DD/MM/YYYY HH:mm") }}
                                </span>
                                <span v-show="props.transfer?.dispatched_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.dispatched_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.transfer.status === 'in_process'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                        <!-- Order Delivery Indicators -->
                        <div class="flex flex-col gap-4 sm:flex-row" v-if="props.transfer.status !== 'rejected'">
                            <!-- Delivered Status -->
                            <div class="relative">
                                <div class="flex flex-col">
                                    <button @click="
                                        changeStatus(
                                            props.transfer.id,
                                            'delivered',
                                            'is_deliver'
                                        )
                                        " :disabled="isType['is_deliver'] ||
                                        props.transfer.status !==
                                        'dispatched' ||
                                        !canReceive
                                        " :class="[
                                            props.transfer.status ===
                                                'dispatched'
                                                ? 'bg-yellow-500 hover:bg-yellow-600'
                                                : statusOrder.indexOf(
                                                    props.transfer.status
                                                ) >
                                                    statusOrder.indexOf(
                                                        'dispatched'
                                                    )
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300 cursor-not-allowed',
                                        ]"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                        <svg v-if="
                                            isType['is_deliver'] &&
                                            props.transfer.status ===
                                            'dispatched'
                                        " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <template v-else>
                                            <img src="/assets/images/delivery.png" class="w-5 h-5 mr-2"
                                                alt="Delivered" />
                                            <span class="text-sm font-bold text-white">
                                                {{
                                                    statusOrder.indexOf(
                                                        props.transfer.status
                                                    ) >
                                                        statusOrder.indexOf(
                                                            "dispatched"
                                                        )
                                                        ? "Delivered"
                                                        : isType["is_deliver"]
                                                            ? "Please Wait..."
                                                            : props.transfer
                                                                .status ===
                                                                "dispatched" &&
                                                                !canReceive
                                                                ? "Waiting to be delivered"
                                                                : "Deliver"
                                                }}
                                            </span>
                                        </template>
                                    </button>
                                    <span v-show="props.transfer?.delivered_at" class="text-sm text-gray-600">
                                        On {{ moment(props.transfer?.delivered_at).format("DD/MM/YYYY HH:mm") }}
                                    </span>
                                    <span v-show="props.transfer?.delivered_by" class="text-sm text-gray-600">
                                        By
                                        {{ props.transfer?.delivered_by?.name }}
                                    </span>
                                </div>

                                <!-- Pulse Indicator if currently at this status -->
                                <div v-if="
                                    props.transfer.status === 'dispatched'
                                " class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse">
                                </div>
                            </div>

                            <!-- Received Status -->
                            <div class="relative">
                                <div class="flex flex-col">
                                    <button @click="
                                        changeStatus(
                                            props.transfer.id,
                                            'received',
                                            'is_receive'
                                        )
                                        " :disabled="isType['is_receive'] ||
                                        props.transfer.status !==
                                        'delivered' ||
                                        !canReceive
                                        " :class="[
                                            props.transfer.status ===
                                                'delivered'
                                                ? 'bg-yellow-500 hover:bg-yellow-600'
                                                : statusOrder.indexOf(
                                                    props.transfer.status
                                                ) >
                                                    statusOrder.indexOf(
                                                        'delivered'
                                                    )
                                                    ? 'bg-green-500'
                                                    : 'bg-gray-300 cursor-not-allowed',
                                        ]"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                        <svg v-if="
                                            isType['is_receive'] &&
                                            props.transfer.status ===
                                            'delivered'
                                        " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <template v-else>
                                            <img src="/assets/images/received.png" class="w-5 h-5 mr-2"
                                                alt="Received" />
                                            <span class="text-sm font-bold text-white">
                                                {{
                                                    statusOrder.indexOf(
                                                        props.transfer.status
                                                    ) >
                                                        statusOrder.indexOf(
                                                            "delivered"
                                                        )
                                                        ? "Received"
                                                        : isType["is_receive"]
                                                            ? "Please Wait..."
                                                            : props.transfer
                                                                .status ===
                                                                "delivered" &&
                                                                !canReceive
                                                                ? "Waiting to be received"
                                                                : "Receive"
                                                }}
                                            </span>
                                        </template>
                                    </button>
                                    <span v-show="props.transfer?.received_at" class="text-sm text-gray-600">
                                        On {{ moment(props.transfer?.received_at).format("DD/MM/YYYY HH:mm") }}
                                    </span>
                                    <span v-show="props.transfer?.received_by" class="text-sm text-gray-600">
                                        By
                                        {{ props.transfer?.received_by?.name }}
                                    </span>
                                </div>

                                <!-- Pulse Indicator if currently at this status -->
                                <div v-if="props.transfer.status === 'delivered'"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse">
                                </div>
                            </div>
                        </div>

                        <!-- Rejected button -->
                        <div class="relative" v-if="props.transfer.status == 'reviewed' || props.transfer.status == 'rejected'">
                            <div class="flex flex-col">
                                <button @click="
                                    changeStatus(
                                        props.transfer.id,
                                        'rejected',
                                        'is_reject'
                                    )
                                    " :disabled="isType['is_reject'] ||
                                        props.transfer.status !== 'reviewed'
                                        " :class="[
                                    props.transfer.status == 'reviewed'
                                        ? 'bg-red-500 hover:bg-red-600'
                                        : statusOrder.indexOf(props.transfer.status) >
                                            statusOrder.indexOf('reviewed')
                                            ? 'bg-red-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                ]" class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]">
                                    <svg v-if="
                                        isLoading &&
                                        props.transfer.status === 'reviewed'
                                    " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <template v-else>
                                        <img src="/assets/images/rejected.png" class="w-5 h-5 mr-2" alt="Rejected" />
                                        <span class="text-sm font-bold text-white">{{
                                            props.transfer.status == 'rejected'
                                                ? "Rejected"
                                                : isType["is_reject"] ? "Please Wait..." : "Reject"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.rejected_at" class="text-sm text-gray-600">
                                    On {{ moment(props.transfer?.rejected_at).format('DD/MM/YYYY HH:mm') }}
                                </span>
                                <span v-show="props.transfer?.rejected_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.rejected_by?.name }}
                                </span>
                            </div>
                            <div v-if="props.transfer.status === 'reviewed'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>

                         <!-- Restore button -->
                         <div class="relative" v-if="props.transfer.status === 'rejected'">
                            <div class="flex flex-col">
                                <button @click="
                                    restoreTransfer(
                                        props.transfer.id,
                                        'reviewed',
                                        'is_restore'
                                    )
                                    " :disabled="isRestoring ||
                                        props.transfer.status !== 'rejected'
                                        " class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px] bg-green-500">
                                    <svg v-if="
                                        isLoading &&
                                        props.transfer.status === 'rejected'
                                    " class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <template v-else>
                                        <img src="/assets/images/restore.jpg" class="w-5 h-5 mr-2" alt="Restore" />
                                        <span class="text-sm font-bold text-white">{{ isRestoring ? "Restoring..." : "Restore Transfer"  }}</span>
                                    </template>
                                </button>
                            </div>
                            <div v-if="props.transfer.status === 'rejected'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back Order Modal -->
            <div v-if="showBackOrderModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl mx-4 max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Back Order Details - Transfer #{{
                                props.transfer.transferID
                            }}
                        </h2>
                        <button @click="attemptCloseModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors duration-150">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6">
                        <!-- Product Information -->
                        <div v-if="selectedItem" class="mb-6 bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Product
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ selectedItem.product?.name }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Product ID
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{
                                            selectedItem.product?.productID
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Quantity to Release
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{
                                            selectedItem.quantity_to_release
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Received Quantity
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{
                                            selectedItem.received_quantity ||
                                            0
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Missing Quantity
                                    </p>
                                    <p class="text-sm font-bold text-red-600">
                                        {{ missingQuantity }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        Total Allocated
                                    </p>
                                    <p class="text-sm text-gray-900">
                                        {{ totalBatchBackOrderQuantity }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Single Batch Backorder Recording -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Record Missing Items for Batch {{ selectedAllocation.batch_number }}
                            </h3>

                            <!-- Error Message -->
                            <div v-if="message" class="mb-4 bg-red-50 border border-red-200 text-red-600 p-4 rounded">
                                {{ message }}
                            </div>

                            <!-- Batch Info -->
                            <div class="bg-gray-100 p-3 rounded-lg mb-3">
                                <h4 class="font-medium text-gray-800">
                                    Batch: {{ selectedAllocation.batch_number }}
                                    (Allocated: {{ selectedAllocation.allocated_quantity }}, 
                                    Received: {{ selectedAllocation.received_quantity || 0 }}, 
                                    Missing: {{ selectedAllocation.allocated_quantity - (selectedAllocation.received_quantity || 0) }})
                                </h4>
                            </div>

                            <!-- Recorded Differences Summary -->
                            <div v-if="selectedAllocation.differences && selectedAllocation.differences.length > 0" class="bg-blue-50 border border-blue-200 p-3 rounded-lg mb-3">
                                <h4 class="font-medium text-blue-800 mb-2">Recorded Differences Summary</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-600 font-medium">Total Recorded:</span>
                                        <span class="ml-2 font-bold text-blue-800">
                                            {{ selectedAllocation.differences.reduce((sum, diff) => sum + (diff.quantity || 0), 0) }} items
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-blue-600 font-medium">Missing Quantity:</span>
                                        <span class="ml-2 font-bold text-red-600">
                                            {{ selectedAllocation.allocated_quantity - (selectedAllocation.received_quantity || 0) }} items
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-blue-600">
                                    💡 {{ selectedAllocation.differences.length }} difference(s) recorded for this batch
                                </div>
                            </div>



                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Quantity
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Status
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Note
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(row, index) in batchBackOrders" :key="index" :class="row.isExisting ? 'bg-blue-50' : ''">
                                            <td class="px-3 py-2">
                                                <div class="flex items-center space-x-2">
                                                    <input type="number" v-model="row.quantity"
                                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                        min="1" @input="validateBatchBackOrderQuantity(row, selectedAllocation)" />
                                                    <span v-if="row.isExisting" class="text-xs text-blue-600 font-medium">Existing</span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <select v-model="row.status"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Select Status...</option>
                                                    <option value="Missing">Missing</option>
                                                    <option value="Damaged">Damaged</option>
                                                    <option value="Lost">Lost</option>
                                                    <option value="Expired">Expired</option>
                                                    <option value="Low Quality">Low Quality</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="text" v-model="row.notes"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="Add note..."
                                                     />
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex space-x-2">
                                                    <button @click="removeBatchBackOrderRow(index)"
                                                        v-if="batchBackOrders.length > 1"
                                                        class="text-red-600 hover:text-red-800 transition-colors duration-150">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add Row for this batch -->
                            <div class="mt-3">
                                <button @click="addBatchBackOrderRow(selectedAllocation.id)" v-if="canAddMoreToAllocation(selectedAllocation)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Add Row for Batch {{ selectedAllocation.batch_number }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 border-t border-gray-200 flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            💡 Tip: You can save each back order individually using the ✓ button, or save all at once.
                        </div>
                        <div class="flex space-x-3">
                            <button @click="attemptCloseModal"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150">
                                Exit
                            </button>
                            <button @click="saveBackOrders"
                                class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="isSaving || !isCurrentAllocationComplete()">
                                <span v-if="isSaving">Saving...</span>
                                <span v-else>Save All & Exit</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incomplete Back Order Warning Modal -->
            <div v-if="showIncompleteBackOrderModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900">Incomplete Back Orders</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-6">
                            You have incomplete back orders for this batch. Are you sure you want to exit without saving them?
                        </p>
                        <div class="flex justify-end space-x-3">
                            <button @click="showIncompleteBackOrderModal = false"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150">
                                Continue Editing
                            </button>
                            <button @click="forceCloseModal"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-150">
                                Exit Anyway
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showDispatchForm" @close="showDispatchForm = false">
            <div class="p-6 bg-white rounded-md shadow-md">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Dispatch Information
                </h2>

                <form @submit.prevent="createDispatch" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Driver</label>
                        <Multiselect v-model="dispatchForm.driver" :options="driverOptions" :searchable="true"
                            :close-on-select="true" :show-labels="false" :allow-empty="true" placeholder="Select Driver"
                            track-by="id" label="name" @select="handleDriverSelect"
                            :class="{ 'border-red-500': dispatchErrors.driver_id }">
                            <template v-slot:option="{ option }">
                                <div :class="{
                                    'add-new-option': option.isAddNew,
                                }">
                                    <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New
                                        Driver</span>
                                    <span v-else>
                                        {{ option.name }}
                                        <span v-if="option.company" class="text-gray-500 text-sm">
                                            ({{ option.company.name }})
                                        </span>
                                    </span>
                                </div>
                            </template>
                        </Multiselect>
                        <p v-if="dispatchErrors.driver_id" class="mt-1 text-sm text-red-600">{{
                            dispatchErrors.driver_id[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dispatch Date</label>
                        <input type="date" v-model="dispatchForm.dispatch_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            :class="{ 'border-red-500': dispatchErrors.dispatch_date }">
                        <p v-if="dispatchErrors.dispatch_date" class="mt-1 text-sm text-red-600">{{
                            dispatchErrors.dispatch_date[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Number of Cartons</label>
                        <input type="number" v-model="dispatchForm.no_of_cartoons"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            :class="{ 'border-red-500': dispatchErrors.no_of_cartoons }">
                        <p v-if="dispatchErrors.no_of_cartoons" class="mt-1 text-sm text-red-600">{{
                            dispatchErrors.no_of_cartoons[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Driver Phone</label>
                        <input type="text" v-model="dispatchForm.driver_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            :class="{ 'border-red-500': dispatchErrors.driver_number }">
                        <p v-if="dispatchErrors.driver_number" class="mt-1 text-sm text-red-600">{{
                            dispatchErrors.driver_number[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vehicle Plate Number</label>
                        <input type="text" v-model="dispatchForm.plate_number"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            :class="{ 'border-red-500': dispatchErrors.plate_number }">
                        <p v-if="dispatchErrors.plate_number" class="mt-1 text-sm text-red-600">{{
                            dispatchErrors.plate_number[0] }}</p>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showDispatchForm = false" :disabled="isSaving"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSaving"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150 flex items-center">
                            <span v-if="isSaving" class="mr-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                            {{ isSaving ? 'Creating...' : 'Save and Dispatch' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showDriverModal" @close="closeDriverModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Driver</h2>

                <div class="mt-6">
                    <form @submit.prevent="submitDriver" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Driver ID</label>
                            <input type="text" v-model="driverForm.driver_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': driverErrors.driver_id }">
                            <p v-if="driverErrors.driver_id" class="mt-1 text-sm text-red-600">{{
                                driverErrors.driver_id[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" v-model="driverForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': driverErrors.name }">
                            <p v-if="driverErrors.name" class="mt-1 text-sm text-red-600">{{ driverErrors.name[0] }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" v-model="driverForm.phone"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': driverErrors.phone }">
                            <p v-if="driverErrors.phone" class="mt-1 text-sm text-red-600">{{ driverErrors.phone[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <Multiselect v-model="driverForm.company" :options="props.companyOptions" :searchable="true"
                                :close-on-select="true" :show-labels="false" :allow-empty="true"
                                placeholder="Select Company" track-by="id" label="name" @select="handleCompanySelect"
                                :class="{ 'border-red-500': driverErrors.logistic_company_id }">
                                <template v-slot:option="{ option }">
                                    <div :class="{
                                        'add-new-option': option.isAddNew,
                                    }">
                                        <span v-if="option.isAddNew" class="text-indigo-600 font-medium">+ Add New
                                            Company</span>
                                        <span v-else>{{ option.name }}</span>
                                    </div>
                                </template>
                            </Multiselect>
                            <p v-if="driverErrors.logistic_company_id" class="mt-1 text-sm text-red-600">{{
                                driverErrors.logistic_company_id[0] }}</p>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeDriverModal" :disabled="isSubmittingDriver"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmittingDriver"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150 flex items-center">
                                <span v-if="isSubmittingDriver" class="mr-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                {{ isSubmittingDriver ? 'Creating...' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Company Modal -->
        <Modal :show="showCompanyModal" @close="closeCompanyModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Add New Company</h2>

                <div class="mt-6">
                    <form @submit.prevent="submitCompany" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" v-model="companyForm.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': companyErrors.name }">
                            <p v-if="companyErrors.name" class="mt-1 text-sm text-red-600">{{ companyErrors.name[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" v-model="companyForm.email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': companyErrors.email }">
                            <p v-if="companyErrors.email" class="mt-1 text-sm text-red-600">{{ companyErrors.email[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" v-model="companyForm.phone"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': companyErrors.phone }">
                            <p v-if="companyErrors.phone" class="mt-1 text-sm text-red-600">{{ companyErrors.phone[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea v-model="companyForm.address" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': companyErrors.address }"></textarea>
                            <p v-if="companyErrors.address" class="mt-1 text-sm text-red-600">{{
                                companyErrors.address[0] }}</p>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="closeCompanyModal" :disabled="isSubmittingCompany"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors duration-150">
                                Cancel
                            </button>
                            <button type="submit" :disabled="isSubmittingCompany"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-150 flex items-center">
                                <span v-if="isSubmittingCompany" class="mr-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                                {{ isSubmittingCompany ? 'Creating...' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import moment from "moment";
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import Modal from "@/Components/Modal.vue";
import {
    UserIcon,
    PhoneIcon,
    TruckIcon,
    BuildingOfficeIcon,
    CalendarIcon,
    CubeIcon,
    ClockIcon,
    ArchiveBoxIcon,
    EnvelopeIcon,
    IdentificationIcon
} from '@heroicons/vue/24/outline';
import Multiselect from 'vue-multiselect';
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

const toast = useToast();
const page = usePage();

const props = defineProps({
    transfer: {
        type: Object,
        required: true,
    },
    drivers: {
        type: Array,
        required: true,
    },
    companyOptions: {
        type: Array,
        required: true,
    },
});

const form = ref([]);
const isLoading = ref(false);
const showBackOrderModal = ref(false);
const selectedItem = ref(null);
const selectedAllocation = ref(null);
const batchBackOrders = ref([]);
const showIncompleteBackOrderModal = ref(false);
const message = ref('');
const isUpdatingQuantity = ref({});
const updateQuantityTimeouts = ref({});
const isReceived = ref([]);
const receivedQuantityTimeouts = ref({});

onMounted(() => {
    form.value = props.transfer.items || [];
});

watch(
    () => props.transfer.items,
    (newItems) => {
        form.value = newItems || [];
    }
);

onBeforeUnmount(() => {
    // Clear all pending timeouts to prevent memory leaks
    Object.values(updateQuantityTimeouts.value).forEach(timeout => {
        if (timeout) clearTimeout(timeout);
    });
    Object.values(receivedQuantityTimeouts.value).forEach(timeout => {
        if (timeout) clearTimeout(timeout);
    });
});

// Status styling
const statusClasses = computed(() => ({
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    rejected: "bg-red-100 text-red-800",
    in_process: "bg-purple-100 text-purple-800",
    dispatched: "bg-orange-100 text-orange-800",
    delivered: "bg-indigo-100 text-indigo-800",
    received: "bg-green-100 text-green-800",
}));

// Methods
const isExpiringItem = (expiryDate) => {
    if (!expiryDate) return false;
    const expiry = moment(expiryDate);
    const now = moment();
    const daysUntilExpiry = expiry.diff(now, "days");
    return daysUntilExpiry <= 30; // Consider items expiring within 30 days as expiring
};

// removeItem function removed

// Validate allocation received quantity
const validateAllocationReceivedQuantity = async (allocation, allocIndex) => {
    // Clear existing timeout for this allocation
    if (receivedQuantityTimeouts.value[allocIndex]) {
        clearTimeout(receivedQuantityTimeouts.value[allocIndex]);
    }

    // Set loading state
    isReceived.value[allocIndex] = true;

    // Calculate the maximum allowed received quantity
    const maxAllowedQuantity = allocation.allocated_quantity || 0;
    const currentReceivedQuantity = allocation.received_quantity || 0;
    
    // Validate the quantity - cannot exceed allocated quantity
    if (currentReceivedQuantity > maxAllowedQuantity) {
        allocation.received_quantity = maxAllowedQuantity;
        toast.warning(`Received quantity cannot exceed allocated quantity. Reset to ${maxAllowedQuantity}`);
    }
    
    // Additional validation: Check if there are existing back orders
    // If there are back orders, the received quantity should not exceed the allocated quantity minus back orders
    if (allocation.differences && allocation.differences.length > 0) {
        const totalBackOrderQuantity = allocation.differences.reduce((sum, diff) => sum + (diff.quantity || 0), 0);
        const maxReceivedQuantity = maxAllowedQuantity - totalBackOrderQuantity;
        
        if (currentReceivedQuantity > maxReceivedQuantity) {
            allocation.received_quantity = maxReceivedQuantity;
            toast.warning(`Received quantity cannot exceed allocated quantity minus back orders. Reset to ${maxReceivedQuantity}`);
        }
    }

    // Set new timeout with 500ms delay for debouncing
    receivedQuantityTimeouts.value[allocIndex] = setTimeout(async () => {
        await axios.post(route("transfers.receivedQuantity"), {
            allocation_id: allocation.id,
            received_quantity: allocation.received_quantity,
        })
        .then((response) => {
            console.log(response.data);
            isReceived.value[allocIndex] = false;
            router.get(route("transfers.show", props.transfer.id), {}, {
                preserveScroll: true,
                only: ['transfer'],
            });
        })
        .catch((error) => {
            isReceived.value[allocIndex] = false;
            toast.error(error.response?.data || "Failed to update received quantity");
            console.log(error);
        });
    }, 500);
};

const openBackOrderModal = (item, allocation) => {
    if (!item || !allocation) {
        console.error('openBackOrderModal: item or allocation is null');
        return;
    }
    
    console.log('Opening back order modal for item:', item);
    console.log('Selected allocation:', allocation);

    showBackOrderModal.value = true;
    selectedItem.value = item;
    selectedAllocation.value = allocation;
    
    // Initialize batchBackOrders with existing differences for THIS SPECIFIC ALLOCATION
    batchBackOrders.value = [];
    
    // Check for existing differences in both item.differences and allocation.differences
    let existingDifferences = [];
    
    // First check allocation.differences (primary source)
    if (allocation.differences && allocation.differences.length > 0) {
        console.log('Found existing differences in allocation:', allocation.differences);
        existingDifferences = allocation.differences;
    }
    // Fallback to item.differences if allocation doesn't have them
    else if (item.differences && item.differences.length > 0) {
        console.log('Found existing differences in item:', item.differences);
        
        // Filter differences by the specific inventory_allocation_id
        existingDifferences = item.differences.filter(difference => 
            difference.inventory_allocation_id === allocation.id
        );
        
        console.log('Filtered differences for allocation:', allocation.id, existingDifferences);
    }
    
    // Populate the form with existing differences
    if (existingDifferences.length > 0) {
        existingDifferences.forEach((difference) => {
            batchBackOrders.value.push({
                id: difference.id,
                transfer_item_id: item.id,
                inventory_allocation_id: difference.inventory_allocation_id || allocation.id,
                quantity: difference.quantity,
                status: difference.status,
                notes: difference.notes || '',
                isExisting: true
            });
        });
        
        console.log('Initialized batchBackOrders with existing differences:', batchBackOrders.value);
    } else {
        console.log('No existing differences found, starting with empty form');
        // Add one empty row for new entry
        batchBackOrders.value.push({
            id: null,
            transfer_item_id: item.id,
            inventory_allocation_id: allocation.id,
            quantity: 0,
            status: '',
            notes: '',
            isExisting: false
        });
    }
};

// Computed properties for back order modal
const missingQuantity = computed(() => {
    if (!selectedItem.value) return 0;
    
    // If a specific allocation is selected, calculate missing quantity for that allocation only
    if (selectedAllocation.value) {
        return selectedAllocation.value.allocated_quantity - (selectedAllocation.value.received_quantity || 0);
    }
    
    // Otherwise, calculate total missing quantity for all allocations of the item
    return selectedItem.value.inventory_allocations?.reduce((total, allocation) => {
        return total + (allocation.allocated_quantity - (allocation.received_quantity || 0));
    }, 0) || 0;
});

function canAddMoreToAllocation(allocation) {
    const total = batchBackOrders.value.reduce((sum, row) => sum + Number(row.quantity || 0), 0);
    return (allocation.received_quantity + total) < allocation.allocated_quantity;
}

const totalBatchBackOrderQuantity = computed(() => {
    let total = 0;
    batchBackOrders.value.forEach((row) => {
        total += Number(row.quantity || 0);
    });
    return total;
});

function isCurrentAllocationComplete() {
    if (!selectedAllocation.value) return false;
    const total = batchBackOrders.value.reduce((sum, row) => sum + Number(row.quantity || 0), 0);
    return (selectedAllocation.value.received_quantity + total) == selectedAllocation.value.allocated_quantity;
}

const getMissingQuantity = (item, allocation = null) => {
    if (!item && !allocation) return 0;
    
    if (allocation) {
        // Calculate missing quantity for specific allocation
        const transferred = allocation.allocated_quantity || 0;
        const received = allocation.received_quantity || 0;
        
        // Debug logging
        console.log('getMissingQuantity - allocation:', allocation);
        console.log('getMissingQuantity - transferred:', transferred, 'received:', received, 'missing:', transferred - received);
        
        return transferred - received;
    } else if (item) {
        // Calculate total transferred and received from all allocations (legacy support)
        let totalTransferred = 0;
        let totalReceived = 0;
        
        if (item.inventory_allocations) {
            item.inventory_allocations.forEach(allocation => {
                totalTransferred += allocation.allocated_quantity || 0;
                totalReceived += allocation.received_quantity || 0;
            });
        }
        
        return totalTransferred - totalReceived;
    }
    
    return 0;
};

// Helper function to calculate remaining quantity for an allocation (considering back orders)
const getRemainingQuantityForAllocation = (allocation) => {
    if (!allocation) return 0;
    
    const allocatedQuantity = allocation.allocated_quantity || 0;
    const receivedQuantity = allocation.received_quantity || 0;
    
    // Calculate total back order quantity for this allocation
    let totalBackOrderQuantity = 0;
    if (allocation.differences && allocation.differences.length > 0) {
        totalBackOrderQuantity = allocation.differences.reduce((sum, diff) => sum + (diff.quantity || 0), 0);
    }
    
    // Remaining quantity = allocated - received - back orders
    const remainingQuantity = allocatedQuantity - receivedQuantity - totalBackOrderQuantity;
    
    return Math.max(0, remainingQuantity); // Ensure it's not negative
};

// Helper function to calculate maximum allowed received quantity for an allocation
const getMaxReceivedQuantity = (allocation) => {
    if (!allocation) return 0;
    
    const allocatedQuantity = allocation.allocated_quantity || 0;
    
    // If there are back orders, the maximum received quantity is allocated minus back orders
    if (allocation.differences && allocation.differences.length > 0) {
        const totalBackOrderQuantity = allocation.differences.reduce((sum, diff) => sum + (diff.quantity || 0), 0);
        return Math.max(0, allocatedQuantity - totalBackOrderQuantity);
    }
    
    // If no back orders, maximum is the allocated quantity
    return allocatedQuantity;
};

const getExistingBackOrders = (item, allocation = null) => {
    if (allocation) {
        // Count back orders for specific allocation
        return allocation.back_order ? allocation.back_order.length : 0;
    } else {
        // Count all back orders for the item (legacy support)
        if (!item || !item.inventory_allocations) return 0;

        let totalBackOrders = 0;
        item.inventory_allocations.forEach((allocation) => {
            if (allocation.back_order && allocation.back_order.length > 0) {
                totalBackOrders += allocation.back_order.length;
            }
        });

        return totalBackOrders;
    }
};

const handleQuantityInput = (event, allocation) => {
    // Clear existing timeout for this allocation
    if (updateQuantityTimeouts.value[allocation.id]) {
        clearTimeout(updateQuantityTimeouts.value[allocation.id]);
    }

    // Set new timeout with 500ms delay
    updateQuantityTimeouts.value[allocation.id] = setTimeout(() => {
        updateQuantity(event, allocation);
    }, 500);
};

const updateQuantity = async (event, allocation) => {
    const newQuantity = parseInt(event.target.value);
    
    if (!newQuantity || newQuantity <= 0) {
        toast.error("Please enter a valid quantity");
        // Reset input to original quantity
        event.target.value = allocation.allocated_quantity;
        return;
    }

    // Check if transfer is eligible for updates
    if (!['pending', 'reviewed'].includes(props.transfer.status)) {
        toast.error("Cannot update quantity for transfers that are not in pending status");
        // Reset input to original quantity
        event.target.value = allocation.allocated_quantity;
        return;
    }

    isUpdatingQuantity.value[allocation.id] = true;

    await axios.post(route("transfers.update-quantity"), {
        allocation_id: allocation.id,
        quantity: newQuantity,
    })
    .then(() => {
        isUpdatingQuantity.value[allocation.id] = false;
        
        // Reload the page to show updated values with preserveScroll
        router.get(route("transfers.show", props.transfer.id), {}, {preserveScroll: true});
    })
    .catch((error) => {
        isUpdatingQuantity.value[allocation.id] = false;
        console.error(error);
        toast.error(error.response?.data || "Failed to update quantity");
        // Reset input to original quantity on error
        event.target.value = allocation.allocated_quantity;
    });
};

function addBatchBackOrderRow(allocationId) {
    const allocation = selectedItem.value.inventory_allocations.find(allocation => allocation.id == allocationId);

    batchBackOrders.value.push({
        id: null,
        inventory_allocation_id: allocationId,
        batch_number: allocation.batch_number,
        barcode: allocation.barcode,
        quantity: 0,
        status: "Missing",
        notes: "",
        transfer_item_id: selectedItem.value.id,
    });
}

// Remove batch back order row
const removeBatchBackOrderRow = (index) => {
    batchBackOrders.value.splice(index, 1);
};

const validateBatchBackOrderQuantity = (row, allocation) => {
    const total = batchBackOrders.value.reduce((sum, r) => sum + Number(r.quantity || 0), 0);
    const missingQuantity = allocation.allocated_quantity - (allocation.received_quantity || 0);
    
    if (total > missingQuantity) {
        message.value = `Total quantity (${total}) exceeds missing quantity (${missingQuantity}) for this batch`;
    } else {
        message.value = "";
    }
};

// Function to get status badge CSS classes
const getStatusBadgeClass = (status) => {
    const statusClasses = {
        'Missing': 'bg-red-100 text-red-800',
        'Damaged': 'bg-orange-100 text-orange-800',
        'Expired': 'bg-yellow-100 text-yellow-800',
        'Lost': 'bg-purple-100 text-purple-800',
        'Low Quality': 'bg-gray-100 text-gray-800'
    };
    
    return statusClasses[status] || 'bg-gray-100 text-gray-800';
};





// Save back orders
const saveBackOrders = async () => {
    console.log(batchBackOrders.value);
    message.value = "";  
    
    // Check if the specific batch mismatch is recorded
    if (!selectedAllocation.value) {
        message.value = "No allocation selected";
        return;
    }
    
    const allocation = selectedAllocation.value;
    const total = batchBackOrders.value.reduce((sum, row) => sum + Number(row.quantity || 0), 0);
    const missingQuantity = allocation.allocated_quantity - (allocation.received_quantity || 0);
    
    // Validate that all missing quantity is accounted for
    if (total !== missingQuantity) {
        message.value = `Batch ${allocation.batch_number} mismatch: Expected ${missingQuantity} but recorded ${total}. Please ensure all missing quantities are recorded.`;
        return;
    }
    
    // Validate that all rows have required fields
    const invalidRows = batchBackOrders.value.filter(row => 
        !row.quantity || row.quantity <= 0 || !row.status
    );
    
    if (invalidRows.length > 0) {
        message.value = "Please ensure all rows have valid quantity and status values.";
        return;
    }
    
    isSaving.value = true;
    await axios.post(route("transfers.save-back-orders"), {
        transfer_id: props.transfer.id,
        packing_list_differences: batchBackOrders.value,
    })
        .then((response) => {
            isSaving.value = false;
            console.log(response.data);
            toast.success("Back orders saved successfully");
            message.value = "";
            
            // Close the modal after successful save
            showBackOrderModal.value = false;
            showIncompleteBackOrderModal.value = false;
            
            // Refresh the page to show updated data (backend will handle recalculation)
            router.get(route("transfers.show", props.transfer.id), {}, {
                preserveScroll: true,
                only: ['transfer'],
            });
        })
        .catch((error) => {
            isSaving.value = false;
            console.log(error);
            message.value = error.response?.data || "Failed to save back orders";
        });
};

const attemptCloseModal = () => {
    if (!selectedAllocation.value) return;
    
    const allocation = selectedAllocation.value;
    const total = batchBackOrders.value.reduce((sum, row) => sum + Number(row.quantity || 0), 0);
    const missingQuantity = allocation.allocated_quantity - (allocation.received_quantity || 0);
    
    // If the total back order quantity equals the missing quantity, we can close the modal
    if (total >= missingQuantity) {
        showBackOrderModal.value = false;
        showIncompleteBackOrderModal.value = false;
    } else {
        // Show warning modal for incomplete back orders
        showIncompleteBackOrderModal.value = true;
    }
};

const forceCloseModal = () => {
    showBackOrderModal.value = false;
    showIncompleteBackOrderModal.value = false;
};

const isType = ref([]);
// Define status order for progression
const statusOrder = ref([
    "pending",
    "reviewed",
    "approved",
    "in_process",
    "dispatched",
    "delivered",
    "received",
]);

// Permission-based computed properties
const canReview = computed(() => {
    return page.props.auth.can?.transfer_review || false;
});

const canApprove = computed(() => {
    return page.props.auth.can?.transfer_approve || false;
});

const canDispatch = computed(() => {
    const auth = page.props.auth;
    return (
        auth.user.warehouse_id == props.transfer.from_warehouse_id &&
        auth.can.transfer_dispatch
    );
});

const canReceive = computed(() => {
    const auth = page.props.auth;
    return (
        auth.user.warehouse_id == props.transfer.to_warehouse_id &&
        auth.can.transfer_receive
    );
});

const isRestoring = ref(false);
const restoreTransfer = async () => {
    Swal.fire({
        title: "Are you sure?",
        text: "You want to restore the transfer?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
    })
    .then(async (result) => {
        if (result.isConfirmed) {
            isRestoring.value = true;
            await axios.post(route("transfers.restore-transfer"), {
                transfer_id: props.transfer.id,
            })
                .then((response) => {
                    isRestoring.value = false;
                    Swal.fire({
                        title: "Success!",
                        text: response.data,
                        icon: "success",
                        confirmButtonText: "OK",
                    }).then(() => {
                        router.get(route("transfers.show", props.transfer.id));
                    });
                })
                .catch((error) => {
                    isRestoring.value = false;
                    console.log(error);
                    toast.error(error.response?.data || "Failed to restore transfer");
                });
        }
    });
};

// Function to change transfer status
const changeStatus = (transferId, newStatus, type) => {
    console.log(transferId, newStatus, type);

    // Get action name for better messaging
    const actionMap = {
        reviewed: "review",
        approved: "approve",
        in_process: "process",
        dispatched: "dispatch",
        delivered: "mark as delivered",
        received: "receive",
    };

    const actionName = actionMap[newStatus] || "change status of";

    Swal.fire({
        title: "Are you sure?",
        text: `Are you sure to make this Transfer ${newStatus.charAt(0).toUpperCase() +
            newStatus.slice(1).replace("_", " ")
            }?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: `Yes, ${actionName}!`,
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Set loading state
            isType.value[type] = true;

            try {
                const response = await axios.post(
                    route("transfers.change-status"),
                    {
                        transfer_id: transferId,
                        status: newStatus,
                    }
                );

                // Reset loading state
                isType.value[type] = false;

                Swal.fire({
                    title: "Success!",
                    text: `Transfer has been ${actionMap[newStatus] || "updated"
                        }d successfully.`,
                    icon: "success",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                }).then(() => {
                    // Reload the page to show the updated status
                    router.get(route("transfers.show", props.transfer.id));
                });
            } catch (error) {
                // Reset loading state
                isType.value[type] = false;

                // Extract error message from response
                let errorMessage = "Failed to update transfer status";

                if (error.response) {
                    if (error.response.status === 403) {
                        errorMessage =
                            error.response.data ||
                            "You don't have permission to perform this action";
                    } else if (error.response.status === 400) {
                        errorMessage =
                            error.response.data || "Invalid operation";
                    } else if (error.response.data) {
                        errorMessage = error.response.data;
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                }

                Swal.fire({
                    title: "Error!",
                    text: errorMessage,
                    icon: "error",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 5000, // Show error longer than success
                });
            }
        }
    });
};

const showDispatchForm = ref(false);
const showDriverModal = ref(false);
const isSaving = ref(false);
const isSubmittingDriver = ref(false);
const dispatchErrors = ref({});
const driverErrors = ref({});

// Update dispatch form structure to match Order/Show.vue exactly
const dispatchForm = ref({
    driver: null,
    dispatch_date: '',
    no_of_cartoons: '',
    driver_number: '',
    plate_number: '',
    logistic_company_id: ''
});

// Update driver form structure to match Order/Show.vue exactly
const driverForm = ref({
    driver_id: '',
    name: '',
    phone: '',
    logistic_company_id: '',
    company: null,
    is_active: true
});

// Add proper driver options computed property from Order/Show.vue
const driverOptions = computed(() => {
    if (!props.drivers || !Array.isArray(props.drivers)) {
        console.log('No drivers available or not an array:', props.drivers);
        return [{
            id: 'new',
            name: 'Add New Driver',
            isAddNew: true
        }];
    }

    const options = props.drivers.map(driver => ({
        id: driver.id,
        name: driver.name,
        phone: driver.phone,
        company: driver.company,
        isAddNew: false
    }));

    // Add the "Add New" option at the end
    options.push({
        id: 'new',
        name: 'Add New Driver',
        isAddNew: true
    });

    console.log('Driver options:', options);
    return options;
});

// Update driver selection handler to match Order/Show.vue exactly
const handleDriverSelect = (selected) => {
    console.log('Driver selected:', selected); // Debug log
    if (selected && selected.isAddNew) {
        // Reset the selection
        dispatchForm.value.driver = null;
        dispatchForm.value.driver_number = '';
        dispatchForm.value.logistic_company_id = '';
        // Open the driver modal
        openDriverModal();
    } else if (selected) {
        // Set the driver info
        dispatchForm.value.driver = selected;
        dispatchForm.value.driver_number = selected.phone || '';
        dispatchForm.value.logistic_company_id = selected.company?.id || '';
    } else {
        // Clear the driver info if deselected
        dispatchForm.value.driver = null;
        dispatchForm.value.driver_number = '';
        dispatchForm.value.logistic_company_id = '';
    }
};

// Add driver modal functions from Order/Show.vue
const openDriverModal = () => {
    driverForm.value = {
        driver_id: '',
        name: '',
        phone: '',
        logistic_company_id: '',
        company: null,
        is_active: true
    };
    showDriverModal.value = true;
};

const closeDriverModal = () => {
    showDriverModal.value = false;
    driverForm.value = {
        driver_id: '',
        name: '',
        phone: '',
        logistic_company_id: '',
        company: null,
        is_active: true
    };
    driverErrors.value = {};
};

const submitDriver = async () => {
    try {
        isSubmittingDriver.value = true;
        driverErrors.value = {};

        // Prepare form data with company ID
        const formData = {
            driver_id: driverForm.value.driver_id,
            name: driverForm.value.name,
            phone: driverForm.value.phone,
            logistic_company_id: driverForm.value.company?.id,
            is_active: driverForm.value.is_active
        };

        const response = await axios.post(route('settings.drivers.store'), formData);

        // Create a new driver option
        const newDriver = {
            id: response.data.id,
            name: driverForm.value.name,
            phone: driverForm.value.phone,
            company: driverForm.value.company,
            isAddNew: false
        };

        // Add the new driver to the options
        props.drivers.push(newDriver);

        // Select the new driver
        dispatchForm.value.driver = newDriver;
        dispatchForm.value.driver_number = newDriver.phone;
        dispatchForm.value.logistic_company_id = newDriver.company?.id;

        closeDriverModal();
        Swal.fire({
            title: 'Success!',
            text: response.data.message || 'Driver created successfully',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
    } catch (error) {
        if (error.response?.status === 422) {
            driverErrors.value = error.response.data.errors;
        } else {
            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        }
    } finally {
        isSubmittingDriver.value = false;
    }
};

const handleCompanySelect = (selected) => {
    if (selected && selected.isAddNew) {
        // Reset the selection
        dispatchForm.value.company = null;
        // Open the company modal - you'll need to implement this
        showCompanyModal.value = true;
    } else if (selected) {
        // Set the company info
        dispatchForm.value.company = selected;
    } else {
        // Clear the company info if deselected
        dispatchForm.value.company = null;
    }
};

// Add company modal state
const showCompanyModal = ref(false);
const isSubmittingCompany = ref(false);
const companyForm = ref({
    name: '',
    email: '',
    phone: '',
    address: '',
    is_active: true
});
const companyErrors = ref({});

async function createDispatch() {
    isSaving.value = true;
    dispatchErrors.value = {};

    console.log('Dispatch form data:', dispatchForm.value); // Debug form data

    // Validate form data before submission
    if (!dispatchForm.value.driver) {
        console.error('Driver not selected');
        dispatchErrors.value.driver_id = ['Please select a driver'];
        isSaving.value = false;
        return;
    }

    const formData = {
        transfer_id: props.transfer.id,
        driver_id: dispatchForm.value.driver?.id,
        logistic_company_id: dispatchForm.value.logistic_company_id,
        dispatch_date: dispatchForm.value.dispatch_date,
        driver_number: dispatchForm.value.driver_number,
        plate_number: dispatchForm.value.plate_number,
        no_of_cartoons: dispatchForm.value.no_of_cartoons,
        status: 'dispatched'
    };

    try {
        const response = await axios.post(route('transfers.dispatch-info'), formData);
        console.log(response.data);
        showDispatchForm.value = false;
        dispatchForm.value = {
            driver: null,
            dispatch_date: new Date().toISOString().split('T')[0],
            no_of_cartoons: '',
            driver_number: '',
            plate_number: '',
            logistic_company_id: ''
        };

        Swal.fire({
            title: 'Success!',
            text: response.data,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        }).then(() => {
            router.get(route("transfers.show", props.transfer.id));
        });
    } catch (error) {
        console.log(error);
        if (error.response?.status === 422) {
            console.log('Validation errors:', error.response.data.errors); // Debug validation errors
            dispatchErrors.value = error.response.data.errors;
            toast.error('Please check the form for errors');
        } else {
            console.error('Error details:', {
                status: error.response?.status,
                data: error.response?.data,
                message: error.message
            }); // Debug detailed error info

            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || error.message || 'Something went wrong',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
            });
        }
    } finally {
        isSaving.value = false;
    }
}

const isSavingQty = ref([]);
async function receivedQty(item, index) {
    isSavingQty.value[index] = true;
    // console.log(item, index);
    if (item.quantity_to_release < item.received_quantity) {
        item.received_quantity = item.quantity_to_release;
    }

    await axios
        .post(route("transfers.receivedQuantity"), {
            transfer_item_id: item.id,
            received_quantity: item.received_quantity,
        })
        .then((response) => {
            isSavingQty.value[index] = false;
        })
        .catch((error) => {
            console.log(error.response.data);
            Swal.fire({
                title: "Error!",
                text: error.response?.data || "Failed to update quantity",
                icon: "error",
                confirmButtonText: "OK",
            });
            isSavingQty.value[index] = false;
            router.get(route("transfers.show", props.transfer?.id), {}, { preserveState: true, preserveScroll: true, only: ['transfer'] });
        });
}

const closeCompanyModal = () => {
    showCompanyModal.value = false;
    companyForm.value = {
        name: '',
        email: '',
        phone: '',
        address: '',
        is_active: true
    };
    companyErrors.value = {};
};

const submitCompany = async () => {
    try {
        isSubmittingCompany.value = true;
        companyErrors.value = {};

        const response = await axios.post(route('settings.companies.store'), companyForm.value);

        // Create a new company option
        const newCompany = {
            id: response.data.id,
            name: companyForm.value.name,
            isAddNew: false
        };

        // Add the new company to the options
        props.companyOptions.push(newCompany);

        // Select the new company
        dispatchForm.value.company = newCompany;

        closeCompanyModal();
        toast.success(response.data.message || 'Company created successfully');
    } catch (error) {
        if (error.response?.status === 422) {
            companyErrors.value = error.response.data.errors;
        } else {
            toast.error(error.response?.data || 'Something went wrong');
        }
    } finally {
        isSubmittingCompany.value = false;
    }
};
</script>