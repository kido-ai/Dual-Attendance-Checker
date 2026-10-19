{{--
    admin/students/archived.blade.php

    Reuses partials/_table.blade.php with mode="archived" to swap the
    actions column to Restore / Permanent Delete.
--}}
<x-layouts.admin
    title="Archived Students"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Student Management'], ['label' => 'Archived']]">
    <div
        x-data="{ showRestoreModal: false, showDeleteModal: false, targetStudent: null }"
        class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <a href="{{ route('admin.students.index') }}" class="flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Student Management
            </a>
            <div class="text-left sm:text-right">
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Archived Students</h2>
                <p class="mt-1 text-sm text-gray-500">Records removed from active management. Restore or permanently delete.</p>
            </div>
        </div>

        {{-- Table with real archived students --}}
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
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
                        @forelse ($archivedStudents as $student)
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
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ $student['status'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        type="button"
                                        title="Restore"
                                        @click="targetStudent = { id: {{ $student['id'] }}, name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showRestoreModal = true"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600">
                                        <x-icon name="arrow-path" class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        title="Delete permanently"
                                        @click="targetStudent = { id: {{ $student['id'] }}, name: '{{ $student['full_name'] }}', number: '{{ $student['student_number'] }}' }; showDeleteModal = true"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-400">No archived students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if (method_exists($archivedStudents, 'links'))
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $archivedStudents->links() }}
            </div>
            @endif
        </div>

        {{-- Restore confirmation modal --}}
        <x-modal show="showRestoreModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <x-icon name="archive-box-restore" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Restore student?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    <span x-text="targetStudent?.name"></span> (<span x-text="targetStudent?.number"></span>) will be
                    moved back to the active Student Management list.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showRestoreModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <form method="POST" :action="'{{ route('admin.students.restore', ['student' => ':id']) }}'.replace(':id', targetStudent?.id ?? '')" style="display:inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            <x-icon name="archive-box-restore" class="h-4 w-4" />
                            Restore
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>

        {{-- Permanent delete confirmation modal --}}
        <x-modal show="showDeleteModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <x-icon name="trash" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Delete permanently?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    <span x-text="targetStudent?.name"></span> (<span x-text="targetStudent?.number"></span>) and all
                    associated records will be permanently removed. This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <form method="POST" :action="'{{ route('admin.students.force-delete', ['student' => ':id']) }}'.replace(':id', targetStudent?.id ?? '')" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                            <x-icon name="trash" class="h-4 w-4" />
                            Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-layouts.admin>