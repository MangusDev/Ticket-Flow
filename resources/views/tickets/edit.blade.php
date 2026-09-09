<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Editar ticket</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                            :value="old('title', $ticket->title)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descrição" />
                        <textarea id="description" name="description" rows="6"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>{{ old('description', $ticket->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
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
                            @foreach (['low' => 'Baixa', 'medium' => 'Média', 'high' => 'Alta'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('priority', $ticket->priority) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('priority')" />
                    </div>

                    @if (in_array(Auth::user()->role?->name, ['admin', 'operador'], true))
                        <div>
                            <x-input-label for="operator_id" value="Operador responsável" />
                            <select id="operator_id" name="operator_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Sem operador atribuído</option>
                                @foreach ($operators as $operator)
                                    <option value="{{ $operator->id }}" @selected(old('operator_id', $ticket->operator_id) == $operator->id)>
                                        {{ $operator->name }}{{ $operator->area ? ' · '.$operator->area->name : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('operator_id')" />
                        </div>

                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach (['open' => 'Aberto', 'in_progress' => 'Em andamento', 'closed' => 'Fechado'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $ticket->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div>
                            <x-input-label for="comment" value="Resposta ou comentário" />
                            <textarea id="comment" name="comment" rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Escreva uma resposta para o solicitante...">{{ old('comment') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                        </div>
                    @else
                        <input type="hidden" name="status" value="{{ $ticket->status }}">
                    @endif

                    <div class="flex gap-3">
                        <a href="{{ route('tickets.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar alterações</button>
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
                                    <p class="mt-1">Status: {{ $audit->previous_status }} &rarr; {{ $audit->new_status }}</p>
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
</x-app-layout>