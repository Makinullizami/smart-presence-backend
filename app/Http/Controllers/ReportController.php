<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // 1. Dashboard Summary
    public function summary()
    {
        $user = Auth::user();

        // Total Classes
        $totalClasses = ClassRoom::where('teacher_id', $user->id)->count();

        // Total Sessions across all classes
        $totalSessions = ClassSession::whereHas('classRoom', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->count();

        // Attendance stats across all classes
        $attendances = Attendance::whereHas('classSession.classRoom', function ($q) use ($user) {
            $q->where('teacher_id', $user->id);
        })->get();

        $totalAttendances = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $sick = $attendances->where('status', 'sick')->count();
        $permission = $attendances->where('status', 'permission')->count();
        $alpha = $attendances->where('status', 'alpha')->count();

        // Average Attendance Rate
        $attendanceRate = $totalAttendances > 0 ? ($present / $totalAttendances) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_classes' => $totalClasses,
                'total_sessions' => $totalSessions,
                'attendance_rate' => round($attendanceRate, 1),
                'distribution' => [
                    'present' => $present,
                    'sick' => $sick,
                    'permission' => $permission,
                    'alpha' => $alpha,
                ]
            ]
        ]);
    }

    // 2. Class Detail Report
    public function classReport(Request $request, $id)
    {
        $class = ClassRoom::where('id', $id)->where('teacher_id', Auth::id())->first();

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Class not found or unauthorized'], 404);
        }

        // Sessions with attendance counts
        $sessions = ClassSession::where('class_room_id', $id)
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('status', 'present');
                }
            ])
            ->withCount([
                'attendances as absent_count' => function ($q) {
                    $q->whereIn('status', ['alpha', 'sick', 'permission']);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Students with "At Risk" flag
        $students = $class->students()->get()->map(function ($student) use ($id) {
            $absentCount = Attendance::where('user_id', $student->id)
                ->whereHas('classSession', function ($q) use ($id) {
                    $q->where('class_room_id', $id);
                })
                ->whereIn('status', ['alpha'])
                ->count();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'absent_count' => $absentCount,
                'is_at_risk' => $absentCount >= 3
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'class_info' => $class,
                'sessions' => $sessions,
                'students' => $students,
            ]
        ]);
    }

    // 3. Export (Placeholder for now, returning JSON)
    public function export(Request $request)
    {
        // Implementation for PDF export would go here using a library.
        // For current scope returning raw data is sufficient for frontend to possibly handle or just a message.
        return response()->json(['message' => 'Export feature coming soon']);
    }
}
