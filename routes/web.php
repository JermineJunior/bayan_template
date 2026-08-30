<?php

use App\Http\Controllers\Account\PreferencesController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BasicData\DepartmentController;
use App\Http\Controllers\BasicData\DriverController;
use App\Http\Controllers\BasicData\ManagementController;
use App\Http\Controllers\BasicData\VehicleController;
use App\Http\Controllers\BasicData\VehicleDriverController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverViolationController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\FuelLogController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\InsurancePolicyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OdometerController;
use App\Http\Controllers\OilController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\SparePartIssueController;
use App\Http\Controllers\SparePartPurchaseController;
use App\Http\Controllers\SparePartStocktakeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierInvoiceController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\VehicleFilterChangeController;
use App\Http\Controllers\VehicleOilChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('home');

Route::middleware('auth')->group(function () {
    // Read-only pages share the roles.view permission.
    Route::middleware('can:roles.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::get('roles/{role}', [RoleController::class, 'edit'])->name('roles.edit');
    });
    //
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('can:roles.create')
        ->name('roles.store');

    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('can:roles.edit')
        ->name('roles.update');

    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('can:roles.delete')
        ->name('roles.destroy');

    // Read-only pages share the users.view permission.
    Route::middleware('can:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('users/{user}', [UserController::class, 'edit'])->name('users.edit');
    });

    Route::post('users', [UserController::class, 'store'])
        ->middleware('can:users.create')
        ->name('users.store');

    Route::put('users/{user}', [UserController::class, 'update'])
        ->middleware('can:users.edit')
        ->name('users.update');

    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->middleware('can:users.edit')
        ->name('users.reset-password');

    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])
        ->middleware('can:users.edit')
        ->name('users.deactivate');

    Route::post('users/{user}/activate', [UserController::class, 'activate'])
        ->middleware('can:users.edit')
        ->name('users.activate');

    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('can:users.delete')
        ->name('users.destroy');

    // The settings screen is a single permission: edit grants access to both
    // viewing the form and saving changes.
    Route::middleware('can:settings.edit')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

// Account preferences: available to any authenticated user (no permission).
Route::middleware('auth')
    ->prefix('account')
    ->name('account.')
    ->group(function () {
        Route::get('preferences', [PreferencesController::class, 'edit'])->name('preferences.edit');
        Route::put('preferences', [PreferencesController::class, 'update'])->name('preferences.update');
    });

// Notifications: available to any authenticated user (no permission).
Route::middleware('auth')
    ->group(function () {
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    });

