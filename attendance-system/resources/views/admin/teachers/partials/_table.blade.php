@php $mode = $mode ?? 'active'; @endphp

<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase tracking-wider text-gray-400">
                    <th class="px-5 py-3 font-medium">Profile</th>
                    <th class="px-5 py-3 font-medium">Employee ID</th>
                    <th class="px-5 py-3 font-medium">Full Name</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">Phone</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Created</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($teachers as $teacher)
                <tr class="transition-colors duration-150 hover:bg-gray-50">
                    <td class="px-5 py-3.5">
                        @if (!empty($teacher['profile_photo']))
                        <img src="{{ $teacher['profile_photo'] }}" alt="{{ $teacher['full_name'] }}" class="h-9 w-9 rounded-full object-cover ring-1 ring-gray-200">
                        @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold text-gray-600">
                            {{ strtoupper(substr($teacher['full_name'], 0, 1)) }}
                        </div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $teacher['employee_id'] }}</td>
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $teacher['full_name'] }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $teacher['email'] }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $teacher['phone'] }}</td>
                    <td class="px-5 py-3.5"><x-status-badge :status="$teacher['status']" /></td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $teacher['created_at'] }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-1">
                            @if ($mode === 'archived')
                            <form method="POST" action="{{ route('admin.teachers.restore', $teacher['id']) }}" onsubmit="return confirm('Restore {{ addslashes($teacher['full_name']) }}?')" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" title="Restore" class="rounded-lg p-1.5 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600">
                                    <x-icon name="archive-box-restore" class="h-4 w-4" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.teachers.force-delete', $teacher['id']) }}" onsubmit="return confirm('Permanently delete {{ addslashes($teacher['full_name']) }}?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete permanently" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                            @else
                            <a href="{{ route('admin.teachers.show', $teacher['id']) }}" title="View" class="rounded-lg p-1.5 text-gray-400 hover:bg-blue-50 hover:text-blue-600">
                                <x-icon name="eye" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('admin.teachers.edit', $teacher['id']) }}" title="Edit" class="rounded-lg p-1.5 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600">
                                <x-icon name="pencil" class="h-4 w-4" />
                            </a>
                            <form method="POST" action="{{ route('admin.teachers.archive', $teacher['id']) }}" onsubmit="return confirm('Archive {{ addslashes($teacher['full_name']) }}?')" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" title="Archive" class="rounded-lg p-1.5 text-gray-400 hover:bg-amber-50 hover:text-amber-600">
                                    <x-icon name="archive-box" class="h-4 w-4" />
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16">
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50">
                                <x-icon name="briefcase" class="h-8 w-8 text-gray-300" />
                            </div>
                            <p class="mt-4 text-sm font-semibold text-gray-700">No teachers found.</p>
                            <p class="mt-1 text-sm text-gray-400">
                                {{ $mode === 'archived' ? 'The archive is currently empty.' : 'Get started by adding your first teacher.' }}
                            </p>
                            @if ($mode !== 'archived')
                            <a href="{{ route('admin.teachers.create') }}" class="mt-5 flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                                <x-icon name="user-plus" class="h-5 w-5" />
                                Add First Teacher
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($teachers->count())
    <div class="border-t border-gray-100 px-5 py-4">
        {{ $teachers->links() }}
    </div>
    @endif
</div>