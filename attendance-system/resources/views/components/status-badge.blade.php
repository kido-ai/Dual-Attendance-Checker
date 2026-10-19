{{--
    components/status-badge.blade.php

    Shared status pill used across the Student Management, Teacher
    Management, and Attendance Records modules (table, profile page,
    archive list, details modal).

    Usage: <x-status-badge :status="$student['status']" />
--}}
@props(['status' => 'Active'])

@php
$tone = match (strtolower($status)) {
'active' => 'bg-emerald-50 text-emerald-700',
'inactive' => 'bg-amber-50 text-amber-700',
'archived' => 'bg-gray-100 text-gray-500',
'graduated' => 'bg-indigo-50 text-indigo-700',
'suspended' => 'bg-red-50 text-red-700',
// Teacher employment types — informational, not health-status colors
'full-time' => 'bg-blue-50 text-blue-700',
'part-time' => 'bg-indigo-50 text-indigo-700',
'contractual' => 'bg-amber-50 text-amber-700',
'visiting' => 'bg-gray-100 text-gray-600',
// Attendance Records — QR / Face / Attendance statuses
'present', 'verified', 'matched' => 'bg-emerald-50 text-emerald-700',
'absent', 'invalid', 'mismatch', 'rejected' => 'bg-red-50 text-red-700',
'expired' => 'bg-amber-50 text-amber-700',
'not verified' => 'bg-gray-100 text-gray-500',
// My QR Code — face enrollment status
'pending enrollment' => 'bg-amber-50 text-amber-700',
'needs update' => 'bg-red-50 text-red-700',
default => 'bg-gray-100 text-gray-500',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold $tone"]) }}>
    {{ $status }}
</span>