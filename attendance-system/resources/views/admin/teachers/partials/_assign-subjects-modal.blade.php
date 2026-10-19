{{--
    admin/teachers/partials/_assign-subjects-modal.blade.php

    Included from index.blade.php. Controlled by the parent's
    `showAssignSubjectsModal` / `targetTeacher` Alpine state. Expects
    $subjectOptions (array of subject names) in scope.
--}}
<x-modal show="showAssignSubjectsModal" max-width="lg">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
        <div>
            <h3 class="text-base font-semibold text-gray-900">Assign Subjects</h3>
            <p class="text-sm text-gray-500">
                Choose the subjects <span class="font-medium text-gray-700" x-text="targetTeacher?.name"></span> will teach
            </p>
        </div>
        <button type="button" @click="showAssignSubjectsModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <x-icon name="x-mark" class="h-5 w-5" />
        </button>
    </div>

    <form method="POST" action="#" class="px-6 py-6">
        @csrf
        <div class="max-h-80 space-y-2 overflow-y-auto">
            @foreach ($subjectOptions as $subject)
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-100 px-4 py-3 transition-colors duration-150 hover:bg-gray-50 has-[:checked]:border-blue-200 has-[:checked]:bg-blue-50">
                    <input type="checkbox" name="subjects[]" value="{{ $subject }}" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-200">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <x-icon name="bookmark" class="h-4 w-4" />
                    </span>
                    <span class="text-sm font-medium text-gray-700">{{ $subject }}</span>
                </label>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-5">
            <button type="button" @click="showAssignSubjectsModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" @click="showAssignSubjectsModal = false" class="flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <x-icon name="check-circle" class="h-4 w-4" />
                Save Assignment
            </button>
        </div>
    </form>
</x-modal>
