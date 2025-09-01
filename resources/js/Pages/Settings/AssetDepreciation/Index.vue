<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Asset Depreciation Settings</h1>
            <p class="mt-2 text-sm text-gray-600">
              Configure depreciation methods, useful life, and category-specific overrides
            </p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="installDefaults"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Install Defaults
            </button>
            <button
              @click="resetToDefaults"
              class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Reset to Defaults
            </button>
            <Link
              :href="route('settings.asset-depreciation.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Add Setting
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Success/Error Messages -->
      <div v-if="$page.props.flash.success" class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ $page.props.flash.success }}</p>
          </div>
        </div>
      </div>

      <div v-if="$page.props.flash.error" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ $page.props.flash.error }}</p>
          </div>
        </div>
      </div>

      <!-- Settings by Category -->
      <div class="space-y-8">
        <div v-for="(categorySettings, category) in settings" :key="category" class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">{{ categories[category] }}</h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ getCategoryDescription(category) }}
            </p>
          </div>
          
          <div class="divide-y divide-gray-200">
            <div v-for="setting in categorySettings" :key="setting.id" class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-center space-x-3">
                    <h4 class="text-sm font-medium text-gray-900">{{ setting.key }}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ types[setting.type] }}
                    </span>
                    <span v-if="!setting.is_active" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      Disabled
                    </span>
                  </div>
                  <p v-if="setting.description" class="mt-1 text-sm text-gray-500">
                    {{ setting.description }}
                  </p>
                  <div class="mt-2">
                    <span class="text-sm text-gray-600">Value: </span>
                    <span class="text-sm font-medium text-gray-900">
                      <span v-if="setting.type === 'boolean'">
                        {{ setting.value ? 'Yes' : 'No' }}
                      </span>
                      <span v-else-if="setting.type === 'json'">
                        <pre class="text-xs bg-gray-50 p-2 rounded">{{ JSON.stringify(setting.value, null, 2) }}</pre>
                      </span>
                      <span v-else>
                        {{ setting.value }}
                      </span>
                    </span>
                  </div>
                  <div v-if="setting.metadata && Object.keys(setting.metadata).length > 0" class="mt-2">
                    <span class="text-xs text-gray-500">
                      Metadata: {{ JSON.stringify(setting.metadata) }}
                    </span>
                  </div>
                </div>
                
                <div class="flex items-center space-x-2">
                  <button
                    @click="toggleStatus(setting)"
                    :class="[
                      'inline-flex items-center px-3 py-1.5 border rounded-md text-xs font-medium',
                      setting.is_active
                        ? 'border-red-300 text-red-700 bg-white hover:bg-red-50'
                        : 'border-green-300 text-green-700 bg-white hover:bg-green-50'
                    ]"
                  >
                    {{ setting.is_active ? 'Disable' : 'Enable' }}
                  </button>
                  
                  <Link
                    :href="route('settings.asset-depreciation.edit', setting.id)"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Edit
                  </Link>
                  
                  <button
                    @click="deleteSetting(setting)"
                    class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="Object.keys(settings).length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No settings configured</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by installing default settings or creating a new setting.
        </p>
        <div class="mt-6">
          <button
            @click="installDefaults"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Install Defaults
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-lg font-medium text-gray-900">Delete Setting</h3>
            <p class="text-sm text-gray-500">
              Are you sure you want to delete "{{ settingToDelete?.key }}"? This action cannot be undone.
            </p>
          </div>
        </div>
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  settings: Object,
  categories: Object,
  types: Object,
})

const showDeleteModal = ref(false)
const settingToDelete = ref(null)

const getCategoryDescription = (category) => {
  const descriptions = {
    default: 'General settings applied to all new assets',
    category_override: 'Asset category-specific depreciation settings',
    method: 'Configuration for different depreciation calculation methods',
    system: 'System-level settings for depreciation calculations',
  }
  return descriptions[category] || ''
}

const installDefaults = () => {
  if (confirm('Are you sure you want to install default settings? This will add new settings but won\'t overwrite existing ones.')) {
    router.post(route('settings.asset-depreciation.install-defaults'))
  }
}

const resetToDefaults = () => {
  if (confirm('Are you sure you want to reset ALL settings to defaults? This will delete all current settings and cannot be undone.')) {
    router.post(route('settings.asset-depreciation.reset-to-defaults'))
  }
}

const deleteSetting = (setting) => {
  settingToDelete.value = setting
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (settingToDelete.value) {
    router.delete(route('settings.asset-depreciation.destroy', settingToDelete.value.id))
    showDeleteModal.value = false
    settingToDelete.value = null
  }
}

const toggleStatus = (setting) => {
  router.post(route('settings.asset-depreciation.toggle-status', setting.id))
}
</script>
