import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function usePermissions() {
    const { props } = usePage()
    
    // Get user permissions from the page props
    const userPermissions = computed(() => {
        return props.auth?.user?.permissions || []
    })
    
    // Get permission names as array
    const permissionNames = computed(() => {
        return userPermissions.value.map(p => p.name)
    })
    
    // Check if user has a specific permission
    const hasPermissionTo = (permission) => {
        if (!permission) return false
        return permissionNames.value.includes(permission)
    }
    
    // Check if user has any of the given permissions
    const hasAnyPermission = (permissions) => {
        if (!Array.isArray(permissions)) return false
        return permissions.some(permission => hasPermissionTo(permission))
    }
    
    // Check if user has all of the given permissions
    const hasAllPermissions = (permissions) => {
        if (!Array.isArray(permissions)) return false
        return permissions.every(permission => hasPermissionTo(permission))
    }
    
    // Check if user can access a specific module
    const canAccessModule = (module) => {
        return userPermissions.value.some(p => p.module === module)
    }
    
    // Check if user is view-only
    const isViewOnly = computed(() => {
        return hasPermissionTo('view-only-access')
    })
    
    // Check if user can perform actions (not view-only)
    const canPerformActions = computed(() => {
        return !isViewOnly.value
    })
    
    // Check if user is system manager
    const isSystemManager = computed(() => {
        return hasPermissionTo('manager-system')
    })
    
    // Check if user is admin
    const isAdmin = computed(() => {
        return props.auth?.user?.isAdmin?.() || false
    })
    
    // Get permissions grouped by module
    const permissionsByModule = computed(() => {
        const grouped = {}
        userPermissions.value.forEach(permission => {
            if (!grouped[permission.module]) {
                grouped[permission.module] = []
            }
            grouped[permission.module].push(permission)
        })
        return grouped
    })
    
    // Check if user has facility management permissions
    const canManageFacilities = computed(() => {
        return hasAnyPermission(['facility-view', 'facility-create', 'facility-edit', 'facility-delete', 'facility-import'])
    })
    
    // Check if user has product management permissions
    const canManageProducts = computed(() => {
        return hasAnyPermission(['product-view', 'product-create', 'product-edit', 'product-delete', 'product-import'])
    })
    
    // Check if user has inventory management permissions
    const canManageInventory = computed(() => {
        return hasAnyPermission(['inventory-view', 'inventory-adjust', 'inventory-transfer'])
    })
    
    // Check if user has user management permissions
    const canManageUsers = computed(() => {
        return hasAnyPermission(['user-view', 'user-create', 'user-edit', 'user-delete'])
    })
    
    // Check if user has warehouse management permissions
    const canManageWarehouses = computed(() => {
        return hasAnyPermission(['warehouse-view', 'warehouse-manage'])
    })
    
    // Check if user has reports access
    const canAccessReports = computed(() => {
        return hasAnyPermission(['reports-view', 'reports-export'])
    })
    
    // Check if user has system administration permissions
    const canManageSystem = computed(() => {
        return hasAnyPermission(['system-settings', 'permission-manage'])
    })
    
    return {
        // Basic permission checks
        hasPermissionTo,
        hasAnyPermission,
        hasAllPermissions,
        canAccessModule,
        
        // Computed properties
        userPermissions,
        permissionNames,
        permissionsByModule,
        isViewOnly,
        canPerformActions,
        isSystemManager,
        isAdmin,
        
        // Module-specific checks
        canManageFacilities,
        canManageProducts,
        canManageInventory,
        canManageUsers,
        canManageWarehouses,
        canAccessReports,
        canManageSystem,
    }
}
