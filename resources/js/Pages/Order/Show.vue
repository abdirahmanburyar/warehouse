<template>
    <AuthenticatedLayout title="Orders" img="/assets/images/orders.png">
        <!-- Order Header -->
        <div v-if="props.error">
            {{ props.error }}
        </div>
    <div v-else>
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('orders.index')">Back to orders</Link>
                    <h1 class="text-xs font-semibold text-gray-900">
                        Order ID. {{ props.order.order_number }}
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span
                        :class="[
                            statusClasses[props.order.status] ||
                                statusClasses.default,
                        ]"
                        class="flex items-center text-xs font-bold px-4 py-2"
                    >
                        <!-- Status Icon -->
                        <span class="mr-3">
                            <!-- Pending Icon -->
                            <img
                                v-if="props.order.status === 'pending'"
                                src="/assets/images/pending.png"
                                class="w-4 h-4"
                                alt="Pending"
                            />

                            <!-- reviewed Icon -->
                            <img
                                v-else-if="props.order.status === 'reviewed'"
                                src="/assets/images/reviewed.png"
                                class="w-4 h-4"
                                alt="Reviewed"
                            />

                             <!-- Approved Icon -->
                             <img
                                v-else-if="props.order.status === 'approved'"
                                src="/assets/images/approved.png"
                                class="w-4 h-4"
                                alt="Approved"
                            />

                            <!-- In Process Icon -->
                            <img
                                v-else-if="props.order.status === 'in_process'"
                                src="/assets/images/inprocess.png"
                                class="w-4 h-4"
                                alt="In Process"
                            />

                            <!-- Dispatched Icon -->
                            <img
                                v-else-if="props.order.status === 'dispatched'"
                                src="/assets/images/dispatch.png"
                                class="w-4 h-4"
                                alt="Dispatched"
                            />

                            <!-- Received Icon -->
                            <img
                                v-else-if="props.order.status === 'received'"
                                src="/assets/images/received.png"
                                class="w-4 h-4"
                                alt="Received"
                            />

                            <!-- Rejected Icon -->
                            <svg
                                v-else-if="props.order.status === 'rejected'"
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
                        {{ props.order.status.toUpperCase() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            <!-- Facility Information -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <h2 class="text-lg font-medium text-gray-900">
                    Facility Details
                </h2>
                <div class="flex items-center">
                    <BuildingOfficeIcon class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-600">{{
                        props.order.facility.name
                    }}</span>
                </div>
                <div class="flex items-center">
                    <EnvelopeIcon class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-600">{{
                        props.order.facility.email
                    }}</span>
                </div>
                <div class="flex items-center">
                    <PhoneIcon class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-600">{{
                        props.order.facility.phone
                    }}</span>
                </div>
                <div class="flex items-center">
                    <MapPinIcon class="h-4 w-4 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-600"
                        >{{ props.order.facility.address }},
                        {{ props.order.facility.city }}</span
                    >
                </div>
            </div>
            <div>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <h2 class="text-xs font-medium text-gray-900">
                        Order Details
                    </h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Order Type
                            </p>
                            <p class="text-xs text-gray-900">
                                {{ props.order.order_type }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Order Date
                            </p>
                            <p class="text-xs text-gray-900">
                                {{ formatDate(props.order.order_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500">
                                Expected Date
                            </p>
                            <p class="text-xs text-gray-900">
                                {{ formatDate(props.order.expected_date) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Stage Timeline -->
        <div class="col-span-2 mb-6">
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
                            (statusOrder.indexOf(props.order.status) /
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('pending')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/pending.png"
                                class="w-7 h-7"
                                alt="Pending"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('pending')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('pending')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('reviewed')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/review.png"
                                class="w-7 h-7"
                                alt="Reviewed"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('reviewed')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('reviewed')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('approved')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/approved.png"
                                class="w-7 h-7"
                                alt="Approved"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('approved')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('approved')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('in_process')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/inprocess.png"
                                class="w-7 h-7"
                                alt="In Process"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('in_process')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('in_process')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('dispatched')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/dispatch.png"
                                class="w-7 h-7"
                                alt="Dispatch"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('dispatched')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('dispatched')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('delivered')
                                    ? 'bg-white border-orange-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/delivery.png"
                                class="w-7 h-7"
                                alt="Dispatch"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('delivered')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('delivered')
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
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('received')
                                    ? 'bg-white border-green-500'
                                    : 'bg-white border-gray-200',
                            ]"
                        >
                            <img
                                src="/assets/images/received.png"
                                class="w-7 h-7"
                                alt="Received"
                                :class="
                                    statusOrder.indexOf(props.order.status) >=
                                    statusOrder.indexOf('received')
                                        ? ''
                                        : 'opacity-40'
                                "
                            />
                        </div>
                        <span
                            class="mt-3 text-xs font-bold"
                            :class="
                                statusOrder.indexOf(props.order.status) >=
                                statusOrder.indexOf('received')
                                    ? 'text-green-600'
                                    : 'text-gray-500'
                            "
                            >Received</span
                        >
                    </div>
                </div>
            </div>
        </div>
        <!-- Order Items Table -->
        <h2 class="text-xs text-gray-900 mb-4">Order Items</h2>
        <table class="min-w-full border border-black border-collapse">
            <thead class="bg-blue-500">
                <tr class="bg-gray-50">
                    <th
                        class="px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        Item
                    </th>
                    <th
                        class="px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        Category
                    </th>
                    <th
                        class="px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        AMC
                    </th>
                    <th
                        class="px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        No. of Days
                    </th>
                    <th
                        class="px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        Ordered Quantity
                    </th>
                    <th
                        class="w-[150px] px-2 py-2 text-left text-xs text-black capitalize border border-black"
                    >
                        Quantity to release
                    </th>
                    <th
                        class="px-2 py-2 text-center text-xs text-black capitalize border border-black"
                    >
                        Item Detail
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="(item, index) in form"
                    :key="item.id"
                    class="hover:bg-gray-50 transition-colors duration-150"
                >
                    <td
                        class="px-3 py-3 text-xs text-gray-900 border border-black"
                    >
                        {{ item.product?.name }}
                    </td>
                    <td
                        class="px-3 py-3 text-xs text-gray-900 border border-black"
                    >
                        {{ item.product?.category?.name }}
                    </td>
                    <td
                        class="px-3 py-3 text-xs text-gray-900 border border-black"
                    >
                        <div class="flex flex-col space-y-1 text-xs">
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
                    <td
                        class="px-3 py-3 text-sm text-gray-900 border border-black"
                    >
                        {{ item.no_of_days }}/30
                    </td>
                    <td
                        class="px-3 py-3 text-lg text-center text-black border border-black"
                    >
                        {{ item.quantity }}
                    </td>
                    <td
                        class="px-3 py-3 text-xs text-gray-900 border border-black"
                    >
                        <input
                            type="number"
                            placeholder="0"
                            v-model="item.quantity_to_release"
                            @keydown.enter="
                                updateQuantity(item, 'quantity_to_release')
                            "
                            :readonly="
                                isUpading || props.order.status != 'pending'
                            "
                            class="w-full rounded-md border border-gray-300 focus:border-orange-500 focus:ring-orange-500 sm:text-sm mb-1"
                        />
                        <label for="received_quantity">Received Quantity</label>
                        <input
                            type="number"
                            placeholder="0"
                            v-model="item.received_quantity"
                            readonly
                            class="w-full rounded-md border border-gray-300 focus:border-orange-500 focus:ring-orange-500 sm:text-sm mb-1"
                        />
                        <label for="days">No. of Days</label>
                        <input
                            type="number"
                            placeholder="0"
                            v-model="item.days"
                            @keydown.enter="updateQuantity(item, 'days')"
                            :readonly="
                                isUpading || props.order.status != 'pending'
                            "
                            class="w-full rounded-md border border-gray-300 focus:border-orange-500 focus:ring-orange-500 sm:text-sm mb-1"
                        />
                        <span v-if="isUpading" class="text-green-500 text-md"
                            >Updating...</span
                        >
                        <button
                            @click="showBackOrderModal(item)"
                            v-if="
                                item.inventory_allocations &&
                                item.inventory_allocations.some(
                                    (a) => a.back_order?.length > 0
                                )
                            "
                            class="text-xs text-orange-600 underline hover:text-orange-800 cursor-pointer mt-1"
                        >
                            Show Back Order
                        </button>
                    </td>
                    <td class="text-xs text-gray-900 border border-black">
                        <table class="min-w-full border border-black">
                            <thead>
                                <tr>
                                    <th
                                        class="text-xs border border-black px-2 py-1"
                                    >
                                        QTY
                                    </th>
                                    <th
                                        class="text-xs border border-black px-2 py-1"
                                    >
                                        Uom
                                    </th>
                                    <th
                                        class="text-xs border border-black px-2 py-1"
                                    >
                                        Batch Number
                                    </th>
                                    <th
                                        class="text-xs border border-black px-2 py-1"
                                    >
                                        Expiry Date
                                    </th>
                                    <th
                                        class="text-xs border border-black px-2 py-1"
                                    >
                                        S. Location
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <tr
                                    v-for="inv in item.inventory_allocations"
                                    :key="inv.id"
                                    class="hover:bg-gray-100"
                                >
                                    <td
                                        class="px-2 py-1 text-xs border border-black"
                                    >
                                        {{ inv.allocated_quantity }}
                                    </td>
                                    <td
                                        class="px-2 py-1 text-xs border border-black"
                                    >
                                        {{ inv.uom }}
                                    </td>
                                    <td
                                        class="px-2 py-1 text-xs border border-black"
                                    >
                                        {{ inv.batch_number }}
                                    </td>
                                    <td
                                        class="px-2 py-1 text-xs border border-black"
                                    >
                                        {{
                                            moment(inv.expiry_date).format(
                                                "DD/MM/YYYY"
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-2 py-1 text-xs border border-black"
                                    >
                                        <div class="flex flex-col">
                                            <span
                                                >WH:
                                                {{ inv.warehouse?.name }}</span
                                            >
                                            <span
                                                >LC:
                                                {{
                                                    inv.location?.location
                                                }}</span
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- dispatch information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
            <div
                v-for="dispatch in props.order.dispatch"
                :key="dispatch.id"
                class="bg-white rounded-lg shadow-lg"
            >
                <div class="p-5">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Order #{{ dispatch.order_id }}
                        </h3>
                        <span class="text-sm text-gray-500">
                            {{
                                new Date(
                                    dispatch.created_at
                                ).toLocaleDateString()
                            }}
                        </span>
                    </div>

                    <!-- Driver Info -->
                    <div class="text-sm text-gray-600 space-y-1 mb-4">
                        <p>
                            <span class="font-medium text-gray-700"
                                >Driver:</span
                            >
                            {{ dispatch.driver_name }}
                        </p>
                        <p>
                            <span class="font-medium text-gray-700"
                                >Phone:</span
                            >
                            {{ dispatch.driver_number }}
                        </p>
                        <p>
                            <span class="font-medium text-gray-700"
                                >Plate #:</span
                            >
                            {{ dispatch.plate_number }}
                        </p>
                    </div>

                    <!-- Dispatch Details -->
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            <span class="text-gray-500">Cartons</span>
                            <div class="font-semibold text-gray-800">
                                {{ dispatch.no_of_cartoons }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                Order Status Actions
            </h3>
            <div class="flex justify-center items-center mb-6">
                <!-- Status Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <!-- Pending status indicator -->
                    <div class="relative">
                        <div class="flex flex-col">
                            <button
                                :class="[
                                    props.order.status === 'pending'
                                        ? 'bg-green-500 hover:bg-green-600'
                                        : statusOrder.indexOf(props.order.status) >
                                          statusOrder.indexOf('pending')
                                    ? 'bg-green-500'
                                    : 'bg-gray-300 cursor-not-allowed',
                            ]"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                            disabled
                        >
                            <img
                                src="/assets/images/pending.png"
                                class="w-5 h-5 mr-2"
                                alt="Pending"
                            />
                            <span class="text-sm font-bold text-white"
                                >Pending since {{ moment(props.order.created_at).format('DD/MM/YYYY HH:mm') }}</span
                            >
                        </button>
                        </div>
                        <span v-show="props.order?.user" class="text-sm text-gray-600">
                            By {{ props.order.user?.name || 'System' }}
                        </span>
                    </div>
                    <!-- Review button -->
                    <div class="relative">
                        <div class="flex flex-col">
                            <button
                            @click="
                                changeStatus(
                                    props.order.id,
                                    'reviewed',
                                    'is_reviewing'
                                )
                            "
                            :disabled="
                                isType['is_reviewing'] ||
                                props.order.status !== 'pending'
                            "
                            :class="[
                                props.order.status === 'pending'
                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                    : statusOrder.indexOf(props.order.status) >
                                      statusOrder.indexOf('pending')
                                    ? 'bg-green-500'
                                    : 'bg-gray-300 cursor-not-allowed',
                            ]"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                        >
                            <img
                                src="/assets/images/review.png"
                                class="w-5 h-5 mr-2"
                                alt="Review"
                            />
                            <span class="text-sm font-bold text-white">{{
                                statusOrder.indexOf(props.order.status) >
                                statusOrder.indexOf("pending")
                                    ? "Reviewed on" + moment(props.order.reviewed_at).format('DD/MM/YYYY HH:mm')
                                    : isType["is_reviewing"]
                                    ? "Please Wait..."
                                    : "Review"
                            }}</span>
                        </button>
                        <span v-show="props.order?.reviewed_by" class="text-sm text-gray-600">
                           By {{ props.order?.reviewed_by?.name }}
                        </span>
                        </div>
                        <div
                            v-if="props.order.status === 'pending'"
                            class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                        ></div>
                    </div>
                    
                    <!-- Approved button -->
                    <div class="relative">
                        <div class="flex flex-col">
                        <button
                            @click="
                                changeStatus(
                                    props.order.id,
                                    'approved',
                                    'is_approve'
                                )
                            "
                            :disabled="
                                isType['is_approve'] ||
                                props.order.status !== 'reviewed'
                            "
                            :class="[
                                props.order.status == 'reviewed'
                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                    : statusOrder.indexOf(props.order.status) >
                                      statusOrder.indexOf('reviewed')
                                    ? 'bg-green-500'
                                    : 'bg-gray-300 cursor-not-allowed',
                            ]"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                        >
                            <svg
                                v-if="
                                    isLoading &&
                                    props.order.status === 'reviewed'
                                "
                                class="animate-spin h-5 w-5 mr-2"
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
                            <template v-else>
                                <img
                                    src="/assets/images/approved.png"
                                    class="w-5 h-5 mr-2"
                                    alt="Approve"
                                />
                                <span class="text-sm font-bold text-white">{{
                                    statusOrder.indexOf(props.order.status) >
                                    statusOrder.indexOf("reviewed")
                                        ? "Approved on" + moment(props.order?.approved_at).format('DD/MM/YYYY HH:mm')
                                        :  isType["is_approve"] ? "Please Wait..." : "Approve"
                                }}</span>
                            </template>
                        </button>
                        <span v-show="props.order?.approved_by" class="text-sm text-gray-600">
                           By {{ props.order?.approved_by?.name }}
                        </span>
                    </div>
                        <div
                            v-if="props.order.status === 'reviewed'"
                            class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                        ></div>
                    </div>

                    <!-- Process button -->
                    <div class="relative">
                        <div class="flex flex-col">                            
                        <button
                            @click="
                                changeStatus(
                                    props.order.id,
                                    'in_process',
                                    'is_process'
                                )
                            "
                            :disabled="
                                isType['is_process'] ||
                                props.order.status !== 'approved'
                            "
                            :class="[
                                props.order.status === 'approved'
                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                    : statusOrder.indexOf(props.order.status) >
                                      statusOrder.indexOf('approved')
                                    ? 'bg-green-500'
                                    : 'bg-gray-300 cursor-not-allowed',
                            ]"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                        >
                            <svg
                                v-if="
                                    isType['is_process'] &&
                                    props.order.status == 'approved'
                                "
                                class="animate-spin h-5 w-5 mr-2"
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
                            <template v-else>
                                <img
                                    src="/assets/images/inprocess.png"
                                    class="w-5 h-5 mr-2"
                                    alt="Process"
                                />
                                <span class="text-sm font-bold text-white">{{
                                    statusOrder.indexOf(props.order.status) >
                                    statusOrder.indexOf("approved")
                                        ? "Processed by" + moment(props.order?.processed_at).format('DD/MM/YYYY HH:mm')
                                        : isType['is_process'] ? "Please Wait..." : "Process"
                                }}</span>
                            </template>
                        </button>
                        <span v-show="props.order?.processed_by" class="text-sm text-gray-600">
                            By {{ props.order?.processed_by?.name }}
                        </span>
                    </div>
                        <div
                            v-if="props.order.status === 'approved'"
                            class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                        ></div>
                    </div>

                    <!-- Dispatch button -->
                    <div class="relative">
                        <button
                            @click="showDispatchForm = true"
                            :disabled="
                                isType['is_dispatch'] ||
                                props.order.status !== 'in_process'
                            "
                            :class="[
                                props.order.status === 'in_process'
                                    ? 'bg-yellow-500 hover:bg-yellow-600'
                                    : statusOrder.indexOf(props.order.status) >
                                      statusOrder.indexOf('in_process')
                                    ? 'bg-green-500'
                                    : 'bg-gray-300 cursor-not-allowed',
                            ]"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                        >
                            <svg
                                v-if="
                                    isType['is_dispatch'] &&
                                    props.order.status === 'in_process'
                                "
                                class="animate-spin h-5 w-5 mr-2"
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
                            <template v-else>
                                <img
                                    src="/assets/images/dispatch.png"
                                    class="w-5 h-5 mr-2"
                                    alt="Dispatch"
                                />
                                <span class="text-sm font-bold text-white">{{
                                    statusOrder.indexOf(props.order.status) >
                                    statusOrder.indexOf("in_process")
                                        ? "Dispatched on " + moment(props.order.dispatched_at).format('DD/MM/YYYY HH:mm')
                                        : isType['is_dispatch'] ? "Please Wait..." : "Dispatch"
                                }}</span>
                            </template>
                        </button>
                        <div
                            v-if="props.order.status === 'in_process'"
                            class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                        ></div>
                    </div>

                    <!-- Order Delivery Indicators -->
                    <div class="flex flex-col gap-4 sm:flex-row">
                        <!-- Delivered Status -->
                        <div class="relative">
                            <button
                                :class="[
                                    props.order.status === 'dispatched'
                                        ? 'bg-[#f59e0b] hover:bg-[#d97706]'
                                        : statusOrder.indexOf(
                                              props.order.status
                                          ) > statusOrder.indexOf('dispatched')
                                        ? 'bg-[#55c5ff]'
                                        : 'bg-gray-300 cursor-not-allowed',
                                ]"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                                disabled
                            >
                                <img
                                    src="/assets/images/delivery.png"
                                    class="w-5 h-5 mr-2"
                                    alt="dispatched"
                                />
                                <span class="text-sm font-bold text-white">
                                    {{
                                        statusOrder.indexOf(
                                            props.order.status
                                        ) > statusOrder.indexOf("dispatched")
                                            ? "dispatched"
                                            : "Waiting to be Delivered"
                                    }}
                                </span>
                            </button>

                            <!-- Pulse Indicator if currently at this status -->
                            <div
                                v-if="props.order.status === 'dispatched'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                            ></div>
                        </div>

                        <!-- Received Status -->
                        <div class="relative">
                            <button
                                :class="[
                                    props.order.status === 'delivered'
                                        ? 'bg-[#f59e0b] hover:bg-[#d97706]'
                                        : statusOrder.indexOf(
                                              props.order.status
                                          ) > statusOrder.indexOf('delivered')
                                        ? 'bg-[#55c5ff]'
                                        : 'bg-gray-300 cursor-not-allowed',
                                ]"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                                disabled
                            >
                                <img
                                    src="/assets/images/received.png"
                                    class="w-5 h-5 mr-2"
                                    alt="Received"
                                />
                                <span class="text-sm font-bold text-white">
                                    {{
                                        statusOrder.indexOf(
                                            props.order.status
                                        ) > statusOrder.indexOf("received")
                                            ? "Received"
                                            : statusOrder.indexOf("delivered")
                                            ? "Waiting to be Delivered"
                                            : "Received"
                                    }}
                                </span>
                            </button>

                            <!-- Pulse Indicator if currently at this status -->
                            <div
                                v-if="props.order.status === 'delivered'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                            ></div>
                        </div>
                    </div>

                    <!-- Reject button (only available for pending status) -->
                    <div
                        class="relative"
                        v-if="props.order.status === 'pending'"
                    >
                        <button
                            @click="
                                changeStatus(
                                    props.order.id,
                                    'rejected',
                                    'is_reject'
                                )
                            "
                            :disabled="isType['is_reject'] || isLoading"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white bg-red-600 hover:bg-red-700 min-w-[160px]"
                        >
                            <svg
                                v-if="isType['is_reject']"
                                class="animate-spin h-5 w-5 mr-2"
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
                            <template v-else>
                                <svg
                                    class="w-5 h-5 mr-2 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                <span class="text-sm font-bold text-white"
                                    >Reject</span
                                >
                            </template>
                        </button>
                    </div>

                    <!-- Status indicator for rejected status -->
                    <div
                        v-if="props.order.status === 'rejected'"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-100 text-red-800 min-w-[160px]"
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
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        <span class="text-sm font-bold">Rejected</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Order Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
        >
            <div
                class="bg-white rounded-lg shadow-xl w-full h-full overflow-hidden"
            >
                <div
                    class="p-4 border-b border-gray-200 flex justify-between items-center"
                >
                    <h3 class="text-lg font-medium text-gray-900">
                        Back Order Details
                    </h3>
                    <button
                        @click="showModal = false"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto h-full">
                    <div class="mb-4">
                        <span class="text-sm font-medium text-gray-700"
                            >Order #{{ props.order.id }}</span
                        >
                        <h2 class="text-xl font-bold text-gray-900">
                            Back Ordered Items
                        </h2>
                    </div>
                    <div class="overflow-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 border border-gray-200"
                        >
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200"
                                    >
                                        Requested Quantity
                                    </th>
                                    <th
                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Back Order
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="item in selectedBackOrder"
                                    :key="item.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="px-3 py-3 text-sm text-gray-900 border-r border-gray-200"
                                    >
                                        {{ item.allocated_quantity }}
                                    </td>
                                    <td class="px-3 py-3 text-sm text-gray-900">
                                        <table class="min-w-full">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th
                                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        Quantity
                                                    </th>
                                                    <th
                                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        Type
                                                    </th>
                                                    <th
                                                        class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        Notes
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white">
                                                <tr
                                                    v-for="backOrder in item.back_order"
                                                    :key="backOrder.id"
                                                >
                                                    <td
                                                        class="px-3 py-3 text-sm text-gray-900"
                                                    >
                                                        {{ backOrder.quantity }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-3 text-sm text-gray-900"
                                                    >
                                                        {{ backOrder.type }}
                                                    </td>
                                                    <td
                                                        class="px-3 py-3 text-sm text-gray-900"
                                                    >
                                                        {{ backOrder.notes }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            These items have been partially received. The
                            remaining quantities are on back order.
                        </p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-200 flex justify-end">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors duration-150"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showDispatchForm" @close="showDispatchForm = false">
            <div class="p-6 bg-white rounded-md shadow-md">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    Dispatch Information
                </h2>

                <!-- Driver Name -->
                <div class="mb-4">
                    <label
                        for="driver_name"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Driver Name
                    </label>
                    <input
                        id="driver_name"
                        type="text"
                        v-model="dispatchForm.driver_name"
                        required
                        placeholder="Enter driver name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Driver Phone Number -->
                <div class="mb-4">
                    <label
                        for="driver_number"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Driver Phone Number
                    </label>
                    <input
                        id="driver_number"
                        type="tel"
                        v-model="dispatchForm.driver_number"
                        placeholder="Enter driver phone number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Vehicle Plate Number -->
                <div class="mb-4">
                    <label
                        for="plate_number"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        Vehicle Plate Number
                    </label>
                    <input
                        id="plate_number"
                        type="text"
                        v-model="dispatchForm.plate_number"
                        placeholder="Enter plate number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Number of Cartons -->
                <div class="mb-6">
                    <label
                        for="no_of_cartoons"
                        class="block text-sm font-medium text-gray-700 mb-1"
                    >
                        No. of Cartons
                    </label>
                    <input
                        id="no_of_cartoons"
                        type="number"
                        min="0"
                        v-model="dispatchForm.no_of_cartoons"
                        placeholder="Enter number of cartons"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDispatchForm = false"
                        :disabled="isSaving"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createDispatch"
                        :disabled="isSaving"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                    >
                        {{ isSaving ? "Processing..." : "Save and Dispatch" }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed, onMounted, ref, h } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { router, Link } from "@inertiajs/vue3";
import {
    BuildingOfficeIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
} from "@heroicons/vue/24/outline";
import Swal from "sweetalert2";
import axios from "axios";
import moment from "moment";
import Modal from "@/Components/Modal.vue";
import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    error: String,
    products: Array,
});

const showModal = ref(false);
const selectedBackOrder = ref(null);
const showDispatchForm = ref(false);

// Function to show the back order modal
const showBackOrderModal = (item) => {
    selectedBackOrder.value = null;
    showModal.value = true;
    selectedBackOrder.value = item?.inventory_allocations;
};

const statusClasses = {
    pending: "bg-yellow-100 text-yellow-800 rounded-full font-bold",
    reviewed: "bg-green-100 text-green-800 rounded-full font-bold",
    approved: "bg-green-100 text-green-800 rounded-full font-bold",
    "in process": "bg-blue-100 text-blue-800 rounded-full font-bold",
    dispatched: "bg-purple-100 text-purple-800 rounded-full font-bold",
    delivered: "bg-purple-100 text-purple-800 rounded-full font-bold",
    received:
        "bg-green-100 text-green-800 rounded-full font-bold flex items-center",
};

const form = ref([]);
const isLoading = ref(false);

onMounted(() => {
    form.value = props.order.items || [];
});

const formatDate = (date) => {
    return moment(date).format("DD/MM/YYYY");
};

const statusOrder = [
    "pending",
    "reviewed",
    "approved",
    "in_process",
    "dispatched",
    "delivered",
    "received",
];

// update quantity
const isUpading = ref(false);
async function updateQuantity(item, type) {
    isUpading.value = true;
    await axios
        .post(route("orders.update-quantity"), {
            item_id: item.id,
            quantity: item.quantity_to_release,
            days: item.days,
            type,
        })
        .then((response) => {
            isUpading.value = false;
            Swal.fire({
                title: "Success!",
                text: response.data,
                icon: "success",
                confirmButtonText: "OK",
            }).then(() => {
                router.get(route("orders.show", props.order.id));
            });
        })
        .catch((error) => {
            isUpading.value = false;
            console.log(error);
            toast.error(error.response?.data || "Failed to update quantity");
        });
}

const dispatchForm = ref({
    driver_name: "",
    driver_number: "",
    plate_number: "",
    no_of_cartoons: "",
    order_id: props.order.id,
    status: "Dispatched",
});

const isSaving = ref(false);

async function createDispatch() {
    isSaving.value = true;
    await axios
        .post(route("orders.dispatch-info"), dispatchForm.value)
        .then((response) => {
            isSaving.value = false;
            showDispatchForm.value = false;
            Swal.fire({
                title: "Success!",
                text: response.data,
                icon: "success",
                confirmButtonText: "OK",
            }).then(() => {
                router.get(route("orders.show", props.order.id));
            });
        })
        .catch((error) => {
            isSaving.value = false;
            console.log(error);
            toast.error(error.response?.data || "Failed to create dispatch");
        });
}

const isType = ref([]);
// Function to change order status
const changeStatus = (orderId, newStatus, type) => {
    console.log(orderId, newStatus, type);
    Swal.fire({
        title: "Are you sure?",
        text: `Do you want to change the order status to ${newStatus}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, change it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            // Set loading state
            isType.value[type] = true;

            await axios
                .post(route("orders.change-status"), {
                    order_id: orderId,
                    status: newStatus,
                })
                .then((response) => {
                    // Reset loading state
                    isType.value[type] = false;

                    Swal.fire({
                        title: "Updated!",
                        text: "Order status has been updated.",
                        icon: "success",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
                    }).then(() => {
                        // Reload the page to show the updated status
                        router.get(route("orders.show", props.order.id));
                    });
                })
                .catch((error) => {
                    // Reset loading state
                    isType.value[type] = false;

                    Swal.fire({
                        title: "Error!",
                        text:
                            error.response?.data ||
                            "Failed to update order status",
                        icon: "error",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000,
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
        isPast: index < currentIndex,
    }));
};

const statusProgress = computed(() => getStatusProgress(props.order.status));
</script>
