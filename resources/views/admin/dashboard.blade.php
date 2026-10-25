<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Administração</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-ink-950">Painel administrativo</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="rounded-control bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Gerenciar usuários</a>
                <a href="{{ route('admin.areas.index') }}" class="rounded-control border border-ink-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-50">Gerenciar áreas</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-ink-50 py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-ink-200">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="start_date" value="De" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1" :value="$startDate" required />
                        <x-input-error class="mt-1" :messages="$errors->get('start_date')" />
                    </div>
                    <div>
                        <x-input-label for="end_date" value="Até" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1" :value="$endDate" required />
                        <x-input-error class="mt-1" :messages="$errors->get('end_date')" />
                    </div>
                    <x-button type="submit">Aplicar período</x-button>
                    <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="rounded-control border border-ink-200 bg-white px-4 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-50">Exportar CSV</a>
                </form>
                <p class="mt-3 text-xs text-ink-500">Relatórios de tickets consideram a data de criação; a média de resolução considera tickets fechados no período selecionado.</p>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Tickets no período', 'value' => $metrics['total'], 'tone' => 'text-ink-950'],
                    ['label' => 'Abertos', 'value' => $metrics['open'], 'tone' => 'text-brand-600'],
                    ['label' => 'Em andamento', 'value' => $metrics['in_progress'], 'tone' => 'text-amber-600'],
                    ['label' => 'Fechados', 'value' => $metrics['closed'], 'tone' => 'text-emerald-600'],
                    ['label' => 'Média até resolução', 'value' => $metrics['average_resolution_hours'] === null ? '—' : $metrics['average_resolution_hours'].' h', 'tone' => 'text-violet-600'],
                    ['label' => 'Usuários ativos / inativos', 'value' => $metrics['active_users'].' / '.$metrics['inactive_users'], 'tone' => 'text-ink-700'],
                ] as $metric)
                    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-ink-200">
                        <p class="text-sm font-medium text-ink-500">{{ $metric['label'] }}</p>
                        <p class="mt-3 text-2xl font-bold {{ $metric['tone'] }}">{{ $metric['value'] }}</p>
                    </div>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                    <h2 class="text-lg font-semibold text-ink-950">Tickets por área</h2>
                    <div class="mt-4 space-y-4">
                        @forelse ($areas as $area)
                            <div>
                                <div class="mb-1 flex justify-between gap-4 text-sm">
                                    <span class="truncate text-ink-700">{{ $area->name }}</span>
                                    <span class="font-semibold text-ink-900">{{ $area->tickets_count }}</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-ink-100">
                                    <div class="h-full rounded-full bg-brand-500" style="width: {{ $metrics['total'] ? min(100, $area->tickets_count / $metrics['total'] * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-ink-500">Nenhuma área cadastrada.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                    <h2 class="text-lg font-semibold text-ink-950">Tickets por operador</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-ink-200 text-xs uppercase text-ink-500">
                                <tr><th class="py-2 pr-4">Operador</th><th class="py-2 pr-4">Área</th><th class="py-2 text-right">Atribuídos</th></tr>
                            </thead>
                            <tbody class="divide-y divide-ink-100">
                                @forelse ($operators as $operator)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-ink-800">{{ $operator->name }} @unless($operator->is_active)<span class="text-xs text-red-600">(inativo)</span>@endunless</td>
                                        <td class="py-3 pr-4 text-ink-600">{{ $operator->area?->name ?? '—' }}</td>
                                        <td class="py-3 text-right font-semibold text-ink-900">{{ $operator->tickets_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-4 text-ink-500">Nenhum operador cadastrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-ink-950">Trilha de auditoria administrativa</h2>
                        <p class="mt-1 text-sm text-ink-500">Criação, edição, ativação, desativação e exclusão de usuários e áreas.</p>
                    </div>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($audits as $audit)
                        <article class="rounded-xl border border-ink-200 bg-ink-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-ink-900">{{ $audit->action_label }}</p>
                                    <p class="mt-1 text-sm text-ink-700">{{ $audit->subject_type }}: {{ $audit->subject_label }}</p>
                                    <p class="mt-1 text-xs text-ink-500">Por {{ $audit->actor_name }} · {{ $audit->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if ($audit->changes)
                                    <details class="max-w-full text-xs text-ink-600">
                                        <summary class="cursor-pointer font-semibold">Detalhes</summary>
                                        <pre class="mt-2 max-w-xl overflow-x-auto whitespace-pre-wrap rounded bg-white p-3">{{ json_encode($audit->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-ink-50 p-4 text-sm text-ink-500">Nenhuma ação administrativa registrada neste período.</p>
                    @endforelse
                </div>
                <div class="mt-5">{{ $audits->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
