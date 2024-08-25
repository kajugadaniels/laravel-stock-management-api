<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeederTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Managing Director',
            'email' => 'md@jabana.com',
            'password' => Hash::make('123'),
            'role' => 'Manager',
        ]);

        User::create([
            'name' => 'Store Keeper',
            'email' => 'storekeeper@jabana.com',
            'password' => Hash::make('123'),
            'role' => 'Storekeeper',
        ]);

        User::create([
            'name' => 'Production',
            'email' => 'production@jabana.com',
            'password' => Hash::make('123'),
            'role' => 'Production',
        ]);
    }
}
