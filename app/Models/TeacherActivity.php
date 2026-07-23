<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherActivity extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'class_name',
        'teacher_id',
        'activity_type',
        'activity_name',
        'timestamp',
        'participants',
        'status',
        'score',
        'details',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'score' => 'float',
        'details' => 'array',
    ];
}
