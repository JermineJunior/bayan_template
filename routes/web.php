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
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuelLogController;
use App\Http\Controllers\OdometerController;
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
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('preferences', [PreferencesController::class, 'edit'])->name('preferences.edit');
    Route::put('preferences', [PreferencesController::class, 'update'])->name('preferences.update');
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
});
