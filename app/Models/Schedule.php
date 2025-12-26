<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_room_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
