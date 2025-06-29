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
            class="inline-flex items-center text-gray-600 hover:text-indigo-600 transition-colors duration-200 mb-6"
        >
            <svg
                class="w-5 h-5 mr-2"
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
            Back to Purchase Orders
        </Link>
        <div class="max-w-7xl mx-auto">
            <!-- Supplier Selection -->
            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-6 transition-all duration-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    Edit Purchase Order
                    {{ props.po?.po_number ? "#" + props.po.po_number : "" }}
                </h2>
                <div class="grid grid-cols-1 gap-6">
                    <div class="w-full max-w-md">
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
                            placeholder="Search and select supplier..."
                            track-by="id"
                            label="name"
                            class="multiselect-modern"
                            @select="handleSupplierSelect"
                            :disabled="form.approved_at"
                        >
                        </Multiselect>
                    </div>

                    <!-- Supplier Details Card -->
                    <div
                        v-if="isLoading"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 rounded-xl p-6"
                    >
                        <div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-24 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-32 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-28"
                            ></div>
                        </div>
                        <div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-32 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-40 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-36 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-44"
                            ></div>
                        </div>
                        <div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-36 mb-3"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-28 mb-2"
                            ></div>
                            <div
                                class="h-4 bg-gray-200 rounded-full animate-pulse w-32"
                            ></div>
                        </div>
                    </div>
                    <div
                        v-else-if="form.supplier"
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gradient-to-br from-gray-50 to-white rounded-xl p-6 border border-gray-100"
                    >
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                                Supplier Details
                            </h3>
                            <p class="text-base font-medium text-gray-900">
                                {{ form.supplier?.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ form.supplier?.contact_person }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                                Contact Information
                            </h3>
                            <p class="text-sm text-gray-600 flex items-center mb-2">
                                <svg
                                    class="w-4 h-4 mr-2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    ></path>
                                </svg>
                                {{ form.supplier?.email }}
                            </p>
                            <p class="text-sm text-gray-600 flex items-center mb-2">
                                <svg
                                    class="w-4 h-4 mr-2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                    ></path>
                                </svg>
                                {{ form.supplier?.phone }}
                            </p>
                            <p class="text-sm text-gray-600 flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    ></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>
                                </svg>
                                {{ form.supplier?.address }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">
                                Purchase Order Info
                            </h3>
                            <p class="text-sm text-gray-900 mb-2">
                                P.O No. #:
                                <span class="font-medium">{{ form.po_number }}</span>
                            </p>
                            <div class="flex items-center mb-2">
                                <span class="text-sm text-gray-900 mr-2">
                                    Ref. No. #:
                                </span>
                                <input
                                    type="text"
                                    v-model="form.original_po_no"
                                    :disabled="form.approved_at"
                                    class="flex-1 text-sm border-0 bg-transparent focus:ring-0 focus:border-b-2 focus:border-indigo-500 transition-all duration-200"
                                />
                            </div>
                            <div class="flex items-center mb-4">
                                <span class="text-sm text-gray-900 mr-2">
                                    Date:
                                </span>
                                <input
                                    type="date"
                                    v-model="form.po_date"
                                    :disabled="form.approved_at"
                                    class="flex-1 text-sm border-0 bg-transparent focus:ring-0 focus:border-b-2 focus:border-indigo-500 transition-all duration-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden transition-all duration-200 mb-6">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                    <p class="mt-1 text-sm text-gray-500">Add items to your purchase order</p>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-12 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="w-1/3 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="w-24 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                            <th class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost</th>
                            <th class="w-32 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="w-12 px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <tr v-for="(item, index) in form.items" :key="index" 
                            class="transition-colors duration-150 hover:bg-gray-50" 
                            :class="{ 'opacity-75': form.approved_at }"
                            :data-item-index="index">
                            <td class="px-3 py-3 text-sm text-gray-500 align-middle">{{ index + 1 }}</td>
                            <td class="px-3 py-3">
                                <Multiselect v-model="item.product" :value="item.product_id"
                                    :options="props.products"
                                    :searchable="true" :close-on-select="true" :show-labels="false" required
                                    :allow-empty="true" placeholder="Search and select item..." track-by="id" label="name"
                                    class="multiselect-modern"
                                    @select="hadleProductSelect(index, $event)"
                                    :disabled="form.approved_at">
                                </Multiselect>
                                <div v-if="item.edited" class="text-xs mt-1 text-red-500 pt-1 flex flex-col">
                                    By: {{ item.edited.name }}
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" v-model="item.quantity" @input="calculateTotal(index)" required
                                    :disabled="form.approved_at"
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1 disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    min="1" placeholder="Enter quantity">
                                <div v-if="item.original_quantity" class="text-xs mt-1 line-through text-red-500 pt-1">
                                    {{ item.original_quantity }}
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" v-model="item.uom" required
                                    :disabled="form.approved_at"
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1 disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    placeholder="e.g. PCS">
                                <div v-if="item.original_uom" class="text-xs mt-1 line-through text-red-500 pt-1">
                                    {{ item.original_uom }}
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" v-model="item.unit_cost" @input="calculateTotal(index)" required
                                    :disabled="form.approved_at"
                                    class="block w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200 px-2 py-1 disabled:bg-gray-50 disabled:cursor-not-allowed"
                                    step="0.01" min="0" placeholder="Enter cost">
                            </td>
                            <td class="px-3 py-3">
                                <input type="text" :value="formatCurrency(item.total_cost)" readonly
                                    class="block w-full rounded-lg bg-gray-50 border-gray-200 text-sm text-gray-500 px-2 py-1"
                                    placeholder="$0.00">
                            </td>
                            <td class="px-3 py-3 text-center">
                                <button v-if="!form.approved_at" type="button" @click="removeItem(index)"
                                    class="text-gray-400 hover:text-red-600 transition-colors duration-200">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="form.items.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No items added</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by adding a new item to your purchase order.</p>
                                    <div class="mt-6">
                                        <button type="button" @click="addItem"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <PlusIcon class="h-5 w-5 mr-2" />
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-3">
                            <button v-if="!form.approved_at" type="button" @click="addItem"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <PlusIcon class="h-5 w-5 mr-2 text-gray-400" />
                                Add Item
                            </button>
                            <button v-if="!form.approved_at" type="button" @click="form.items = []"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                <svg class="h-5 w-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Clear Items
                            </button>
                        </div>
                        <div class="w-72">
                            <div class="bg-white rounded-lg p-4 border border-gray-100">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-900">Total Amount</span>
                                    <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Memo -->
            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-6 transition-all duration-200">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-3">Memo</label>
                <textarea v-model="form.notes" rows="3" 
                    class="w-full rounded-lg border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all duration-200" 
                    placeholder="Enter additional notes or memo..."></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-col items-center space-y-6 mb-[80px]">
                <div class="flex justify-end space-x-3">
                    <div class="flex flex-col items-start">
                        <button type="button" @click="reviewPO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
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
                            <svg v-if="form.reviewed_at" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg v-if="isProcessing.review" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.reviewed_at ? "Reviewed on " + formatDate(form.reviewed_at) : isProcessing.review ? "Processing..." : "Review" }}
                        </button>
                        <div v-if="form.reviewed_at" class="mt-2 text-left">
                            <div class="text-xs font-medium text-start text-gray-700">by {{ form.reviewed_by?.name }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col items-start">
                        <button type="button" @click="approvePO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                                form.approved_at
                                    ? 'bg-green-50 text-green-700 border border-green-200'
                                    : isProcessing.approve
                                    ? 'bg-green-400 text-white cursor-wait'
                                    : 'bg-green-500 text-white hover:bg-green-600 focus:ring-2 focus:ring-green-500 focus:ring-offset-2'
                            ]"
                            :disabled="form.approved_at || isProcessing.approve || !form.reviewed_at"
                        >
                            <CheckCircleIcon v-if="form.approved_at" class="h-4 w-4" />
                            <ArrowPathIcon v-else-if="isProcessing.approve" class="h-4 w-4 animate-spin" />
                            <CheckCircleIcon v-else class="h-4 w-4" />
                            {{ form.approved_at ? 'Approved on ' + formatDate(form.approved_at) : isProcessing.approve ? 'Processing...' : 'Approve' }}
                        </button>
                        <div v-if="form.approved_at" class="mt-2 text-left">
                            <div class="text-xs font-medium text-start text-gray-700">by {{ form.approved_by?.name }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center">
                        <button v-if="!form.approved_at" type="button" @click="rejectPO"
                            :class="[
                                'inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
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
                            <svg v-if="form.rejected_at" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-else-if="!form.reviewed_at" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m4-6V4"></path>
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg v-if="isProcessing.reject" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.rejected_at ? "Rejected on " + formatDate(form.rejected_at) : isProcessing.reject ? "Processing..." : "Reject" }}
                        </button>
                        <div v-if="form.rejected_at" class="mt-2 text-center">
                            <div class="text-xs font-medium text-start text-gray-700">by {{ form.rejected_by?.name }}</div>
                        </div>
                    </div>

                    <div class="flex flex-col items-center">
                        <button type="button" @click="router.visit(route('supplies.index'))" :disabled="isSubmitting"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            Exit
                        </button>
                    </div>

                    <div class="flex flex-col items-center">
                        <button v-if="!form.approved_at" type="button" @click="submitForm" :disabled="isSubmitting"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isSubmitting ? "Updating..." : "Update Changes" }}
                        </button>
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
import { ref, computed } from "vue";
import axios from "axios";
import { PlusIcon, TrashIcon, CheckCircleIcon, ArrowPathIcon } from "@heroicons/vue/24/outline";
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

