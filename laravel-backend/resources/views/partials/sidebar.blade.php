<div class="w-64 bg-white h-screen shadow-lg flex flex-col">
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-gray-800">ChatBot</h1>
    </div>
    <ul class="space-y-2 p-4 flex-1 overflow-y-auto">
        <!-- Common items -->

        @if (auth()->check() && auth()->user()->role === 'agent')
        <!-- Agent-specific links -->
        <li>
            <a href="{{ route('dashboard') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('dashboard') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>


        <li>
            <a href="{{ route('conversations.index') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('conversations.index') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-comments"></i>
                <span>Conversations</span>
            </a>
        </li>
        @elseif (auth()->check() && auth()->user()->role === 'User')
        <!-- Admin-specific links -->
        <li>
            <a href="{{ route('dashboard') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('dashboard') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('agents.index') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('agents.index') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-user-friends"></i>
                <span>Agents</span>
            </a>
        </li>
        <li>
            <a href="{{ route('conversations.index') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('conversations.index') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-comments"></i>
                <span>Conversations</span>
            </a>
        </li>
        <li>
            <a href="{{ route('reports') }}"
                class="flex items-center space-x-3 p-3 rounded-lg 
                    {{ request()->routeIs('reports') ? 'bg-blue-500 text-white' : 'text-gray-700 hover:text-white hover:bg-blue-500 transition' }}">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        </li>
        @endif
    </ul>
    <div class="p-4 border-t">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="block text-gray-600 hover:text-blue-500 transition flex items-center">
                <i class="fas fa-sign-out-alt"></i> <span class="ml-2">Logout</span>
            </button>
        </form>
    </div>

</div>