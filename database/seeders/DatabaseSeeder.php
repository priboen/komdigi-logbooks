<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Database\Factories\ProjectFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Testing\Fakes\Fake;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'roles' => 'admin',
            'password' => Hash::make('12345678'),
            'phone' => '08123456789'
        ]);
        User::factory()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@mail.com',
            'roles' => 'supervisor',
            'password' => Hash::make('12345678'),
            'phone' => '08123456788'
        ]);
        User::factory()->create([
            'name' => 'Student Alpha',
            'email' => 'student@mail.com',
            'roles' => 'student',
            'password' => Hash::make('12345678'),
            'phone' => '08123456789'
        ]);
        User::factory()->create([
            'name' => 'Student Beta',
            'email' => 'student1@mail.com',
            'roles' => 'student',
            'password' => Hash::make('12345678'),
            'phone' => '08223456789'
        ]);
        User::factory()->create([
            'name' => 'Student Charlie',
            'email' => 'student2@mail.com',
            'roles' => 'student',
            'password' => Hash::make('12345678'),
            'phone' => '08323456789'
        ]);
        ProjectFactory::new()->count(10)->create();
    }
}
