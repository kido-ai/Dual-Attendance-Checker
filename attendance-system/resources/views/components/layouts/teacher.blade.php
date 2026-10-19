<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Teacher Dashboard' }} &middot; {{ $systemSettings['system_name'] }}</title>

    {{--
        Tailwind + Alpine.js are expected to be compiled through Vite
        (same build as the admin layout — see README.md for setup).
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-[#F8FAFC] font-sans text-gray-900 antialiased">
    <!-- Sidebar (fixed, collapsible on mobile) -->
    <x-teacher.sidebar />

    <!-- Main column offset for the fixed sidebar on desktop -->
    <div class="flex min-h-screen flex-col lg:pl-72">

        <x-teacher.navbar :title="$title ?? 'Dashboard'" :breadcrumbs="$breadcrumbs ?? []" />

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <x-footer />
    </div>

    {{--
        Profile Modal - Teacher\ProfileController deliberately only
        accepts email/phone/photo/password (employee_id, name, and
        employment status are admin-owned records). editable-fields
        matches that boundary exactly, so the modal never shows a field
        the backend would silently ignore.
    --}}
    <x-profile-modal
        name="{{ auth()->user()->teacher?->full_name ?? auth()->user()->name }}"
        email="{{ auth()->user()->teacher?->email ?? auth()->user()->email }}"
        :phone="auth()->user()->teacher?->phone"
        role="Teacher"
        role-tone="blue"
        :avatar-url="auth()->user()->teacher?->profile_photo ? asset('storage/' . ltrim(auth()->user()->teacher->profile_photo, '/')) : (auth()->user()->profile_photo ? asset('storage/' . ltrim(auth()->user()->profile_photo, '/')) : null)"
        :member-since="auth()->user()->teacher?->created_at?->format('M j, Y')"
        :last-updated="auth()->user()->teacher?->updated_at?->format('M j, Y g:i A')"
        identifier-label="Employee ID"
        :identifier-value="auth()->user()->teacher?->employee_id"
        update-action="{{ route('teacher.profile.update') }}"
        :editable-fields="['name', 'email', 'phone', 'photo']" />
</body>

</html>