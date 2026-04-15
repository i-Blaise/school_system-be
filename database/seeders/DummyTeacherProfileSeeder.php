<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\TeacherEmergencyContact;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class DummyTeacherProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users who are teachers
        $teachers = User::whereIn('role', ['teacher', 'Teacher'])->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers found to seed profiles for.');
            return;
        }

        $departments = ['Science', 'Mathematics', 'Language Arts', 'Social Studies', 'Physical Education', 'Arts'];
        $designations = ['Senior Teacher', 'Assistant Teacher', 'Head of Department', 'Class Teacher', 'Subject Teacher'];
        $qualifications = ['B.Ed', 'M.Ed', 'B.Sc', 'M.Sc', 'BA', 'MA', 'Ph.D'];
        $employmentStatuses = ['Full Time', 'Part Time', 'Contract'];
        $relations = ['Spouse', 'Parent', 'Sibling', 'Friend', 'Child', 'Emergency'];
        $specialties = ['Physics', 'Algebra', 'English', 'History', 'Biology', 'Chemistry'];
        $dummyNames = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Williams', 'Charlie Brown', 'Eva Green'];

        $countNew = 0;
        $countUpdated = 0;

        foreach ($teachers as $index => $teacher) {
            $medicalAlert = rand(1, 100) <= 20; // 20% chance of true
            
            $profileData = [
                'school_id' => $teacher->school_id,
                'teacher_id' => 'TCH-' . strtoupper(Str::random(6)),
                'employee_id' => 'EMP-' . str_pad($index + 1000, 4, '0', STR_PAD_LEFT),
                'department' => Arr::random($departments),
                'designation' => Arr::random($designations),
                'joining_date' => date('Y-m-d', strtotime('-' . rand(0, 1800) . ' days')),
                'qualification' => Arr::random($qualifications),
                // Only overwrite these if they are empty
                'subject_specialty' => Arr::random($specialties),
                'date_of_birth' => date('Y-m-d', strtotime('-' . rand(25 * 365, 60 * 365) . ' days')),
                'phone' => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT), 
                'phone_country_code' => '+1', 
                'address' => rand(10, 999) . ' Dummy Street, Simulation City',
                'medical_condition_alert' => $medicalAlert,
                'medical_condition_details' => $medicalAlert ? 'Requires periodic checkups.' : null,
                'registration_status' => 'completed', // Move out of draft
                'status' => 'Active', // The HR employment state
                'created_by' => $teacher->id,
                'twitter' => 'teacher_' . Str::random(5),
                'linkedin' => 'teacher_' . Str::random(5),
                'facebook' => 'teacher_' . Str::random(5),
                'employment_status' => Arr::random($employmentStatuses),
            ];

            if (!$teacher->teacherProfile) {
                // Create
                $profileData['user_id'] = $teacher->id;
                $profile = TeacherProfile::create($profileData);
                $countNew++;
            } else {
                // Update existing profile with dummy data for any missing fields
                $profile = $teacher->teacherProfile;
                
                $updateData = [];
                foreach ($profileData as $key => $value) {
                    if (is_null($profile->$key) || $profile->$key === '' || $key === 'registration_status' || $key === 'status') {
                        $updateData[$key] = $value;
                    }
                }
                // Specifically update status to active/published just for testing UI
                $updateData['registration_status'] = 'completed'; 
                $profile->update($updateData);
                $countUpdated++;
            }

            // Create 1 or 2 emergency contacts if they don't have any
            if ($profile->emergencyContacts()->count() === 0) {
                $contactCount = rand(1, 2);
                for ($i = 0; $i < $contactCount; $i++) {
                    TeacherEmergencyContact::create([
                        'teacher_profile_id' => $profile->id,
                        'name' => Arr::random($dummyNames),
                        'relation' => Arr::random($relations),
                        'phone_country_code' => '+1',
                        'phone' => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                    ]);
                }
            }
        }
        
        $this->command->info("Teacher profiles seeded successfully. Created: $countNew, Updated: $countUpdated.");
    }
}
