<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'id'           => 1,
            'username'     => 'admin',
            'email'        => 'admin@teste.com',
            'nivel_acesso' => 'Admin',
            'password'     => Hash::make('password'),
        ]);

        User::factory(10)->create();
    }
}