@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'ui-control block w-full px-3 py-2 text-sm text-ink-900 placeholder:text-ink-300']) }}>
