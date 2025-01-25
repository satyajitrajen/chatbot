<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function storeMessage(Request $request)
{
    Log::info('Received payload for storing message:', $request->all());

    $validator = Validator::make($request->all(), [
        'user_id' => 'required|string',
        'message' => 'nullable|string',
        'attachment' => 'nullable|array',
        'attachment.name' => 'nullable|string',
        'attachment.type' => 'nullable|string',
        'attachment.data' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed for message storage.', ['errors' => $validator->errors()]);
        return response()->json(['error' => 'Validation failed.', 'details' => $validator->errors()], 400);
    }

    $validatedData = $request->all();
    if (isset($validatedData['attachment'])) {
        try {
            $validatedData['attachment_path'] = $this->storeAttachment($validatedData['attachment']);
        } catch (\Exception $e) {
            Log::error('Attachment handling failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Attachment handling failed.'], 500);
        }
    }

    try {
        // Save the message to the database
        $message = Conversation::create([
            'user_id' => $validatedData['user_id'],
            'agent_id' => $validatedData['agent_id'] ?? null,
            'message' => $validatedData['message'] ?? null,
            'sender_type' => $validatedData['sender_type'] ?? null,
            'cuser' => $validatedData['cuser'] ?? null,
            'attachment_url' => $validatedData['attachment_path'] ?? null,
        ]);

        Log::info('Message stored successfully.', ['message' => $message]);

        // Broadcast the message
        Log::info('Attempting to broadcast the message...', ['message' => $message]);
        broadcast(new MessageSent($message))->toOthers();
        Log::info('Broadcast completed successfully.');

        return response()->json(['success' => true, 'message' => $message], 200);
    } catch (\Exception $e) {
        Log::error('Error storing message:', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to store message.'], 500);
    }
}

private function storeAttachment($attachment)
{
    if (!isset($attachment['data']) || !str_contains($attachment['data'], 'base64,')) {
        throw new \Exception('Invalid attachment data.');
    }

    $decodedData = base64_decode(explode(',', $attachment['data'])[1], true);
    if (!$decodedData) {
        throw new \Exception('Failed to decode Base64 data.');
    }

    $filePath = 'uploads/' . uniqid() . '_' . $attachment['name'];
    Storage::disk('public')->put($filePath, $decodedData);

    return Storage::url($filePath);
}

}
