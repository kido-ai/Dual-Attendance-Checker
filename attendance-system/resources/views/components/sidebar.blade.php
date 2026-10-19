{{--
    components/sidebar.blade.php
    Admin sidebar. Expects a parent x-data scope exposing `sidebarOpen`.
--}}
@php
$navSections = [
[
'label' => 'Overview',
'items' => [
['label' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard'],
['label' => 'Settings', 'icon' => 'cog', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
],
],
[
'label' => 'People',
'items' => [
['label' => 'Student Management', 'icon' => 'academic-cap', 'route' => 'admin.students.index', 'active' => 'admin.students.*'],
['label' => 'Teacher Management', 'icon' => 'briefcase', 'route' => 'admin.teachers.index', 'active' => 'admin.teachers.*'],
],
],
[
'label' => 'Attendance',
'items' => [
['label' => 'Attendance Records', 'icon' => 'calendar-days', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*'],
],
],
];

// ---- Real user data --------------------------------------------------
$adminUser = auth()->user();
$adminName = $adminUser?->name ?? 'Administrator';
$adminEmail = $adminUser?->email ?? '';
$adminInitials = \Illuminate\Support\Str::of($adminName)
->explode(' ')->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');

// Prefer the admin's own profile_photo if the User model has one,
// otherwise fall back to the generated ui-avatars image.
$adminPhotoUrl = null;
if ($adminUser?->profile_photo
&& Storage::disk('public')->exists($adminUser->profile_photo)) {
$adminPhotoUrl = asset('storage/' . ltrim($adminUser->profile_photo, '/'));
}
$adminPhotoUrl ??= 'https://ui-avatars.com/api/?name='
. urlencode($adminName) . '&background=059669&color=fff';
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
    <!-- School logo / brand -->
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

    <!-- User profile + logout -->
    <div class="shrink-0 border-t border-gray-100 p-4">
        <div class="flex items-center gap-3 rounded-xl px-2 py-2">
            <img
                src="{{ $adminPhotoUrl }}"
                alt="{{ $adminName }}"
                class="h-9 w-9 rounded-full object-cover ring-2 ring-emerald-50">
            <div class="min-w-0 flex-1 leading-tight">
                <p class="truncate text-sm font-semibold text-gray-900">{{ $adminName }}</p>
                <p class="truncate text-xs text-gray-500">{{ $adminEmail }}</p>
            </div>
        </div>
        <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="mt-1">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 transition-colors duration-150 hover:bg-red-50 hover:text-red-600">
                <x-icon name="logout" class="h-5 w-5" />
                Logout
            </button>
        </form>
    </div>
</aside>