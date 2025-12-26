<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function classReport($class_id)
    {
        // Get attendance for a specific class (via schedules)
        $report = Attendance::whereHas('schedule', function ($query) use ($class_id) {
            $query->where('class_room_id', $class_id);
        })
            ->with(['user', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Attendance report for class ' . $class_id,
            'data' => $report,
        ]);
    }
}
