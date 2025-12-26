<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use Illuminate\Http\Request;

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
            'teacher_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $classRoom = ClassRoom::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => $classRoom,
        ], 201);
    }
}
