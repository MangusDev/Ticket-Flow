@props(['headers' => []])

<div {{ $attributes->merge(['class' => 'ui-card overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
            <thead class="bg-ink-50 text-xs uppercase tracking-wide text-ink-500">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-5 py-3.5 font-semibold">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>