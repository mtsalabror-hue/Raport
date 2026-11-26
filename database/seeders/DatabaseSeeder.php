<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator']);
        $guruRole = Role::create(['name' => 'Guru Mata Pelajaran', 'slug' => 'guru', 'description' => 'Guru']);
        $waliRole = Role::create(['name' => 'Wali Kelas', 'slug' => 'wali_kelas', 'description' => 'Wali Kelas']);

        User::create(['name' => 'Admin SMK', 'email' => 'admin@smk.test', 'password' => Hash::make('password'), 'role_id' => $adminRole->id, 'is_active' => true]);
        User::create(['name' => 'Guru Demo', 'email' => 'guru@smk.test', 'password' => Hash::make('password'), 'role_id' => $guruRole->id, 'is_active' => true]);
        User::create(['name' => 'Wali Demo', 'email' => 'wali@smk.test', 'password' => Hash::make('password'), 'role_id' => $waliRole->id, 'is_active' => true]);
    }
}
