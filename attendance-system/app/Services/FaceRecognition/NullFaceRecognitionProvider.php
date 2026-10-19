<?php

namespace App\Services\FaceRecognition;

/**
 * The default binding. Deliberately does NOT compare images by any
 * shortcut (filename match, student_id match, pixel hashing, etc.) --
 * per the project requirement, that would not be face recognition, it
 * would be pretending to be face recognition. Until a real provider is
 * configured and bound in place of this one, every verify-face request
 * fails cleanly with a specific, identifiable error, which the frontend
 * is built to interpret as a *technical* failure and route to the Manual
 * Verification fallback -- not as a face mismatch.
 */
class NullFaceRecognitionProvider implements FaceRecognitionProvider
{
    public function compare(string $referenceImagePath, string $liveImageData): array
    {
        throw new \RuntimeException(
            'No face recognition provider is configured. Bind a real ' .
            'implementation of FaceRecognitionProvider (see the docblock ' .
            'on that interface) in AppServiceProvider before this endpoint ' .
            'can return real match results.'
        );
    }
}
