<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Detalhes do ticket</p>
                <h2 class="mt-1 text-xl font-semibold text-ink-950">{{ $ticket->title }}</h2>
            </div>
            <a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Voltar</a>
        </div>
    </x-slot>

    <div class="bg-ink-50 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Título</p>
                        <p class="mt-2 text-lg font-semibold text-ink-900">{{ $ticket->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Solicitante</p>
                        <p class="mt-2 text-lg font-semibold text-ink-900">{{ $ticket->requester?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Área</p>
                        <p class="mt-2 text-ink-800">{{ $ticket->area?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Operador responsável</p>
                        <p class="mt-2 text-ink-800">{{ $ticket->operator?->name ?? 'Sem operador atribuído' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Status</p>
                        <p class="mt-2 text-ink-800">{{ \App\Enums\TicketStatus::labelFor($ticket->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Prioridade</p>
                        <p class="mt-2 text-ink-800">{{ \App\Enums\TicketPriority::labelFor($ticket->priority) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Data de criação</p>
                        <p class="mt-2 text-ink-800">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Última atualização</p>
                        <p class="mt-2 text-ink-800">{{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="mt-8 border-t border-ink-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Descrição</p>
                    <p class="mt-3 whitespace-pre-wrap text-ink-800">{{ $ticket->description }}</p>
                </div>
            </div>

            @if ($ticket->status === \App\Enums\TicketStatus::CLOSED->value)
                <div class="mt-6">
                    @if (Auth::user()->role?->name === 'solicitante' && Auth::id() === $ticket->user_id)
                        <x-toast type="info" message="Este ticket está fechado e não pode receber novas respostas." />
                    @endif

                    @can('reopen', $ticket)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                            <p class="font-semibold text-amber-900">Ticket fechado</p>
                            <p class="mt-1 text-sm text-amber-800">Reabra o ticket para continuar o atendimento e permitir novas respostas.</p>
                            <form method="POST" action="{{ route('tickets.reopen', $ticket) }}" class="mt-4">
                                @csrf
                                @method('PATCH')
                                <x-button type="submit">Reabrir ticket</x-button>
                            </form>
                        </div>
                    @endcan
                </div>
            @endif

            <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                <h3 class="text-lg font-semibold text-ink-900">Histórico de respostas e comentários</h3>

                @if ($ticket->audits->isNotEmpty())
                    <div class="mt-5 space-y-4">
                        @foreach ($ticket->audits as $audit)
                            <div class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="font-semibold text-ink-900">{{ $audit->user?->name ?? 'Sistema' }}</p>
                                    <span class="text-xs text-ink-500">{{ $audit->created_at->format('d/m/Y H:i') }}</span>
                                </div>

                                @if ($audit->previous_status !== $audit->new_status)
                                    <p class="mt-2 text-sm text-ink-700">
                                        Status: {{ \App\Enums\TicketStatus::labelFor($audit->previous_status) }} → {{ \App\Enums\TicketStatus::labelFor($audit->new_status) }}
                                    </p>
                                @endif

                                @if ($audit->comment)
                                    <p class="mt-2 whitespace-pre-wrap text-sm text-ink-700">{{ $audit->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-ink-500">Ainda não há respostas ou comentários registrados para este ticket.</p>
                @endif
            </div>

            @if (Auth::user()->role?->name === 'solicitante' && Auth::id() === $ticket->user_id && $ticket->status !== \App\Enums\TicketStatus::CLOSED->value)
                <div class="mt-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                    <h3 class="text-lg font-semibold text-ink-900">Responder ao ticket</h3>
                    <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="comment" value="Resposta ou comentário" />
                            <textarea id="comment" name="comment" rows="5" class="ui-control mt-2 block w-full px-3 py-2 text-sm" placeholder="Escreva uma resposta..." required>{{ old('comment') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                        </div>
                        <div class="flex justify-end">
                            <x-button type="submit">Enviar resposta</x-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
