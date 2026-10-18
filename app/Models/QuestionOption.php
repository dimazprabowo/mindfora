<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'score',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'order' => 'integer',
        ];
    }

    // Relationships
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
