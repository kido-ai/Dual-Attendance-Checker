<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceSession extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'attendance_sessions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'teacher_id',
        'title',
        'attendance_date',
        'started_at',
        'ended_at',
        'status',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date',
        'started_at'      => 'datetime',
        'ended_at'        => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Teacher who started this attendance session.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Attendance records that belong to this session.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Get only open attendance sessions.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Get only closed attendance sessions.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Get today's attendance sessions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if the session is currently open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Determine if the session is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Close the attendance session.
     */
    public function close(): bool
    {
        return $this->update([
            'status'    => 'closed',
            'ended_at'  => now(),
        ]);
    }
}