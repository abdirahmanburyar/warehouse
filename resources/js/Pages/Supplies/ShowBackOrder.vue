<template>
    <AuthenticatedLayout title="Back Order" description="Back Order History">
        <div class="py-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">SN</th>
                            <th class="px-4 py-2">Backorder ID</th>
                            <th class="px-4 py-2">Backorder Date</th>
                            <th class="px-4 py-2">Packing list Number</th>
                            <th class="px-4 py-2">Supplier Name</th>
                            <th class="px-4 py-2">Reported by Warehouse/Facility Name</th>
                            <th class="px-4 py-2">Note & Attachment</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(order, idx) in backOrders" :key="order.id">
                            <td class="px-4 py-2">{{ idx + 1 }}</td>
                            <td class="px-4 py-2">
                                <button class="text-blue-600 underline" @click="openHistoryModal(order)">
                                    {{ order.back_order_number }}
                                </button>
                            </td>
                            <td class="px-4 py-2">{{ formatDate(order.back_order_date) }}</td>
                            <td class="px-4 py-2">{{ order.packing_list?.packing_list_number }}</td>
                            <td class="px-4 py-2">{{ order.packing_list?.purchase_order?.supplier?.name || '-' }}</td>
                            <td class="px-4 py-2">{{ order.source_type || '-' }}</td>
                            <td class="px-4 py-2">
                                <div v-if="order.attach_documents && order.attach_documents.length">
                                    <div v-for="(doc, i) in order.attach_documents" :key="i">
                                        <a :href="doc.path" target="_blank" class="text-blue-600 underline">{{ doc.name }}</a>
                                    </div>
                                </div>
                                <div v-else>
                                    {{ order.notes || '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(order.status)">
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <!-- Actions (e.g., view, edit, export) -->
                                <button class="text-indigo-600 hover:text-indigo-900" @click="openHistoryModal(order)">View History</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal for BackOrderHistory -->
        <Modal :show="showModal" max-width="5xl" @close="showModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">Back Order History for {{ selectedOrder?.back_order_number }}</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">SN</th>
                            <th class="px-4 py-2">Item Name</th>
                            <th class="px-4 py-2">Expiry Date</th>
                            <th class="px-4 py-2">Batch Number</th>
                            <th class="px-4 py-2">QTY</th>
                            <th class="px-4 py-2">Backorder reason</th>
                            <th class="px-4 py-2">Note & Attachment</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(history, idx) in histories" :key="history.id">
                            <td class="px-4 py-2">{{ idx + 1 }}</td>
                            <td class="px-4 py-2">{{ history.product?.name }}</td>
                            <td class="px-4 py-2">{{ formatDate(history.expiry_date) }}</td>
                            <td class="px-4 py-2">{{ history.batch_number }}</td>
                            <td class="px-4 py-2">{{ history.quantity }}</td>
                            <td class="px-4 py-2">{{ history.status }}</td>
                            <td class="px-4 py-2">
                                <div v-if="history.attach_documents && history.attach_documents.length">
                                    <div v-for="(doc, i) in history.attach_documents" :key="i">
                                        <a :href="doc.path" target="_blank" class="text-blue-600 underline">{{ doc.name }}</a>
                                    </div>
                                </div>
                                <div v-else>
                                    {{ history.note || '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(history.status)">
                                    {{ history.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
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
</script>