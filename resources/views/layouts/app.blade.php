<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-ink-50">
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-ink-950/40 lg:hidden" @click="sidebarOpen = false"></div>

            <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-300 transition-transform lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-sm font-black text-white">HD</div>
                    <div><p class="text-sm font-bold text-white">HelpDesk</p><p class="text-[11px] text-slate-400">Suporte interno</p></div>
                </div>

                <nav class="flex-1 space-y-1 px-3 py-6">
                    <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Navegação</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium transition hover:bg-white/10 hover:text-white {{ request()->routeIs('dashboard', 'admin.dashboard', 'operador.dashboard', 'solicitante.dashboard') ? 'bg-brand-600 text-white' : '' }}"><span class="w-5 text-center">▦</span>Dashboard</a>
                    <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium transition hover:bg-white/10 hover:text-white {{ request()->routeIs('tickets.index', 'tickets.show', 'tickets.edit') ? 'bg-white/10 text-white' : '' }}"><span class="w-5 text-center">▤</span>Meus Tickets</a>
                    <a href="{{ route('tickets.create') }}" class="mt-2 flex items-center gap-3 rounded-control px-3 py-2.5 text-sm font-medium transition hover:bg-white/10 hover:text-white {{ request()->routeIs('tickets.create') ? 'bg-white/10 text-white' : '' }}"><span class="w-5 text-center text-lg">+</span>Novo Ticket</a>
                </nav>

                <div class="border-t border-white/10 p-4">
                    <div class="mb-3 min-w-0"><p class="truncate text-sm font-semibold text-white">{{ Auth::user()->name }}</p><p class="truncate text-xs text-slate-400">{{ Auth::user()->email }}</p></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="w-full rounded-control px-3 py-2 text-left text-sm font-medium text-slate-400 transition hover:bg-white/10 hover:text-white">Sair da conta</button></form>
                </div>
            </aside>

            <div class="lg:pl-64">
                <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-ink-200 bg-white/95 px-4 backdrop-blur sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="rounded-control p-2 text-ink-500 hover:bg-ink-100 lg:hidden" @click="sidebarOpen = true" aria-label="Abrir menu">☰</button>
                        <div class="hidden text-xs text-ink-500 sm:block">Atendimento <span class="mx-1 text-ink-300">/</span> <span class="font-semibold text-ink-700">{{ str_replace('.', ' / ', request()->route()?->getName() ?? 'dashboard') }}</span></div>
                    </div>
                    <div class="flex items-center gap-3"><span class="hidden text-sm font-medium text-ink-700 sm:block">{{ Auth::user()->name }}</span><div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-700">{{ collect(explode(' ', Auth::user()->name))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') }}</div></div>
                </header>

                @isset($header)
                    <header class="border-b border-ink-200 bg-white"><div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">{{ $header }}</div></header>
                @endisset

                <main>
                    <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-6 lg:px-8"><x-toast type="success" :message="session('success')" /><x-toast type="error" :message="session('error')" /></div>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
