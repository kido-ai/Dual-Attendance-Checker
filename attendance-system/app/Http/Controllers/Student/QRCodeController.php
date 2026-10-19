<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\QRService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class QRCodeController extends Controller
{
    /**
     * Display the student's QR code page.
     */
    public function show(): View
    {
        $student = $this->currentStudent();
        $qrToken = $student->qrToken;
        $qrService = new QRService();

        // Build student array for the view
        $studentArray = [
            'name' => $student->full_name ?? $student->name,
            'student_number' => $student->student_number,
            'course' => $student->course ?? 'N/A',
            'year' => $student->year_level ?? 'N/A',
            'section' => $student->section ?? 'N/A',
            'photo' => $student->profile_photo ? Storage::url($student->profile_photo) : null,
        ];

        // QR data
        if ($qrToken) {
            $qrImagePath = $qrService->ensureQrImageExists($qrToken);
            $qrImageUrl = Storage::disk('public')->exists($qrImagePath)
                ? Storage::url($qrImagePath)
                : null;

            $qrArray = [
                'student_number' => $student->student_number,
                'token' => $qrToken->token,
                'token_id' => $qrToken->token,
                'generated_at' => $qrToken->created_at?->format('F d, Y') ?? 'N/A',
                'expires_at' => $qrToken->effectiveExpirationDate()?->format('F d, Y') ?? 'No expiration set',
                'updated_at' => $qrToken->updated_at?->format('F d, Y') ?? 'N/A',
                'status' => $qrToken->isExpired() ? 'Expired' : ucfirst($qrToken->status ?? 'active'),
                'expiration_status' => $qrToken->isExpired() ? 'Expired' : 'Active',
                'image_url' => $qrImageUrl,
            ];
        } else {
            $qrArray = null;
        }

        // Face enrollment status
        $faceProfile = $student->faceProfile;
        $faceEnrollment = [
            'status' => $faceProfile?->status ? str_replace('_', ' ', ucfirst($faceProfile->status)) : 'Pending Enrollment',
            'message' => $this->faceEnrollmentMessage($faceProfile?->status),
            'last_updated' => $faceProfile?->enrolled_at?->format('F d, Y') ?? 'Not yet enrolled',
        ];

        return view('student.qr.index', [
            'hasQrCode' => $qrToken !== null,
            'student' => $studentArray,
            'qr' => $qrArray,
            'faceEnrollment' => $faceEnrollment,
        ]);
    }

    /**
     * Download the QR code image.
     */
    public function download(): Response|RedirectResponse
    {
        $student = $this->currentStudent();
        $qrToken = $student->qrToken;

        abort_if($qrToken === null, 404, 'No QR Code has been assigned to your account yet.');

        $qrService = new QRService();
        $path = $qrService->ensureQrImageExists($qrToken);

        return Storage::disk('public')->download(
            $path,
            "qr-{$student->student_number}.png"
        );
    }

    /**
     * Return live QR status for AJAX polling.
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        $student = $this->currentStudent();
        $qrToken = $student->qrToken;

        if ($qrToken === null) {
            return response()->json(['status' => 'unassigned']);
        }

        return response()->json([
            'status' => $qrToken->isExpired() ? 'expired' : $qrToken->status,
            'expires_at' => $qrToken->effectiveExpirationDate()?->toIso8601String(),
        ]);
    }

    // ---- Helpers ----

    private function currentStudent(): Student
    {
        $student = Auth::user()->student;

        abort_if($student === null, 403, 'No student profile is linked to this account yet. Contact an administrator.');

        return $student;
    }

    private function faceEnrollmentMessage(?string $status): string
    {
        return match ($status) {
            'verified' => 'Your face enrollment is verified and ready for attendance check-in.',
            'needs_update' => 'Your face enrollment needs to be updated. Please visit the administrator office.',
            default => 'Your face enrollment is pending. Please visit the administrator office to complete enrollment.',
        };
    }
}
