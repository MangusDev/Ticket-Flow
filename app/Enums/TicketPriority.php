<?php

namespace App\Enums;

enum TicketPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Baixa',
            self::MEDIUM => 'Média',
            self::HIGH => 'Alta',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::LOW => 'neutral',
            self::MEDIUM => 'warning',
            self::HIGH => 'danger',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $priority) => $priority->value, self::cases());
    }

    public static function labelFor(?string $value): string
    {
        return self::tryFrom($value ?? '')?->label() ?? ucfirst((string) $value);
    }

    public static function toneFor(?string $value): string
    {
        return self::tryFrom($value ?? '')?->tone() ?? 'neutral';
    }
}
