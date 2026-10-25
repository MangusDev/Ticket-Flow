<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminAudit;
use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketAudit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        [$start, $end] = $this->reportPeriod($request);
        $range = [$start, $end];

        $statusCounts = Ticket::query()
            ->whereBetween('created_at', $range)
            ->select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalTickets = (int) $statusCounts->sum();
        $areas = Area::query()
            ->withCount(['tickets' => fn (Builder $query) => $query->whereBetween('tickets.created_at', $range)])
            ->orderBy('name')
            ->get();

        $operatorRoleId = Role::where('name', 'operador')->value('id');
        $operators = User::query()
            ->with('area')
            ->when($operatorRoleId, fn (Builder $query) => $query->where('role_id', $operatorRoleId), fn (Builder $query) => $query->whereKey(0))
            ->withCount(['operator as tickets_count' => fn (Builder $query) => $query->whereBetween('tickets.created_at', $range)])
            ->orderByDesc('tickets_count')
            ->orderBy('name')
            ->get();

        $resolutionMinutes = $this->resolvedTicketDurations($range);
        $users = User::query()
            ->selectRaw('is_active, COUNT(*) as aggregate')
            ->groupBy('is_active')
            ->pluck('aggregate', 'is_active');

        $audits = AdminAudit::query()
            ->whereBetween('created_at', $range)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', [
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'metrics' => [
                'total' => $totalTickets,
                'open' => (int) $statusCounts->get(TicketStatus::OPEN->value, 0),
                'in_progress' => (int) $statusCounts->get(TicketStatus::IN_PROGRESS->value, 0),
                'closed' => (int) $statusCounts->get(TicketStatus::CLOSED->value, 0),
                'average_resolution_hours' => $resolutionMinutes->isEmpty()
                    ? null
                    : round($resolutionMinutes->avg() / 60, 1),
                'active_users' => (int) $users->get(1, 0),
                'inactive_users' => (int) $users->get(0, 0),
            ],
            'areas' => $areas,
            'operators' => $operators,
            'audits' => $audits,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->reportPeriod($request);
        $range = [$start, $end];

        return response()->streamDownload(function () use ($range): void {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                throw new \RuntimeException('Não foi possível criar o arquivo CSV.');
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['ID', 'Título', 'Solicitante', 'Área', 'Operador', 'Status', 'Prioridade', 'Criado em', 'Fechado em'], ',', '"', '\\');

            Ticket::query()
                ->with(['requester', 'area', 'operator'])
                ->whereBetween('created_at', $range)
                ->orderBy('created_at')
                ->chunk(500, function (Collection $tickets) use ($stream): void {
                    $closedAtByTicket = TicketAudit::query()
                        ->select('ticket_id')
                        ->selectRaw('MAX(created_at) as closed_at')
                        ->whereIn('ticket_id', $tickets->pluck('id'))
                        ->where('new_status', TicketStatus::CLOSED->value)
                        ->groupBy('ticket_id')
                        ->pluck('closed_at', 'ticket_id');

                    foreach ($tickets as $ticket) {
                        $closedAt = $closedAtByTicket->get($ticket->id);

                        fputcsv($stream, array_map($this->safeCsvCell(...), [
                            $ticket->id,
                            $ticket->title,
                            $ticket->requester?->name ?? '',
                            $ticket->area?->name ?? '',
                            $ticket->operator?->name ?? '',
                            TicketStatus::labelFor($ticket->status),
                            TicketPriority::labelFor($ticket->priority),
                            $ticket->created_at->format('Y-m-d H:i:s'),
                            $closedAt ? Carbon::parse($closedAt)->format('Y-m-d H:i:s') : '',
                        ]), ',', '"', '\\');
                    }
                });

            fclose($stream);
        }, "relatorio-tickets-{$range[0]->toDateString()}-{$range[1]->toDateString()}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{Carbon, Carbon}
     */
    private function reportPeriod(Request $request): array
    {
        $defaults = [
            'start_date' => now()->subDays(29)->toDateString(),
            'end_date' => now()->toDateString(),
        ];
        $request->merge([
            'start_date' => $request->input('start_date', $defaults['start_date']),
            'end_date' => $request->input('end_date', $defaults['end_date']),
        ]);
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->endOfDay();

        if ($start->diffInDays($end) > 366) {
            throw ValidationException::withMessages([
                'end_date' => 'O período do relatório não pode exceder 366 dias.',
            ]);
        }

        return [$start, $end];
    }

    /**
     * @param  array{Carbon, Carbon}  $range
     * @return Collection<int, float>
     */
    private function resolvedTicketDurations(array $range): Collection
    {
        $latestClosures = TicketAudit::query()
            ->select('ticket_id')
            ->selectRaw('MAX(created_at) as resolved_at')
            ->where('new_status', TicketStatus::CLOSED->value)
            ->groupBy('ticket_id');

        return DB::query()
            ->fromSub($latestClosures, 'closures')
            ->join('tickets', 'tickets.id', '=', 'closures.ticket_id')
            ->where('tickets.status', TicketStatus::CLOSED->value)
            ->whereBetween('closures.resolved_at', $range)
            ->get(['tickets.created_at', 'closures.resolved_at'])
            ->map(fn (object $ticket) => Carbon::parse($ticket->created_at)
                ->diffInMinutes(Carbon::parse($ticket->resolved_at)));
    }

    private function safeCsvCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[\s\x00-\x1F]*[=+\-@]/u', $value) === 1
            ? "'".$value
            : $value;
    }
}
