<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{

    public function index()
    {
        $user = Auth::user(); // Get the authenticated user
    
        // Initialize variables for storing data
        $groupedConversations = collect();
        $conversationsGroupedByCuser = collect();
    
        if ($user->role === 'User') {
            // Fetch all conversations grouped by user_id and `cuser`
            $allConversations = Conversation::select('user_id', 'cuser', DB::raw('MAX(created_at) as last_seen'), DB::raw('MAX(id) as last_message_id'))
                ->groupBy('user_id', 'cuser')
                ->orderBy('last_seen', 'desc')
                ->get()
                ->map(function ($conversation) {
                    $lastMessage = Conversation::where('id', $conversation->last_message_id)->value('message');
                    $cuserName = User::where('id', $conversation->cuser)->value('name'); // Fetch name of the cuser
                    return [
                        'user_id' => $conversation->user_id,
                        'cuser' => $conversation->cuser,
                        'cuser_name' => $cuserName ?? 'Unknown', // Fallback if name not found
                        'last_message' => $lastMessage ?? 'No messages yet',
                        'last_seen' => Carbon::parse($conversation->last_seen)->toISOString(),
                    ];
                });
    
            // Filter out conversations with non-null `cuser` and group them by `cuser`
            $conversationsGroupedByCuser = $allConversations->whereNotNull('cuser')->groupBy('cuser')->map(function ($conversations, $cuser) {
                $cuserName = User::where('id', $cuser)->value('name'); // Fetch cuser name
                return [
                    'cuser' => $cuser,
                    'cuser_name' => $cuserName ?? 'Unknown',
                    'conversations' => $conversations,
                ];
            })->values();
    
            // Filter out conversations already included in `conversationsGroupedByCuser`
            $groupedConversations = $allConversations->whereNull('cuser');
        } else {
            // Fetch conversations for the agent
            $agent = Agent::where('user_id', $user->id)->first();
    
            if (!$agent) {
                return redirect()->back()->with('error', 'Agent not found.');
            }
    
            $allConversations = Conversation::where('agent_id', $agent->id)
                ->select('user_id', 'cuser', DB::raw('MAX(created_at) as last_seen'), DB::raw('MAX(id) as last_message_id'))
                ->groupBy('user_id', 'cuser')
                ->orderBy('last_seen', 'desc')
                ->get()
                ->map(function ($conversation) {
                    $lastMessage = Conversation::where('id', $conversation->last_message_id)->value('message');
                    $cuserName = User::where('id', $conversation->cuser)->value('name'); // Fetch name of the cuser
                    return [
                        'user_id' => $conversation->user_id,
                        'cuser' => $conversation->cuser,
                        'cuser_name' => $cuserName ?? 'Unknown', // Fallback if name not found
                        'last_message' => $lastMessage ?? 'No messages yet',
                        'last_seen' => Carbon::parse($conversation->last_seen)->toISOString(),
                    ];
                });
    
            // Filter out conversations with non-null `cuser` and group them by `cuser`
            $conversationsGroupedByCuser = $allConversations->whereNotNull('cuser')->groupBy('cuser')->map(function ($conversations, $cuser) {
                $cuserName = User::where('id', $cuser)->value('name'); // Fetch cuser name
                return [
                    'cuser' => $cuser,
                    'cuser_name' => $cuserName ?? 'Unknown',
                    'conversations' => $conversations,
                ];
            })->values();
    
            // Filter out conversations already included in `conversationsGroupedByCuser`
            $groupedConversations = $allConversations->whereNull('cuser');
        }
    
        return view('conversations', compact('groupedConversations', 'conversationsGroupedByCuser'));
    }
    
    // In ConversationController.php
public function getPredefinedMessages()
{
    $messages = PredefinedMessage::all(); // Assuming you have a table for predefined messages
    return response()->json($messages);
}

    
    



    public function loadChat($userId)
    {
        $conversations = Conversation::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'sender_type' => $message->sender_type,
                    'message' => $message->message,
                    'created_at' => $message->created_at->toISOString(),
                    'attachment_url' => $message->attachment_url
                ];
            });

        return response()->json($conversations);
    }





    public function sendMessages(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|string',
            'message' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|max:2048', // Attachment validation (max 2MB)
            'is_forwarded' => 'nullable|boolean',
        ]);

        // Handle forwarded messages (skip storage and processing)
        if ($request->input('is_forwarded', false)) {
            Log::info('Forwarded message received. Skipping storage.', [
                'user_id' => $validatedData['user_id'],
                'message' => $validatedData['message'],
            ]);

            return response()->json(['success' => true, 'message' => 'Forwarded message received and ignored.']);
        }

        $userId = Auth::id();

        // Verify if the authenticated user is an agent
        $agent = Agent::where('user_id', $userId)->first();

        if (!$agent) {
            Log::error('Agent not found for authenticated user', ['user_id' => $userId]);
            return response()->json(['success' => false, 'error' => 'You are not authorized to send messages as an agent.'], 403);
        }

        $agentId = $agent->id;
        $attachmentUrl = null;

        try {
            // Handle attachment upload if present
            if ($request->hasFile('attachment')) {
                $attachmentUrl =  $this->storeAttachment($request->file('attachment'));
            }

            // Save the conversation in the database
            $conversation = Conversation::create([
                'user_id' => $validatedData['user_id'],
                'message' => $validatedData['message'],
                'attachment_url' => $attachmentUrl,
                'agent_id' => $agentId,
                'sender_type' => 'agent',
            ]);

            Log::info('Message saved successfully', [
                'conversation_id' => $conversation->id,
                'user_id' => $conversation->user_id,
                'agent_id' => $conversation->agent_id,
                'message' => $conversation->message,
                'attachment_url' => $conversation->attachment_url,
            ]);
            $fullAttachmentUrl = $attachmentUrl ? url($attachmentUrl) : null; 
            // Prepare payload for the external API
            $payload = [
                'client_id' => $validatedData['user_id'],
                'agent_id' => $agentId,
                'message' => $validatedData['message'],
                'attachment_url' => $fullAttachmentUrl,
                'is_forwarded' => true, // Mark the message as forwarded
            ];
            Log::info('Payload forwarded to external API:', $payload);


            $response = Http::post('http://127.0.0.1:3000/api/agent_message', $payload);

            if ($response->successful()) {
                Log::info('Message forwarded to external API successfully.');
                return response()->json(['success' => true, 'message' => 'Message sent successfully and forwarded to the client!']);
            } else {
                Log::error('Failed to forward message to external API', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return response()->json(['success' => false, 'error' => 'Message saved but failed to forward to the client.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred during sendMessage process', [
                'exception_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'error' => 'Message could not be processed. Please try again.'], 500);
        }
    }
    private function storeAttachment($attachment)
    {
        if (!$attachment->isValid()) {
            throw new \Exception('Invalid attachment uploaded.');
        }
    
        // Generate a unique file name with the original extension
        $fileName = uniqid() . '_' . $attachment->getClientOriginalName();
    
        // Store the file in the "uploads" directory within the public disk
        $filePath = $attachment->storeAs('uploads', $fileName, 'public');
    
        // Return the publicly accessible URL of the stored file
        return Storage::url($filePath);
    }
    
}
