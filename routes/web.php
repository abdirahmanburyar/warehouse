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
use App\Http\Controllers\InventoryController;
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

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management Routes
    Route::middleware(PermissionMiddleware::class . ':user.view')
        ->prefix('users')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            // Create and Edit routes for navigation purposes
            Route::get('/create', function () {
                return Inertia::render('User/Create');
            })->middleware(PermissionMiddleware::class . ':user.create')->name('users.create');
            Route::post('/store', [UserController::class, 'store'])->middleware(PermissionMiddleware::class . ':user.create')->name('users.store');

            // User roles management
            Route::get('/{user}/roles', [UserController::class, 'showRoles'])
                ->middleware(PermissionMiddleware::class . ':user.edit')
                ->name('users.roles');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':user.delete')->name('users.destroy');
        });

    // Role Management Routes
    Route::middleware(PermissionMiddleware::class . ':user.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles-permissions', [RoleController::class, 'getAllRolesAndPermissions'])->name('roles.get-all');
    });
    Route::post('/roles', [RoleController::class, 'store'])->middleware(PermissionMiddleware::class . ':user.create')->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware(PermissionMiddleware::class . ':user.edit')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':user.delete')->name('roles.destroy');
    Route::post('/users/{user}/roles', [RoleController::class, 'assignRoles'])->middleware(PermissionMiddleware::class . ':user.edit')->name('users.roles.assign');

    // Category Management Routes
    Route::middleware([\App\Http\Middleware\TwoFactorAuth::class, PermissionMiddleware::class . ':category.view'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware(PermissionMiddleware::class . ':category.create')->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware(PermissionMiddleware::class . ':category.edit')->name('categories.update');
        Route::get('/categories/{category}/destroy', [CategoryController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':category.delete')->name('categories.destroy');
    });

    // Warehouse Management Routes
    Route::controller(WarehouseController::class)
        ->prefix('/inventories/warehouses')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':warehouse.view')->name('inventories.warehouses.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':warehouse.create')->name('inventories.warehouses.store');
            Route::get('/create', 'create')->middleware(PermissionMiddleware::class . ':warehouse.create')->name('inventories.warehouses.create');
            Route::get('/{warehouse}/edit', 'edit')->middleware(PermissionMiddleware::class . ':warehouse.edit')->name('inventories.warehouses.edit');
            Route::delete('/{warehouse}/delete', 'destroy')->middleware(PermissionMiddleware::class . ':warehouse.delete')->name('inventories.warehouses.destroy');
        });
    // Location Routes
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
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':product.create')->name('products.store');
            Route::get('/create', 'create')->name('products.create');

            Route::delete('/{product}/destroy', 'destroy')->middleware(PermissionMiddleware::class . ':product.delete')->name('products.destroy');
            Route::post('/bulk', 'bulk')->middleware(PermissionMiddleware::class . ':product.delete')->name('products.bulk');
            Route::post('/search', 'search')->name('products.search');

            Route::get('/{product}/edit', 'edit')->name('products.edit');
            
            

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
            Route::post('/import-items', 'importItems')->name('import-items');
        });

    // Settings Routes
    Route::middleware(PermissionMiddleware::class . ':settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
    // Expired Routes
    Route::controller(ExpiredController::class)
        ->prefix('/expired')->group(function () {
            Route::get('/', 'index')->name('expired.index');
            Route::post('/dispose', 'markAsDisposed')->name('expired.dispose');
        });

    // Order routes
    Route::prefix('orders')->name('orders.')->middleware(['auth', 'verified'])->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/items/{order}', [OrderController::class, 'items'])->name('items');
        Route::get('/{order}/show', [OrderController::class, 'show'])->name('show');
        
        Route::post('/change-status', [OrderController::class, 'changeStatus'])->name('change-status');
        Route::post('/bulk-change-status', [OrderController::class, 'bulkChangeStatus'])->name('bulk-change-status');
        Route::post('/bulk-change-item-status', [OrderController::class, 'bulkChangeItemStatus'])->name('bulk-change-item-status');
        Route::post('/update-item', [OrderController::class, 'updateItem'])->name('update-item');
        Route::post('/change-item-status', [OrderController::class, 'changeItemStatus'])->name('change-item-status');
        Route::get('/get-outstanding/{id}', [OrderController::class, 'getOutstanding'])->name('outstanding');
    });

    Route::controller(ApprovalController::class)
        ->prefix('approvals')
        ->group(function () {
            Route::get('/', 'index')->name('approvals.index');
            Route::post('/', 'store')->name('approvals.store');
            Route::delete('/{approval}/destroy', 'destroy')->name('approvals.destroy');
        });

    Route::controller(FacilityController::class)
        ->prefix('/facilities')
        ->group(function () {
            Route::get('/', 'index')->name('facilities.index');
            Route::post('/store', 'store')->name('facilities.store');
            Route::delete('/{facility}', 'destroy')->name('facilities.destroy');
        });

    Route::get('assets/locations/{location}/sub-locations', [AssetController::class, 'getSubLocations'])->name('assets.locations.sub-locations');

    // Remove duplicate resource routes since we already have individual routes defined above
    // Route::middleware('role:admin')->group(function () {
    //     Route::resource('users', UserController::class);
    //     Route::resource('products', ProductController::class);
    Route::prefix('product/categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('products.categories.index');
        Route::get('/create', [CategoryController::class, 'create'])->name('products.categories.create');
        Route::post('/', [CategoryController::class, 'store'])->name('products.categories.store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('products.categories.edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('products.categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('products.categories.destroy');
    });
    // });

    Route::controller(TransferController::class)
        ->prefix('/transfers')
        ->group(function () {
            Route::get('/', 'index')->name('transfers.index');
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


    Route::prefix('/dispatch')->group(function () {
        Route::get('/', [DispatchController::class, 'index'])->name('dispatch.index');
        Route::post('/process', [DispatchController::class, 'process'])->name('dispatch.process');
    });

    Route::controller(ReportController::class)
        ->prefix('/reports')
        ->group(function () {
            Route::get('/', 'index')->name('reports.index');
            Route::get('/stock-level-report', 'stockLevelReport')->name('reports.stockLevelReport');
            Route::get('/issued-quantity', 'issuedQuantity')->name('reports.issuedQuantity');
        });

    // Product Management Routes
});

require __DIR__ . '/auth.php';
