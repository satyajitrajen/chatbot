document.addEventListener("DOMContentLoaded", function () {
    const chatMessagesContainer = document.getElementById("chatMessages");
    const chatUserAvatar = document.getElementById("chatUserAvatar");
    const chatUserName = document.getElementById("chatUserName");
    const chatStatus = document.getElementById("chatStatus");
    const lastActiveTime = document.getElementById("lastActiveTime");
    const messageForm = document.getElementById("messageForm");
    const contactsList = document.getElementById("contactsList");
    const conversationUserIdField = document.getElementById("conversationUserId");
    const inputField = document.getElementById("chat-input");
    const suggestionsContainer = document.getElementById("predefined-suggestions");
    initializePredefinedSuggestions("chat-input", "predefined-suggestions", "/api/predefined-messages");

    console.log("Initializing chat functionality...");
    window.Echo.channel('chat').listen('MessageSent', (e) => {
        console.log('New message received:', e.message);
        handleNewMessage(e.message);
    });
    /**
     * Creates a new conversation and adds it to the contacts list.
     * @param {string} userId - The ID of the user.
     * @param {string} message - The last message in the conversation.
     * @param {string} timestamp - The timestamp of the message.
     */
    function createNewConversation(userId, message, timestamp) {
        const contactsList = document.getElementById("contactsList");

        // Create a new conversation item
        const conversationItem = document.createElement("div");
        conversationItem.classList.add(
            "flex",
            "items-center",
            "space-x-4",
            "border-b",
            "p-2",
            "cursor-pointer",
            "contact-item",
            "transition",
            "duration-200"
        );
        conversationItem.setAttribute("data-chat-id", userId);
        conversationItem.setAttribute("data-last-seen", timestamp);

        conversationItem.innerHTML = `
        <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">
            ${userId.charAt(0)}
        </div>
        <div>
            <div class="font-medium text-gray-800">${userId}</div>
            <div class="text-sm text-gray-500">
                <span class="last-message">${message}</span>
            </div>
        </div>
    `;

        // Append the new conversation to the contacts list
        contactsList.appendChild(conversationItem);
    }

    function handleNewMessage(message) {
        // Check if the conversation exists
        let existingChat = document.querySelector(`.contact-item[data-chat-id="${message.user_id}"]`);

        if (existingChat) {
            // Update the last message preview and highlight as unread
            existingChat.classList.add("bg-yellow-100", "font-bold"); // Mark as unread
            existingChat.querySelector('.last-message').textContent = message.message;
        } else {
            // Add a new conversation
            createNewConversation(message.user_id, message.message, message.created_at);
        }

        // Append the message if the conversation is active
        if (conversationUserIdField.value === message.user_id) {
            appendMessage(message, "user");
            markConversationAsRead(message.user_id);
        }
    }
    /**
   * Loads the chat messages for a specific user.
   * @param {string} userId - The user ID to load chat for.
   */
    function loadChat(userId) {
        fetch(`/conversations/loadChat/${userId}`)
            .then((response) => response.json())
            .then((data) => {
                // Safely update UI details
                if (chatUserAvatar) chatUserAvatar.textContent = userId.charAt(0);
                if (chatUserName) chatUserName.textContent = `Chat ID: ${userId}`;
                if (chatStatus) chatStatus.textContent = "Active now";

                // Clear and populate messages
                if (chatMessagesContainer) {
                    chatMessagesContainer.innerHTML = "";
                    data.forEach((msg) => appendMessage(msg, msg.sender_type));
                    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                }

                // Mark as read
                markConversationAsRead(userId);
            })
            .catch((error) => console.error("Error loading chat messages:", error));
    }


    /**
     * Appends a message to the chat container.
     */
    /**
     * Appends a message to the chat container.
     * @param {Object} msg - The message object containing the message details.
     * @param {string} type - The type of message ("user" or "receiver").
     */
    function appendMessage(msg, type) {
        const messageElement = document.createElement("div");
        const messageTime = new Date(msg.created_at).toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
        });

        // Construct message content dynamically based on conditions
        let messageContent = "";
        if (msg.message) {
            messageContent += `<p class="text-gray-800 mb-2">${msg.message}</p>`;
        }
        if (msg.attachment_url) {
            messageContent += getAttachmentHTML(msg.attachment_url);
        }

        if (type === "user") {
            messageElement.classList.add("flex", "items-start", "space-x-4");
            messageElement.innerHTML = `
                <div class="w-12 h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold">
                    U
                </div>
                <div class="bg-indigo-100 p-4 rounded-lg max-w-lg shadow-sm">
                    ${messageContent}
                    <div class="flex justify-end mt-2 text-xs text-gray-500">${messageTime}</div>
                </div>
            `;
        } else {
            messageElement.classList.add("flex", "items-end", "justify-end", "space-x-4");
            messageElement.innerHTML = `
                <div class="bg-white p-4 rounded-lg max-w-lg border border-gray-300 shadow-sm">
                    ${messageContent}
                    <div class="flex justify-end mt-2 text-xs text-gray-500">${messageTime}</div>
                </div>
            `;
        }

        chatMessagesContainer.appendChild(messageElement);
        chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight; // Scroll to bottom
    }


    // Fetch predefined messages from the server
    async function fetchPredefinedMessages() {
        try {
            const response = await fetch("/api/predefined-messages"); // Adjust API URL if needed
            return await response.json();
        } catch (error) {
            console.error("Error fetching predefined messages:", error);
            return [];
        }
    }

    // Display predefined message suggestions
    function showSuggestions(messages, query) {
        suggestionsContainer.innerHTML = ""; // Clear previous suggestions
        suggestionsContainer.classList.add("hidden");

        if (query.trim() === "") return;

        const filteredMessages = messages.filter((msg) =>
            msg.message.toLowerCase().includes(query.toLowerCase())
        );

        if (filteredMessages.length > 0) {
            suggestionsContainer.classList.remove("hidden");

            filteredMessages.forEach((msg) => {
                const suggestionItem = document.createElement("div");
                suggestionItem.textContent = msg.message;
                suggestionItem.className = "p-2 cursor-pointer hover:bg-gray-100";
                suggestionItem.addEventListener("click", () => {
                    inputField.value = msg.message; // Set the message in the input field
                    suggestionsContainer.classList.add("hidden");
                });
                suggestionsContainer.appendChild(suggestionItem);
            });
        }
    }

    /**
     * Generates HTML for the attachment.
     * @param {string} attachmentUrl - The URL of the attachment.
     * @returns {string} - HTML string for the attachment.
     */
    function getAttachmentHTML(attachmentUrl, fileName = null) {
        const fileExtension = (fileName || attachmentUrl.split('/').pop()).split('.').pop().toLowerCase();

        if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
            // Render image preview
            return `
                <div class="w-full h-auto mb-2">
                    <img src="${attachmentUrl}" alt="${fileName || 'Attachment'}" class="rounded-lg shadow-md max-w-full">
                </div>
            `;
        } else {
            // Render other file types (e.g., PDF, DOCX) as a downloadable link
            const displayFileName = fileName || attachmentUrl.split('/').pop();
            return `
                <div class="flex items-center space-x-2 mt-2">
                    <i class="fas fa-paperclip text-indigo-500"></i>
                    <a href="${attachmentUrl}" class="text-indigo-600 underline" target="_blank">${displayFileName}</a>
                </div>
            `;
        }
    }






    /**
     * Marks a conversation as read and removes highlight.
     * @param {string} userId - The user ID of the conversation to mark as read.
     */
    function markConversationAsRead(userId) {
        const chatItem = document.querySelector(`.contact-item[data-chat-id="${userId}"]`);
        if (chatItem) {
            chatItem.classList.remove("bg-yellow-100", "font-bold");
        }
    }

    /**
     * Updates the active conversation and loads its chat.
     */
    /**
 * Updates the active conversation and loads its chat.
 */
    contactsList.addEventListener("click", function (event) {
        // Get the closest `.contact-item` ancestor for the clicked element
        const activeItem = event.target.closest(".contact-item");

        console.log("Click event detected on contacts list.");
        console.log(event.target);

        if (activeItem) {
            // Retrieve the user ID and last seen from the clicked conversation
            const userId = activeItem.getAttribute("data-chat-id");
            const lastSeen = activeItem.getAttribute("data-last-seen");

            // Debugging log to verify the user ID and last seen are being captured
            console.log(`Clicked on user ID: ${userId}, Last Seen: ${lastSeen}`);

            // Highlight the active conversation
            document.querySelectorAll(".contact-item").forEach((item) => {
                item.classList.remove("bg-indigo-100", "rounded-2xl");
            });
            activeItem.classList.add("bg-indigo-100", "rounded-2xl");

            // Update the hidden field with the active user ID
            conversationUserIdField.value = userId;

            // Update last active time (optional)
            if (lastActiveTime) {
                lastActiveTime.textContent = timeAgo(lastSeen);
            }

            // Load the chat for the selected conversation
            loadChat(userId);
        } else {
            console.warn("No valid conversation item was clicked.");
        }
    });



    /**
     * Formats the time for the "last active" display.
     */
    function timeAgo(lastSeen) {
        const now = new Date();
        const diff = Math.floor((now - new Date(lastSeen)) / 1000);

        if (diff < 60) return "Active just now";
        if (diff < 3600) return `Last active ${Math.floor(diff / 60)} minutes ago`;
        if (diff < 86400) return `Last active ${Math.floor(diff / 3600)} hours ago`;
        return `Last active ${Math.floor(diff / 86400)} days ago`;
    }

    /**
     * Automatically load the first conversation if available.
     */
    const firstChat = document.querySelector(".contact-item");
    if (firstChat) {
        const firstUserId = firstChat.getAttribute("data-chat-id");
        conversationUserIdField.value = firstUserId;
        firstChat.classList.add("bg-indigo-100", "rounded-2xl");
        loadChat(firstUserId);
    }

    // Handle message form submission
    messageForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(messageForm);
        const userId = conversationUserIdField.value;
        const message = formData.get("message").trim();
        const attachmentInput = messageForm.querySelector("input[name='attachment']");
        const attachmentFile = attachmentInput?.files[0] || null; // Get file input if exists
        const timestamp = new Date().toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
        });

        // Validate that at least a message or attachment is provided
        if (!message && !attachmentFile) {
            alert("Please provide a message or attach a file.");
            return;
        }

        // Clear the input fields
        messageForm.querySelector("input[name='message']").value = "";
        if (attachmentInput) {
            attachmentInput.value = ""; // Clear attachment input
        }

        // Build the dynamic message content
        let messageContent = "";
        if (message) {
            messageContent += `<p class="text-gray-800 mb-2">${message}</p>`;
        }

        if (attachmentFile) {
            const fileReader = new FileReader();
            fileReader.onload = function (e) {
                const attachmentHTML = getAttachmentHTML(e.target.result, attachmentFile.name);

                // Append the message with attachment to the chat
                const messageElement = document.createElement("div");
                messageElement.classList.add("flex", "items-end", "justify-end", "space-x-4");
                messageElement.innerHTML = `
                    <div class="bg-white p-4 rounded-lg max-w-lg border border-gray-300 shadow-sm">
                        ${messageContent}
                        ${attachmentHTML}
                        <div class="flex justify-end mt-2 text-xs text-gray-500">${timestamp}</div>
                    </div>
                `;
                chatMessagesContainer.appendChild(messageElement);
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            };
            fileReader.readAsDataURL(attachmentFile);
        } else {
            // Handle plain message without attachment
            const messageElement = document.createElement("div");
            messageElement.classList.add("flex", "items-end", "justify-end", "space-x-4");
            messageElement.innerHTML = `
                <div class="bg-white p-4 rounded-lg max-w-lg border border-gray-300 shadow-sm">
                    ${messageContent}
                    <div class="flex justify-end mt-2 text-xs text-gray-500">${timestamp}</div>
                </div>
            `;
            chatMessagesContainer.appendChild(messageElement);
            chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
        }

        // Send the form data to the server
        if (attachmentFile) {
            formData.append("attachment", attachmentFile);
        }
        fetch(messageForm.action, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
            },
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (!data.success) {
                    alert("Failed to send the message.");
                }
            })
            .catch((error) => {
                console.error("Error sending message:", error);
                alert("Failed to send the message.");
            });
    });









    const detailsButton = document.getElementById("detailsButton");
    const detailsModal = document.getElementById("detailsModal");
    const metadataContent = document.getElementById("metadataContent");
    const closeModal = document.getElementById("closeModal");

    // Open the modal and fetch metadata
    detailsButton.addEventListener("click", function () {
        const userId = document.getElementById("conversationUserId").value;
        if (!userId) {
            alert("No user selected.");
            return;
        }

        // Fetch metadata from server
        fetch(`/api/metadata/${userId}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const metadata = data.metadata;

                    metadataContent.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user text-indigo-500"></i>
                            <p><strong>Chat ID:</strong> ${metadata.user_id}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-network-wired text-indigo-500"></i>
                            <p><strong>IP Address:</strong> ${metadata.ip_address || "N/A"}</p>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-globe text-indigo-500"></i>
                            <p><strong>Country:</strong> ${metadata.country || "N/A"}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-map-marker-alt text-indigo-500"></i>
                            <p><strong>Region:</strong> ${metadata.region || "N/A"}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-city text-indigo-500"></i>
                            <p><strong>City:</strong> ${metadata.city || "N/A"}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-map text-indigo-500"></i>
                            <p><strong>Latitude:</strong> ${metadata.latitude || "N/A"}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-map-pin text-indigo-500"></i>
                            <p><strong>Longitude:</strong> ${metadata.longitude || "N/A"}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-clock text-indigo-500"></i>
                            <p><strong>Timestamp:</strong> ${new Date(metadata.timestamp).toLocaleString()}</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-desktop text-indigo-500"></i>
                            <p><strong>User Agent:</strong> ${metadata.user_agent || "N/A"}</p>
                        </div>
                    </div>
                `;


                } else {
                    metadataContent.innerHTML = `<p class="text-red-500">Failed to load metadata.</p>`;
                }

                // Show modal
                detailsModal.classList.remove("hidden");
            })
            .catch((error) => {
                console.error("Error fetching metadata:", error);
                metadataContent.innerHTML = `<p class="text-red-500">Error fetching metadata.</p>`;
                detailsModal.classList.remove("hidden");
            });
    });

    // Close the modal
    closeModal.addEventListener("click", function () {
        detailsModal.classList.add("hidden");
    });

   
});

