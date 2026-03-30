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

        // Fetch all teachers in this school and eager-load their profile
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->with(['teacherProfile'])
            ->get();

        // Map the data to a cleaner format specifically for the dashboard
        $formattedTeachers = $teachers->map(function ($teacher) {
            $profile = $teacher->teacherProfile;

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'gender' => $teacher->gender,
                'profile_picture' => $teacher->profile_picture,
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
