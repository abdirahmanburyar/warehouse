
<template>
    <AuthenticatedLayout title="Track Orders">
        <div class="py-2">
            <!-- Search and Filters -->
            <div class="mb-6 flex gap-4">
                <div class="flex-1">
                    <input type="text" v-model="search" placeholder="Search orders..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                </div>
                <div class="flex items-center gap-4">
                    <button @click="handleBulkAction" :disabled="selectedOrders.length === 0 || isBulkDeleting" :class="[
                        'px-4 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                        selectedOrders.length === 0
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-gray-900 text-white hover:bg-gray-800'
                    ]">
                        {{ isBulkDeleting ? 'Deleting' : 'Process' }} Selected Orders
                    </button>
                    <span class="text-sm text-gray-600" v-if="selectedOrders.length > 0">
                        {{ selectedOrders.length }} order(s) selected
                    </span>
                </div>
                <button @click="openModal"
                    class="bg-gray-900 text-white rounded-full px-6 py-2.5 text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-700 transition-colors duration-200 ease-in-out flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Create Order
                </button>
            </div>

            <!-- Status Tabs -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex w-full" aria-label="Order Status Tabs">
                    <button @click="filterByStatus('')" 
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                !status ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                        <i class="fas fa-list-ul"></i>
                        All
                    </button>
                    <button @click="filterByStatus('pending')"
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                status === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-yellow-600 hover:border-yellow-300'
                            ]">
                        <i class="fas fa-clock"></i>
                        Pending
                    </button>
                    <button @click="filterByStatus('approved')"
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                status === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-green-600 hover:border-green-300'
                            ]">
                        <i class="fas fa-check"></i>
                        Approved
                    </button>
                    <button @click="filterByStatus('in processing')"
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                status === 'in processing' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-blue-600 hover:border-blue-300'
                            ]">
                        <i class="fas fa-cog"></i>
                        In Process
                    </button>
                    <button @click="filterByStatus('dispatched')"
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                status === 'dispatched' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-purple-600 hover:border-purple-300'
                            ]">
                        <i class="fas fa-truck"></i>
                        Dispatched
                    </button>
                    <button @click="filterByStatus('delivered')"
                            :class="[
                                'flex-1 py-4 px-1 text-sm font-medium border-b-2 whitespace-nowrap flex items-center justify-center gap-2',
                                status === 'delivered' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-emerald-600 hover:border-emerald-300'
                            ]">
                        <i class="fas fa-box-check"></i>
                        Delivered
                    </button>
                </nav>
            </div>

            <!-- Orders Table -->
            <div class="bg-white overflow-hidden sm:rounded-lg">
                <div class="p-2">
                    <div class="flex justify-between">
                        <div class="w-full">
                        <div class="w-full overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll"
                                                    class="rounded border-gray-300 text-gray-900 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                            </div>
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-hashtag mr-2"></i>Order Number
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-building mr-2"></i>Facility
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-box mr-2"></i>Items
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-calendar mr-2"></i>Order Date
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-calendar mr-2"></i>Expected Date
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-clock mr-2"></i>Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <i class="fas fa-cog mr-2"></i>Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="order in props.orders.data" :key="order.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <input type="checkbox" v-model="selectedOrders" :value="order.id"
                                                    class="rounded border-gray-300 text-gray-900 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50">
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span class="text-sm font-medium text-gray-900">{{ order.order_number
                                                }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">F: {{
                                                    order.facility.name }}</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900">W: {{
                                                    order.warehouse.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ order.number_items }} items
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(order.order_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(order.expected_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col gap-2">
                                                <!-- Status Timeline -->
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex items-center">
                                                        <div :class="[
                                                            'w-8 h-8 rounded-full flex items-center justify-center cursor-help',
                                                            order.status === 'pending' ? 'bg-yellow-100 text-yellow-600' : 
                                                            order.status === 'approved' || order.status === 'in processing' || order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'
                                                        ]" title="Pending">
                                                            <i class="fas fa-clock text-xs"></i>
                                                        </div>
                                                        <div :class="[
                                                            'h-0.5 w-4',
                                                            order.status === 'approved' || order.status === 'in processing' || order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-500' : 'bg-gray-300'
                                                        ]"></div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div :class="[
                                                            'w-8 h-8 rounded-full flex items-center justify-center cursor-help',
                                                            order.status === 'approved' ? 'bg-green-100 text-green-600' :
                                                            order.status === 'in processing' || order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'
                                                        ]" title="Approved">
                                                            <i class="fas fa-check text-xs"></i>
                                                        </div>
                                                        <div :class="[
                                                            'h-0.5 w-4',
                                                            order.status === 'in processing' || order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-500' : 'bg-gray-300'
                                                        ]"></div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div :class="[
                                                            'w-8 h-8 rounded-full flex items-center justify-center cursor-help',
                                                            order.status === 'in processing' ? 'bg-blue-100 text-blue-600' :
                                                            order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'
                                                        ]" title="In Process">
                                                            <i class="fas fa-cog text-xs"></i>
                                                        </div>
                                                        <div :class="[
                                                            'h-0.5 w-4',
                                                            order.status === 'dispatched' || order.status === 'delivered' ? 'bg-green-500' : 'bg-gray-300'
                                                        ]"></div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div :class="[
                                                            'w-8 h-8 rounded-full flex items-center justify-center cursor-help',
                                                            order.status === 'dispatched' ? 'bg-purple-100 text-purple-600' :
                                                            order.status === 'delivered' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400'
                                                        ]" title="Dispatched">
                                                            <i class="fas fa-truck text-xs"></i>
                                                        </div>
                                                        <div :class="[
                                                            'h-0.5 w-4',
                                                            order.status === 'delivered' ? 'bg-green-500' : 'bg-gray-300'
                                                        ]"></div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <div :class="[
                                                            'w-8 h-8 rounded-full flex items-center justify-center cursor-help',
                                                            order.status === 'delivered' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'
                                                        ]" title="Delivered">
                                                            <i class="fas fa-box-check text-xs"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Status Badge -->
                                                <span :class="getStatusClass(order.status)">
                                                    {{ order.status }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex items-center space-x-2">
                                                <button v-if="order.status === 'pending'"
                                                    @click="changeStatus(order.id, 'approved')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                >
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button v-if="order.status === 'pending'"
                                                    @click="changeStatus(order.id, 'rejected')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <button v-if="order.status === 'approved'"
                                                    @click="changeStatus(order.id, 'in processing')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                >
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <button v-if="order.status === 'in processing'"
                                                    @click="changeStatus(order.id, 'dispatched')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                                                >
                                                    <i class="fas fa-truck"></i>
                                                </button>
                                                <button v-if="order.status === 'dispatched'"
                                                    @click="changeStatus(order.id, 'delivered')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                                >
                                                    <i class="fas fa-box-check"></i>
                                                </button>
                                                <button @click="viewOrder(order)"
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button v-if="order.status === 'pending'" @click="editOrder(order)"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button v-if="order.status === 'pending'" @click="confirmDelete(order)"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!props.orders.data.length">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No orders found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                    <div class="mt-4 flex items-center justify-between p-3">
                        <div class="text-sm text-gray-500" v-if="props.orders?.data?.length">
                            Showing {{ props.orders.meta.from }} to {{ props.orders.meta.to }} of {{
                                props.orders.meta.total }} results
                        </div>
                        <Pagination :links="props.orders.meta.links" />
                    </div>
                    </div>
                       
                        <div class="bg-white p-6 rounded-lg shadow-sm animate-[slideIn_0.5s_ease-out] motion-safe:transition-all motion-safe:duration-500">
                            <!-- Order Statistics -->
                            <div class="flex flex-col gap-2 fade-in">
                                <!-- Pending Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['Pending', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.pending || 0, (getTotalOrders || 1) - (props.stats?.pending || 0)],
                                                backgroundColor: ['#eab308', '#fef3c7'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.pending) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.pending || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">Pending</span>
                                    </div>
                                </div>
                                <!-- Approved Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['Approved', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.approved || 0, (getTotalOrders || 1) - (props.stats?.approved || 0)],
                                                backgroundColor: ['#16a34a', '#dcfce7'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.approved) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.approved || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">Approved</span>
                                    </div>
                                </div>

                                <!-- Rejected Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['Rejected', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.rejected || 0, (getTotalOrders || 1) - (props.stats?.rejected || 0)],
                                                backgroundColor: ['#dc2626', '#fee2e2'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.rejected) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.rejected || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">Rejected</span>
                                    </div>
                                </div>

                                <!-- In Processing Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['In Processing', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.['in processing'] || 0, (getTotalOrders || 1) - (props.stats?.['in processing'] || 0)],
                                                backgroundColor: ['#2563eb', '#dbeafe'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.['in processing']) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.['in processing'] || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">In Process</span>
                                    </div>
                                </div>

                                <!-- Dispatched Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['Dispatched', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.dispatched || 0, (getTotalOrders || 1) - (props.stats?.dispatched || 0)],
                                                backgroundColor: ['#9333ea', '#f3e8ff'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.dispatched) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.dispatched || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">Dispatched</span>
                                    </div>
                                </div>

                                <!-- Delivered Orders -->
                                <div class="relative h-12 flex items-center">
                                    <div class="relative w-12">
                                        <Doughnut :data="{
                                            labels: ['Delivered', 'Other'],
                                            datasets: [{
                                                data: [props.stats?.delivered || 0, (getTotalOrders || 1) - (props.stats?.delivered || 0)],
                                                backgroundColor: ['#4b5563', '#f3f4f6'],
                                                borderWidth: 0
                                            }]
                                        }" :options="{
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                cutout: '80%',
                                                plugins: {
                                                    legend: { display: false }
                                                }
                                            }" />
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-sm font-bold text-gray-900">{{ getPercentage(props.stats?.delivered) }}%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start flex-col">
                                        <span class="ml-3 text-xl text-gray-600">{{ props.stats?.delivered || 0 }}</span>
                                        <span class="ml-3 text-xs text-gray-600">Delivered</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>

        <!-- Order Modal -->
        <Modal :show="showModal" @close="closeModal" :maxWidth="'2xl'">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">
                    {{ isEditing ? 'Edit Order' : 'Create New Order' }}
                </h2>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Facility Selection -->
                    <div>
                        <InputLabel for="facility" value="Facility" />
                        <select id="facility" v-model="form.facility_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                            required>
                            <option value="">Select Facility</option>
                            <option v-for="f in props.facilities" :key="f.id" :value="f.id">
                                {{ f.name }}
                            </option>
                        </select>
                    </div>
                    <!-- Supplier Selection -->
                    <div>
                        <InputLabel for="warehouse" value="Supplier" />
                        <select id="warehouse" v-model="form.warehouse_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                            required>
                            <option value="">Select Supplier</option>
                            <option v-for="w in props.warehouses" :key="w.id" :value="w.id">
                                {{ w.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Order Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Order Date -->
                        <div>
                            <InputLabel for="order_date" value="Order Date" />
                            <input type="date" id="order_date" v-model="form.order_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                                required />
                            <div v-if="errors.order_date" class="text-red-500 text-sm mt-1">{{ errors.order_date }}
                            </div>
                        </div>

                        <!-- Expected Date -->
                        <div>
                            <InputLabel for="expected_date" value="Expected Date" />
                            <input type="date" id="expected_date" v-model="form.expected_date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                                :min="form.order_date" />
                            <div v-if="errors.expected_date" class="text-red-500 text-sm mt-1">{{ errors.expected_date
                                }}</div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div>
                        <InputLabel value="Order Items" />
                        <div v-for="(item, index) in form.items" :key="index" class="flex gap-4 mt-2">
                            <div class="flex-1">
                                <Multiselect v-model="item.product" :options="searchResults" :searchable="true"
                                    :loading="isLoading" :internal-search="false" :clear-on-select="true"
                                    :close-on-select="true" :options-limit="300" :max-height="600"
                                    :show-no-results="true" :hide-selected="true" @search-change="searchProduct"
                                    placeholder="Search product by name or barcode" label="name" track-by="id"
                                    :preselect-first="false" @select="selectProduct(index, $event)" class="mt-1 w-full">
                                    <template v-slot:noResult>
                                        <div class="text-gray-500">No products found</div>
                                    </template>
                                </Multiselect>
                            </div>
                            <div class="w-32">
                                <input type="number" v-model="item.quantity" min="1" placeholder="Qty"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200"
                                    required />
                            </div>
                            <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="button" @click="addItem" class="mt-2 text-sm text-gray-600 hover:text-gray-900">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                        <div v-if="errors.items" class="text-red-500 text-sm mt-1">{{ errors.items }}</div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <textarea id="notes" v-model="form.notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50"
                            placeholder="Add any special instructions or notes..."></textarea>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="processing" :class="{ 'opacity-25': processing }" @click="submitForm">
                            {{ isEditing ? 'Update Order' : 'Create Order' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Order Modal -->
        <Modal :show="showViewModal" @close="closeViewModal" :maxWidth="'7xl'">
            <div v-if="selectedOrder" class="p-8 bg-white" style="min-height: 29.7cm; width: 21cm; margin: 0 auto;">
                <!-- Order Header -->
                <div class="bg-gray-50 p-6 mb-6 rounded-lg border">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Order #{{ selectedOrder.order_number }}</h1>
                            <p class="text-sm text-gray-600 mt-1">Created on {{ formatDate(selectedOrder.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span :class="[
                                'px-3 py-1 text-sm font-medium rounded-full',
                                selectedOrder.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                    selectedOrder.status === 'approved' ? 'bg-green-100 text-green-800' :
                                        selectedOrder.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                            selectedOrder.status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                                selectedOrder.status === 'dispatched' ? 'bg-purple-100 text-purple-800' :
                                                    'bg-gray-100 text-gray-800'
                            ]">
                                {{ selectedOrder.status?.charAt(0).toUpperCase() + selectedOrder.status?.slice(1) ||
                                    'N/A' }}
                            </span>
                            <p class="text-sm text-gray-600 mt-2">Expected by {{ formatDate(selectedOrder.expected_date)
                                }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Details Grid -->
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Facility Information -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b">
                                <h2 class="font-semibold text-gray-900">Facility Information</h2>
                            </div>
                            <div class="p-4 space-y-3">
                                <p class="text-lg font-medium text-gray-900">{{ selectedOrder.facility?.name || 'N/A' }}
                                </p>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><i class="fas fa-envelope w-5"></i> {{ selectedOrder.facility?.email || 'N/A' }}
                                    </p>
                                    <p><i class="fas fa-phone w-5"></i> {{ selectedOrder.facility?.phone || 'N/A' }}</p>
                                    <p><i class="fas fa-map-marker-alt w-5"></i> {{ selectedOrder.facility?.address ||
                                        'N/A' }}</p>
                                    <p class="ml-5">{{ [selectedOrder.facility?.city,
                                    selectedOrder.facility?.state].filter(Boolean).join(', ') || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b">
                                <h2 class="font-semibold text-gray-900">Order Items</h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                Product</th>
                                            <th
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                                Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in selectedOrder.items" :key="item.id">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ item.product?.name || 'N/A'
                                                }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ item.quantity }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Supplier Information -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b">
                                <h2 class="font-semibold text-gray-900">Supplier Information</h2>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <p class="text-lg font-medium text-gray-900">{{ selectedOrder.warehouse?.name ||
                                        'N/A' }}</p>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="font-medium text-gray-700">Contact Information</p>
                                            <div class="mt-2 space-y-1 text-gray-600">
                                                <p><i class="fas fa-user w-5"></i> {{
                                                    selectedOrder.warehouse?.manager_name || 'N/A' }}</p>
                                                <p><i class="fas fa-envelope w-5"></i> {{
                                                    selectedOrder.warehouse?.manager_email || 'N/A' }}</p>
                                                <p><i class="fas fa-phone w-5"></i> {{
                                                    selectedOrder.warehouse?.manager_phone || 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-700">Location</p>
                                            <div class="mt-2 space-y-1 text-gray-600">
                                                <p>{{ selectedOrder.warehouse?.address || 'N/A' }}</p>
                                                <p>{{ [selectedOrder.warehouse?.city, selectedOrder.warehouse?.state,
                                                selectedOrder.warehouse?.postal_code].filter(Boolean).join(', ') ||
                                                    'N/A' }}</p>
                                                <p>{{ selectedOrder.warehouse?.country || 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t">
                                        <p class="font-medium text-gray-700">Storage Capabilities</p>
                                        <div class="mt-2 grid grid-cols-2 gap-4 text-sm text-gray-600">
                                            <div>
                                                <p><i class="fas fa-thermometer-half w-5"></i> Temperature: {{
                                                    selectedOrder.warehouse?.temperature_min || 'N/A' }}°C to {{
                                                        selectedOrder.warehouse?.temperature_max || 'N/A' }}°C</p>
                                                <p><i class="fas fa-tint w-5"></i> Humidity: {{
                                                    selectedOrder.warehouse?.humidity_min || 'N/A' }}% to {{
                                                        selectedOrder.warehouse?.humidity_max || 'N/A' }}%</p>
                                            </div>
                                            <div>
                                                <p><i class="fas fa-warehouse w-5"></i> Capacity: {{
                                                    selectedOrder.warehouse?.capacity || 'N/A' }}</p>
                                                <p v-if="selectedOrder.warehouse?.has_cold_storage"
                                                    class="text-blue-600">
                                                    <i class="fas fa-snowflake w-5"></i> Cold Storage Available
                                                </p>
                                                <p v-if="selectedOrder.warehouse?.has_hazardous_storage"
                                                    class="text-yellow-600">
                                                    <i class="fas fa-exclamation-triangle w-5"></i> Hazardous Storage
                                                    Available
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b">
                                <h2 class="font-semibold text-gray-900">Order Timeline</h2>
                            </div>
                            <div class="p-4">
                                <div class="relative">
                                    <div class="absolute h-full w-0.5 bg-gray-200 left-[15px] top-0"></div>
                                    <div class="space-y-6">
                                        <div class="flex items-start gap-4">
                                            <div class="relative z-10">
                                                <div
                                                    class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900">Order Placed</p>
                                                <p class="text-sm text-gray-600">{{ formatDate(selectedOrder.created_at)
                                                    }}</p>
                                                <p class="text-sm text-gray-600">by {{ selectedOrder.user?.name || 'N/A'
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-4">
                                            <div class="relative z-10">
                                                <div :class="[
                                                    'w-8 h-8 rounded-full flex items-center justify-center',
                                                    selectedOrder.approved_at ? 'bg-green-500' : (selectedOrder.rejected_at ? 'bg-red-500' : 'bg-gray-200')
                                                ]">
                                                    <i :class="[
                                                        'text-sm',
                                                        selectedOrder.approved_at ? 'fas fa-check text-white' : (selectedOrder.rejected_at ? 'fas fa-times text-white' : 'fas fa-clock text-gray-400')
                                                    ]"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900">{{ selectedOrder.rejected_at ?
                                                    'Order Rejected' : 'Order Approval' }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ selectedOrder.approved_at ? formatDate(selectedOrder.approved_at)
                                                        :
                                                        (selectedOrder.rejected_at ? formatDate(selectedOrder.rejected_at) :
                                                            'Pending') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div v-if="!selectedOrder.rejected_at" class="space-y-6">
                                            <div class="flex items-start gap-4">
                                                <div class="relative z-10">
                                                    <div :class="[
                                                        'w-8 h-8 rounded-full flex items-center justify-center',
                                                        selectedOrder.processed_at ? 'bg-green-500' : 'bg-gray-200'
                                                    ]">
                                                        <i :class="[
                                                            'fas fa-cog text-sm',
                                                            selectedOrder.processed_at ? 'text-white' : 'text-gray-400'
                                                        ]"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900">Processing</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ selectedOrder.processed_at ?
                                                            formatDate(selectedOrder.processed_at) : 'Pending' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div class="relative z-10">
                                                    <div :class="[
                                                        'w-8 h-8 rounded-full flex items-center justify-center',
                                                        selectedOrder.dispatched_at ? 'bg-green-500' : 'bg-gray-200'
                                                    ]">
                                                        <i :class="[
                                                            'fas fa-truck text-sm',
                                                            selectedOrder.dispatched_at ? 'text-white' : 'text-gray-400'
                                                        ]"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900">Dispatched</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ selectedOrder.dispatched_at ?
                                                            formatDate(selectedOrder.dispatched_at) : 'Pending' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-4">
                                                <div class="relative z-10">
                                                    <div :class="[
                                                        'w-8 h-8 rounded-full flex items-center justify-center',
                                                        selectedOrder.delivered_at ? 'bg-green-500' : 'bg-gray-200'
                                                    ]">
                                                        <i :class="[
                                                            'fas fa-box text-sm',
                                                            selectedOrder.delivered_at ? 'text-white' : 'text-gray-400'
                                                        ]"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-900">Delivered</p>
                                                    <p class="text-sm text-gray-600">
                                                        {{ selectedOrder.delivered_at ?
                                                            formatDate(selectedOrder.delivered_at) : 'Pending' }}
                                                    </p>
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
            <div v-else class="p-8 bg-white text-center">
                <div v-if="loading" class="flex items-center justify-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-gray-600">Loading order details...</span>
                </div>
                <div v-else class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2">No order selected</p>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Order</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this order? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeDeleteModal">Cancel</SecondaryButton>
                    <DangerButton @click="deleteOrder" :class="{ 'opacity-25': processing }" :disabled="processing">
                        Delete Order
                    </DangerButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Multiselect from 'vue-multiselect'
import "vue-multiselect/dist/vue-multiselect.css";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useToast } from 'vue-toastification';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js';
import moment from 'moment';
import Swal from 'sweetalert2'
import axios from 'axios';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    orders: Object,
    stats: Object,
    facilities: Array,
    filters: Object,
    warehouses: Array,
    errors: Object,
});

const getTotalOrders = computed(() => {
    if (!props.stats) return 0;
    return Object.values(props.stats).reduce((a, b) => a + b, 0);
});

const getPercentage = (value) => {
    if (!value || !getTotalOrders.value) return 0;
    return Math.round((value / getTotalOrders.value) * 100);
};

const toast = useToast();

const showStats = ref(true);
const showModal = ref(false);
const showDeleteModal = ref(false);
const showViewModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);
const errors = ref({});
const selectedOrder = ref(null);
const loading = ref(false);
const isBulkDeleting = ref(false);
const selectedOrders = ref([]);
const selectAll = ref(false);
const searchResults = ref([]);
const isLoading = ref(false);

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const page = ref(props.filters?.page || 1);

const form = ref({
    id: null,
    warehouse_id: '',
    facility_id: '',
    items: [
        {
            id: null,
            product_id: '',
            product: null,
            quantity: 1
        }
    ],
    notes: '',
    order_date: new Date().toISOString().split('T')[0],
    expected_date: ''
});

const getStatusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
        case 'rejected':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        case 'in processing':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
        case 'dispatched':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800';
        case 'delivered':
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800';
        default:
            return 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
    }
};

