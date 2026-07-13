<?php

namespace App\Models;

use Database\Factories\PollFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['teacher_id', 'question', 'room_code', 'is_multiple_choice', 'duration_minutes', 'status', 'started_at', 'ended_at', 'school_id', 'province_id'])]
class Poll extends Model
{
    /** @use HasFactory<PollFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'is_multiple_choice' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Poll $poll) {
            if (empty($poll->room_code)) {
                $poll->room_code = static::generateUniqueRoomCode();
            }
        });
    }

    public static function generateUniqueRoomCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('room_code', $code)->exists());

        return $code;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Vote::class)->selectRaw('DISTINCT student_id');
    }

    public function participantCount(): int
    {
        return $this->votes()->distinct('student_id')->count('student_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeByRoomCode($query, string $roomCode)
    {
        return $query->where('room_code', strtoupper($roomCode));
    }
}
