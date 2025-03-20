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
use App\Http\Controllers\InventoryController; // Added this line
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Middleware\PermissionMiddleware;

// Dashboard route - protected by auth and 2FA
Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', \App\Http\Middleware\TwoFactorAuth::class])->name('dashboard');

// Two-Factor Authentication Routes
Route::middleware('auth')->group(function () {
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/resend', [TwoFactorController::class, 'resend'])->name('two-factor.resend');
});

Route::middleware(['auth', 'verified', \App\Http\Middleware\TwoFactorAuth::class])->group(function () {
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
    
    // Approval Routes
    Route::middleware(PermissionMiddleware::class.':approval.view')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/create', [ApprovalController::class, 'create'])->name('approvals.create');
        Route::get('/approvals/{approval}/edit', [ApprovalController::class, 'edit'])->name('approvals.edit');
    });
    Route::post('/approvals', [ApprovalController::class, 'store'])->middleware(PermissionMiddleware::class.':approval.create')->name('approvals.store');
    Route::delete('/approvals/{approval}', [ApprovalController::class, 'destroy'])->middleware(PermissionMiddleware::class.':approval.delete')->name('approvals.destroy');
    
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
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
        Route::post('/inventories', [InventoryController::class, 'store'])->name('inventories.store');
        Route::get('/inventories/{inventory}', [InventoryController::class, 'show'])->name('inventories.show');
        Route::delete('/inventories/{inventory}', [InventoryController::class, 'destroy'])->name('inventories.destroy');
    });
});

require __DIR__.'/auth.php';
