@props(['type' => 'success', 'message' => null])

@php
    $styles = [
        'success' => ['wrap' => 'border-emerald-200 bg-emerald-50 text-emerald-800', 'icon' => '✓'],
        'error' => ['wrap' => 'border-red-200 bg-red-50 text-red-800', 'icon' => '!'],
        'info' => ['wrap' => 'border-brand-200 bg-brand-50 text-brand-700', 'icon' => 'i'],
    ][$type] ?? ['wrap' => 'border-ink-200 bg-ink-50 text-ink-700', 'icon' => 'i'];
@endphp

@if ($message)
    <div x-data="{ visible: true }" x-show="visible" x-transition.opacity.duration.200ms role="status" class="mb-4 flex items-start gap-3 rounded-control border px-4 py-3 text-sm shadow-sm {{ $styles['wrap'] }}">
        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/70 text-xs font-bold">{{ $styles['icon'] }}</span>
        <p class="flex-1">{{ $message }}</p>
        <button type="button" @click="visible = false" class="font-semibold opacity-60 hover:opacity-100" aria-label="Fechar mensagem">&times;</button>
    </div>
@endif