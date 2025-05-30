<template>
  <UserAuthTab>
    <div>
      <Head title="Role Management" />

      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Role Management</h2>
        <button
          @click="openCreateModal"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center transition"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Role
        </button>
      </div>

      <!-- Search and Filters -->
      <div class="mb-6">
        <div class="flex items-center space-x-4">
          <div class="flex-grow">
            <TextInput
              v-model="search"
              type="text"
              placeholder="Search roles..."
              class="w-full text-sm text-black"
              @input="performSearch"
            />
          </div>
        </div>
      </div>

      <!-- Roles Table -->
      <div class="bg-white shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 border border-black">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border-r border-black">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider border-r border-black">Permissions</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="roles.length === 0" class="text-center">
              <td colspan="3" class="px-6 py-4 text-sm text-gray-500">No roles found</td>
            </tr>
            <tr v-for="role in filteredRoles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 text-sm text-black border-r border-black">{{ role.name }}</td>
              <td class="px-6 py-4 text-sm text-black border-r border-black">
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-for="(permission, index) in getDisplayPermissions(role)" 
                    :key="index"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ permission }}
                  </span>
                  <span v-if="role.permissions && role.permissions.length > 5" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                    +{{ role.permissions.length - 5 }} more
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-black whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <button 
                    @click="openEditModal(role)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Edit Role"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button 
                    @click="confirmDelete(role)"
                    class="text-red-600 hover:text-red-900"
                    title="Delete Role"
                    :disabled="role.name === 'administrator'"
                    :class="{'opacity-50 cursor-not-allowed': role.name === 'administrator'}"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Create/Edit Role Modal -->
      <Modal :show="showModal" @close="closeModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ isEditing ? 'Edit Role' : 'Create New Role' }}
          </h3>
          <form @submit.prevent="submitForm">
            <div class="mb-4">
              <InputLabel for="name" value="Role Name" />
              <TextInput
                id="name"
                type="text"
                v-model="form.name"
                class="mt-1 block w-full"
                required
                :disabled="isEditing && form.original_name === 'administrator'"
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>
            
            <!-- Permissions Selection -->
            <div class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <InputLabel value="Permissions" />
                <div class="flex items-center space-x-2">
                  <button 
                    type="button" 
                    @click="selectAllPermissions"
                    class="text-sm text-blue-600 hover:text-blue-800"
                    :disabled="processing || (isEditing && form.original_name === 'administrator')"
                    :class="{'opacity-50 cursor-not-allowed': processing || (isEditing && form.original_name === 'administrator')}"
                  >
                    Select All
                  </button>
                  <span class="text-gray-500">|</span>
                  <button 
                    type="button" 
                    @click="deselectAllPermissions"
                    class="text-sm text-blue-600 hover:text-blue-800"
                    :disabled="processing || (isEditing && form.original_name === 'administrator')"
                    :class="{'opacity-50 cursor-not-allowed': processing || (isEditing && form.original_name === 'administrator')}"
                  >
                    Deselect All
                  </button>
                </div>
              </div>
              
              <!-- Permission Groups -->
              <div class="border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto">
                <div v-for="(permissions, module) in groupedPermissions" :key="module" class="mb-4">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-gray-700 capitalize">{{ formatModuleName(module) }}</h4>
                    <div class="flex items-center space-x-2">
                      <button 
                        type="button" 
                        @click="selectModulePermissions(module)"
                        class="text-xs text-blue-600 hover:text-blue-800"
                        :disabled="processing || (isEditing && form.original_name === 'administrator')"
                        :class="{'opacity-50 cursor-not-allowed': processing || (isEditing && form.original_name === 'administrator')}"
                      >
                        Select All
                      </button>
                      <span class="text-gray-500">|</span>
                      <button 
                        type="button" 
                        @click="deselectModulePermissions(module)"
                        class="text-xs text-blue-600 hover:text-blue-800"
                        :disabled="processing || (isEditing && form.original_name === 'administrator')"
                        :class="{'opacity-50 cursor-not-allowed': processing || (isEditing && form.original_name === 'administrator')}"
                      >
                        Deselect All
                      </button>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    <div v-for="permission in permissions" :key="permission.id" class="flex items-start">
                      <div class="flex items-center h-5">
                        <input
                          :id="`permission-${permission.id}`"
                          type="checkbox"
                          :value="permission.id"
                          v-model="form.permissions"
                          class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                          :disabled="isEditing && form.original_name === 'administrator'"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                          {{ formatPermissionName(permission.name) }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end mt-6 gap-4">
              <SecondaryButton 
                type="button" 
                @click="closeModal" 
                :disabled="processing"
              >
                Cancel
              </SecondaryButton>
              <PrimaryButton :disabled="processing || (isEditing && form.original_name === 'administrator')">
                {{ processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>
    </div>
  </UserAuthTab>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Swal from 'sweetalert2';
import debounce from 'lodash/debounce';
import { useToast } from 'vue-toastification';
import UserAuthTab from '@/Layouts/UserAuthTab.vue';

const props = defineProps({
  roles: Array,
  permissions: Array,
  filters: Object
});

// Initialize toast
const toast = useToast();

// UI state
const search = ref(props.filters?.search || '');
const showModal = ref(false);
const isEditing = ref(false);
const processing = ref(false);

// Form state
const form = useForm({
  id: null,
  name: '',
  original_name: '',
  permissions: [],
});

// Filter roles based on search
const filteredRoles = computed(() => {
  if (!search.value) return props.roles;
  
  return props.roles.filter(role => 
    role.name.toLowerCase().includes(search.value.toLowerCase())
  );
});

// Group permissions by module
const groupedPermissions = computed(() => {
  const groups = {};
  
  props.permissions.forEach(permission => {
    const parts = permission.name.split('.');
    const module = parts[0];
    
    if (!groups[module]) {
      groups[module] = [];
    }
    
    groups[module].push(permission);
  });
  
  // Sort modules alphabetically
  return Object.keys(groups).sort().reduce((sorted, key) => {
    sorted[key] = groups[key];
    return sorted;
  }, {});
});

// Format module name for display
function formatModuleName(module) {
  return module.replace(/-/g, ' ');
}

// Format permission name for display
function formatPermissionName(name) {
  const parts = name.split('.');
  if (parts.length > 1) {
    return parts[1].replace(/-/g, ' ');
  }
  return name;
}

// Get display permissions (limited to 5 for UI)
function getDisplayPermissions(role) {
  if (!role.permissions || role.permissions.length === 0) return [];
  
  return role.permissions.slice(0, 5).map(permission => {
    const parts = permission.name.split('.');
    return parts.length > 1 ? `${parts[0]}.${parts[1]}` : permission.name;
  });
}

// Open create modal
function openCreateModal() {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  showModal.value = true;
}

// Open edit modal
function openEditModal(role) {
  isEditing.value = true;
  form.id = role.id;
  form.name = role.name;
  form.original_name = role.name;
  form.permissions = role.permissions.map(permission => permission.id);
  form.clearErrors();
  showModal.value = true;
}

// Close modal
function closeModal() {
  showModal.value = false;
}

// Submit form
function submitForm() {
  processing.value = true;
  
  // Use the same route for both create and edit, just pass the ID for editing
  const url = route('roles.store');
  const successMessage = isEditing.value ? 'Role updated successfully' : 'Role created successfully';
  const errorMessage = isEditing.value ? 'Failed to update role' : 'Failed to create role';
  
  form.post(url, {
    onSuccess: () => {
      toast.success(successMessage);
      closeModal();
      processing.value = false;
    },
    onError: () => {
      toast.error(errorMessage);
      processing.value = false;
    }
  });
}

// Confirm delete
function confirmDelete(role) {
  if (role.name === 'administrator') {
    toast.error('Cannot delete the administrator role');
    return;
  }
  
  Swal.fire({
    title: 'Are you sure?',
    text: `Delete role "${role.name}"? This may affect users assigned to this role.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    confirmButtonText: 'Yes, delete it',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('roles.destroy', role.id), {
        onSuccess: () => {
          toast.success('Role deleted successfully');
        },
        onError: () => {
          toast.error('Failed to delete role');
        }
      });
    }
  });
}

// Select all permissions
function selectAllPermissions() {
  form.permissions = props.permissions.map(permission => permission.id);
}

// Deselect all permissions
function deselectAllPermissions() {
  form.permissions = [];
}

// Select all permissions for a module
function selectModulePermissions(module) {
  const modulePermissions = groupedPermissions.value[module].map(permission => permission.id);
  form.permissions = [...new Set([...form.permissions, ...modulePermissions])];
}

// Deselect all permissions for a module
function deselectModulePermissions(module) {
  const modulePermissionIds = groupedPermissions.value[module].map(permission => permission.id);
  form.permissions = form.permissions.filter(id => !modulePermissionIds.includes(id));
}

// Perform search with debouncing
const performSearch = debounce(() => {
  router.get(route('settings.roles.index'), { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, 300);

// Initialize filters with values from props
const sort_field = ref(props.filters?.sort_field || 'name');
const sort_direction = ref(props.filters?.sort_direction || 'asc');

// Watch search field and call debounced search
watch(search, debounce(() => {
  debouncedSearch();
}, 300));

// This section has been removed to avoid duplicate declarations
// The form is already declared above

const editingRole = ref(false);
const currentRole = ref(null);
const isSubmitted = ref(false);

// When a form is submitted successfully
const handleFormSuccess = (message) => {
  toast.success(message);

  // Determine current page context
  const isInSettingsPage = window.location.href.includes('settings');

  // Refresh data based on context
  if (isInSettingsPage) {
    // When on settings page, refresh the roles list
    const params = {};
    if (search.value) params.search = search.value;
    if (sort_field.value) params.sort_field = sort_field.value;
    if (sort_direction.value) params.sort_direction = sort_direction.value;

    router.visit(route('settings.index', { tab: 'roles', ...params }), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['roles', 'filters']
    });
  } else {
    // When on roles page, refresh data
    const params = {};
    if (search.value) params.search = search.value;
    if (sort_field.value) params.sort_field = sort_field.value;
    if (sort_direction.value) params.sort_direction = sort_direction.value;

    router.visit(route('settings.roles.index', params), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['roles', 'filters']
    });
  }

  // Reset processing and isSubmitted flags
  isSubmitted.value = false;
}

// Create a new role
const createRole = () => {
  isSubmitted.value = true;

  // Determine if we're in settings page
  const isInSettingsPage = window.location.href.includes('settings');

  if (isInSettingsPage) {
    form.transform(data => ({
      ...data,
      _headers: { 'X-From-Settings': 'true' }
    }));
  }

  form.post(route('roles.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      handleFormSuccess('Role created successfully');
    },
    onError: () => {
      isSubmitted.value = false;
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
  isSubmitted.value = true;

  // Determine if we're in settings page
  const isInSettingsPage = window.location.href.includes('settings');

  if (isInSettingsPage) {
    editForm.transform(data => ({
      ...data,
      _headers: { 'X-From-Settings': 'true' }
    }));
  }

  editForm.put(route('roles.update', editForm.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal();
      handleFormSuccess('Role updated successfully');
    },
    onError: () => {
      isSubmitted.value = false;
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
      deleteRole();
    }
  });
};

// Delete a role
const deleteRole = () => {
  if (!currentRole.value) return;

  isSubmitted.value = true;

  // Determine if we're in settings page
  const isInSettingsPage = window.location.href.includes('settings');

  const deleteForm = useForm({
    _method: 'DELETE',
    _headers: isInSettingsPage ? { 'X-From-Settings': 'true' } : {}
  });

  deleteForm.post(route('roles.destroy', currentRole.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      handleFormSuccess('Role deleted successfully');
    },
    onError: (error) => {
      isSubmitted.value = false;
      Swal.fire({
        title: 'Error',
        text: error.message || 'Failed to delete role',
        icon: 'error'
      });
    }
  });
};


// Sort function
function sort(field) {
  sort_field.value = field;
  sort_direction.value = sort_direction.value === 'asc' ? 'desc' : 'asc';

  const currentUrl = window.location.pathname;
  const params = {};

  // Only include non-empty values
  if (search.value) params.search = search.value;
  if (sort_field.value) params.sort_field = sort_field.value;
  if (sort_direction.value) params.sort_direction = sort_direction.value;

  if (currentUrl.includes('settings')) {
    router.visit(route('settings.index', { tab: 'roles', ...params }), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['roles', 'filters']
    });
  } else {
    router.visit(route('settings.roles.index', params), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['roles', 'filters']
    });
  }
}

// Format date
function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}
</script>
