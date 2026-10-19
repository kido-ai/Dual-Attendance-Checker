{{--
    components/student/navbar.blade.php
--}}
@props([
'title' => 'Dashboard',
'breadcrumbs' => [],
])

@php
$navUser = auth()->user();
$navStudent = $navUser?->student;
$navName = $navStudent?->full_name ?? $navUser?->name ?? 'Student';

$photoPath = $navStudent?->profile_photo ?? $navUser?->profile_photo;
$navPhotoUrl = null;
if ($photoPath && Storage::disk('public')->exists($photoPath)) {
$navPhotoUrl = Storage::disk('public')->url($photoPath);
}
$navPhotoUrl ??= 'https://ui-avatars.com/api/?name='
. urlencode($navName) . '&background=059669&color=fff';
@endphp

<header class="sticky top-0 z-20 border-b border-gray-100 bg-white/80 backdrop-blur">
    <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">

        <!-- Mobile menu button -->
        <button
            @click="sidebarOpen = true"
            type="button"
            class="rounded-lg p-2 text-gray-500 hover:bg-gray-50 hover:text-gray-900 lg:hidden">
            <span class="sr-only">Open sidebar</span>
            <x-icon name="bars-3" class="h-6 w-6" />
        </button>

        <!-- Title + breadcrumb -->
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-lg font-semibold text-gray-900">{{ $title }}</h1>
            @if (count($breadcrumbs))
            <nav class="flex items-center gap-1.5 text-xs text-gray-500">
                @foreach ($breadcrumbs as $i => $crumb)
                @if ($i > 0)
                <x-icon name="chevron-right" class="h-3.5 w-3.5 text-gray-300" />
                @endif
                <span class="{{ $loop->last ? 'font-medium text-gray-600' : '' }}">{{ $crumb['label'] }}</span>
                @endforeach
            </nav>
            @endif
        </div>

        <!-- Date -->
        <div class="hidden items-center gap-2 rounded-xl border border-gray-100 px-3 py-1.5 text-xs font-medium text-gray-500 lg:flex">
            <x-icon name="calendar-days" class="h-4 w-4 text-gray-400" />
            {{ now()->format('l, d M Y') }}
        </div>

        <!-- Student profile dropdown -->
        <div class="relative" x-data="{ profileOpen: false }">
            <button
                @click="profileOpen = !profileOpen"
                type="button"
                class="flex items-center gap-2 rounded-xl border border-transparent py-1 pl-1 pr-2 hover:border-gray-100 hover:bg-gray-50">
                <img
                    src="{{ $navPhotoUrl }}"
                    alt="{{ $navName }}"
                    class="h-8 w-8 rounded-full object-cover">
                <span class="hidden text-sm font-medium text-gray-700 sm:block">{{ $navName }}</span>
                <x-icon name="chevron-down" class="hidden h-4 w-4 text-gray-400 sm:block" />
            </button>

            <div
                x-show="profileOpen"
                @click.outside="profileOpen = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-52 origin-top-right rounded-2xl border border-gray-100 bg-white p-1.5 shadow-lg"
                x-cloak>
                <button type="button" @click="profileOpen = false; $dispatch('open-profile-modal')" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <x-icon name="user-circle" class="h-5 w-5 text-gray-400" /> My Profile
                </button>
                <div class="my-1 border-t border-gray-100"></div>
                <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                        <x-icon name="logout" class="h-5 w-5" /> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>