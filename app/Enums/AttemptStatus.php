<?php

namespace App\Enums;

enum AttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Sedang Dikerjakan',
            self::Submitted => 'Selesai Disubmit',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InProgress => 'amber',
            self::Submitted => 'green',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
            self::Submitted => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
