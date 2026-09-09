<x-app-layout>
	<x-slot name="header">
		<div class="flex items-center justify-between">
			<h2 class="font-semibold text-xl text-gray-800">Áreas</h2>
			<a href="{{ route('admin.areas.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
				Nova área
			</a>
		</div>
	</x-slot>

	<div class="py-6">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			@foreach (['success' => 'green', 'error' => 'red'] as $key => $color)
				@if (session($key))
					<div class="bg-{{ $color }}-100 text-{{ $color }}-800 p-4 rounded mb-4">
						{{ session($key) }}
					</div>
				@endif
			@endforeach

			<div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
				<table class="w-full">
					<thead class="bg-gray-100">
						<tr>
							<th class="text-left p-4">Nome</th>
							<th class="text-left p-4">Usuários</th>
							<th class="text-left p-4">Tickets</th>
							<th class="text-left p-4">Ações</th>
						</tr>
					</thead>
					<tbody>
						@forelse ($areas as $area)
							<tr class="border-t">
								<td class="p-4">{{ $area->name }}</td>
								<td class="p-4">{{ $area->users_count }}</td>
								<td class="p-4">{{ $area->tickets_count }}</td>
								<td class="p-4 flex gap-3">
									<a href="{{ route('admin.areas.edit', $area) }}" class="text-blue-600">Editar</a>
									<form method="POST" action="{{ route('admin.areas.destroy', $area) }}">
										@csrf
										@method('DELETE')
										<button type="submit" class="text-red-600" onclick="return confirm('Excluir esta área?')">
											Excluir
										</button>
									</form>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="4" class="p-4 text-center">Nenhuma área encontrada.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="mt-4">{{ $areas->links() }}</div>
		</div>
	</div>
</x-app-layout>
