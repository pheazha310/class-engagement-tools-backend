<?php

namespace App\Models;

use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'province_id'])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
