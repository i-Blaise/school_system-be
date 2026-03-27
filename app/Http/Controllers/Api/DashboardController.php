<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SchoolAward;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $enrolledStudents = User::where('school_id', $schoolId)->where('role', 'student')->count();
        $activeTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->count();
        $supportStaff = User::where('school_id', $schoolId)->whereNotIn('role', ['admin', 'student', 'teacher'])->count();
        
        $totalAwards = SchoolAward::where('school_id', $schoolId)->count();

        // Onboarding Check
        $hasProfile = \App\Models\SchoolProfile::where('school_id', $schoolId)->exists();
        $hasStudents = User::where('school_id', $schoolId)->where('role', 'student')->exists();
        $hasTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->exists();

        return response()->json([
            'overview' => [
                'enrolled_students' => $enrolledStudents,
                'active_teachers' => $activeTeachers,
                'support_staff' => $supportStaff,
                'total_awards' => $totalAwards,
            ],
            'onboarding' => [
                'has_profile' => $hasProfile,
                'has_students' => $hasStudents,
                'has_teachers' => $hasTeachers,
            ],
            'students_by_gender' => [
                'total' => $enrolledStudents,
                'boys'  => User::where('school_id', $schoolId)->where('role', 'student')->where('gender', 'male')->count(),
                'girls' => User::where('school_id', $schoolId)->where('role', 'student')->where('gender', 'female')->count(),
            ],
            'students_by_class' => StudentProfile::where('school_id', $schoolId)
                ->select('class_name', DB::raw('count(*) as count'))
                ->groupBy('class_name')
                ->get()
                ->pluck('count', 'class_name'),
            'student_performance' => [
                'labels' => ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => []
            ],
            'events' => [],
            'earnings' => [
                'labels' => [],
                'datasets' => []
            ],
            'student_attendance' => [
                'labels' => [],
                'datasets' => []
            ],
            'to_do_list' => []
        ]);
    }
}
