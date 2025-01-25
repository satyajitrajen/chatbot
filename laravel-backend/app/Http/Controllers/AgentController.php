<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    /**
     * Display a listing of the agents.
     */
    public function index()
    {
        $agents = Agent::all();
        Log::info('Fetched all agents.', ['count' => $agents->count()]);
        return view('agent', compact('agents'));
    }

    /**
     * Store a newly created agent in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the input
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);
    
            // Create the User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'agent', // Assign a default role for agents
            ]);
    
            // Log the user creation
            Log::info('User created successfully.', ['user_id' => $user->id]);
    
            // Create the Agent associated with the User
            $agent = Agent::create([
                'name' => $request->name,
                'user_id' => $user->id,
            ]);
    
            // Log the agent creation
            Log::info('Agent created successfully.', ['agent_id' => $agent->id]);
    
            return redirect()->route('agents.index')->with('success', 'User and Agent created successfully.');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Failed to create user and agent: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create user and agent. Please try again.');
        }
    }
    
    

    

    /**
     * Show the form for editing the specified agent.
     */
    public function edit(Agent $agent)
    {
        Log::info("Editing agent.", ['agent_id' => $agent->id, 'name' => $agent->name]);
        return view('agent-edit', compact('agent'));
    }

    /**
     * Update the specified agent in storage.
     */
    public function update(Request $request, Agent $agent)
    {
        try {
            // Validate the input
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $agent->user_id, // Validate against users table
            ]);
    
            // Update the associated User
            $user = $agent->user; // Get the associated User
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);
    
            // Log the user update
            Log::info('User updated successfully.', ['user_id' => $user->id]);
    
            // Update the Agent
            $agent->update([
                'is_available' => $request->has('is_available'),
            ]);
    
            // Log the agent update
            Log::info('Agent updated successfully.', ['agent_id' => $agent->id]);
    
            return redirect()->route('agents.index')->with('success', 'User and Agent updated successfully.');
        } catch (\Exception $e) {
            // Log the error and redirect back with an error message
            Log::error('Failed to update user and agent: ' . $e->getMessage());
            return redirect()->route('agents.index')->with('error', 'Failed to update user and agent. Please try again.');
        }
    }
    
    /**
     * Remove the specified agent from storage.
     */
    public function destroy(Agent $agent)
    {
        try {
            $agentId = $agent->id;
            $agentName = $agent->name;
            $agent->delete();

            Log::info("Agent deleted successfully.", ['agent_id' => $agentId, 'name' => $agentName]);
            return redirect()->route('agents.index')->with('success', 'Agent deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to delete agent: " . $e->getMessage());
            return redirect()->route('agents.index')->with('error', 'Failed to delete agent.');
        }
    }
}
