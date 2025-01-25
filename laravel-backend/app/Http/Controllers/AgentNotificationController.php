<?php
namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentNotificationController extends Controller
{
    public function notifyAgent(Request $request)
    {
        Log::info('Payload received for notifying agent:', $request->all());
    
        $clientId = $request->input('client_id');
        $message = $request->input('message');
    
        if (empty($clientId) || empty($message)) {
            Log::error('Invalid payload received.', compact('clientId', 'message'));
            return response()->json(['error' => 'Invalid payload'], 400);
        }
    
        try {
            // Find an available agent
            $agent = Agent::where('status', true)->first();
    
            if (!$agent) {
                Log::warning("No available agents for client ID: {$clientId}");
                return response()->json(['error' => 'No available agents'], 400);
            }
    
            // Mark the agent as unavailable
            $agent->update(['status' => false]);
    
            // Create a conversation entry
             Conversation::create([
                'user_id' =>  $clientId, // Anonymous client
                'agent_id' => $agent->id,
                'message' => $message,
                'sender_type' => 'user',
                'status' => 'sent',
            ]);
    
            Log::info("Agent assigned successfully: {$agent->id}");
    
            return response()->json([
                'success' => true,
                'message' => 'Agent assigned successfully.',
                'agent_id' => $agent->id,
                'client_id' => $clientId,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error during agent assignment: {$e->getMessage()}");
            return response()->json(['error' => 'Agent assignment failed'], 500);
        }
    }
    
}
