@props(['variant' => 'primary', 'type' => 'button', 'disabled' => false, 'tag' => 'button'])

@php
    $classes = [
        'primary' => 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-600/30',
        'secondary' => 'border border-ink-200 bg-white text-ink-700 hover:bg-ink-50 focus:ring-brand-600/20',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600/30',
        'ghost' => 'text-ink-700 hover:bg-ink-100 focus:ring-brand-600/20',
    ][$variant] ?? 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-600/30';
@endphp

@if ($tag === 'a')
    <a {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-control px-3.5 py-2 text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 {$classes}"]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($disabled) {{ $attributes->merge(['class' => "inline-flex items-center justify-center gap-2 rounded-control px-3.5 py-2 text-sm font-semibold shadow-sm transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {$classes}"]) }}>{{ $slot }}</button>
@endif