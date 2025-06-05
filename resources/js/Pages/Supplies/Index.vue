<template>

    <Head title="Purchase Orders" />
    <AuthenticatedLayout title="Manage Your Purchase Orders"
        description="Ensuring an Optimcal Flow of Essentials Resource" img="/assets/images/supplies.png">
        <div class="overflow-hidden mb-[80px]">
            <div class="text-gray-900">
                <!-- Action Buttons Row -->
                <div class="flex flex-wrap items-center justify-end gap-2 px-2 mb-6">
                    <div class="relative inline-block text-left z-20" ref="backOrderDropdownRef">
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-3xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            id="back-order-menu" :aria-expanded="showBackOrderDropdown" aria-haspopup="true"
                            @click.stop="toggleBackOrderDropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Back Orders
                        </button>
                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <div v-if="showBackOrderDropdown"
                                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu" aria-orientation="vertical" aria-labelledby="back-order-menu">
                                <div class="py-1" role="none">
                                    <a @click="router.get(route('supplies.back-order'))"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                        role="menuitem">
                                        Back Order
                                    </a>
                                    <a @click="router.get(route('supplies.showBackOrder'))"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                        role="menuitem">
                                        View Back Order
                                    </a>
                                </div>
                            </div>
                        </transition>
                    </div>
                    <div class="relative inline-block text-left z-20" ref="supplyDropdownRef">
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-3xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            id="supply-menu" :aria-expanded="showSupplyDropdown" aria-haspopup="true"
                            @click.stop="toggleSupplyDropdown">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Supplies
                        </button>
                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <div v-if="showSupplyDropdown"
                                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu" aria-orientation="vertical" aria-labelledby="supply-menu">
                                <div class="py-1" role="none">
                                    <a @click="router.get(route('supplies.packing-list'))"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                        role="menuitem">
                                        Receive New PL
                                    </a>
                                    <a @click="router.get(route('supplies.packing-list.showPK'))"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                        role="menuitem">
                                        View PL Lists
                                    </a>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <button @click="router.get(route('supplies.purchase_order'))"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-3xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create new Purchase order
                    </button>
                    <div class="relative inline-block text-left z-20" ref="dropdownRef">
                        <button type="button"
                            class="px-4 py-2 bg-gray-800 text-white hover:bg-gray-700 transition-colors rounded-3xl duration-200 inline-flex items-center gap-2"
                            id="options-menu" :aria-expanded="showDropdown" aria-haspopup="true"
                            @click.stop="toggleDropdown">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            List of Suppliers
                        </button>
                        <transition enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95">
                            <div v-if="showDropdown"
                                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                <div class="py-1" role="none">
                                    <a @click="navigateToCreateSupplier"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer"
                                        role="menuitem">
                                        Create Supplier
                                    </a>
                                    <a href="#" @click="navigateToViewSupplier"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                        role="menuitem">
                                        View Suppliers
                                    </a>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Search and Filter Row -->
                <div class="flex flex-wrap justify-between w-full px-2 mb-6">
                    <div class="flex-1 mr-2">
                        <label for="search" class="text-sm font-medium text-gray-700">Search</label>
                        <input type="text" v-model="search" placeholder="Search by PO number, supplier or status..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                    </div>
                    <div class="flex-1 mx-2">
                        <label for="supplier" class="text-sm font-medium text-gray-700">Supplier</label>
                        <select v-model="supplier"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Filter by Supplier</option>
                            <option :value="s.id" v-for="s in props.suppliers">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="flex-1 ml-2">
                        <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                        <select v-model="status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Filter by Status</option>
                            <option value="pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="flex-1 ml-2 w-[100px]">
                        <label for="per_page" class="text-sm font-medium text-gray-700">Per Page</label>
                        <select v-model="per_page" @change="props.filters.page = 1"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-[#F7DC6F] rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Supply Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_items }}</p>
                            </div>
                            <div class="p-3 bg-orange-100 rounded-full">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-500 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Cost of Supplies Received</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_cost) }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-orange-500 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="w-full">
                                <div class="flex flex-col w-full">
                                    <div class="flex items-center gap-5">
                                        <p class="text-xs font-bold text-black-600">Max Lead Time</p>
                                        <p class="text-lg text-gray-900 text-center">{{ stats.lead_times?.max }}</p>
                                    </div>
                                    <div class="flex items-center gap-5 ">
                                        <p class="text-xs font-bold text-black-600">Avg Lead Time</p>
                                        <p class="text-lg text-gray-900 text-center">{{ stats.lead_times?.avg }}</p>
                                    </div>
                                    <div class="flex items-center gap-5">
                                        <p class="text-xs font-bold text-black-600">Low Lead Time</p>
                                        <p class="text-lg text-gray-900 text-center">{{ stats.lead_times?.low }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 bg-red-100 rounded-full">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-teal-500 rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Number of Back Orders</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.back_orders }}</p>
                            </div>
                            <div class="p-3 bg-teal-100 rounded-full">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#9333EA] rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-black-600">Requested Purchase Orders</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.pending_orders }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Orders Table and Statistics Row -->
                <div class="px-2 mb-6">
                    <div class="grid grid-cols-12 gap-4">
                        <!-- Table Column (10/12) -->
                        <div class="col-span-10">
                            <div class="relative">
                                <div class="overflow-auto max-h-[calc(100vh-300px)]">
                                    <table class="min-w-full text-left border-b border-gray-200">
                                        <thead class="bg-white sticky top-0 z-10">
                                            <tr>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    SN#
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    PO Number
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    Supplier
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    P.O Date
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    Total Amount
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    Status
                                                </th>
                                                <th class="px-4 py-3 text-left align-middle  text-blue-700 whitespace-nowrap font-bold text-sm text-black rounded-tl-3xl text-left" style="background: #F7F9FB;">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(po, i) in props.purchaseOrders.data" :key="po.id" class="border-b border-gray-100">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black text-left">{{ i + 1 }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-black text-left">
                                                    <Link :href="route('supplies.editPO', po.id)"
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                    {{ po.po_number }}
                                                    </Link>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-black">
                                                    {{ po.supplier?.name || 'No supplier' }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-black text-left">
                                                    {{ moment(po.po_date).format('DD/MM/YYYY') }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-black text-left">
                                                    {{formatCurrency(po.items?.reduce((sum, item) => sum +
                                                        (item.total_cost || 0),
                                                    0) || 0) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <div class="flex items-center space-x-4">
                                                        <!-- Always show Pending Icon (all statuses start as pending) -->
                                                        <img src="/assets/images/pending.png" class="w-12 h-12"
                                                            alt="Pending" />

                                                        <!-- Show Reviewed Icon if status is reviewed, approved, or rejected -->
                                                        <svg v-if="po.status === 'reviewed' || po.status === 'approved' || po.status === 'rejected'"
                                                            class="w-12 h-12 text-blue-700" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>

                                                        <!-- Show Approved Icon if status is approved -->
                                                        <img v-if="po.status === 'approved'"
                                                            src="/assets/images/approved.png" class="w-12 h-12"
                                                            alt="Approved" />

                                                        <!-- Show Rejected Icon if status is rejected -->
                                                        <svg v-if="po.status === 'rejected'"
                                                            class="w-12 h-12 text-red-700" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-left text-sm font-medium space-x-2">
                                                    <button @click="router.visit(route('supplies.po-show', po.id))"
                                                        class="text-gray-600 hover:text-gray-900">
                                                        <EyeIcon class="h-5 w-5" />
                                                    </button>
                                                    <button @click="router.visit(route('supplies.editPO', po.id))"
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            viewBox="0 0 20 20" fill="currentColor">
                                                            <path
                                                                d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                            <path fill-rule="evenodd"
                                                                d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                    <button @click="confirmDelete(po.id)"
                                                        class="text-red-600 hover:text-red-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr v-if="!purchaseOrders?.data?.length">
                                                <td colspan="7"
                                                    class="px-6 py-4 text-center text-gray-500">

                                                    No purchase orders found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="flex justify-end mt-3 mb-6">
                                <TailwindPagination
                                :data="props.purchaseOrders"
                                @pagination-change-page="getResults"
                                :limit="2"
                            />
                            </div>
                        </div>

                        <!-- Statistics Column (2/12) -->
                        <div class="col-span-2">
                            <div class="sticky top-4 bg-white p-4 h-full">
                                <h3 class="text-sm font-bold text-gray-900 mb-6">Order Statistics</h3>
                                <div class="space-y-8">
                                    <!-- Pending -->
                                    <div class="relative">
                                        <div class="flex items-center mb-2">
                                            <div class="w-16 h-16 relative mr-4">
                                                <svg class="w-16 h-16 transform -rotate-90">
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0"
                                                        stroke-width="4" />
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#eab308"
                                                        stroke-width="4"
                                                        :stroke-dasharray="`${(stats.pending_orders / (stats.total_orders || 1)) * 175.9} 175.9`" />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="text-base font-bold text-yellow-600">{{
                                                        stats.total_orders > 0 ?
                                                            Math.round((stats.pending_orders / stats.total_orders) * 100) :
                                                        0 }}%</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-gray-900">{{ stats.pending_orders ||
                                                    0 }}</div>
                                                <div class="text-base text-gray-600">Pending</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reviewed -->
                                    <div class="relative">
                                        <div class="flex items-center mb-2">
                                            <div class="w-16 h-16 relative mr-4">
                                                <svg class="w-16 h-16 transform -rotate-90">
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0"
                                                        stroke-width="4" />
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#3b82f6"
                                                        stroke-width="4"
                                                        :stroke-dasharray="`${(stats.reviewed_orders / (stats.total_orders || 1)) * 175.9} 175.9`" />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="text-base font-bold text-blue-600">{{
                                                        stats.total_orders > 0 ?
                                                            Math.round((stats.reviewed_orders / stats.total_orders) * 100) :
                                                        0 }}%</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-gray-900">{{ stats.reviewed_orders ||
                                                    0 }}</div>
                                                <div class="text-base text-gray-600">Reviewed</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Approved -->
                                    <div class="relative">
                                        <div class="flex items-center mb-2">
                                            <div class="w-16 h-16 relative mr-4">
                                                <svg class="w-16 h-16 transform -rotate-90">
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0"
                                                        stroke-width="4" />
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#22c55e"
                                                        stroke-width="4"
                                                        :stroke-dasharray="`${(stats.approved_orders / (stats.total_orders || 1)) * 175.9} 175.9`" />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="text-base font-bold text-green-600">{{
                                                        stats.total_orders > 0 ?
                                                            Math.round((stats.approved_orders / stats.total_orders) * 100) :
                                                        0 }}%</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-gray-900">{{ stats.approved_orders ||
                                                    0 }}</div>
                                                <div class="text-base text-gray-600">Approved</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Rejected -->
                                    <div class="relative">
                                        <div class="flex items-center mb-2">
                                            <div class="w-16 h-16 relative mr-4">
                                                <svg class="w-16 h-16 transform -rotate-90">
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0"
                                                        stroke-width="4" />
                                                    <circle cx="32" cy="32" r="28" fill="none" stroke="#ef4444"
                                                        stroke-width="4"
                                                        :stroke-dasharray="`${(stats.rejected_orders / (stats.total_orders || 1)) * 175.9} 175.9`" />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <span class="text-base font-bold text-red-600">{{ stats.total_orders
                                                        > 0 ?
                                                        Math.round((stats.rejected_orders / stats.total_orders) * 100) :
                                                        0 }}%</span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-lg font-bold text-gray-900">{{ stats.rejected_orders ||
                                                    0 }}</div>
                                                <div class="text-base text-gray-600">Rejected</div>
                                            </div>
                                        </div>
                                    </div>


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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from "vue-toastification";
import Swal from 'sweetalert2';
import axios from 'axios';
import { TailwindPagination } from 'laravel-vue-pagination';
import moment from 'moment';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { EyeIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const dropdownRef = ref(null);
const showDropdown = ref(false);
const showSupplyDropdown = ref(false);
const supplyDropdownRef = ref(null);
const backOrderDropdownRef = ref(null);
const showBackOrderDropdown = ref(false);

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
    showSupplyDropdown.value = false;
    showBackOrderDropdown.value = false;
};

