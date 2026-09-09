@props(['tone' => 'neutral'])

@php
    $tones = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        'info' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        'neutral' => 'bg-ink-100 text-ink-700 ring-ink-500/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.$tones[$tone]]) }}>{{ $slot }}</span>