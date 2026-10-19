<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class QRToken extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | CORRECTION (reverts an earlier, wrong "fix")
    |--------------------------------------------------------------------------
    | An earlier pass in this project changed this to `$table = 'q_r_tokens'`,
    | reasoning from the *migration filename*
    | (2026_07_11_025551_create_q_r_tokens_table). That was a mistake: the
    | migration filename is just a label, not the actual table name it
    | creates. dual_auth_attendance.sql -- the real, live database dump --
    | shows the table that migration actually created is `qr_tokens`
    | (CREATE TABLE `qr_tokens` ...). The original `$table = 'qr_tokens'`
    | was correct all along. Restored here.
    */
    protected $table = 'qr_tokens';

    protected $fillable = [
        'student_id',
        'token',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isExpired()
    {
        $expiration = $this->effectiveExpirationDate();

        return $expiration !== null && now()->greaterThanOrEqualTo($expiration);
    }

    public function effectiveExpirationDate(): ?Carbon
    {
        if ($this->expires_at) {
            return $this->expires_at;
        }

        if (! $this->created_at) {
            return null;
        }

        return $this->created_at->copy()->addDays(
            (int) SystemSetting::get('qr_expiration_days', '365')
        );
    }
}
