<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Painel do operador</p>
                <span class="sr-only">Área do operador</span>
                <h2 class="mt-1 text-xl font-semibold text-slate-800">Olá, {{ Auth::user()->name }}</h2>
            </div>
            <a href="{{ route('tickets.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                Criar ticket
            </a>
        </div>
    </x-slot>

    <div class="bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('tickets.index', ['assigned' => 1]) }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Meus tickets</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $assignedTicketsCount }}</p>
                    <p class="mt-1 text-sm text-blue-600">Ver tickets atribuídos a mim</p>
                </a>
                <a href="{{ route('tickets.index') }}" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                    <p class="text-sm font-medium text-slate-500">Tickets da minha área</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $areaTicketsCount }}</p>
                    <p class="mt-1 text-sm text-blue-600">Ver tickets disponíveis na área</p>
                </a>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-800">Acesso rápido</h3>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="{{ route('tickets.index', ['assigned' => 1]) }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Meus tickets</a>
                    <a href="{{ route('tickets.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Tickets da área</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
