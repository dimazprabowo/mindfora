<?php

namespace App\Enums;

enum StressCategory: string
{
    case Low = 'rendah';
    case Medium = 'sedang';
    case High = 'tinggi';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Stres Rendah',
            self::Medium => 'Stres Sedang',
            self::High => 'Stres Tinggi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'green',
            self::Medium => 'amber',
            self::High => 'red',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
            self::Medium => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200',
            self::High => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
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

    /**
     * Tentukan kategori stres berdasarkan skor & threshold.
     * Jika threshold null, gunakan default persentase: 0-33%=rendah, 34-66%=sedang, 67-100%=tinggi.
     */
    public static function fromScore(int $scoreTotal, int $maxPossible, ?int $thresholdLowMax = null, ?int $thresholdMediumMax = null): self
    {
        if ($maxPossible <= 0) {
            return self::Low;
        }

        if ($thresholdLowMax !== null && $thresholdMediumMax !== null) {
            if ($scoreTotal <= $thresholdLowMax) {
                return self::Low;
            }
            if ($scoreTotal <= $thresholdMediumMax) {
                return self::Medium;
            }

            return self::High;
        }

        // Default: persentase
        $percentage = ($scoreTotal / $maxPossible) * 100;

        return match (true) {
            $percentage <= 33 => self::Low,
            $percentage <= 66 => self::Medium,
            default => self::High,
        };
    }
}
