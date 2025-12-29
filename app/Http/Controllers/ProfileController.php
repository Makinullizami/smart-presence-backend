<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'photo' => 'nullable|string',
            'nim' => 'nullable|string|min:5|max:20',
            'faculty' => 'nullable|string|min:3|max:255',
            'major' => 'nullable|string|min:3|max:255',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        $user = $request->user();

        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
