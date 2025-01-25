(function () {
    const CHAT_WIDGET_ID = "chat-widget";

    if (document.getElementById(CHAT_WIDGET_ID)) {
        console.warn("Chatbot widget already loaded.");
        return;
    }

    const widgetHTML = `
    <div id="${CHAT_WIDGET_ID}" class="fixed bottom-4 right-4 w-80 max-w-full bg-white shadow-lg rounded-lg overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white p-4 flex items-center justify-between">
            <h4 class="font-bold text-lg">Chat with Us</h4>
            <button id="close-chat" class="text-white bg-blue-700 rounded-full w-6 h-6 flex items-center justify-center focus:outline-none hover:bg-blue-800">
                ✕
            </button>
        </div>
        <div id="chat-box" class="flex-1 p-4 max-h-64 overflow-y-auto space-y-2 bg-gray-50">
            <!-- Messages will dynamically appear here -->
        </div>
        <div id="suggestions" class="p-4 flex flex-wrap gap-2 bg-gray-100 border-t"></div>
        <div class="p-4 bg-gray-100 border-t">
            <div class="flex items-center space-x-2">
                <input
                    id="chat-input"
                    class="flex-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Type your message..."
                />
                <button id="send-message" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Send
                </button>
            </div>
        </div>
    </div>
    `;

    const widgetCSS = `
    #chat-widget {
        font-family: 'Arial', sans-serif;
        animation: slideIn 0.3s ease-in-out;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    #chat-box {
        font-size: 0.875rem;
        line-height: 1.25rem;
        scroll-behavior: smooth;
    }
    #suggestions button {
        background-color: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: background-color 0.3s;
        margin: 4px;
    }
    #suggestions button:hover {
        background-color: #e5e7eb;
    }
    #chat-box::-webkit-scrollbar {
        width: 8px;
    }
    #chat-box::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }
    #chat-box::-webkit-scrollbar-track {
        background: #f3f4f6;
    }
    @keyframes slideIn {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }
    `;

    // Inject CSS
    const styleTag = document.createElement("style");
    styleTag.textContent = widgetCSS;
    document.head.appendChild(styleTag);

    // Inject HTML
    const div = document.createElement("div");
    div.innerHTML = widgetHTML;
    document.body.appendChild(div);

    // WebSocket setup
    const socket = io("hhttp://192.168.1.4:4000"); // Update with your server URL
    const chatBox = document.getElementById("chat-box");
    const chatInput = document.getElementById("chat-input");
    const suggestionsDiv = document.getElementById("suggestions");
    const closeChat = document.getElementById("close-chat");
    const sendMessage = document.getElementById("send-message");

    let isAgentChat = false; // Track if in agent chat mode
    let typingTimeout; // Manage typing indicator timeout

    // Helper to add messages
    function addMessage(message, isBot = false) {
        const messageDiv = document.createElement("div");
        messageDiv.className = `mb-2 ${isBot ? "text-left" : "text-right"}`;
        messageDiv.innerHTML = `
            <span class="${isBot ? "bg-gray-200 text-gray-800" : "bg-blue-500 text-white"} p-2 rounded inline-block max-w-xs">
                ${message}
            </span>
        `;
        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Display suggestions
    function displaySuggestions(suggestions) {
        console.log("Displaying suggestions:", suggestions); // Debug log

        suggestionsDiv.innerHTML = ""; // Clear existing suggestions

        if (!suggestions || suggestions.length === 0) {
            console.warn("No suggestions to display."); // Debug empty suggestions
            return;
        }

        suggestions.forEach((suggestion) => {
            const button = document.createElement("button");
            button.textContent = suggestion;
            button.className = "bg-gray-200 text-gray-800 px-3 py-1 rounded-full hover:bg-gray-300";
            button.addEventListener("click", () => {
                console.log("Chip clicked:", suggestion); // Debug chip click
                chatInput.value = suggestion; // Pre-fill input
                sendMessageToServer(); // Automatically send
            });
            suggestionsDiv.appendChild(button);
        });

        console.log("Suggestions rendered successfully.");
    }

    // Handle sending messages
   // Handle sending messages
function sendMessageToServer() {
    const message = chatInput.value.trim();
    if (!message) return;

    addMessage(message); // Show user message

    // Use the current socket.id as the client_id
    const clientId = socket.id; // socket.id is the client ID
    const senderType = "user";  // Always "user" for client messages

    // Emit the message with the client_id (which is socket.id) and sender_type "user"
    socket.emit("message", { client_id: clientId, message, sender_type: senderType });

    chatInput.value = ""; // Clear input
    addTypingIndicator(); // Show typing indicator
}


    // Typing indicator
    function addTypingIndicator() {
        clearTimeout(typingTimeout); // Clear previous timeout
        const typingIndicator = document.getElementById("typing-indicator") || document.createElement("div");
        typingIndicator.id = "typing-indicator";
        typingIndicator.className = "text-left mb-2";
        typingIndicator.innerHTML = `
            <span class="bg-gray-200 text-gray-800 p-2 rounded inline-block">
                Typing...
            </span>
        `;
        chatBox.appendChild(typingIndicator);
        chatBox.scrollTop = chatBox.scrollHeight;

        typingTimeout = setTimeout(() => {
            typingIndicator.remove();
        }, 3000);
    }

    // WebSocket responses
    socket.on("response", (data) => {
        console.log("Response received:", data); // Debug log for response

        document.getElementById("typing-indicator")?.remove();

        if (data.is_agent_chat) {
            isAgentChat = true; // Switch to agent chat mode
        }

        addMessage(data.response, true);

        if (data.chips && data.chips.length > 0) {
            console.log("Chips received from server:", data.chips); // Debug chips
            displaySuggestions(data.chips);
        } else {
            console.warn("No chips received in response."); // Warn if no chips
            suggestionsDiv.innerHTML = ""; // Clear suggestions if none provided
        }
    });

    // Error handling
    socket.on("connect_error", () => {
        addMessage("Unable to connect to the server. Please try again later.", true);
    });

    socket.on("disconnect", () => {
        addMessage("Disconnected from the server. Please refresh the page.", true);
    });

    // Close chat widget
    closeChat.addEventListener("click", () => {
        document.getElementById(CHAT_WIDGET_ID).remove();
    });

    // Send message on "Enter"
    chatInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            sendMessageToServer();
        }
    });

    sendMessage.addEventListener("click", sendMessageToServer);
})();
