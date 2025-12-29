<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'class_session_id', // Added
        'check_in_time',
        'check_out_time',
        'status',
        'method',
        'location_lat',
        'location_long',
        'notes', // Added
        'attachment', // Added
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }
}
