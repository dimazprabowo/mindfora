<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Terjadwal',
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'blue',
            self::Active => 'green',
            self::Completed => 'gray',
            self::Cancelled => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            self::Active => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Completed => 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200',
            self::Cancelled => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
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
