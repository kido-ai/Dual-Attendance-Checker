<x-layouts.admin
    title="Archived Teachers"
    :breadcrumbs="[['label' => 'Dashboard'], ['label' => 'Teacher Management'], ['label' => 'Archived']]">
    <div
        x-data="{ showRestoreModal: false, showDeleteModal: false, targetTeacher: null }"
        class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <a href="{{ route('admin.teachers.index') }}" class="flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-700">
                <x-icon name="chevron-right" class="h-4 w-4 rotate-180" />
                Back to Teacher Management
            </a>
            <div class="text-left sm:text-right">
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Archived Teachers</h2>
                <p class="mt-1 text-sm text-gray-500">Faculty records removed from active management. Restore or permanently delete.</p>
            </div>
        </div>

        @include('admin.teachers.partials._table', ['teachers' => $archivedTeachers, 'mode' => 'archived'])

        {{-- Restore confirmation modal --}}
        <x-modal show="showRestoreModal" max-width="sm">
            <div class="p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <x-icon name="archive-box-restore" class="h-6 w-6" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-900">Restore teacher?</h3>
                <p class="mt-1.5 text-sm text-gray-500">
                    <span x-text="targetTeacher?.name"></span> (<span x-text="targetTeacher?.id"></span>) will be
                    moved back to the active Teacher Management list.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="showRestoreModal = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" @click="showRestoreModal = false" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <x-icon name="archive-box-restore" class="h-4 w-4" />
                        Restore
                    </button>
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
                    <span x-text="targetTeacher?.name"></span> (<span x-text="targetTeacher?.id"></span>) and all
                    associated teaching records will be permanently removed. This action cannot be undone.
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