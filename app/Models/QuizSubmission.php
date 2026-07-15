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
        'score',
        'percentage',
        'time_taken',
        'submitted_at',
        'status',
        'class_name',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'percentage' => 'float',
            'time_taken' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
