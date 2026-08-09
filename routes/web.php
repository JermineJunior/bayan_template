<?php

use App\Http\Controllers\Account\PreferencesController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BasicData\DepartmentController;
use App\Http\Controllers\BasicData\ManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth'])->name('home');

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

// Basic data (managements / departments).
Route::middleware('auth')->group(function () {
    // Read-only pages share the basic-data.view permission.
    Route::middleware('can:basic-data.view')->group(function () {
        Route::get('managements', [ManagementController::class, 'index'])->name('managements.index');
        Route::get('managements/create', [ManagementController::class, 'create'])->name('managements.create');
        Route::get('managements/{management}', [ManagementController::class, 'edit'])->name('managements.edit');

        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::get('departments/{department}', [DepartmentController::class, 'edit'])->name('departments.edit');
    });

    Route::post('managements', [ManagementController::class, 'store'])
        ->middleware('can:basic-data.create')
        ->name('managements.store');

    Route::put('managements/{management}', [ManagementController::class, 'update'])
        ->middleware('can:basic-data.edit')
        ->name('managements.update');

    Route::delete('managements/{management}', [ManagementController::class, 'destroy'])
        ->middleware('can:basic-data.delete')
        ->name('managements.destroy');

    Route::post('departments', [DepartmentController::class, 'store'])
        ->middleware('can:basic-data.create')
        ->name('departments.store');

    Route::put('departments/{department}', [DepartmentController::class, 'update'])
        ->middleware('can:basic-data.edit')
        ->name('departments.update');

    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
        ->middleware('can:basic-data.delete')
        ->name('departments.destroy');
});
