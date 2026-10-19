<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Why this migration exists
|--------------------------------------------------------------------------
| dual_auth_attendance.sql shows the real, already-migrated enums:
|   attendances.qr_status:   enum('verified','invalid','expired')
|   attendances.face_status: enum('matched','mismatch','not_verified')
|
| The "Student forgot QR" flow (spec section 20) needs to persist
| qr_status = 'bypassed', and the Manual Verification fallback (section 19)
| needs face_status = 'manual'. Neither value exists in the current enum,
| so writing either would throw a MySQL data-truncation error. This is
| the only schema change this feature actually still needs -- everything
| else (attendance_session_id, the unique(attendance_session_id,
| student_id) constraint, attendance_sessions' columns, face_profiles'
| columns, qr_tokens' columns) already exists exactly as the real models
| expect. No activity_logs changes, per your instruction.
|
| Raw ALTER TABLE is used because Laravel's schema builder can't modify
| an existing enum's value list without doctrine/dbal.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE attendances
            MODIFY qr_status ENUM('verified','invalid','expired','bypassed') DEFAULT 'invalid'
        ");

        DB::statement("
            ALTER TABLE attendances
            MODIFY face_status ENUM('matched','mismatch','not_verified','manual') DEFAULT 'not_verified'
        ");
    }

    public function down(): void
    {
        // Reverting drops any rows already using the new values back to
        // their nearest safe equivalent before shrinking the enum, so the
        // ALTER doesn't fail on existing data.
        DB::statement("UPDATE attendances SET qr_status = 'invalid' WHERE qr_status = 'bypassed'");
        DB::statement("UPDATE attendances SET face_status = 'not_verified' WHERE face_status = 'manual'");

        DB::statement("
            ALTER TABLE attendances
            MODIFY qr_status ENUM('verified','invalid','expired') DEFAULT 'invalid'
        ");

        DB::statement("
            ALTER TABLE attendances
            MODIFY face_status ENUM('matched','mismatch','not_verified') DEFAULT 'not_verified'
        ");
    }
};
