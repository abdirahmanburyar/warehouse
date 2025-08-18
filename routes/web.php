<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\PermissionMiddleware;
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
use App\Http\Controllers\ReceivedBackorderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\LogisticCompanyController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ReasonController;
use App\Http\Controllers\ReorderLevelController;
use Maatwebsite\Excel\Facades\Excel;

// Welcome route - accessible without authentication

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome');
})
    ->name('welcome')
    ->middleware('guest');

// Test route for expired stats (no auth required)
Route::get('/test-expired-stats', function() {
    $now = \Carbon\Carbon::now();
    $sixMonthsFromNow = $now->copy()->addMonths(6);
    $oneYearFromNow = $now->copy()->addYear();

    $expiredCount = \App\Models\InventoryItem::where('quantity', '>', 0)
        ->where('expiry_date', '<', $now)
        ->count();
    $expiring6MonthsCount = \App\Models\InventoryItem::where('quantity', '>', 0)
        ->where('expiry_date', '>=', $now)
        ->where('expiry_date', '<=', $sixMonthsFromNow)
        ->count();
    $expiring1YearCount = \App\Models\InventoryItem::where('quantity', '>', 0)
        ->where('expiry_date', '>=', $now)
        ->where('expiry_date', '<=', $oneYearFromNow)
        ->count();

    return response()->json([
        'expired' => $expiredCount,
        'expiring_within_6_months' => $expiring6MonthsCount,
        'expiring_within_1_year' => $expiring1YearCount,
        'now' => $now->toDateString(),
        'six_months_from_now' => $sixMonthsFromNow->toDateString(),
        'one_year_from_now' => $oneYearFromNow->toDateString(),
    ]);
});

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
        Route::post('/warehouse/tracert-items', 'warehouseTracertItems')->name('dashboard.warehouse.tracert-items');
        Route::post('/facility/tracert-items', 'facilityTracertItems')->name('dashboard.facility.tracert-items');
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
            ->name('settings.users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->name('settings.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->name('settings.users.edit');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
            
        // User status routes
        Route::post('/users/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
            
        Route::post('/users/bulk-status', [UserController::class, 'bulkToggleStatus'])
            ->name('users.bulk-status');
    });

    // Logistics Management Routes
    Route::prefix('settings')->group(function () {
        // Logistic Companies
        Route::prefix('logistics')->group(function () {
            Route::get('/companies', [LogisticCompanyController::class, 'index'])->name('settings.logistics.companies.index');
            Route::post('/companies', [LogisticCompanyController::class, 'store'])->name('settings.logistics.companies.store');
            Route::delete('/companies/{company}', [LogisticCompanyController::class, 'destroy'])->name('settings.logistics.companies.destroy');
            Route::put('/companies/{company}/toggle-status', [LogisticCompanyController::class, 'toggleStatus'])->name('settings.logistics.companies.toggle-status');
        });

        // Drivers
        Route::prefix('drivers')->group(function () {
            Route::get('/', [DriverController::class, 'index'])->name('settings.drivers.index');
            Route::post('/', [DriverController::class, 'store'])->name('settings.drivers.store');
            Route::delete('/{driver}', [DriverController::class, 'destroy'])->name('settings.drivers.destroy');
            Route::put('/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('settings.drivers.toggle-status');
        });
    });

    // Role Management Routes
    Route::middleware(PermissionMiddleware::class . ':role.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::get('/reports/orders', [ReportController::class, 'orders'])->name('reports.orders');
        Route::get('/reports/order-tracking', [ReportController::class, 'orderTracking'])->name('reports.order-tracking');
        Route::get('/reports/order-fulfillment', [ReportController::class, 'orderFulfillment'])->name('reports.order-fulfillment');
        Route::get('/reports/transfers', [ReportController::class, 'transfers'])->name('reports.transfers');
        Route::get('/reports/transfer-issued-quantity', [ReportController::class, 'transferIssuedQuantity'])->name('reports.transfer-issued-quantity');
        Route::get('/reports/transfer-received-quantity', [ReportController::class, 'transferReceivedQuantity'])->name('reports.transfer-received-quantity');
        Route::get('/reports/transfer-type', [ReportController::class, 'transferType'])->name('reports.transfer-type');
        Route::get('/reports/transfer-reasons', [ReportController::class, 'transferReasons'])->name('reports.transfer-reasons');
        Route::get('/reports/purchase-orders', [ReportController::class, 'purchaseOrders'])->name('reports.purchase-orders');
        Route::get('/reports/packing-list', [ReportController::class, 'packingList'])->name('reports.packing-list');
        Route::get('/reports/lmis-monthly-report', [ReportController::class, 'lmisMonthlyReport'])->name('reports.lmis-monthly');
        Route::put('/reports/lmis-monthly-report/review', [ReportController::class, 'reviewLmisReport'])->name('reports.lmis-monthly.review');
        Route::put('/reports/lmis-monthly-report/approve', [ReportController::class, 'approveLmisReport'])->name('reports.lmis-monthly.approve');
        Route::put('/reports/lmis-monthly-report/reject', [ReportController::class, 'rejectLmisReport'])->name('reports.lmis-monthly.reject');
        Route::post('/reports/orders/exportToExcel', [ReportController::class, 'exportOrdersToExcel'])->name('reports.orders.exportToExcel');
        Route::get('/reports/order-tracking/export', [\App\Http\Controllers\ReportController::class, 'exportOrderTrackingExcel'])->name('reports.order-tracking.export');

        // monthlyConsumption
        Route::get('/reports/monthly-consumption', [ReportController::class, 'monthlyConsumption'])->name('reports.monthly-consumption');
        
        // Facilities Reports
        Route::get('/reports/facilities-list', [ReportController::class, 'facilitiesListReport'])->name('reports.facilities-list');
        Route::get('/reports/lmis-monthly-consumption', [ReportController::class, 'lmisMonthlyConsumptionReport'])->name('reports.lmis-monthly-consumption');
        Route::get('/reports/facility-compliance', [ReportController::class, 'facilityComplianceReport'])->name('reports.facility-compliance');
        
        // LMIS Report Routes
        Route::get('/reports/facility-lmis-report', [ReportController::class, 'facilityLmisReport'])->name('reports.facility-lmis-report');
        Route::post('/reports/facility-lmis-report/store', [ReportController::class, 'storeFacilityLmisReport'])->name('reports.facility-lmis-report.store');
        Route::post('/reports/facility-lmis-report/submit', [ReportController::class, 'submitFacilityLmisReport'])->name('reports.facility-lmis-report.submit');
        Route::post('/reports/facility-lmis-report/generate-from-movements', [ReportController::class, 'generateFacilityLmisReportFromMovements'])->name('reports.facility-lmis-report.generate-from-movements');
        Route::get('/reports/facility-lmis-report/create', [ReportController::class, 'createFacilityLmisReport'])->name('reports.facility-lmis-report.create');
    });

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

            // delete warehouse
            Route::get('/{id}/toggle-status', 'toggleStatus')->name('inventories.warehouses.toggle-status');
            Route::post('/get-warehouses', 'getWarehousesPluck')->name('warehouses.get-warehouses');

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

        // 'warehouse.locations
        Route::get('/{id}/locations', 'getLocations')->name('warehouse.locations');
    });

    // Dosage Management Routes
    Route::prefix('product/dosages')->group(function () {
            Route::get('/', [DosageController::class, 'index'])->name('products.dosages.index');
            Route::get('/create', [DosageController::class, 'create'])->name('products.dosages.create');
            Route::post('/store', [DosageController::class, 'store'])->name('products.dosages.store');
            Route::get('/{dosage}/edit', [DosageController::class, 'edit'])->name('products.dosages.edit');
        Route::get('/{dosage}/toggle-status', [DosageController::class, 'toggleStatus'])->name('products.dosages.toggle-status');
            Route::delete('/{dosage}', [DosageController::class, 'destroy'])->name('products.dosages.destroy');
    });

    // Product Management Routes
    Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('products.index');
            Route::get('/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/', [ProductController::class, 'store'])->name('products.store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/import-excel', [ProductController::class, 'importExcel'])->name('products.import-excel');
            Route::get('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::get('/import-status/{importId}', [ProductController::class, 'checkImportStatus'])
            ->name('products.import-status');
    });

    // Eligible Items Management Routes
    Route::prefix('eligible-items')->group(function () {
            Route::get('/', [EligibleItemController::class, 'index'])->name('products.eligible.index');
            Route::get('/create', [EligibleItemController::class, 'create'])->name('products.eligible.create');
            Route::post('/store', [EligibleItemController::class, 'store'])->name('products.eligible.store');
            Route::get('/{eligibleItem}/edit', [EligibleItemController::class, 'edit'])->name('products.eligible.edit');
            Route::post('/update', [EligibleItemController::class, 'update'])->name('products.eligible.update');
            Route::get('/{id}/delete', [EligibleItemController::class, 'destroy'])->name('products.eligible.destroy');
            Route::post('/import', [EligibleItemController::class, 'import'])->name('products.eligible.import');
    });

    // UOM Management Routes
    Route::prefix('uom')->group(function () {
            Route::get('/', [\App\Http\Controllers\UomController::class, 'index'])->name('products.uom.index');
            Route::get('/create', [\App\Http\Controllers\UomController::class, 'create'])->name('products.uom.create');
            Route::post('/store', [\App\Http\Controllers\UomController::class, 'store'])->name('products.uom.store');
            Route::get('/{uom}/edit', [\App\Http\Controllers\UomController::class, 'edit'])->name('products.uom.edit');
            Route::put('/{uom}', [\App\Http\Controllers\UomController::class, 'update'])->name('products.uom.update');
            Route::delete('/{uom}', [\App\Http\Controllers\UomController::class, 'destroy'])->name('products.uom.destroy');
        Route::get('/{uom}/toggle-status', [\App\Http\Controllers\UomController::class, 'toggleStatus'])->name('products.uom.toggle-status');
    });

    // Facility Type Management Routes
    Route::prefix('facility-types')->group(function () {
            Route::get('/', [\App\Http\Controllers\FacilityTypeController::class, 'index'])->name('products.facility-types.index');
            Route::get('/create', [\App\Http\Controllers\FacilityTypeController::class, 'create'])->name('products.facility-types.create');
            Route::post('/store', [\App\Http\Controllers\FacilityTypeController::class, 'store'])->name('products.facility-types.store');
            Route::get('/{facilityType}/edit', [\App\Http\Controllers\FacilityTypeController::class, 'edit'])->name('products.facility-types.edit');
            Route::delete('/{facilityType}', [\App\Http\Controllers\FacilityTypeController::class, 'destroy'])->name('products.facility-types.destroy');
        Route::get('/{facilityType}/toggle-status', [\App\Http\Controllers\FacilityTypeController::class, 'toggleStatus'])->name('products.facility-types.toggle-status');
    });

    // Supply Management Routes
    Route::prefix('supplies')->group(function () {
            Route::get('/', [SupplyController::class, 'index'])->name('supplies.index');

        // supplies.deletePO
            Route::get('/{id}/delete', [SupplyController::class, 'destroy'])->name('supplies.deletePO');

        // supplies.uploadDocument
        Route::post('/{id}/upload-document', [SupplyController::class, 'uploadDocument'])->name('supplies.uploadDocument');
            Route::get('/create', [SupplyController::class, 'create'])->name('supplies.create');
            Route::get('/show', [SupplyController::class, 'show'])->name('supplies.show');
            Route::get('/{id}/showPO', [SupplyController::class, 'showPO'])->name('supplies.po-show');
            Route::get('/packing-list/{id}/get-po', [SupplyController::class, 'getPO'])->name('supplies.get-purchaseOrder');
            Route::get('/packing-list/{id}/edit', [SupplyController::class, 'editPK'])->name('supplies.packing-list.edit');
            Route::get('/packing-list/show', [SupplyController::class, 'showPK'])->name('supplies.packing-list.showPK');
            Route::get('/packing-list/create', [SupplyController::class, 'newPackingList'])->name('supplies.packing-list.create');
            Route::get('/packing-list', [SupplyController::class, 'newPackingList'])->name('supplies.packing-list');
            Route::get('/back-orders', [SupplyController::class, 'showBackOrder'])->name('supplies.showBackOrder');
            Route::get('/purchase_orders', [SupplyController::class, 'newPO'])->name('supplies.purchase_order');
            Route::post('/purchase_orders/store', [SupplyController::class, 'storePO'])->name('supplies.storePO');
            Route::get('/purchase_orders/{id}/edit', [SupplyController::class, 'editPO'])->name('supplies.editPO');
            Route::post('/packing-list/store', [SupplyController::class, 'storePK'])->name('supplies.storePK');
        Route::delete('/purchase_orders/documents/{document}', [SupplyController::class, 'deleteDocument'])->name('supplies.deleteDocument');

        // supplies.back-order
        Route::get('/back-order', [SupplyController::class, 'backOrder'])->name('supplies.back-order');

        // supplies.get-packingList
        Route::get('/packing-list/{id}/get-back-order', [SupplyController::class, 'getBackOrder'])->name('supplies.get-back-order');

        // supplies.showBackOrder
        // Route::get('/showBackOrder', [SupplyController::class, 'showBackOrder'])->name('supplies.showBackOrder');
        // edit po - actions
            Route::post('/purchase_orders/{id}/review', [SupplyController::class, 'reviewPO'])->name('supplies.reviewPO');
            Route::post('/purchase_orders/{id}/approve', [SupplyController::class, 'approvePO'])->name('supplies.approvePO');
            Route::post('/purchase_orders/{id}/reject', [SupplyController::class, 'rejectPO'])->name('supplies.rejectPO');
            Route::put('/purchase_orders/{id}/update', [SupplyController::class, 'updatePurchaseOrder'])->name('supplies.updatePO');

            Route::post('/packing-list/review', [SupplyController::class, 'reviewPK'])->name('supplies.reviewPK');
            Route::post('/packing-list/approve', [SupplyController::class, 'approvePK'])->name('supplies.approvePK');
            Route::post('/packing-list/reject', [SupplyController::class, 'rejectPK'])->name('supplies.rejectPK');

            Route::post('/back-order/dispose', [SupplyController::class, 'dispose'])->name('back-order.dispose');
            Route::post('/back-order/receive', [SupplyController::class, 'receive'])->name('back-order.receive');
            Route::post('/store-location', [SupplyController::class, 'storeLocation'])->name('supplies.store-location');
               
    
        Route::post('/store', [SupplyController::class, 'store'])->name('supplies.store');
        Route::get('/{supply}/edit', [SupplyController::class, 'edit'])->name('supplies.edit');
        Route::put('/{supply}', [SupplyController::class, 'update'])->name('supplies.update');
            Route::delete('/{supply}', [SupplyController::class, 'destroy'])->name('supplies.destroy');

        // supplies.packing-list.update
        Route::post('/packing-list/update', [SupplyController::class, 'updatePK'])->name('supplies.packing-list.update');


        // back-order.liquidate
        Route::post('/liquidate', [SupplyController::class, 'liquidate'])->name('back-order.liquidate');

        // back-order.dispose
        Route::post('/dispose', [SupplyController::class, 'dispose'])->name('back-order.dispose');
        Route::get('/packing-list/{id}/show', [SupplyController::class, 'showPackingList'])->name('supplies.packing-list.show');

        // Add new route for packing list document upload
        Route::post('/packing-list/{id}/upload-document', [SupplyController::class, 'uploadPackingListDocument'])
            ->name('supplies.packing-list.uploadDocument');

        Route::get('/back-orders/list', [\App\Http\Controllers\SupplyController::class, 'listBackOrders'])->name('supplies.backOrders.list');
        Route::get('/back-orders/{id}/histories', [\App\Http\Controllers\SupplyController::class, 'getBackOrderHistories'])->name('supplies.backOrders.histories');
        Route::post('/back-orders/{id}/attachments', [\App\Http\Controllers\SupplyController::class, 'uploadBackOrderAttachment'])->name('supplies.backOrders.uploadAttachment');
        Route::delete('/back-orders/{id}/attachments', [\App\Http\Controllers\SupplyController::class, 'deleteBackOrderAttachment'])->name('supplies.backOrders.deleteAttachment');

        // Received Back Order Routes
        Route::get('/received-backorder', [ReceivedBackorderController::class, 'index'])->name('supplies.received-backorder.index');
        Route::get('/received-backorder/create', [ReceivedBackorderController::class, 'create'])->name('supplies.received-backorder.create');
        Route::post('/received-backorder', [ReceivedBackorderController::class, 'store'])->name('supplies.received-backorder.store');
        Route::get('/received-backorder/{receivedBackorder}', [ReceivedBackorderController::class, 'show'])->name('supplies.received-backorder.show');


        Route::delete('/received-backorder/{receivedBackorder}', [ReceivedBackorderController::class, 'destroy'])->name('supplies.received-backorder.destroy');
        
        // Received Back Order Action Routes
        Route::post('/received-backorder/{receivedBackorder}/review', [ReceivedBackorderController::class, 'review'])->name('supplies.received-backorder.review');
        Route::post('/received-backorder/{receivedBackorder}/approve', [ReceivedBackorderController::class, 'approve'])->name('supplies.received-backorder.approve');
        Route::post('/received-backorder/{receivedBackorder}/reject', [ReceivedBackorderController::class, 'reject'])->name('supplies.received-backorder.reject');
        Route::delete('/received-backorder/{receivedBackorder}/attachments', [ReceivedBackorderController::class, 'deleteAttachment'])->name('supplies.received-backorder.deleteAttachment');

    });

    // Liquidate & Disposal Management Routes
    Route::controller(LiquidateDisposalController::class)
        ->name('liquidate-disposal.')
        ->prefix('liquidate-disposal')
        ->group(function(){
            Route::get('/', 'index')->name('index');
            Route::get('/liquidates/{id}', 'showLiquidate')->name('liquidates.show');
            Route::get('/disposals/{id}', 'showDisposal')->name('disposals.show');
            
            // Liquidation action routes
            Route::post('/liquidates/{id}/review', 'reviewLiquidate')->name('liquidates.review');
            Route::post('/liquidates/{id}/approve', 'approveLiquidate')->name('liquidates.approve');
            Route::post('/liquidates/{id}/reject', 'rejectLiquidate')->name('liquidates.reject');
            Route::post('/liquidates/{id}/rollback', 'rollbackLiquidate')->name('liquidates.rollback');
            
            // Disposal action routes
            Route::post('/disposals/{id}/review', 'reviewDisposal')->name('disposals.review');
            Route::post('/disposals/{id}/approve', 'approveDisposal')->name('disposals.approve');
            Route::post('/disposals/{id}/reject', 'rejectDisposal')->name('disposals.reject');
            Route::post('/disposals/{id}/rollback', 'rollbackDisposal')->name('disposals.rollback');
        });

    // Supplier Management Routes
    Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
            // Route::get('/create', [SupplierController::class, 'create'])->name('supplies.suppliers.create');
            Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('supplies.suppliers.edit');
            Route::post('/', [SupplierController::class, 'store'])->name('supplies.suppliers.store');
            Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
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
            Route::get('/create', [SettingsController::class, 'create'])->name('settings.create');
            Route::post('/', [SettingsController::class, 'store'])->name('settings.store');
            Route::get('/{settings}/edit', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/{settings}', [SettingsController::class, 'update'])->name('settings.update');
            Route::delete('/{settings}', [SettingsController::class, 'destroy'])->name('settings.destroy');
    });

    // Reorder Level Management Routes
        Route::group([], function () {
        Route::get('/reorder-levels', [ReorderLevelController::class, 'index'])->name('settings.reorder-levels.index');
        Route::get('/reorder-levels/create', [ReorderLevelController::class, 'create'])
            ->name('settings.reorder-levels.create');
        Route::post('/reorder-levels', [ReorderLevelController::class, 'store'])
            ->name('settings.reorder-levels.store');
        Route::get('/reorder-levels/{reorderLevel}/edit', [ReorderLevelController::class, 'edit'])
            ->name('settings.reorder-levels.edit');
        Route::put('/reorder-levels/{reorderLevel}', [ReorderLevelController::class, 'update'])
            ->name('settings.reorder-levels.update');
        Route::delete('/reorder-levels/{reorderLevel}', [ReorderLevelController::class, 'destroy'])
            ->name('settings.reorder-levels.destroy');
        
        // Import routes
        Route::post('/reorder-levels/import', [ReorderLevelController::class, 'importExcel'])
            ->name('settings.reorder-levels.import');
        Route::get('/reorder-levels/import/format', [ReorderLevelController::class, 'getImportFormat'])
            ->name('settings.reorder-levels.import.format');
    });

    // Order Management Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/{id}/show', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/change-status', [OrderController::class, 'changeStatus'])->name('orders.change-status');
        Route::post('/reject', [OrderController::class, 'rejectOrder']);

        // restore order
        Route::post('/restore-order', [OrderController::class, 'restoreOrder'])->name('orders.restore-order');

        Route::get('/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // dispatch info
        Route::post('/dispatch-info', [OrderController::class, 'dispatchInfo'])->name('orders.dispatch-info');
        
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

        // get inventory for transfer
        Route::post('/inventory', [TransferController::class, 'getSourceInventoryDetail'])->name('transfers.inventory');
                
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
        // destroyItem route removed

        // update transfer item quantity
        Route::post('/update-item', [TransferController::class, 'updateItem'])->name('transfers.update-item');

        // transfer back order
        Route::get('/back-order', [TransferController::class, 'transferBackOrder'])->name('transfers.back-order');

        // transfer liquidate
        Route::post('/liquidate', [TransferController::class, 'transferLiquidate'])->name('transfers.liquidate');

        // transfer dispose
        Route::post('/dispose', [TransferController::class, 'transferDispose'])->name('transfers.dispose');


         // transfer update-quantity
         Route::post('/update-quantity', [TransferController::class, 'updateQuantity'])->name('transfers.update-quantity');

         // save transfer back orders
         Route::post('/save-back-orders', [TransferController::class, 'saveBackOrders'])->name('transfers.save-back-orders');
         
         // delete transfer back order
         // deleteBackOrder route removed
          // change transfer status
          Route::post('/change-status', [TransferController::class, 'changeStatus'])->name('transfers.change-status');
          
          // restore transfer
          Route::post('/restore-transfer', [TransferController::class, 'restoreTransfer'])->name('transfers.restore-transfer');

          Route::post('/dispatch-info', [TransferController::class, 'dispatchInfo'])->name('transfers.dispatch-info');

            // mark transfer as delivered
            Route::post('/mark-delivered', [TransferController::class, 'markDelivered'])->name('transfers.mark-delivered');

            // Add routes for drivers and logistics companies
            Route::get('/get-drivers', [TransferController::class, 'getDrivers'])->name('transfers.get-drivers');
            Route::get('/get-logistic-companies', [TransferController::class, 'getLogisticCompanies'])->name('transfers.get-logistic-companies');
            Route::post('/add-driver', [TransferController::class, 'addDriver'])->name('transfers.add-driver');

            // receivedQuantity
            Route::post('/update-received-quantity', [TransferController::class, 'receivedQuantity'])->name('transfers.receivedQuantity');
    });

    // Purchase Order Management Routes
    Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
            Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
            Route::get('/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
            Route::put('/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
            Route::delete('/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        
        // Import routes
            Route::post('/import', [PurchaseOrderController::class, 'importItems'])->name('purchase-orders.import');
        Route::get('/import/progress', [PurchaseOrderController::class, 'getImportProgress'])->name('purchase-orders.import.progress');
    });

    // Dispatch Management Routes
    Route::prefix('dispatches')->group(function () {
            Route::get('/', [DispatchController::class, 'index'])->name('dispatches.index');
            Route::get('/create', [DispatchController::class, 'create'])->name('dispatches.create');
            Route::post('/', [DispatchController::class, 'store'])->name('dispatches.store');
            Route::get('/{dispatch}/edit', [DispatchController::class, 'edit'])->name('dispatches.edit');
            Route::put('/{dispatch}', [DispatchController::class, 'update'])->name('dispatches.update');
            Route::delete('/{dispatch}', [DispatchController::class, 'destroy'])->name('dispatches.destroy');
    });

    // Facility Management Routes
    Route::prefix('facilities')->group(function () {
        Route::get('/', [FacilityController::class, 'index'])->name('facilities.index');
        Route::get('/{id}/show', [FacilityController::class, 'show'])->name('facilities.show');
        Route::get('/create', [FacilityController::class, 'create'])->name('facilities.create');
        Route::post('/import', [FacilityController::class, 'import'])->name('facilities.import');
        Route::get('/download-template', [FacilityController::class, 'downloadTemplate'])->name('facilities.download-template');
        Route::post('/store', [FacilityController::class, 'store'])->name('facilities.store');
        Route::get('/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
        Route::delete('/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');
        Route::get('/{facility}/toggle-status', [FacilityController::class, 'toggleStatus'])->name('facilities.toggle-status');

        // tabs
        Route::get('/{facility}/inventory', [FacilityController::class, 'inventory'])->name('facilities.inventory');
        Route::get('/{facility}/dispence', [FacilityController::class, 'dispence'])->name('facilities.dispence');
        Route::get('/{facility}/expiry', [FacilityController::class, 'expiry'])->name('facilities.expiry');

        // get facilities
        Route::post('/get-facilities', [FacilityController::class, 'getFacilities'])->name('facilities.get-facilities');
    });

    // District Management Routes
    Route::prefix('districts')->group(function () {
            Route::get('/', [DistrictController::class, 'index'])->name('districts.index');
            Route::get('/create', [DistrictController::class, 'create'])->name('districts.create');
            Route::post('/', [DistrictController::class, 'store'])->name('districts.store');
            Route::get('/{district}/edit', [DistrictController::class, 'edit'])->name('districts.edit');
            Route::put('/{district}', [DistrictController::class, 'update'])->name('districts.update');
            Route::delete('/{district}', [DistrictController::class, 'destroy'])->name('districts.destroy');

        // get district by region
        Route::post('/get-districts', [DistrictController::class, 'getDistricts'])->name('districts.get-districts');
        Route::post('/store', [DistrictController::class, 'store'])->name('districts.store');
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
            Route::post('/assets/retire', 'retireAsset')->name('assets.retire');
            Route::post('/assets/import', 'import')->name('assets.import');

            // upload assets excel file 
            Route::post('/assets/import', 'import')->name('assets.import');

            // export
            Route::get('/assets/export', function(\Illuminate\Http\Request $request){
                if (!auth()->user()->can('asset-export')) { abort(403); }
                return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AssetsExport($request->all()), 'assets.xlsx');
            })->name('assets.export');

            // Asset Document Upload Route
            Route::post('/assets/documents/store', 'storeDocument')->name('assets.documents.store');

            // Asset Document Delete Route
            Route::get('/documents/{document}', 'deleteDocument')->name('assets.document.delete');

            // Asset Approval Routes
            Route::get('/approvals', 'approvalsIndex')->name('assets.approvals.index');
            Route::post('/{asset}/submit-approval', 'submitForApproval')->name('assets.submit-approval');
            Route::post('/{asset}/approve', 'approveAsset')->name('assets.approve');
            Route::post('/{asset}/approve-transfer', 'approveTransfer')->name('assets.approve-transfer');
            Route::post('/{asset}/approve-retirement', 'approveRetirement')->name('assets.approve-retirement');
            Route::get('/pending-approvals', 'getPendingApprovals')->name('assets.pending-approvals');
            Route::get('/{asset}/approval-history', 'getApprovalHistory')->name('assets.approval-history');
            Route::get('/{asset}/history', 'getAssetHistory')->name('assets.history');
            Route::get('/history', 'getAllAssetHistory')->name('assets.history.index');
            Route::get('/{asset}/debug-approval', 'debugApprovalWorkflow')->name('assets.debug-approval');
        });

    // Assignees API (minimal for inline creation)
    Route::post('assets-management/assignees', [\App\Http\Controllers\AssigneeController::class, 'store'])->name('assets.assignees.store');

    // Asset Types
    Route::prefix('assets-management/types')->controller(\App\Http\Controllers\AssetTypeController::class)->group(function(){
        Route::get('/', 'index')->name('assets.types.index');
        Route::post('/', 'store')->name('assets.types.store');
        Route::put('/{assetType}', 'update')->name('assets.types.update');
        Route::delete('/{assetType}', 'destroy')->name('assets.types.destroy');
    });

    // Inventory Management Routes
    Route::prefix('inventory')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('inventories.index');
            Route::get('/create', [InventoryController::class, 'create'])->name('inventories.create');
            Route::post('/', [InventoryController::class, 'store'])->name('inventories.store');
            Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventories.edit');
            Route::put('/{inventory}', [InventoryController::class, 'update'])->name('inventories.update');
            Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('inventories.destroy');
            Route::patch('/update-location', [InventoryController::class, 'updateLocation'])->name('inventories.update-location');
        Route::get('/get-locations', [InventoryController::class, 'getLocations'])->name('inventories.getLocations');
        Route::get('/get-all-locations', [InventoryController::class, 'getAllLocations'])->name('inventories.getAllLocations');
            Route::post('/import', [InventoryController::class, 'import'])->name('inventories.import');
    });

    // API Routes
    Route::prefix('api')->group(function () {
        // Issue Quantity Reports Export Routes
            Route::get('/reports/issueQuantityReports/export', [ReportController::class, 'exportIssueQuantityReports']);
        // Removed the individual report items export endpoint as it's now handled client-side
            Route::get('/reports/inventory-report/data', [ReportController::class, 'inventoryReportData'])->name('reports.inventoryReport.data');
        // Warehouse API endpoint for dropdowns
        Route::get('/warehouses', [\App\Http\Controllers\WarehouseController::class, 'getWarehouses'])->name('api.warehouses');
    });

    // Report Routes
    Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/facilities/monthly-consumption', [ReportController::class, 'monthlyConsumption'])->name('reports.monthlyConsumption');
            Route::get('/template-products', [ReportController::class, 'getTemplateProducts'])->name('reports.template-products');
            Route::get('/receivedQuantities', [ReportController::class, 'receivedQuantities'])->name('reports.receivedQuantities');
            Route::get('/physicalCount', [ReportController::class, 'physicalCountReport'])->name('reports.physicalCount');
        
        // Inventory Adjustment Routes
            Route::post('/createAdjustments', [ReportController::class, 'createAdjustments'])->name('reports.createAdjustments');
            Route::post('/adjustments/{id}/review', [ReportController::class, 'reviewAdjustment'])->name('reports.reviewAdjustment');
            Route::post('/adjustments/{id}/approve', [ReportController::class, 'approveAdjustment'])->name('reports.approveAdjustment');
            Route::post('/adjustments/{id}/reject', [ReportController::class, 'rejectAdjustment'])->name('reports.rejectAdjustment');
        
        // Excel Upload Route
            Route::post('/upload-consumption', [ConsumptionUploadController::class, 'upload'])->name('reports.upload-consumption');

        // Issue Quantity Routes
            Route::get('/issueQuantityReports', [ReportController::class, 'issueQuantityReports'])->name('reports.issueQuantityReports');

        // Inventory Routes
            Route::get('/inventory-report', [ReportController::class, 'inventoryReport'])->name('reports.inventoryReport');
        // Route::post('/inventory-report/generate', [ReportController::class, 'generateInventoryReport'])->middleware(PermissionMiddleware::class . ':report.generate')->name('reports.generateInventoryReport');

        // Physical count report routes
            Route::post('/physical-count/generate', [ReportController::class, 'generatePhysicalCountReport'])->name('reports.physicalCountReport');

        // reports.physical-count.update
            Route::post('/physical-count/update', [ReportController::class, 'updatePhysicalCountReport'])->name('reports.physical-count.update');
        // reports.physical-count.status
            Route::post('/physical-count/status', [ReportController::class, 'updatePhysicalCountStatus'])->name('reports.physical-count.status');
        // reports.physical-count.approve
            Route::post('/physical-count/approve', [ReportController::class, 'approvePhysicalCountReport'])->name('reports.physical-count.approve');
        // reports.physical-count.reject
            Route::post('/physical-count/reject', [ReportController::class, 'rejectPhysicalCountReport'])->name('reports.physical-count.reject');
        // reports.physical-count.rollback
            Route::post('/physical-count/rollback', [ReportController::class, 'rollBackRejectPhysicalCountReport'])->name('reports.physical-count.rollback');

        // reports.physicalCountShow
        Route::get('/physical-count-show', [ReportController::class, 'physicalCountShow'])->name('reports.physicalCountShow');

        // reports.disposals
        Route::get('/disposals', [ReportController::class, 'disposals'])->name('reports.disposals');

        // reports.issue-quantity
        Route::post('/issue-quantity/upload', [ReportController::class, 'importIssueQuantity'])->name('reports.issue-quantity.upload');
        
        // Warehouse monthly inventory report
            Route::get('/warehouse-monthly', [ReportController::class, 'warehouseMonthlyReport'])->name('reports.warehouseMonthly');
        Route::put('/warehouse-monthly/adjustments', [ReportController::class, 'updateInventoryReportAdjustments'])->name('reports.warehouseMonthly.updateInventoryReportAdjustments');
            Route::put('/warehouse-monthly/submit', [ReportController::class, 'submitInventoryReport'])->name('reports.warehouseMonthly.submit');
            Route::put('/warehouse-monthly/review', [ReportController::class, 'reviewInventoryReport'])->name('reports.warehouseMonthly.review');
            Route::put('/warehouse-monthly/approve', [ReportController::class, 'approveInventoryReport'])->name('reports.warehouseMonthly.approve');
            Route::put('/warehouse-monthly/reject', [ReportController::class, 'rejectInventoryReport'])->name('reports.warehouseMonthly.reject');
            Route::post('/warehouse-monthly/export-to-excel', [ReportController::class, 'exportToExcel'])->name('reports.warehouseMonthly.exportToExcel');

        // Product Reports Routes
            Route::get('/products/active-inactive', [ReportController::class, 'activeInactiveProducts'])->name('reports.products.active-inactive');
            Route::get('/products/eligibility', [ReportController::class, 'productEligibility'])->name('reports.products.eligibility');
            Route::get('/products/categories', [ReportController::class, 'productCategories'])->name('reports.products.categories');
            Route::get('/products/dosage-forms', [ReportController::class, 'productDosageForms'])->name('reports.products.dosage-forms');
            Route::get('/products/expiry-tracking', [ReportController::class, 'productExpiryTracking'])->name('reports.products.expiry-tracking');
        
        // Liquidation & Disposal Reports
            Route::get('/liquidation-disposal/liquidation', [ReportController::class, 'liquidationReport'])->name('reports.liquidation-disposal.liquidation');
            Route::get('/liquidation-disposal/disposal', [ReportController::class, 'disposalReport'])->name('reports.liquidation-disposal.disposal');
        
        // Procurement Reports
            Route::get('/procurement/purchase-orders', [ReportController::class, 'purchaseOrdersReport'])->name('reports.procurement.purchase-orders');
            Route::get('/procurement/packing-list', [ReportController::class, 'packingListReport'])->name('reports.procurement.packing-list');
            Route::get('/procurement/backorder', [ReportController::class, 'backorderReport'])->name('reports.procurement.backorder');
            Route::get('/procurement/lead-time-analysis', [ReportController::class, 'leadTimeAnalysisReport'])->name('reports.procurement.lead-time-analysis');
            Route::get('/procurement/demand-forecasting', [ReportController::class, 'demandForecastingReport'])->name('reports.procurement.demand-forecasting');
    });

    // Approval Routes
    // Reason Management Routes
    Route::controller(ReasonController::class)->prefix('reasons')->group(function () {
        Route::get('/', 'index')->name('reasons.index');
        Route::post('/store', 'store')->name('reasons.store');
        Route::delete('/destroy', 'destroy')->name('reasons.destroy');
        Route::get('/get-reasons', 'getReasons')->name('reasons.get-reasons');
    });
        
    });
    
    // Approval Routes
    Route::middleware(['auth', \App\Http\Middleware\TwoFactorAuth::class])->prefix('approvals')->group(function () {
        Route::get('/', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });


require __DIR__.'/auth.php';
