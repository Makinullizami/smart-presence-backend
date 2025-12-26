<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use Illuminate\Http\Request;

class FaceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'embedding' => 'required', // Assuming JSON string or similar
        ]);

        $face = FaceEmbedding::updateOrCreate(
            ['user_id' => $request->user_id],
            ['embedding' => $request->embedding]
        );

        return response()->json([
            'success' => true,
            'message' => 'Face embedded registered successfully',
            'data' => $face,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'embedding' => 'required',
        ]);

        // Dummy verification logic
        // In real app, you would compare embeddings with Python or PHP library

        return response()->json([
            'success' => true,
            'message' => 'Face verification simulated',
            'data' => [
                'match' => true,
                'user_id' => 1, // Dummy user ID
                'confidence' => 0.98,
            ],
        ]);
    }
}
