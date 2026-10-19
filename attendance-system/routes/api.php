<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\FaceRecognitionApiController;
use App\Http\Controllers\API\QRValidationController;
use App\Http\Controllers\API\AttendanceApiController;

/*
|--------------------------------------------------------------------------
| CHANGE from the uploaded version
|--------------------------------------------------------------------------
| 1. QRValidationController@validate -> @verify. Naming a controller
|    action `validate` risks colliding with Laravel's
|    ValidatesRequests::validate() helper if that trait is ever added to
|    the base Controller (it was in the default skeleton before Laravel
|    11, and plenty of projects still add it back). Even without an
|    active collision today, it's a landmine for exactly the kind of
|    "why did my $this->validate() call break" bug that's hard to trace.
|    Renamed to `verify`, matching FaceRecognitionApiController::verify()
|    right below it for a consistent naming pattern across both
|    verification endpoints.
|
| 2. Added ->name() to all three routes. They had none before, which
|    meant the only way to reference them (from the Take Attendance
|    page's JS, from tests, from route() calls anywhere) was hardcoding
|    the literal path string. Named for the same reason every other
|    route file in this app names its routes.
*/

Route::middleware('auth')->group(function () {

    Route::post('/qr/validate', [QRValidationController::class, 'verify'])
        ->name('api.qr.verify');

    Route::post('/face/verify', [FaceRecognitionApiController::class, 'verify'])
        ->name('api.face.verify');

    Route::post('/attendance/store', [AttendanceApiController::class, 'store'])
        ->name('api.attendance.store');
});
