<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Agent::create([
            'name' => 'Agent 1',
            'email' => 'agent1@example.com',
            'is_available' => true,
            'status' => 'active',
        ]);

        Agent::create([
            'name' => 'Agent 2',
            'email' => 'agent2@example.com',
            'is_available' => true,
            'status' => 'active',
        ]);
    }
}
