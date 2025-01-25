<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View
    {
          // Count total agents
    $totalAgents = Agent::count();

    // Count total conversations
    $totalConversations = Conversation::count();

    // Pass data to the view
    return view('dashboard', compact('totalAgents', 'totalConversations'));
    }
}
