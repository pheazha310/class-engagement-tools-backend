<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['game_session_id', 'user_id', 'question_id', 'submitted_answer', 'is_correct', 'points_awarded', 'participant_name'])]
class GameAnswer extends Model
{
    use HasFactory;

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
    ];

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }
}
