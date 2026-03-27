<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LumiEdUsersSeeder extends Seeder
{
    private function randomName(): string
    {
        $firstNames = ['Ama', 'Kwame', 'Abena', 'Kofi', 'Efua', 'Yaw', 'Akua', 'Kojo', 'Adwoa', 'Kweku',
                       'Naomi', 'James', 'Clara', 'David', 'Grace', 'Samuel', 'Esther', 'Michael', 'Linda', 'Peter'];
        $lastNames  = ['Mensah', 'Boateng', 'Asante', 'Owusu', 'Darko', 'Tetteh', 'Bonsu', 'Acheampong', 'Appiah', 'Nyarko'];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    public function run(): void
    {
        $school = School::firstOrCreate(
            ['slug' => 'lumied-shs'],
            ['name' => 'LumiEd SHS']
        );

        $this->command->info("Seeding into school: {$school->name} ({$school->id})");

        // 100 Students
        $students = [];
        for ($i = 1; $i <= 100; $i++) {
            $students[] = [
                'id'         => Str::orderedUuid(),
                'school_id'  => $school->id,
                'name'       => $this->randomName(),
                'email'      => "student{$i}@lumied.com",
                'password'   => Hash::make('password'),
                'role'       => 'student',
                'gender'     => ['male', 'female'][array_rand(['male', 'female'])],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insertOrIgnore($students);

        // 30 Teachers
        $teachers = [];
        for ($i = 1; $i <= 30; $i++) {
            $teachers[] = [
                'id'         => Str::orderedUuid(),
                'school_id'  => $school->id,
                'name'       => $this->randomName(),
                'email'      => "teacher{$i}@lumied.com",
                'password'   => Hash::make('password'),
                'role'       => 'teacher',
                'gender'     => ['male', 'female'][array_rand(['male', 'female'])],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insertOrIgnore($teachers);

        $this->command->info('Seeded: 100 students + 30 teachers into LumiEd SHS.');
    }
}
