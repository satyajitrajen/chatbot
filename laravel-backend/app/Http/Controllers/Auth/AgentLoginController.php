<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AgentLoginController extends Controller
{

    
  /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.agent-login');
    }

    /**
     * Handle agent login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the agent by email
        $agent = Agent::where('email', $request->email)->first();

        if ($agent && Hash::check($request->password, $agent->password)) {
            // Store agent info in the session
            session(['agent_id' => $agent->id, 'agent_name' => $agent->name]);

            Log::info('Agent logged in successfully.', ['email' => $request->email]);
            return redirect()->route('agents.index')->with('success', 'Welcome back!');
        }

        Log::warning('Agent login failed.', ['email' => $request->email]);
        return redirect()->back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    /**
     * Logout the agent.
     */
    public function logout()
    {
        session()->forget(['agent_id', 'agent_name']);
        Log::info('Agent logged out.');
        return redirect()->route('agent.login')->with('success', 'Logged out successfully.');
    }

}
