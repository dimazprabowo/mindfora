<?php

namespace App\Enums;

enum TestProgramStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Aktif',
            self::Archived => 'Arsip',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::Archived => 'blue',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-800 dark:bg-gray-700/50 dark:text-gray-200',
            self::Active => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Archived => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
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
