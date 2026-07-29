<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory, HasUuids;

    public const POLL_TYPE_MULTIPLE_CHOICE = 'multiple_choice';
    public const POLL_TYPE_YES_NO = 'yes_no';
    public const POLL_TYPE_RATING = 'rating';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'question',
        'poll_type',
        'status',
        'duration_minutes',
        'allow_multiple_votes',
        'anonymous',
        'show_results',
        'public_token',
        'room_code',
        'created_by',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'allow_multiple_votes' => 'boolean',
            'anonymous' => 'boolean',
            'show_results' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Poll $poll) {
            if (empty($poll->public_token)) {
                $poll->public_token = Str::random(32);
            }
            if (empty($poll->room_code)) {
                $poll->room_code = self::generateUniqueRoomCode();
            }
        });
    }

    /**
     * Generate a unique 6-character alphanumeric room code.
     */
    private static function generateUniqueRoomCode(): string
    {
        $maxAttempts = 10;
        do {
            $code = strtoupper(Str::random(6));
            $exists = self::where('room_code', $code)->exists();
            $maxAttempts--;
        } while ($exists && $maxAttempts > 0);

        return $code;
    }

    public function setTeacherIdAttribute($value): void
    {
        $this->attributes['created_by'] = $value;
    }

    public function getTeacherIdAttribute(): mixed
    {
        return $this->attributes['created_by'] ?? null;
    }

    public function getShareTokenAttribute(): string
    {
        return $this->public_token;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function scopeByCreator($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByPublicToken($query, string $token)
    {
        return $query->where('public_token', $token);
    }

    public function scopeByRoomCode($query, string $roomCode)
    {
        return $query->where('room_code', $roomCode);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeActiveWithDuration($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('duration_minutes')
            ->whereNotNull('started_at');
    }
}
