@extends('layouts.app')

@section('content')
<div class="flex h-[89vh] bg-gray-100 rounded-2xl">
    <!-- Left Sidebar: Contacts List -->
    <div class="w-1/4 bg-white shadow-md overflow-y-auto rounded-2xl">
        <div class="sticky top-0 bg-white z-10 p-6 mb-4 text-lg font-semibold text-gray-800 border-b flex justify-between items-center">
            <span>Conversations</span>
            <div class="relative">
                <select id="conversationFilter" class="block w-full border border-gray-300 rounded-lg bg-white p-2 shadow-sm focus:outline-none focus:ring focus:ring-indigo-300">
                    <option value="all">Chatbot</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="messenger">Messenger</option>
                </select>
            </div>
        </div>
        <div class="space-y-4 p-4" id="contactsList">
            <!-- Grouped by Cuser -->
            @foreach ($conversationsGroupedByCuser as $group)
            <div class="mb-4">
                <button
                    class="w-full flex items-center justify-between bg-indigo-500 text-white p-3 rounded-lg shadow hover:bg-indigo-600 transition duration-200 focus:outline-none"
                    data-collapse-target="group-{{ $group['cuser'] }}">
                    <span>{{ $group['cuser_name'] }}</span>
                    <i class="fas fa-chevron-down transition-transform duration-200"></i>
                </button>
                <div
                    id="group-{{ $group['cuser'] }}"
                    class="hidden mt-2 space-y-2 bg-gray-100 rounded-lg p-3">
                    @foreach ($group['conversations'] as $conversation)
                    <div class="flex items-center space-x-4 border-b p-2 cursor-pointer contact-item transition duration-200"
                        data-chat-id="{{ $conversation['user_id'] }}" data-last-seen="{{ $conversation['last_seen'] }}">
                        <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">
                            {{ substr($conversation['user_id'], 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-800 no-wrap">
                                {{ $conversation['user_id'] }}
                            </div>
                            <div class="text-sm text-gray-500 last-message no-wrap">
                                {{ $conversation['last_message'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <!-- Individual Conversations -->
            <h2 class="text-lg font-semibold mb-4">Individual Conversations</h2>
            @foreach ($groupedConversations as $conversation)
            <div class="flex items-center space-x-4 border-b p-2 cursor-pointer contact-item transition duration-200"
                data-chat-id="{{ $conversation['user_id'] }}" data-last-seen="{{ $conversation['last_seen'] }}">
                <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">
                    {{ substr($conversation['user_id'], 0, 1) }}
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-800 no-wrap">
                        {{ $conversation['user_id'] }}
                    </div>
                    <div class="text-sm text-gray-500 last-message no-wrap">
                        {{ $conversation['last_message'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="contact-item hidden p-4  border-gray-300 rounded-lg bg-white shadow-sm my-2" data-source="whatsapp">
            <h1 class="text-lg font-bold text-gray-800">WhatsApp Conversation</h1>
            <p class="text-sm text-gray-600">No messages found for the selected filter</p>
        </div>
        <div class="contact-item hidden p-4  border-gray-300 rounded-lg bg-white shadow-sm my-2" data-source="messenger">
            <h1 class="text-lg font-bold text-gray-800">Messenger Conversation</h1>
            <p class="text-sm text-gray-600">No messages found for the selected filter</p>
        </div>


    </div>

    <!-- Right Chat Window -->
    <div class="w-3/4 bg-white flex flex-col rounded-2xl">
        <!-- Chat Header (Sticky) -->
        <div class="sticky top-0 p-6 border-b flex justify-between items-center bg-gray-50 z-10 rounded-2xl">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold"
                    id="chatUserAvatar">
                    <!-- Placeholder -->
                </div>
                <div>
                    <div class="font-semibold text-gray-800" id="chatUserName">User ID</div>
                    <div class="text-sm text-gray-500" id="chatStatus">Active now</div>
                </div>
            </div>
            <button
                class="text-gray-500 hover:text-indigo-500 transition duration-200"
                id="detailsButton"
                title="View Details">
                <i class="fas fa-info-circle text-lg"></i>
            </button>
        </div>
        <div id="detailsModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white w-1/3 rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">User Details</h2>
                <div id="metadataContent" class="text-sm text-gray-600 space-y-2">
                    <!-- Metadata details will be injected here -->
                </div>
                <button id="closeModal" class="mt-4 p-2 w-full bg-indigo-500 text-white rounded-lg shadow hover:bg-indigo-600 transition duration-200">
                    Close
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50" id="chatMessages">
            <!-- Messages will be dynamically injected here -->
        </div>

        <!-- Message Input -->
        <div class="p-4 border-t bg-gray-50 rounded-2xl">
            <form id="messageForm" action="{{ route('conversations.sendMessage') }}" method="POST" enctype="multipart/form-data"
                class="relative flex items-center space-x-4 w-full">
                @csrf
                <!-- Hidden input for user_id -->
                <input type="hidden" name="user_id" id="conversationUserId" value="">

                <!-- Suggestions Container -->
                <div id="predefined-suggestions" class="hidden absolute bg-white border rounded-lg shadow-lg w-full max-w-md mb-46 z-50"></div>

                <!-- Message Input Field -->
                <input type="text" name="message" id="chat-input" placeholder="Type your message..."
                    class="flex-1 p-3 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-indigo-300">

                <!-- Attachment Input Field -->
                <label for="attachment" class="flex items-center cursor-pointer">
                    <i class="fas fa-paperclip text-indigo-500 text-xl"></i>
                    <input type="file" name="attachment" id="attachment" class="hidden">
                </label>

                <!-- Submit Button -->
                <button type="submit"
                    class="p-3 bg-indigo-500 text-white rounded-lg shadow hover:bg-indigo-600 transition duration-200">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const collapsibleButtons = document.querySelectorAll('[data-collapse-target]');
        const filterDropdown = document.getElementById("conversationFilter");
        const contactsList = document.getElementById("contactsList");
        filterDropdown.addEventListener("change", function() {
            const filterValue = this.value.toLowerCase();

            // Check if the filter value is 'all'
            if (filterValue === "all") {
                contactsList.classList.remove("hidden"); // Show contacts list
            } else {
                contactsList.classList.add("hidden"); // Hide contacts list
            }

            // Select all `.contact-item` elements
            document.querySelectorAll(".contact-item").forEach((item) => {
                const sourceType = item.getAttribute("data-source");

                // Show or hide based on the filter value
                if (filterValue === "all" || sourceType === filterValue) {
                    item.classList.remove("hidden");
                } else {
                    item.classList.add("hidden");
                }
            });
        });

        collapsibleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-collapse-target');
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    targetElement.classList.toggle('hidden');
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('rotate-180'); // Rotate arrow icon
                    }
                }
            });
        });
    });
</script>
@endsection