function initializePredefinedSuggestions(inputFieldId, suggestionsContainerId, apiEndpoint) {
        const inputField = document.getElementById(inputFieldId);
        const suggestionsContainer = document.getElementById(suggestionsContainerId);
        let predefinedMessages = [];

        // Fetch predefined messages from the server
        async function fetchPredefinedMessages() {
            try {
                const response = await fetch(apiEndpoint);
                predefinedMessages = await response.json();
            } catch (error) {
                console.error("Error fetching predefined messages:", error);
            }
        }

        // Show suggestions based on user input
        function showSuggestions(query) {
            suggestionsContainer.innerHTML = ""; // Clear previous suggestions
            suggestionsContainer.classList.add("hidden");

            if (query.trim() === "") return;

            const filteredMessages = predefinedMessages.filter((msg) =>
                msg.message.toLowerCase().includes(query.toLowerCase())
            );

            if (filteredMessages.length > 0) {
                suggestionsContainer.classList.remove("hidden");

                filteredMessages.forEach((msg) => {
                    const suggestionItem = document.createElement("div");
                    suggestionItem.textContent = msg.message;
                    suggestionItem.className = "p-2 cursor-pointer hover:bg-gray-100 mb-2";
                    suggestionItem.addEventListener("click", () => {
                        inputField.value = msg.message; // Set the message in the input field
                        suggestionsContainer.classList.add("hidden");
                    });
                    suggestionsContainer.appendChild(suggestionItem);
                });
            }
        }

        // Hide suggestions when clicking outside
        document.addEventListener("click", (e) => {
            if (!suggestionsContainer.contains(e.target) && e.target !== inputField) {
                suggestionsContainer.classList.add("hidden");
            }
        });

        // Add event listener for input field
        inputField.addEventListener("input", (event) => {
            const query = event.target.value;
            showSuggestions(query);
        });

        // Initialize predefined messages on load
        fetchPredefinedMessages();
    }
