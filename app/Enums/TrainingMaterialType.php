<?php

namespace App\Enums;

enum TrainingMaterialType: string
{
    case Audio = 'audio';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Audio => 'Audio',
            self::Video => 'Video',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Audio => 'blue',
            self::Video => 'purple',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Audio => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
            self::Video => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-200',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Audio => 'musical-note',
            self::Video => 'video-camera',
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
