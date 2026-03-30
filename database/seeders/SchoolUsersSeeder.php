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

    private function randomClassInfo(): array
    {
        $classes = [
            ['name' => 'Class 1', 'age' => 6],
            ['name' => 'Class 2', 'age' => 7],
            ['name' => 'Class 3', 'age' => 8],
            ['name' => 'Class 4', 'age' => 9],
            ['name' => 'Class 5', 'age' => 10],
            ['name' => 'Class 6', 'age' => 11],
            ['name' => 'JHS 1', 'age' => 12],
            ['name' => 'JHS 2', 'age' => 13],
            ['name' => 'JHS 3', 'age' => 14],
        ];

        return $classes[array_rand($classes)];
    }

    private function getDobFromAge(int $age): string
    {
        // Random month and day
        $month = str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(mt_rand(1, 28), 2, '0', STR_PAD_LEFT);
        $year = date('Y') - $age;

        return "{$year}-{$month}-{$day}";
    }

    public function run(): void
    {
        // Dynamically resolve school ID to support both local and production
        $school = \App\Models\School::where('id', '019d2d9f-4135-73e1-873d-771335dc2d3f')->first()
               ?? \App\Models\School::where('name', 'LIKE', '%LumiEd%')->first()
               ?? \App\Models\School::first();

        if (!$school) {
            $this->command->error("No school found in the database. Please seed schools first.");
            return;
        }

        $this->schoolId = $school->id;
        $this->command->info("Seeding into school: {$school->name} ({$this->schoolId})");

        // Wipe existing students and teachers globally by email pattern to avoid UniqueConstraintViolation
        // across different school IDs if they were previously seeded.
        $studentEmails = [];
        for ($i = 1; $i <= 100; $i++) $studentEmails[] = "student{$i}@school-prod.com";
        $teacherEmails = [];
        for ($i = 1; $i <= 30; $i++) $teacherEmails[] = "teacher{$i}@school-prod.com";

        $allSeededEmails = array_merge($studentEmails, $teacherEmails);

        // Delete profiles first
        $userIds = User::whereIn('email', $allSeededEmails)->pluck('id');
        StudentProfile::whereIn('user_id', $userIds)->delete();
        TeacherProfile::whereIn('user_id', $userIds)->delete();

        // Delete users
        $deleted = User::whereIn('email', $allSeededEmails)->delete();
        
        $this->command->info("Cleaned up {$deleted} existing entries.");

        // Random gender bias — male% is anywhere from 30% to 70% per run
        $maleBias = mt_rand(30, 70);
        $gender = fn() => mt_rand(1, 100) <= $maleBias ? 'male' : 'female';
        $this->command->info("Gender bias this run: {$maleBias}% male.");

        // ---- 100 Students ----
        for ($i = 1; $i <= 100; $i++) {
            $userId = (string) Str::orderedUuid();
            $classInfo = $this->randomClassInfo();
            
            User::create([
                'id'         => $userId,
                'school_id'  => $this->schoolId,
                'name'       => $this->randomName(),
                'email'      => "student{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'gender'     => $gender(),
            ]);

            StudentProfile::create([
                'user_id'    => $userId,
                'school_id'  => $this->schoolId,
                'class_name' => $classInfo['name'],
                'date_of_birth' => $this->getDobFromAge($classInfo['age']),
            ]);
        }
        $this->command->info('Seeded 100 students with class-appropriate DOB.');

        // ---- 30 Teachers ----
        for ($i = 1; $i <= 30; $i++) {
            $userId = (string) Str::orderedUuid();
            $age = mt_rand(25, 55);
            $name = $this->randomName();
            $genderVal = $gender();

            // Random phone number generator (Ghana format for realism)
            $phone = '0' . mt_rand(20, 59) . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);

            // Simple profile picture from ui-avatars
            $profilePicture = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&color=7F9CF5&background=EBF4FF";

            User::create([
                'id'         => $userId,
                'school_id'  => $this->schoolId,
                'name'       => $name,
                'email'      => "teacher{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'teacher',
                'gender'     => $genderVal,
                'profile_picture' => $profilePicture,
            ]);

            // Fake social handles
            $handle = strtolower(str_replace(' ', '', $name)) . mt_rand(10, 99);

            $subjects = ['Mathematics', 'English Language', 'Integrated Science', 'Social Studies', 'Physics', 'Chemistry', 'Biology', 'Economics', 'Geography', 'History', 'Information Technology', 'French'];
            $randomSubjects = (array) array_rand(array_flip($subjects), mt_rand(1, 2));
            $subjectSpecialty = implode(', ', $randomSubjects);

            TeacherProfile::create([
                'user_id'    => $userId,
                'school_id'  => $this->schoolId,
                'date_of_birth' => $this->getDobFromAge($age),
                'subject_specialty' => $subjectSpecialty,
                'phone'      => $phone,
                'twitter'    => "https://twitter.com/{$handle}",
                'linkedin'   => "https://linkedin.com/in/{$handle}",
                'facebook'   => "https://facebook.com/{$handle}",
            ]);
        }
        $this->command->info('Seeded 30 teachers with realistic DOB, contacts, and socials.');
        $this->command->info('Done: 100 students + 30 teachers seeded successfully.');
    }
}
