<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\BrokerManagementController;

Route::resource('properties', PropertyController::class);

use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::get('/brokers', [BrokerManagementController::class, 'index'])->name('admin.brokers');
    Route::post('/brokers/{user}/approve', [BrokerManagementController::class, 'approveBroker'])->name('admin.brokers.approve');
    Route::post('/brokers/{user}/revoke', [BrokerManagementController::class, 'revokeBroker'])->name('admin.brokers.revoke');
    Route::post('/brokers/{user}/make-admin', [BrokerManagementController::class, 'makeAdmin'])->name('admin.brokers.makeAdmin');
});

