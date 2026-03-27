<?php

namespace Database\Seeders;

use App\Models\User;
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

    public function run(): void
    {
        $this->command->info("Seeding into school ID: {$this->schoolId}");

        // 100 Students
        $students = [];
        for ($i = 1; $i <= 100; $i++) {
            $students[] = [
                'id'         => Str::orderedUuid(),
                'school_id'  => $this->schoolId,
                'name'       => $this->randomName(),
                'email'      => "student{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insertOrIgnore($students);
        $this->command->info('Seeded 100 students.');

        // 30 Teachers
        $teachers = [];
        for ($i = 1; $i <= 30; $i++) {
            $teachers[] = [
                'id'         => Str::orderedUuid(),
                'school_id'  => $this->schoolId,
                'name'       => $this->randomName(),
                'email'      => "teacher{$i}@school-prod.com",
                'password'   => Hash::make('password'),
                'role'       => 'teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insertOrIgnore($teachers);
        $this->command->info('Seeded 30 teachers.');

        $this->command->info('Done: 100 students + 30 teachers seeded successfully.');
    }
}
