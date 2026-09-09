<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 ease-in-out hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-600/30 focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
