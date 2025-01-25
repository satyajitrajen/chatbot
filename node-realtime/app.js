require("dotenv").config();
const express = require("express");
const { Server } = require("socket.io");
const cors = require("cors");
const axios = require("axios");

const app = express();
app.use(cors());
app.use(express.json());

const server = app.listen(3000, () => {
    console.log("Node.js server running on port 3000");
});

const io = new Server(server, {
    cors: { origin: "*" },
});

// Store connected clients
const clients = {}; // Maps client socket IDs to their sockets
const activeConversations = {}; // Maps client socket IDs to assigned agent IDs

/**
 * Handle WebSocket Connections
 */
io.on("connection", (socket) => {
    console.log(`[Connection] User connected: socket.id=${socket.id}`);

    // Register clients automatically
    clients[socket.id] = {
        socket: socket,
        hasSentFirstMessage: false, // Track first message
    };
    console.log(`[Register Client] Registered client with socket.id=${socket.id}`);
    const initialMessage = {
        response: "Welcome to our chat! How can we assist you today?",
        is_agent_chat: false,
    };

    socket.emit("response", initialMessage);
    /**
     * Handle client messages
     */
    socket.on("message", async (data) => {
        console.log(`[Client Message] Received from socket.id=${socket.id}:`, data);


        const clientId = socket.id;
        const clientInfo = clients[clientId];
        const cuser = data.UserId;
        // Check if this is the first message
        if (!clientInfo.hasSentFirstMessage) {
            console.log(`[First Message] Capturing client metadata for socket.id=${clientId}`);

            // Collect metadata
            const ipAddress = socket.handshake.address.replace("::ffff:", ""); // Normalize IP
            const userAgent = socket.handshake.headers["user-agent"];
            const timestamp = new Date().toISOString();

            // Optional: Add geolocation data using IP
            const geoData = await getGeoLocation(ipAddress);

            // Send metadata to Laravel
            const metadataPayload = {
                user_id: clientId,
                ip_address: ipAddress,
                user_agent: userAgent,
                timestamp: timestamp,
                location: geoData, // Geolocation data (optional)
            };

            await forwardMetadataToLaravel(metadataPayload);

            // Mark as first message sent
            clientInfo.hasSentFirstMessage = true;
        }

        const agentId = activeConversations[clientId]; // Check if the agent is assigned

        // Check if the message is genuinely from the user and not a forwarded one
        if (data.is_forwarded) {
            console.log(`[Client Message] Ignoring forwarded message.`);
            return;
        }

        if (agentId) {
            console.log(`[Client Message] Forwarding to Laravel for agent_id=${agentId}`);
            await forwardMessageToLaravel({
                user_id: clientId,
                agent_id: agentId,
                message: data.message,
                sender_type: "user",
                cuser: cuser,
                attachment: data.attachment || null,
            });
        } else {
            console.log(`[Client Message] No agent assigned. Sending to Rasa.`);
            try {
                const rasaPayload = { sender: clientId, message: data.message };
                const rasaUrl = `${process.env.RASA_SERVER_URL}/webhooks/rest/webhook`;
                const rasaResponse = await axios.post(rasaUrl, rasaPayload);

                const rasaMessage = rasaResponse.data[0]?.text || "No response from Rasa.";
                console.log(`[Client Message] Rasa response:`, rasaMessage);


                await forwardMessageToLaravel({
                    user_id: clientId,
                    message: data.message,
                    sender_type: "user",
                    cuser: cuser,
                    attachment: data.attachment || null,
                });

                await forwardMessageToLaravel({
                    user_id: clientId,
                    message: rasaMessage,
                    sender_type: "bot",
                    cuser: cuser,

                });


                if (rasaMessage.toLowerCase().includes("agent")) {
                    console.log(`[Client Message] Rasa suggests agent chat.`);
                    socket.emit("response", { response: "Connecting you to an agent...", is_agent_chat: true });

                    const agentAssigned = await notifyAgents(clientId, data.message);
                    if (agentAssigned.success) {
                        socket.emit("response", {
                            response: `Agent assigned: Agent ID=${agentAssigned.agent_id}`,
                            is_agent_chat: true,
                        });
                    } else {
                        socket.emit("response", {
                            response: "No agents available. Please try again later.",
                            is_agent_chat: false,
                        });
                    }
                } else {
                    socket.emit("response", { response: rasaMessage, is_agent_chat: false });
                }
            } catch (error) {
                console.error(`[Client Message] Error processing message:`, error.message);
                socket.emit("response", { response: "Sorry, something went wrong." });
            }
        }
    });

    /**
     * Handle disconnection
     */
    socket.on("disconnect", () => {
        console.log(`[Disconnect] User disconnected: socket.id=${socket.id}`);
        delete clients[socket.id];

        // Remove active conversation for the disconnected client
        for (const [clientId, agentId] of Object.entries(activeConversations)) {
            if (clientId === socket.id) {
                console.log(`[Disconnect] Clearing active conversation for client_id=${clientId}`);
                delete activeConversations[clientId];
            }
        }
    });
});

