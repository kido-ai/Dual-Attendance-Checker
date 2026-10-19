<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\QRCodeController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\ProfileController;

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/qr-code', [QRCodeController::class, 'show'])
            ->name('qr.show');

        Route::get('/qr', [QRCodeController::class, 'show'])
            ->name('qr.index');

        Route::get('/qr-download', [QRCodeController::class, 'download'])
            ->name('qr.download');

        Route::get('/qr-status', [QRCodeController::class, 'status'])
            ->name('qr.status');

        Route::get('/attendance', [AttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::get('/attendance/export-pdf', [AttendanceController::class, 'exportPdf'])
            ->name('attendance.export-pdf');

        Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
            ->name('attendance.show');

        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');
    });
