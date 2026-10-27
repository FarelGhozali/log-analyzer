<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('ids.admin.email')],
            [
                'name' => config('ids.admin.name'),
                'password' => config('ids.admin.password'),
            ],
        );

        $this->call(ExpertRuleSeeder::class);
    }
}
