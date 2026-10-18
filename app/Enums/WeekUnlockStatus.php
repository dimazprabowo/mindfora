<?php

namespace App\Enums;

enum WeekUnlockStatus: string
{
    case Locked = 'locked';
    case Available = 'available';
    case Overdue = 'overdue';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Locked => 'Terkunci',
            self::Available => 'Tersedia',
            self::Overdue => 'Terlambat',
            self::Completed => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Locked => 'gray',
            self::Available => 'green',
            self::Overdue => 'amber',
            self::Completed => 'blue',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Locked => 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200',
            self::Available => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Overdue => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
            self::Completed => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
