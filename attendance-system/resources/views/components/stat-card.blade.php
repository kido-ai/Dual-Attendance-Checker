{{--
    components/stat-card.blade.php

    Props:
        icon        - icon name from x-icon
        label       - short description, e.g. "Total Students"
        value       - large headline number/string
        trend       - 'up' | 'down' | null
        trendValue  - e.g. "+4.2%"
        trendLabel  - e.g. "vs last week"
        tone        - tailwind color root: emerald | blue | indigo | amber
--}}
@props([
    'icon' => 'chart-bar',
    'label' => 'Metric',
    'value' => '0',
    'trend' => null,
    'trendValue' => null,
    'trendLabel' => 'vs last week',
    'tone' => 'emerald',
])

@php
    $toneMap = [
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'amber' => 'bg-amber-50 text-amber-600',
    ];
    $iconTone = $toneMap[$tone] ?? $toneMap['emerald'];
@endphp

<div class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
    <div class="flex items-start justify-between">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $iconTone }} transition-transform duration-200 group-hover:scale-105">
            <x-icon :name="$icon" class="h-6 w-6" />
        </div>

        @if ($trend)
            <span class="flex items-center gap-1 rounded-full px-2 py-1 text-xs font-semibold
                {{ $trend === 'up' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                <x-icon :name="$trend === 'up' ? 'trending-up' : 'trending-down'" class="h-3.5 w-3.5" />
                {{ $trendValue }}
            </span>
        @endif
    </div>

    <p class="mt-5 text-3xl font-semibold tracking-tight text-gray-900">{{ $value }}</p>
    <p class="mt-1 text-sm text-gray-500">{{ $label }}</p>

    @if ($trend)
        <p class="mt-3 text-xs text-gray-400">{{ $trendLabel }}</p>
    @endif
</div>
