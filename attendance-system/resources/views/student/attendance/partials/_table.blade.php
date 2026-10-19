<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium">Time</th>
                    <th class="px-5 py-3 font-medium">Teacher</th>
                    <th class="px-5 py-3 font-medium">Session</th>
                    <th class="px-5 py-3 font-medium">QR Verification</th>
                    <th class="px-5 py-3 font-medium">Face Verification</th>
                    <th class="px-5 py-3 font-medium">Attendance</th>
                    <th class="px-5 py-3 font-medium">Remarks</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($records as $record)
                <tr class="transition-colors duration-150 hover:bg-gray-50">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $record['date'] }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $record['time'] }}</td>
                    <td class="px-5 py-3.5 text-gray-700">{{ $record['teacher'] }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $record['subject'] }}</td>
                    <td class="px-5 py-3.5"><x-status-badge :status="$record['qr_status']" /></td>
                    <td class="px-5 py-3.5"><x-status-badge :status="$record['face_status']" /></td>
                    <td class="px-5 py-3.5"><x-status-badge :status="$record['status']" /></td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $record['remarks'] }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <button
                            type="button"
                            @click="targetRecord = @js($record); showDetailsModal = true"
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                            <x-icon name="eye" class="h-4 w-4" />
                            View Details
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-16">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                                <x-icon name="inbox" class="h-8 w-8 text-gray-300" />
                            </div>
                            <p class="mt-4 text-sm font-semibold text-gray-700">No attendance records found.</p>
                            <p class="mt-1 text-sm text-gray-400">Your recorded sessions will appear here once you check in.</p>
                            <a href="{{ route('student.dashboard') }}" class="mt-5 flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                                Back to Dashboard
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($records->hasPages())
    <div class="flex flex-col gap-3 border-t border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-gray-500">Showing {{ $records->firstItem() }}&ndash;{{ $records->lastItem() }} of {{ $records->total() }} attendance records</p>
        <div class="flex items-center gap-1">
            {{ $records->links() }}
        </div>
    </div>
    @endif
</div>