// Basic data (managements / departments / drivers / vehicles).
Route::middleware('auth')->group(function () {
    // Read-only pages share the module's .view permission.
    Route::middleware('can:managements.view')->group(function () {
        Route::get('managements', [ManagementController::class, 'index'])->name('managements.index');
        Route::get('managements/create', [ManagementController::class, 'create'])->name('managements.create');
        Route::get('managements/{management}', [ManagementController::class, 'edit'])->name('managements.edit');
    });

    Route::middleware('can:departments.view')->group(function () {
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::get('departments/{department}', [DepartmentController::class, 'edit'])->name('departments.edit');
    });

    Route::middleware('can:drivers.view')->group(function () {
        Route::get('drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('drivers/create', [DriverController::class, 'create'])->name('drivers.create');
        Route::get('drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
        Route::get('drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
    });

    Route::middleware('can:vehicles.view')->group(function () {
        Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    });

    Route::post('managements', [ManagementController::class, 'store'])
        ->middleware('can:managements.create')
        ->name('managements.store');

    Route::put('managements/{management}', [ManagementController::class, 'update'])
        ->middleware('can:managements.edit')
        ->name('managements.update');

    Route::delete('managements/{management}', [ManagementController::class, 'destroy'])
        ->middleware('can:managements.delete')
        ->name('managements.destroy');

    Route::post('departments', [DepartmentController::class, 'store'])
        ->middleware('can:departments.create')
        ->name('departments.store');

    Route::put('departments/{department}', [DepartmentController::class, 'update'])
        ->middleware('can:departments.edit')
        ->name('departments.update');

    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
        ->middleware('can:departments.delete')
        ->name('departments.destroy');

    Route::post('drivers', [DriverController::class, 'store'])
        ->middleware('can:drivers.create')
        ->name('drivers.store');

    Route::put('drivers/{driver}', [DriverController::class, 'update'])
        ->middleware('can:drivers.edit')
        ->name('drivers.update');

    Route::delete('drivers/{driver}', [DriverController::class, 'destroy'])
        ->middleware('can:drivers.delete')
        ->name('drivers.destroy');

    Route::post('vehicles', [VehicleController::class, 'store'])
        ->middleware('can:vehicles.create')
        ->name('vehicles.store');

    Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])
        ->middleware('can:vehicles.edit')
        ->name('vehicles.update');

    Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])
        ->middleware('can:vehicles.delete')
        ->name('vehicles.destroy');
    // assigning drivers to vehicles is a separate permission, since it is a different model (VehicleDriver) and has its own rules.
    Route::post('vehicles/{vehicle}/assign-driver', [VehicleDriverController::class, 'store'])
        ->middleware('can:vehicles.assign')
        ->name('vehicles.assign-driver');

    Route::delete('assignments/{assignment}', [VehicleDriverController::class, 'destroy'])
        ->middleware('can:vehicles.end-assignment')
        ->name('assignments.destroy');

    // recording an odometer reading reuses the vehicle-edit gate: anyone who
    // manages a vehicle's data may log a normal reading. Corrections are
    // further gated by the odometer.correct permission inside OdometerService.
    Route::post('vehicles/{vehicle}/odometer-readings', [OdometerController::class, 'store'])
        ->middleware('can:vehicles.edit')
        ->name('vehicles.odometer.store');

    // Fuel logs: fuel entries are always created from a vehicle's context.
    Route::middleware('can:fuel.view')->group(function () {
        Route::get('fuel-logs', [FuelLogController::class, 'index'])->name('fuel-logs.index');
        Route::get('vehicles/{vehicle}/fuel-logs/create', [FuelLogController::class, 'create'])->name('vehicles.fuel-logs.create');
    });

    Route::post('vehicles/{vehicle}/fuel-logs', [FuelLogController::class, 'store'])
        ->middleware('can:fuel.create')
        ->name('vehicles.fuel-logs.store');

    Route::delete('fuel-logs/{fuelLog}', [FuelLogController::class, 'destroy'])
        ->middleware('can:fuel.delete')
        ->name('fuel-logs.destroy');

    // Combined oils + filters catalog page.
    Route::get('catalogs', [CatalogController::class, 'index'])
        ->name('catalog.index');

    // Oil catalog — route names mirror Route::resource('oils', OilController::class).
    Route::middleware('can:oils.view')->group(function () {
        Route::get('oils', [OilController::class, 'index'])->name('oils.index');
        Route::get('oils/create', [OilController::class, 'create'])->name('oils.create');
        Route::get('oils/{oil}/edit', [OilController::class, 'edit'])->name('oils.edit');
    });

    Route::post('oils', [OilController::class, 'store'])
        ->middleware('can:oils.create')
        ->name('oils.store');

    Route::put('oils/{oil}', [OilController::class, 'update'])
        ->middleware('can:oils.edit')
        ->name('oils.update');

    Route::delete('oils/{oil}', [OilController::class, 'destroy'])
        ->middleware('can:oils.delete')
        ->name('oils.destroy');

    // Oil changes: always logged from a vehicle's context.
    Route::get('vehicles/{vehicle}/oil-changes/create', [VehicleOilChangeController::class, 'create'])
        ->middleware('can:oil-changes.view')
        ->name('vehicles.oil-changes.create');

    Route::post('vehicles/{vehicle}/oil-changes', [VehicleOilChangeController::class, 'store'])
        ->middleware('can:oil-changes.create')
        ->name('vehicles.oil-changes.store');

    // Filter catalog — route names mirror Route::resource('filters', FilterController::class).
    Route::middleware('can:filters.view')->group(function () {
        Route::get('filters', [FilterController::class, 'index'])->name('filters.index');
        Route::get('filters/create', [FilterController::class, 'create'])->name('filters.create');
        Route::get('filters/{filter}/edit', [FilterController::class, 'edit'])->name('filters.edit');
    });

    Route::post('filters', [FilterController::class, 'store'])
        ->middleware('can:filters.create')
        ->name('filters.store');

    Route::put('filters/{filter}', [FilterController::class, 'update'])
        ->middleware('can:filters.edit')
        ->name('filters.update');

    Route::delete('filters/{filter}', [FilterController::class, 'destroy'])
        ->middleware('can:filters.delete')
        ->name('filters.destroy');

    // Filter changes: always logged from a vehicle's context.
    Route::get('vehicles/{vehicle}/filter-changes/create', [VehicleFilterChangeController::class, 'create'])
        ->middleware('can:filter-changes.view')
        ->name('vehicles.filter-changes.create');

    Route::post('vehicles/{vehicle}/filter-changes', [VehicleFilterChangeController::class, 'store'])
        ->middleware('can:filter-changes.create')
        ->name('vehicles.filter-changes.store');

    // Insurance policies: always logged from a vehicle's context.
    Route::get('vehicles/{vehicle}/insurance-policies/create', [InsurancePolicyController::class, 'create'])
        ->middleware('can:insurance-policies.view')
        ->name('vehicles.insurance-policies.create');

    Route::post('vehicles/{vehicle}/insurance-policies', [InsurancePolicyController::class, 'store'])
        ->middleware('can:insurance-policies.create')
        ->name('vehicles.insurance-policies.store');

    // Violations: always logged from a driver's context.
    Route::get('drivers/{driver}/violations/create', [DriverViolationController::class, 'create'])
        ->middleware('can:violations.view')
        ->name('drivers.violations.create');

    Route::post('drivers/{driver}/violations', [DriverViolationController::class, 'store'])
        ->middleware('can:violations.create')
        ->name('drivers.violations.store');

    Route::get('drivers/{driver}/violations/{violation}/edit', [DriverViolationController::class, 'edit'])
        ->middleware('can:violations.edit')
        ->name('drivers.violations.edit');

    Route::put('drivers/{driver}/violations/{violation}', [DriverViolationController::class, 'update'])
        ->middleware('can:violations.edit')
        ->name('drivers.violations.update');

    Route::delete('violations/{violation}', [DriverViolationController::class, 'destroy'])
        ->middleware('can:violations.delete')
        ->name('violations.destroy');

    Route::get('incidents', [IncidentController::class, 'index'])
        ->middleware('can:incidents.view')
        ->name('incidents.index');

    Route::get('vehicles/{vehicle}/incidents/create', [IncidentController::class, 'create'])
        ->middleware('can:incidents.create')
        ->name('vehicles.incidents.create');

    Route::post('vehicles/{vehicle}/incidents', [IncidentController::class, 'store'])
        ->middleware('can:incidents.create')
        ->name('vehicles.incidents.store');

    Route::get('incidents/{incident}', [IncidentController::class, 'show'])
        ->middleware('can:incidents.view')
        ->name('incidents.show');

    Route::patch('incidents/{incident}/claim-status', [IncidentController::class, 'updateClaimStatus'])
        ->middleware('can:incidents.edit')
        ->name('incidents.update-claim-status');

    Route::delete('incidents/{incident}', [IncidentController::class, 'destroy'])
        ->middleware('can:incidents.delete')
        ->name('incidents.destroy');

    // Expenses: browsing + manual entry. Auto-generated rows (fuel/oil/filter/
    // maintenance) can only be removed by deleting their source record.
    Route::middleware('can:expenses.view')->group(function () {
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    });

    Route::post('expenses', [ExpenseController::class, 'store'])
        ->middleware('can:expenses.create')
        ->name('expenses.store');

    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('can:expenses.delete')
        ->name('expenses.destroy');

    // Read-only pages share the maintenance .view permission.
    Route::middleware(['auth', 'can:maintenance.view'])->group(function () {
        Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
        Route::get('maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
        Route::get('maintenance/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
    });

    Route::middleware('auth')->group(function () {
        Route::post('maintenance', [MaintenanceController::class, 'store'])
            ->middleware('can:maintenance.create')
            ->name('maintenance.store');

        Route::put('maintenance/{maintenance}', [MaintenanceController::class, 'update'])
            ->middleware('can:maintenance.edit')
            ->name('maintenance.update');

        Route::patch('maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])
            ->middleware('can:maintenance.edit')
            ->name('maintenance.update-status');

        Route::delete('maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])
            ->middleware('can:maintenance.delete')
            ->name('maintenance.destroy');

        Route::get('/maintenance/vehicle/{vehicle}/odometer', [MaintenanceController::class, 'getOdometer'])->name('maintenance.vehicle.odometer');

        Route::get('invoice/create/{maintenance}', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::post('invoice/{maintenance}', [InvoiceController::class, 'store'])->name('invoice.store');
        Route::get('invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
        Route::get('invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
        Route::Put('invoice/{invoice}', [InvoiceController::class, 'update'])->name('invoice.update');
        Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    });
});

// Reports: a hub page plus three routes per report (form / results / print).
// Each report is gated by its source module's .view permission.
Route::middleware('auth')
    ->prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::middleware('can:fuel.view')->group(function () {
            Route::get('fuel-consumption', [ReportController::class, 'fuelConsumptionForm'])->name('fuel-consumption.form');
            Route::get('fuel-consumption/results', [ReportController::class, 'fuelConsumptionResults'])->name('fuel-consumption.results');
            Route::get('fuel-consumption/print', [ReportController::class, 'fuelConsumptionPrint'])->name('fuel-consumption.print');
        });

        Route::middleware('can:vehicles.view')->group(function () {
            Route::get('fleet-overview', [ReportController::class, 'fleetOverviewForm'])->name('fleet-overview.form');
            Route::get('fleet-overview/results', [ReportController::class, 'fleetOverviewResults'])->name('fleet-overview.results');
            Route::get('fleet-overview/print', [ReportController::class, 'fleetOverviewPrint'])->name('fleet-overview.print');
        });

        Route::middleware('can:oil-changes.view')->group(function () {
            Route::get('oil-filter-changes', [ReportController::class, 'oilFilterChangesForm'])->name('oil-filter-changes.form');
            Route::get('oil-filter-changes/results', [ReportController::class, 'oilFilterChangesResults'])->name('oil-filter-changes.results');
            Route::get('oil-filter-changes/print', [ReportController::class, 'oilFilterChangesPrint'])->name('oil-filter-changes.print');
        });

        Route::middleware('can:insurance-policies.view')->group(function () {
            Route::get('insurance-status', [ReportController::class, 'insuranceStatusForm'])->name('insurance-status.form');
            Route::get('insurance-status/results', [ReportController::class, 'insuranceStatusResults'])->name('insurance-status.results');
            Route::get('insurance-status/print', [ReportController::class, 'insuranceStatusPrint'])->name('insurance-status.print');
        });

        Route::middleware('can:incidents.view')->group(function () {
            Route::get('incidents-log', [ReportController::class, 'incidentsLogForm'])->name('incidents-log.form');
            Route::get('incidents-log/results', [ReportController::class, 'incidentsLogResults'])->name('incidents-log.results');
            Route::get('incidents-log/print', [ReportController::class, 'incidentsLogPrint'])->name('incidents-log.print');
        });

        Route::middleware('can:expenses.view')->group(function () {
            Route::get('expenses', [ReportController::class, 'expensesForm'])->name('expenses.form');
            Route::get('expenses/results', [ReportController::class, 'expensesResults'])->name('expenses.results');
            Route::get('expenses/print', [ReportController::class, 'expensesPrint'])->name('expenses.print');
        });

        Route::middleware('can:violations.view')->group(function () {
            Route::get('driver-violations', [ReportController::class, 'driverViolationsForm'])->name('driver-violations.form');
            Route::get('driver-violations/results', [ReportController::class, 'driverViolationsResults'])->name('driver-violations.results');
            Route::get('driver-violations/print', [ReportController::class, 'driverViolationsPrint'])->name('driver-violations.print');
        });

        Route::middleware('can:maintenance.view')->group(function () {
            Route::get('maintenance-cost', [ReportController::class, 'maintenanceCostForm'])->name('maintenance-cost.form');
            Route::get('maintenance-cost/results', [ReportController::class, 'maintenanceCostResults'])->name('maintenance-cost.results');
            Route::get('maintenance-cost/print', [ReportController::class, 'maintenanceCostPrint'])->name('maintenance-cost.print');
        });
    });

// Suppliers: standard CRUD plus invoice/payment sub-routes scoped under a
// supplier and an invoice respectively.
Route::middleware('auth')->group(function () {
    Route::resource('suppliers', SupplierController::class);

    // Invoices are created from a supplier's context.
    Route::get('suppliers/{supplier}/invoices/create', [SupplierInvoiceController::class, 'create'])
        ->middleware('can:suppliers.create')
        ->name('suppliers.invoices.create');

    Route::post('suppliers/{supplier}/invoices', [SupplierInvoiceController::class, 'store'])
        ->middleware('can:suppliers.create')
        ->name('suppliers.invoices.store');

    Route::get('supplier-invoices/{invoice}', [SupplierInvoiceController::class, 'show'])
        ->middleware('can:suppliers.view')
        ->name('supplier-invoices.show');

    // Payments are created under an invoice.
    Route::post('supplier-invoices/{invoice}/payments', [SupplierPaymentController::class, 'store'])
        ->middleware('can:suppliers.view')
        ->name('supplier-invoices.payments.store');
});

// Spare parts: catalog CRUD plus purchase / issue / stocktake logging, each
// scoped under the spare part it mutates.
Route::middleware('auth')->group(function () {
    Route::resource('spare-parts', SparePartController::class);

    // Purchase / issue / stocktake are all stock mutations — they reuse the
    // spare-parts.create gate, matching how the suppliers invoice flow works.
    Route::middleware('can:spare-parts.create')->group(function () {
        Route::get('spare-parts/{sparePart}/purchase', [SparePartPurchaseController::class, 'create'])
            ->name('spare-parts.purchase.create');
        Route::post('spare-parts/{sparePart}/purchase', [SparePartPurchaseController::class, 'store'])
            ->name('spare-parts.purchase.store');

        Route::get('spare-parts/{sparePart}/issue', [SparePartIssueController::class, 'create'])
            ->name('spare-parts.issue.create');
        Route::post('spare-parts/{sparePart}/issue', [SparePartIssueController::class, 'store'])
            ->name('spare-parts.issue.store');

        Route::get('spare-parts/{sparePart}/stocktake', [SparePartStocktakeController::class, 'create'])
            ->name('spare-parts.stocktake.create');
        Route::post('spare-parts/{sparePart}/stocktake', [SparePartStocktakeController::class, 'store'])
            ->name('spare-parts.stocktake.store');
    });
});
