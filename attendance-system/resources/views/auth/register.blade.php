<x-layouts.guest title="Create Account">
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Get started</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Create your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Set up your sign-in details to access the Attendance Suite.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Full name" />
            <x-text-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Your full name"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email address" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                placeholder="name@example.com"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Create a password"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Enter your password again"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="rounded-xl bg-emerald-700 py-3 shadow-md shadow-emerald-700/15 hover:bg-emerald-800 focus:ring-emerald-200">
            Create account
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    @if (Route::has('login'))
    <p class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 transition hover:text-emerald-800">Sign in</a>
    </p>
    @endif
</x-layouts.guest>
