<template>
    <AuthenticatedLayout
        title="Transfer Details"
        description="Transfer Details"
        img="/assets/images/transfer.png"
    >
        <div class="container mx-auto">
            <!-- Transfer Header -->
            <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Transfer Details
                    </h1>
                    <div class="flex items-center space-x-4">
                        <span
                            :class="[
                                statusClasses[props.transfer.status] ||
                                    statusClasses.default,
                            ]"
                            class="flex items-center text-xs font-bold px-4 py-2"
                        >
                            <!-- Status Icon -->
                            <span class="mr-3">
                                <!-- Pending Icon -->
                                <img
                                    v-if="props.transfer.status === 'pending'"
                                    src="/assets/images/pending.png"
                                    class="w-4 h-4"
                                    alt="Pending"
                                />

                                <!-- reviewed Icon -->
                                <img
                                    v-else-if="
                                        props.transfer.status === 'reviewed'
                                    "
                                    src="/assets/images/reviewed.png"
                                    class="w-4 h-4"
                                    alt="Reviewed"
                                />

                                <!-- Approved Icon -->
                                <img
                                    v-else-if="
                                        props.transfer.status === 'approved'
                                    "
                                    src="/assets/images/approved.png"
                                    class="w-4 h-4"
                                    alt="Approved"
                                />

                                <!-- In Process Icon -->
                                <img
                                    v-else-if="
                                        props.transfer.status === 'in_process'
                                    "
                                    src="/assets/images/inprocess.png"
                                    class="w-4 h-4"
                                    alt="In Process"
                                />

                                <!-- Dispatched Icon -->
                                <img
                                    v-else-if="
                                        props.transfer.status === 'dispatched'
                                    "
                                    src="/assets/images/dispatch.png"
                                    class="w-4 h-4"
                                    alt="Dispatched"
                                />

                                <!-- Received Icon -->
                                <img
                                    v-else-if="
                                        props.transfer.status === 'received'
                                    "
                                    src="/assets/images/received.png"
                                    class="w-4 h-4"
                                    alt="Received"
                                />

                                <!-- Rejected Icon -->
                                <svg
                                    v-else-if="
                                        props.transfer.status === 'rejected'
                                    "
                                    class="w-4 h-4 text-red-700"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                            </span>
                            {{ props.transfer.status.toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Transfer ID and Date -->
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500"
                                >Transfer ID:</span
                            >
                            <span class="ml-2 font-semibold"
                                >#{{ props.transfer.transferID }}</span
                            >
                        </div>
                        <div>
                            <span class="text-sm text-gray-500"
                                >Transfer Date:</span
                            >
                            <span class="ml-2 font-semibold">{{
                                moment(props.transfer.transfer_date).format(
                                    "DD/MM/YYYY"
                                )
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- From and To Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- From Section -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3
                            class="text-lg font-semibold text-blue-800 mb-3 flex items-center"
                        >
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                />
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
                            <span
                                class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                            >
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
                            <span
                                class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"
                            >
                                Facility
                            </span>
                        </div>
                    </div>

                    <!-- To Section -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3
                            class="text-lg font-semibold text-green-800 mb-3 flex items-center"
                        >
                            <svg
                                class="w-5 h-5 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                />
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
                            <span
                                class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
                            >
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
                            <span
                                class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"
                            >
                                Facility
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Transfer Timeline -->

                <div class="col-span-2 mb-6 mt-5">
                    <div class="relative">
                        <!-- Timeline Track Background -->
                        <div
                            class="absolute top-7 left-0 right-0 h-2 bg-gray-200 z-0"
                        ></div>

                        <!-- Timeline Progress -->
                        <div
                            class="absolute top-7 left-0 h-2 bg-green-500 z-0 transition-all duration-500 ease-in-out"
                            :style="{
                                width: `${
                                    (statusOrder.indexOf(
                                        props.transfer.status
                                    ) /
                                        (statusOrder.length - 1)) *
                                    100
                                }%`,
                            }"
                        ></div>

                        <!-- Timeline Steps -->
                        <div class="relative flex justify-between">
                            <!-- Pending -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('pending')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/pending.png"
                                        class="w-7 h-7"
                                        alt="Pending"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >= statusOrder.indexOf('pending')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('pending')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Pending</span
                                >
                            </div>

                            <!-- Reviewed -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('reviewed')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/review.png"
                                        class="w-7 h-7"
                                        alt="Reviewed"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >= statusOrder.indexOf('reviewed')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('reviewed')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Reviewed</span
                                >
                            </div>

                            <!-- Approved -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('approved')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/approved.png"
                                        class="w-7 h-7"
                                        alt="Approved"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >= statusOrder.indexOf('approved')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('approved')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Approved</span
                                >
                            </div>

                            <!-- In Process -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('in_process')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/inprocess.png"
                                        class="w-7 h-7"
                                        alt="In Process"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >=
                                            statusOrder.indexOf('in_process')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('in_process')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >In Process</span
                                >
                            </div>

                            <!-- Dispatch -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('dispatched')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/dispatch.png"
                                        class="w-7 h-7"
                                        alt="Dispatch"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >=
                                            statusOrder.indexOf('dispatched')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('dispatched')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Dispatched</span
                                >
                            </div>

                            <!-- Delivered -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('delivered')
                                            ? 'bg-white border-orange-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/delivery.png"
                                        class="w-7 h-7"
                                        alt="Dispatch"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >=
                                            statusOrder.indexOf('delivered')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('delivered')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Delivered</span
                                >
                            </div>

                            <!-- Received -->
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-14 h-14 rounded-full border-4 flex items-center justify-center z-10"
                                    :class="[
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('received')
                                            ? 'bg-white border-green-500'
                                            : 'bg-white border-gray-200',
                                    ]"
                                >
                                    <img
                                        src="/assets/images/received.png"
                                        class="w-7 h-7"
                                        alt="Received"
                                        :class="
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) >= statusOrder.indexOf('received')
                                                ? ''
                                                : 'opacity-40'
                                        "
                                    />
                                </div>
                                <span
                                    class="mt-3 text-xs font-bold"
                                    :class="
                                        statusOrder.indexOf(
                                            props.transfer.status
                                        ) >= statusOrder.indexOf('received')
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                    >Received</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Items Table -->
                <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Transfer Items
                    </h3>

                   <div class="overflow-auto">
                    <table class="min-w-full border border-collapse border-gray-300">
                        <thead>
                            <tr class="bg-gray-50">
                                <th
                                    class="min-w-[300px] px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Item Name
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-center text-black font-semibold"
                                    colspan="5"
                                >
                                    Item details
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Total Quantity on Hand Per Unit
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Reasons for Transfers
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Quantity to be transferred
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-left text-black font-semibold"
                                    rowspan="2"
                                >
                                    Received Quantity
                                </th>
                                <th
                                    class="px-4 py-2 border border-gray-300 text-center text-black font-semibold"
                                    rowspan="2"
                                >
                                    Action
                                </th>
                            </tr>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 border border-gray-300 text-center text-black font-semibold">
                                    UoM
                                </th>
                                <th class="px-4 py-2 border border-gray-300 text-center text-black font-semibold">
                                    QTY
                                </th>
                                <th class="px-4 py-2 border border-gray-300 text-center text-black font-semibold">
                                    Batch Number
                                </th>
                                <th class="px-4 py-2 border border-gray-300 text-center text-black font-semibold">
                                    Expiry Date
                                </th>
                                <th class="px-4 py-2 border border-gray-300 text-center text-black font-semibold">
                                    Location
                                </th>
                                
                            </tr>
                        </thead>

                        <tbody>
                            <template v-for="(item, index) in form" :key="item.id">
                                <!-- Main row for items with multiple allocations -->
                                <tr
                                    v-for="(allocation, allocIndex) in item.inventory_allocations || [{}]"
                                    :key="`${item.id}-${allocIndex}`"
                                    class="hover:bg-gray-50 transition-colors duration-150 border-b border-gray-300"
                                >
                                    <!-- Item Name (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-left text-black align-top"
                                    >
                                        <div class="font-medium">{{ item.product?.name }}</div>
                                        {{ item.quantity_to_release }}
                                    </td>
                                    
                                    <!-- Category (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-left text-black align-top"
                                    >
                                        {{ item.product?.category?.name }}
                                    </td>
                                    
                                    <!-- Item Details - QTY -->
                                    <td class="px-4 py-2 border border-gray-300 text-center text-black">
                                        {{ allocation.allocated_quantity || 0 }}
                                    </td>
                                    
                                    <!-- Item Details - Batch Number -->
                                    <td class="px-4 py-2 border border-gray-300 text-center text-black">
                                        <span
                                            :class="{
                                                'text-red-600 font-bold': allocation.batch_number === 'HK5273'
                                            }"
                                        >
                                            {{ allocation.batch_number || 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Item Details - Expiry Date -->
                                    <td class="px-4 py-2 border border-gray-300 text-center text-black">
                                        <span
                                            :class="{
                                                'text-red-600': isExpiringItem(allocation.expiry_date)
                                            }"
                                        >
                                            {{
                                                allocation.expiry_date
                                                    ? moment(allocation.expiry_date).format("MMM YYYY")
                                                    : 'N/A'
                                            }}
                                        </span>
                                    </td>
                                    
                                    <!-- Item Details - Location -->
                                    <td class="px-4 py-2 border border-gray-300 text-center text-black">
                                        {{ allocation.location?.location || 'N/A' }}
                                    </td>
                                    
                                    <!-- Item Details - UoM -->
                                    <td class="px-4 py-2 border border-gray-300 text-center text-black">
                                        {{ item.uom || 'N/A' }}
                                    </td>
                                    
                                    <!-- Total Quantity on Hand Per Unit (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-center text-black align-top"
                                    >
                                        {{ item.quantity_per_unit || 0 }}
                                    </td>
                                    
                                    <!-- Reasons for Transfers (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-center text-black align-top"
                                    >
                                        <span
                                            :class="{
                                                'text-red-600': allocation.batch_number === 'HK5273'
                                            }"
                                        >
                                            {{ item.transfer_reason || (isExpiringItem(allocation.expiry_date) ? 'Soon to expire' : 'Slow Moving') }}
                                        </span>
                                    </td>
                                    
                                    <!-- Quantity to be transferred (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-center text-black align-top"
                                    >
                                        <input
                                            type="number"
                                            v-model="item.quantity_to_release"
                                            @keyup.enter="updateQuantity(item)"
                                            class="w-20 text-center border border-gray-300 rounded px-2 py-1 text-sm"
                                        />
                                        <span v-if="isUpading[index]" class="text-green-600">
                                            {{ isUpading[index] ? 'Updating...' : '' }}
                                        </span>
                                       
                                    </td>
                                    
                                    <!-- Received Quantity (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-center text-black align-top"
                                    >
                                    <input
                                        type="number"
                                        v-model="item.received_quantity"
                                        :max="item.quantity_to_release || 0"
                                        min="0"
                                        @input="validateReceivedQuantity(item)"
                                        :id="`received-quantity-${index}`"
                                        class="w-20 text-center border border-gray-300 rounded px-2 py-1 text-sm"
                                    />
                                    <!-- :readonly="!['delivered', 'received'].includes(props.transfer.status)" -->
                                      <!-- Backorder button - show when quantity_to_release > received_quantity -->
                                      <button
                                            @click="showBackOrderModal(item)"
                                            v-if="(item.quantity_to_release || 0) > (item.received_quantity || 0)"
                                            class="text-xs text-orange-600 underline hover:text-orange-800 cursor-pointer mt-1 block"
                                        >
                                            Back Order
                                        </button>
                                    </td>
                                    
                                    <!-- Action (only show on first row for this item) -->
                                    <td
                                        v-if="allocIndex === 0"
                                        :rowspan="item.inventory_allocations?.length || 1"
                                        class="px-4 py-2 border border-gray-300 text-center align-top"
                                    >
                                        <button
                                            v-if="props.transfer.status === 'pending'"
                                            @click="removeItem(index)"
                                            class="text-red-600 hover:text-red-800 transition-colors"
                                            title="Delete item"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                   </div>
                </div>
            </div>
        </div>

        <!-- Back Order Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900">Back Order Details - Transfer #{{ props.transfer.transferID }}</h2>
                    <button
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors duration-150"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6">
                    <!-- Product Information -->
                    <div v-if="selectedBackOrderItem" class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Product</p>
                                <p class="text-sm text-gray-900">{{ selectedBackOrderItem.product?.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Product ID</p>
                                <p class="text-sm text-gray-900">{{ selectedBackOrderItem.product?.productID }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Quantity to Release</p>
                                <p class="text-sm text-gray-900">{{ selectedBackOrderItem.quantity_to_release }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Received Quantity</p>
                                <p class="text-sm text-gray-900">{{ selectedBackOrderItem.received_quantity || 0 }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Missing Quantity</p>
                                <p class="text-sm font-bold text-red-600">{{ getMissingQuantity(selectedBackOrderItem) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Existing Back Orders</p>
                                <p class="text-sm text-gray-900">{{ getExistingBackOrders(selectedBackOrderItem) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="text-sm font-medium text-blue-800">Instructions</h3>
                        </div>
                        <p class="text-sm text-blue-700">
                            Record the missing quantity by categorizing items as Missing, Damaged, Lost, Expired, or Low Quality. 
                            You can add multiple entries to account for different issue types. 
                            The total of all entries should equal the missing quantity ({{ getMissingQuantity(selectedBackOrderItem) }}).
                        </p>
                    </div>

                    <!-- Backorder Recording Table -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Record Missing Items</h3>
                        
                        <!-- Error Message -->
                        <div v-if="backOrderError" class="mb-4 bg-red-50 border border-red-200 text-red-600 p-4 rounded">
                            {{ backOrderError }}
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(row, index) in backOrderRows" :key="index">
                                        <td class="px-3 py-2">
                                            <input 
                                                type="number" 
                                                v-model="row.quantity"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                min="1" 
                                                :max="getMissingQuantity(selectedBackOrderItem)"
                                                @input="validateBackOrderQuantities"
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <select 
                                                v-model="row.status"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            >
                                                <option v-for="status in ['Missing', 'Damaged', 'Lost', 'Expired', 'Low quality']"
                                                    :key="status" 
                                                    :value="status"
                                                >
                                                    {{ status }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input 
                                                type="text" 
                                                v-model="row.note"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                placeholder="Add note..."
                                            >
                                        </td>
                                        <td class="px-3 py-2">
                                            <button 
                                                @click="removeBackOrderRow(index)" 
                                                v-if="backOrderRows.length > 1"
                                                class="text-red-600 hover:text-red-800 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                                type="button"
                                                :disabled="isDeleting[index]"
                                            >
                                                <!-- Loading spinner when deleting -->
                                                <svg v-if="isDeleting[index]" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <!-- Delete icon when not deleting -->
                                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Row and Summary -->
                        <div class="mt-4 flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <button 
                                    @click="addBackOrderRow"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!canAddMoreRows"
                                >
                                    Add Row
                                </button>
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ totalBackOrderQuantity }}</span>
                                    <span class="text-gray-600"> / {{ getMissingQuantity(selectedBackOrderItem) }} items recorded</span>
                                </div>
                            </div>

                            <!-- Status indicator -->
                            <div class="text-sm">
                                <span v-if="remainingToAllocate <= 0" class="text-green-600 font-medium">
                                    ✓ All missing items recorded
                                </span>
                                <span v-else class="text-yellow-600 font-medium">
                                    {{ remainingToAllocate }} items remaining
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150"
                    >
                        Exit
                    </button>
                    <button
                        @click="saveBackOrders"
                        class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSaving || !isValidForSave"
                    >
                        <span v-if="isSaving">Saving...</span>
                        <span v-else>Save Back Orders and Exit</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import moment from "moment";
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    transfer: {
        type: Object,
        required: true,
    },
});

const form = ref([]);
const isLoading = ref(false);
const showModal = ref(false);
const selectedBackOrderItem = ref(null);
const backOrderRows = ref([]);
const backOrderError = ref('');
const isSaving = ref(false);
const isDeleting = ref([]);

onMounted(() => {
    form.value = props.transfer.items || [];
});

const statusOrder = [
    "pending",
    "reviewed",
    "approved",
    "in_process",
    "dispatched",
    "delivered",
    "received",
];

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

const getTimelineStatusBorder = (status) => {
    const currentStatusIndex = statusOrder.indexOf(props.transfer.status);
    const statusIndex = statusOrder.indexOf(status);

    // Handle rejected status
    if (props.transfer.status === "rejected") {
        if (status === "pending") return "border-green-500"; // completed
        if (status === "reviewed") return "border-green-500"; // completed
        if (status === "approved") return "border-red-500"; // rejected
        return "border-gray-300"; // not applicable
    }

    // Normal progression
    if (statusIndex <= currentStatusIndex) {
        return "border-green-500"; // completed
    } else if (statusIndex === currentStatusIndex + 1) {
        return "border-orange-500"; // current/next
    } else {
        return "border-gray-300"; // not reached
    }
};

const getTimelineLineClass = (fromStatus, toStatus) => {
    const currentStatusIndex = statusOrder.indexOf(props.transfer.status);
    const fromStatusIndex = statusOrder.indexOf(fromStatus);
    const toStatusIndex = statusOrder.indexOf(toStatus);

    // Handle rejected status
    if (props.transfer.status === "rejected") {
        if (fromStatus === "pending" && toStatus === "reviewed")
            return "bg-green-400"; // completed
        if (fromStatus === "reviewed" && toStatus === "approved")
            return "bg-red-400"; // rejected
        return "bg-gray-200"; // not applicable
    }

    // Normal progression
    if (
        fromStatusIndex <= currentStatusIndex &&
        toStatusIndex <= currentStatusIndex
    ) {
        return "bg-green-400"; // completed
    } else if (
        fromStatusIndex <= currentStatusIndex &&
        toStatusIndex === currentStatusIndex + 1
    ) {
        return "bg-orange-400"; // current/next
    } else {
        return "bg-gray-200"; // not reached
    }
};

const getTimelineImageClass = (status) => {
    const currentStatusIndex = statusOrder.indexOf(props.transfer.status);
    const statusIndex = statusOrder.indexOf(status);

    // Handle rejected status
    if (props.transfer.status === "rejected") {
        if (status === "pending") return "text-green-500"; // completed
        if (status === "reviewed") return "text-green-500"; // completed
        if (status === "approved") return "text-red-500"; // rejected
        return "text-gray-400"; // not applicable
    }

    // Normal progression
    if (statusIndex <= currentStatusIndex) {
        return "text-green-500"; // completed
    } else if (statusIndex === currentStatusIndex + 1) {
        return "text-orange-500"; // current/next
    } else {
        return "text-gray-400"; // not reached
    }
};

// Computed properties for table functionality
const showReceivedColumn = computed(() => {
    return ["delivered", "received"].includes(props.transfer.status);
});

const showActionsColumn = computed(() => {
    return ["delivered", "received"].includes(props.transfer.status);
});

const canEditReceivedQuantity = computed(() => {
    return props.transfer.status === "delivered";
});

const totalOrderedQuantity = computed(() => {
    return (
        props.transfer.items?.reduce(
            (total, item) => total + (item.quantity || 0),
            0
        ) || 0
    );
});

const totalReceivedQuantity = computed(() => {
    return (
        props.transfer.items?.reduce(
            (total, item) => total + (item.received_quantity || 0),
            0
        ) || 0
    );
});

const completionPercentage = computed(() => {
    if (totalOrderedQuantity.value === 0) return 0;
    return Math.round(
        (totalReceivedQuantity.value / totalOrderedQuantity.value) * 100
    );
});

// Methods
const isExpiringItem = (expiryDate) => {
    if (!expiryDate) return false;
    const expiry = moment(expiryDate);
    const now = moment();
    const daysUntilExpiry = expiry.diff(now, "days");
    return daysUntilExpiry <= 30; // Consider items expiring within 30 days as expiring
};

const getReceivedQuantityClass = (item) => {
    if (!item.received_quantity) return "text-gray-500";
    if (item.received_quantity === item.quantity) return "text-green-600";
    if (item.received_quantity < item.quantity) return "text-orange-600";
    return "text-gray-900";
};

const canCreateBackorder = (item) => {
    return (
        props.transfer.status === "delivered" &&
        (item.received_quantity || 0) < item.quantity
    );
};

const updateReceivedQuantity = (itemId, quantity) => {
    // TODO: Implement API call to update received quantity
    console.log("Update received quantity for item:", itemId, "to:", quantity);
};

const openBackOrderModal = (item) => {
    // TODO: Implement backorder modal
    console.log("Open backorder modal for item:", item);
};

const viewBackorders = (item) => {
    // TODO: Implement view backorders
    console.log("View backorders for item:", item);
};

const removeItem = (index) => {
    if (confirm('Are you sure you want to remove this item from the transfer?')) {
        form.value.splice(index, 1);
        // TODO: Implement API call to remove item from transfer
        console.log("Removed item at index:", index);
    }
};

// update quantity
const isUpading = ref(false);
async function updateQuantity(item, type) {
    isUpading.value = true;
    await axios
        .post(route("transfers.update-quantity"), {
            item_id: item.id,
            quantity: item.quantity_to_release,
        })
        .then((response) => {
            isUpading.value = false;
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
            isUpading.value = false;
            console.log(error);
            toast.error(error.response?.data || "Failed to update quantity");
        });
}

const focusReceivedQuantity = (index) => {
    const input = document.querySelector(`#received-quantity-${index}`);
    input.focus();
}

const showBackOrderModal = (item) => {
    console.log(item);
    selectedBackOrderItem.value = null;
    showModal.value = true;
    selectedBackOrderItem.value = item;
    backOrderRows.value = [];
    isDeleting.value = []; // Reset deleting states
    
    // Load existing backorders from inventory allocations
    if (item.inventory_allocations) {
        item.inventory_allocations.forEach(allocation => {
            if (allocation.back_order && allocation.back_order.length > 0) {
                allocation.back_order.forEach(backOrder => {
                    backOrderRows.value.push({
                        id: backOrder.id, // Include ID for existing backorders
                        quantity: backOrder.quantity,
                        status: backOrder.type,
                        note: backOrder.notes || ''
                    });
                    isDeleting.value.push(false); // Initialize deleting state for each row
                });
            }
        });
    }
    
    // If no existing backorders found, add one empty row for new entry
    if (backOrderRows.value.length === 0) {
        addBackOrderRow();
    }
}

const validateReceivedQuantity = (item) => {
    if (item.received_quantity > item.quantity_to_release) {
        item.received_quantity = item.quantity_to_release;
    }
}

const getMissingQuantity = (item) => {
    return item.quantity_to_release - item.received_quantity;
}

const getExistingBackOrders = (item) => {
    if (!item || !item.inventory_allocations) return 0;
    
    let totalBackOrders = 0;
    item.inventory_allocations.forEach(allocation => {
        if (allocation.back_order && allocation.back_order.length > 0) {
            totalBackOrders += allocation.back_order.length;
        }
    });
    
    return totalBackOrders;
}

const getRemainingToAllocate = (item) => {
    return getMissingQuantity(item) - getExistingBackOrders(item);
}

const addBackOrderRow = () => {
    backOrderRows.value.push({
        quantity: 0,
        status: '',
        note: ''
    });
    isDeleting.value.push(false); // Initialize deleting state for new row
}

const removeBackOrderRow = async (index) => {
    const row = backOrderRows.value[index];
    
    // If the row has an ID, it's an existing backorder - delete from database
    if (row.id) {
        // Set loading state for this specific row
        isDeleting.value[index] = true;
        
        try {
            await axios.post(route('transfers.delete-back-order'), {
                backorder_id: row.id
            });
            toast.success('Backorder record deleted');
        } catch (error) {
            console.error('Error deleting backorder:', error);
            toast.error(error.response?.data?.error || 'Failed to delete backorder');
            return; // Don't remove from frontend if backend deletion failed
        } finally {
            // Clear loading state for this row
            isDeleting.value[index] = false;
        }
    }
    
    // Remove from frontend array
    backOrderRows.value.splice(index, 1);
    
    // Also remove the corresponding isDeleting entry to keep arrays in sync
    isDeleting.value.splice(index, 1);
}

const validateBackOrderQuantities = () => {
    const totalQuantity = backOrderRows.value.reduce((total, row) => total + row.quantity, 0);
    if (totalQuantity > getMissingQuantity(selectedBackOrderItem.value)) {
        backOrderError.value = 'Total quantity exceeds missing quantity';
    } else {
        backOrderError.value = '';
    }
}

const totalBackOrderQuantity = computed(() => {
    return backOrderRows.value.reduce((total, row) => total + row.quantity, 0);
});

const canAddMoreRows = computed(() => {
    return totalBackOrderQuantity.value < getMissingQuantity(selectedBackOrderItem.value);
});

const remainingToAllocate = computed(() => {
    return getMissingQuantity(selectedBackOrderItem.value) - totalBackOrderQuantity.value;
});

const isValidForSave = computed(() => {
    return remainingToAllocate.value === 0 && backOrderError.value === '';
});

const saveBackOrders = async () => {
    if (!isValidForSave.value) return;

    isSaving.value = true;
    try {
        const response = await axios.post(route('transfers.save-back-orders'), {
            item_id: selectedBackOrderItem.value.id,
            back_orders: backOrderRows.value
        });
        toast.success(response.data);
        showModal.value = false;
        router.get(route("transfers.show", props.transfer.id));
    } catch (error) {
        console.log(error);
        toast.error(error.response?.data || "Failed to save back orders");
    } finally {
        isSaving.value = false;
    }
}
</script>
