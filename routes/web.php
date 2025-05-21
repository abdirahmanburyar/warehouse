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
use App\Http\Controllers\ConsumptionUploadController;
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

// Default route - redirect to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// All routes that require authentication and 2FA
Route::middleware(['auth', \App\Http\Middleware\TwoFactorAuth::class])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management Routes
    Route::middleware(PermissionMiddleware::class . ':user.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware(PermissionMiddleware::class . ':user.create')
            ->name('users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware(PermissionMiddleware::class . ':user.create')
            ->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware(PermissionMiddleware::class . ':user.edit')
            ->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])
            ->middleware(PermissionMiddleware::class . ':user.edit')
            ->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->middleware(PermissionMiddleware::class . ':user.delete')
            ->name('users.destroy');
    });

    // Role Management Routes
    Route::middleware(PermissionMiddleware::class . ':role.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware(PermissionMiddleware::class . ':role.create')
            ->name('roles.create');
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
            Route::post('/categories/store', [CategoryController::class, 'store'])
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

    // Product Management Routes
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->middleware(PermissionMiddleware::class . ':product.view')->name('products.index');
        Route::get('/create', [ProductController::class, 'create'])->middleware(PermissionMiddleware::class . ':product.create')->name('products.create');
        Route::post('/', [ProductController::class, 'store'])->middleware(PermissionMiddleware::class . ':product.create')->name('products.store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->middleware(PermissionMiddleware::class . ':product.edit')->name('products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->middleware(PermissionMiddleware::class . ':product.edit')->name('products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':product.delete')->name('products.destroy');
    });

    // Eligible Items Management Routes
    Route::prefix('eligible-items')->group(function () {
        Route::get('/', [EligibleItemController::class, 'index'])->middleware(PermissionMiddleware::class . ':eligible-item.view')->name('products.eligible.index');
        Route::get('/create', [EligibleItemController::class, 'create'])->middleware(PermissionMiddleware::class . ':eligible-item.create')->name('eligible-items.create');
        Route::post('/', [EligibleItemController::class, 'store'])->middleware(PermissionMiddleware::class . ':eligible-item.create')->name('eligible-items.store');
        Route::get('/{eligibleItem}/edit', [EligibleItemController::class, 'edit'])->middleware(PermissionMiddleware::class . ':eligible-item.edit')->name('eligible-items.edit');
        Route::put('/{eligibleItem}', [EligibleItemController::class, 'update'])->middleware(PermissionMiddleware::class . ':eligible-item.edit')->name('eligible-items.update');
        Route::delete('/{eligibleItem}', [EligibleItemController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':eligible-item.delete')->name('eligible-items.destroy');
    });

    // Supply Management Routes
    Route::prefix('supplies')->group(function () {
        Route::get('/', [SupplyController::class, 'index'])->name('supplies.index');
        Route::get('/create', [SupplyController::class, 'create'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.create');
        Route::post('/', [SupplyController::class, 'store'])->middleware(PermissionMiddleware::class . ':supply.create')->name('supplies.store');
        Route::get('/{supply}/edit', [SupplyController::class, 'edit'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('supplies.edit');
        Route::put('/{supply}', [SupplyController::class, 'update'])->middleware(PermissionMiddleware::class . ':supply.edit')->name('supplies.update');
        Route::delete('/{supply}', [SupplyController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':supply.delete')->name('supplies.destroy');
    });

    // Supplier Management Routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->middleware(PermissionMiddleware::class . ':supplier.view')->name('suppliers.index');
        Route::get('/create', [SupplierController::class, 'create'])->middleware(PermissionMiddleware::class . ':supplier.create')->name('suppliers.create');
        Route::post('/', [SupplierController::class, 'store'])->middleware(PermissionMiddleware::class . ':supplier.create')->name('suppliers.store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->middleware(PermissionMiddleware::class . ':supplier.edit')->name('suppliers.edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->middleware(PermissionMiddleware::class . ':supplier.edit')->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':supplier.delete')->name('suppliers.destroy');
    });

    // Expired Management Routes
    Route::prefix('expired')->group(function () {
        Route::get('/', [ExpiredController::class, 'index'])->middleware(PermissionMiddleware::class . ':expired.view')->name('expired.index');
        Route::get('/create', [ExpiredController::class, 'create'])->middleware(PermissionMiddleware::class . ':expired.create')->name('expired.create');
        Route::post('/', [ExpiredController::class, 'store'])->middleware(PermissionMiddleware::class . ':expired.create')->name('expired.store');
        Route::get('/{expired}/edit', [ExpiredController::class, 'edit'])->middleware(PermissionMiddleware::class . ':expired.edit')->name('expired.edit');
        Route::put('/{expired}', [ExpiredController::class, 'update'])->middleware(PermissionMiddleware::class . ':expired.edit')->name('expired.update');
        Route::delete('/{expired}', [ExpiredController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':expired.delete')->name('expired.destroy');
    });

    // Settings Management Routes
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->middleware(PermissionMiddleware::class . ':settings.view')->name('settings.index');
        Route::get('/create', [SettingsController::class, 'create'])->middleware(PermissionMiddleware::class . ':settings.create')->name('settings.create');
        Route::post('/', [SettingsController::class, 'store'])->middleware(PermissionMiddleware::class . ':settings.create')->name('settings.store');
        Route::get('/{settings}/edit', [SettingsController::class, 'edit'])->middleware(PermissionMiddleware::class . ':settings.edit')->name('settings.edit');
        Route::put('/{settings}', [SettingsController::class, 'update'])->middleware(PermissionMiddleware::class . ':settings.edit')->name('settings.update');
        Route::delete('/{settings}', [SettingsController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':settings.delete')->name('settings.destroy');
    });

    // Order Management Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware(PermissionMiddleware::class . ':order.view')->name('orders.index');
        Route::get('/create', [OrderController::class, 'create'])->middleware(PermissionMiddleware::class . ':order.create')->name('orders.create');
        Route::post('/', [OrderController::class, 'store'])->middleware(PermissionMiddleware::class . ':order.create')->name('orders.store');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->middleware(PermissionMiddleware::class . ':order.edit')->name('orders.edit');
        Route::put('/{order}', [OrderController::class, 'update'])->middleware(PermissionMiddleware::class . ':order.edit')->name('orders.update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':order.delete')->name('orders.destroy');
    });

    // Transfer Management Routes
    Route::prefix('transfers')->group(function () {
        Route::get('/', [TransferController::class, 'index'])->middleware(PermissionMiddleware::class . ':transfer.view')->name('transfers.index');
        Route::get('/create', [TransferController::class, 'create'])->middleware(PermissionMiddleware::class . ':transfer.create')->name('transfers.create');
        Route::post('/', [TransferController::class, 'store'])->middleware(PermissionMiddleware::class . ':transfer.create')->name('transfers.store');
        Route::get('/{transfer}/edit', [TransferController::class, 'edit'])->middleware(PermissionMiddleware::class . ':transfer.edit')->name('transfers.edit');
        Route::put('/{transfer}', [TransferController::class, 'update'])->middleware(PermissionMiddleware::class . ':transfer.edit')->name('transfers.update');
        Route::delete('/{transfer}', [TransferController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':transfer.delete')->name('transfers.destroy');
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
        Route::get('/', [FacilityController::class, 'index'])->middleware(PermissionMiddleware::class . ':facility.view')->name('facilities.index');
        Route::get('/create', [FacilityController::class, 'create'])->middleware(PermissionMiddleware::class . ':facility.create')->name('facilities.create');
        Route::post('/', [FacilityController::class, 'store'])->middleware(PermissionMiddleware::class . ':facility.create')->name('facilities.store');
        Route::get('/{facility}/edit', [FacilityController::class, 'edit'])->middleware(PermissionMiddleware::class . ':facility.edit')->name('facilities.edit');
        Route::put('/{facility}', [FacilityController::class, 'update'])->middleware(PermissionMiddleware::class . ':facility.edit')->name('facilities.update');
        Route::delete('/{facility}', [FacilityController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':facility.delete')->name('facilities.destroy');
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
    Route::prefix('assets')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->middleware(PermissionMiddleware::class . ':asset.view')->name('assets.index');
        Route::get('/create', [AssetController::class, 'create'])->middleware(PermissionMiddleware::class . ':asset.create')->name('assets.create');
        Route::post('/', [AssetController::class, 'store'])->middleware(PermissionMiddleware::class . ':asset.create')->name('assets.store');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->middleware(PermissionMiddleware::class . ':asset.edit')->name('assets.edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->middleware(PermissionMiddleware::class . ':asset.edit')->name('assets.update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':asset.delete')->name('assets.destroy');
    });

    // Inventory Management Routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->middleware(PermissionMiddleware::class . ':inventory.view')->name('inventories.index');
        Route::get('/create', [InventoryController::class, 'create'])->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.create');
        Route::post('/', [InventoryController::class, 'store'])->middleware(PermissionMiddleware::class . ':inventory.create')->name('inventories.store');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->middleware(PermissionMiddleware::class . ':inventory.edit')->name('inventories.edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->middleware(PermissionMiddleware::class . ':inventory.edit')->name('inventories.update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->middleware(PermissionMiddleware::class . ':inventory.delete')->name('inventories.destroy');
    });

    // Report Routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/facilities/monthly-consumption', [ReportController::class, 'monthlyConsumption'])->name('reports.monthlyConsumption');
        Route::get('/stockLevelReport', [ReportController::class, 'stockLevelReport'])->name('reports.stockLevelReport');
        
        // Excel Upload Route
        Route::post('/upload-consumption', [ConsumptionUploadController::class, 'upload'])->name('reports.upload-consumption');
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
