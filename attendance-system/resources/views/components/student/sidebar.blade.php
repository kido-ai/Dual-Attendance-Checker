{{--
    components/student/sidebar.blade.php
    Referenced as <x-student.sidebar />.
--}}
@php
$navSections = [
[
'label' => 'Overview',
'items' => [
['label' => 'Dashboard', 'icon' => 'home', 'route' => 'student.dashboard'],
['label' => 'My QR Code', 'icon' => 'qr-code', 'route' => 'student.qr.index', 'active' => 'student.qr.*'],
],
],
[
'label' => 'Attendance',
'items' => [
['label' => 'Attendance History', 'icon' => 'clipboard-check', 'route' => 'student.attendance.index', 'active' => 'student.attendance.*'],
],
],
];

// ---- Real user + student data ---------------------------------------
$authUser = auth()->user();
$student = $authUser?->student; // Student model (or null)
$studentName = $student?->full_name ?? $authUser?->name ?? 'Student';

// Build "BSIT 3-A · 2023-01452" style meta line from real columns.
$courseLine = trim(implode(' ', array_filter([
$student?->course,
$student?->year_level,
])));
if ($student?->section) {
$courseLine .= '-' . $student->section;
}
$metaLine = collect([$courseLine, $student?->student_number])
->filter(fn ($v) => filled($v))
->implode(' · ');

// Prefer student's photo, fall back to user's, fall back to ui-avatars.
$photoPath = $student?->profile_photo ?? $authUser?->profile_photo;
$studentPhotoUrl = null;
if ($photoPath && Storage::disk('public')->exists($photoPath)) {
$studentPhotoUrl = Storage::disk('public')->url($photoPath);
}
$studentPhotoUrl ??= 'https://ui-avatars.com/api/?name='
. urlencode($studentName) . '&background=059669&color=fff';
@endphp

<!-- Mobile overlay -->
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"
    x-cloak></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex h-full w-[85vw] max-w-[18rem] flex-col border-r border-gray-100 bg-white shadow-2xl transition-transform duration-300 ease-in-out lg:w-72 lg:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <!-- Brand -->
    <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-gray-100 px-4 sm:px-6">
        <div class="flex min-w-0 flex-1 items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-50 text-emerald-700 shadow-sm shadow-emerald-100">
                @if (!empty($systemSettings['school_logo']) && Storage::disk('public')->exists($systemSettings['school_logo']))
                <img src="{{ Storage::disk('public')->url($systemSettings['school_logo']) }}" class="h-full w-full object-contain p-1" alt="{{ $systemSettings['school_name'] }} logo">
                @else
                <x-icon name="building-library" class="h-5 w-5" />
                @endif
            </div>
            <div class="min-w-0 leading-tight">
                <p class="truncate text-sm font-semibold text-gray-900">{{ $systemSettings['school_name'] }}</p>
                <p class="truncate text-xs text-gray-500">{{ $systemSettings['system_name'] }}</p>
            </div>
        </div>

        <button
            type="button"
            @click="sidebarOpen = false"
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 lg:hidden"
            aria-label="Close sidebar">
            <x-icon name="x-mark" class="h-5 w-5" />
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5 sm:px-4 sm:py-6">
        @foreach ($navSections as $section)
        <div>
            <p class="px-2 text-xs font-semibold uppercase tracking-wider text-gray-400">
                {{ $section['label'] }}
            </p>
            <div class="mt-2 space-y-1">
                @foreach ($section['items'] as $item)
                @php $active = request()->routeIs($item['active'] ?? $item['route']); @endphp
                <a
                    href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                    @click="sidebarOpen = false"
                    class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150
                                {{ $active
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="absolute left-0 h-6 w-1 rounded-r-full bg-emerald-600 transition-opacity duration-150 {{ $active ? 'opacity-100' : 'opacity-0' }}"></span>

                    <x-icon
                        :name="$item['icon']"
                        class="h-5 w-5 shrink-0 transition-colors duration-150 {{ $active ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600' }}" />
                    <span class="truncate text-left">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </nav>

    <!-- Student profile card + logout -->
    <div class="shrink-0 border-t border-gray-100 p-4">
        <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-3">
            <div class="flex items-center gap-3">
                <img
                    src="{{ $studentPhotoUrl }}"
                    alt="{{ $studentName }}"
                    class="h-10 w-10 rounded-full object-cover ring-2 ring-white">
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ $studentName }}</p>
                    @if ($metaLine)
                    <p class="truncate text-xs text-gray-500">{{ $metaLine }}</p>
                    @endif
                </div>
            </div>
        </div>
        <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="mt-1">
            @csrf
            <button
                type="submit"
                class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 transition-colors duration-150 hover:bg-red-50 hover:text-red-600">
                <x-icon name="logout" class="h-5 w-5" />
                Logout
            </button>
        </form>
    </div>
</aside>