const toggleSupplyDropdown = () => {
    showSupplyDropdown.value = !showSupplyDropdown.value;
    showDropdown.value = false;
    showBackOrderDropdown.value = false;
};

const toggleBackOrderDropdown = () => {
    showBackOrderDropdown.value = !showBackOrderDropdown.value;
    showDropdown.value = false;
    showSupplyDropdown.value = false;
};

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
            showDropdown.value = false;
        }
        if (supplyDropdownRef.value && !supplyDropdownRef.value.contains(e.target)) {
            showSupplyDropdown.value = false;
        }
        if (backOrderDropdownRef.value && !backOrderDropdownRef.value.contains(e.target)) {
            showBackOrderDropdown.value = false;
        }
    });
});

function getResults(page = 1){
    props.filters.page = page;
}

const props = defineProps({
    purchaseOrders: {
        type: Object,
        required: true,
    },
    filters: Object,
    suppliers: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_items: 0,
            total_cost: 0,
            lead_times: {
                max: '0 Months',
                min: '0 Months',
                avg: '0 Months'
            },
            pending_orders: 0,
            reviewed_orders: 0,
            approved_orders: 0,
            rejected_orders: 0,
            total_orders: 0,
            back_orders: 0,
        })
    },
    suppliers: {
        required: true,
        type: Array
    }
});

