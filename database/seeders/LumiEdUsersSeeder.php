<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LumiEdUsersSeeder extends Seeder
{
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
                'name'       => fake()->name(),
                'email'      => "student{$i}@lumied.com",
                'password'   => Hash::make('password'),
                'role'       => 'student',
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
                'name'       => fake()->name(),
                'email'      => "teacher{$i}@lumied.com",
                'password'   => Hash::make('password'),
                'role'       => 'teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        User::insertOrIgnore($teachers);

        $this->command->info('✅ Seeded: 100 students + 30 teachers into LumiEd SHS.');
    }
}
