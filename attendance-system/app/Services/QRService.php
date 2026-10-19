<?php

namespace App\Services;

use App\Models\QRToken;
use App\Models\SystemSetting;
use App\Models\Student;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Owns everything about QR tokens: issuing them and validating them.
 * The controller never touches QRToken directly -- it asks this service
 * "is this token good?" and gets back a plain result object.
 */
class QRService
{
    /**
     * Issue a new, opaque token for a student. Never encodes anything
     * identifying (no student_id, number, or name) -- just a random
     * UUID the database can look up.
     */
    public function generateToken(Student $student, ?\DateTimeInterface $expiresAt = null): QRToken
    {
        // A student should only ever have one *active* token. Retire any
        // existing one before issuing a new one so scanning an old,
        // physically-reprinted QR code can't succeed after a reissue.
        QRToken::where('student_id', $student->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        $expiration = $expiresAt ?? now()->addDays(
            (int) SystemSetting::get('qr_expiration_days', '365')
        );

        return QRToken::create([
            'student_id' => $student->id,
            'token' => (string) Str::uuid(),
            'status' => 'active',
            'expires_at' => $expiration,
        ]);
    }

    /**
     * Validate a scanned token against the database. Returns one of:
     *   ['status' => 'invalid']        - no matching token exists
     *   ['status' => 'already_used']   - token exists but isn't active
     *   ['status' => 'expired']        - token exists, was active, but past expires_at
     *   ['status' => 'verified', 'token' => QRToken]
     *
     * This never mutates state (no "mark used" here) -- QR validation is
     * step one of two. The token is only actually consumed once the
     * whole dual-auth flow succeeds (see markUsed()), so a valid QR scan
     * followed by a face mismatch can be retried with the same code.
     */
    public function validateToken(string $rawToken): array
    {
        $qrToken = QRToken::with('student')->where('token', $rawToken)->first();

        if (! $qrToken) {
            return ['status' => 'invalid'];
        }

        if ($qrToken->status !== 'active') {
            return ['status' => 'already_used'];
        }

        if ($qrToken->isExpired()) {
            $qrToken->update(['status' => 'expired']);

            return ['status' => 'expired'];
        }

        if (! $qrToken->student) {
            return ['status' => 'invalid'];
        }

        return ['status' => 'verified', 'token' => $qrToken];
    }

    /**
     * Consume a token so it can never be scanned again. Called only
     * after a full successful dual-auth attendance is recorded.
     */
    public function markUsed(QRToken $qrToken): void
    {
        $qrToken->update(['status' => 'inactive']);
    }

    /**
     * Backward-compatible helper used by older controller code.
     */
    public function generateForStudent(Student $student): QRToken
    {
        return $this->generateToken($student);
    }

    /**
     * Backward-compatible helper used by older controller code.
     */
    public function regenerateForStudent(Student $student): QRToken
    {
        $existingToken = $student->qrToken;

        if ($existingToken) {
            $existingToken->delete();
        }

        return $this->generateForStudent($student);
    }

    /**
     * Remove the generated QR image if one was persisted.
     */
    public function ensureQrImageExists(QRToken $qrToken): string
    {
        $path = "qr-codes/{$qrToken->token}.png";

        if (! Storage::disk('public')->exists($path)) {
            Storage::disk('public')->makeDirectory('qr-codes');

            $qrCode = new QRCode(new QROptions([
                'outputInterface' => QRGdImagePNG::class,
                'outputBase64' => false,
                'scale' => 10,
                'margin' => 1,
            ]));

            $png = $qrCode->render($qrToken->token);

            if (! is_string($png)) {
                throw new \RuntimeException('Failed to generate QR code image for the student token.');
            }

            Storage::disk('public')->put($path, $png);
        }

        return $path;
    }

    public function deleteQRImage(string $token): void
    {
        Storage::disk('public')->delete("qr-codes/{$token}.png");
    }
}
