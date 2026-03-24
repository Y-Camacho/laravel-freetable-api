<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@freetable.test'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->update(['role' => 'admin']);
        }

        if (method_exists($admin, 'assignRole') && !$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        for ($i = 1; $i <= 30; $i++) {
            $client = User::firstOrCreate(
                ['email' => "client{$i}@freetable.test"],
                [
                    'name' => "Cliente {$i}",
                    'password' => 'password',
                    'role' => 'client',
                ]
            );

            if ($client->role !== 'client') {
                $client->update(['role' => 'client']);
            }

            if (method_exists($client, 'assignRole') && !$client->hasRole('client')) {
                $client->assignRole('client');
            }
        }
    }
}