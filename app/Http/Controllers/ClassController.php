<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassRoom::with('teacher')->get();

        return response()->json([
            'success' => true,
            'message' => 'List of classes',
            'data' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Generate a unique code for the class
        $data = $request->all();
        $data['code'] = Str::upper(Str::random(6));
        $data['teacher_id'] = Auth::id();

        $classRoom = ClassRoom::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => $classRoom,
        ], 201);
    }

    public function getLecturerClasses(Request $request)
    {
        $user = Auth::user();
        $classes = ClassRoom::where('teacher_id', $user->id)
            ->withCount('students')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List of lecturer classes',
            'data' => $classes,
        ]);
    }

    public function getStudentClasses(Request $request)
    {
        $user = Auth::user();
        // Assuming the relationship is defined in User model as 'classes'
        $classes = $user->classes()
            ->with('teacher')
            ->withCount('students')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List of student classes',
            'data' => $classes,
        ]);
    }

    public function joinClass(Request $request)
    {
        $request->validate([
            'code' => 'required|string|exists:class_rooms,code',
        ]);

        $user = Auth::user();
        $classRoom = ClassRoom::where('code', $request->code)->first();

        // Check if already joined
        if ($user->classes()->where('class_room_id', $classRoom->id)->exists()) {
            return response()->json([
                'success' => true, // Treat as success/idempotent
                'message' => 'You are already a member of this class',
                'data' => $classRoom,
            ], 200);
        }

        $user->classes()->attach($classRoom->id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined class',
            'data' => $classRoom,
        ]);
    }

    public function getStudentClassDetail($id)
    {
        $classRoom = ClassRoom::with(['teacher', 'materials', 'assignments', 'schedules'])->find($id);

        if (!$classRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Class detail',
            'data' => $classRoom,
        ]);
    }

    public function destroy($id)
    {
        $classRoom = ClassRoom::find($id);

        if (!$classRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found',
            ], 404);
        }

        // Optional: specific permission check, though middleware/policy is better
        if ($classRoom->teacher_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $classRoom->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
        ]);
    }

    // Session Management
    public function startSession(Request $request, $id)
    {
        $classRoom = ClassRoom::find($id);
        if (!$classRoom) {
            return response()->json(['success' => false, 'message' => 'Class not found'], 404);
        }

        if ($classRoom->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Deactivate any existing active sessions
        ClassSession::where('class_room_id', $id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'end_time' => now()]);

        // Create new session
        $session = ClassSession::create([
            'class_room_id' => $id,
            'start_time' => now(),
            'is_active' => true,
            'session_token' => Str::random(32), // For future use
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance session started',
            'data' => $session,
        ]);
    }

    public function stopSession(Request $request, $id)
    {
        $classRoom = ClassRoom::find($id);
        if (!$classRoom) {
            return response()->json(['success' => false, 'message' => 'Class not found'], 404);
        }

        if ($classRoom->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Deactivate active sessions
        $updated = ClassSession::where('class_room_id', $id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'end_time' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance session stopped',
            'updated_count' => $updated,
        ]);
    }

    public function getActiveSession($id)
    {
        $session = ClassSession::where('class_room_id', $id)
            ->where('is_active', true)
            ->first();

        if ($session) {
            $user = Auth::user();
            if ($user) {
                $attendance = \App\Models\Attendance::where('class_session_id', $session->id)
                    ->where('user_id', $user->id)
                    ->first();
                $session->has_attended = !!$attendance;
                $session->attendance_status = $attendance ? $attendance->status : null;
                $session->attendance_time = $attendance ? $attendance->created_at : null;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $session ? 'Active session found' : 'No active session',
            'data' => $session,
        ]);
    }

    public function getClassStudents($id)
    {
        $classRoom = ClassRoom::find($id);

        if (!$classRoom) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found',
            ], 404);
        }

        // Check if user is teacher or student of this class
        $user = Auth::user();
        if ($classRoom->teacher_id !== $user->id && !$classRoom->students()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to class students',
            ], 403);
        }

        $students = $classRoom->students;

        return response()->json([
            'success' => true,
            'message' => 'List of students in class',
            'data' => $students,
        ]);
    }
}
