@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Agents Card -->
        <div class="bg-white p-6 shadow-lg rounded-lg text-center hover:shadow-xl transition duration-300">
            <h2 class="text-xl font-semibold text-gray-700 uppercase tracking-wide">Total Agents</h2>
            <p class="text-5xl font-bold text-blue-500 mt-4">{{ $totalAgents }}</p>
            <div class="mt-4">
                <a href="{{ route('agents.index') }}" class="text-sm text-blue-500 hover:text-blue-700 underline">
                    View All Agents
                </a>
            </div>
        </div>

        <!-- Total Conversations Card -->
        <div class="bg-white p-6 shadow-lg rounded-lg text-center hover:shadow-xl transition duration-300">
            <h2 class="text-xl font-semibold text-gray-700 uppercase tracking-wide">Total Conversations</h2>
            <p class="text-5xl font-bold text-green-500 mt-4">{{ $totalConversations }}</p>
            <div class="mt-4">
                <a href="{{ route('conversations.index') }}" class="text-sm text-green-500 hover:text-green-700 underline">
                    View All Conversations
                </a>
            </div>
        </div>

        <!-- Placeholder for Additional Card -->
        <div class="bg-white p-6 shadow-lg rounded-lg text-center hover:shadow-xl transition duration-300">
            <h2 class="text-xl font-semibold text-gray-700 uppercase tracking-wide">Insights</h2>
            <p class="text-2xl font-medium text-gray-500 mt-4">Coming Soon!</p>
            <div class="mt-4">
                <a href="#" class="text-sm text-gray-500 hover:text-gray-700 underline">
                    Learn More
                </a>
            </div>
        </div>
    </div>

    <!-- Add a Spacer or Additional Section -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Overview</h3>
        <div class="bg-gray-50 p-6 rounded-lg shadow-md">
            <p class="text-gray-600">
                Welcome to your dashboard! Here, you can track and manage your agents, conversations, and other key metrics. Explore the cards above for quick insights, or use the navigation menu to dive deeper.
            </p>
        </div>
    </div>
@endsection
