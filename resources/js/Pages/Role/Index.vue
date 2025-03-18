<template>

  <Head title="Role Management" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Role Management</h2>
    </template>

    <div>
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <div v-if="$page.props.flash && $page.props.flash.success"
            class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            {{ $page.props.flash.error }}
          </div>

          <!-- Role Creation Form -->
          <div class="mb-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-medium mb-4">Create New Role</h3>
            <form @submit.prevent="createRole">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <InputLabel for="name" value="Role Name" />
                  <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div>
                  <InputLabel value="Permissions" />
                  <SelectInput v-model="form.permissions" :options="permissions" multiple class="mt-1 block w-full"
                    placeholder="Select permissions" />
                  <InputError :message="form.errors.permissions" class="mt-2" />
                </div>
              </div>

              <div class="mt-4 flex justify-end">
                <PrimaryButton :disabled="form.processing">
                  Create Role
                </PrimaryButton>
              </div>
            </form>
          </div>

          <!-- Roles List -->
          <div>
            <h3 class="text-lg font-medium mb-4">Existing Roles</h3>
            <div v-if="roles.length === 0" class="p-4 bg-gray-50 rounded text-center">
              No roles found. Create your first role using the form above.
            </div>
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Role Name
                    </th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Permissions
                    </th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="role in roles" :key="role.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-gray-900">
                        <div class="flex flex-wrap gap-1">
                          <span v-for="permission in role.permissions" :key="permission.id"
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ permission.name }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button @click="editRole(role)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        Edit
                      </button>
                      <button v-if="role.name !== 'admin'" @click="confirmDeleteRole(role)"
                        class="text-red-600 hover:text-red-900">
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Role Modal -->
    <Modal :show="editingRole" @close="closeEditModal">
      <div class="p-6 relative overflow-visible">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Role: {{ editForm.name }}</h2>

        <form @submit.prevent="updateRole">
          <div>
            <InputLabel for="edit-name" value="Role Name" />
            <TextInput id="edit-name" type="text" class="mt-1 block w-full" v-model="editForm.name" required />
            <InputError :message="editForm.errors.name" class="mt-2" />
          </div>

          <div class="mt-4">
            <InputLabel value="Permissions" />
            <SelectInput v-model="editForm.permissions" :options="permissions" multiple class="mt-1 block w-full"
              placeholder="Select permissions" />
            <InputError :message="editForm.errors.permissions" class="mt-2" />
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton @click="closeEditModal" class="mr-3">
              Cancel
            </SecondaryButton>
            <PrimaryButton :disabled="editForm.processing">
              Update Role
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Swal from 'sweetalert2';

const props = defineProps({
  roles: Array,
  permissions: Array
});

// Form for creating a new role
const form = useForm({
  name: '',
  permissions: []
});

// Form for editing a role
const editForm = useForm({
  id: null,
  name: '',
  permissions: []
});

const editingRole = ref(false);
const currentRole = ref(null);

// Create a new role
const createRole = () => {
  form.post(route('roles.store'), {
    onSuccess: () => {
      form.reset();
    }
  });
};

// Open the edit modal for a role
const editRole = (role) => {
  currentRole.value = role;
  editForm.id = role.id;
  editForm.name = role.name;
  editForm.permissions = role.permissions.map(p => p.id);
  editingRole.value = true;
};

// Close the edit modal
const closeEditModal = () => {
  editingRole.value = false;
  editForm.reset();
  currentRole.value = null;
};

// Update a role
const updateRole = () => {
  editForm.put(route('roles.update', editForm.id), {
    onSuccess: () => {
      closeEditModal();
    }
  });
};

// Confirm deletion of a role
const confirmDeleteRole = (role) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete the role "${role.name}". This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      deleteRole(role);
    }
  });
};

// Delete a role
const deleteRole = (role) => {
  useForm({}).delete(route('roles.destroy', role.id), {
    onSuccess: () => {
      Swal.fire(
        'Deleted!',
        `The role "${role.name}" has been deleted.`,
        'success'
      );
    }
  });
};
</script>
