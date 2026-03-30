<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    /**
     * Get isolated attendance graphs designed for dashboard heatmaps and sparklines.
     */
    public function teacherGraphs(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $thirtyDaysAgo = now()->subDays(30)->startOfDay();

        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->with(['attendances' => function ($query) use ($thirtyDaysAgo) {
                $query->where('date', '>=', $thirtyDaysAgo->format('Y-m-d'))->orderBy('date', 'desc');
            }])
            ->get();

        // 1. Generate master array of last 30 calendar days
        $last30Days = [];
        $last7WorkDays = [];

        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend();

            // Unshift places oldest dates at the start (perfect for chronological axis graphs)
            array_unshift($last30Days, [
                'date' => $dateString,
                'is_weekend' => $isWeekend
            ]);

            if (!$isWeekend && count($last7WorkDays) < 7) {
                array_unshift($last7WorkDays, $dateString);
            }
        }

        $graphData = $teachers->map(function ($teacher) use ($last30Days, $last7WorkDays) {
            
            // Key records by date string for rapid O(1) lookups during array building
            $attendancesByDate = $teacher->attendances->keyBy(function($att) {
                return is_string($att->date) ? substr($att->date, 0, 10) : $att->date->format('Y-m-d');
            });

            // Build 30-day Calendar Heat Map
            $heatMap = [];
            foreach ($last30Days as $dayInfo) {
                $date = $dayInfo['date'];
                $hasAttended = $attendancesByDate->has($date);

                if ($dayInfo['is_weekend']) {
                    $status = 'weekend'; // Allows UI to render it as gray box
                } else {
                    $status = $hasAttended ? 'present' : 'absent'; // Green or Red box
                }

                $heatMap[] = [
                    'date' => $date,
                    'status' => $status,
                ];
            }

            // Build 7-day Timeline Graph focusing on Clock-In variances
            $sparkline = [];
            foreach ($last7WorkDays as $date) {
                $record = $attendancesByDate->get($date);
                $clockInTime = null;

                if ($record && $record->clock_in) {
                    // Extract exactly just the time "H:i:s"
                    $clockInTime = Carbon::parse($record->clock_in)->format('H:i:s');
                }

                $sparkline[] = [
                    'date' => $date,
                    'status' => $record ? 'present' : 'absent',
                    'clock_in' => $clockInTime
                ];
            }

            return [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'profile_picture' => $teacher->profile_picture,
                'graphs' => [
                    'heat_map_30_days' => $heatMap,
                    'sparkline_7_days' => $sparkline
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $graphData
        ]);
    }
}
