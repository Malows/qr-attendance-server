<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeLocationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Only run in non-production environments
        if (app()->environment('production')) {
            return;
        }

        // Get existing users with employees and locations
        $users = User::with(['employees', 'locations'])->get();

        foreach ($users as $user) {
            // Assign each employee to some of the user's locations randomly
            foreach ($user->employees as $employee) {
                if ($user->locations->count() > 0) {
                    // Randomly assign employee to 1-3 locations
                    $locationsToAssign = $user->locations->random(
                        min($user->locations->count(), random_int(1, 3))
                    );
                    
                    $employee->locations()->attach($locationsToAssign->pluck('id')->toArray());
                }
            }
        }

        $this->command->info('Employee-Location relationships seeded successfully!');
    }
}
