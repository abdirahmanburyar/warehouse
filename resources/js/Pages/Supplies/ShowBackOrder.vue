<template>
    <AuthenticatedLayout title="Back Order" description="Back Order History">
        <div class="py-6" @click="handleOutsideClick">
            <div class="overflow-auto">
            </div>
            <table class="min-w-full border border-gray-300 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 border border-gray-300">SN</th>
                        <th class="px-4 py-2 border border-gray-300">Backorder ID</th>
                        <th class="px-4 py-2 border border-gray-300">Backorder Date</th>
                        <th class="px-4 py-2 border border-gray-300">Packing list Number</th>
                        <th class="px-4 py-2 border border-gray-300">Supplier Name</th>
                        <th class="px-4 py-2 border border-gray-300">Reported By</th>
                        <th class="px-4 py-2 border border-gray-300">Note & Attachment</th>
                        <th class="px-4 py-2 border border-gray-300">Status</th>
                        <th class="px-4 py-2 border border-gray-300">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <tr v-for="(order, idx) in backOrders" :key="order.id">
                        <td class="px-4 py-2 border border-gray-300">{{ idx + 1 }}</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <button class="text-blue-600 underline" @click="openHistoryModal(order)">
                                {{ order.back_order_number }}
                            </button>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">{{ formatDate(order.back_order_date) }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ order.packing_list?.packing_list_number }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ order.packing_list?.purchase_order?.supplier?.name || '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ order.reported_by || '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300">
                            <div v-if="order.attach_documents && order.attach_documents.length">
                                <div class="relative">
                                    <button @click.stop="toggleDropdown(order.id)" class="text-blue-600 underline">
                                        {{ order.attach_documents.length }} document(s)
                                    </button>
                                    <div v-if="openDropdowns.includes(order.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                        <div class="p-2">
                                            <div v-for="(doc, i) in order.attach_documents" :key="i" class="mb-1">
                                                <a :href="doc.path" target="_blank" class="text-blue-600 underline block text-xs">{{ doc.name }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div v-if="order.notes && order.notes.length > 50">
                                    <div class="relative">
                                        <button @click.stop="toggleDropdown(order.id)" class="text-gray-600">
                                            {{ order.notes.substring(0, 50) }}...
                                        </button>
                                        <div v-if="openDropdowns.includes(order.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                            <div class="p-2">
                                                <p class="text-xs">{{ order.notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    {{ order.notes || '-' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(order.status)">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border border-gray-300">
                            <!-- Actions (e.g., view, edit, export) -->
                            <button class="text-indigo-600 hover:text-indigo-900" @click="openHistoryModal(order)">View History</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal for BackOrderHistory -->
        <Modal :show="showModal" max-width="5xl" @close="showModal = false">
            <div class="p-6" @click="handleModalOutsideClick">
                <h2 class="text-lg font-semibold mb-4">Back Order History for {{ selectedOrder?.back_order_number }}</h2>
                <table class="min-w-full border border-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 border border-gray-300">SN</th>
                            <th class="px-4 py-2 border border-gray-300">Item Name</th>
                            <th class="px-4 py-2 border border-gray-300">Expiry Date</th>
                            <th class="px-4 py-2 border border-gray-300">Batch Number</th>
                            <th class="px-4 py-2 border border-gray-300">QTY</th>
                            <th class="px-4 py-2 border border-gray-300">Backorder reason</th>
                            <th class="px-4 py-2 border border-gray-300">Note & Attachment</th>
                            <th class="px-4 py-2 border border-gray-300">Status</th>
                            <th class="px-4 py-2 border border-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr v-for="(history, idx) in histories" :key="history.id">
                            <td class="px-4 py-2 border border-gray-300">{{ idx + 1 }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ history.product?.name }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ formatDate(history.expiry_date) }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ history.batch_number }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ history.quantity }}</td>
                            <td class="px-4 py-2 border border-gray-300">{{ history.status }}</td>
                            <td class="px-4 py-2 border border-gray-300">
                                <div v-if="history.attach_documents && history.attach_documents.length">
                                    <div class="relative">
                                        <button @click.stop="toggleHistoryDropdown(history.id)" class="text-blue-600 underline">
                                            {{ history.attach_documents.length }} document(s)
                                        </button>
                                        <div v-if="openHistoryDropdowns.includes(history.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                            <div class="p-2">
                                                <div v-for="(doc, i) in history.attach_documents" :key="i" class="mb-1">
                                                    <a :href="doc.path" target="_blank" class="text-blue-600 underline block text-xs">{{ doc.name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <div v-if="history.note && history.note.length > 50">
                                        <div class="relative">
                                            <button @click.stop="toggleHistoryDropdown(history.id)" class="text-gray-600">
                                                {{ history.note.substring(0, 50) }}...
                                            </button>
                                            <div v-if="openHistoryDropdowns.includes(history.id)" class="absolute z-10 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg">
                                                <div class="p-2">
                                                    <p class="text-xs">{{ history.note }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        {{ history.note || '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-2 border border-gray-300">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(history.status)">
                                    {{ history.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 border border-gray-300">
                                <!-- Actions (e.g., view, download attachment) -->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const backOrders = ref([]);
const histories = ref([]);
const showModal = ref(false);
const selectedOrder = ref(null);
const openDropdowns = ref([]);
const openHistoryDropdowns = ref([]);

onMounted(async () => {
    const { data } = await axios.get(route('supplies.backOrders.list'));
    backOrders.value = data;
});

function openHistoryModal(order) {
    selectedOrder.value = order;
    showModal.value = true;
    fetchHistories(order.id);
}

async function fetchHistories(backOrderId) {
    const { data } = await axios.get(route('supplies.backOrders.histories', backOrderId));
    histories.value = data;
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
}

function statusClass(status) {
    switch (status) {
        case 'pending':
        case 'Pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
        case 'Processing':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
        case 'Received':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function toggleDropdown(id) {
    if (openDropdowns.value.includes(id)) {
        openDropdowns.value = openDropdowns.value.filter((i) => i !== id);
    } else {
        openDropdowns.value.push(id);
    }
}

function toggleHistoryDropdown(id) {
    if (openHistoryDropdowns.value.includes(id)) {
        openHistoryDropdowns.value = openHistoryDropdowns.value.filter((i) => i !== id);
    } else {
        openHistoryDropdowns.value.push(id);
    }
}

function handleOutsideClick() {
    // Close all main table dropdowns when clicking outside
    openDropdowns.value = [];
}

function handleModalOutsideClick() {
    // Close all modal dropdowns when clicking outside
    openHistoryDropdowns.value = [];
}
</script>