<template>
    <Head title="Purchase Orders" />

    <AuthenticatedLayout
        title="Purchase Orders"
        description="Manage your purchase orders"
        img="/assets/images/inventory.png"
    >
        <div class="overflow-hidden bg-white sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search and Filters -->
                <div
                    class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between"
                >
                    <!-- Left side filters in a row -->
                    <div
                        class="flex flex-col flex-grow gap-3 md:flex-row md:items-center"
                    >
                        <!-- Search Bar -->
                        <div class="relative flex-grow w-full md:w-auto">
                            <div
                                class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"
                            >
                                <svg
                                    class="w-5 h-5 text-gray-400"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by PO number, supplier..."
                                class="w-full py-2 pl-10 pr-4 transition duration-150 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Status Filter -->
                        <select
                            v-model="status"
                            class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-40 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="completed">Completed</option>
                        </select>

                        <!-- Date Range Filter -->
                        <div class="flex gap-2">
                            <input
                                type="date"
                                v-model="start_date"
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-40 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <input
                                type="date"
                                v-model="end_date"
                                class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-40 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <!-- Per Page Selector -->
                        <select
                            v-model="per_page"
                            class="w-full py-2 pl-3 pr-10 transition duration-150 border border-gray-300 rounded-lg md:w-32 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="5">5 / page</option>
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>

                        <!-- Reset Filters Button -->
                        <button
                            @click="resetFilters"
                            class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-gray-800 border border-transparent rounded-md hover:bg-gray-700"
                        >
                            <span class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    />
                                </svg>
                                Reset
                            </span>
                        </button>
                    </div>

                    <!-- Add New PO Button -->
                    <div class="mt-4 space-x-4 sm:mt-0 sm:ml-16 sm:flex-none">
                        <button
                            type="button"
                            @click="openModal"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
                        >
                            Add Purchase Order
                        </button>
                    </div>
                </div>

                <!-- Purchase Orders Table -->
                <div class="flex flex-col">
                    <div class="overflow-x-auto">
                        <div class="inline-block min-w-full align-middle">
                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"
                            >
                                <!-- Fixed Header -->
                                <div class="sticky top-0 z-10 bg-white">
                                    <table
                                        class="min-w-full divide-y divide-gray-300"
                                    >
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th
                                                    scope="col"
                                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                                >
                                                    PO Number
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                >
                                                    Supplier
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                >
                                                    Date
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                >
                                                    Total Amount
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                >
                                                    Status
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="relative py-3.5 pl-3 pr-4 sm:pr-6"
                                                >
                                                    <span class="sr-only"
                                                        >Actions</span
                                                    >
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white divide-y divide-gray-200"
                                        >
                                            <tr
                                                v-if="
                                                    !props.purchase_orders
                                                        .data ||
                                                    props.purchase_orders.data
                                                        .length === 0
                                                "
                                            >
                                                <td
                                                    colspan="6"
                                                    class="px-6 py-24 text-center"
                                                >
                                                    <div
                                                        class="flex flex-col items-center"
                                                    >
                                                        <div
                                                            class="p-3 bg-gray-100 rounded-full"
                                                        >
                                                            <svg
                                                                class="w-12 h-12 text-gray-400"
                                                                fill="none"
                                                                viewBox="0 0 24 24"
                                                                stroke="currentColor"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                                />
                                                            </svg>
                                                        </div>
                                                        <h3
                                                            class="mt-4 text-base font-medium text-gray-900"
                                                        >
                                                            No Purchase Orders
                                                            Found
                                                        </h3>
                                                        <p
                                                            class="mt-2 text-sm text-gray-500"
                                                        >
                                                            {{
                                                                search ||
                                                                status ||
                                                                start_date ||
                                                                end_date
                                                                    ? "No results match your search criteria."
                                                                    : "Get started by creating your first purchase order."
                                                            }}
                                                        </p>
                                                        <div class="mt-6">
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    openModal
                                                                "
                                                                class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                                            >
                                                                <svg
                                                                    class="w-5 h-5 mr-2"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    fill="none"
                                                                    viewBox="0 0 24 24"
                                                                    stroke="currentColor"
                                                                >
                                                                    <path
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                                                    />
                                                                </svg>
                                                                Create Purchase
                                                                Order
                                                            </button>
                                                        </div>
                                                        <div
                                                            v-if="
                                                                search ||
                                                                status ||
                                                                start_date ||
                                                                end_date
                                                            "
                                                            class="mt-4"
                                                        >
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    resetFilters
                                                                "
                                                                class="text-sm text-primary-600 hover:text-primary-500"
                                                            >
                                                                Clear all
                                                                filters
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr
                                                v-for="order in props
                                                    .purchase_orders.data"
                                                :key="order.id"
                                                class="hover:bg-gray-50"
                                            >
                                                <td
                                                    class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 whitespace-nowrap sm:pl-6"
                                                >
                                                    <Link
                                                        :href="
                                                            route(
                                                                'purchase-orders.packing-list',
                                                                order.id
                                                            )
                                                        "
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        {{ order.po_number }}
                                                    </Link>
                                                </td>
                                                <td
                                                    class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                >
                                                    {{ order.supplier?.name }}
                                                </td>
                                                <td
                                                    class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                >
                                                    {{
                                                        formatDate(
                                                            order.po_date
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            order.total_amount
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-3 py-4 text-sm whitespace-nowrap"
                                                >
                                                    <span
                                                        :class="{
                                                            'bg-green-100 text-green-800':
                                                                order.status ===
                                                                'completed',
                                                            'bg-yellow-100 text-yellow-800':
                                                                order.status ===
                                                                'pending',
                                                            'bg-gray-100 text-gray-800':
                                                                order.status ===
                                                                'draft',
                                                        }"
                                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                                    >
                                                        {{ order.status }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="relative py-4 pl-3 pr-4 text-sm font-medium text-right whitespace-nowrap sm:pr-6"
                                                >
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <button
                                                            @click="
                                                                editOrder(order)
                                                            "
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="w-5 h-5"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                            >
                                                                <path
                                                                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                                                                />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            @click="
                                                                openItemsModal(
                                                                    order
                                                                )
                                                            "
                                                            class="text-blue-600 hover:text-blue-900"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="w-5 h-5"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                            >
                                                                <path
                                                                    d="M10 12a2 2 0 100-4 2 2 0 000 4z"
                                                                />
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            @click="
                                                                deleteOrder(
                                                                    order
                                                                )
                                                            "
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                class="w-5 h-5"
                                                                viewBox="0 0 20 20"
                                                                fill="currentColor"
                                                            >
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd"
                                                                />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <Pagination :links="props.purchase_orders.meta.links" />
                </div>
            </div>
        </div>

        <!-- Purchase Order Modal -->
        <TransitionRoot appear :show="showModal" as="template">
            <Dialog as="div" @close="closeModal" class="relative z-50">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-black bg-opacity-25" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto">
                    <div
                        class="flex items-center justify-center min-h-full p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="duration-300 ease-out"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="duration-200 ease-in"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl max-w-7xl rounded-2xl sm:my-8 sm:w-full sm:max-w-5xl sm:p-6"
                            >
                                <form
                                    @submit.prevent="submitForm"
                                    class="space-y-6"
                                >
                                    <DialogTitle
                                        as="h3"
                                        class="text-lg font-medium leading-6 text-gray-900"
                                    >
                                        {{
                                            form.id
                                                ? "Edit Purchase Order"
                                                : "Create Purchase Order"
                                        }}
                                    </DialogTitle>

                                    <!-- PO Number and Supplier -->
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label
                                                for="po_number"
                                                class="block text-sm font-medium text-gray-700"
                                                >PO Number</label
                                            >
                                            <input
                                                type="text"
                                                id="po_number"
                                                v-model="form.po_number"
                                                required
                                                placeholder="Enter PO number"
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                        </div>
                                        <div>
                                            <label
                                                for="supplier_id"
                                                class="block text-sm font-medium text-gray-700"
                                                >Supplier</label
                                            >
                                            <select
                                                id="supplier_id"
                                                v-model="form.supplier_id"
                                                required
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="">
                                                    Select a supplier
                                                </option>
                                                <option
                                                    v-for="supplier in props.suppliers"
                                                    :key="supplier.id"
                                                    :value="supplier.id"
                                                >
                                                    {{ supplier.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                for="po_date"
                                                class="block text-sm font-medium text-gray-700"
                                                >PO Date</label
                                            >
                                            <input
                                                type="date"
                                                id="po_date"
                                                v-model="form.po_date"
                                                required
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label
                                            for="notes"
                                            class="block text-sm font-medium text-gray-700"
                                            >Notes</label
                                        >
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="2"
                                            placeholder="Enter notes"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-end gap-3 mt-6">
                                        <button
                                            type="button"
                                            :disabled="processing"
                                            @click="closeModal"
                                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="processing"
                                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        >
                                            {{
                                                form.id
                                                    ? processing
                                                        ? "Updating..."
                                                        : "Update"
                                                    : processing
                                                    ? "Creating..."
                                                    : "Create"
                                            }}
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Items Modal -->
        <TransitionRoot appear :show="showItemsModal" as="template">
            <Dialog as="div" class="relative z-[60]" @close="closeItemsModal">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-[60] w-screen overflow-y-auto">
                    <div
                        class="flex items-end justify-center min-h-full p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative px-4 pt-5 pb-4 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-5xl sm:p-6"
                            >
                                <div
                                    class="absolute top-0 right-0 hidden pt-4 pr-4 sm:block"
                                >
                                    <button
                                        type="button"
                                        class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click="closeItemsModal"
                                    >
                                        <span class="sr-only">Close</span>
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <div class="sm:flex sm:items-start">
                                    <div
                                        class="w-full mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left"
                                    >
                                        <DialogTitle
                                            as="h3"
                                            class="text-lg font-semibold leading-6 text-gray-900"
                                        >
                                            Purchase Order Items
                                            <span
                                                v-if="selectedOrder"
                                                class="ml-2 text-sm text-gray-500"
                                            >
                                                (PO #{{
                                                    selectedOrder.po_number
                                                }})
                                            </span>
                                        </DialogTitle>
                                        <div class="mt-4">
                                            <div class="overflow-x-auto">
                                                {{ selectedOrder }}
                                                <table
                                                    class="min-w-full divide-y divide-gray-200"
                                                    v-if="
                                                        selectedOrder &&
                                                        selectedOrder.po_items
                                                    "
                                                >
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                scope="col"
                                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900"
                                                            >
                                                                Product
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                            >
                                                                Original
                                                                Quantity
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                            >
                                                                Quantity
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                            >
                                                                Unit Cost
                                                            </th>
                                                            <th
                                                                scope="col"
                                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                                            >
                                                                Total Cost
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody
                                                        class="bg-white divide-y divide-gray-200"
                                                    >
                                                        <tr
                                                            v-for="item in selectedOrder.po_items"
                                                            :key="item.id"
                                                            class="hover:bg-gray-50"
                                                        >
                                                            <td
                                                                class="py-4 pl-4 pr-3 text-sm text-gray-900 whitespace-nowrap"
                                                            >
                                                                {{
                                                                    item.item_description
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                            >
                                                                {{
                                                                    formatNumber(
                                                                        item.original_quantity
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                            >
                                                                {{
                                                                    formatNumber(
                                                                        item.quantity
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        item.unit_cost
                                                                    )
                                                                }}
                                                            </td>
                                                            <td
                                                                class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        item.total_cost
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                        <tr
                                                            v-if="
                                                                !selectedOrder
                                                                    .po_items
                                                                    .length
                                                            "
                                                        >
                                                            <td
                                                                colspan="4"
                                                                class="px-3 py-4 text-sm text-center text-gray-500"
                                                            >
                                                                No items found
                                                                for this
                                                                purchase order
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot
                                                        v-if="
                                                            selectedOrder
                                                                .po_items.length
                                                        "
                                                        class="bg-gray-50"
                                                    >
                                                        <tr>
                                                            <td
                                                                colspan="3"
                                                                class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900"
                                                            >
                                                                Total:
                                                            </td>
                                                            <td
                                                                class="whitespace-nowrap px-3 py-3.5 text-sm font-semibold text-gray-900"
                                                            >
                                                                {{
                                                                    formatCurrency(
                                                                        selectedOrder.po_items.reduce(
                                                                            (
                                                                                total,
                                                                                item
                                                                            ) =>
                                                                                total +
                                                                                item.total_cost,
                                                                            0
                                                                        )
                                                                    )
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex justify-center w-full px-3 py-2 mt-3 text-sm font-semibold text-gray-900 bg-white rounded-md shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                        @click="closeItemsModal"
                                    >
                                        Close
                                    </button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, nextTick } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";
import axios from "axios";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import { debounce } from "lodash";
import Swal from "sweetalert2";

const toast = useToast();

const props = defineProps({
    purchase_orders: Object,
    suppliers: Array,
    products: Array,
    filters: Object,
});

// Modal states
const showModal = ref(false);
const showDeleteModal = ref(false);
const showItemsModal = ref(false);
const processing = ref(false);
const errors = ref(null);

// Selected purchase order for deletion or viewing items
const selectedOrder = ref(null);

const openItemsModal = (order) => {
    selectedOrder.value = order;
    showItemsModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    form.value = {
        id: null,
        po_number: "",
        supplier_id: "",
        po_date: new Date().toISOString().split("T")[0],
        total_amount: 0,
        notes: "",
        status: "pending",
    };
    errors.value = null;
    ``;
};

const formatNumber = (num) => {
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
};

// Close items modal
const closeItemsModal = () => {
    showItemsModal.value = false;
    selectedOrder.value = null;
};

// Submit form
const submitForm = async () => {
    processing.value = true;

    await axios
        .post(route("purchase-orders.store"), form.value)
        .then((response) => {
            processing.value = false;
            toast.success(response.data);
            closeModal();
            reloadOrders();
        })
        .catch((error) => {
            console.log(error);
            processing.value = false;
            toast.error(error.response.data);
        });
};

// Delete order
const deleteOrder = async () => {
    if (!selectedOrder.value) return;

    processing.value = true;
    try {
        const response = await axios.delete(
            route("purchase-orders.destroy", selectedOrder.value.id)
        );
        if (response.data.success) {
            toast.success(
                response.data || "Purchase order deleted successfully"
            );
            reloadOrders();
        } else {
            toast.error(response.data || "An error occurred");
        }
    } catch (error) {
        toast.error("An error occurred while deleting the purchase order");
        console.error("Error:", error);
    } finally {
        processing.value = false;
    }
};

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

// Format date
const formatDate = (date) => {
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).format(new Date(date));
};

// Form data
const form = ref({
    id: null,
    po_number: "",
    supplier_id: "",
    po_date: new Date().toISOString().split("T")[0],
    total_amount: 0,
    notes: "",
    status: "pending",
    items: [],
});

// Reactive state
const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
const start_date = ref(props.filters?.start_date || "");
const end_date = ref(props.filters?.end_date || "");
const per_page = ref(props.filters?.per_page || 10);

// Watch for filter changes
watch(
    [
        () => search.value,
        () => status.value,
        () => start_date.value,
        () => end_date.value,
        () => per_page.value,
    ],
    () => {
        reloadOrders();
    }
);

// Reset filters
const resetFilters = () => {
    search.value = "";
    status.value = "";
    start_date.value = "";
    end_date.value = "";
    per_page.value = 10;
    reloadOrders();
};

// Reload orders with current filters
const reloadOrders = () => {
    const query = {};
    if (search.value) query.search = search.value;
    if (status.value) query.status = status.value;
    if (start_date.value) query.start_date = start_date.value;
    if (end_date.value) query.end_date = end_date.value;
    if (per_page.value) query.per_page = per_page.value;
    router.get(route("purchase-orders.index"), query, {
        preserveState: true,
        preserveScroll: true,
        only: ["purchase_orders", "suppliers", "products"],
    });
};

// Open modal for create/edit
const openModal = (order = null) => {
    errors.value = null;
    if (order) {
        form.value = {
            id: order.id,
            po_number: order.po_number,
            supplier_id: order.supplier?.id || "",
            po_date: order.po_date,
            total_amount: order.total_amount,
            status: order.status,
            notes: order.notes,
        };
    } else {
        form.value = {
            id: null,
            po_number: "",
            supplier_id: "",
            po_date: new Date().toISOString().split("T")[0],
            total_amount: 0,
            status: "pending",
            notes: "",
        };
    }
    showModal.value = true;
};

function editOrder(order) {
    form.value = {
        id: order.id,
        po_number: order.po_number,
        supplier_id: order.supplier?.id || "",
        po_date: order.po_date,
        total_amount: order.total_amount,
        status: order.status,
        notes: order.notes,
    };
    showModal.value = true;
}
</script>

<style>
.multiselect {
    min-height: 36px;
}

.multiselect__tags {
    min-height: 36px;
    padding: 4px 40px 0 8px;
    border-radius: 6px;
    border-color: #d1d5db;
}

.multiselect__input {
    border: none !important;
    outline: none !important;
}

.multiselect__input:focus {
    outline: none !important;
    box-shadow: none !important;
}

.multiselect--active {
    outline: none !important;
    box-shadow: none !important;
}

.multiselect__single {
    padding-left: 4px;
    margin-bottom: 0;
}

.multiselect__placeholder {
    padding-left: 4px;
    margin-bottom: 0;
    color: #6b7280;
}

.multiselect__content-wrapper {
    border-color: #d1d5db;
}

.multiselect__option--highlight {
    background: #4f46e5;
}

.multiselect__option--selected.multiselect__option--highlight {
    background: #dc2626;
}
</style>