function reloadOrder() {
    router.get(route('orders.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['orders', 'stats']
    });
}

async function changeStatus(orderId, status) {
    try {
        const response = await axios.post(route('orders.change-status'), {
            status,
            id: orderId
        });
        
        if (response.data) {
            toast.success('Order status updated successfully');
            reloadOrder();
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        toast.error(error.response?.data || 'Failed to update order status');
    }
}

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

onMounted(() => {
    if (!window.Echo) {
        console.error('Echo not initialized. Make sure Laravel Echo is properly set up.');
        return;
    }

    window.Echo.channel('orders')
        .listen('.order-received', function (event) {
            reloadOrder();
        });
});

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const openModal = () => {
    showModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedOrder.value = null;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedOrder.value = null;
};

const removeItem = (index) => {
    form.value.items.splice(index, 1);
};

const addItem = () => {
    form.value.items.push({
        id: null,
        product: null,
        product_id: '',
        quantity: 1
    });
};

const selectProduct = (index, product) => {
    form.value.items[index].product_id = product.id;
};

const filterByStatus = (newStatus) => {
    router.get(
        route('orders.index'),
        { status: newStatus === status.value ? '' : newStatus },
        { preserveState: true, preserveScroll: true }
    );
};

const editOrder = (order) => {
    form.value = {
        id: order.id,
        warehouse_id: order.warehouse_id,
        facility_id: order.facility_id,
        items: order.items.map(item => ({
            id: item.id,
            product_id: item.product.id,
            quantity: item.quantity,
            product: {
                id: item.product.id,
                name: item.product.name
            }
        })),
        notes: order.notes || '',
        order_date: order.order_date ? new Date(order.order_date).toISOString().split('T')[0] : '',
        expected_date: order.expected_date ? new Date(order.expected_date).toISOString().split('T')[0] : ''
    };
    isEditing.value = true;
    showModal.value = true;
};

const submitForm = async () => {
    // Validate items
    if (!form.value.items.some(item => item.product_id && item.quantity > 0)) {
        toast.error('Please add at least one product with quantity');
        return;
    }

    const formData = {
        ...form.value,
        items: form.value.items.map(item => ({
            id: item.id,
            product_id: item.product_id,
            quantity: item.quantity
        }))
    };
    processing.value = true;
    await axios.post(route('orders.store'), formData)
        .then((response) => {
            processing.value = false;
            toast.success(response.data);
            closeModal();
            reloadOrder();
        })
        .catch((error) => {
            console.log(error);
            processing.value = false;
            toast.error(error.response.data || 'An error occurred');
        });
};

const resetForm = () => {
    form.value = {
        id: null,
        warehouse_id: '',
        facility_id: '',
        items: [
            {
                id: null,
                product_id: '',
                product: null,
                quantity: 1
            }
        ],
        notes: '',
        order_date: new Date().toISOString().split('T')[0],
        expected_date: ''
    };
};

const confirmDelete = (order) => {
    selectedOrder.value = order
    showDeleteModal.value = true
}

const deleteOrder = async () => {
    if (!selectedOrder.value) return

    processing.value = true
    try {
        await axios.delete(`/api/orders/${selectedOrder.value.id}`)
        toast.success('Order deleted successfully')
        closeDeleteModal()
        router.get(route('orders.index'), {}, { preserveState: true })
    } catch (error) {
        toast.error(error.response.data || 'An error occurred')
    } finally {
        processing.value = false
    }
}

const viewOrder = (order) => {
    selectedOrder.value = order
    loading.value = true
    setTimeout(() => {
        loading.value = false
        showViewModal.value = true
    }, 1000)
}

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedOrders.value = props.orders.data.map(order => order.id)
    } else {
        selectedOrders.value = []
    }
}

const handleBulkAction = async () => {
    isBulkDeleting.value = true
    if (selectedOrders.value.length === 0) {
        isBulkDeleting.value = false
        toast.warning('Please select at least one order')
        return
    }

    await axios.post(route('orders.bulk'), {
        orderIds: selectedOrders.value
    })
        .then((response) => {
            isBulkDeleting.value = false
            toast.success(response.data);
            reloadOrder();
            selectedOrder.value = [];
        })
        .catch((error) => {
            isBulkDeleting.value = false
            Swal.fire('Error', error.response.data || 'An error occurred', 'error')
        })
}

const searchProduct = async (query) => {
    if (query == '') {
        searchResults.value = [];
        return;
    };
    isLoading.value = true;
    await axios.post(route('order.product.search'), {
        search: query
    })
        .then((response) => {
            isLoading.value = false;
            searchResults.value = response.data;
        })
        .catch((error) => {
            isLoading.value = false;
            toast.error(error.response?.data || 'An error occurred');
        })
};
</script>
<style scoped>
@keyframes slideInRight {
    0% {
        opacity: 0;
        transform: translateX(30px);
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

.fade-in {
    animation: slideInRight 0.5s ease-out;
}
</style>