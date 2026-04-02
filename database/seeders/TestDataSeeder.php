<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Broker;
use App\Models\Client;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create broker
        $broker = Broker::create([
            'name' => 'Juan García',
            'email' => 'juan@broker.com',
            'phone' => '+34 612 345 678',
            'license_number' => 'LIC001',
            'commission_rate' => 3.5,
            'company_name' => 'García Inmobiliaria',
            'bio' => 'Especialista en propiedades residenciales',
            'status' => 'active',
        ]);

        // Create clients
        Client::create([
            'name' => 'María López',
            'email' => 'maria@client.com',
            'phone' => '+34 623 456 789',
            'address' => 'Calle Principal 123',
            'city' => 'Madrid',
            'budget_min' => 250000,
            'budget_max' => 400000,
            'property_type' => 'casa',
            'broker_id' => $broker->id,
        ]);

        Client::create([
            'name' => 'Antonio Ruiz',
            'email' => 'antonio@client.com',
            'phone' => '+34 634 567 890',
            'address' => 'Avenida del Sol 456',
            'city' => 'Barcelona',
            'budget_min' => 350000,
            'budget_max' => 550000,
            'property_type' => 'apartamento',
            'broker_id' => $broker->id,
        ]);
    }
}
