<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ContractController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Auth routes (login, logout) - không cần middleware admin.auth
Auth::routes();

// Admin routes - yêu cầu authentication
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', fn() => view('admin.dashboard'))->name('dashboard');
    
    // Contracts routes
    Route::resource('contracts', ContractController::class);
    Route::post('contracts/check-expired', [ContractController::class, 'checkExpired'])->name('contracts.check-expired');
});