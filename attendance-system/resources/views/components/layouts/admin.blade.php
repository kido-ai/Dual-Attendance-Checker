<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} &middot; {{ $systemSettings['system_name'] }}</title>

    {{--
        Tailwind + Alpine.js are expected to be compiled through Vite.
        Add to resources/js/app.js:  import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();
        tailwind.config.js should scan ./resources/views/**/*.blade.php
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-[#F8FAFC] font-sans text-gray-900 antialiased">
    <!-- Sidebar (fixed, collapsible on mobile) -->
    <x-sidebar />

    <!-- Main column offset for the fixed sidebar on desktop -->
    <div class="flex min-h-screen flex-col lg:pl-72">

        <x-navbar :title="$title ?? 'Dashboard'" :breadcrumbs="$breadcrumbs ?? []" />

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <x-footer />
    </div>

    {{--
        Profile Modal — Admin's editable fields are limited to name/email
        because the User model (Admin has no separate profile table) only
        has those two columns. Admin\ProfileController isn't built yet in
        this codebase, so the update action is guarded with Route::has()
        the same way every other optional route reference in this project is.
    --}}
    <x-profile-modal
        name="{{ auth()->user()->name }}"
        email="{{ auth()->user()->email }}"
        role="Administrator"
        role-tone="emerald"
        :avatar-url="auth()->user()->profile_photo ? asset('storage/' . ltrim(auth()->user()->profile_photo, '/')) : null"
        :member-since="auth()->user()->created_at?->format('M j, Y')"
        :last-updated="auth()->user()->updated_at?->format('M j, Y g:i A')"
        update-action="{{ Route::has('admin.profile.update') ? route('admin.profile.update') : '#' }}"
        :editable-fields="['name', 'email', 'photo']" />
</body>

</html>