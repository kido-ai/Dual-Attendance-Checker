<?php

namespace App\Services\FaceRecognition;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Concrete example of a real provider, using a plain HTTP call to
 * whatever face-comparison service you stand up or subscribe to -- a
 * self-hosted Python `face_recognition`/dlib/DeepFace microservice, or a
 * cloud API normalized behind your own thin wrapper endpoint.
 *
 * Not bound by default (see AppServiceProvider notes in
 * FaceRecognitionService.php). To use it:
 *   1. Set FACE_RECOGNITION_ENDPOINT and FACE_RECOGNITION_KEY in .env
 *   2. Add both to config/services.php under a 'face_recognition' key
 *   3. Bind FaceRecognitionProvider::class to this class in
 *      AppServiceProvider::register()
 *
 * Expected contract with your endpoint (adjust to whatever you stand up):
 *   POST {endpoint}/compare  { reference_image: base64, live_image: base64 }
 *     -> { "matched": bool, "confidence": float }
 */
class HttpFaceRecognitionProvider implements FaceRecognitionProvider
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $apiKey,
    ) {}

    public function compare(string $referenceImagePath, string $liveImageData): array
    {
        $referenceImage = null;

        foreach (['public', 'local'] as $disk) {
            $candidate = Storage::disk($disk)->get($referenceImagePath);
            if ($candidate !== null) {
                $referenceImage = $candidate;
                break;
            }
        }

        if ($referenceImage === null) {
            throw new \RuntimeException('Enrolled reference image is missing from storage.');
        }

        $http = Http::timeout(10)
            ->acceptJson();

        if ($this->apiKey !== '') {
            $http = $http->withHeaders([
                'X-API-Key' => $this->apiKey,
            ]);
        }

        $response = $http->post("{$this->endpoint}/compare", [
            'reference_image' => base64_encode($referenceImage),
            'live_image' => base64_encode($liveImageData),
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Face recognition provider request failed: ' . $response->status());
        }

        $data = $response->json();

        return [
            'matched' => (bool) ($data['matched'] ?? false),
            'confidence' => (float) ($data['confidence'] ?? 0.0),
        ];
    }
}
