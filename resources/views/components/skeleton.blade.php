@props(['class' => 'h-4 w-full'])

<div {{ $attributes->merge(['class' => "animate-pulse rounded-control bg-ink-100 {$class}"]) }} aria-hidden="true"></div>