const form = ref({
    id: props.po?.id,
    supplier_id: props.po?.supplier_id,
    supplier: props.po?.supplier,
    original_po_no: props.po?.original_po_no,
    notes: props.po?.notes,
    po_number: props.po?.po_number,
    po_date: props.po?.po_date || new Date().toISOString().split('T')[0],
    items: props.po?.items || [],
    reviewed_at: props.po?.reviewed_at,
    reviewed_by: props.po?.reviewed_by,
    approved_at: props.po?.approved_at,
    approved_by: props.po?.approved_by,
    rejected_at: props.po?.rejected_at,
    rejected_by: props.po?.rejected_by
});

const isLoading = ref(false);
const isSubmitting = ref(false);
const isProcessing = ref({
    review: false,
    approve: false,
    reject: false
});

function addItem() {
    // Check if there are existing items and if the last item has no product_id
    if (form.value.items.length > 0) {
        const lastItem = form.value.items[form.value.items.length - 1];
        if (!lastItem.product_id) {
            return;
        }
    }

    form.value.items.push({
        product_id: null,
        product: null,
        uom: "",
        quantity: 1,
        unit_cost: 0,
        total_cost: 0
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
    return form.value.items.reduce((sum, item) => sum + (item.total_cost || 0), 0);
});

function handleSupplierSelect(selected) {
    form.value.supplier_id = selected.id;
    form.value.supplier = selected;
    addItem();
}

function hadleProductSelect(index, selected) {
    form.value.items[index].product_id = selected.id;
    form.value.items[index].product = selected;
    addItem();
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value || 0);
}

