<?php

namespace App\Enums;

enum FileStatus: string
{
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Processing => 'Memproses',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Processing => 'amber',
            self::Completed => 'green',
            self::Failed => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Processing => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
            self::Completed => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Failed => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
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
