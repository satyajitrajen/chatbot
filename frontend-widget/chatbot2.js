(function () {
    const CHAT_WIDGET_ID = "chat-widget";

    if (document.getElementById(CHAT_WIDGET_ID)) {
        console.warn("Chatbot widget already loaded.");
        return;
    }

    const CHAT_WIDGET_CONFIG = {
        requireLogin: false, // Set to true if login is required
        chatServerUrl: "http://127.0.0.1:3000", // WebSocket chat server URL
        authServerUrl: "http://127.0.0.1:8000", // Authentication server URL
    };

    let isAuthenticated = false; // Track authentication state
    let authToken = null; // Store authentication token
    let clientId = null;
    let UserId = null;
    const widgetHTML = `
    <div id="${CHAT_WIDGET_ID}" class="fixed bottom-4 right-4 w-96 max-w-full bg-white shadow-lg rounded-lg overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-500 text-white px-6 py-4 flex items-center justify-between">
            <h4 class="font-bold text-lg">Chat with Us</h4>
              <div class="flex items-center space-x-2">
            <button id="minimize-chat" class="text-white bg-purple-700 rounded-full w-8 h-8 flex items-center justify-center focus:outline-none hover:bg-purple-800">
                −
            </button>
            <button id="close-chat" class="text-white bg-purple-700 rounded-full w-8 h-8 flex items-center justify-center focus:outline-none hover:bg-purple-800">
                ✕
            </button>
        </div>
        </div>
        <div id="login-modal" class="flex-1 p-6 ${CHAT_WIDGET_CONFIG.requireLogin ? "" : "hidden"}">
            <h3 class="font-semibold text-lg mb-4 text-gray-700">Log In</h3>
            <input id="login-email" type="email" placeholder="Enter your email" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <input id="login-password" type="password" placeholder="Enter your password" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button id="login-button" class="w-full bg-indigo-500 text-white py-3 rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                Log In
            </button>
            <p class="text-sm text-gray-500 mt-4 text-center">Don't have an account? <span id="register-link" class="text-indigo-600 cursor-pointer font-semibold">Register</span></p>
        </div>
        <div id="register-modal" class="hidden flex-1 p-6">
            <h3 class="font-semibold text-lg mb-4 text-gray-700">Create an Account</h3>
            <input id="register-name" type="text" placeholder="Enter your name" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <input id="register-email" type="email" placeholder="Enter your email" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <input id="register-password" type="password" placeholder="Enter your password" class="w-full p-3 mb-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button id="register-button" class="w-full bg-indigo-500 text-white py-3 rounded-lg hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                Register
            </button>
            <p class="text-sm text-gray-500 mt-4 text-center">Already have an account? <span id="login-link" class="text-indigo-600 cursor-pointer font-semibold">Log In</span></p>
        </div>
        <div id="chat-content" class="${CHAT_WIDGET_CONFIG.requireLogin ? "hidden" : ""}">
            <div id="chat-box" class="flex-1 p-6 max-h-72 overflow-y-auto space-y-3 bg-gray-50">
                <!-- Messages will dynamically appear here -->
            </div>
            <div id="suggestions" class="p-4 flex flex-wrap gap-2 bg-gray-100 border-t"></div>
            <div class="p-4 bg-gray-100 border-t">
               <div class="flex items-center space-x-3 bg-white shadow-md rounded-lg p-2">
    <!-- Message Input -->
    <div class="relative flex-1">
        <input
            id="chat-input"
            type="text"
            placeholder="Type your message..."
            class="w-full p-3 pr-10 text-sm border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <!-- Emoji Icon -->
       
    </div>

    <!-- Attachment Button -->
    <label for="attachment-input" class="cursor-pointer flex items-center justify-center w-10 h-10 bg-gray-100 border border-gray-300 rounded-full shadow hover:bg-gray-200 focus:outline-none">
        <input type="file" id="attachment-input" class="hidden" />
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.585 7.413a2 2 0 112.828-2.828l.354.354a2 2 0 010 2.828L10 16.182a4 4 0 11-5.657-5.657l7.071-7.07" />
        </svg>
    </label>

    <!-- Send Button -->
    <button id="send-message" class="flex items-center justify-center w-10 h-10 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </button>
</div>

            </div>
        </div>
    </div><div id="minimized-chat" class="hidden fixed bottom-4 right-4 bg-gradient-to-r from-indigo-600 to-purple-500 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg cursor-pointer">
    💬
</div>`
        ;


    const widgetCSS = `
    #chat-widget {
        font-family: 'Inter', sans-serif;
        animation: slideIn 0.3s ease-in-out;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    #chat-box {
        font-size: 0.875rem;
        line-height: 1.5rem;
        scroll-behavior: smooth;
    }
    .chat-image {
        max-width: 120px;
        max-height: 120px;
        object-fit: cover;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    #suggestions button {
        background-color: #edf2f7;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }
    #suggestions button:hover {
        background-color: #e2e8f0;
        border-color: #cbd5e0;
    }
    #chat-box::-webkit-scrollbar {
        width: 6px;
    }
    #chat-box::-webkit-scrollbar-thumb {
        background: #a0aec0;
        border-radius: 4px;
    }
    #chat-box::-webkit-scrollbar-track {
        background: #edf2f7;
    }
        #minimized-chat {
    animation: fadeIn 0.3s ease-in-out;
}
    @keyframes slideIn {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        } }
            @keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
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

    const chatContent = document.getElementById("chat-content");
    const loginModal = document.getElementById("login-modal");
    const registerModal = document.getElementById("register-modal");
    const loginButton = document.getElementById("login-button");
    const registerButton = document.getElementById("register-button");
    const registerLink = document.getElementById("register-link");
    const loginLink = document.getElementById("login-link");
    const chatInput = document.getElementById("chat-input");
    const chatBox = document.getElementById("chat-box");
    const sendMessage = document.getElementById("send-message");
    const minimizeChatButton = document.getElementById("minimize-chat");
    const minimizedChatIcon = document.getElementById("minimized-chat");
    const closeChatButton = document.getElementById("close-chat");

    // WebSocket setup
    const socket = io(CHAT_WIDGET_CONFIG.chatServerUrl);

    socket.on("connect", () => {
        console.log("WebSocket connected:", socket.id);
        clientId = socket.id; // Set client ID
    });

    socket.on("connect_error", (error) => {
        console.error("WebSocket connection error:", error);
    });
    // Close Chat Button
    closeChatButton.addEventListener("click", () => {
        const chatWidget = document.getElementById(CHAT_WIDGET_ID);
        if (chatWidget) {
            chatWidget.remove(); // Remove the chat widget from the DOM
            console.log("Chat widget closed.");
        }
    });

    minimizeChatButton.addEventListener("click", () => {
        const chatWidget = document.getElementById(CHAT_WIDGET_ID);
        if (chatWidget && minimizedChatIcon) {
            chatWidget.style.display = "none"; // Hide the full chat widget
            minimizedChatIcon.style.display = "flex"; // Show the minimized icon
        }
    });

    // Restore Chat Widget from Minimized Icon
    minimizedChatIcon.addEventListener("click", () => {
        const chatWidget = document.getElementById(CHAT_WIDGET_ID);
        if (chatWidget && minimizedChatIcon) {
            chatWidget.style.display = "flex"; // Show the full chat widget
            minimizedChatIcon.style.display = "none"; // Hide the minimized icon
        }
    });

    socket.on("response", (data) => {
        const messageDiv = document.createElement("div");
        messageDiv.className = "text-left mb-2";

        if (data.attachment_url) {
            const fileExtension = data.attachment_url.split(".").pop().toLowerCase();
            if (["png", "jpg", "jpeg", "gif"].includes(fileExtension)) {
                // Display image attachments
                messageDiv.innerHTML = `
                    <div class="bg-gray-200 text-gray-800 p-2 rounded inline-block max-w-xs">
                        <img src="${data.attachment_url}" alt="Attachment" class="chat-image">
                    </div>`;
            } else {
                // Display other file types as a downloadable link
                const fileName = data.attachment_url.split("/").pop();
                messageDiv.innerHTML = `
                    <div class="bg-gray-200 text-gray-800 p-2 rounded inline-block max-w-xs">
                        <a href="${data.attachment_url}" target="_blank" class="text-blue-500 underline">
                            📎 ${fileName}
                        </a>
                    </div>`;
            }
        } else {
            messageDiv.innerHTML = `
                <span class="bg-gray-200 text-gray-800 p-2 rounded inline-block max-w-xs">
                    ${data.response}
                </span>`;
        }


        chatBox.appendChild(messageDiv);
        chatBox.scrollTop = chatBox.scrollHeight;
    });


    // Login Flow
    loginButton.addEventListener("click", async () => {
        const email = document.getElementById("login-email").value.trim();
        const password = document.getElementById("login-password").value.trim();

        if (!email || !password) {
            alert("Please enter your email and password.");
            return;
        }

        try {
            const response = await fetch(`${CHAT_WIDGET_CONFIG.authServerUrl}/api/login`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ email, password }),
            });

            const data = await response.json();
            if (response.ok) {
                isAuthenticated = true;
                authToken = data.token;
                UserId = data.user.id;
                chatContent.classList.remove("hidden");
                loginModal.classList.add("hidden");
            } else {
                alert(data.message || "Login failed. Please try again.");
            }
        } catch (error) {
            console.error("Login error:", error);
            alert("Login failed. Please try again.");
        }
    });

    // Registration Flow
    registerButton.addEventListener("click", async () => {
        const name = document.getElementById("register-name").value.trim();
        const email = document.getElementById("register-email").value.trim();
        const password = document.getElementById("register-password").value.trim();

        if (!name || !email || !password) {
            alert("Please fill all fields.");
            return;
        }

        try {
            const response = await fetch(`${CHAT_WIDGET_CONFIG.authServerUrl}/api/register`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name, email, password }),
            });

            const data = await response.json();
            if (response.ok) {
                alert("Registration successful! Please log in.");
                registerModal.classList.add("hidden");
                loginModal.classList.remove("hidden");
            } else {
                alert(data.message || "Registration failed. Please try again.");
            }
        } catch (error) {
            console.error("Registration error:", error);
            alert("Registration failed. Please try again.");
        }
    });

    // Toggle between login and registration
    registerLink.addEventListener("click", () => {
        loginModal.classList.add("hidden");
        registerModal.classList.remove("hidden");
    });

    loginLink.addEventListener("click", () => {
        registerModal.classList.add("hidden");
        loginModal.classList.remove("hidden");
    });

    // Send message
    // Send message
    sendMessage.addEventListener("click", async () => {
        const message = chatInput.value.trim();
        const attachmentInput = document.getElementById("attachment-input");
        const file = attachmentInput.files[0]; // Get the selected file

        if (!message && !file) {
            alert("Please provide a message or attach a file.");
            return;
        }

        // Show user message in the chat
        if (message) {
            const userMessageDiv = document.createElement("div");
            userMessageDiv.className = "text-right mb-2";
            userMessageDiv.innerHTML = `
                <div class="bg-blue-500 text-white p-2 rounded inline-block max-w-xs">
                    ${message}
                </div>`;
            chatBox.appendChild(userMessageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        // Show attachment preview
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const fileDiv = document.createElement("div");
                fileDiv.className = "text-right mb-2";

                const fileExtension = file.name.split(".").pop().toLowerCase();
                if (["png", "jpg", "jpeg", "gif"].includes(fileExtension)) {
                    // Display image attachment
                    fileDiv.innerHTML = `
                        <div class="bg-blue-500 text-white p-2 rounded inline-block max-w-xs">
                            <img src="${e.target.result}" alt="Attachment" class="chat-image">
                        </div>`;
                } else {
                    // Display other file types as a downloadable link
                    fileDiv.innerHTML = `
                        <div class="bg-blue-500 text-white p-2 rounded inline-block max-w-xs">
                            <a href="${e.target.result}" download="${file.name}" class="text-white underline">
                                📎 ${file.name}
                            </a>
                        </div>`;
                }

                chatBox.appendChild(fileDiv);
                chatBox.scrollTop = chatBox.scrollHeight;

                // Send the file to the server
                socket.emit("message", {
                    clientId,
                    sender_type: "user",
                    authToken,
                    UserId,
                    message: message || null,
                    attachment: {
                        name: file.name,
                        type: file.type,
                        data: e.target.result, // Base64 encoded file data
                    },
                });
            };
            reader.readAsDataURL(file);
        }
        else {
            // Send only the message if no file is attached
            socket.emit("message", { clientId, sender_type: "user", authToken, UserId, message });
        }

        // Clear the input fields
        chatInput.value = "";
        attachmentInput.value = null;
    });


})();
