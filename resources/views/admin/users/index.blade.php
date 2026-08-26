<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">
                Usuários
            </h2>

            <a
                href="{{ route('admin.users.create') }}"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Novo usuário
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                <table class="w-full">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="text-left p-4">Nome</th>
                            <th class="text-left p-4">E-mail</th>
                            <th class="text-left p-4">Role</th>
                            <th class="text-left p-4">Área</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($users as $user)

                            <tr class="border-t">

                                <td class="p-4">
                                    {{ $user->name }}
                                </td>

                                <td class="p-4">
                                    {{ $user->email }}
                                </td>

                                <td class="p-4">
                                    {{ $user->role->name ?? '-' }}
                                </td>

                                <td class="p-4">
                                    {{ $user->area->name ?? '-' }}
                                </td>

                                <td class="p-4">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="text-blue-600"
                                    >
                                        Editar
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="p-4 text-center">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>

        </div>
    </div>

</x-app-layout>