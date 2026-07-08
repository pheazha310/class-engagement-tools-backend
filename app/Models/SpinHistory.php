<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wheel_id', 'participant_id', 'participant_name'])]
class SpinHistory extends Model
{
    public function wheel(): BelongsTo
    {
        return $this->belongsTo(Wheel::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
