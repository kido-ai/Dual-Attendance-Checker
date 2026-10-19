<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Attendance History</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            margin: 24px;
        }

        .header {
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .meta {
            font-size: 12px;
            color: #475569;
            margin-top: 6px;
        }

        .student {
            margin: 14px 0;
            font-size: 12px;
            color: #334155;
        }

        .filters {
            margin: 16px 0 12px;
            font-size: 11px;
            color: #334155;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #dbe2ea;
            padding: 7px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #ecfdf5;
            color: #0f172a;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            background: #e2e8f0;
            border-radius: 9999px;
            padding: 2px 8px;
            text-transform: capitalize;
        }

        .present {
            background: #dcfce7;
            color: #166534;
        }

        .rejected,
        .absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .verified {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .matched {
            background: #dcfce7;
            color: #166534;
        }

        .invalid,
        .mismatch,
        .not-verified {
            background: #fef2f2;
            color: #b91c1c;
        }

        .expired {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">Student Attendance History</div>
        <div class="meta">Generated: {{ $generatedAt }}</div>
    </div>

    <div class="student">
        <strong>Student:</strong> {{ $student->full_name }}<br>
        <strong>Student Number:</strong> {{ $student->student_number }}<br>
        <strong>Course:</strong> {{ $student->course }} / {{ $student->year_level }} / {{ $student->section }}
    </div>

    <div class="filters">
        @foreach ($filters as $filter)
        <span>{{ $filter }}</span>@if (! $loop->last) · @endif
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Teacher</th>
                <th>Session</th>
                <th>QR</th>
                <th>Face</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
            <tr>
                <td>{{ $record->time_in?->format('M d, Y') ?? 'N/A' }}</td>
                <td>{{ $record->time_in?->format('g:i A') ?? 'N/A' }}</td>
                <td>{{ $record->teacher?->full_name ?? 'Unknown Teacher' }}</td>
                <td>{{ $record->attendanceSession?->title ?? 'Attendance Session' }}</td>
                <td><span class="badge {{ strtolower($record->qr_status ?? 'unknown') }}">{{ ucfirst($record->qr_status ?? 'unknown') }}</span></td>
                <td><span class="badge {{ strtolower(str_replace(' ', '-', $record->face_status ?? 'unknown')) }}">{{ ucfirst($record->face_status ?? 'unknown') }}</span></td>
                <td><span class="badge {{ strtolower($record->attendance_status ?? 'unknown') }}">{{ ucfirst($record->attendance_status ?? 'unknown') }}</span></td>
                <td>{{ $record->remarks ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; padding: 18px; color: #64748b;">No attendance records were found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>