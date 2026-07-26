<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }
}
