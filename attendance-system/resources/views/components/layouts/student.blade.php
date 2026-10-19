<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Student Dashboard' }} &middot; {{ $systemSettings['system_name'] }}</title>

    {{--
        Tailwind + Alpine.js are expected to be compiled through Vite
        (same build as the admin/teacher layouts — see README.md for setup).
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ sidebarOpen: false }"
    class="min-h-screen bg-[#F8FAFC] font-sans text-gray-900 antialiased">
    <!-- Sidebar (fixed, collapsible on mobile) -->
    <x-student.sidebar />

    <!-- Main column offset for the fixed sidebar on desktop -->
    <div class="flex min-h-screen flex-col lg:pl-72">

        <x-student.navbar :title="$title ?? 'Dashboard'" :breadcrumbs="$breadcrumbs ?? []" />

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        <x-footer />
    </div>

    {{--
        Profile Modal - Student\ProfileController deliberately only
        accepts photo + password (student_number, name, course, year,
        and section are admin-owned academic records). editable-fields
        reflects that: no name/email/phone/address inputs render here,
        by design, not by omission.
    --}}
    <x-profile-modal
        name="{{ auth()->user()->student?->full_name ?? auth()->user()->name }}"
        email="{{ auth()->user()->email }}"
        role="Student"
        role-tone="indigo"
        :avatar-url="auth()->user()->student?->profile_photo ? asset('storage/' . ltrim(auth()->user()->student->profile_photo, '/')) : (auth()->user()->profile_photo ? asset('storage/' . ltrim(auth()->user()->profile_photo, '/')) : null)"
        :member-since="auth()->user()->student?->created_at?->format('M j, Y')"
        :last-updated="auth()->user()->student?->updated_at?->format('M j, Y g:i A')"
        identifier-label="Student Number"
        :identifier-value="auth()->user()->student?->student_number"
        update-action="{{ route('student.profile.update') }}"
        :editable-fields="['name', 'email', 'photo']" />
</body>

</html>