const search = ref(props.filters?.search || '');
const supplier = ref(props.filters?.supplier || '')
const status = ref(props.filters?.status || '')
const per_page = ref(props.filters?.per_page)

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

function reloadPO() {
    const query = {}
    if (search.value) query.search = search.value;
    if (supplier.value) query.supplier = supplier.value;
    if (status.value) query.status = status.value;
    if (per_page.value) {
        query.per_page = per_page.value;
    }
    if(props.filters.page) query.page = props.filters.page;
    router.get(route('supplies.index'), query, {
        preserveScroll: false,
        preserveState: false,
        only: [
            'purchaseOrders', 'filters'
        ]
    })
}

const confirmDelete = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        await axios.get(route('supplies.deletePO', id))
            .then((response) => {
                toast.success(response.data);
                reloadPO();
            })
            .catch((error) => {
                console.log(error);
                toast.error(error.response.data)
            });
    }
};

// Calculate order statistics based on purchaseOrders status
const orderStats = computed(() => {
    const orders = props.purchaseOrders?.data || [];
    const pending = orders.filter(order => order.status?.toLowerCase() === 'pending').length;
    const reviewed = orders.filter(order => order.status?.toLowerCase() === 'reviewed').length;
    const approved = orders.filter(order => order.status?.toLowerCase() === 'approved').length;
    const rejected = orders.filter(order => order.status?.toLowerCase() === 'rejected').length;
    const total = orders.length;

    return {
        pending_orders: pending,
        reviewed_orders: reviewed,
        approved_orders: approved,
        rejected_orders: rejected,
        total_orders: total
    };
});

const stats = computed(() => {
    return {
        ...props.stats || {
            total_items: 0,
            total_cost: 0,
            lead_times: {
                max: '0 Months',
                min: '0 Months',
                avg: '0 Months'
            },
            back_orders: 0
        },
        ...orderStats.value
    };
});


const navigateToCreateSupplier = () => {
    router.get(route('supplies.create'));
}

const navigateToViewSupplier = () => {
    router.get(route('supplies.show'));
}

watch([
    () => search.value,
    () => supplier.value,
    () => status.value,
    () => status.per_page,
    () => props.filters.page
], () => {
    reloadPO();
});
</script>