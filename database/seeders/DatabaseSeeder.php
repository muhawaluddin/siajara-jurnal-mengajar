<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin default aplikasi.
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Admin->value,
        ]);

        // Pastikan minimal satu user guru tersedia.
        User::factory()->create([
            'name' => 'Guru Utama',
            'email' => 'guru@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Guru->value,
        ]);

        // Panggil seeder domain utama.
        $this->call([
            SubjectSeeder::class,
            StudentSeeder::class,
            AttendanceSeeder::class,
            TeachingJournalSeeder::class,
        ]);
    }
}
