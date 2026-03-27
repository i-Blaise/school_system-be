<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BackfillGenderSeeder extends Seeder
{
    public function run(): void
    {
        $genders = ['male', 'female'];

        $updated = 0;
        User::whereNull('gender')->chunk(100, function ($users) use ($genders, &$updated) {
            foreach ($users as $user) {
                $user->update(['gender' => $genders[array_rand($genders)]]);
                $updated++;
            }
        });

        $this->command->info("Done: {$updated} users updated with gender.");
    }
}
