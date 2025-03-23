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
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Middleware\PermissionMiddleware;

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
    Route::middleware(PermissionMiddleware::class.':user.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        // Create and Edit routes for navigation purposes
        Route::get('/users/create', function() {
            return Inertia::render('User/Create');
        })->middleware(PermissionMiddleware::class.':user.create')->name('users.create');
        
        Route::get('/users/{user}/edit', function(App\Models\User $user) {
            return Inertia::render('User/Edit', [
                'user' => $user
            ]);
        })->middleware(PermissionMiddleware::class.':user.edit')->name('users.edit');
        
        // User roles management
        Route::get('/users/{user}/roles', [UserController::class, 'showRoles'])
            ->middleware(PermissionMiddleware::class.':user.edit')
            ->name('users.roles');
    });
    Route::post('/users', [UserController::class, 'store'])->middleware(PermissionMiddleware::class.':user.create')->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware(PermissionMiddleware::class.':user.edit')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware(PermissionMiddleware::class.':user.delete')->name('users.destroy');
    
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
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
    
    // Category Routes
    Route::get('/categories', [CategoryController::class, 'index'])->middleware(PermissionMiddleware::class.':category.view')->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware(PermissionMiddleware::class.':category.create')->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware(PermissionMiddleware::class.':category.delete')->name('categories.destroy');
    Route::get('/api/categories', [CategoryController::class, 'getCategories'])->middleware(PermissionMiddleware::class.':category.view')->name('api.categories');
    
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
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    
    // Inventory Routes
    Route::controller(InventoryController::class)
    ->prefix('/inventories')
    ->group(function () {
        Route::get('/', 'index')->name('inventories.index');
        Route::post('/store', 'store')->name('inventories.store');
        Route::get('/{inventory}', 'show')->name('inventories.show');
        Route::delete('/{inventory}', 'destroy')->name('inventories.destroy');
    });

    // Supply Routes
    Route::controller(SupplyController::class)
    ->prefix('/supplies')->group(function () {
        Route::get('/', 'index')->name('supplies.index');
        Route::get('/create', 'create')->name('supplies.create');
        Route::post('/store', 'store')->name('supplies.store');
        Route::post('/batch', 'storeBatch')->name('supplies.store-batch');
        Route::get('/{supply}', 'show')->name('supplies.show');
        Route::delete('/{supply}', 'destroy')->name('supplies.destroy');
        Route::post('/supply-items/{id}/approve', [SupplyController::class, 'approveItem'])->name('supply-items.approve');
        Route::post('/approve-bulk', 'approveBulk')->name('supplies.approve-bulk');
        Route::get('/{supply}/items', [SupplyController::class, 'getItems'])->name('supplies.items');
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
        Route::delete('/{supplier}', 'destroy')->name('suppliers.destroy');

    });
    
    // Remove duplicate resource routes since we already have individual routes defined above
    // Route::middleware('role:admin')->group(function () {
    //     Route::resource('users', UserController::class);
    //     Route::resource('roles', RoleController::class);
    // });
});

require __DIR__.'/auth.php';
