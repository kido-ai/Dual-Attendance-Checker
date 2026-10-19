<?php

namespace App\Services\FaceRecognition;

/**
 * Anything that can compare a live-captured face image against a
 * previously enrolled reference implements this. Swap the bound
 * implementation (see AppServiceProvider notes in
 * FaceRecognitionService.php) to connect a real provider -- AWS
 * Rekognition CompareFaces, Azure Face API, a self-hosted
 * face_recognition/dlib microservice, etc. -- without touching the
 * controller, the routes, or the attendance workflow at all.
 */
interface FaceRecognitionProvider
{
    /**
     * @param string $referenceImagePath Path (on the private disk) to the
     *                                   student's enrolled reference photo
     *                                   -- this is exactly what's stored
     *                                   in face_profiles.face_embedding.
     * @param string $liveImageData      Raw image bytes captured live from
     *                                   the teacher's device camera.
     *
     * @return array{matched: bool, confidence: float} confidence is 0-1.
     *
     * @throws \RuntimeException if the comparison could not be performed
     *                           at all (provider outage, corrupt image,
     *                           etc.) -- as distinct from a clean "these
     *                           two faces are different" result.
     */
    public function compare(string $referenceImagePath, string $liveImageData): array;
}
