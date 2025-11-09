// proxy.js
// This script connects to the MSMP WebSocket and provides a REST API for PHP.

const express = require('express');
const WebSocket = require('ws');

// --- CONFIGURATION ---
const MSMP_URL = 'ws://127.0.0.1:25585';
const MSMP_SECRET = 'YOUR_40_CHAR_SUPER_SECRET_KEY'; // MUST match server.properties
const PROXY_PORT = 8081; // The port your PHP website will talk to
// ---------------------

const app = express();
let ws;
let rpcId = 1;
const rpcCallbacks = new Map();

function connectToMSMP() {
    console.log('Connecting to Minecraft Server Management Protocol...');
    
    // Set the authentication header
    const headers = {
        'Authorization': `Bearer ${MSMP_SECRET}`
    };

    ws = new WebSocket(MSMP_URL, { headers });

    ws.on('open', () => {
        console.log('Successfully connected to MSMP!');
        
        // Optional: Discover all available commands
        // sendRpcRequest('rpc.discover').then(console.log).catch(console.error);
    });

    ws.on('message', (data) => {
        const response = JSON.parse(data.toString());
        
        // Check if this is a response to a request we sent
        if (response.id && rpcCallbacks.has(response.id)) {
            const callback = rpcCallbacks.get(response.id);
            if (response.error) {
                callback.reject(response.error);
            } else {
                callback.resolve(response.result);
            }
            rpcCallbacks.delete(response.id);
        } else if (response.method) {
            // This is a server notification (e.g., player joined)
            // console.log('Server Notification:', response);
        }
    });

    ws.on('close', () => {
        console.log('Disconnected from MSMP. Reconnecting in 5 seconds...');
        setTimeout(connectToMSMP, 5000);
    });

    ws.on('error', (err) => {
        console.error('MSMP WebSocket error:', err.message);
    });
}

// Function to send a JSON-RPC request and get a promise-based response
function sendRpcRequest(method, params = {}) {
    return new Promise((resolve, reject) => {
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            return reject(new Error('MSMP WebSocket is not connected.'));
        }

        const id = rpcId++;
        const payload = {
            jsonrpc: '2.0',
            id: id,
            method: method,
            params: params
        };

        // Register the callback for this request ID
        rpcCallbacks.set(id, { resolve, reject });
        
        // Send the request
        ws.send(JSON.stringify(payload));
        
        // Timeout if no response in 5s
        setTimeout(() => {
            if (rpcCallbacks.has(id)) {
                rpcCallbacks.delete(id);
                reject(new Error('RPC request timed out.'));
            }
        }, 5000);
    });
}

// Start the WebSocket connection
connectToMSMP();

// --- Create the REST API for PHP ---

app.get('/players', async (req, res) => {
    try {
        // MSMP provides separate methods for connected and offline players
        const connectedPlayers = await sendRpcRequest('minecraft:players/connected/list');
        const offlinePlayers = await sendRpcRequest('minecraft:players/offline/list'); // Note: This gets *all* known players
        
        // You'll need to figure out playtime. MSMP might not provide this.
        // For now, we combine them.
        res.json({ connected: connectedPlayers, offline: offlinePlayers });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/bans', async (req, res) => {
    try {
        // The method is likely 'minecraft:banned_players/list' or similar.
        // You MUST use 'rpc.discover' to find the exact method name.
        const bans = await sendRpcRequest('minecraft:banned_players/list');
        res.json(bans);
    } catch (error) {
        // This will likely fail until you find the right method name
        res.status(500).json({ error: error.message, tip: "Method 'minecraft:banned_players/list' might be wrong. Use rpc.discover to find the correct one." });
    }
});

app.get('/ops', async (req, res) => {
    try {
        // This method is confirmed in the docs
        const ops = await sendRpcRequest('minecraft:operators/list');
        res.json(ops);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// Start the Express server
app.listen(PROXY_PORT, () => {
    console.log(`MSMP Proxy REST API listening at http://localhost:${PROXY_PORT}`);
});