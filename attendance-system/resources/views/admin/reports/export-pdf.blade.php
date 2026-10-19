<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1f2937;
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

        .rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .absent {
            background: #fef3c7;
            color: #92400e;
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
        .mismatch {
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
        <div class="title">Dual Auth Attendance Report</div>
        <div class="meta">Generated: {{ $generatedAt }}</div>
    </div>

    <div class="filters">
        @foreach ($filters as $filter)
        <span>{{ $filter }}</span>@if (! $loop->last) · @endif
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Student No.</th>
                <th>Teacher</th>
                <th>Date</th>
                <th>Time In</th>
                <th>QR</th>
                <th>Face</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->student?->full_name ?? 'Unknown Student' }}</td>
                <td>{{ $record->student?->student_number ?? 'N/A' }}</td>
                <td>{{ $record->teacher?->full_name ?? 'Unknown Teacher' }}</td>
                <td>{{ $record->time_in?->format('Y-m-d') ?? 'N/A' }}</td>
                <td>{{ $record->time_in?->format('H:i:s') ?? 'N/A' }}</td>
                <td><span class="badge {{ strtolower($record->qr_status ?? 'unknown') }}">{{ ucfirst($record->qr_status ?? 'unknown') }}</span></td>
                <td><span class="badge {{ strtolower($record->face_status ?? 'unknown') }}">{{ ucfirst($record->face_status ?? 'unknown') }}</span></td>
                <td><span class="badge {{ strtolower($record->attendance_status ?? 'unknown') }}">{{ ucfirst($record->attendance_status ?? 'unknown') }}</span></td>
                <td>{{ $record->remarks ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding: 18px; color: #64748b;">No records were found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>