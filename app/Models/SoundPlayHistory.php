<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoundPlayHistory extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'sound_id',
        'sound_name',
        'audio_url',
        'sound_category',
        'icon',
        'duration_seconds',
        'played_by',
        'played_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'played_at' => 'datetime',
        ];
    }

    public function sound(): BelongsTo
    {
        return $this->belongsTo(Sound::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'played_by');
    }
}
