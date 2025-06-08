<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DosageController;
use App\Http\Controllers\EligibleItemController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReverbTestController;
use App\Http\Controllers\ExpiredController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LiquidateDisposalController;
use App\Http\Controllers\ConsumptionUploadController;
use App\Http\Controllers\ProductUploadController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use Spatie\Permission\Middleware\PermissionMiddleware;

// Welcome route - accessible without authentication


Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome');
})->name('welcome');

// Broadcast routes
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Two-Factor Authentication Routes - These must be accessible without 2FA
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});


// All routes that require authentication and 2FA
Route::middleware(['auth', \App\Http\Middleware\TwoFactorAuth::class])->group(function () {
    
    // Default route - redirect to login or dashboard
    Route::controller(DashboardController::class)
        ->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
        });
    // Unauthorized access page
    Route::get('/unauthorized', function () {
        return Inertia::render('Unauthorized');
    })->name('unauthorized');
    
    Route::get('/test-import', function() {
        $import = new IssueQuantityItemsImport('2025-06', 1); // Use a real user ID
        logger()->info('TEST ROUTE CALLED');
        Excel::import($import, storage_path('app/test.xlsx'));
        return 'done';
    });

    // Test route for permission events
    Route::get('/test-permission-event', [\App\Http\Controllers\TestController::class, 'testPermissionEvent'])
        ->name('test.permission-event');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Permissions API endpoint
    Route::get('/api/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('api.permissions.index');
    
    // User Management Routes
    Route::middleware(PermissionMiddleware::class . ':user.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('settings.users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware(PermissionMiddleware::class . ':user.create')
            ->name('settings.users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware(PermissionMiddleware::class . ':user.create')
            ->name('settings.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware(PermissionMiddleware::class . ':user.edit')
            ->name('settings.users.edit');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->middleware(PermissionMiddleware::class . ':user.delete')
            ->name('users.destroy');
            
        // User status routes
        Route::post('/users/toggle-status', [UserController::class, 'toggleStatus'])
            ->middleware(PermissionMiddleware::class . ':user.edit')
            ->name('users.toggle-status');
            
        Route::post('/users/bulk-status', [UserController::class, 'bulkToggleStatus'])
            ->middleware(PermissionMiddleware::class . ':user.edit')
            ->name('users.bulk-status');
    });

    // Role Management Routes
    Route::middleware(PermissionMiddleware::class . ':role.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('settings.roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware(PermissionMiddleware::class . ':role.create')
            ->name('settings.roles.create');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware(PermissionMiddleware::class . ':role.create')
            ->name('roles.store');
            

        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware(PermissionMiddleware::class . ':role.edit')
            ->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->middleware(PermissionMiddleware::class . ':role.edit')
            ->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware(PermissionMiddleware::class . ':role.delete')
            ->name('roles.destroy');
    });

    Route::post('/users/{user}/roles', [RoleController::class, 'assignRoles'])
        ->middleware(PermissionMiddleware::class . ':user.edit')
        ->name('users.roles.assign');

    // Category Management Routes
    Route::middleware([\App\Http\Middleware\TwoFactorAuth::class, PermissionMiddleware::class . ':category.view'])
        ->group(function () {
            Route::get('/categories', [CategoryController::class, 'index'])->name('products.categories.index');
            Route::post('/categories/store', [CategoryController::class, 'store'])->name('products.categories.store');
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('products.categories.create');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
                ->name('products.categories.edit');
            Route::get('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
                // ->middleware(PermissionMiddleware::class . ':category.edit')
                ->name('products.categories.toggle-status');

            Route::get('/categories/{category}/destroy', [CategoryController::class, 'destroy'])
                ->middleware(PermissionMiddleware::class . ':category.delete')
                ->name('categories.destroy');
        });

    // Warehouse Management Routes
    Route::controller(WarehouseController::class)
        ->prefix('/inventories/warehouses')
        ->group(function () {
            Route::get('/', 'index')->name('inventories.warehouses.index');
            Route::post('/store', 'store')->name('inventories.warehouses.store');
            Route::get('/create', 'create')->name('inventories.warehouses.create');
            Route::delete('/{id}/delete', 'destroy')->name('inventories.warehouses.destroy');
            Route::get('/{id}/edit', 'edit')->name('inventories.warehouses.edit');

        });

     // Warehouse Management Routes
     Route::controller(LocationController::class)
     ->prefix('/inventories/locations')
     ->group(function () {
         Route::get('/', 'index')->name('inventories.location.index');
         Route::post('/store', 'store')->name('inventories.location.store');
         Route::delete('/{id}/delete', 'destroy')->name('inventories.location.destroy');
         Route::get('/{id}/edit', 'edit')->name('inventories.location.edit');
         Route::get('/create', 'create')->name('inventories.location.create');
     });

    // Dosage Management Routes
    Route::prefix('product/dosages')->group(function () {
        Route::get('/', [DosageController::class, 'index'])->middleware(PermissionMiddleware::class . ':dosage.view')->name('products.dosages.index');
        Route::get('/create', [DosageController::class, 'create'])->middleware(PermissionMiddleware::class . ':dosage.create')->name('products.dosages.create');
        Route::post('/store', [DosageController::class, 'store'])->middleware(PermissionMiddleware::class . ':dosage.create')->name('products.dosages.store');
        Route::get('/{dosage}/edit', [DosageController::class, 'edit'])->middleware(PermissionMiddleware::class . ':dosage.edit')->name('products.dosages.edit');
        Route::get('/{dosage}/toggle-status', [DosageController::class, 'toggleStatus'])->name('products.dosages.toggle-status');
        Route::delete('/{dosage}', [DosageController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':dosage.delete')->name('products.dosages.destroy');
    });

    // Product Management Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->middleware(PermissionMiddleware::class . ':product.view')->name('products.index');
        Route::get('/create', [ProductController::class, 'create'])->middleware(PermissionMiddleware::class . ':product.create')->name('products.create');
        Route::post('/', [ProductController::class, 'store'])->middleware(PermissionMiddleware::class . ':product.create')->name('products.store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware(PermissionMiddleware::class . ':product.edit')->name('products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware(PermissionMiddleware::class . ':product.edit')->name('products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':product.delete')->name('products.destroy');
        Route::post('/import-excel', [ProductUploadController::class, 'upload'])->name('products.import-excel');
        Route::get('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->middleware(PermissionMiddleware::class . ':product.edit')->name('products.toggle-status');
    });

    // Eligible Items Management Routes
    Route::prefix('eligible-items')->group(function () {
        Route::get('/', [EligibleItemController::class, 'index'])->middleware(PermissionMiddleware::class . ':eligible-item.view')->name('products.eligible.index');
        Route::get('/create', [EligibleItemController::class, 'create'])->middleware(PermissionMiddleware::class . ':eligible-item.create')->name('products.eligible.create');
        Route::post('/store', [EligibleItemController::class, 'store'])->middleware(PermissionMiddleware::class . ':eligible-item.create')->name('products.eligible.store');
        Route::get('/{eligibleItem}/edit', [EligibleItemController::class, 'edit'])->middleware(PermissionMiddleware::class . ':eligible-item.edit')->name('products.eligible.edit');
        Route::post('/update', [EligibleItemController::class, 'update'])->middleware(PermissionMiddleware::class . ':eligible-item.edit')->name('products.eligible.update');
        Route::get('/{eligibleItem}', [EligibleItemController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':eligible-item.delete')->name('products.eligible.destroy');
        Route::post('/import', [EligibleItemController::class, 'import'])->middleware(PermissionMiddleware::class . ':eligible-item.create')->name('products.eligible.import');
    });

    // Supply Management Routes
    Route::prefix('supplies')->group(function () {
        Route::get('/', [SupplyController::class, 'index'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.index');
        Route::get('/create', [SupplyController::class, 'create'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.create');
        Route::get('/show', [SupplyController::class, 'show'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.show');
        Route::get('/{id}/showPO', [SupplyController::class, 'showPO'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.po-show');
        Route::get('/packing-list/{id}/get-po', [SupplyController::class, 'getPO'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.get-purchaseOrder');
        Route::get('/packing-list/{id}/edit', [SupplyController::class, 'editPK'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('supplies.packing-list.edit');
        Route::get('/packing-list/show', [SupplyController::class, 'showPK'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.packing-list.showPK');
        Route::get('/packing-list', [SupplyController::class, 'newPackingList'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.packing-list');
        Route::get('/back-orders', [SupplyController::class, 'showBackOrder'])->middleware(PermissionMiddleware::class . ':supply.view')->name('supplies.showBackOrder');
        Route::get('/purchase_orders', [SupplyController::class, 'newPO'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.purchase_order');
        Route::post('/purchase_orders/store', [SupplyController::class, 'storePO'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.storePO');
        Route::get('/purchase_orders/{id}/edit', [SupplyController::class, 'editPO'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('supplies.editPO');
        Route::post('/packing-list/store', [SupplyController::class, 'storePK'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.storePK');
        Route::delete('/purchase_orders/documents/{document}', [SupplyController::class, 'deleteDocument'])->name('supplies.deleteDocument');


        // edit po - actions
        Route::post('/purchase_orders/{id}/review', [SupplyController::class, 'reviewPO'])->middleware(PermissionMiddleware::class . ':supply.review')->name('supplies.reviewPO');
        Route::post('/purchase_orders/{id}/approve', [SupplyController::class, 'approvePO'])->middleware(PermissionMiddleware::class . ':supply.approve')->name('supplies.approvePO');
        Route::post('/purchase_orders/{id}/reject', [SupplyController::class, 'rejectPO'])->middleware(PermissionMiddleware::class . ':supply.reject')->name('supplies.rejectPO');

        Route::post('/packing-list/review', [SupplyController::class, 'reviewPK'])->middleware(PermissionMiddleware::class . ':supply.review')->name('supplies.reviewPK');
        Route::post('/packing-list/approve', [SupplyController::class, 'approvePK'])->middleware(PermissionMiddleware::class . ':supply.approve')->name('supplies.approvePK');
        Route::post('/packing-list/reject', [SupplyController::class, 'rejectPK'])->middleware(PermissionMiddleware::class . ':supply.reject')->name('supplies.rejectPK');

        Route::post('/back-order/dispose', [SupplyController::class, 'dispose'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('back-order.dispose');
        Route::post('/back-order/receive', [SupplyController::class, 'receive'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('back-order.receive');
        Route::post('/store-location', [SupplyController::class, 'storeLocation'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('supplies.store-location');
               
    
        Route::post('/store', [SupplyController::class, 'store'])->name('supplies.store');
        Route::get('/{supply}/edit', [SupplyController::class, 'edit'])->name('supplies.edit');
        Route::put('/{supply}', [SupplyController::class, 'update'])->name('supplies.update');
        Route::delete('/{supply}', [SupplyController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':supply.delete')->name('supplies.destroy');

        // supplies.packing-list.update
        Route::post('/packing-list/update', [SupplyController::class, 'updatePK'])->name('supplies.packing-list.update');

    });

    Route::controller(LiquidateDisposalController::class)
        ->name('liquidate-disposal.')
        ->group(function(){
            Route::get('/disposals', 'disposals')->name('disposals');
            Route::get('/liquidates', 'liquidates')->name('liquidates');
            Route::get('/liquidates/{id}/review', 'reviewLiquidate')->name('liquidates.review');
            Route::get('/liquidates/{id}/approve', 'approveLiquidate')->name('liquidates.approve');
            Route::get('/liquidates/{id}/reject', 'rejectLiquidate')->name('liquidates.reject');

            Route::get('/disposals/{id}/review', 'reviewDisposal')->name('disposals.review');
            Route::get('/disposals/{id}/approve', 'approveDisposal')->name('disposals.approve');
            Route::get('/disposals/{id}/reject', 'rejectDisposal')->name('disposals.reject');
        });

    // Supplier Management Routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->middleware(PermissionMiddleware::class . ':supplier.view')->name('suppliers.index');
        // Route::get('/create', [SupplierController::class, 'create'])->middleware(PermissionMiddleware::class . ':supplier.create')->name('supplies.suppliers.create');
        Route::get('/{id}/edit', [SupplierController::class, 'edit'])->middleware(PermissionMiddleware::class . ':supplier.edit')->name('supplies.suppliers.edit');
        Route::post('/', [SupplierController::class, 'store'])->middleware(PermissionMiddleware::class . ':supplier.create')->name('supplies.suppliers.store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->middleware(PermissionMiddleware::class . ':supplier.edit')->name('suppliers.edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->middleware(PermissionMiddleware::class . ':supplier.edit')->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':supplier.delete')->name('suppliers.destroy');
    });

    // Expired Management Routes
    Route::prefix('expired')->group(function () {
        Route::get('/', [ExpiredController::class, 'index'])->name('expired.index');
        Route::get('/create', [ExpiredController::class, 'create'])->name('expired.create');
        Route::post('/', [ExpiredController::class, 'store'])->name('expired.store');
        Route::get('/{expired}/edit', [ExpiredController::class, 'edit'])->name('expired.edit');
        Route::put('/{expired}', [ExpiredController::class, 'update'])->name('expired.update');
        Route::delete('/{expired}', [ExpiredController::class, 'destroy'])->name('expired.destroy');
        Route::get('/{transfer}/transfer', [ExpiredController::class, 'transfer'])->name('expired.transfer');
        Route::post('/dispose', [ExpiredController::class, 'dispose'])->name('expired.dispose');
    });

    // Settings Management Routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/create', [SettingsController::class, 'create'])->middleware(PermissionMiddleware::class . ':settings.create')->name('settings.create');
        Route::post('/', [SettingsController::class, 'store'])->middleware(PermissionMiddleware::class . ':settings.create')->name('settings.store');
        Route::get('/{settings}/edit', [SettingsController::class, 'edit'])->middleware(PermissionMiddleware::class . ':settings.edit')->name('settings.edit');
        Route::put('/{settings}', [SettingsController::class, 'update'])->middleware(PermissionMiddleware::class . ':settings.edit')->name('settings.update');
        Route::delete('/{settings}', [SettingsController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':settings.delete')->name('settings.destroy');
    });

    // Order Management Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}/show', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/change-status', [OrderController::class, 'changeItemStatus'])->name('orders.change-status');
        Route::post('/reject', [OrderController::class, 'rejectOrder']);

        Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');


        // 'order.update-quantity
        Route::post('/update-quantity', [OrderController::class, 'updateQuantity'])->name('orders.update-quantity');
    });

    // Transfer Management Routes
    // ->middleware(PermissionMiddleware::class . ':transfer.view')
    Route::prefix('transfers')->group(function () {
        Route::get('/', [TransferController::class, 'index'])->name('transfers.index');
        Route::get('/{id}/show', [TransferController::class, 'show'])->name('transfers.show');
        Route::get('/create', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('/store', [TransferController::class, 'store'])->name('transfers.store');
        Route::get('/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit');
        Route::put('/{transfer}', [TransferController::class, 'update'])->name('transfers.update');
        Route::delete('/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy');
                
        // Route to get available inventories for transfer
        Route::get('/get-inventories', [TransferController::class, 'getInventories'])->name('transfers.getInventories');
               
         
        // Back order functionality
        Route::post('/backorder', [TransferController::class, 'backorder'])->name('transfers.backorder');
        Route::post('/remove-back-order', [TransferController::class, 'removeBackOrder'])->name('transfers.remove-back-order');
        
        // Item status change
        Route::post('/change-item-status', [TransferController::class, 'changeItemStatus'])->name('transfers.changeItemStatus');
        
        // receive transfer
        Route::post('/receive', [TransferController::class, 'receiveTransfer'])->name('transfers.receiveTransfer');
        
        // receive back order
        Route::post('/receive-back-order', [TransferController::class, 'receiveBackOrder'])->name('transfers.receiveBackOrder');
        
        // delete transfer item
        Route::get('/items/{id}', [TransferController::class, 'destroyItem'])->name('transfers.items.destroy');

        // update transfer item quantity
        Route::post('/update-item', [TransferController::class, 'updateItem'])->name('transfers.update-item');

        // transfer back order
        Route::get('/back-order', [TransferController::class, 'transferBackOrder'])->name('transfers.back-order');

        // transfer liquidate
        Route::post('/liquidate', [TransferController::class, 'transferLiquidate'])->name('transfers.liquidate');

        // transfer dispose
        Route::post('/dispose', [TransferController::class, 'transferDispose'])->name('transfers.dispose');
    });


    // Purchase Order Management Routes
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->middleware(PermissionMiddleware::class . ':purchase-order.view')->name('purchase-orders.index');
        Route::get('/create', [PurchaseOrderController::class, 'create'])->middleware(PermissionMiddleware::class . ':purchase-order.create')->name('purchase-orders.create');
        Route::post('/', [PurchaseOrderController::class, 'store'])->middleware(PermissionMiddleware::class . ':purchase-order.create')->name('purchase-orders.store');
        Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->middleware(PermissionMiddleware::class . ':purchase-order.edit')->name('purchase-orders.edit');
        Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->middleware(PermissionMiddleware::class . ':purchase-order.edit')->name('purchase-orders.update');
        Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':purchase-order.delete')->name('purchase-orders.destroy');
    });

    // Dispatch Management Routes
    Route::prefix('dispatches')->group(function () {
        Route::get('/', [DispatchController::class, 'index'])->middleware(PermissionMiddleware::class . ':dispatch.view')->name('dispatches.index');
        Route::get('/create', [DispatchController::class, 'create'])->middleware(PermissionMiddleware::class . ':dispatch.create')->name('dispatches.create');
        Route::post('/', [DispatchController::class, 'store'])->middleware(PermissionMiddleware::class . ':dispatch.create')->name('dispatches.store');
        Route::get('/{dispatch}/edit', [DispatchController::class, 'edit'])->middleware(PermissionMiddleware::class . ':dispatch.edit')->name('dispatches.edit');
        Route::put('/{dispatch}', [DispatchController::class, 'update'])->middleware(PermissionMiddleware::class . ':dispatch.edit')->name('dispatches.update');
        Route::delete('/{dispatch}', [DispatchController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':dispatch.delete')->name('dispatches.destroy');
    });

    // Facility Management Routes
    Route::prefix('facilities')->group(function () {
        Route::get('/', [FacilityController::class, 'index'])->name('facilities.index');
        Route::get('/{id}/show', [FacilityController::class, 'show'])->name('facilities.show');
        Route::get('/create', [FacilityController::class, 'create'])->name('facilities.create');
        Route::post('/import', [FacilityController::class, 'import'])->name('facilities.import');
        Route::post('/store', [FacilityController::class, 'store'])->name('facilities.store');
        Route::get('/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
        Route::delete('/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');
        Route::get('/{facility}/toggle-status', [FacilityController::class, 'toggleStatus'])->name('facilities.toggle-status');

        // tabs
        Route::get('/{facility}/inventory', [FacilityController::class, 'inventory'])->name('facilities.inventory');
        Route::get('/{facility}/dispence', [FacilityController::class, 'dispence'])->name('facilities.dispence');
        Route::get('/{facility}/expiry', [FacilityController::class, 'expiry'])->name('facilities.expiry');
    });

    // District Management Routes
    Route::prefix('districts')->group(function () {
        Route::get('/', [DistrictController::class, 'index'])->middleware(PermissionMiddleware::class . ':district.view')->name('districts.index');
        Route::get('/create', [DistrictController::class, 'create'])->middleware(PermissionMiddleware::class . ':district.create')->name('districts.create');
        Route::post('/', [DistrictController::class, 'store'])->middleware(PermissionMiddleware::class . ':district.create')->name('districts.store');
        Route::get('/{district}/edit', [DistrictController::class, 'edit'])->middleware(PermissionMiddleware::class . ':district.edit')->name('districts.edit');
        Route::put('/{district}', [DistrictController::class, 'update'])->middleware(PermissionMiddleware::class . ':district.edit')->name('districts.update');
        Route::delete('/{district}', [DistrictController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':district.delete')->name('districts.destroy');
    });

    // Asset Management Routes
    Route::controller(AssetController::class)
        ->prefix('assets-management')
        ->group(function () {
            Route::get('/', 'index')->name('assets.index');
            Route::get('/{id}/show', 'show')->name('assets.show');
            Route::get('/get-assets', 'getAssets')->name('assets.get');
            Route::get('/create', 'create')->name('assets.create');
            Route::post('/store', 'store')->name('assets.store');
            Route::get('/{asset}/edit', 'edit')->name('assets.edit');
            Route::put('/{asset}', 'update')->name('assets.update');
            Route::delete('/{asset}', 'destroy')->name('assets.destroy');
            Route::post('/store-source-fund', 'storeSourceFund')->name('assets.fundsource.store');
            Route::post('/store-region', 'storeRegion')->name('assets.regions.store');
            // Asset locations routes
            Route::get('/locations', 'locationIndex')->name('assets.locations.index');
            Route::get('/sub-locations', 'subLocationIndex')->name('assets.sub-locations.index');
            Route::get('/locations/{location}/sub-locations', 'getSubLocations')->name('assets.locations.sub-locations');
            Route::post('/locations/sub-locations', 'storeSubLocation')->name('assets.locations.sub-locations.store');
            Route::post('/categories/store', 'storeCategory')->name('assets.categories.store');
            Route::post('/locations/store', 'storeAssetLocation')->name('assets.locations.store');
            Route::post('/fund-sources/store', 'storeFundSource')->name('assets.fund-sources.store');

            // asset transfer submission
            Route::post('/assets/{asset}/transfer', 'transferAsset')->name('assets.transfer');

            // upload assets excel file 
            Route::post('/assets/import', 'import')->name('assets.import');

            // Asset Document Upload Route
            Route::post('/assets/documents/store', 'storeDocument')->name('assets.documents.store');

            // Asset Document Delete Route
            Route::get('/documents/{document}', 'deleteDocument')->name('assets.document.delete');
        });

    // Inventory Management Routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->middleware(PermissionMiddleware::class . ':inventory.view')->name('inventories.index');
        Route::get('/create', [InventoryController::class, 'create'])->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.create');
        Route::post('/', [InventoryController::class, 'store'])->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.store');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->middleware(PermissionMiddleware::class . ':inventory.edit')->name('inventories.edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->middleware(PermissionMiddleware::class . ':inventory.edit')->name('inventories.update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':inventory.delete')->name('inventories.destroy');


        Route::post('/get-locations', [InventoryController::class, 'getLocations'])->name('invetnories.getLocations');
        Route::post('/import', [InventoryController::class, 'import'])->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.import');
    });

    // API Routes
    Route::prefix('api')->group(function () {
        // Issue Quantity Reports Export Routes
        Route::get('/reports/issueQuantityReports/export', [ReportController::class, 'exportIssueQuantityReports'])->middleware(PermissionMiddleware::class . ':report.view');
        // Removed the individual report items export endpoint as it's now handled client-side
        Route::get('/reports/inventory-report/data', [ReportController::class, 'inventoryReportData'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.inventoryReport.data');
        // Warehouse API endpoint for dropdowns
        Route::get('/warehouses', [\App\Http\Controllers\WarehouseController::class, 'getWarehouses'])->name('api.warehouses');
    });

    // Report Routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.index');
        Route::get('/facilities/monthly-consumption', [ReportController::class, 'monthlyConsumption'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.monthlyConsumption');
        Route::get('/receivedQuantities', [ReportController::class, 'receivedQuantities'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.receivedQuantities');
        Route::get('/physicalCount', [ReportController::class, 'physicalCountReport'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.physicalCount');
        
        // Inventory Adjustment Routes
        Route::post('/createAdjustments', [ReportController::class, 'createAdjustments'])->middleware(PermissionMiddleware::class . ':report.edit')->name('reports.createAdjustments');
        Route::post('/adjustments/{id}/review', [ReportController::class, 'reviewAdjustment'])->middleware(PermissionMiddleware::class . ':report.review')->name('reports.reviewAdjustment');
        Route::post('/adjustments/{id}/approve', [ReportController::class, 'approveAdjustment'])->middleware(PermissionMiddleware::class . ':report.approve')->name('reports.approveAdjustment');
        Route::post('/adjustments/{id}/reject', [ReportController::class, 'rejectAdjustment'])->middleware(PermissionMiddleware::class . ':report.reject')->name('reports.rejectAdjustment');
        
        // Excel Upload Route
        Route::post('/upload-consumption', [ConsumptionUploadController::class, 'upload'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.upload-consumption');

        // Issue Quantity Routes
        Route::get('/issueQuantityReports', [ReportController::class, 'issueQuantityReports'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.issueQuantityReports');

        // Inventory Routes
        Route::get('/inventory-report', [ReportController::class, 'inventoryReport'])->middleware(PermissionMiddleware::class . ':report.view')->name('reports.inventoryReport');
        // Route::post('/inventory-report/generate', [ReportController::class, 'generateInventoryReport'])->middleware(PermissionMiddleware::class . ':report.generate')->name('reports.generateInventoryReport');

        // Physical count report routes
        Route::post('/physical-count/generate', [ReportController::class, 'generatePhysicalCountReport'])->middleware(PermissionMiddleware::class . ':report.generate')->name('reports.physicalCountReport');

        // reports.physical-count.update
        Route::post('/physical-count/update', [ReportController::class, 'updatePhysicalCountReport'])->middleware(PermissionMiddleware::class . ':report.edit')->name('reports.physical-count.update');
        // reports.physical-count.status
        Route::post('/physical-count/status', [ReportController::class, 'updatePhysicalCountStatus'])->middleware(PermissionMiddleware::class . ':report.edit')->name('reports.physical-count.status');
        // reports.physical-count.approve
        Route::post('/physical-count/approve', [ReportController::class, 'approvePhysicalCountReport'])->middleware(PermissionMiddleware::class . ':report.approve')->name('reports.physical-count.approve');
        // reports.physical-count.reject
        Route::post('/physical-count/reject', [ReportController::class, 'rejectPhysicalCountReport'])->middleware(PermissionMiddleware::class . ':report.reject')->name('reports.physical-count.reject');
        // reports.physical-count.rollback
        Route::post('/physical-count/rollback', [ReportController::class, 'rollBackRejectPhysicalCountReport'])->middleware(PermissionMiddleware::class . ':report.edit')->name('reports.physical-count.rollback');

        // reports.physicalCountShow
        Route::get('/physical-count-show', [ReportController::class, 'physicalCountShow'])->name('reports.physicalCountShow');

        // reports.disposals
        Route::get('/disposals', [ReportController::class, 'disposals'])->name('reports.disposals');

        // reports.issue-quantity
        Route::post('/issue-quantity/upload', [ReportController::class, 'importIssueQuantity'])->name('reports.issue-quantity.upload');
    });
    
    // Order Management Routes
    Route::prefix('orders')->group(function () {
        Route::post('/change-status', [OrderController::class, 'changeStatus'])->name('orders.change-status');
        Route::post('/change-item-status', [OrderController::class, 'changeItemStatus'])->name('orders.change-item-status');
        Route::post('/bulk-change-status', [OrderController::class, 'bulkChangeStatus'])->name('orders.bulk-change-status');
        Route::post('/bulk-change-item-status', [OrderController::class, 'bulkChangeItemStatus'])->name('orders.bulk-change-item-status');
    });
    // Approval Routes
    Route::middleware(['auth', \App\Http\Middleware\TwoFactorAuth::class])->prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });
});


require __DIR__.'/auth.php';
