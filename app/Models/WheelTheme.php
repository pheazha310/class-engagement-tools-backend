<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $name
 * @property array<string, mixed>|null $config
 * @property bool $is_default
 */
#[Fillable(['name', 'config', 'is_default'])]
class WheelTheme extends Model
{
    /** @use HasFactory<WheelThemeFactory> */
    use HasFactory;

    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
    ];

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

    public function wheels(): HasMany
    {
        return $this->hasMany(Wheel::class);
    }
}
