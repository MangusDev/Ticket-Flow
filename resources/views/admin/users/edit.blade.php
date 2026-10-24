<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Editar usuário
        </h2>
    </x-slot>

    <div class="py-6">

        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form
                    method="POST"
                    action="{{ route('admin.users.update', $user) }}"
                >

                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block font-medium mb-1">
                            Nome
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="w-full border-gray-300 rounded"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium mb-1">
                            E-mail
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="w-full border-gray-300 rounded"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium mb-1">
                            Nova senha
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="w-full border-gray-300 rounded"
                        >

                        <p class="text-sm text-gray-500 mt-1">
                            Deixe vazio para manter a senha atual.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium mb-1">
                            Confirmar nova senha
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="w-full border-gray-300 rounded"
                        >
                    </div>

                    <div class="mb-4">

                        <label class="block font-medium mb-1">
                            Role
                        </label>

                        <select
                            name="role_id"
                            class="w-full border-gray-300 rounded"
                            required
                        >

                            @foreach($roles as $role)

                                <option
                                    value="{{ $role->id }}"
                                    data-role-name="{{ $role->name }}"
                                    @selected(old('role_id', $user->role_id) == $role->id)
                                >
                                    {{ ucfirst($role->name) }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mb-6">

                        <label class="block font-medium mb-1">
                            Área (obrigatória para operadores e solicitantes)
                        </label>

                        <select
                            name="area_id"
                            id="area_id"
                            class="w-full border-gray-300 rounded"
                        >
                            <option value="" @selected(old('area_id', $user->area_id) === null)>
                                Sem área (somente administrador)
                            </option>

                            @foreach($areas as $area)

                                <option
                                    value="{{ $area->id }}"
                                    @selected(old('area_id', $user->area_id) == $area->id)
                                >
                                    {{ $area->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="flex gap-3">

                        <a
                            href="{{ route('admin.users.index') }}"
                            class="px-4 py-2 bg-gray-200 rounded"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded"
                        >
                            Salvar alterações
                        </button>

                    </div>

                </form>

                <script>
                    const roleSelect = document.querySelector('select[name="role_id"]');
                    const areaSelect = document.getElementById('area_id');

                    function updateAreaRequirement() {
                        const selectedRole = roleSelect.options[roleSelect.selectedIndex];
                        const isAdmin = selectedRole?.dataset.roleName === 'admin';
                        areaSelect.required = !isAdmin;

                        if (isAdmin) {
                            areaSelect.value = '';
                        }
                    }

                    roleSelect.addEventListener('change', updateAreaRequirement);
                    updateAreaRequirement();
                </script>

            </div>

        </div>

    </div>

</x-app-layout>