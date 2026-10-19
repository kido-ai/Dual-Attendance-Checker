{{--
    admin/students/partials/_filters.blade.php

    Included from index.blade.php. Expects $courses, $yearLevels,
    $sections, $statuses arrays in scope for the filter dropdowns.
    UI/placeholder only — no live filtering wired in.
--}}
<div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm" x-data="{ filtersOpen: {{ request()->hasAny(['search','course','year_level','section','status']) ? 'true' : 'false' }} }">
    <form method="GET" action="{{ route('admin.students.index') }}" class="space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <label class="relative block w-full lg:max-w-xs">
                <span class="sr-only">Search students</span>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search student..."
                    oninput="this.form.submit()"
                    class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2.5 pl-9 pr-3 text-sm placeholder:text-gray-400 focus:border-emerald-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </label>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="filtersOpen = !filtersOpen"
                    class="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition-colors duration-150 hover:bg-gray-50"
                    :class="filtersOpen && 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <x-icon name="funnel" class="h-4 w-4" />
                    Filters
                    <x-icon name="chevron-down" class="h-4 w-4 transition-transform duration-150" x-bind:class="filtersOpen && 'rotate-180'" />
                </button>

                <a href="{{ route('admin.students.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600">
                    <x-icon name="arrow-path" class="h-4 w-4" />
                    Reset Filters
                </a>
            </div>

            <button
                type="button"
                @click="showAddModal = true"
                class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-emerald-700 lg:w-auto">
                <x-icon name="user-plus" class="h-5 w-5" />
                Add Student
            </button>
        </div>

        <div
            x-show="filtersOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-4 grid grid-cols-1 gap-3 border-t border-gray-100 pt-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="filter-course" class="mb-1.5 block text-xs font-medium text-gray-500">Course</label>
                <select id="filter-course" name="course" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Courses</option>
                    @foreach ($courses as $course)
                    <option value="{{ $course }}" @selected(request('course')===$course)>{{ $course }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-year" class="mb-1.5 block text-xs font-medium text-gray-500">Year Level</label>
                <select id="filter-year" name="year_level" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Years</option>
                    @foreach ($yearLevels as $year)
                    <option value="{{ $year }}" @selected(request('year_level')===$year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-section" class="mb-1.5 block text-xs font-medium text-gray-500">Section</label>
                <select id="filter-section" name="section" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Sections</option>
                    @foreach ($sections as $section)
                    <option value="{{ $section }}" @selected(request('section')===$section)>{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-status" class="mb-1.5 block text-xs font-medium text-gray-500">Status</label>
                <select id="filter-status" name="status" onchange="this.form.submit()" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                    <option value="{{ strtolower($status) }}" @selected(request('status')===strtolower($status))>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
</div>