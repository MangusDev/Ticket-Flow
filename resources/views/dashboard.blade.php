@php
    $statusLabels = ['open' => 'Aberto', 'in_progress' => 'Em andamento', 'closed' => 'Fechado'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Visão geral</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-ink-950">Dashboard</h1></div>
    </x-slot>

    <div class="bg-ink-50 py-8"><div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
        <div><p class="text-2xl font-bold text-ink-950">Olá, {{ Auth::user()->name }}.</p><p class="mt-1 text-sm text-ink-500">Acompanhe o andamento das suas solicitações.</p></div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['label' => 'Total de tickets', 'value' => $metrics['total'], 'tone' => 'text-ink-950'], ['label' => 'Tickets abertos', 'value' => $metrics['open'], 'tone' => 'text-brand-600'], ['label' => 'Em andamento', 'value' => $metrics['in_progress'], 'tone' => 'text-amber-600'], ['label' => 'Concluídos', 'value' => $metrics['closed'], 'tone' => 'text-emerald-600']] as $metric)
                <div class="ui-card p-5"><p class="text-sm font-medium text-ink-500">{{ $metric['label'] }}</p><p class="mt-3 text-3xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p></div>
            @endforeach
        </div>

        <x-card class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-ink-200 px-5 py-4"><div><h2 class="font-semibold text-ink-950">Tickets recentes</h2><p class="mt-1 text-sm text-ink-500">Suas últimas solicitações abertas.</p></div><a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Ver todos</a></div>
            @forelse ($recentTickets as $ticket)
                <a href="{{ route('tickets.edit', $ticket) }}" class="flex items-center justify-between gap-4 border-b border-ink-100 px-5 py-4 transition hover:bg-ink-50 last:border-0"><div class="min-w-0"><p class="truncate font-semibold text-ink-900">{{ $ticket->title }}</p><p class="mt-1 text-xs text-ink-500">SUP-{{ $ticket->id }} · {{ $ticket->created_at->format('d/m/Y H:i') }}</p></div><x-badge :tone="['open' => 'info', 'in_progress' => 'warning', 'closed' => 'success'][$ticket->status] ?? 'neutral'">{{ $statusLabels[$ticket->status] ?? $ticket->status }}</x-badge></a>
            @empty
                <div class="ui-empty border-0">Você ainda não abriu nenhum ticket.</div>
            @endforelse
        </x-card>
    </div></div>
</x-app-layout>
