<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Admin-only controller for enrolling a student's reference face.
 * This is a one-time setup per student, done by the admin before that
 * student can use dual-authentication attendance.
 *
 * Route to add in routes/admin.php:
 *   Route::post('students/{student}/enroll-face', [FaceEnrollmentController::class, 'store'])
 *       ->name('students.enroll-face');
 */
class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceService,
    ) {
    }

    /**
     * Receive a photo upload and store it as the student's enrolled
     * reference face. Called from the Admin student management page
     * (admin/students/show.blade.php) -- a simple file upload form,
     * not a camera stream. A clear, frontal photo works best.
     */
    public function store(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $imageData = file_get_contents($request->file('photo')->getRealPath());

        $this->faceService->enrollFace($student, $imageData);

        return back()->with('status', "Face enrolled successfully for {$student->full_name}.");
    }
}
