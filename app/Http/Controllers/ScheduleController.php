<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function showByClass($class_id)
    {
        $schedules = Schedule::where('class_room_id', $class_id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Schedules for class ' . $class_id,
            'data' => $schedules,
        ]);
    }
}
