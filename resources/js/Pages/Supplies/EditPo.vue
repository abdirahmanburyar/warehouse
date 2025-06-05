<template>
    <Head title="Purchase Order" />
    <AuthenticatedLayout
        title="Purchase Orders"
        description="Manage your purchase orders"
    >
    <div v-if="props.error">{{props.error}}</div>
    <div v-else>
        <Link
            :href="route('supplies.index')"
            class="flex items-center text-gray-500 hover:text-gray-700 cursor-pointer"
        >
            <svg
                class="w-6 h-6 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                ></path>
            </svg>
            Back to Suppliers
        </Link>
        <div class="">
            <!-- Supplier Selection -->
            <div class="bg-white shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Edit Purchase Order
                    {{ props.po?.po_number ? "#" + props.po.po_number : "" }}
                </h2>
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Supplier Information
                </h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-[400px] mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Select Supplier
                        </label>
                        <Multiselect
                            v-model="form.supplier"
                            :value="form.supplier_id"
                            :options="props.suppliers"
                            :searchable="true"
                            :close-on-select="true"
                            :show-labels="false"
                            :allow-empty="true"
                            placeholder="Select supplier"
                            track-by="id"
                            label="name"
                            @select="handleSupplierSelect"
                            :disabled="form.approved_at"
                        >
                        </Multiselect>
                    </div>

                    <!-- Supplier Details Card -->
                    <div
                        v-if="isLoading"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4"
                    >
                        <div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-24 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-32 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-28"
                            ></div>
                        </div>
                        <div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-32 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-40 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-36 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-44"
                            ></div>
                        </div>
                        <div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-36 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-28 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 animate-pulse w-32"
                            ></div>
                        </div>
                    </div>
                    <div
                        v-else-if="form.supplier"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4"
                    >
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Supplier Details
                            </h3>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ form.supplier?.name }}
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ form.supplier?.contact_person }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Contact Information
                            </h3>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ form.supplier?.email }}
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ form.supplier?.phone }}
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ form.supplier?.address }}
                            </p>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Purchase Order Info
                                </h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    P.O No. #: {{ form.po_number }}
                                </p>
                                <p class="mt-1 text-sm text-gray-900">
                                    Ref. No. #:
                                    <input
                                        type="text"
                                        v-model="form.original_po_no"
                                        class="border-0"
                                    />
                                </p>
                                <div class="mt-1 flex flex-col gap-2">
                                    <div class="flex items-center">
                                        Data:
                                        <input
                                            type="date"
                                            v-model="form.po_date"
                                            class="border-0"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- notes -->
            <!-- notes -->
            <div class="mt-8 flex-1 flex flex-col">
                <label for="notes" class="text-sm font-medium text-gray-500">Memo</label>
                <div class="bg-white shadow-sm border border-gray-200">
                    <textarea v-model="form.notes" class="w-full p-3 border-none resize-none" placeholder="Enter memo"></textarea>
                </div>
            </div>

            <!-- Items List -->
            <div class="mt-8 flex-1 flex flex-col">
                <div
                    class="bg-white shadow-sm border border-gray-200"
                >
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="w-[40px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    #
                                </th>
                                <th
                                    class="w-[400px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Item
                                </th>
                                <th
                                    class="w-[100px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Qty
                                </th>
                                <th
                                    class="w-[100px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    UoM
                                </th>
                                <th
                                    class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Unit Cost
                                </th>
                                <th
                                    class="w-[120px] px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Amount
                                </th>
                                <th class="w-[40px] px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="(item, index) in form.items"
                                :key="index"
                                :class="[
                                    'hover:bg-gray-50',
                                    { 'opacity-75': form.approved_at },
                                ]"
                            >
                                <td
                                    class="px-3 py-2 text-sm text-gray-500 align-top pt-4"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td class="px-3 py-2">
                                    <Multiselect
                                        v-model="item.product"
                                        :value="item.product_id"
                                        :options="props.products"
                                        :searchable="true"
                                        :close-on-select="true"
                                        :show-labels="false"
                                        :allow-empty="true"
                                        placeholder="Select item"
                                        track-by="id"
                                        label="name"
                                        @select="
                                            hadleProductSelect(index, $event)
                                        "
                                        :disabled="form.approved_at"
                                    >
                                    </Multiselect>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        v-model="item.quantity"
                                        @input="calculateTotal(index)"
                                        :disabled="form.approved_at"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-50 disabled:cursor-not-allowed"
                                        min="1"
                                    />
                                    <div
                                        v-if="item.original_quantity"
                                        class="text-xs mt-1 line-through text-red-500 pt-1 flex flex-col"
                                    >
                                        {{ item.original_quantity }}
                                    </div>
                                    <span
                                        v-if="item.edited"
                                        class="text-xs mt-1 text-red-500 pt-1 flex flex-col"
                                    >
                                        By: {{ item.edited.name }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        v-model="item.uom"
                                        :disabled="form.approved_at"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    />
                                    <div
                                        v-if="item.original_uom"
                                        class="text-xs mt-1 line-through text-red-500 pt-1"
                                    >
                                        {{ item.original_uom }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="number"
                                        v-model="item.unit_cost"
                                        @input="calculateTotal(index)"
                                        :disabled="form.approved_at"
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm disabled:bg-gray-50 disabled:cursor-not-allowed"
                                        step="0.01"
                                        min="0"
                                    />
                                </td>
                                <td class="px-3 py-2">
                                    <input
                                        type="text"
                                        :value="formatCurrency(item.total_cost)"
                                        readonly
                                        class="block w-full text-left text-black focus:ring-0 sm:text-sm bg-transparent"
                                    />
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <button
                                        v-if="!form.approved_at"
                                        @click="removeItem(index)"
                                        class="text-gray-400 hover:text-red-600"
                                    >
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="form.items.length === 0">
                                <td
                                    colspan="7"
                                    class="px-3 py-4 text-center text-sm text-gray-500"
                                >
                                    No items added. Click "Add Item" to start
                                    creating your purchase order.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Footer -->
                    <div class="border-t border-gray-200 px-3 py-4">
                        <div class="flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button
                                    v-if="!form.approved_at"
                                    type="button"
                                    @click="addItem"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Add Item
                                </button>
                            </div>
                            <div class="w-72 space-y-2">
                                <div
                                    class="flex justify-between items-center text-sm"
                                >
                                    <span class="font-medium text-gray-900"
                                        >Total</span
                                    >
                                    <span class="text-gray-900">{{
                                        formatCurrency(subtotal)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col items-end mb-[100px]">
                <div class="flex justify-end space-x-3 mb-[80px]">
                    <div class="mt-6 flex justify-end gap-x-4">
                        <button
                            type="button"
                            @click="reviewPO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                                form.reviewed_at
                                    ? 'bg-yellow-50 text-yellow-700 border border-yellow-200'
                                    : isProcessing.review
                                    ? 'bg-yellow-400 text-white cursor-wait'
                                    : 'bg-yellow-500 text-white hover:bg-yellow-600 focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2',
                            ]"
                            :disabled="
                                isProcessing.review ||
                                isProcessing.approve ||
                                isProcessing.reject ||
                                form.reviewed_at ||
                                form.approved_at ||
                                form.rejected_at
                            "
                        >
                            <svg
                                v-if="form.reviewed_at"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                ></path>
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                ></path>
                            </svg>
                            <svg
                                v-if="isProcessing.review"
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
                            {{
                                form.reviewed_at
                                    ? "Reviewed"
                                    : isProcessing.review
                                    ? "Processing..."
                                    : "Review"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="approvePO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                                form.approved_at
                                    ? 'bg-green-50 text-green-700 border border-green-200'
                                    : !form.reviewed_at
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : isProcessing.approve
                                    ? 'bg-green-400 text-white cursor-wait'
                                    : 'bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2',
                            ]"
                            :disabled="
                                isProcessing.review ||
                                isProcessing.approve ||
                                isProcessing.reject ||
                                !form.reviewed_at ||
                                form.approved_at ||
                                form.rejected_at
                            "
                        >
                            <svg
                                v-if="form.approved_at"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <svg
                                v-else-if="!form.reviewed_at"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                ></path>
                            </svg>
                            <svg
                                v-if="isProcessing.approve"
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
                            {{
                                form.approved_at
                                    ? "Approved"
                                    : isProcessing.approve
                                    ? "Processing..."
                                    : "Approve"
                            }}
                        </button>
                        <button
                            v-if="!form.approved_at"
                            type="button"
                            @click="rejectPO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out',
                                form.rejected_at
                                    ? 'bg-red-50 text-red-700 border border-red-200'
                                    : !form.reviewed_at
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : isProcessing.reject
                                    ? 'bg-red-400 text-white cursor-wait'
                                    : 'bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
                            ]"
                            :disabled="
                                isProcessing.review ||
                                isProcessing.approve ||
                                isProcessing.reject ||
                                !form.reviewed_at ||
                                form.rejected_at ||
                                form.approved_at
                            "
                        >
                            <svg
                                v-if="form.rejected_at"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <svg
                                v-else-if="!form.reviewed_at"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4"
                                ></path>
                            </svg>
                            <svg
                                v-else
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <svg
                                v-if="isProcessing.reject"
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
                            {{
                                form.rejected_at
                                    ? "Rejected"
                                    : isProcessing.reject
                                    ? "Processing..."
                                    : "Reject"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="router.visit(route('supplies.index'))"
                            :disabled="isSubmitting"
                            class="inline-flex items-center gap-x-2 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white ring-1 ring-inset ring-gray-300 hover:bg-gray-50 shadow-sm transition-all duration-200 ease-in-out"
                        >
                            Back
                        </button>
                        <button
                            v-if="!form.approved_at"
                            type="button"
                            @click="submitForm"
                            class="flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="isSubmitting"
                        >
                            {{ isSubmitting ? "Updating..." : "Update Changes" }}
                        </button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">
                        Status Information
                    </h3>
                    <div class="mt-2 space-y-2 text-sm">
                        <div
                            v-if="form.reviewed_by"
                            class="flex items-center gap-x-2 text-amber-700"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <span
                                >Reviewed on
                                {{ formatDate(form.reviewed_at) }}</span
                            >
                        </div>
                        <div
                            v-if="form.approved_by"
                            class="flex items-center gap-x-2 text-green-700"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <span
                                >Approved on
                                {{ formatDate(form.approved_at) }}</span
                            >
                        </div>
                        <div
                            v-if="form.rejected_by"
                            class="flex items-center gap-x-2 text-red-700"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            <span
                                >Rejected on
                                {{ formatDate(form.rejected_at) }}</span
                            >
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
import { Head, router, Link } from "@inertiajs/vue3";
import { ref, computed, BaseTransitionPropsValidators } from "vue";
import axios from "axios";
import { PlusIcon, TrashIcon } from "@heroicons/vue/24/outline";
import moment from "moment";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import "@/Components/multiselect.css";

import { useToast } from "vue-toastification";

const toast = useToast();

const props = defineProps({
    products: Array,
    suppliers: Array,
    po: Object,
    users: Array,
    error: String
});

const selectedSupplier = ref(props.po?.supplier || null);
const defaultForm = {
    id: null,
    supplier_id: null,
    supplier: null,
    po_number: "",
    original_po_no: "",
    po_date: new Date().toISOString().split("T")[0],
    status: null,
    reviewed_by: null,
    reviewed_at: null,
    approved_by: null,
    approved_at: null,
    rejected_by: null,
    rejected_at: null,
    items: [],
    documents: [],
};

const form = ref(props.po);

function addDocument() {
    if (!form.value.documents) {
        form.value.documents = [];
    }
    form.value.documents.push({
        document_type: '',
        file: null
    });
}

const removeDocument = (index) => {
    form.value.documents.splice(index, 1);
};

const deleteDocument = async (docId) => {
    try {
        const result = await Swal.fire({
            title: 'Delete Document?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            await axios.delete(route('supplies.deleteDocument', docId));
            
            // Remove from props.po.documents
            const index = props.po.documents.findIndex(doc => doc.id === docId);
            if (index !== -1) {
                props.po.documents.splice(index, 1);
            }

            toast.success('Document deleted successfully');
        }
    } catch (error) {
        toast.error(error.response?.data || 'Failed to delete document');
    }
};

function handleFileUpload(event, index) {
    const file = event.target.files[0];
    if (file) {
        form.value.documents[index].file = file;
    }
}

function hadleProductSelect(index, selected) {
    form.value.items[index].product_id = selected.id;
    form.value.items[index].product = selected;
}

function handleSupplierSelect(selected) {
    selectedSupplier.value = selected;
    form.value.supplier_id = selected?.id;
    form.value.supplier = selected;
    if (selected) {
        onSupplierChange(selected);
    }
}

function addItem() {
    form.value.items.push({
        product_id: null,
        product: null,
        uom: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0,
    });
}

function removeItem(index) {
    form.value.items.splice(index, 1);

    // If all items are removed, add one back
    if (form.value.items.length === 0) {
        addItem();
    }
}

function calculateTotal(index) {
    const item = form.value.items[index];
    item.total_cost = item.quantity * item.unit_cost;
}

const subtotal = computed(() => {
    return form.value.items.reduce(
        (sum, item) => sum + (item.total_cost || 0),
        0
    );
});

const isSubmitting = ref(false);

async function submitForm() {
    if (!form.value.supplier_id) {
        toast.warning("Please select a supplier");
        return;
    }

    // Validate items
    if (!form.value.items || !form.value.items.length) {
        toast.warning("Please add at least one item");
        return;
    }

    // Check if all items have required fields
    const hasInvalidItems = form.value.items.some(item => {
        return !item.product_id || !item.quantity || !item.unit_cost;
    });

    if (hasInvalidItems) {
        toast.warning("Please fill in all required fields for each item");
        return;
    }

    Swal.fire({
        title: "Confirm Update",
        text: "Are you sure you want to update this purchase order?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, update it!",
    }).then((result) => {
        if (result.isConfirmed) {
            isSubmitting.value = true;

            // Submit the form
            axios.post(route("supplies.storePO"), form.value, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((response) => {
                isSubmitting.value = false;
                Swal.fire({
                    title: "Success!",
                    text: response.data,
                    icon: "success",
                }).then(() => {
                    // Refresh the page to show updated data
                    router.visit(route("supplies.editPO", form.value.id), {
                        preserveScroll: true,
                        preserveState: false
                    });
                });
            })
            .catch((error) => {                
                Swal.fire({
                    title: "Failed!",
                    text: error.response.data,
                    icon: "error",
                });
                isSubmitting.value = false;
            });
        }
    });
}

function formatCurrency(value) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
}

function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return moment(date).format("DD/MM/YYYY");
}

const isLoading = ref(false);
const isProcessing = ref({
    review: false,
    approve: false,
    reject: false,
});

async function reviewPO() {
    // Prevent multiple clicks
    if (isProcessing.review) return;

    const result = await Swal.fire({
        title: "Review Purchase Order",
        text: "Are you sure you want to mark this purchase order for review?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#EAB308",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, review it!",
    });

    if (result.isConfirmed) {
        try {
            // Set processing state
            isProcessing.review = true;
            console.log("Setting review processing to true");

            // Show loading indicator
            Swal.fire({
                title: "Processing...",
                text: "Marking purchase order for review",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await axios.post(route("supplies.reviewPO", { id: form.value.id }));

            // Close loading indicator
            Swal.close();

            await Swal.fire({
                title: "Success!",
                text: "Purchase order has been marked for review.",
                icon: "success",
            });
            router.visit(route("supplies.index"));
        } catch (error) {
            // Close loading indicator
            Swal.close();

            toast.error(
                error.response?.data || "Failed to review purchase order"
            );
            // Reset processing state on error
            isProcessing.review = false;
            console.log("Setting review processing to false (error)");
        }
    }
}

async function approvePO() {
    // Prevent multiple clicks
    if (isProcessing.approve) return;

    const result = await Swal.fire({
        title: "Approve Purchase Order",
        text: "Are you sure you want to approve this purchase order?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#22C55E",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, approve it!",
    });

    if (result.isConfirmed) {
        try {
            // Set processing state
            isProcessing.approve = true;
            console.log("Setting approve processing to true");

            // Show loading indicator
            Swal.fire({
                title: "Processing...",
                text: "Approving purchase order",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await axios.post(
                route("supplies.approvePO", { id: form.value.id })
            );

            // Close loading indicator
            Swal.close();

            await Swal.fire({
                title: "Success!",
                text: "Purchase order has been approved.",
                icon: "success",
            });
            router.visit(route("supplies.index"));
        } catch (error) {
            // Close loading indicator
            Swal.close();

            toast.error(
                error.response?.data || "Failed to approve purchase order"
            );
            // Reset processing state on error
            isProcessing.approve = false;
            console.log("Setting approve processing to false (error)");
        }
    }
}

async function rejectPO() {
    // Prevent multiple clicks
    if (isProcessing.reject) return;

    const result = await Swal.fire({
        title: "Reject Purchase Order",
        text: "Are you sure you want to reject this purchase order?",
        input: "textarea",
        inputLabel: "Rejection Reason",
        inputPlaceholder: "Enter the reason for rejection...",
        inputAttributes: {
            required: "true",
        },
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#EF4444",
        cancelButtonColor: "#6B7280",
        confirmButtonText: "Yes, reject it!",
        inputValidator: (value) => {
            if (!value) {
                return "You need to provide a reason for rejection";
            }
        },
    });

    if (result.isConfirmed) {
        try {
            // Set processing state
            isProcessing.reject = true;
            console.log("Setting reject processing to true");

            // Show loading indicator
            Swal.fire({
                title: "Processing...",
                text: "Rejecting purchase order",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await axios.post(
                route("supplies.rejectPO", { id: form.value.id }),
                {
                    reason: result.value,
                }
            );

            // Close loading indicator
            Swal.close();

            await Swal.fire({
                title: "Rejected!",
                text: "Purchase order has been rejected.",
                icon: "success",
            });
            router.visit(route("supplies.editpk", props.po?.id));
        } catch (error) {
            // Close loading indicator
            Swal.close();

            toast.error(
                error.response?.data || "Failed to reject purchase order"
            );
            // Reset processing state on error
            isProcessing.reject = false;
            console.log("Setting reject processing to false (error)");
        }
    }
}

async function onSupplierChange(selected) {
    isLoading.value = true;
    try {
        let value = selected.id;
        if (!value) {
            selectedSupplier.value = null;
            form.value.supplier_id = null;
            return;
        }

        form.value.supplier_id = value;
        const supplier = props.suppliers.find((s) => s.id == value);
        form.value.supplier = supplier;
        selectedSupplier.value = supplier;
    } catch (error) {
        console.error("Error changing supplier:", error);
    } finally {
        isLoading.value = false;
    }
}

// const po_date = ref(moment().format('YYYY-MM-DD'));
</script>
