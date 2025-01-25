@extends('layouts.app2')

@section('content')
<div class="flex min-h-screen items-center bg-gray-100">
    <!-- First Section: Sliders -->
    <div class="flex-1 min-h-screen bg-white flex flex-col items-center justify-center">
        <!-- Heading -->

        <!-- Image Container -->
        <div class="h-full w-full max-w-lg flex items-center justify-center">
            <img src="{{ asset('images/logo.png') }}" class="max-h-full object-contain" alt="Logo">
        </div>
        <h1 class="text-2xl py-5 font-bold text-gray-800 mb-4">Welcome to Chatbot</h1>

    </div>




    <!-- Second Section: Login -->
    <div class="w-1/3 py-12 px-6  h-full flex items-center justify-center">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Admin Login</h2>
            @if ($errors->any())
            <div class="mb-4">
                <ul class="list-disc list-inside text-red-500">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="w-full p-2 border rounded" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="w-full p-2 border rounded" required>
                </div>
                <div class="mb-4 flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="mr-2">
                    <label for="remember" class="text-gray-700">Remember Me</label>
                </div>
                <div class="flex justify-center mb-4">
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</button>
                </div>
            </form>
            
        </div>
    </div>
</div>
@endsection