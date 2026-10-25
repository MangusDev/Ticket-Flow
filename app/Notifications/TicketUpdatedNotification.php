<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketUpdatedNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ?string $comment = null,
        public bool $statusChanged = false,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Atualização no seu ticket')
            ->greeting('Olá, '.$notifiable->name)
            ->line('Seu ticket "'.$this->ticket->title.'" foi atualizado.');

        if ($this->statusChanged) {
            $message->line('Novo status: '.TicketStatus::labelFor($this->ticket->status).'.');
        }

        if ($this->comment) {
            $message->line('Nova resposta: '.$this->comment);
        }

        return $message->action('Ver ticket', url('/tickets/'.$this->ticket->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => $this->ticket->title,
            'status' => $this->ticket->status,
            'comment' => $this->comment,
            'status_changed' => $this->statusChanged,
        ];
    }
}