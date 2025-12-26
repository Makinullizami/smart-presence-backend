<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'location_lat' => 'nullable|string',
            'location_long' => 'nullable|string',
            'method' => 'required|string', // face, gps, manual
        ]);

        // Check if already checked in today for this schedule
        $existing = Attendance::where('user_id', $request->user_id)
            ->whereDate('created_at', Carbon::today())
            ->where('schedule_id', $request->schedule_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in',
            ], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'schedule_id' => $request->schedule_id,
            'check_in_time' => Carbon::now(),
            'status' => 'present',
            'method' => $request->input('method'),
            'location_lat' => $request->location_lat,
            'location_long' => $request->location_long,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => $attendance,
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        // Find the latest attendance for today
        $attendance = Attendance::where('user_id', $request->user_id)
            ->whereDate('created_at', Carbon::today())
            ->where('schedule_id', $request->schedule_id)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No check-in record found for today',
            ], 404);
        }

        $attendance->update([
            'check_out_time' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful',
            'data' => $attendance,
        ]);
    }

    public function history($user_id)
    {
        $history = Attendance::where('user_id', $user_id)
            ->with(['schedule', 'schedule.classRoom'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Attendance history',
            'data' => $history,
        ]);
    }
}
