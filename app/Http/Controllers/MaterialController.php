<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Get all materials for a class
     */
    public function index($classId)
    {
        $materials = Material::where('class_room_id', $classId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'materials' => $materials
        ]);
    }

    /**
     * Add material to class (Lecturer only)
     */
    public function store(Request $request, $classId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_url' => 'nullable|url|max:500',
            'file_type' => 'nullable|string|max:50',
        ]);

        $class = ClassRoom::findOrFail($classId);

        // Check authorization
        if ($class->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $material = $class->materials()->create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil ditambahkan',
            'material' => $material
        ], 201);
    }

    /**
     * Delete material (Lecturer only)
     */
    public function destroy($id)
    {
        $material = Material::findOrFail($id);
        $class = $material->classRoom;

        // Check authorization
        if ($class->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Materi berhasil dihapus'
        ]);
    }
}
