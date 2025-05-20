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
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Middleware\PermissionMiddleware;

// Welcome route - accessible without authentication

Route::get('/welcome', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome');
})->name('welcome');

// Broadcast routes
Route::get('/broadcasting/auth', function () {
    return \Laravel\Echo\Broadcasting\Auth::check();
})->middleware('auth')->name('broadcasting.auth');

// Two-Factor Authentication Routes - These must be accessible without 2FA
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});

// All routes that require 2FA
Route::middleware(['auth', 'verified', \App\Http\Middleware\TwoFactorAuth::class])->group(function () {


    // Dashboard route
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(ExpiredController::class)
        ->prefix('/expires')
        ->name('expired.')
        ->group(function(){
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/dispose', 'dispose')->name('dispose');
            Route::get('/transfer/{inventory}', 'transfer')->name('transfer');
        });

    // Order Routes
    Route::controller(OrderController::class)
        ->prefix('orders')
        ->name('orders.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pending', 'pending')->name('pending');
            Route::get('/approved', 'approved')->name('approved');
            Route::get('/in-process', 'inProcess')->name('in-process');
            Route::get('/dispatched', 'dispatched')->name('dispatched');
            Route::get('/delivered', 'delivered')->name('delivered');
            Route::get('/received', 'received')->name('received');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/change-status', 'changeStatus')->name('change-status');
            Route::post('/bulk-change-status', 'bulkChangeStatus')->name('bulk-change-status');
            Route::post('/bulk-change-item-status', 'bulkChangeItemStatus')->name('bulk-change-item-status');
        });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // User Management Routes
    Route::middleware(PermissionMiddleware::class . ':user.view')
        ->prefix('users')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('settings.users.index');
            
            // Create routes
            Route::get('/create', [UserController::class, 'create'])
                ->middleware(PermissionMiddleware::class . ':user.create')
                ->name('settings.users.create');
                
            Route::post('/store', [UserController::class, 'store'])
                ->middleware(PermissionMiddleware::class . ':user.create')
                ->name('settings.users.store');

            // Edit routes
            Route::get('/{user}/edit', function ($user) {
                return Inertia::render('User/Edit', [
                    'user' => App\Models\User::with(['roles', 'warehouse'])->findOrFail($user),
                    'roles' => App\Models\Role::all(),
                    'warehouses' => App\Models\Warehouse::all()
                ]);
            })->middleware(PermissionMiddleware::class . ':user.edit')
              ->name('settings.users.edit');
            Route::put('/{user}/edit', [UserController::class, 'update'])
                ->middleware(PermissionMiddleware::class . ':user.edit')
                ->name('users.update');

            // Bulk actions
            Route::post('/bulk-status', [UserController::class, 'bulkUpdateStatus'])
                ->middleware(PermissionMiddleware::class . ':user.edit')
                ->name('users.bulk-status');

            // User roles management
            Route::get('/{user}/roles', [UserController::class, 'showRoles'])
                ->middleware(PermissionMiddleware::class . ':user.edit')
                ->name('settings.users.roles');
            
            // Delete
            Route::delete('/{user}', [UserController::class, 'destroy'])
                ->middleware(PermissionMiddleware::class . ':user.delete')
                ->name('users.destroy');
        });

    // Role Management Routes
    Route::controller(RoleController::class)
        ->middleware(PermissionMiddleware::class . ':user.view')        
        ->group(function () {
            Route::get('/roles', 'index')->name('settings.roles.index');
            Route::post('/roles', 'store')->name('settings.roles.store');
            Route::get('/roles/{role}/edit', 'edit')->name('settings.roles.edit');
            Route::put('/roles/{role}', 'update')->name('settings.roles.update');
            Route::delete('/roles/{role}', 'destroy')->name('settings.roles.destroy');
        });

    Route::post('/users/{user}/roles', [RoleController::class, 'assignRoles'])
        ->middleware(PermissionMiddleware::class . ':user.edit')
        ->name('users.roles.assign');

    // Category Management Routes
    Route::middleware([\App\Http\Middleware\TwoFactorAuth::class, PermissionMiddleware::class . ':category.view'])
        ->group(function () {
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [CategoryController::class, 'store'])
                ->middleware(PermissionMiddleware::class . ':category.create')
                ->name('categories.store');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])
                ->middleware(PermissionMiddleware::class . ':category.edit')
                ->name('categories.edit');
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
     });

    // Dosage Management Routes
    Route::prefix('product/dosages')->group(function () {
        Route::get('/', [DosageController::class, 'index'])->middleware(PermissionMiddleware::class . ':dosage.view')->name('products.dosages.index');
        Route::get('/create', [DosageController::class, 'create'])->middleware(PermissionMiddleware::class . ':dosage.create')->name('products.dosages.create');
        Route::post('/', [DosageController::class, 'store'])->middleware(PermissionMiddleware::class . ':dosage.create')->name('products.dosages.store');
        Route::get('/{dosage}/edit', [DosageController::class, 'edit'])->middleware(PermissionMiddleware::class . ':dosage.edit')->name('products.dosages.edit');
        Route::put('/{dosage}', [DosageController::class, 'update'])->middleware(PermissionMiddleware::class . ':dosage.edit')->name('products.dosages.update');
        Route::delete('/{dosage}', [DosageController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':dosage.delete')->name('products.dosages.destroy');
    });

    // Eligible Items Management Routes
    Route::prefix('product/eligible')->group(function () {
        Route::get('/', [EligibleItemController::class, 'index'])->name('products.eligible.index');
        Route::get('/create', [EligibleItemController::class, 'create'])->name('products.eligible.create');
        Route::post('/store', [EligibleItemController::class, 'store'])->name('products.eligible.store');
        Route::get('/{eligible}/edit', [EligibleItemController::class, 'edit'])->name('products.eligible.edit');
        Route::delete('/{eligible}', [EligibleItemController::class, 'destroy'])->name('products.eligible.destroy');
    });

    // Product Management Routes
    Route::controller(ProductController::class)
        ->prefix('/products')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':product.view')->name('products.index');
            Route::get('/create', 'create')->middleware(PermissionMiddleware::class . ':product.create')->name('products.create');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':product.create')->name('products.store');
            Route::get('/{product}/edit', 'edit')->middleware(PermissionMiddleware::class . ':product.edit')->name('products.edit');
            Route::put('/{product}', 'update')->middleware(PermissionMiddleware::class . ':product.edit')->name('products.update');
            Route::delete('/{product}', 'destroy')->middleware(PermissionMiddleware::class . ':product.delete')->name('products.destroy');
        });

        // Inventory Routes
        Route::controller(InventoryController::class)
            ->prefix('/inventories')
            ->group(function () {
                // Main inventory routes
                Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':inventory.view')->name('inventories.index');
                Route::get('/items', 'items')->middleware(PermissionMiddleware::class . ':inventory.view')->name('inventories.items');
                
                // CRUD operations
                Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.store');
                Route::put('/{inventory}', 'update')->middleware(PermissionMiddleware::class . ':inventory.edit')->name('inventories.update');
                Route::delete('/{inventory}', 'destroy')->middleware(PermissionMiddleware::class . ':inventory.delete')->name('inventories.destroy');
                Route::post('/bulk', 'bulk')->middleware(PermissionMiddleware::class . ':inventory.delete')->name('inventories.bulk');
            });

        // Supply Routes
        Route::controller(SupplyController::class)
            ->prefix('/supplies')
            ->group(function () {
                Route::get('/', 'index')->name('supplies.index');
                Route::get('/show', 'show')->name('supplies.show');
                Route::get('/items/{supply}', 'getItems')->name('supplies.items');
                Route::get('/packing-list/{pk}/edit', 'editPK')->name('supplies.packing-list.edit');
                Route::post('/packing-list/update', 'updatePK')->name('supplies.packing-list.update');
                Route::post('/packing-list/review', 'reviewPK')->name('supplies.packing-list.review');
                Route::post('/packing-list/approve', 'approvePK')->name('supplies.packing-list.approve');
                Route::post('/packing-list/location', 'storePKLocation')->name('supplies.packing-list.location.store');
                Route::get('/packing-list/show', 'showPK')->name('supplies.packing-list.showPK');
                Route::get('/purchase-order/{id}/show', 'showPO')->name('supplies.po-show');
                Route::get('/locations', 'locationsShow')->name('supplies.locations');
                Route::get('/locations/{id}/edit', 'locationEdit')->name('supplies.location.edit');     

                // Back orde            
                Route::get('/back-order/{id}/delete', 'deletePackingListDiff')->name("supplies.deletePackingListDiff");
                Route::get("/back-order/show", 'showBackOrder')->name('supplies.showBackOrder');

                
                Route::patch('/items/{item}/status', 'approveItem')->name('supplies.items.update-status');
                Route::delete('/{supply}/delete', 'destroy')->name('supplies.destroy');
                Route::post('/bulk-delete', 'bulkDelete')->name('supplies.bulk-delete');
                Route::get('/{id}/get', 'getSupplier')->name("supplier.get");
                Route::get('/supplier/{search}', 'searchsupplier')->name("supplier.searchSupplier");
                Route::get('/product/{id}', 'searchProduct')->name("product.search");
                
                // Purchase Order Routes
                Route::get('/puchase-order', 'newPO')->name('supplies.purchase_order');
                Route::get('/purchase-order/create', 'create')->name('supplies.create');
                Route::post('/purchase-order/store', 'storePO')->name('supplies.storePO');
                Route::post('/purchase-order/{id}/reject', 'rejectPO')->name('supplies.rejectPO');
                Route::post('/purchase-order/{id}/approve', 'approvePO')->name('supplies.approvePO');
                Route::post('/purchase-order/{id}/review', 'reviewPO')->name('supplies.reviewPO');
                Route::post('/packing-list/store', 'storePK')->name('supplies.storePK');


                Route::get('/purchase-order/{id}/edit', 'editPO')->name('supplies.editPO');
                Route::get('/purchase-order/{id}/get', 'getPurchaseOrder')->name('supplies.getPO');
                Route::put('/purchase-order/{id}/update', 'updatePurchaseOrder')->name('supplies.updatePO');
                Route::get('/purchase-order/{id}/delete', 'deletePurchaseOrder')->name('supplies.deletePO');
                Route::delete('/purchase-order-item/{id}/delete', 'deletePurchaseOrderItem')->name('supplies.deleteItem');

                // Packing list
                Route::get('/back-order', 'backOrder')->name('supplies.back-order');
                Route::get('/packing-list', 'newPackingList')->name('supplies.packing-list');
                Route::get('/packing-list/{id}/purchase-order', 'getPO')->name('supplies.get-purchaseOrder');
                Route::get('/back-order/{id}/purchase-order', 'getBackOrder')->name('supplies.get-packingList');

                // back order 
                Route::post('/back-order/status-change', 'BackOrderstatusChange')->name('supplies.backorder-status');

                // locations
                Route::post('/location/store', 'storeLocation')->name('supplies.location-store');

                // laod items
                Route::get('/packing-list/{id}/load-items', 'loadItems')->name('supplies.packing-list.loadItems');
            });

        // Supplier Routes
        Route::controller(SupplierController::class)
            ->prefix('/suppliers')->group(function () {
                Route::post('/store', 'store')->name('supplies.suppliers.store');
                Route::delete('/{supplier}/destroy', 'destroy')->name('supplies.suppliers.destroy');
                Route::get('/{supplier}/edit', 'edit')->name('supplies.suppliers.edit');            
            });

        // Purchase Orders
        Route::controller(PurchaseOrderController::class)
            ->prefix('purchase-orders')->name('purchase-orders.')->group(function () {
                Route::post('/import', 'importItems')->name('import');
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{purchaseOrder}', 'show')->name('show');
                Route::get('/{purchaseOrder}/edit', 'edit')->name('edit');
                Route::put('/{purchaseOrder}', 'update')->name('update');
                Route::delete('/{purchaseOrder}', 'destroy')->name('destroy');

                // Packing List Routes
                Route::get('/{purchaseOrder}/packing-list', 'packingList')->name('packing-list');
                Route::post('/packing-list/store', 'packingListStore')->name('packing-list.store');
                Route::post('/{purchaseOrder}/packing-list/create', 'createPackingList')->name('packing-list.create');
                Route::get('/packing-list/{id}/items', 'getPackingListItems')->name('packing-list.items');
                Route::post('/{purchaseOrder}/packing-list/update-item', 'updateItem')->name('packing-list.update-item');
                Route::post('/{purchaseOrder}/packing-list/bulk-approve', 'bulkApprove')->name('packing-list.bulk-approve');
                Route::get('/{purchaseOrder}/packing-list/export', 'exportPackingList')->name('packing-list.export');
                Route::post('/delete-items', 'deleteItems')->name('deleteItems');

                // Import Route
            Route::get('/approved', [OrderController::class, 'approved'])->name('approved');
            Route::get('/in-process', [OrderController::class, 'inProcess'])->name('in-process');
            Route::get('/dispatched', [OrderController::class, 'dispatched'])->name('dispatched');
            Route::get('/delivered', [OrderController::class, 'delivered'])->name('delivered');
            Route::get('/received', [OrderController::class, 'received'])->name('received');
            Route::get('/{id}/show', [OrderController::class, 'show'])->name('show');

            // Other order routes
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/items/{order}', [OrderController::class, 'items'])->name('items');
            Route::get('/{order}/show', [OrderController::class, 'show'])->name('show');
            Route::post('/bulk-change-status', [OrderController::class, 'bulkChangeStatus'])->name('bulk-change-status');
            Route::post('/bulk-change-item-status', [OrderController::class, 'bulkChangeItemStatus'])->name('bulk-change-item-status');
        });

        // Approvals routes
        Route::controller(ApprovalController::class)
            ->prefix('approvals')
            ->middleware(['auth', 'verified'])
            ->group(function () {
                Route::get('/', 'index')->name('approvals.index');
                Route::post('/{id}/approve', 'approve')->name('approvals.approve');
                Route::post('/{id}/reject', 'reject')->name('approvals.reject');
            });

        Route::controller(FacilityController::class)
            ->prefix('/facilities')
            ->group(function () {
                Route::get('/', 'index')->name('facilities.index');
                Route::post('/store', 'store')->name('facilities.store');
                Route::delete('/{facility}', 'destroy')->name('facilities.destroy');
            });

        Route::get('assets/locations/{location}/sub-locations', [AssetController::class, 'getSubLocations'])->name('assets.locations.sub-locations');

        Route::prefix('product/categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('products.categories.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('products.categories.create');
            Route::post('/store', [CategoryController::class, 'store'])->name('products.categories.store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('products.categories.edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('products.categories.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('products.categories.destroy');
        });

        Route::controller(TransferController::class)
            ->prefix('/transfers')
            ->group(function () {
                Route::get('/', 'index')->name('transfers.index');
                Route::get('/create', 'create')->name('transfers.create');
                Route::post('/store', 'store')->name('transfers.store');
                Route::post('/approve/{id}', 'approve')->name('transfers.approve');
                Route::post('/reject/{id}', 'reject')->name('transfers.reject');
                Route::post('/inProcess/{id}', 'inProcess')->name('transfers.inProcess');
                Route::post('/completeTransfer/{id}', 'completeTransfer')->name('transfers.completeTransfer');
            });

        Route::controller(DistrictController::class)
            ->prefix('/districts')
            ->group(function () {
                Route::get('/', 'index')->name('districts.index');
                Route::post('/store', 'store')->name('districts.store');
                Route::delete('/{district}', 'destroy')->name('districts.destroy');
            });

        Route::controller(DispatchController::class)
            ->prefix('/orders-dispatch')
            ->group(function () {
                Route::get('/', 'index')->name('dispatch.index');
                Route::post('/process', 'process')->name('dispatch.process');
            });
        Route::controller(App\Http\Controllers\AssetController::class)
            ->prefix('/assets-management')
            ->group(function () {
        // Asset Management Routes
        Route::get('/', 'index')->name('assets.index');
        Route::get('/get-assets', 'getAssets')->name('assets.get');
        Route::get('/create', 'create')->name('assets.create');
        Route::post('/store', 'store')->name('assets.store');
        Route::get('/{asset}/edit', 'edit')->name('assets.edit');
        Route::put('/{asset}', 'update')->name('assets.update');
        Route::delete('/{asset}/delete', 'destroy')->name('assets.destroy');

            
        // Asset Categories Routes
        Route::get('/categories', [App\Http\Controllers\AssetCategoryController::class, 'index'])->name('assets.categories.index');
        Route::post('/categories', [App\Http\Controllers\AssetCategoryController::class, 'store'])->name('assets.categories.store');
        
        // Asset Locations Routes
        Route::get('/locations', [App\Http\Controllers\AssetLocationController::class, 'index'])->name('assets.locations.index');
        Route::post('/locations/store', [App\Http\Controllers\AssetLocationController::class, 'store'])->name('assets.locations.store');
        Route::get('/locations/{location}/sub-locations', [App\Http\Controllers\AssetLocationController::class, 'getSubLocations'])->name('assets.locations.sub-locations');
        Route::post('/locations/sub-locations', [App\Http\Controllers\AssetLocationController::class, 'storeSubLocation'])->name('assets.locations.sub-locations.store');
        Route::get('/locations/create', [App\Http\Controllers\AssetLocationController::class, 'create'])->name('assets.locations.create');
        Route::get('/locations/{id}/edit', [App\Http\Controllers\AssetLocationController::class, 'edit'])->name('assets.locations.edit');
        Route::delete('/locations/{id}', [App\Http\Controllers\AssetLocationController::class, 'destroy'])->name('assets.locations.destroy');

        // Asset Sub-Locations Routes
        Route::get('/sub-locations', [App\Http\Controllers\SubLocationController::class, 'index'])->name('assets.sub-locations.index');
        Route::get('/sub-locations/create', [App\Http\Controllers\SubLocationController::class, 'create'])->name('assets.sub-locations.create');
        Route::post('/sub-locations/store', [App\Http\Controllers\SubLocationController::class, 'store'])->name('assets.sub-locations.store');
        Route::get('/sub-locations/{id}/edit', [App\Http\Controllers\SubLocationController::class, 'edit'])->name('assets.sub-locations.edit');
        Route::delete('/sub-locations/{id}', [App\Http\Controllers\SubLocationController::class, 'destroy'])->name('assets.sub-locations.destroy');

            
        });

        // Approvals routes
        Route::controller(ApprovalController::class)
            ->prefix('approvals')
            ->name('approvals.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/{id}/approve', 'approve')->name('approve');
                Route::post('/{id}/reject', 'reject')->name('reject');
            });

        // Dispatch routes
        Route::prefix('dispatch')
            ->name('dispatch.')
            ->group(function () {
                Route::get('/', [DispatchController::class, 'index'])->name('index');
                Route::post('/process', [DispatchController::class, 'process'])->name('process');
            });
            
        // Transfer routes
        Route::prefix('transfers')
            ->name('transfers.')
            ->group(function () {
                Route::get('/get-inventories', [TransferController::class, 'getInventories'])->name('getInventories');
            });


        Route::controller(ReportController::class)
            ->prefix('/reports')
            ->group(function () {
                Route::get('/', 'index')->name('reports.index');
                Route::get('/stock-level-report', 'stockLevelReport')->name('reports.stockLevelReport');
                Route::get('/issued-quantity', 'issuedQuantity')->name('reports.issuedQuantity');
            });
});

require __DIR__ . '/auth.php';
