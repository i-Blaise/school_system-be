<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TeacherProfile;
use App\Models\TeacherEmergencyContact;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DummyTeacherProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

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

        $countNew = 0;
        $countUpdated = 0;

        foreach ($teachers as $teacher) {
            $medicalAlert = $faker->boolean(20); // 20% chance of true
            
            $profileData = [
                'school_id' => $teacher->school_id,
                'teacher_id' => 'TCH-' . strtoupper(Str::random(6)),
                'employee_id' => 'EMP-' . $faker->unique()->numerify('####'),
                'department' => $faker->randomElement($departments),
                'designation' => $faker->randomElement($designations),
                'joining_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'qualification' => $faker->randomElement($qualifications),
                // Only overwrite these if they are empty
                'subject_specialty' => $faker->word(),
                'date_of_birth' => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
                'phone' => $faker->numerify('##########'), 
                'phone_country_code' => '+1', 
                'address' => $faker->address(),
                'medical_condition_alert' => $medicalAlert,
                'medical_condition_details' => $medicalAlert ? $faker->sentence() : null,
                'status' => 'published', // Move out of draft
                'created_by' => $teacher->id,
                'twitter' => $faker->userName(),
                'linkedin' => $faker->userName(),
                'facebook' => $faker->userName(),
                'employment_status' => $faker->randomElement($employmentStatuses),
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
                    if (is_null($profile->$key) || $profile->$key === '' || $key === 'status') {
                        $updateData[$key] = $value;
                    }
                }
                // Specifically update status to active/published just for testing UI
                $updateData['status'] = 'active'; 
                $profile->update($updateData);
                $countUpdated++;
            }

            // Create 1 or 2 emergency contacts if they don't have any
            if ($profile->emergencyContacts()->count() === 0) {
                $contactCount = $faker->numberBetween(1, 2);
                for ($i = 0; $i < $contactCount; $i++) {
                    TeacherEmergencyContact::create([
                        'teacher_profile_id' => $profile->id,
                        'name' => $faker->name(),
                        'relation' => $faker->randomElement($relations),
                        'phone_country_code' => '+1',
                        'phone' => $faker->numerify('##########'),
                    ]);
                }
            }
        }
        
        $this->command->info("Teacher profiles seeded successfully. Created: $countNew, Updated: $countUpdated.");
    }
}
