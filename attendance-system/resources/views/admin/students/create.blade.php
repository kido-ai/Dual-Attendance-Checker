{{--
    admin/students/create.blade.php

    Full-page alternative to the "Add Student" modal on index.blade.php —
    useful for direct links/bookmarks. Shares the same _form partial.
--}}
<x-layouts.admin
    title="Add Student"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Student Management'], ['label' => 'Add Student']]">
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Student Management
            </a>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900">Add Student</h2>
            <p class="mt-1 text-sm text-gray-500">Create a new student record ahead of QR and Face ID enrollment.</p>
        </div>

        @include('admin.students.partials._form', [
        'student' => null,
        'isModal' => false,
        'cancelUrl' => route('admin.students.index'),
        'formAction' => route('admin.students.store'),
        'formMethod' => 'POST',
        ])
    </div>
</x-layouts.admin>