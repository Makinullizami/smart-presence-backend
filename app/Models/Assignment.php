<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_room_id',
        'title',
        'description',
        'deadline',
        'max_score',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
