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
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">From</h3>
                        <p class="text-gray-600">{{ props.transfer.from_warehouse?.name || props.transfer.from_facility?.name || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">To</h3>
                        <p class="text-gray-600">{{ props.transfer.to_warehouse?.name || props.transfer.to_facility?.name || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Transfer Approval Section -->
            <div class="mt-8 mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Transfer Approval
                </h3>
                
                <!-- Transfer Approval Actions -->
                <div class="flex justify-center items-center mb-6">
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <!-- Pending Approval -->
                        <div class="relative">
                            <div class="flex flex-col items-center">
                                <button
                                    :class="[
                                        props.transfer.status === 'pending'
                                            ? 'bg-yellow-500 hover:bg-yellow-600'
                                            : statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending')
                                            ? 'bg-green-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[140px]"
                                    disabled
                                >
                                    <img
                                        src="/assets/images/pending.png"
                                        class="w-5 h-5 mr-2"
                                        alt="Pending"
                                    />
                                    <span class="text-sm font-bold">Pending</span>
                                </button>
                                <span v-show="props.transfer?.user" class="text-xs text-gray-600 mt-1">
                                    By {{ props.transfer.user?.name || 'System' }}
                                </span>
                            </div>
                            <!-- Pulse indicator for pending -->
                            <div
                                v-if="props.transfer.status === 'pending'"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                            ></div>
                        </div>

                        <!-- Approve Button -->
                        <div class="relative">
                            <div class="flex flex-col items-center">
                                <button
                                    @click="changeStatus(props.transfer.id, 'approved', 'is_approve')"
                                    :disabled="
                                        isType['is_approve'] ||
                                        props.transfer.status !== 'pending' ||
                                        !$page.props.auth.can.transfer_approve
                                    "
                                    :class="[
                                        props.transfer.status === 'pending' && $page.props.auth.can.transfer_approve
                                            ? 'bg-green-500 hover:bg-green-600'
                                            : statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending')
                                            ? 'bg-green-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[140px]"
                                >
                                    <svg
                                        v-if="isType['is_approve'] && props.transfer.status === 'pending'"
                                        class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
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
                                        <span class="text-sm font-bold">{{
                                            statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending')
                                                ? `Approved on ${moment(props.transfer?.approved_at).format('DD/MM/YYYY')}`
                                                : isType['is_approve'] ? "Please Wait..." : "Approve"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.approved_by" class="text-xs text-gray-600 mt-1">
                                    By {{ props.transfer?.approved_by?.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Reject Button -->
                        <div class="relative">
                            <div class="flex flex-col items-center">
                                <button
                                    @click="changeStatus(props.transfer.id, 'rejected', 'is_reject')"
                                    :disabled="
                                        isType['is_reject'] ||
                                        props.transfer.status !== 'pending' ||
                                        !$page.props.auth.can.transfer_approve
                                    "
                                    :class="[
                                        props.transfer.status === 'pending' && $page.props.auth.can.transfer_approve
                                            ? 'bg-red-500 hover:bg-red-600'
                                            : props.transfer.status === 'rejected'
                                            ? 'bg-red-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[140px]"
                                >
                                    <svg
                                        v-if="isType['is_reject'] && props.transfer.status === 'pending'"
                                        class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
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
                                        <span class="text-sm font-bold">{{
                                            props.transfer.status === 'rejected'
                                                ? `Rejected on ${moment(props.transfer?.rejected_at).format('DD/MM/YYYY')}`
                                                : isType['is_reject'] ? "Please Wait..." : "Reject"
                                        }}</span>
                                    </template>
                                </button>
                                <span v-show="props.transfer?.rejected_by" class="text-xs text-gray-600 mt-1">
                                    By {{ props.transfer?.rejected_by?.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="mt-6 flex justify-center">
                    <div class="flex items-center space-x-4">
                        <!-- Pending Icon -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="props.transfer.status === 'pending' ? 'bg-yellow-500' : 'bg-green-500'">
                                <img src="/assets/images/pending.png" class="w-5 h-5" alt="Pending" />
                            </div>
                            <span class="text-xs text-gray-600 mt-1">Pending</span>
                        </div>

                        <!-- Arrow -->
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>

                        <!-- Approved/Rejected Icon -->
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                 :class="{
                                     'bg-green-500': props.transfer.status === 'approved' || statusOrder.indexOf(props.transfer.status) > statusOrder.indexOf('pending'),
                                     'bg-red-500': props.transfer.status === 'rejected',
                                     'bg-gray-300': props.transfer.status === 'pending'
                                 }">
                                <img v-if="props.transfer.status === 'rejected'" 
                                     src="/assets/images/rejected.png" class="w-5 h-5" alt="Rejected" />
                                <img v-else 
                                     src="/assets/images/approved.png" class="w-5 h-5" alt="Approved" />
                            </div>
                            <span class="text-xs text-gray-600 mt-1">
                                {{ props.transfer.status === 'rejected' ? 'Rejected' : 'Approved' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer Status Actions -->
            <div class="mt-8 mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                    Transfer Status Actions
                </h3>
                <div class="flex justify-center items-center mb-6">
                    <!-- Status Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <!-- Pending status indicator -->
                        <div class="relative">
                            <div class="flex flex-col">
                                <button
                                    :class="[
                                        props.transfer.status === 'pending'
                                            ? 'bg-green-500 hover:bg-green-600'
                                            : statusOrder.indexOf(props.transfer.status) >
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
                                    >Pending since {{ moment(props.transfer.created_at).format('DD/MM/YYYY HH:mm') }}</span
                                >
                            </button>
                            </div>
                            <span v-show="props.transfer?.user" class="text-sm text-gray-600">
                                By {{ props.transfer.user?.name || 'System' }}
                            </span>
                        </div>

                        <!-- Process button (Sender only) -->
                        <div class="relative" v-if="canSenderAct">
                            <div class="flex flex-col">                            
                            <button
                                @click="changeStatus(props.transfer.id, 'in_process', 'is_process')"
                                :disabled="
                                    isType['is_process'] ||
                                    props.transfer.status !== 'pending'
                                "
                                :class="[
                                    props.transfer.status === 'pending'
                                        ? 'bg-yellow-500 hover:bg-yellow-600'
                                        : statusOrder.indexOf(props.transfer.status) >
                                          statusOrder.indexOf('pending')
                                        ? 'bg-green-500'
                                        : 'bg-gray-300 cursor-not-allowed',
                                ]"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                            >
                                <svg
                                    v-if="
                                        isType['is_process'] &&
                                        props.transfer.status == 'pending'
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
                                        statusOrder.indexOf(props.transfer.status) >
                                        statusOrder.indexOf("pending")
                                            ? "Processed on " + moment(props.transfer?.processed_at).format('DD/MM/YYYY HH:mm')
                                            : isType['is_process'] ? "Please Wait..." : "Process"
                                    }}</span>
                                </template>
                            </button>
                            <span v-show="props.transfer?.processed_by" class="text-sm text-gray-600">
                                By {{ props.transfer?.processed_by?.name }}
                            </span>
                        </div>
                            <div
                                v-if="props.transfer.status === 'pending' && canSenderAct"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                            ></div>
                        </div>

                        <!-- Dispatch button (Sender only) -->
                        <div class="relative" v-if="canSenderAct">
                            <div class="flex flex-col">
                            <button
                                @click="changeStatus(props.transfer.id, 'dispatched', 'is_dispatch')"
                                :disabled="
                                    isType['is_dispatch'] ||
                                    props.transfer.status !== 'in_process'
                                "
                                :class="[
                                    props.transfer.status === 'in_process'
                                        ? 'bg-yellow-500 hover:bg-yellow-600'
                                        : statusOrder.indexOf(props.transfer.status) >
                                          statusOrder.indexOf('in_process')
                                        ? 'bg-green-500'
                                        : 'bg-gray-300 cursor-not-allowed',
                                ]"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                            >
                                <svg
                                    v-if="
                                        isType['is_dispatch'] &&
                                        props.transfer.status === 'in_process'
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
                                        statusOrder.indexOf(props.transfer.status) >
                                        statusOrder.indexOf("in_process")
                                            ? "Dispatched on " + moment(props.transfer.dispatched_at).format('DD/MM/YYYY HH:mm')
                                            : isType['is_dispatch'] ? "Please Wait..." : "Dispatch"
                                    }}</span>
                                </template>
                            </button>
                            <span v-show="props.transfer?.dispatched_by" class="text-sm text-gray-600">
                                By {{ props.transfer?.dispatched_by?.name }}
                            </span>
                        </div>
                            <div
                                v-if="props.transfer.status === 'in_process' && canSenderAct"
                                class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                            ></div>
                        </div>

                        <!-- Delivery Actions (Receiver only) -->
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <!-- Delivered Status -->
                            <div class="relative" v-if="canReceiverAct">
                                <div class="flex flex-col">
                                <button
                                    @click="changeStatus(props.transfer.id, 'delivered', 'is_delivered')"
                                    :disabled="
                                        isType['is_delivered'] ||
                                        props.transfer.status !== 'dispatched'
                                    "
                                    :class="[
                                        props.transfer.status === 'dispatched'
                                            ? 'bg-yellow-500 hover:bg-yellow-600'
                                            : statusOrder.indexOf(
                                                  props.transfer.status
                                              ) > statusOrder.indexOf('dispatched')
                                            ? 'bg-green-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                                >
                                    <img
                                        src="/assets/images/delivery.png"
                                        class="w-5 h-5 mr-2"
                                        alt="delivered"
                                    />
                                    <span class="text-sm font-bold text-white">
                                        {{
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) > statusOrder.indexOf("dispatched")
                                                ? "Delivered on " + moment(props.transfer.delivered_at).format('DD/MM/YYYY HH:mm')
                                                : isType['is_delivered'] ? "Please Wait..." : "Mark Delivered"
                                        }}
                                    </span>
                                </button>
                                <span v-show="props.transfer?.delivered_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.delivered_by?.name }}
                                </span> 
                                </div>

                                <!-- Pulse Indicator if currently at this status -->
                                <div
                                    v-if="props.transfer.status === 'dispatched' && canReceiverAct"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                                ></div>
                            </div>

                            <!-- Received Status -->
                            <div class="relative" v-if="canReceiverAct">
                                <div class="flex flex-col">
                                <button
                                    @click="changeStatus(props.transfer.id, 'received', 'is_received')"
                                    :disabled="
                                        isType['is_received'] ||
                                        props.transfer.status !== 'delivered'
                                    "
                                    :class="[
                                        props.transfer.status === 'delivered'
                                            ? 'bg-yellow-400 hover:bg-yellow-500'
                                            : statusOrder.indexOf(
                                                  props.transfer.status
                                              ) > statusOrder.indexOf('delivered')
                                            ? 'bg-green-500'
                                            : 'bg-gray-300 cursor-not-allowed',
                                    ]"
                                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 text-white min-w-[160px]"
                                >
                                    <img
                                        src="/assets/images/received.png"
                                        class="w-5 h-5 mr-2"
                                        alt="Received"
                                    />
                                    <span class="text-sm font-bold text-white">
                                        {{
                                            statusOrder.indexOf(
                                                props.transfer.status
                                            ) > statusOrder.indexOf("delivered")
                                                ? "Received on " + moment(props.transfer.received_at).format('DD/MM/YYYY HH:mm')
                                                : isType['is_received'] ? "Please Wait..." : "Mark Received"
                                        }}
                                    </span>
                                </button>
                                <span v-show="props.transfer?.received_by" class="text-sm text-gray-600">
                                    By {{ props.transfer?.received_by?.name }}
                                </span> 
                                </div>

                                <!-- Pulse Indicator if currently at this status -->
                                <div
                                    v-if="props.transfer.status === 'delivered' && canReceiverAct"
                                    class="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-pulse"
                                ></div>
                            </div>
                        </div>

                        <!-- Status indicator for rejected status -->
                        <div
                            v-if="props.transfer.status === 'rejected'"
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

            <!-- Transfer Items -->
            <div class="mb-6 px-6 py-6 bg-white rounded-lg shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Transfer Items</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Product</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Quantity</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700" v-if="props.transfer.status === 'delivered' || props.transfer.status === 'received'">Received</th>
                                <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in props.transfer.items" :key="item.id" class="border-t">
                                <td class="px-4 py-2">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ item.product?.name }}</p>
                                        <p class="text-sm text-gray-500">{{ item.product?.code }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-gray-700">{{ item.quantity }}</td>
                                <td class="px-4 py-2" v-if="props.transfer.status === 'delivered' || props.transfer.status === 'received'">
                                    <input
                                        v-if="props.transfer.status === 'delivered' && canReceiverAct"
                                        type="number"
                                        :value="item.received_quantity || 0"
                                        @change="updateReceivedQuantity(item.id, $event.target.value)"
                                        class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                                        :min="0"
                                        :max="item.quantity"
                                    />
                                    <span v-else class="text-gray-700">{{ item.received_quantity || 0 }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex space-x-2">
                                        <button
                                            v-if="props.transfer.status === 'delivered' && (item.received_quantity || 0) < item.quantity"
                                            @click="openBackOrderModal(item)"
                                            class="px-3 py-1 bg-orange-500 text-white text-sm rounded hover:bg-orange-600"
                                        >
                                            Back Order
                                        </button>
                                        <button
                                            v-if="props.transfer.status !== 'received' && canSenderAct"
                                            @click="deleteTransferItem(item.id)"
                                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back Order Modal -->
            <div v-if="showBackOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Back Orders</h3>
                        
                        <div v-if="selectedItem" class="mb-4">
                            <p class="text-sm text-gray-600">
                                Product: <strong>{{ selectedItem.product?.name }}</strong><br>
                                Ordered: <strong>{{ selectedItem.quantity }}</strong><br>
                                Received: <strong>{{ selectedItem.received_quantity || 0 }}</strong><br>
                                Missing: <strong>{{ missingQuantity }}</strong>
                            </p>
                        </div>

                        <div class="mb-4">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-2 py-2 text-left text-sm font-medium text-gray-700">Quantity</th>
                                        <th class="px-2 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                                        <th class="px-2 py-2 text-left text-sm font-medium text-gray-700">Notes</th>
                                        <th class="px-2 py-2 text-left text-sm font-medium text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(backOrder, index) in backOrders" :key="index" class="border-t">
                                        <td class="px-2 py-2">
                                            <input
                                                v-model.number="backOrder.quantity"
                                                type="number"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                                :min="1"
                                                :max="missingQuantity"
                                            />
                                        </td>
                                        <td class="px-2 py-2">
                                            <select
                                                v-model="backOrder.type"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                            >
                                                <option value="">Select type</option>
                                                <option value="Missing">Missing</option>
                                                <option value="Damaged">Damaged</option>
                                                <option value="Wrong Item">Wrong Item</option>
                                            </select>
                                        </td>
                                        <td class="px-2 py-2">
                                            <input
                                                v-model="backOrder.notes"
                                                type="text"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                                placeholder="Optional notes"
                                            />
                                        </td>
                                        <td class="px-2 py-2">
                                            <button
                                                @click="removeBackOrderRow(index)"
                                                class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mb-4">
                            <button
                                @click="addBackOrderRow"
                                :disabled="!canAddMoreBackOrders"
                                class="px-4 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 disabled:bg-gray-300"
                            >
                                Add Row
                            </button>
                            <div class="text-sm text-gray-600">
                                Total: {{ totalBackOrderQuantity }} / {{ missingQuantity }}
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button
                                @click="showBackOrderModal = false"
                                class="px-4 py-2 bg-gray-300 text-gray-700 text-sm rounded hover:bg-gray-400"
                            >
                                Cancel
                            </button>
                            <button
                                @click="saveBackOrders"
                                :disabled="!isValidForSave || isSaving"
                                class="px-4 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 disabled:bg-gray-300"
                            >
                                {{ isSaving ? 'Saving...' : 'Save Back Orders' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  transfer: {
    type: Object,
    required: true
  },
  error: String
});

// Get current user context
const currentUserWarehouse = computed(() => usePage().props.warehouse);
const currentUserFacility = computed(() => usePage().props.auth.user.facility_id);

// Role-based permissions
const canSenderAct = computed(() => {
  return props.transfer.from_warehouse_id === currentUserWarehouse.value?.id ||
         props.transfer.from_facility_id === currentUserFacility.value;
});

const canReceiverAct = computed(() => {
  return props.transfer.to_warehouse_id === currentUserWarehouse.value?.id ||
         props.transfer.to_facility_id === currentUserFacility.value;
});

// Status configuration
const statusOrder = ['pending', 'in_process', 'dispatched', 'delivered', 'received'];

const statusClasses = {
  pending: 'bg-yellow-100 text-yellow-800',
  in_process: 'bg-blue-100 text-blue-800', 
  dispatched: 'bg-purple-100 text-purple-800',
  delivered: 'bg-orange-100 text-orange-800',
  received: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800'
};

// Loading states
const isType = ref({
  is_process: false,
  is_dispatch: false,
  is_delivered: false,
  is_received: false,
  is_approve: false,
  is_reject: false
});

// Main status change function
const changeStatus = async (transferId, newStatus, loadingKey) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: `Do you want to change the transfer status to ${newStatus}?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, change it!",
  });

  if (result.isConfirmed) {
    isType.value[loadingKey] = true;

    try {
      const response = await axios.post(route("transfers.changeItemStatus"), {
        transfer_id: transferId,
        status: newStatus,
      });

      await Swal.fire({
        title: "Updated!",
        text: response.data.message || "Transfer status updated successfully",
        icon: "success",
        timer: 3000,
      });

      router.reload();
    } catch (error) {
      await Swal.fire({
        title: "Error!",
        text: error.response?.data || "Failed to update transfer status",
        icon: "error",
        timer: 3000,
      });
    } finally {
      isType.value[loadingKey] = false;
    }
  }
};

// Update received quantity
const updateReceivedQuantity = async (itemId, quantity) => {
  try {
    await axios.post(route('transfers.items.update-received'), {
      item_id: itemId,
      received_quantity: parseInt(quantity)
    });
    
    toast.success('Received quantity updated');
  } catch (error) {
    toast.error('Failed to update received quantity');
    console.error(error);
  }
};

// Delete transfer item
const deleteTransferItem = async (itemId) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'This will permanently delete this transfer item.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    try {
      await axios.delete(route('transfers.items.delete', itemId));
      toast.success('Transfer item deleted successfully');
      router.reload();
    } catch (error) {
      toast.error('Failed to delete transfer item');
      console.error(error);
    }
  }
};

// Back order management
const showBackOrderModal = ref(false);
const selectedItem = ref(null);
const backOrders = ref([]);
const isSaving = ref(false);
const missingQuantity = computed(() => selectedItem.value ? selectedItem.value.quantity - (selectedItem.value.received_quantity || 0) : 0);
const totalBackOrderQuantity = computed(() => backOrders.value.reduce((acc, curr) => acc + (curr.quantity || 0), 0));
const canAddMoreBackOrders = computed(() => totalBackOrderQuantity.value < missingQuantity.value);

const openBackOrderModal = (item) => {
  selectedItem.value = item;
  backOrders.value = [];
  showBackOrderModal.value = true;
};

const addBackOrderRow = () => {
  backOrders.value.push({
    quantity: 1,
    type: '',
    notes: ''
  });
};

const removeBackOrderRow = (index) => {
  backOrders.value.splice(index, 1);
};

const saveBackOrders = async () => {
  isSaving.value = true;
  try {
    await axios.post(route('transfers.items.back-orders'), {
      item_id: selectedItem.value.id,
      back_orders: backOrders.value
    });
    
    toast.success('Back orders saved successfully');
    showBackOrderModal.value = false;
    router.reload();
  } catch (error) {
    toast.error('Failed to save back orders');
    console.error(error);
  } finally {
    isSaving.value = false;
  }
};

const isValidForSave = computed(() => {
  return backOrders.value.every((backOrder) => backOrder.quantity > 0 && backOrder.type !== '');
});
</script>