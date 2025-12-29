<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
    {
        $user = auth()->user();

        // 1. Get student classes
        $studentClasses = $user->classes()
            ->with(['materials'])
            ->get();

        $notifications = [];

        foreach ($studentClasses as $classRoom) { // Loop directly over ClassRoom objects now
            // 2. Check for Active Sessions (Attendance)
            $activeSession = \App\Models\ClassSession::where('class_room_id', $classRoom->id)
                ->where('is_active', true)
                ->first();

            if ($activeSession) {
                // Check if already attended
                $attended = \App\Models\Attendance::where('user_id', $user->id)
                    ->where('class_session_id', $activeSession->id)
                    ->exists();

                if (!$attended) {
                    $notifications[] = [
                        'id' => 'session_' . $activeSession->id,
                        'title' => 'Sesi Absensi Aktif',
                        'message' => "Absensi dibuka untuk kelas {$classRoom->name}. Segera lakukan check-in!",
                        'type' => 'attendance',
                        'class_id' => $classRoom->id,
                        'time' => $activeSession->created_at->diffForHumans(),
                        'is_read' => false,
                        'created_at' => $activeSession->created_at,
                    ];
                }
            }

            // 3. Check for New Materials (Last 24h)
            $recentMaterials = $classRoom->materials()
                ->where('created_at', '>=', now()->subDay())
                ->get();

            foreach ($recentMaterials as $material) {
                $notifications[] = [
                    'id' => 'material_' . $material->id,
                    'title' => 'Materi Baru',
                    'message' => "Materi baru '{$material->title}' telah ditambahkan di kelas {$classRoom->name}.",
                    'type' => 'material',
                    'class_id' => $classRoom->id,
                    'time' => $material->created_at->diffForHumans(),
                    'is_read' => false, // In a real app, check against a 'read_notifications' table
                    'created_at' => $material->created_at,
                ];
            }
        }

        // 4. Merge with stored system notifications (if any)
        $systemNotifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'message' => $notif->body ?? $notif->message,
                    'type' => $notif->type ?? 'info',
                    'time' => $notif->created_at->diffForHumans(),
                    'is_read' => (bool) $notif->is_read,
                    'created_at' => $notif->created_at,
                ];
            });

        // Combine and Sort by Date Descending
        $allNotifications = collect($notifications)->merge($systemNotifications)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'message' => 'User notifications',
            'data' => $allNotifications,
        ]);
    }
}
