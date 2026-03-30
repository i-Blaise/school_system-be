<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class TeacherProfileController extends Controller
{
    /**
     * Retrieve a list of all teachers for the authenticated user's school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        // Fetch all teachers in this school and eager-load their profile and last 30 days attendance
        $thirtyDaysAgo = now()->subDays(30)->format('Y-m-d');
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->with(['teacherProfile', 'attendances' => function ($query) use ($thirtyDaysAgo) {
                $query->where('date', '>=', $thirtyDaysAgo)->orderBy('date', 'desc');
            }])
            ->get();

        // Calculate expected working days (excluding weekends)
        $last30WorkDays = collect();
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            if (!$date->isWeekend()) {
                $last30WorkDays->push($date->format('Y-m-d'));
            }
        }
        $totalWorkingDays = $last30WorkDays->count();
        $last7WorkDays = $last30WorkDays->take(7);

        // Map the data to a cleaner format specifically for the dashboard
        $formattedTeachers = $teachers->map(function ($teacher) use ($last30WorkDays, $totalWorkingDays, $last7WorkDays) {
            $profile = $teacher->teacherProfile;

            $attendedDates = $teacher->attendances->map(function($att) {
                // Account for potential string or Carbon dates stored in DB depending on casting setup
                return is_string($att->date) ? substr($att->date, 0, 10) : $att->date->format('Y-m-d');
            })->toArray();
            
            $attendedCount = count(array_intersect($attendedDates, $last30WorkDays->toArray()));
            $attendancePercentage = $totalWorkingDays > 0 ? round(($attendedCount / $totalWorkingDays) * 100, 1) : 0;

            // Generate chronological sparkline (oldest day to newest day)
            $sparkline = [];
            foreach ($last7WorkDays as $day) {
                array_unshift($sparkline, in_array($day, $attendedDates));
            }

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'gender' => $teacher->gender,
                'profile_picture' => $teacher->profile_picture,
                'attendance_stats' => [
                    'last_30_days_percentage' => $attendancePercentage,
                    'last_30_days_present' => $attendedCount,
                    'total_working_days' => $totalWorkingDays,
                    'recent_7_days_trend' => $sparkline, // perfect for mini graphs (sparklines)
                ],
                'profile' => $profile ? [
                    'date_of_birth' => $profile->date_of_birth,
                    'age' => $profile->date_of_birth ? Carbon::parse($profile->date_of_birth)->age : null,
                    'phone' => $profile->phone,
                    'subject_specialty' => $profile->subject_specialty,
                    'employment_status' => $profile->employment_status,
                    'socials' => [
                        'twitter' => $profile->twitter,
                        'linkedin' => $profile->linkedin,
                        'facebook' => $profile->facebook,
                    ],
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedTeachers
        ]);
    }
}
