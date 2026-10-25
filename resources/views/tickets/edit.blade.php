<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Editar ticket</h2>
    </x-slot>

    <div class="bg-ink-50 py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-ink-200 sm:p-8">
                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full bg-ink-100" :value="$ticket->title" readonly />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="6" class="ui-control mt-1 block w-full bg-ink-100" readonly>{{ $ticket->description }}</textarea>
                    </div>

                    <div>
                        <x-input-label for="area_id" value="Área" />
                        <select id="area_id" name="area_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" @selected(old('area_id', $ticket->area_id) == $area->id)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('area_id')" />
                    </div>

                    <div>
                        <x-input-label for="priority" value="Prioridade" />
                        <select id="priority" name="priority" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach (\App\Enums\TicketPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', $ticket->priority) === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
                    </div>

                    <div>
                        <x-input-label for="operator_id" value="Operador responsável" />
                        <select id="operator_id" name="operator_id" class="ui-control mt-1 block w-full px-3 py-2" >
                            <option value="">Sem operador atribuído</option>
                            @foreach ($operators as $operator)
                                <option value="{{ $operator->id }}" data-area-id="{{ $operator->area_id }}" @selected(old('operator_id', $ticket->operator_id) == $operator->id)>
                                    {{ $operator->name }}{{ $operator->area ? ' · '.$operator->area->name : '' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('operator_id')" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="ui-control mt-1 block w-full px-3 py-2" required>
                            @foreach (\App\Enums\TicketStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $ticket->status) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div>
                        <x-input-label for="comment" value="Resposta ou comentário" />
                        <textarea id="comment" name="comment" rows="4" class="ui-control mt-1 block w-full px-3 py-2" placeholder="Escreva uma resposta para o solicitante...">{{ old('comment') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                    </div>

                    <div class="flex gap-3 border-t border-ink-200 pt-6">
                        <a href="{{ route('tickets.index') }}" class="rounded-control px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-100">Cancelar</a>
                        <x-button type="submit">Salvar alterações</x-button>
                    </div>
                </form>
            </div>

            @if ($ticket->audits->isNotEmpty())
                <div class="mt-6 bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Histórico de movimentações</h3>
                    <div class="mt-4 space-y-4">
                        @foreach ($ticket->audits as $audit)
                            <div class="border-l-4 border-gray-200 pl-4">
                                <p class="text-sm text-gray-600">
                                    {{ $audit->user->name }} em {{ $audit->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if ($audit->previous_status !== $audit->new_status)
                                    <p class="mt-1">Status: {{ \App\Enums\TicketStatus::labelFor($audit->previous_status) }} &rarr; {{ \App\Enums\TicketStatus::labelFor($audit->new_status) }}</p>
                                @endif
                                @if ($audit->comment)
                                    <p class="mt-1">{{ $audit->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        const areaSelect = document.getElementById('area_id');
        const operatorSelect = document.getElementById('operator_id');

        function filterOperatorsByArea() {
            for (const option of operatorSelect.options) {
                if (!option.value) {
                    continue;
                }

                option.disabled = option.dataset.areaId !== areaSelect.value;
                option.hidden = option.disabled;
            }

            const selectedOperator = operatorSelect.selectedOptions[0];
            if (selectedOperator?.disabled) {
                operatorSelect.value = '';
            }
        }

        areaSelect.addEventListener('change', filterOperatorsByArea);
        filterOperatorsByArea();
    </script>
</x-app-layout>