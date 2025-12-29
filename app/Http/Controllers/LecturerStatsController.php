<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerStatsController extends Controller
{
    /**
     * Get weekly attendance trends for the last 12 weeks.
     */
    public function trends()
    {
        $user = Auth::user();

        $trends = Attendance::select(
            DB::raw('YEARWEEK(created_at, 1) as year_week'),
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "present" then 1 else 0 end) as present_count')
        )
            ->whereHas('classSession.classRoom', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get()
            ->map(function ($item) {
                return [
                    'week' => $item->year_week,
                    'rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 1) : 0
                ];
            });

        return response()->json(['success' => true, 'data' => $trends]);
    }

    /**
     * Compare performance between classes.
     */
    public function comparison()
    {
        $user = Auth::user();

        $classes = ClassRoom::where('teacher_id', $user->id)
            ->withCount(['attendances as total_attendances'])
            ->withCount([
                'attendances as present_attendances' => function ($q) {
                    $q->where('status', 'present');
                }
            ])
            ->get()
            ->map(function ($class) {
                $rate = $class->total_attendances > 0
                    ? round(($class->present_attendances / $class->total_attendances) * 100, 1)
                    : 0;

                return [
                    'class_name' => $class->name,
                    'class_code' => $class->code,
                    'attendance_rate' => $rate
                ];
            });

        return response()->json(['success' => true, 'data' => $classes]);
    }

    /**
     * Distribution of student performance (High, Medium, Low).
     */
    public function distribution()
    {
        $user = Auth::user();

        $stats = Attendance::select(
            'user_id',
            DB::raw('count(*) as total'),
            DB::raw('sum(case when status = "present" then 1 else 0 end) as present')
        )
            ->whereHas('classSession.classRoom', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->groupBy('user_id')
            ->get();

        $high = 0;   // >= 90%
        $medium = 0; // 75-89%
        $low = 0;    // < 75%

        foreach ($stats as $stat) {
            $rate = $stat->total > 0 ? ($stat->present / $stat->total) * 100 : 0;
            if ($rate >= 90)
                $high++;
            else if ($rate >= 75)
                $medium++;
            else
                $low++;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'high' => $high,
                'medium' => $medium,
                'low' => $low
            ]
        ]);
    }

    /**
     * Analyze Attendance Status (Present vs Sick vs Permission vs Alpha).
     */
    public function methods()
    {
        $user = Auth::user();

        // User requested to prioritize Status over Method due to column issues/preference.
        // We will return the counts for each status.

        $stats = Attendance::select('status', DB::raw('count(*) as count'))
            ->whereHas('classSession.classRoom', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure keys exist with 0 if default
        $data = [
            'Hadir' => $stats['present'] ?? 0,
            'Izin' => $stats['permission'] ?? 0,
            'Sakit' => $stats['sick'] ?? 0,
            'Alpha' => ($stats['alpha'] ?? 0) + ($stats['absent'] ?? 0),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Punctuality Analysis (Late vs On Time).
     */
    public function punctuality()
    {
        $user = Auth::user();

        $stats = Attendance::select('status', DB::raw('count(*) as count'))
            ->whereHas('classSession.classRoom', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            ->whereIn('status', ['present', 'late'])
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'on_time' => $stats['present'] ?? 0,
                'late' => $stats['late'] ?? 0
            ]
        ]);
    }
}
