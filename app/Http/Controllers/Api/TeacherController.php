<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    /**
     * Store a newly created teacher (or save as draft).
     *
     * POST /api/teachers
     */
    public function store(StoreTeacherRequest $request)
    {
        $validated = $request->validated();
        $admin = $request->user();
        $schoolId = $admin->school_id;
        $isDraft = $validated['status'] === 'draft';

        return DB::transaction(function () use ($validated, $admin, $schoolId, $isDraft) {

            // --- 1. Create the User record ---
            $user = User::create([
                'name'      => $validated['full_name'] ?? 'Draft Teacher',
                'email'     => $validated['email'] ?? $this->generatePlaceholderEmail($schoolId),
                'password'  => bcrypt(Str::random(32)), // random password; admin/teacher resets later
                'school_id' => $schoolId,
                'role'      => 'teacher',
                'gender'    => $validated['gender'] ?? null,
            ]);

            // --- 2. Handle profile photo upload ---
            $profilePhotoPath = null;
            if (isset($validated['profile_photo'])) {
                $profilePhotoPath = $validated['profile_photo']->store('profile_photos', 'public');
                $user->update(['profile_picture' => $profilePhotoPath]);
            }

            // --- 3. Auto-generate IDs ---
            $teacherId = $this->generateTeacherId($schoolId);
            $employeeId = $this->generateEmployeeId($schoolId);

            // --- 4. Create the Teacher Profile ---
            $profile = TeacherProfile::create([
                'user_id'                => $user->id,
                'school_id'              => $schoolId,
                'teacher_id'             => $teacherId,
                'employee_id'            => $employeeId,
                'department'             => $validated['department'] ?? null,
                'designation'            => $validated['designation'] ?? null,
                'joining_date'           => $validated['joining_date'] ?? null,
                'qualification'          => $validated['qualification'] ?? null,
                'subject_specialty'      => $validated['specialization'] ?? null,
                'date_of_birth'          => $validated['date_of_birth'] ?? null,
                'phone'                  => $validated['phone'] ?? null,
                'phone_country_code'     => $validated['phone_country_code'] ?? '+233',
                'address'                => $validated['address'] ?? null,
                'medical_condition_alert'   => $validated['medical_condition_alert'] ?? false,
                'medical_condition_details' => $validated['medical_condition_details'] ?? null,
                'status'                 => $isDraft ? 'draft' : 'active',
                'created_by'             => $admin->id,
            ]);

            // --- 5. Emergency contact ---
            if (!empty($validated['emergency_contact']['name'])) {
                $profile->emergencyContacts()->create([
                    'name'               => $validated['emergency_contact']['name'],
                    'relation'           => $validated['emergency_contact']['relation'] ?? null,
                    'phone_country_code' => $validated['emergency_contact']['phone_country_code'] ?? '+233',
                    'phone'              => $validated['emergency_contact']['phone'] ?? null,
                ]);
            }

            // --- 6. Return response ---
            $profile->load(['user', 'emergencyContacts']);

            return response()->json([
                'success' => true,
                'message' => $isDraft
                    ? 'Teacher saved as draft successfully.'
                    : 'Teacher added successfully.',
                'data' => $this->formatTeacherResponse($profile),
            ], 201);
        });
    }

    /**
     * List all teachers (with optional draft filter).
     *
     * GET /api/teachers
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $schoolId = $request->user()->school_id;
        $status = $request->query('status'); // ?status=draft or ?status=active

        $query = TeacherProfile::where('school_id', $schoolId)
            ->with(['user', 'emergencyContacts']);

        if ($status) {
            $query->where('status', $status);
        }

        $teachers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $teachers->map(fn ($profile) => $this->formatTeacherResponse($profile)),
        ]);
    }

    /**
     * Show a single teacher.
     *
     * GET /api/teachers/{id}
     */
    public function show(\Illuminate\Http\Request $request, string $id)
    {
        $schoolId = $request->user()->school_id;

        $profile = TeacherProfile::where('school_id', $schoolId)
            ->with(['user', 'emergencyContacts'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatTeacherResponse($profile),
        ]);
    }

    // ──────────────────────────────────────────────
    //  Helper methods
    // ──────────────────────────────────────────────

    /**
     * Generate a unique teacher ID like T-0001, T-0002, etc.
     */
    private function generateTeacherId(string $schoolId): string
    {
        $count = TeacherProfile::where('school_id', $schoolId)->count();
        return 'T-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique employee ID like EMP-1001, EMP-1002, etc.
     */
    private function generateEmployeeId(string $schoolId): string
    {
        $count = TeacherProfile::where('school_id', $schoolId)->count();
        return 'EMP-' . (1000 + $count + 1);
    }

    /**
     * Generate a placeholder email for draft teachers who don't have one yet.
     */
    private function generatePlaceholderEmail(string $schoolId): string
    {
        return 'draft-' . Str::uuid() . '@placeholder.local';
    }

    /**
     * Format teacher data for API responses.
     */
    private function formatTeacherResponse(TeacherProfile $profile): array
    {
        $user = $profile->user;

        return [
            'id'                     => $profile->id,
            'teacher_id'             => $profile->teacher_id,
            'employee_id'            => $profile->employee_id,
            'status'                 => $profile->status,

            // Personal info
            'full_name'              => $user->name ?? null,
            'email'                  => $user->email ?? null,
            'gender'                 => $user->gender ?? null,
            'date_of_birth'          => $profile->date_of_birth?->format('Y-m-d'),
            'profile_photo'          => $user->profile_picture ?? null,

            // Professional info
            'department'             => $profile->department,
            'designation'            => $profile->designation,
            'joining_date'           => $profile->joining_date?->format('Y-m-d'),
            'qualification'          => $profile->qualification,
            'specialization'         => $profile->subject_specialty,

            // Contact
            'phone_country_code'     => $profile->phone_country_code,
            'phone'                  => $profile->phone,
            'address'                => $profile->address,

            // Health
            'medical_condition_alert'   => $profile->medical_condition_alert,
            'medical_condition_details' => $profile->medical_condition_details,

            // Emergency contact
            'emergency_contacts'     => $profile->emergencyContacts->map(fn ($ec) => [
                'id'                 => $ec->id,
                'name'               => $ec->name,
                'relation'           => $ec->relation,
                'phone_country_code' => $ec->phone_country_code,
                'phone'              => $ec->phone,
            ]),

            'created_at'             => $profile->created_at,
            'updated_at'             => $profile->updated_at,
        ];
    }
}
