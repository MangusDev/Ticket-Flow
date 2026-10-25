<?php

use App\Http\Controllers\TicketController;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->role?->name === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role?->name === 'operador') {
            return redirect()->route('operador.dashboard');
        }

        return redirect()->route('solicitante.dashboard');
    })->name('dashboard');

    Route::get('/operador/dashboard', function (Request $request) {
        $user = $request->user();
        $assignedTicketsCount = Ticket::where('operator_id', $user->id)->count();
        $areaTicketsCount = Ticket::where('area_id', $user->area_id)->count();

        return view('operador.dashboard', compact('assignedTicketsCount', 'areaTicketsCount'));
    })->middleware('role:operador')->name('operador.dashboard');

    Route::get('/solicitante/dashboard', function (Request $request) {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('dashboard', [
            'metrics' => [
                'total' => $tickets->count(),
                TicketStatus::OPEN->value => $tickets->where('status', TicketStatus::OPEN->value)->count(),
                TicketStatus::IN_PROGRESS->value => $tickets->where('status', TicketStatus::IN_PROGRESS->value)->count(),
                TicketStatus::CLOSED->value => $tickets->where('status', TicketStatus::CLOSED->value)->count(),
            ],
            'recentTickets' => $tickets->take(3),
        ]);
    })->middleware('role:solicitante')->name('solicitante.dashboard');

    Route::patch('tickets/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');
    Route::resource('tickets', TicketController::class);
});

Route::middleware(['auth', 'admin', 'active'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->except(['show']);

        Route::resource('areas', \App\Http\Controllers\Admin\AreaController::class)
            ->except(['show']);
        Route::patch('areas/{area}/toggle', [\App\Http\Controllers\Admin\AreaController::class, 'toggle'])
            ->name('areas.toggle');
    });

require __DIR__.'/auth.php';