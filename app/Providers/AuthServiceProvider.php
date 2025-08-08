<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Facility;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Transfer;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Asset::class => \App\Policies\AssetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Super admin bypass - admins can do everything
        Gate::before(function (User $user) {
            if ($user->hasRole('admin')) {
                return true;
            }
        });
        
        // Warehouse-specific permissions
        Gate::define('manage-warehouse', function (User $user, Warehouse $warehouse) {
            // User can manage warehouse if they are assigned to it or have global warehouse management permission
            return $user->warehouse_id === $warehouse->id || $user->can('warehouse.edit');
        });
        
        Gate::define('view-warehouse', function (User $user, Warehouse $warehouse) {
            // Users can view their assigned warehouse or if they have global view permission
            return $user->warehouse_id === $warehouse->id || $user->can('warehouse.view');
        });
        
        // Facility-specific permissions
        Gate::define('manage-facility', function (User $user, Facility $facility) {
            // User can manage facility if they are assigned to it or have global facility management permission
            return $user->facility_id === $facility->id || $user->can('facility.edit');
        });
        
        Gate::define('view-facility', function (User $user, Facility $facility) {
            // Users can view their assigned facility or if they have global view permission
            return $user->facility_id === $facility->id || $user->can('facility.view');
        });
        
        // Inventory-specific permissions
        Gate::define('manage-inventory', function (User $user, Inventory $inventory) {
            // User can manage inventory in their warehouse/facility or if they have global inventory management permission
            return 
                ($user->warehouse_id === $inventory->warehouse_id) || 
                ($user->facility_id === $inventory->facility_id) || 
                $user->can('inventory.edit');
        });
        
        // Transfer-specific permissions
        Gate::define('manage-transfer', function (User $user, Transfer $transfer) {
            // User can manage transfers if they're at the source or destination warehouse/facility
            // or if they have global transfer management permission
            $isSourceUser = $user->warehouse_id === $transfer->source_warehouse_id || 
                           $user->facility_id === $transfer->source_facility_id;
            $isDestinationUser = $user->warehouse_id === $transfer->destination_warehouse_id || 
                               $user->facility_id === $transfer->destination_facility_id;
            
            return $isSourceUser || $isDestinationUser || $user->can('transfer.edit');
        });
        
        // Order-specific permissions
        Gate::define('manage-order', function (User $user, Order $order) {
            // User can manage orders if they're at the source or destination warehouse/facility
            // or if they have global order management permission
            $isRelatedToUser = $user->warehouse_id === $order->warehouse_id || 
                              $user->facility_id === $order->facility_id;
            
            return $isRelatedToUser || $user->can('order.edit');
        });
        
        // Purchase Order specific permissions
        Gate::define('approve-purchase-order', function (User $user, PurchaseOrder $purchaseOrder) {
            // Only users with specific approve permission can approve purchase orders
            return $user->can('purchase-order.approve');
        });
        
        Gate::define('review-purchase-order', function (User $user, PurchaseOrder $purchaseOrder) {
            // Users with review permission or those assigned to the warehouse can review
            return $user->can('purchase-order.review') || 
                   $user->warehouse_id === $purchaseOrder->warehouse_id;
        });
    }
}
