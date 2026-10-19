{{--
    admin/students/partials/_table.blade.php

    Included from index.blade.php (and archived.blade.php with
    mode="archived"). Expects $students (array/collection of associative
    arrays) in scope, and reads $mode ('active' default, or 'archived')
    to swap the actions column.

    Row actions reach into the parent page's Alpine scope
    (showArchiveModal / showDeleteModal / showRestoreModal / targetStudent),
    which is why this is a plain @include partial rather than an isolated
    component — Alpine state cascades through the DOM regardless.
--}}
@php $mode = $mode ?? 'active'; @endphp

<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1080px] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-5 py-3 font-medium">Profile</th>
                    <th class="px-5 py-3 font-medium">Student No.</th>
                    <th class="px-5 py-3 font-medium">Full Name</th>
                    <th class="px-5 py-3 font-medium">Course</th>
                    <th class="px-5 py-3 font-medium">Year</th>
                    <th class="px-5 py-3 font-medium">Section</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Created</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($students as $student)
                    <tr class="transition-colors duration-150 hover:bg-gray-50">
                        <td class="px-5 py-3.5">
                            <img
                                src="https://ui-avatars.com/api/?name={{ urlencode($student['full_name']) }}&background=E2E8F0&color=334155"
                                alt="{{ $student['full_name'] }}"
                                class="h-9 w-9 rounded-full"
                            >
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student['student_number'] }}</td>
                        <td class="px-5 py-3.5 font-medium text-gray-900">{{ $student['full_name'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student['course'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student['year'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student['section'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $student['email'] }}</td>
                        <td class="px-5 py-3.5"><x-status-badge :status="$student['status']" /></td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $student['created_at'] }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1">
                                @if ($mode === 'archived')
                                    {{-- Archived table: Restore + Permanent Delete --}}
                                    <button
                                        type="button"
                                        title="Restore"
                                        @click="targetStudent = { name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showRestoreModal = true"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600"
                                    >
                                        <x-icon name="archive-box-restore" class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        title="Delete permanently"
                                        @click="targetStudent = { name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showDeleteModal = true"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600"
                                    >
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                @else
                                    {{-- Active table: View / Edit / Archive + overflow menu --}}
                                    <a href="{{ Route::has('admin.students.show') ? route('admin.students.show', $student['id']) : '#' }}" title="View" class="rounded-lg p-1.5 text-gray-400 hover:bg-blue-50 hover:text-blue-600">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ Route::has('admin.students.edit') ? route('admin.students.edit', $student['id']) : '#' }}" title="Edit" class="rounded-lg p-1.5 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    <button
                                        type="button"
                                        title="Archive"
                                        @click="targetStudent = { name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showArchiveModal = true"
                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-amber-50 hover:text-amber-600"
                                    >
                                        <x-icon name="archive-box" class="h-4 w-4" />
                                    </button>

                                    {{-- Overflow dropdown --}}
                                    <div class="relative" x-data="{ rowMenuOpen: false }">
                                        <button
                                            type="button"
                                            @click="rowMenuOpen = !rowMenuOpen"
                                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                                        >
                                            <x-icon name="ellipsis-vertical" class="h-4 w-4" />
                                        </button>
                                        <div
                                            x-show="rowMenuOpen"
                                            @click.outside="rowMenuOpen = false"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-cloak
                                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-2xl border border-gray-100 bg-white p-1.5 shadow-lg"
                                        >
                                            <a href="{{ Route::has('admin.students.show') ? route('admin.students.show', $student['id']) : '#' }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                                <x-icon name="user-circle" class="h-4 w-4 text-gray-400" /> View Profile
                                            </a>
                                            <button type="button" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                                <x-icon name="key" class="h-4 w-4 text-gray-400" /> Reset Password
                                            </button>
                                            <button type="button" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                                <x-icon name="no-symbol" class="h-4 w-4 text-gray-400" /> Deactivate
                                            </button>
                                            <div class="my-1 border-t border-gray-100"></div>
                                            <button
                                                type="button"
                                                @click="rowMenuOpen = false; targetStudent = { name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showDeleteModal = true"
                                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                                            >
                                                <x-icon name="trash" class="h-4 w-4" /> Delete
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    {{-- Empty state --}}
                    <tr>
                        <td colspan="10" class="px-5 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                                    <x-icon name="academic-cap" class="h-8 w-8 text-gray-300" />
                                </div>
                                <p class="mt-4 text-sm font-semibold text-gray-700">No students found</p>
                                <p class="mt-1 text-sm text-gray-400">
                                    {{ $mode === 'archived' ? 'The archive is currently empty.' : 'Get started by adding your first student.' }}
                                </p>
                                @if ($mode !== 'archived')
                                    <button
                                        type="button"
                                        @click="showAddModal = true"
                                        class="mt-5 flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700"
                                    >
                                        <x-icon name="user-plus" class="h-5 w-5" />
                                        Add First Student
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination placeholder --}}
    @if (count($students))
        <div class="flex flex-col gap-3 border-t border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-gray-500">Showing 1&ndash;{{ count($students) }} of 235 Students</p>
            <div class="flex items-center gap-1">
                <button type="button" class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50" disabled>Previous</button>
                <button type="button" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">1</button>
                <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">2</button>
                <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">3</button>
                <button type="button" class="rounded-lg border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-50">Next</button>
            </div>
        </div>
    @endif
</div>
