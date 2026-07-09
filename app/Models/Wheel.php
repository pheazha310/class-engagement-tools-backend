<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'description', 'color', 'removal_mode', 'share_token'])]
class Wheel extends Model
{
    /** @use HasFactory<WheelFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function spinHistories(): HasMany
    {
        return $this->hasMany(SpinHistory::class);
    }

    public function generateShareToken(): string
    {
        $plaintext = bin2hex(random_bytes(32));

        $this->forceFill([
            'share_token' => hash('sha256', $plaintext),
        ])->save();

        return $plaintext;
    }

    public static function findByShareToken(string $token): ?self
    {
        return static::where('share_token', hash('sha256', $token))->first();
    }
}
