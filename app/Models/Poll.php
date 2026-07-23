<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory, HasUuids;

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
                $poll->public_token = (string) Str::uuid();
            }
        });
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

    public function scopeExpired($query)
    {
        return $query
            ->where('status', 'active')
            ->whereNotNull('started_at')
            ->whereNotNull('duration_minutes')
            ->whereRaw('started_at <= NOW() - (duration_minutes * INTERVAL \'1 minute\')');
    }

    public function hasExpired(): bool
    {
        if (! $this->isActive() || ! $this->started_at || ! $this->duration_minutes) {
            return false;
        }

        return $this->started_at->copy()->addMinutes($this->duration_minutes)->isPast();
    }

    public function scopeByCreator($query, string $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeByPublicToken($query, string $token)
    {
        return $query->where('public_token', $token);
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
}
