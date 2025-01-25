<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RasaService
{
    protected $client;
    protected $rasaUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->rasaUrl = env('RASA_SERVER_URL', 'http://localhost:5005/webhooks/rest/webhook');
    }

    public function sendMessage($message, $sender)
    {
        try {
            $response = $this->client->post($this->rasaUrl, [
                'json' => [
                    'sender' => $sender,
                    'message' => $message,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error("Rasa service error: " . $e->getMessage());
            return [
                ['text' => 'Sorry, I am unable to process your request at the moment.'],
            ];
        }
    }
}
