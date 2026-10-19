<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly FaceRecognitionService $faceService,
    ) {}

    public function store(Request $request, Student $student): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $imageData = file_get_contents($request->file('photo')->getRealPath());

        if ($imageData === false) {
            return back()->withErrors(['photo' => 'Unable to read the uploaded photo.']);
        }

        $this->faceService->enrollFace($student, $imageData);

        return back()->with('success', "Face enrolled successfully for {$student->full_name}.");
    }
}
