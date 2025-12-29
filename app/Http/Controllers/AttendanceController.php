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
            'class_room_id' => 'nullable|exists:class_rooms,id',
            'location_lat' => 'nullable|string',
            'location_long' => 'nullable|string',
            'method' => 'required|string',
            'status' => 'nullable|string|in:present,sick,permission,alpha',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $classSessionId = null;

        if ($request->has('class_room_id')) {
            $activeSession = \App\Models\ClassSession::where('class_room_id', $request->class_room_id)
                ->where('is_active', true)
                ->first();

            if (!$activeSession) {
                return response()->json(['success' => false, 'message' => 'Attendance is currently closed'], 400);
            }
            $classSessionId = $activeSession->id;
        }

        if ($request->has('class_room_id') && !$classSessionId) {
            return response()->json(['success' => false, 'message' => 'Attendance session is invalid'], 400);
        }

        // Check for existing attendance
        $query = Attendance::where('user_id', auth()->id());
        if ($classSessionId) {
            $query->where('class_session_id', $classSessionId);
        } else {
            $query->whereDate('created_at', Carbon::today())
                ->where('schedule_id', $request->schedule_id);
        }
        $existing = $query->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Already checked in'], 400);
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('attachments'), $filename);
            $attachmentPath = 'attachments/' . $filename;
        }

        $attendance = Attendance::create([
            'user_id' => auth()->id(), // Use authenticated user ID
            'schedule_id' => $request->schedule_id,
            'class_session_id' => $classSessionId,
            'check_in_time' => Carbon::now(),
            'status' => $request->input('status', 'present'),
            'method' => $request->input('method'),
            'location_lat' => $request->location_lat,
            'location_long' => $request->location_long,
            'notes' => $request->input('notes'),
            'attachment' => $attachmentPath,
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
            // 'user_id' => 'required|exists:users,id', // Removed
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        // Find the latest attendance for today
        $attendance = Attendance::where('user_id', auth()->id())
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

    public function history(Request $request)
    {
        $history = Attendance::where('user_id', auth()->id())
            ->with(['schedule', 'schedule.classRoom', 'classSession', 'classSession.classRoom'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Attendance history',
            'data' => $history,
        ]);
    }

    public function getSessionAttendees($sessionId)
    {
        $attendances = Attendance::where('class_session_id', $sessionId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Session attendees',
            'data' => $attendances,
        ]);
    }

    public function today(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('created_at', Carbon::today())
            ->with(['classSession', 'classSession.classRoom'])
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Today attendance',
            'attendance' => $attendance,
        ]);
    }

    public function statistics(Request $request)
    {
        $user = auth()->user();

        $stats = [
            'present' => Attendance::where('user_id', $user->id)->where('status', 'present')->count(),
            'permission' => Attendance::where('user_id', $user->id)->where('status', 'permission')->count(),
            'sick' => Attendance::where('user_id', $user->id)->where('status', 'sick')->count(),
            'alpha' => Attendance::where('user_id', $user->id)->where('status', 'alpha')->count(),
            'late' => Attendance::where('user_id', $user->id)
                ->where('status', 'present')
                ->whereColumn('check_in_time', '>', 'created_at') // Simplified logic, ideally compare with schedule start time
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Attendance statistics',
            'data' => $stats,
        ]);
    }
}
