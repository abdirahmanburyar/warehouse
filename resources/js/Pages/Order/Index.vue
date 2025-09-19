<template>
    <Head title="All Orders" />
    <AuthenticatedLayout
        title="Track Your Orders"
        description="Keeping Essentials Ready, Every Time"
        img="/assets/images/orders.png"
    >
        <!-- Filters Section -->
        <div class="relative bg-white mb-2 text-xs">
            <div
                class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center mb-5"
            >
                <!-- Search -->
                <div class="relative w-full">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Search by Order No"
                        class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <svg
                        class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
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
                </div>

                <div class="w-full">
                    <Multiselect
                        v-model="region"
                        :options="props.regions"
                        :searchable="true"
                        :close-on-select="true"
                        :allow-empty="true"
                        @select="handleRegionSelect"
                        placeholder="Select Region"
                    >
                    </Multiselect>
                </div>

                <!-- District Filter -->
                <div>
                    <Multiselect
                        v-model="district"
                        :options="districts"
                        :searchable="true"
                        :close-on-select="true"
                        :allow-empty="true"
                        @select="handleDistrictSelect"
                        placeholder="Select District"
                    >
                    </Multiselect>
                </div>
                <!-- Facility Filter -->
                <div class="w-full">
                    <Multiselect
                        v-model="facility"
                        :options="facilities"
                        :searchable="true"
                        :close-on-select="true"
                        :allow-empty="true"
                        placeholder="Select Facility"
                    >
                    </Multiselect>
                </div>

                <!-- Order Type Filter -->
                <div class="w-full">
                    <Multiselect
                        v-model="orderType"
                        :options="orderTypes"
                        :searchable="true"
                        :close-on-select="true"
                        :allow-empty="true"
                        placeholder="Select Order Type"
                    >
                    </Multiselect>
                </div>

                <!-- Date From -->
                <div class="w-full flex items-center space-x-2">
                    <input
                        type="date"
                        v-model="dateFrom"
                        class="w-[300px] px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <span class="text-sm">To</span>
                    <input
                        type="date"
                        v-model="dateTo"
                        class="w-[300px] px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>
            <!-- Move Icon Legend Button to top right and remove text -->
            <div class="flex justify-end items-center gap-2">
                <select
                    v-model="per_page"
                    @change="props.filters.page = 1"
                    class="md:w-[200px] sm:w-[150px] xs:w-full border border-black rounded-3xl"
                >
                    <option value="10">10 Per page</option>
                    <option value="25">25 Per page</option>
                    <option value="50">50 Per page</option>
                    <option value="100">100 Per page</option>
                </select>
                <button
                    @click="showIconLegend = true"
                    class="flex items-center justify-center w-10 h-10 bg-blue-50 text-blue-700 rounded-full hover:bg-blue-100 transition-colors duration-200 shadow"
                    aria-label="Show Icon Legend"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <!-- Status Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.value"
                        @click="currentStatus = tab.value"
                        class="whitespace-nowrap py-4 px-1 font-bold text-xs"
                        :class="[
                            currentStatus === tab.value
                                ? 'border-b-4 border-green-500 text-green-600'
                                : 'border-b-4 border-transparent text-black hover:text-gray-700 hover:border-gray-300',
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            v-if="
                                props.orders.meta?.counts &&
                                props.orders.meta.counts[tab.value || 'all']
                            "
                            class="ml-2 px-2 py-0.5 text-xs rounded-full"
                            :class="`bg-${tab.color}-100 text-${tab.color}-800`"
                        >
                            {{ props.orders.meta.counts[tab.value || "all"] }}
                        </span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Icon Legend Slideover -->
        <div
            v-if="showIconLegend"
            class="fixed inset-0 overflow-hidden z-50"
            aria-labelledby="slide-over-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="absolute inset-0 overflow-hidden">
                <!-- Background overlay -->
                <div
                    class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="showIconLegend = false"
                ></div>

                <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex sm:pl-16">
                    <div class="w-screen max-w-md">
                        <div class="h-full flex flex-col bg-white shadow-xl">
                            <!-- Header -->
                            <div class="px-4 py-6 bg-blue-50 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-blue-900" id="slide-over-title">
                                        Order Status Icons Legend
                                    </h2>
                                    <button
                                        @click="showIconLegend = false"
                                        class="rounded-md text-blue-400 hover:text-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="relative flex-1 px-4 sm:px-6 overflow-y-auto">
                                <div class="space-y-6 py-6">
                                    <div class="text-sm text-gray-600 mb-4">
                                        <p>These icons represent the current status of each order in the workflow:</p>
                                    </div>
                                    
                                    <!-- Icon Legend Items -->
                                    <div class="space-y-4">
                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/pending.png" class="w-8 h-8" alt="Pending" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Pending</h3>
                                                <p class="text-sm text-gray-600">Order has been submitted and is awaiting review</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/review.png" class="w-8 h-8" alt="Reviewed" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Reviewed</h3>
                                                <p class="text-sm text-gray-600">Order has been reviewed by management</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/approved.png" class="w-8 h-8" alt="Approved" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Approved</h3>
                                                <p class="text-sm text-gray-600">Order has been approved for processing</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/rejected.png" class="w-8 h-8" alt="Rejected" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Rejected</h3>
                                                <p class="text-sm text-gray-600">Order has been rejected and will not proceed</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/inprocess.png" class="w-8 h-8" alt="In Process" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">In Process</h3>
                                                <p class="text-sm text-gray-600">Order is being prepared and processed</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/dispatch.png" class="w-8 h-8" alt="Dispatched" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Dispatched</h3>
                                                <p class="text-sm text-gray-600">Order has been dispatched for delivery</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/delivered.png" class="w-8 h-8" alt="Delivered" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Delivered</h3>
                                                <p class="text-sm text-gray-600">Order has been delivered to the facility</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <img src="/assets/images/received.png" class="w-8 h-8" alt="Received" />
                                            <div>
                                                <h3 class="font-medium text-gray-900">Received</h3>
                                                <p class="text-sm text-gray-600">Order has been received and confirmed by facility</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Workflow Information -->
                                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                                        <h3 class="font-medium text-blue-900 mb-2">Order Workflow</h3>
                                        <p class="text-sm text-blue-800">
                                            Orders progress through these stages sequentially. Each icon represents a completed stage in the process.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-[80px]">
            <!-- Orders Table -->
            <div class="lg:col-span-10 text-xs">
                <div>
                    <div class="overflow-auto">
                        <table class="w-full table-sm">
                                <thead style="background-color: #F4F7FB;">
                                    <tr>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tl-lg"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Order Number
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Facility
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Order Type
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Order Date
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Expected Date
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Handled By
                                        </th>
                                        <th
                                            class="px-2 py-2 text-left text-xs font-bold uppercase border-b rounded-tr-lg"
                                            style="color: #4F6FCB; border-bottom: 2px solid #B7C6E6;"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    <tr v-if="orders.data?.length === 0">
                                        <td
                                            colspan="7"
                                            class="px-2 py-2 text-center text-sm text-gray-600 border-b"
                                            style="border-bottom: 1px solid #B7C6E6;"
                                        >
                                            No orders found
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="order in orders.data"
                                        :key="order.id"
                                        class="border-b"
                                        :class="{
                                            'hover:bg-gray-50': true,
                                            'text-red-500':
                                                order.status === 'rejected',
                                        }"
                                        style="border-bottom: 1px solid #B7C6E6;"
                                    >
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            <Link :href="route('orders.show', order.id)">{{ order.order_number }}</Link>
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-900 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            {{ order.facility?.name }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            {{ order.order_type }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            {{ formatDate(order.order_date) }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            {{ formatDate(order.expected_date) }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-xs text-gray-600 border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            {{ order.facility?.handledby?.name || "Not assigned" }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap border-b" style="border-bottom: 1px solid #B7C6E6;">
                                            <div class="flex items-center gap-2">
                                                <!-- Status Progress Icons - Only show actions taken -->
                                                <div class="flex items-center gap-1">
                                                    <!-- Always show pending as it's the initial state -->
                                                    <img src="/assets/images/pending.png" class="w-6 h-6" alt="pending" title="Pending" />
                                                    <!-- Only show reviewed if status is reviewed or further -->
                                                    <img v-if="['reviewed','approved','in_process','dispatched','delivered','received'].includes(order.status)" src="/assets/images/review.png" class="w-6 h-6" alt="Reviewed" title="Reviewed" />
                                                    <!-- Only show approved if status is approved or further -->
                                                    <img v-if="['approved','in_process','dispatched','delivered','received'].includes(order.status)" src="/assets/images/approved.png" class="w-6 h-6" alt="Approved" title="Approved" />
                                                    <!-- Only show rejected if status is rejected -->
                                                    <img v-if="order.status === 'rejected'" src="/assets/images/rejected.png" class="w-6 h-6" alt="Rejected" title="Rejected" />
                                                    <!-- Only show in_process if status is in_process or further -->
                                                    <img v-if="['in_process','dispatched','delivered','received'].includes(order.status)" src="/assets/images/inprocess.png" class="w-6 h-6" alt="In Process" title="In Process" />
                                                    <!-- Only show dispatched if status is dispatched or further -->
                                                    <img v-if="['dispatched','delivered','received'].includes(order.status)" src="/assets/images/dispatch.png" class="w-6 h-6" alt="Dispatched" title="Dispatched" />
                                                    <img v-if="['delivered','received'].includes(order.status)" src="/assets/images/delivery.png" class="w-6 h-6" alt="Delivered" title="Delivered" />
                                                    <!-- Only show received if status is received -->
                                                    <img v-if="['received'].includes(order.status)" src="/assets/images/received.png" class="w-6 h-6" alt="Received" title="Received" />
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                   <div class="mt-4 flex justify-between bg-white px-6 py-4 border-t border-gray-200">                    
                    <div class="text-sm text-gray-500">
                        Showing {{ orders.meta.from }} to {{ orders.meta.to }} of {{ orders.meta.total }} orders
                    </div>
                    <TailwindPagination
                        :data="props.orders"
                        :limit="2"
                        @pagination-change-page="getResult"
                    />
                   </div>
                </div>
            </div>
            <!-- Status Statistics -->
            <div class="lg:col-span-1">
                <div class="sticky text-xs top-4 sticky:mt-5">
                    <div class="space-y-8">
                        <!-- Pending -->
                        <div class="relative">
                            <div class="flex items-center mb-2">
                                <div class="w-16 h-16 relative mr-4">
                                    <svg class="w-16 h-16 transform -rotate-90">
                                        <circle
                                            cx="32"
                                            cy="32"
                                            r="28"
                                            fill="none"
                                            stroke="#e2e8f0"
                                            stroke-width="4"
                                        />
                                        <circle
                                            cx="32"
                                            cy="32"
                                            r="28"
                                            fill="none"
                                            stroke="#eab308"
                                            stroke-width="4"
                                            :stroke-dasharray="(safeStats.pending === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.pending / totalOrders) * 175.93} 175.93`"
                                        />
                                    </svg>
                                    <div
                                        class="absolute inset-0 flex items-center justify-center"
                                    >
                                        <span
                                            class="text-base font-bold text-yellow-600"
                                            >{{
                                                totalOrders > 0
                                                    ? Math.round(
                                                          (safeStats.pending /
                                                              totalOrders) *
                                                              100
                                                      )
                                                    : 0
                                            }}%</span
                                        >
                                    </div>
                                </div>
                                <div>
                                    <div
                                        class="text-lg font-bold text-gray-900"
                                    >
                                        {{ safeStats.pending }}
                                    </div>
                                    <div class="text-base text-gray-600">
                                        Pending
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviewed -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#22c55e"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.reviewed === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.reviewed / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-green-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.reviewed /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.reviewed }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Reviewed
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approved -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#22c55e"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.approved === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.approved / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-green-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.approved /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.approved }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Approved
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#ef4444"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.rejected === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.rejected / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-red-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.rejected /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.rejected }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Rejected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- In Process -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#3b82f6"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.in_process === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.in_process / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-blue-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.in_process /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.in_process }}
                                </div>
                                <div class="text-base text-gray-600">
                                    In Process
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatched -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#8b5cf6"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.dispatched === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.dispatched / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-purple-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.dispatched /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.dispatched }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Dispatched
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivered -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#f59e42"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.delivered === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.delivered / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-base font-bold text-orange-600">
                                        {{
                                            totalOrders > 0
                                                ? Math.round((safeStats.delivered / totalOrders) * 100)
                                                : 0
                                        }}%
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.delivered }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Delivered
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Received -->
                    <div class="relative">
                        <div class="flex items-center mb-2">
                            <div class="w-16 h-16 relative mr-4">
                                <svg class="w-16 h-16 transform -rotate-90">
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#e2e8f0"
                                        stroke-width="4"
                                    />
                                    <circle
                                        cx="32"
                                        cy="32"
                                        r="28"
                                        fill="none"
                                        stroke="#6366f1"
                                        stroke-width="4"
                                        :stroke-dasharray="(safeStats.received === totalOrders && totalOrders > 0) ? '175.93 175.93' : `${(safeStats.received / totalOrders) * 175.93} 175.93`"
                                    />
                                </svg>
                                <div
                                    class="absolute inset-0 flex items-center justify-center"
                                >
                                    <span
                                        class="text-base font-bold text-indigo-600"
                                        >{{
                                            totalOrders > 0
                                                ? Math.round(
                                                      (safeStats.received /
                                                          totalOrders) *
                                                          100
                                                  )
                                                : 0
                                        }}%</span
                                    >
                                </div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-900">
                                    {{ safeStats.received }}
                                </div>
                                <div class="text-base text-gray-600">
                                    Received
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted, onBeforeUnmount } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";
import { TailwindPagination } from "laravel-vue-pagination";
import moment from "moment";
import axios from "axios";

const props = defineProps({
    orders: Object,
    filters: Object,
    stats: {
        type: Object,
        default: () => ({})
    },
    regions: Array,
});

const districts = ref([]);
const facilities = ref([]);

// Debounce setup
let searchTimeout = null;

// Ensure stats object has all required properties with defaults
const safeStats = computed(() => {
    try {
        const defaultStats = {
            pending: 0,
            reviewed: 0,
            approved: 0,
            in_process: 0,
            dispatched: 0,
            delivered: 0,
            received: 0,
            rejected: 0
        };
        
        const result = {
            ...defaultStats,
            ...(props.stats || {})
        };
        
        console.log('🔍 Order Stats Debug:', {
            propsStats: props.stats,
            defaultStats,
            result
        });
        
        return result;
    } catch (error) {
        console.error('Error in safeStats computed:', error);
        return {
            pending: 0,
            reviewed: 0,
            approved: 0,
            in_process: 0,
            dispatched: 0,
            delivered: 0,
            received: 0,
            rejected: 0
        };
    }
});

async function handleRegionSelect(option) {
    if (!option) {
        district.value = null;
        districts.value = [];
        facility.value = null;
        facilities.value = [];
        return;
    }
    
    // Smart validation - clear district if it doesn't belong to selected region
    if (district.value && districts.value.length > 0) {
        const districtExists = districts.value.some(d => d.value === district.value);
        if (!districtExists) {
            district.value = null;
        }
    }
    
    facility.value = null;
    facilities.value = [];
    await loadDistrict();
}

async function handleDistrictSelect(option) {
    if (!option) {
        facility.value = null;
        facilities.value = [];
        return;
    }
    
    // Smart validation - clear facility if it doesn't belong to selected district
    if (facility.value && facilities.value.length > 0) {
        const facilityExists = facilities.value.some(f => f.value === facility.value);
        if (!facilityExists) {
            facility.value = null;
        }
    }
    
    await loadFacility();
}

// Fixed order types
const orderTypes = ["All", "Quarterly", "Replenishment"];

// Compute total orders
const totalOrders = computed(() => {
    return (
        safeStats.value.pending +
        safeStats.value.reviewed +
        safeStats.value.approved +
        safeStats.value.in_process +
        safeStats.value.dispatched +
        safeStats.value.delivered +
        safeStats.value.received +
        safeStats.value.rejected
    );
});

// Status configuration
const statusTabs = [
    { value: null, label: "All Orders", color: "blue" },
    { value: "pending", label: "Pending", color: "yellow" },
    { value: "reviewed", label: "Reviewed", color: "yellow" },
    { value: "approved", label: "Approved", color: "green" },
    { value: "in_process", label: "In Process", color: "blue" },
    { value: "dispatched", label: "Dispatched", color: "purple" },
    { value: "delivered", label: "Delivered", color: "orange" },
    { value: "received", label: "Received", color: "indigo" },
    { value: "rejected", label: "Rejected", color: "red" },
];

// Filter states
const search = ref(props.filters.search);
const currentStatus = ref(props.filters.currentStatus || null); // Default to "All Orders" (null)
const facility = ref(props.filters?.facility);
const orderType = ref(props.filters?.orderType);
const district = ref(props.filters?.district);
const dateFrom = ref(props.filters?.dateFrom);
const dateTo = ref(props.filters?.dateTo);
const per_page = ref(props.filters.per_page || 25);
const region = ref(props.filters?.region);

// UI states
const showIconLegend = ref(false);

// Initialize data on page load
onMounted(async () => {
    // Load districts if region is already selected
    if (region.value) {
        await loadDistrict();
    }
    
    // Load facilities if district is already selected
    if (district.value) {
        await loadFacility();
    }
});

// Cleanup on unmount
onBeforeUnmount(() => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
});

// Watch for filter changes with debouncing for search
watch(
    () => search.value,
    () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(() => {
            reloadOrder();
        }, 500);
    }
);

// Watch for other filter changes (no debouncing needed)
watch(
    [
        () => currentStatus.value,
        () => facility.value,
        () => orderType.value,
        () => district.value,
        () => dateFrom.value,
        () => dateTo.value,
        () => region.value,
        () => per_page.value,
        () => props.filters.page,
    ],
    () => {
        reloadOrder();
    }
);

function reloadOrder() {
    const query = {};

    // Only add non-empty values to the query
    if (search.value) query.search = search.value;
    if (facility.value) query.facility = facility.value;
    if (currentStatus.value) query.currentStatus = currentStatus.value;
    if (orderType.value) query.orderType = orderType.value;
    if (per_page.value) query.per_page = per_page.value;
    if (props.filters.page) query.page = props.filters.page;
    if (district.value) query.district = district.value;
    if (dateFrom.value) query.dateFrom = dateFrom.value;
    if (dateTo.value) query.dateTo = dateTo.value;
    if (region.value) {
        query.region = region.value;
    }

    router.get(route("orders.index"), query, {
        preserveScroll: true,
        preserveState: true,
        only: ["orders", "stats"],
    });
}

function getResult(page = 1) {
    props.filters.page = page;
}

async function loadDistrict() {
    try {
        const response = await axios.post(route("districts.get-districts"), { 
            region: region.value 
        });
        districts.value = response.data;
    } catch (error) {
        console.log(error);
        districts.value = [];
    }
}

async function loadFacility() {
    try {
        const response = await axios.post(route("facilities.get-facilities"), { 
            district: district.value 
        });
        facilities.value = response.data;
    } catch (error) {
        console.log(error);
        facilities.value = [];
    }
}

const formatDate = (date) => {
    return moment(date).format("DD/MM/YYYY");
};
</script>
