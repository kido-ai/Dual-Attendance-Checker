<div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm" x-data="{ filtersOpen: {{ request()->hasAny(['search','teacher_id','attendance_status','qr_status','face_status','date']) ? 'true' : 'false' }} }">
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <label class="relative block w-full lg:max-w-sm">
                <span class="sr-only">Search attendance records</span>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search student, teacher, or remarks..."
                    class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2.5 pl-9 pr-3 text-sm placeholder:text-gray-400 focus:border-emerald-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </label>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition-colors duration-150 hover:bg-gray-50"
                    x-bind:class="filtersOpen && 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <x-icon name="funnel" class="h-4 w-4" />
                    Filters
                    <x-icon name="chevron-down" class="h-4 w-4 transition-transform duration-150" x-bind:class="filtersOpen && 'rotate-180'" />
                </button>

                <a
                    href="{{ route('admin.attendance.index') }}"
                    class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600">
                    <x-icon name="arrow-path" class="h-4 w-4" />
                    Reset Filters
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('admin.reports.pdf', request()->query()) }}"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
                    <x-icon name="document-arrow-down" class="h-4 w-4" />
                    Export PDF
                </a>
                <a
                    href="{{ route('admin.reports.excel', request()->query()) }}"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 transition-colors duration-150 hover:bg-gray-50">
                    <x-icon name="table-cells" class="h-4 w-4" />
                    Export Excel
                </a>
                <button
                    type="submit"
                    class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700">
                    <x-icon name="arrow-path" class="h-4 w-4" />
                    Refresh Records
                </button>
            </div>
        </div>

        <div
            x-show="filtersOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-4 grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Teacher</label>
                <select name="teacher_id" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Teachers</option>
                    @foreach ($teachers as $teacher)
                    <option value="{{ $teacher['id'] }}" {{ request('teacher_id') == $teacher['id'] ? 'selected' : '' }}>{{ $teacher['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Attendance Status</label>
                <select name="attendance_status" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Statuses</option>
                    @foreach ($attendanceStatuses as $status)
                    <option value="{{ strtolower($status) }}" {{ request('attendance_status') == strtolower($status) ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">QR Verification</label>
                <select name="qr_status" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All QR Statuses</option>
                    @foreach ($qrStatuses as $status)
                    <option value="{{ strtolower($status) }}" {{ request('qr_status') == strtolower($status) ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Face Verification</label>
                <select name="face_status" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Face Statuses</option>
                    @foreach ($faceStatuses as $status)
                    <option value="{{ strtolower($status) }}" {{ request('face_status') == strtolower($status) ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Date</label>
                <input
                    type="date"
                    name="date"
                    value="{{ request('date') }}"
                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
        </div>
    </form>
</div>