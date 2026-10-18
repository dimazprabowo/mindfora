<?php

namespace App\Enums;

enum EducationLevel: string
{
    case None = 'none';
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';
    case D3 = 'd3';
    case D4 = 'd4';
    case S1 = 's1';
    case S2 = 's2';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Tidak Bersekolah',
            self::SD => 'SD',
            self::SMP => 'SMP',
            self::SMA => 'SMA / SMK',
            self::D3 => 'Diploma (D3)',
            self::D4 => 'Diploma (D4)',
            self::S1 => 'Sarjana (S1)',
            self::S2 => 'Magister (S2)',
            self::S3 => 'Doktor (S3)',
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
