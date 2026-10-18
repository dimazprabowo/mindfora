<?php

namespace App\Enums;

enum QuestionType: string
{
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Likert = 'likert';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Radio => 'Pilihan Ganda (Radio)',
            self::Checkbox => 'Checkbox (Multi-jawaban)',
            self::Likert => 'Skala Likert',
            self::Text => 'Isian Teks',
        };
    }

    public function isScored(): bool
    {
        return match ($this) {
            self::Radio, self::Checkbox, self::Likert => true,
            self::Text => false,
        };
    }

    public function hasOptions(): bool
    {
        return match ($this) {
            self::Radio, self::Checkbox, self::Likert => true,
            self::Text => false,
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
