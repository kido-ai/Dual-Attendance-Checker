{{-- components/footer.blade.php --}}
<footer class="border-t border-gray-100 bg-white">
    <div class="mx-auto flex max-w-full flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-gray-400 sm:flex-row sm:px-6 lg:px-8">
        <p>&copy; {{ now()->year }} {{ $systemSettings['school_name'] }}. All rights reserved.</p>
        <div class="flex items-center gap-3">
            <span>{{ $systemSettings['system_name'] }}</span>
            <span class="text-gray-200">|</span>
            <span>v2.4.1</span>
        </div>
    </div>
</footer>