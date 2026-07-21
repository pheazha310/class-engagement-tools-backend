<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'game_session_id',
    'teacher_id',
    'game_type',
    'settings',
    'participants',
    'scores',
    'total_questions',
    'started_at',
    'ended_at',
])]
class GameHistory extends Model
{
    use HasFactory;

    protected $casts = [
        'settings' => 'array',
        'participants' => 'array',
        'scores' => 'array',
        'total_questions' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
