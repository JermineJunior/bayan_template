<?php

use App\Http\Controllers\Account\PreferencesController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth'])->name('home');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Read-only pages share the roles.view permission.
    Route::middleware('can:roles.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::get('roles/{role}', [RoleController::class, 'edit'])->name('roles.edit');
    });

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
