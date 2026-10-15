<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
            [
                "name" => "Pengguna",
                "email" => "Pengguna@gmail.com",
                "password" => Hash::make("password123"),
                "no_hp" => "087645673421",
                "role" => "pengguna",
                "photo_profile" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],[
                "name" => "Admin",
                "email" => "Admin@gmail.com",
                "password" => Hash::make("password123"),
                "no_hp" => "08764567",
                "role" => "admin",
                "photo_profile" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ],[
                "name" => "Petugas",
                "email" => "Petugas@gmail.com",
                "password" => Hash::make("password123"),
                "no_hp" => "087645677688",
                "role" => "petugas",
                "photo_profile" => null,
                "created_at" => now(),
                "updated_at" => now(),
            ]
        ]);
    }
}
