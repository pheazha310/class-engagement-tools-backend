<?php

namespace App\Models;

use Database\Factories\PollOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['poll_id', 'option_text', 'vote_count'])]
class PollOption extends Model
{
    /** @use HasFactory<PollOptionFactory> */
    use HasFactory;

    protected $table = 'poll_options';

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'option_id');
    }
}
