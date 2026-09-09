<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Atendimento</p><h2 class="mt-1 text-xl font-semibold text-ink-950">{{ Auth::user()->role?->name === 'operador' ? 'Tickets disponíveis' : 'Meus tickets' }}</h2></div>
            <x-button tag="a" href="{{ route('tickets.create') }}">Novo ticket</x-button>
        </div>
    </x-slot>

    <div class="bg-ink-50 py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-data-table :headers="['Ticket', 'Área', 'Status', 'Prioridade', 'Ações']">
            @forelse ($tickets as $ticket)
                <tr class="transition-colors hover:bg-ink-50">
                    <td class="whitespace-nowrap px-5 py-4"><p class="font-semibold text-ink-900">{{ $ticket->title }}</p><p class="mt-0.5 text-xs text-ink-500">SUP-{{ $ticket->id }}</p></td>
                    <td class="px-5 py-4 text-ink-700">{{ $ticket->area->name }}</td>
                    <td class="px-5 py-4"><x-badge :tone="['open' => 'info', 'in_progress' => 'warning', 'closed' => 'success'][$ticket->status] ?? 'neutral'">{{ ['open' => 'Aberto', 'in_progress' => 'Em andamento', 'closed' => 'Fechado'][$ticket->status] ?? $ticket->status }}</x-badge></td>
                    <td class="px-5 py-4"><x-badge :tone="$ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'neutral')">{{ ucfirst($ticket->priority) }}</x-badge></td>
                    <td class="px-5 py-4"><a href="{{ route('tickets.edit', $ticket) }}" class="font-semibold text-brand-600 hover:text-brand-700">Editar</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center"><p class="font-semibold text-ink-700">Nenhum ticket encontrado</p><p class="mt-1 text-sm text-ink-500">Crie um novo ticket para iniciar o atendimento.</p></td></tr>
            @endforelse
        </x-data-table>
        <div class="mt-4">{{ $tickets->links() }}</div>
    </div></div>
</x-app-layout>
