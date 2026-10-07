<?php

namespace App\Enums;

enum QuizQuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case ShortText = 'short_text';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Multiple choice',
            self::TrueFalse => 'True / False',
            self::ShortText => 'Short text (manually graded)',
        };
    }

    public function isAutoGradable(): bool
    {
        return $this !== self::ShortText;
    }

    public static function options(): array
    {
        return [
            self::MultipleChoice->value => self::MultipleChoice->label(),
            self::TrueFalse->value => self::TrueFalse->label(),
            self::ShortText->value => self::ShortText->label(),
        ];
    }
}
