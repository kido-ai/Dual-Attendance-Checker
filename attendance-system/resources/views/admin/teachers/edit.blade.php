<x-layouts.admin
    title="Edit Teacher"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Teacher Management'], ['label' => 'Edit Teacher']]">
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Teacher Management
            </a>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900">Edit Teacher</h2>
            <p class="mt-1 text-sm text-gray-500">Update {{ $teacher->full_name }}'s record.</p>
        </div>

        @include('admin.teachers.partials._form', [
        'teacher' => [
        'employee_id' => $teacher->employee_id,
        'first_name' => $teacher->first_name,
        'middle_name' => $teacher->middle_name,
        'last_name' => $teacher->last_name,
        'email' => $teacher->email ?? $teacher->user?->email,
        'phone' => $teacher->phone,
        'profile_photo' => $teacher->profile_photo,
        'status' => $teacher->status,
        'photo_url' => $teacher->profile_photo ? Storage::disk('public')->url($teacher->profile_photo) : null,
        ],
        'isModal' => false,
        'cancelUrl' => route('admin.teachers.index'),
        'formAction' => route('admin.teachers.update', $teacher),
        'formMethod' => 'PUT',
        ])
    </div>
</x-layouts.admin>