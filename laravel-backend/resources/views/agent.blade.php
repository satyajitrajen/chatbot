@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto">
    <!-- Page Title -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Agents Management</h1>
        <!-- Add Agent Button -->
        <button data-modal-target="addAgentModal" data-modal-toggle="addAgentModal"
            class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition">
            + Add Agent
        </button>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
    <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4">
        {{ session('error') }}
    </div>
    @endif

    @if (session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
    @endif

    <!-- Agents Table -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="table-auto w-full text-left border-collapse">
            <thead class="bg-gray-100 text-gray-700 text-sm font-semibold">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm divide-y divide-gray-200">
                @forelse ($agents as $agent)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">{{ $agent->user->name }}</td>
                    <td class="px-6 py-4">{{ $agent->user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="{{ $agent->is_available ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $agent->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center space-x-2">
                        <!-- Edit Button -->
                        <button data-modal-target="editAgentModal-{{ $agent->id }}" data-modal-toggle="editAgentModal-{{ $agent->id }}"
                            class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition">
                            Edit
                        </button>
                        <!-- Delete Button -->
                        <button data-modal-target="deleteAgentModal-{{ $agent->id }}" data-modal-toggle="deleteAgentModal-{{ $agent->id }}"
                            class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition">
                            Delete
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center px-6 py-4 text-gray-500">No agents found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-modal id="addAgentModal" title="Add Agent">
        <form action="{{ route('agents.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-3 rounded-md shadow hover:bg-blue-700 transition focus:ring focus:ring-blue-300">
                    Add Agent
                </button>
            </div>
        </form>
    </x-modal>
    <!-- Edit Agent Modal -->
    @foreach ($agents as $agent)
    <x-modal id="editAgentModal-{{ $agent->id }}" title="Edit Agent">
        <form action="{{ route('agents.update', $agent->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name-{{ $agent->id }}" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name-{{ $agent->id }}" name="name" value="{{ $agent->user->name }}"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div>
                    <label for="email-{{ $agent->id }}" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email-{{ $agent->id }}" name="email" value="{{ $agent->user->email }}"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="password-{{ $agent->id }}" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password-{{ $agent->id }}" name="password"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Leave blank to keep the current password.</p>
                </div>
                <div>
                    <label for="is_available-{{ $agent->id }}" class="block text-sm font-medium text-gray-700">Availability</label>
                    <input type="checkbox" id="is_available-{{ $agent->id }}" name="is_available"
                        class="mt-2 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        {{ $agent->is_available ? 'checked' : '' }}>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="bg-yellow-500 text-white px-6 py-3 rounded-md hover:bg-yellow-600 transition focus:ring focus:ring-yellow-300">
                    Update Agent
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Delete Agent Modal -->
    <x-modal id="deleteAgentModal-{{ $agent->id }}" title="Delete Agent">
        <p class="text-gray-700 text-base">
            Are you sure you want to delete <span class="font-semibold">{{ $agent->user->name }}</span>? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-4 mt-6">
            <button type="button" data-modal-hide="deleteAgentModal-{{ $agent->id }}"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                Cancel
            </button>
            <form action="{{ route('agents.destroy', $agent->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition focus:ring focus:ring-red-300">
                    Delete
                </button>
            </form>
        </div>
    </x-modal>
    @endforeach
</div>
@endsection