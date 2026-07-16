<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['teacher_id', 'game_type', 'settings', 'status', 'join_code'])]
class GameSession extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (GameSession $session) {
            if (! $session->join_code) {
                $session->join_code = static::generateUniqueJoinCode();
            }
        });
    }

    public static function generateUniqueJoinCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('join_code', $code)->exists());

        return $code;
    }

    protected $casts = [
        'settings' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function isGuest(): bool
    {
        return $this->teacher_id === null;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function answers()
    {
        return $this->hasMany(GameAnswer::class);
    }
}
