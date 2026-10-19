<x-layouts.guest title="Sign In">
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Welcome back</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Sign in to your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">Enter your account details to continue.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="role" value="Account type" />
            <select
                id="role"
                name="role"
                required
                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/10">
                <option value="">Choose your account type</option>
                <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                <option value="teacher" @selected(old('role') === 'teacher')>Teacher</option>
                <option value="student" @selected(old('role') === 'student')>Student</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email address" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="name@example.com"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-emerald-700 transition hover:text-emerald-800">
                    Forgot password?
                </a>
                @endif
            </div>
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
                class="mt-1.5 rounded-xl border-slate-200 px-3.5 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label class="flex items-center gap-2.5">
            <x-checkbox name="remember" />
            <span class="text-sm text-slate-600">Remember me</span>
        </label>

        <x-primary-button class="rounded-xl bg-emerald-700 py-3 shadow-md shadow-emerald-700/15 hover:bg-emerald-800 focus:ring-emerald-200">
            Sign in
            <x-icon name="arrow-right" class="h-4 w-4" />
        </x-primary-button>
    </form>

    @if (Route::has('register'))
    <p class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">
        New to the Attendance Suite?
        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 transition hover:text-emerald-800">Create an account</a>
    </p>
    @endif
</x-layouts.guest>
