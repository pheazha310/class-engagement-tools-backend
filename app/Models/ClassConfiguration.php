<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClassConfiguration extends Model
{
    /** @use HasFactory<\Database\Factories\ClassConfigurationFactory> */
    use HasFactory;

    protected $fillable = [
        'class_name',
        'subject',
        'grade_level',
        'teacher_id',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setClassNameAttribute(string $value): void
    {
        $this->attributes['class_name'] = $value;
        $this->attributes['slug'] = Str::slug($value) . '-' . uniqid();
    }
}
