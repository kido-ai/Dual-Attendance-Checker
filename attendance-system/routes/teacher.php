<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\ProfileController;

/*
|--------------------------------------------------------------------------
| Route names kept consistent with this project's existing convention
|--------------------------------------------------------------------------
| `teacher.attendance` (not `teacher.attendance.take`) is the main Take
| Attendance page, matching what was already wired into the sidebar and
| every other Blade view in this codebase. Everything under it
| (start/end/scan-qr/verify-face/mark/manual/search-student) are the new
| endpoints this feature needs. `teacher.attendance.history.*` is
| unchanged and already correct.
|
| REMOVED: the old `attendance.store` route (POST /attendance/store).
| It backed a single-shot endpoint that trusted a client-submitted final
| decision -- superseded entirely by the three-step scan-qr -> verify-face
| -> mark flow below, which never accepts attendance_status from the
| browser.
*/

Route::middleware(['auth', 'role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // --- Take Attendance (main page + session lifecycle) ---
        Route::get('/attendance', [AttendanceController::class, 'takeAttendance'])
            ->name('attendance');

        Route::post('/attendance/start', [AttendanceController::class, 'start'])
            ->name('attendance.start');

        Route::post('/attendance/end', [AttendanceController::class, 'end'])
            ->name('attendance.end');

        // --- Dual authentication steps ---
        Route::post('/attendance/scan-qr', [AttendanceController::class, 'scanQr'])
            ->name('attendance.scan-qr');

        Route::post('/attendance/verify-face', [AttendanceController::class, 'verifyFace'])
            ->name('attendance.verify-face');

        Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance'])
            ->name('attendance.mark');

        // --- Fallbacks ---
        Route::post('/attendance/manual', [AttendanceController::class, 'manualAttendance'])
            ->name('attendance.manual');

        Route::get('/attendance/search-student', [AttendanceController::class, 'searchStudent'])
            ->name('attendance.search-student');

        // --- History (unchanged) ---
        Route::get('/attendance-history', [AttendanceController::class, 'history'])
            ->name('attendance.history.index');

        Route::get('/attendance-history/{attendance}', [AttendanceController::class, 'historyShow'])
            ->name('attendance.history.show');

        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');
    });
