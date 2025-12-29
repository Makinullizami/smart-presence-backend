<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\ClassRoom;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * Get all assignments for a class
     */
    public function index($classId)
    {
        $assignments = Assignment::where('class_room_id', $classId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'assignments' => $assignments
        ]);
    }

    /**
     * Create assignment (Lecturer only)
     */
    public function store(Request $request, $classId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:now',
            'max_score' => 'nullable|integer|min:1|max:100',
        ]);

        $class = ClassRoom::findOrFail($classId);

        // Check authorization
        if ($class->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $assignment = $class->assignments()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat',
            'assignment' => $assignment
        ], 201);
    }

    /**
     * Delete assignment (Lecturer only)
     */
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);
        $class = $assignment->classRoom;

        // Check authorization
        if ($class->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus'
        ]);
    }
}
