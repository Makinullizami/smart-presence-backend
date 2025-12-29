<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_room_id',
        'start_time',
        'end_time',
        'is_active',
        'session_token',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
