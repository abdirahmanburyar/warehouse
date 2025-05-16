<template>
  <UserAuthTab>
    <div>

      <Head title="Role Management" />

     
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

// Initialize filters with values from props
const search = ref(props.filters?.search || '');
const sort_field = ref(props.filters?.sort_field || 'name');
const sort_direction = ref(props.filters?.sort_direction || 'asc');

// Watch search field and call debounced search
watch(search, debounce(() => {
  debouncedSearch();
}, 300));

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

    router.visit(route('roles.index', params), {
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

// Search function
function debouncedSearch() {
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
    router.visit(route('roles.index', params), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      only: ['roles', 'filters']
    });
  }
}

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
    router.visit(route('roles.index', params), {
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
