{{--
    admin/teachers/create.blade.php

    Full-page alternative to the "Add Teacher" modal on index.blade.php —
    useful for direct links/bookmarks. Shares the same _form partial.
--}}
<x-layouts.admin
    title="Add Teacher"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Teacher Management'], ['label' => 'Add Teacher']]"
>
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Teacher Management
            </a>
            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-gray-900">Add Teacher</h2>
            <p class="mt-1 text-sm text-gray-500">Create a new faculty record ahead of subject and class assignment.</p>
        </div>

        @include('admin.teachers.partials._form', [
            'teacher' => null,
            'isModal' => false,
            'cancelUrl' => route('admin.teachers.index'),
            'formAction' => Route::has('admin.teachers.store') ? route('admin.teachers.store') : '#',
            'formMethod' => 'POST',
        ])
    </div>
</x-layouts.admin>
