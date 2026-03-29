<?php

declare(strict_types=1);

namespace RenderRoom\ScriptHub\Services;

enum ServiceCategory: string
{
    case Analytics = 'analytics';
    case Pixels = 'pixels';
    case Chats = 'chats';
    case LeadGen = 'leadgen';

    public function label(): string
    {
        return match ($this) {
            self::Analytics => 'Аналитика',
            self::Pixels => 'Рекламные пиксели',
            self::Chats => 'Чаты и коммуникации',
            self::LeadGen => 'Лидогенерация',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Analytics => 'pi pi-chart-bar',
            self::Pixels => 'pi pi-bullseye',
            self::Chats => 'pi pi-comments',
            self::LeadGen => 'pi pi-megaphone',
        };
    }

    public function position(): int
    {
        return match ($this) {
            self::Analytics => 0,
            self::Pixels => 1,
            self::Chats => 2,
            self::LeadGen => 3,
        };
    }
}
