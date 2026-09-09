<x-app-layout>
	<x-slot name="header">
		<h2 class="font-semibold text-xl text-gray-800">Nova área</h2>
	</x-slot>

	<div class="py-6">
		<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white shadow-sm sm:rounded-lg p-6">
				<form method="POST" action="{{ route('admin.areas.store') }}">
					@csrf
					<label for="name" class="block font-medium mb-1">Nome</label>
					<input id="name" type="text" name="name" value="{{ old('name') }}"
						class="w-full border-gray-300 rounded" required autofocus>
					@error('name')
						<p class="text-red-600 text-sm mt-1">{{ $message }}</p>
					@enderror

					<div class="flex gap-3 mt-6">
						<a href="{{ route('admin.areas.index') }}" class="px-4 py-2 bg-gray-200 rounded">Cancelar</a>
						<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar área</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</x-app-layout>
