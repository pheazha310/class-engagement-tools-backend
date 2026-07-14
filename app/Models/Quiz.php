<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'subject',
        'class_name',
        'duration',
        'passing_score',
        'due_date',
        'shuffle_questions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'shuffle_questions' => 'boolean',
            'passing_score' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
