<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Employee\Dashboard::class)->name('dashboard');
    Route::get('/attendance', \App\Livewire\Attendance\CheckInOut::class)->name('attendance');
    Route::get('/requests', \App\Livewire\Employee\Requests::class)->name('requests');
    Route::get('/profile', \App\Livewire\Employee\Profile::class)->name('profile');
    
    // Legacy / specific routes
    Route::get('/leave', \App\Livewire\Leave\RequestForm::class)->name('leave.request');
    Route::get('/overtime', \App\Livewire\Overtime\RequestForm::class)->name('overtime.request');
    
    Route::post('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
    // Payroll PDF routes
    // Payroll PDF routes
    Route::get('/payroll/{payroll}/view', [\App\Http\Controllers\PayrollPdfController::class, 'view'])
        ->name('payroll.view');
});

// Signed route for WhatsApp access (outside auth middleware group if valid signature)
Route::get('/payroll/{payroll}/stream', [\App\Http\Controllers\PayrollPdfController::class, 'stream'])
    ->name('payroll.stream')
    ->middleware('signed');
