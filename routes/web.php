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
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\PurchaseOrderController;
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
    Route::middleware(PermissionMiddleware::class.':user.view')
        ->prefix('users')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            // Create and Edit routes for navigation purposes
            Route::get('/create', function() {
                return Inertia::render('User/Create');
            })->middleware(PermissionMiddleware::class.':user.create')->name('users.create');
            Route::post('/store', [UserController::class, 'store'])->middleware(PermissionMiddleware::class.':user.create')->name('users.store');
            
            // User roles management
            Route::get('/{user}/roles', [UserController::class, 'showRoles'])
                ->middleware(PermissionMiddleware::class.':user.edit')
                ->name('users.roles');
            Route::delete('/{user}', [UserController::class, 'destroy'])->middleware(PermissionMiddleware::class.':user.delete')->name('users.destroy');
        });
        
    // Role Management Routes
    Route::middleware(PermissionMiddleware::class.':user.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles-permissions', [RoleController::class, 'getAllRolesAndPermissions'])->name('roles.get-all');
    });
    Route::post('/roles', [RoleController::class, 'store'])->middleware(PermissionMiddleware::class.':user.create')->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware(PermissionMiddleware::class.':user.edit')->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware(PermissionMiddleware::class.':user.delete')->name('roles.destroy');
    Route::post('/users/{user}/roles', [RoleController::class, 'assignRoles'])->middleware(PermissionMiddleware::class.':user.edit')->name('users.roles.assign');
        
    // Category Management Routes
    Route::middleware([\App\Http\Middleware\TwoFactorAuth::class, PermissionMiddleware::class.':category.view'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->middleware(PermissionMiddleware::class.':category.create')->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware(PermissionMiddleware::class.':category.edit')->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware(PermissionMiddleware::class.':category.delete')->name('categories.destroy');
    });

    // SubCategory Management Routes
    Route::middleware([\App\Http\Middleware\TwoFactorAuth::class, PermissionMiddleware::class.':category.view'])->group(function () {
        Route::get('/subcategories', [SubCategoryController::class, 'index'])->name('subcategories.index');
        Route::post('/subcategories', [SubCategoryController::class, 'store'])->middleware(PermissionMiddleware::class.':category.create')->name('subcategories.store');
        Route::put('/subcategories/{subcategory}', [SubCategoryController::class, 'update'])->middleware(PermissionMiddleware::class.':category.edit')->name('subcategories.update');
        Route::delete('/subcategories/{subcategory}', [SubCategoryController::class, 'destroy'])->middleware(PermissionMiddleware::class.':category.delete')->name('subcategories.destroy');
    });
        
    // Warehouse Management Routes
    Route::controller(WarehouseController::class)
        ->prefix('/warehouses')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class.':warehouse.view')->name('warehouses.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class.':warehouse.create')->name('warehouses.store');
            Route::put('/{warehouse}', 'update')->middleware(PermissionMiddleware::class.':warehouse.edit')->name('warehouses.update');
            Route::delete('/{warehouse}', 'destroy')->middleware(PermissionMiddleware::class.':warehouse.delete')->name('warehouses.destroy');
        });

    // Dosage Management Routes
    Route::controller(DosageController::class)
        ->prefix('/dosages')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class.':dosage.view')->name('dosages.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class.':dosage.create')->name('dosages.store');
            Route::delete('/{dosage}', 'destroy')->middleware(PermissionMiddleware::class.':dosage.delete')->name('dosages.destroy');
            Route::get('/by-category/{category}', 'getByCategory')->name('dosages.by-category');
        });
        
    // Product Management Routes
    Route::controller(ProductController::class)
        ->prefix('/products')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class.':product.view')->name('products.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class.':product.create')->name('products.store');
            Route::put('/{product}', 'update')->middleware(PermissionMiddleware::class.':product.edit')->name('products.update');
            Route::delete('/{product}', 'destroy')->middleware(PermissionMiddleware::class.':product.delete')->name('products.destroy');
            Route::post('/bulk', 'bulk')->middleware(PermissionMiddleware::class.':product.delete')->name('products.bulk');
            Route::post('/search', 'search')->name('products.search');
        });
        
    // Inventory Routes
    Route::controller(InventoryController::class)
        ->prefix('/inventories')
        ->group(function () {
            Route::get('/', 'index')->middleware(PermissionMiddleware::class.':inventory.view')->name('inventories.index');
            Route::post('/store', 'store')->middleware(PermissionMiddleware::class.':inventory.create')->name('inventories.store');
            Route::put('/{inventory}', 'update')->middleware(PermissionMiddleware::class.':inventory.edit')->name('inventories.update');
            Route::delete('/{inventory}', 'destroy')->middleware(PermissionMiddleware::class.':inventory.delete')->name('inventories.destroy');
            Route::post('/bulk', 'bulk')->middleware(PermissionMiddleware::class.':inventory.delete')->name('inventories.bulk');
        });

    // Supply Routes
    Route::controller(SupplyController::class)
        ->prefix('/supplies')
        ->group(function () {
            Route::get('/', 'index')->name('supplies.index');
            Route::post('/store', 'store')->name('supplies.store');
            Route::get('/items/{supply}', 'getItems')->name('supplies.items');
            Route::patch('/items/{item}/status', 'approveItem')->name('supplies.items.update-status');
            Route::delete('/{supply}', 'destroy')->name('supplies.destroy');
            Route::post('/bulk-delete', 'bulkDelete')->name('supplies.bulk-delete');
        });

    // Approval Routes
    Route::controller(ApprovalController::class)
        ->prefix('approvals')
        // ->middleware(['auth', 'verified', \App\Http\Middleware\TwoFactorAuth::class])
        ->group(function () {
            Route::get('/', 'index')
                ->name('approvals.index');
            Route::get('/create', 'create')
                ->name('approvals.create');
            Route::get('/{approval}/edit', 'edit')
            ->name('approvals.edit');
        Route::post('/', 'store')
            ->name('approvals.store');
        Route::delete('/{approval}', 'destroy')
            ->name('approvals.destroy');
    });
        
    // Supplier Routes
    Route::controller(SupplierController::class)
        ->prefix('/suppliers')->group(function () {
            // Redirect to supplies index with suppliers tab active
            Route::get('/', function() {
                return redirect()->route('supplies.index', ['tab' => 'suppliers']);
            })->name('suppliers.index');
            Route::post('/store', 'store')->name('suppliers.store');
            Route::delete('/{supplier}/destroy', 'destroy')->name('suppliers.destroy');

        });
        
    // Purchase Orders
    Route::prefix('purchase-orders')
        ->controller(PurchaseOrderController::class)
        ->group(function () {
        Route::get('/', 'index')->name('purchase-orders.index');
        Route::post('/store', 'store')->name('purchase-orders.store');
        Route::delete('/{purchaseOrder}/destroy', 'destroy')->name('purchase-orders.destroy');
        Route::post('/back-orders', 'createBackOrder')->name('purchase-orders.back-orders.create');

        // packing list
        Route::get('/{purchaseOrder}/packing-list', 'packingList')->name('purchase-orders.packing-list');
        Route::post('/packing-list/store', 'packingListStore')->name('purchase-orders.packing-list.store');
        Route::get('/{purchaseOrder}/back-orders/{productId}', 'getBackOrders')->name('purchase-orders.back-orders.get');

    });

    // Settings Routes
    Route::middleware(PermissionMiddleware::class.':settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
    // Expired Routes
    Route::controller(ExpiredController::class)
        ->prefix('/expired')->group(function () {
            Route::get('/', 'index')->name('expired.index');
            Route::post('/dispose', 'markAsDisposed')->name('expired.dispose');
        });

    Route::controller(OrderController::class)
        ->prefix('/orders')
        ->group(function () {
            Route::get('/', 'index')->name('orders.index');
            Route::post('/store', 'store')->name('orders.store');
            Route::get('/{order}', 'show')->name('orders.show');
            Route::delete('/{order}', 'destroy')->name('orders.destroy');
            Route::post('/bulk', 'bulk')->name('orders.bulk');
            Route::post('/search', 'searchProduct')->name('order.product.search');
            Route::post('/status', 'changeStatus')->name('orders.change-status');   
        });

    
    // Remove duplicate resource routes since we already have individual routes defined above
    // Route::middleware('role:admin')->group(function () {
    //     Route::resource('users', UserController::class);
    //     Route::resource('roles', RoleController::class);
    // });

});

require __DIR__.'/auth.php';
