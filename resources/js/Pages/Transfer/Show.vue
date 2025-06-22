<template>
    <AuthenticatedLayout>
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

                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                    >
                                        Product
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                    >
                                        Batch
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                    >
                                        Expiry
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                    >
                                        Ordered Qty
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                        v-if="showReceivedColumn"
                                    >
                                        Received Qty
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-700"
                                        v-if="showActionsColumn"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in props.transfer.items"
                                    :key="item.id"
                                    class="border-t hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3">
                                        <div>
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{ item.product?.name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Code:
                                                {{ item.product?.productID }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{
                                                    item.product?.movement
                                                }}
                                                Movement
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="
                                                item.inventory_allocations
                                                    ?.length > 0
                                            "
                                            class="text-sm text-gray-700"
                                        >
                                            {{
                                                item.inventory_allocations[0]
                                                    .batch_number || "N/A"
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-sm text-gray-400"
                                            >N/A</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="
                                                item.inventory_allocations
                                                    ?.length > 0 &&
                                                item.inventory_allocations[0]
                                                    .expiry_date
                                            "
                                            class="text-sm"
                                            :class="
                                                isExpiringItem(
                                                    item
                                                        .inventory_allocations[0]
                                                        .expiry_date
                                                )
                                                    ? 'text-red-600 font-medium'
                                                    : 'text-gray-700'
                                            "
                                        >
                                            {{
                                                moment(
                                                    item
                                                        .inventory_allocations[0]
                                                        .expiry_date
                                                ).format("DD/MM/YYYY")
                                            }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-sm text-gray-400"
                                            >N/A</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-sm font-medium text-gray-900"
                                            >{{ item.quantity }}</span
                                        >
                                    </td>
                                    <td
                                        class="px-4 py-3"
                                        v-if="showReceivedColumn"
                                    >
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <input
                                                v-if="canEditReceivedQuantity"
                                                type="number"
                                                :value="
                                                    item.received_quantity || 0
                                                "
                                                @input="
                                                    updateReceivedQuantity(
                                                        item.id,
                                                        $event.target.value
                                                    )
                                                "
                                                class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                :min="0"
                                                :max="item.quantity"
                                            />
                                            <span
                                                v-else
                                                class="text-sm font-medium"
                                                :class="
                                                    getReceivedQuantityClass(
                                                        item
                                                    )
                                                "
                                            >
                                                {{
                                                    item.received_quantity || 0
                                                }}
                                            </span>

                                            <!-- Status indicator -->
                                            <div
                                                v-if="
                                                    item.received_quantity !==
                                                        undefined &&
                                                    item.received_quantity <
                                                        item.quantity
                                                "
                                                class="flex items-center"
                                            >
                                                <span
                                                    class="w-2 h-2 bg-orange-400 rounded-full mr-1"
                                                ></span>
                                                <span
                                                    class="text-xs text-orange-600"
                                                    >Partial</span
                                                >
                                            </div>
                                            <div
                                                v-else-if="
                                                    item.received_quantity ===
                                                    item.quantity
                                                "
                                                class="flex items-center"
                                            >
                                                <span
                                                    class="w-2 h-2 bg-green-400 rounded-full mr-1"
                                                ></span>
                                                <span
                                                    class="text-xs text-green-600"
                                                    >Complete</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-3"
                                        v-if="showActionsColumn"
                                    >
                                        <div class="flex space-x-2">
                                            <button
                                                v-if="canCreateBackorder(item)"
                                                @click="
                                                    openBackOrderModal(item)
                                                "
                                                class="px-3 py-1 bg-orange-500 text-white text-sm rounded hover:bg-orange-600 transition-colors"
                                            >
                                                Backorder
                                            </button>
                                            <button
                                                v-if="
                                                    item.backorders?.length > 0
                                                "
                                                @click="viewBackorders(item)"
                                                class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors"
                                            >
                                                View ({{
                                                    item.backorders.length
                                                }})
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
                                Total Items:
                                <span class="font-medium">{{
                                    props.transfer.items?.length || 0
                                }}</span>
                            </div>
                            <div
                                v-if="showReceivedColumn"
                                class="text-sm text-gray-600"
                            >
                                Total Ordered:
                                <span class="font-medium">{{
                                    totalOrderedQuantity
                                }}</span>
                                | Total Received:
                                <span class="font-medium">{{
                                    totalReceivedQuantity
                                }}</span>
                                | Completion:
                                <span
                                    class="font-medium"
                                    :class="
                                        completionPercentage === 100
                                            ? 'text-green-600'
                                            : 'text-orange-600'
                                    "
                                >
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
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import moment from "moment";

const props = defineProps({
    transfer: {
        type: Object,
        required: true,
    },
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

// Timeline status order
// Timeline status class function
const getTimelineStatusClass = (status) => {
    const currentStatusIndex = statusOrder.indexOf(props.transfer.status);
    const statusIndex = statusOrder.indexOf(status);

    // Handle rejected status
    if (props.transfer.status === "rejected") {
        if (status === "pending")
            return "bg-green-500 border-green-500 text-white"; // completed
        if (status === "reviewed")
            return "bg-green-500 border-green-500 text-white"; // completed
        if (status === "approved")
            return "bg-red-500 border-red-500 text-white"; // rejected
        return "bg-gray-100 border-gray-300 text-gray-400"; // not applicable
    }

    // Normal progression
    if (statusIndex <= currentStatusIndex) {
        return "bg-green-500 border-green-500 text-white"; // completed
    } else if (statusIndex === currentStatusIndex + 1) {
        return "bg-orange-500 border-orange-500 text-white"; // current/next
    } else {
        return "bg-gray-100 border-gray-300 text-gray-400"; // not reached
    }
};

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
</script>
