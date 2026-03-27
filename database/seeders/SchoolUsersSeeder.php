<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolUsersSeeder extends Seeder
{
    // Target school ID
    private string $schoolId = '019d2d9f-4135-73e1-873d-771335dc2d3f';

    private function randomName(): string
    {
        $firstNames = ['Ama', 'Kwame', 'Abena', 'Kofi', 'Efua', 'Yaw', 'Akua', 'Kojo', 'Adwoa', 'Kweku',
                       'Naomi', 'James', 'Clara', 'David', 'Grace', 'Samuel', 'Esther', 'Michael', 'Linda', 'Peter'];
        $lastNames  = ['Mensah', 'Boateng', 'Asante', 'Owusu', 'Darko', 'Tetteh', 'Bonsu', 'Acheampong', 'Appiah', 'Nyarko'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function randomClass(): string
    {
        $classes = [
            'Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6',
            'JHS 1', 'JHS 2', 'JHS 3',
        ];

        return $classes[array_rand($classes)];
    }

    public function run(): void
    {
        $this->command->info("Seeding into school ID: {$this->schoolId}");

        // Wipe existing students and teachers + their profiles for this school
        $studentIds = User::where('school_id', $this->schoolId)->where('role', 'student')->pluck('id');
        $teacherIds = User::where('school_id', $this->schoolId)->where('role', 'teacher')->pluck('id');

        StudentProfile::whereIn('user_id', $studentIds)->delete();
        TeacherProfile::whereIn('user_id', $teacherIds)->delete();

        $deleted = User::where('school_id', $this->schoolId)
            ->whereIn('role', ['student', 'teacher'])
            ->delete();
        $this->command->info("Deleted {$deleted} existing students/teachers.");

        // Random gender bias — male% is anywhere from 30% to 70% per run
        $maleBias = mt_rand(30, 70);
        $gender = fn() => mt_rand(1, 100) <= $maleBias ? 'male' : 'female';
        $this->command->info("Gender bias this run: {$maleBias}% male.");

        // ---- 100 Students ----
        $students = [];
        for ($i = 1; $i <= 100; $i++) {
            $students[] = [
                'id'         => (string) Str::orderedUuid(),
                'school_id'  => $this->schoolId,
                'name'       => $this->randomName(),
                'email'      => "student{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'gender'     => $gender(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insert($students);

        // Create student_profiles with class_name
        $studentProfiles = collect($students)->map(fn($s) => [
            'id'         => (string) Str::orderedUuid(),
            'user_id'    => $s['id'],
            'school_id'  => $this->schoolId,
            'class_name' => $this->randomClass(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();
        StudentProfile::insert($studentProfiles);

        $this->command->info('Seeded 100 students with profiles.');

        // ---- 30 Teachers ----
        $teachers = [];
        for ($i = 1; $i <= 30; $i++) {
            $teachers[] = [
                'id'         => (string) Str::orderedUuid(),
                'school_id'  => $this->schoolId,
                'name'       => $this->randomName(),
                'email'      => "teacher{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'teacher',
                'gender'     => $gender(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insert($teachers);

        // Create teacher_profiles
        $teacherProfiles = collect($teachers)->map(fn($t) => [
            'id'         => (string) Str::orderedUuid(),
            'user_id'    => $t['id'],
            'school_id'  => $this->schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();
        TeacherProfile::insert($teacherProfiles);

        $this->command->info('Seeded 30 teachers with profiles.');
        $this->command->info('Done: 100 students + 30 teachers seeded successfully.');
    }
}
