<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_room_id',
        'title',
        'description',
        'file_url',
        'file_type',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
}
