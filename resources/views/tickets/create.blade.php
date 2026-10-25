<x-app-layout>
    <x-slot name="header">
        <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Atendimento</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-ink-950">Abrir ticket</h1></div>
    </x-slot>

    <div class="bg-ink-50 py-8"><div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <x-card class="p-6 sm:p-8">
            <div class="mb-8 border-b border-ink-200 pb-5"><h2 class="text-lg font-semibold text-ink-950">Conte-nos o que aconteceu</h2><p class="mt-1 text-sm text-ink-500">Preencha os dados abaixo para que nossa equipe possa ajudar.</p></div>
            <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
                @csrf
                <div><x-input-label for="title" value="Título" /><x-text-input id="title" name="title" type="text" class="mt-2" :value="old('title')" placeholder="Ex.: Não consigo acessar o sistema" required autofocus /><x-input-error class="mt-2" :messages="$errors->get('title')" /></div>
                <div><x-input-label for="description" value="Descrição" /><textarea id="description" name="description" rows="7" class="ui-control mt-2 block w-full px-3 py-2 text-sm" placeholder="Descreva o problema com o máximo de detalhes possível..." required>{{ old('description') }}</textarea><x-input-error class="mt-2" :messages="$errors->get('description')" /></div>
                <div class="grid gap-6 sm:grid-cols-2"><div><x-input-label for="area_id" value="Área responsável" /><select id="area_id" name="area_id" class="ui-control mt-2 block w-full px-3 py-2 text-sm" required><option value="">Selecione uma área</option>@foreach ($areas as $area)<option value="{{ $area->id }}" @selected(old('area_id') == $area->id)>{{ $area->name }}</option>@endforeach</select><x-input-error class="mt-2" :messages="$errors->get('area_id')" /></div><div><x-input-label for="priority" value="Prioridade" /><select id="priority" name="priority" class="ui-control mt-2 block w-full px-3 py-2 text-sm" required>@foreach (\App\Enums\TicketPriority::cases() as $priority)<option value="{{ $priority->value }}" @selected(old('priority', \App\Enums\TicketPriority::LOW->value) === $priority->value)>{{ $priority->label() }}</option>@endforeach</select><x-input-error class="mt-2" :messages="$errors->get('priority')" /></div></div>
                <input type="hidden" name="status" value="open">
                <div class="flex items-center justify-end gap-3 border-t border-ink-200 pt-6"><a href="{{ route('tickets.index') }}" class="rounded-control px-4 py-2 text-sm font-semibold text-ink-700 transition hover:bg-ink-100">Cancelar</a><x-button type="submit">Abrir ticket</x-button></div>
            </form>
        </x-card>
    </div></div>
</x-app-layout>