<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Área do solicitante</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold">Olá, {{ Auth::user()->name }}</h3>
                    <p class="mt-2 text-gray-600">Aqui você acompanha os tickets que abriu.</p>
                    <div class="mt-5 flex gap-3">
                        <a href="{{ route('tickets.index') }}" class="px-4 py-2 bg-gray-200 rounded">Meus tickets</a>
                        <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Abrir ticket</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>