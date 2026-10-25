<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Aberto',
            self::IN_PROGRESS => 'Em andamento',
            self::CLOSED => 'Fechado',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::OPEN => 'info',
            self::IN_PROGRESS => 'warning',
            self::CLOSED => 'success',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }

    public static function labelFor(?string $value): string
    {
        return self::tryFrom($value ?? '')?->label() ?? (string) $value;
    }

    public static function toneFor(?string $value): string
    {
        return self::tryFrom($value ?? '')?->tone() ?? 'neutral';
    }

    public static function canTransition(string $current, string $next): bool
    {
        $allowedTransitions = match (self::tryFrom($current)) {
            self::OPEN => [self::IN_PROGRESS, self::CLOSED],
            self::IN_PROGRESS => [self::OPEN, self::CLOSED],
            self::CLOSED => [],
            null => [],
        };

        return in_array(self::tryFrom($next), $allowedTransitions, true);
    }
}