/**
 * API Endpoint to handle agent messages
 */
app.post("/api/agent_message", async (req, res) => {
    const { client_id, agent_id, message, attachment_url } = req.body;

    console.log(`[Agent Message API] Received message from agent_id=${agent_id} for client_id=${client_id}`);

    try {
        // Validate input
        if (!client_id || !agent_id || (!message && !attachment_url)) {
            console.error("[Agent Message API] Missing required fields in request body.");
            return res.status(400).send({
                success: false,
                error: "Missing required fields: client_id, agent_id, and either message or attachment_url",
            });
        }

        // Find the client socket using client_id
        const clientInfo = clients[client_id];

        if (clientInfo && clientInfo.socket) {
            const clientSocket = clientInfo.socket; // Access the socket instance
            console.log(`[Agent Message API] Forwarding message to client socket.id=${client_id}`);

            // Prepare the payload
            const payload = {
                response: message || "Attachment received.",
                is_agent_chat: true,
                attachment_url: attachment_url || null, // Include attachment if present
            };

            // Forward the agent's message to the client only
            clientSocket.emit("response", payload);

            console.log(`[Agent Message API] Message successfully forwarded to client.`);
            return res.status(200).send({
                success: true,
                message: "Message delivered to client.",
            });
        } else {
            console.warn(`[Agent Message API] Client not connected: client_id=${client_id}`);
            return res.status(404).send({ success: false, error: "Client not connected" });
        }
    } catch (error) {
        console.error(`[Agent Message API] Unexpected error: ${error.message}`);
        return res.status(500).send({ success: false, error: "Internal server error" });
    }
});



/**
 * Notify Laravel for agent assignment
 */
async function notifyAgents(clientId, message) {
    console.log(`[Notify Agents] Notifying Laravel for clientId=${clientId}`);
    const laravelPayload = { client_id: clientId, message: message };
    try {
        const laravelUrl = `${process.env.LARAVEL_SERVER_URL}/api/notify-agent`;
        const laravelResponse = await axios.post(laravelUrl, laravelPayload);

        console.log(`[Notify Agents] Laravel response:`, laravelResponse.data);

        if (laravelResponse.status === 200 && laravelResponse.data.success) {
            const agentId = laravelResponse.data.agent_id;

            // Assign agent to client
            activeConversations[clientId] = agentId;
            console.log(`[Notify Agents] Agent assigned: agent_id=${agentId} for client_id=${clientId}`);
            return { success: true, agent_id: agentId };
        } else {
            console.error(`[Notify Agents] Laravel failed to assign agent.`);
            return { success: false };
        }
    } catch (error) {
        console.error(`[Notify Agents] Error:`, error.message);
        return { success: false };
    }
}

/**
 * Forward messages to Laravel for storage
 */
async function forwardMessageToLaravel(payload) {
    console.log(`[Forward Message] Payload:`, payload);
    try {
        const laravelUrl = `${process.env.LARAVEL_SERVER_URL}/api/store-message`;
        const response = await axios.post(laravelUrl, payload);

        console.log(`[Forward Message] Laravel response:`, response.data);

        if (response.status === 200) {
            return { success: true, data: response.data };
        } else {
            return { success: false };
        }
    } catch (error) {
        console.error(`[Error] Forwarding message to Laravel:`, error.message);
        return { success: false };
    }
}
async function getGeoLocation(ip) {
    try {
        if (isLocalhost(ip)) {
            return { country: "Localhost", region: "Localhost", city: "Localhost", latitude: null, longitude: null };
        }

        const response = await axios.get(`http://ip-api.com/json/${ip}?fields=status,country,regionName,city,lat,lon,query`);
        if (response.data.status === "fail") {
            console.error(`[GeoLocation] Failed for IP=${ip}: ${response.data.message}`);
            return null;
        }

        return {
            ip: response.data.query,
            country: response.data.country,
            region: response.data.regionName,
            city: response.data.city,
            latitude: response.data.lat,
            longitude: response.data.lon,
        };
    } catch (error) {
        console.error(`[GeoLocation] Error for IP=${ip}:`, error.message);
        return null;
    }
}

/**
 * Utility function to detect localhost IPs
 */
function isLocalhost(ip) {
    return ip === "127.0.0.1" || ip === "::1" || ip.startsWith("::ffff:127.");
}



/**
 * Forward Metadata to Laravel
 */
async function forwardMetadataToLaravel(payload) {
    console.log(`[Forward Metadata] Payload:`, payload);
    try {
        const laravelUrl = `${process.env.LARAVEL_SERVER_URL}/api/store-metadata`;
        const response = await axios.post(laravelUrl, payload);

        console.log(`[Forward Metadata] Laravel response:`, response.data);
        return response.data;
    } catch (error) {
        console.error(`[Forward Metadata] Error forwarding metadata:`, error.message);
    }
}