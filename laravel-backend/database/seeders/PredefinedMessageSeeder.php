<?php

namespace Database\Seeders;

use App\Models\PredefinedMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PredefinedMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            'Hello! How can I help you?',
            'What is your query?',
            'Please provide more details.',
            'Thank you for reaching out!',
            'We will get back to you shortly.',
        ];

        foreach ($messages as $message) {
            PredefinedMessage::create(['message' => $message]);
        }
    }
}
