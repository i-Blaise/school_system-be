<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateUsersToProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6',
            'JHS 1', 'JHS 2', 'JHS 3'
        ];

        // 1. Migrate Students
        $students = \App\Models\User::where('role', 'student')->get();
        $studentProfilesCreated = 0;

        foreach ($students as $student) {
            if (!$student->studentProfile) {
                \App\Models\StudentProfile::create([
                    'user_id' => $student->id,
                    'school_id' => $student->school_id,
                    'class_name' => $classes[array_rand($classes)],
                ]);
                $studentProfilesCreated++;
            }
        }

        // 2. Migrate Teachers
        $teachers = \App\Models\User::where('role', 'teacher')->get();
        $teacherProfilesCreated = 0;

        foreach ($teachers as $teacher) {
            if (!$teacher->teacherProfile) {
                \App\Models\TeacherProfile::create([
                    'user_id' => $teacher->id,
                    'school_id' => $teacher->school_id,
                ]);
                $teacherProfilesCreated++;
            }
        }

        $this->command->info("Migrated {$studentProfilesCreated} students and {$teacherProfilesCreated} teachers to their profile tables.");
    }
}
