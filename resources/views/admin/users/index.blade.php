<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-wide text-ink-500">Administração</p><h2 class="mt-1 text-xl font-semibold text-ink-950">Usuários</h2></div>
            <x-button tag="a" href="{{ route('admin.users.create') }}">Novo usuário</x-button>
        </div>
    </x-slot>

    <div class="bg-ink-50 py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-data-table :headers="['Nome', 'E-mail', 'Perfil', 'Área', 'Status', 'Ações']">
            @forelse ($users as $user)
                <tr class="transition-colors hover:bg-ink-50">
                    <td class="px-5 py-4 font-semibold text-ink-900">{{ $user->name }}</td>
                    <td class="px-5 py-4 text-ink-700">{{ $user->email }}</td>
                    <td class="px-5 py-4"><x-badge :tone="$user->role?->name === 'admin' ? 'info' : 'neutral'">{{ $user->role->name ?? '-' }}</x-badge></td>
                    <td class="px-5 py-4 text-ink-700">{{ $user->area->name ?? '-' }}</td>
                    <td class="px-5 py-4">
                        <x-badge :tone="$user->is_active ? 'success' : 'danger'">
                            {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                        </x-badge>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-semibold text-brand-600 hover:text-brand-700">Editar</a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Deseja desativar este usuário?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-semibold text-red-600 hover:text-red-700">
                                    {{ $user->is_active ? 'Desativar' : 'Inativo' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center"><p class="font-semibold text-ink-700">Nenhum usuário encontrado</p><p class="mt-1 text-sm text-ink-500">Cadastre um usuário para começar.</p></td></tr>
            @endforelse
        </x-data-table>
        <div class="mt-4">{{ $users->links() }}</div>
    </div></div>
</x-app-layout>
