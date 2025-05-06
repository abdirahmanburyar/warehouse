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
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\SupplierController;
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
        ->prefix('/warehouses')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':warehouse.view')->name('warehouses.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':warehouse.create')->name('warehouses.store');
            Route::put('/{warehouse}', 'update')->middleware(PermissionMiddleware::class . ':warehouse.edit')->name('warehouses.update');
            Route::delete('/{warehouse}', 'destroy')->middleware(PermissionMiddleware::class . ':warehouse.delete')->name('warehouses.destroy');
        });

    // Dosage Management Routes
    Route::controller(DosageController::class)
        ->prefix('/dosages')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':dosage.view')->name('dosages.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':dosage.create')->name('dosages.store');
            Route::get('/{dosage}/destroy', 'destroy')->middleware(PermissionMiddleware::class . ':dosage.delete')->name('dosages.destroy');
        });

    // Product Management Routes
    Route::controller(ProductController::class)
        ->prefix('/products')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':product.view')->name('products.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class . ':product.create')->name('products.store');
            Route::put('/{product}', 'update')->middleware(PermissionMiddleware::class . ':product.edit')->name('products.update');
            Route::delete('/{product}', 'destroy')->middleware(PermissionMiddleware::class . ':product.delete')->name('products.destroy');
            Route::post('/bulk', 'bulk')->middleware(PermissionMiddleware::class . ':product.delete')->name('products.bulk');
            Route::post('/search', 'search')->name('products.search');
            Route::post('/eligible/store', 'addEligibleItemStore')->name('eligible-items.store');
            Route::get('/eligible/{id}', 'destroyEligibleItem')->name('eligible-items.destroy');
        });

    // Inventory Routes
    Route::controller(InventoryController::class)
        ->prefix('/inventories')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class . ':inventory.view')->name('inventories.index');
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
            Route::get('/items/{supply}', 'getItems')->name('supplies.items');
            Route::get('/purchase-order/{id}/show', 'showPO')->name('supplies.po-show');


            
            Route::patch('/items/{item}/status', 'approveItem')->name('supplies.items.update-status');
            Route::delete('/{supply}', 'destroy')->name('supplies.destroy');
            Route::post('/bulk-delete', 'bulkDelete')->name('supplies.bulk-delete');
            Route::get('/puchase-order', 'newPO')->name('supplies.purchase_order');
            Route::get('/{id}/get', 'getSupplier')->name("supplier.get");
            Route::get('/supplier/{search}', 'searchsupplier')->name("supplier.searchSupplier");
            Route::get('/product/{id}', 'searchProduct')->name("product.search");

            // Purchase Order Routes
            Route::get('/purchase-order/create', 'create')->name('supplies.create');
            Route::post('/purchase-order/store', 'storePO')->name('supplies.storePO');
            Route::get('/purchase-order/{id}/edit', 'editPO')->name('supplies.editPO');
            Route::get('/purchase-order/{id}/get', 'getPurchaseOrder')->name('supplies.getPO');
            Route::put('/purchase-order/{id}/update', 'updatePurchaseOrder')->name('supplies.updatePO');
            Route::get('/purchase-order/{id}/delete', 'deletePurchaseOrder')->name('supplies.deletePO');
            Route::delete('/purchase-order-item/{id}/delete', 'deletePurchaseOrderItem')->name('supplies.deleteItem');

            // Packing list
            Route::get('/packing-list', 'newPackingList')->name('supplies.packing-list');
        });

    // Supplier Routes
    Route::controller(SupplierController::class)
        ->prefix('/suppliers')->group(function () {
            // Redirect to supplies index with suppliers tab active
            Route::get('/', function () {
                return redirect()->route('supplies.index', ['tab' => 'suppliers']);
            })->name('suppliers.index');
            Route::post('/store', 'store')->name('suppliers.store');
            Route::delete('/{supplier}/destroy', 'destroy')->name('suppliers.destroy');
            Route::get('/{supplier}/edit', 'edit')->name('suppliers.edit');            
        });

    // Purchase Orders
    Route::controller(PurchaseOrderController::class)
        ->prefix('purchase-orders')->name('purchase-orders.')->group(function () {
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

    // Remove duplicate resource routes since we already have individual routes defined above
    // Route::middleware('role:admin')->group(function () {
    //     Route::resource('users', UserController::class);
    //     Route::resource('roles', RoleController::class);
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
    
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/', function () { return redirect('/dashboard'); });
        Route::get('/dashboard', function () { return Inertia::render('Dashboard'); })->name('dashboard');
    });

    Route::prefix('/assets-management')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('assets.index');
        Route::post('/store', [AssetController::class, 'store'])->name('assets.store');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
        Route::post('/{asset}/update-status', [AssetController::class, 'updateStatus'])->name('assets.update-status');
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
        });
});

require __DIR__ . '/auth.php';
