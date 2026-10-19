<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\FaceEnrollmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |------------------------------------------------------------------
        | Students
        |------------------------------------------------------------------
        | QR token is auto-issued on StudentController::store().
        | Face enrollment is a separate POST done by the admin after
        | creation, via FaceEnrollmentController.
        | QR regeneration (lost/compromised token) is also admin-only.
        */
        Route::get('students/archived', [StudentController::class, 'archived'])
            ->name('students.archived');

        Route::resource('students', StudentController::class);

        Route::post('students/{student}/regenerate-qr', [StudentController::class, 'regenerateQr'])
            ->name('students.regenerate-qr');

        Route::put('students/{student}/archive', [StudentController::class, 'archive'])
            ->name('students.archive');

        Route::put('students/{student}/restore', [StudentController::class, 'restore'])
            ->name('students.restore');

        Route::delete('students/{student}/force-delete', [StudentController::class, 'forceDelete'])
            ->name('students.force-delete');

        Route::post('students/{student}/enroll-face', [FaceEnrollmentController::class, 'store'])
            ->name('students.enroll-face');

        Route::get('teachers/archived', [TeacherController::class, 'archived'])
            ->name('teachers.archived');

        Route::resource('teachers', TeacherController::class);

        Route::put('teachers/{teacher}/archive', [TeacherController::class, 'archive'])
            ->name('teachers.archive');

        Route::put('teachers/{teacher}/restore', [TeacherController::class, 'restore'])
            ->name('teachers.restore');

        Route::delete('teachers/{teacher}/force-delete', [TeacherController::class, 'forceDelete'])
            ->name('teachers.force-delete');

        Route::resource('users', UserController::class);

        /*
        |------------------------------------------------------------------
        | Attendance (read-only monitoring for admin)
        |------------------------------------------------------------------
        | NOTE: admin/attendance/export must be defined BEFORE the
        | {attendance} parameterized route, otherwise Laravel matches
        | "export" as an attendance id and passes it to show() instead.
        */
        Route::get('attendance/export', [AttendanceController::class, 'export'])
            ->name('attendance.export');

        Route::get('attendance', [AttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::get('attendance/{attendance}', [AttendanceController::class, 'show'])
            ->name('attendance.show');

        /*
        |------------------------------------------------------------------
        | Reports
        |------------------------------------------------------------------
        */
        Route::get('reports', [ReportController::class, 'attendance'])->name('reports.index');
        Route::get('reports/students', [ReportController::class, 'student'])->name('reports.students');
        Route::get('reports/teachers', [ReportController::class, 'teacher'])->name('reports.teachers');
        Route::get('reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [SettingController::class, 'reset'])->name('settings.reset');
        Route::delete('settings/logo', [SettingController::class, 'removeLogo'])->name('settings.remove-logo');
    });
