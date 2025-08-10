<template>
    <div class="space-y-6">
        <!-- Form Modal -->
        <Modal :show="showForm" @close="closeForm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ form.id ? 'Edit Approval Step' : 'Add Approval Step' }}
                </h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Role Selection -->
                    <div>
                        <InputLabel for="role_id" value="Role" />
                        <select v-model="form.role_id" class="mt-1 block w-full">
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Model Type -->
                    <div>
                        <InputLabel for="model" value="Model Type" />
                        <select v-model="form.model" class="mt-1 block w-full">
                            <option value="">Select a model</option>
                            <option value="PurchaseOrderItem">Purchase Order Item</option>
                            <option value="Order">Order</option>
                        </select>
                    </div>

                    <!-- Action Type -->
                    <div>
                        <InputLabel for="action" value="Action" />
                        <select v-model="form.action" class="mt-1 block w-full">
                            <option value="">Select an action</option>
                            <option value="confirm">Confirm</option>
                            <option value="verify">Verify</option>
                            <option value="approve">Approve</option>
                        </select>
                    </div>

                    <!-- Sequence -->
                    <div>
                        <InputLabel for="sequence" value="Sequence" />
                        <input
                            id="sequence"
                            type="number"
                            min="1"
                            v-model="form.sequence"
                            class="mt-1 block w-full"
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <TextArea
                            id="notes"
                            v-model="form.notes"
                            class="mt-1 block w-full"
                            rows="3"
                        />
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 mt-6">
                        <SecondaryButton @click="closeForm" :disabled="isSubmitting">Cancel</SecondaryButton>
                        <PrimaryButton :disabled="isSubmitting">
                            {{ form.id ? isSubmitting ? 'Updating...' : 'Update' : isSubmitting ? 'Creating...' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Main Content -->
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">Approval Steps</h2>
            <PrimaryButton @click="openForm">Add New Step</PrimaryButton>
        </div>

        <!-- Approval Steps Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sequence</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="approval in approvals.data" :key="approval.id">
                        <td class="px-6 py-4 whitespace-nowrap">{{ getRoleName(approval.role_id) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ getModelName(approval.model) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getActionClass(approval.action)" class="px-2 py-1 text-xs rounded-full">
                                {{ approval.action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ approval.sequence }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ approval.notes || '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="editApproval(approval)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <button @click="deleteApproval(approval.id)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    approvals: Object,
    roles: Array,
});

const showForm = ref(false);
const form = ref({
    id: null,
    role_id: '',
    model: '',
    action: '',
    sequence: 1,
    notes: '',
});

const openForm = () => {
    form.value.id = null;
    form.value.role_id = '';
    form.value.model = '';
    form.value.action = '';
    form.value.sequence = 1;
    form.value.notes = '';
    showForm.value = true;
};

const closeForm = () => {
    form.value.id = null;
    showForm.value = false;
};

const isSubmitting = ref(false);

const submit = async () => {
    console.log(form.value);
    isSubmitting.value = true;
    await axios.post(route('approvals.store'), form.value)
        .then((response) => {
            isSubmitting.value = false;
            toast.success(response.data);
            closeForm();
        })
        .catch(error => {
            isSubmitting.value = false;
            toast.error(error.response.data)
        });
};

const editApproval = (approval) => {
    form.value.id = approval.id;
    form.value.role_id = approval.role_id;
    form.value.model = approval.model;
    form.value.action = approval.action;
    form.value.sequence = approval.sequence;
    form.value.notes = approval.notes;
    showForm.value = true;
};

const deleteApproval = (id) => {
    if (confirm('Are you sure you want to delete this approval step?')) {
        form.delete(route('approvals.destroy', id));
    }
};

const getRoleName = (roleId) => {
    const role = props.roles.find(r => r.id === roleId);
    return role ? role.name : 'Unknown';
};

const getModelName = (model) => {
    const modelMap = {
        'App\\Models\\PurchaseOrderItem': 'Purchase Order Item',
        'App\\Models\\Order': 'Order',
        'App\\Models\\Transfer': 'Transfer'
    };
    return modelMap[model] || model;
};

const getActionClass = (action) => {
    const classes = {
        confirm: 'bg-blue-100 text-blue-800',
        verify: 'bg-yellow-100 text-yellow-800',
        approve: 'bg-green-100 text-green-800'
    };
    return classes[action] || 'bg-gray-100 text-gray-800';
};
</script>