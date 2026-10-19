<x-layouts.admin
    title="Teacher Management"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Teacher Management']]">
    <div
        x-data="{
            showAddModal: false,
            showArchiveModal: false,
            showDeleteModal: false,
            showRestoreModal: false,
            showAssignSubjectsModal: false,
            showAssignClassesModal: false,
            targetTeacher: null,
        }"
        class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Teacher Management</h2>
                <p class="mt-1 text-sm text-gray-500">Manage faculty information, employment records, teaching assignments, and account status.</p>
            </div>
            <a
                href="{{ Route::has('admin.teachers.archived') ? route('admin.teachers.archived') : '#' }}"
                class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="archive-box" class="h-4 w-4" />
                View Archived Teachers
            </a>
        </div>

        {{-- Statistics cards --}}
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

        {{-- Top action bar: search, filters, add teacher --}}
        @include('admin.teachers.partials._filters', [
        'statuses' => ['active', 'inactive', 'archived'],
        ])

        {{-- Teacher table --}}
        @include('admin.teachers.partials._table', ['teachers' => $teachers, 'mode' => 'active'])

        {{-- Add Teacher modal --}}
        <x-modal show="showAddModal" max-width="2xl">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Add Teacher</h3>
                    <p class="text-sm text-gray-500">Create a new faculty record</p>
                </div>
                <button type="button" @click="showAddModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto px-6 py-6">
                @include('admin.teachers.partials._form', [
                'teacher' => null,
                'isModal' => true,
                'formAction' => Route::has('admin.teachers.store') ? route('admin.teachers.store') : '#',
                'formMethod' => 'POST',
                ])
            </div>
        </x-modal>

        {{-- Archive confirmation modal --}}
        <x-modal show="showArchiveModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <x-icon name="archive-box" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Archive teacher?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    <span x-text="targetTeacher?.name"></span> (<span x-text="targetTeacher?.id"></span>) will be
                    moved to the archive and unassigned from active sessions. You can restore them at any time.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showArchiveModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="showArchiveModal = false" class="flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                        <x-icon name="archive-box" class="h-4 w-4" />
                        Archive
                    </button>
                </div>
            </div>
        </x-modal>

        {{-- Delete confirmation modal --}}
        <x-modal show="showDeleteModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <x-icon name="trash" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Delete teacher?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    This will permanently delete <span x-text="targetTeacher?.name"></span>
                    (<span x-text="targetTeacher?.id"></span>) and unassign them from every subject and class. This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="showDeleteModal = false" class="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        <x-icon name="trash" class="h-4 w-4" />
                        Delete Permanently
                    </button>
                </div>
            </div>
        </x-modal>
    </div>
</x-layouts.admin>