const formatDate = (dateString) => {
    if (!dateString) return '';
    return moment(dateString).format('MM/DD/YYYY hh:mm A');
};

async function reviewPO() {
    if (isProcessing.value.review) return;

    const result = await Swal.fire({
        title: 'Review Purchase Order',
        text: 'Are you sure you want to review this purchase order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, review it!'
    });

    if (!result.isConfirmed) return;

    try {
        isProcessing.value.review = true;
        const response = await axios.post(route('supplies.reviewPO', form.value.id));
        form.value.reviewed_at = response.data.reviewed_at;
        form.value.reviewed_by = response.data.reviewed_by;
        
        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Purchase order has been reviewed successfully',
            confirmButtonColor: '#3085d6'
        });
        
        router.visit(route('supplies.index'));
    } catch (error) {
        console.error('Error reviewing PO:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data || 'Failed to review purchase order'
        });
    } finally {
        isProcessing.value.review = false;
    }
}

async function approvePO() {
    if (isProcessing.value.approve) return;

    const result = await Swal.fire({
        title: 'Approve Purchase Order',
        text: 'Are you sure you want to approve this purchase order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, approve it!'
    });

    if (!result.isConfirmed) return;

    try {
        isProcessing.value.approve = true;
        const response = await axios.post(route('supplies.approvePO', form.value.id));
        form.value.approved_at = response.data.approved_at;
        form.value.approved_by = response.data.approved_by;
        form.value.status = 'approved';
        
        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Purchase order has been approved successfully',
            confirmButtonColor: '#3085d6'
        });
        
        router.visit(route('supplies.index'));
    } catch (error) {
        console.error('Error approving PO:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data || 'Failed to approve purchase order'
        });
    } finally {
        isProcessing.value.approve = false;
    }
}

async function rejectPO() {
    if (isProcessing.value.reject) return;

    const { value: reason } = await Swal.fire({
        title: 'Rejection Reason',
        input: 'textarea',
        inputLabel: 'Please provide a reason for rejection',
        inputPlaceholder: 'Type your reason here...',
        inputAttributes: {
            'aria-label': 'Type your reason here'
        },
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Reject',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason!';
            }
        }
    });

    if (!reason) return;

    try {
        isProcessing.value.reject = true;
        const response = await axios.post(route('purchase-orders.reject', form.value.id), {
            reason: reason
        });
        form.value.rejected_at = response.data.rejected_at;
        form.value.rejected_by = response.data.rejected_by;
        
        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Purchase order has been rejected successfully',
            confirmButtonColor: '#3085d6'
        });
        
        router.visit(route('supplies.index'));
    } catch (error) {
        console.error('Error rejecting PO:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to reject purchase order'
        });
    } finally {
        isProcessing.value.reject = false;
    }
}

async function submitForm() {
    if (!form.value.supplier_id) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please select a supplier'
        });
        return;
    }

    if (form.value.items.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please add at least one item'
        });
        return;
    }

    // Validate items
    const invalidItems = form.value.items.filter(item => !item.product_id || !item.quantity || !item.unit_cost);
    if (invalidItems.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: 'Please fill in all required fields for each item (Product, Quantity, and Unit Cost)'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Update Purchase Order',
        text: 'Are you sure you want to update this purchase order?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    });

    if (!result.isConfirmed) return;

    isSubmitting.value = true;

    try {
        const response = await axios.put(route('supplies.updatePO', form.value.id), form.value);
        
        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Purchase order has been updated successfully',
            confirmButtonColor: '#3085d6'
        });
        
        router.visit(route('supplies.index'));
    } catch (error) {
        console.error('Error updating PO:', error);
        let errorMessage = 'Failed to update purchase order';
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.data) {
            // Handle validation errors
            const validationErrors = error.response.data;
            if (typeof validationErrors === 'object') {
                errorMessage = Object.values(validationErrors).flat().join('\n');
            } else {
                errorMessage = validationErrors;
            }
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage,
            confirmButtonColor: '#3085d6'
        });
    } finally {
        isSubmitting.value = false;
    }
}
</script>
