<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = request()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'teacher' => redirect()->route('teacher.dashboard'),
        'student' => redirect()->route('student.dashboard'),
        default => abort(403, 'Your account role is invalid.'),
    };
})->name('home');

Route::get('/test', function () {
    return '<h1>It works!</h1>';
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        $user = request()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.profile.edit');
        } elseif ($user->role === 'teacher') {
            return redirect()->route('teacher.profile.edit');
        } elseif ($user->role === 'student') {
            return redirect()->route('student.profile.edit');
        }

        abort(403, 'No profile page for this role.');
    })->name('profile.edit');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/teacher.php';
require __DIR__ . '/student.php';
