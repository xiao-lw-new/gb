<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RootUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'name'    => 'Root',
                'address' => '0x0000000000000000000000000000000000000000',
                'p_id'    => 0,
                'path'    => '|',
                'status'  => 1,
                'active'  => 1,
                'password' => Hash::make('root_mg_ecosystem_safe_pwd'),
            ]
        );
    }
}
