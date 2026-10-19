{{--
    admin/students/edit.blade.php

    $student comes from StudentController@edit(Student $student)
    via route-model binding.
--}}
<x-layouts.admin
    title="Edit Student"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Student Management'], ['label' => 'Edit Student']]">
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('admin.students.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Student Management
            </a>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900">Edit Student</h2>
            <p class="mt-1 text-sm text-gray-500">
                Update {{ $student->first_name }} {{ $student->last_name }}'s record.
            </p>
        </div>

        @include('admin.students.partials._form', [
        'student' => $student,
        'isModal' => false,
        'cancelUrl' => route('admin.students.index'),
        'formAction' => route('admin.students.update', $student->id),
        'formMethod' => 'PUT',
        ])
    </div>
</x-layouts.admin>