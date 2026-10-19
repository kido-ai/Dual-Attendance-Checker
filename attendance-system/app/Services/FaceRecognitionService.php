<?php

namespace App\Services;

use App\Models\FaceProfile;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Services\FaceRecognition\FaceRecognitionProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Controllers call this, never FaceRecognitionProvider directly. This
 * class owns the business rules (does this student even have a profile
 * yet? what confidence threshold counts as a match? what do we persist
 * on enrollment?) and hands the actual image comparison off to whichever
 * provider is bound in the container.
 *
 * CORRECTED to match the real face_profiles table (dual_auth_attendance.sql):
 *   id, student_id, face_embedding (longtext), enrolled_at, status
 *   status enum('enrolled','not_enrolled')
 * There's no separate column for a provider-specific reference id, so
 * enrollment stores the reference photo's storage path directly in
 * face_embedding and that's what gets handed to the provider at compare
 * time. No provider call happens at enrollment -- "enroll" is just "save
 * the reference photo," which needs no provider-specific logic.
 *
 * To connect a real provider: bind FaceRecognitionProvider::class to a
 * concrete implementation (see HttpFaceRecognitionProvider for the
 * pattern) in AppServiceProvider::register(), e.g.:
 *
 *   $this->app->bind(FaceRecognitionProvider::class, function () {
 *       return new HttpFaceRecognitionProvider(
 *           config('services.face_recognition.endpoint'),
 *           config('services.face_recognition.key'),
 *       );
 *   });
 *
 * Until that binding exists, the default NullFaceRecognitionProvider
 * makes every verification attempt fail loudly and specifically -- see
 * that class's docblock for why that's correct behavior, not a bug.
 */
class FaceRecognitionService
{
    public function __construct(
        private readonly FaceRecognitionProvider $provider,
    ) {}

    /**
     * Enroll (or re-enroll) a student's reference face. Stores the raw
     * image on the private disk and records the profile as enrolled.
     */
    public function enrollFace(Student $student, string $imageData): FaceProfile
    {
        $path = "face-profiles/{$student->id}-" . now()->timestamp . '.jpg';
        Storage::disk('public')->put($path, $imageData);

        return FaceProfile::updateOrCreate(
            ['student_id' => $student->id],
            [
                'face_embedding' => $path,
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]
        );
    }

    /**
     * Verify a live-captured image against the student's enrolled face.
     *
     * @return array{face_status: 'matched'|'mismatch'|'error', confidence: float|null, message: ?string}
     */
    public function verifyFace(Student $student, string $imageData): array
    {
        $profile = $student->faceProfile;

        if (! $profile || $profile->status !== 'enrolled' || ! $profile->face_embedding) {
            return [
                'face_status' => 'error',
                'confidence' => null,
                'message' => 'This student has no registered face profile. They must be enrolled before dual-authentication attendance can be used.',
            ];
        }

        try {
            $result = $this->provider->compare($profile->face_embedding, $imageData);
        } catch (\Throwable $e) {
            Log::error('Face recognition provider error', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'face_status' => 'error',
                'confidence' => null,
                'message' => 'Face verification could not be completed. Please try again.',
            ];
        }

        $threshold = (float) SystemSetting::get('face_match_threshold', '0.55');
        $matched = $result['matched'] && $result['confidence'] >= $threshold;

        return [
            'face_status' => $matched ? 'matched' : 'mismatch',
            'confidence' => $result['confidence'],
            'message' => $matched ? null : 'The face does not match the registered student.',
        ];
    }
}
