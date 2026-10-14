<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\EventSetupController;
use App\Http\Controllers\Admin\RegistrationsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/register');

// Public — members and visitors register themselves, no sign-in.
Route::get('register', [RegistrationController::class, 'create'])->name('register');

// Each submit writes a row and opens a Razorpay order, so a script can't hammer it.
Route::post('register', [RegistrationController::class, 'store'])->middleware('throttle:10,1')->name('register.store');
Route::post('register/paid', [RegistrationController::class, 'paid'])->name('register.paid');

// Each call is a Razorpay API request; the form polls every 3s, well under this.
Route::post('register/status', [RegistrationController::class, 'status'])->middleware('throttle:30,1')->name('register.status');
Route::get('register/{reference}/confirmed', [RegistrationController::class, 'confirmed'])->name('register.confirmed');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::redirect('/', '/admin/registrations');

        Route::get('registrations', [RegistrationsController::class, 'index'])->name('registrations');

        Route::get('event-setup', [EventSetupController::class, 'index'])->name('event-setup');
        Route::post('chapters', [EventSetupController::class, 'storeChapter'])->name('chapters.store');
        Route::delete('chapters/{chapter}', [EventSetupController::class, 'destroyChapter'])->name('chapters.destroy');
        Route::post('business-categories', [EventSetupController::class, 'storeCategory'])->name('business-categories.store');
        Route::delete('business-categories/{businessCategory}', [EventSetupController::class, 'destroyCategory'])
            ->name('business-categories.destroy');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
