<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'quiz_id',
        'student_name',
        'class_name',
        'answers',
        'score',
        'total_points',
        'percentage',
        'passing_score',
        'time_taken',
        'submitted_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'score' => 'integer',
            'total_points' => 'integer',
            'percentage' => 'float',
            'passing_score' => 'integer',
            'time_taken' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
