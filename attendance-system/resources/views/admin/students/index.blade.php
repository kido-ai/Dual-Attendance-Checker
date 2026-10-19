{{--
    admin/students/index.blade.php

    DISPLAYS REAL DATA from the database.
    Expects: $students (paginated array), $stats, $courses, $yearLevels, $sections, $statuses
    The UI is unchanged; all sample data has been removed.
--}}
<x-layouts.admin
    title="Student Management"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Student Management']]">
    <div
        x-data="{
            showAddModal: false,
            showArchiveModal: false,
            showDeleteModal: false,
            showRestoreModal: false,
            targetStudent: null,
        }"
        class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Student Management</h2>
                <p class="mt-1 text-sm text-gray-500">Manage student information, enrollment status, and academic records.</p>
            </div>
            <a
                href="{{ route('admin.students.archived') }}"
                class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="archive-box" class="h-4 w-4" />
                View Archived Students
            </a>
        </div>

        {{-- Statistics cards (real data from controller) --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
            <x-stat-card
                :icon="$stat['icon']"
                :tone="$stat['tone']"
                :label="$stat['label']"
                :value="$stat['value']"
                :trend="$stat['trend']"
                :trend-value="$stat['trendValue']" />
            @endforeach
        </div>

        {{-- Top action bar: search, filters, add student --}}
        @include('admin.students.partials._filters', [
        'courses' => $courses,
        'yearLevels' => $yearLevels,
        'sections' => $sections,
        'statuses' => $statuses
        ])

        {{-- Student table with real data --}}
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                            <th class="px-5 py-3 font-medium">Student</th>
                            <th class="px-5 py-3 font-medium">Student No.</th>
                            <th class="px-5 py-3 font-medium">Course</th>
                            <th class="px-5 py-3 font-medium">Year</th>
                            <th class="px-5 py-3 font-medium">Section</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($students as $student)
                        <tr class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if (!empty($student['profile_photo']))
                                    <img src="{{ $student['profile_photo'] }}" alt="{{ $student['full_name'] }}" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200" />
                                    @else
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-600">
                                        {{ substr($student['full_name'], 0, 1) }}
                                    </div>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $student['full_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student['student_number'] }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student['course'] ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student['year'] ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student['section'] ?? 'N/A' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $student['email'] }}</td>
                            <td class="px-5 py-3.5">
                                <x-status-badge :status="$student['status']" />
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.students.show', $student['id']) }}" title="View" class="rounded-lg p-2 text-gray-500 hover:bg-blue-50 hover:text-blue-600">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student['id']) }}" title="Edit" class="rounded-lg p-2 text-gray-500 hover:bg-indigo-50 hover:text-indigo-600">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.students.archive', $student['id']) }}"
                                        onsubmit="return confirm('Mark ' + '{{ addslashes($student['full_name']) }}' + ' as inactive? This keeps them in the student list but marks them inactive.')"
                                        style="display:inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" title="Mark inactive" class="rounded-lg p-2 text-gray-500 hover:bg-amber-50 hover:text-amber-600">
                                            <x-icon name="archive-box" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if (method_exists($students, 'links'))
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $students->links() }}
            </div>
            @endif
        </div>

        {{-- Add Student modal --}}
        <x-modal show="showAddModal" max-width="2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Add Student</h3>
                    <p class="text-sm text-gray-500">Create a new student record</p>
                </div>
                <button type="button" @click="showAddModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto px-6 py-6">
                @include('admin.students.partials._form', [
                'student' => null,
                'isModal' => true,
                'formAction' => route('admin.students.store'),
                'formMethod' => 'POST',
                ])
            </div>
        </x-modal>
    </div>
</x-layouts.admin>