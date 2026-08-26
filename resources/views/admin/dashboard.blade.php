<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel Administrativo
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-semibold mb-4">
                    Administração
                </h3>

                <a
                    href="{{ route('admin.users.index') }}"
                    class="inline-block bg-blue-600 text-white px-4 py-2 rounded"
                >
                    Gerenciar usuários
                </a>

            </div>

        </div>
    </div>
</x-app-layout>