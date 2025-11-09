// proxy.js
// Now supports managing multiple servers from a JSON file.

const express = require('express');
const WebSocket = require('ws');
const fs = require('fs');

// --- CONFIGURATION ---
const SERVERS_CONFIG_FILE = 'servers.json';
const PROXY_PORT = 8081; // The port your PHP website will talk to
// ---------------------

const app = express();
app.use(express.json());
const servers = JSON.parse(fs.readFileSync(SERVERS_CONFIG_FILE, 'utf8'));

// We now use Maps to store connections and callbacks, keyed by server ID
const serverConnections = new Map();
const rpcCallbacks = new Map();

function connectToMSMP(server) {
    console.log(`[${server.id}] Connecting to ${server.name} (${server.url})...`);
    
    const headers = {
        'Authorization': `Bearer ${server.secret}`
    };

    const ws = new WebSocket(server.url, { headers });
    serverConnections.set(server.id, ws);
    rpcCallbacks.set(server.id, new Map()); // Each server gets its own callback map

    let rpcId = 1; // rpcId is now per-connection

    ws.on('open', () => {
        console.log(`[${server.id}] Successfully connected to MSMP!`);
    });

    ws.on('message', (data) => {
        const response = JSON.parse(data.toString());
        const callbacks = rpcCallbacks.get(server.id);
        
        if (response.id && callbacks.has(response.id)) {
            const callback = callbacks.get(response.id);
            if (response.error) {
                callback.reject(response.error);
            } else {
                callback.resolve(response.result);
            }
            callbacks.delete(response.id);
        } else if (response.method) {
            // console.log(`[${server.id}] Server Notification:`, response);
        }
    });

    ws.on('close', () => {
        console.log(`[${server.id}] Disconnected from MSMP. Reconnecting in 5 seconds...`);
        serverConnections.delete(server.id);
        setTimeout(() => connectToMSMP(server), 5000);
    });

    ws.on('error', (err) => {
        console.error(`[${server.id}] MSMP WebSocket error:`, err.message);
    });

    // We must redefine sendRpcRequest *inside* this scope to capture the
    // correct ws, callbacks, and rpcId for this specific server.
    server.sendRpcRequest = (method, params = {}) => {
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

            const callbacks = rpcCallbacks.get(server.id);
            callbacks.set(id, { resolve, reject });
            
            ws.send(JSON.stringify(payload));
            
            setTimeout(() => {
                if (callbacks.has(id)) {
                    callbacks.delete(id);
                    reject(new Error('RPC request timed out.'));
                }
            }, 5000);
        });
    };
}

// Connect to all servers defined in the JSON
servers.forEach(connectToMSMP);


// --- Create the REST API for PHP ---
// The API now expects a server ID in the URL, e.g., /api/survival/players

// Helper function to get the server object from a request
function getServerFromRequest(req, res) {
    const serverId = req.params.serverId;
    const server = servers.find(s => s.id === serverId);
    if (!server) {
        res.status(404).json({ error: 'Server not found.' });
        return null;
    }
    if (!server.sendRpcRequest) {
        res.status(503).json({ error: 'Server is not connected.' });
        return null;
    }
    return server;
}

// NEW ENDPOINT: /servers
// Provides the list of servers to the PHP frontend.
app.get('/servers', (req, res) => {
    // Only send the public-safe data (id and name)
    const serverList = servers.map(s => ({ id: s.id, name: s.name }));
    res.json(serverList);
});

// UPDATED ENDPOINT: /api/:serverId/players
app.get('/api/:serverId/players', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;

    try {
        const connectedPlayers = await server.sendRpcRequest('minecraft:players/connected/list');
        const offlinePlayers = await server.sendRpcRequest('minecraft:players/offline/list');
        res.json({ connected: connectedPlayers, offline: offlinePlayers });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// UPDATED ENDPOINT: /api/:serverId/bans
app.get('/api/:serverId/bans', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;

    try {
        const bans = await server.sendRpcRequest('minecraft:banned_players/list');
        res.json(bans);
    } catch (error) {
        res.status(500).json({ error: error.message, tip: "Check rpc.discover for 'minecraft:banned_players/list'" });
    }
});

// UPDATED ENDPOINT: /api/:serverId/ops
app.get('/api/:serverId/ops', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;
    
    try {
        const ops = await server.sendRpcRequest('minecraft:operators/list');
        res.json(ops);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// --- NEW 'WRITE' ACTION ENDPOINTS ---

// POST /api/:serverId/say
app.post('/api/:serverId/say', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;
    
    const { message } = req.body;
    if (!message) {
        return res.status(400).json({ error: 'Missing "message" in request body.' });
    }

    try {
        // MSMP method is likely 'minecraft:chat/send_system_message'
        const result = await server.sendRpcRequest('minecraft:chat/send_system_message', { message: message });
        res.json({ success: true, result: result });
    } catch (error) {
        res.status(500).json({ error: error.message, tip: "Method 'minecraft:chat/send_system_message' might be wrong. Use rpc.discover." });
    }
});

// POST /api/:serverId/kick
app.post('/api/:serverId/kick', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;
    
    const { name } = req.body;
    if (!name) {
        return res.status(400).json({ error: 'Missing "name" in request body.' });
    }

    try {
        // MSMP params often take a {name: "..."} or {uuid: "..."} object
        const result = await server.sendRpcRequest('minecraft:players/kick', [{ name: name }]);
        res.json({ success: true, result: result });
    } catch (error) {
        res.status(500).json({ error: error.message, tip: "Method 'minecraft:players/kick' might be wrong." });
    }
});

// POST /api/:serverId/ban
app.post('/api/:serverId/ban', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;
    
    const { name, reason } = req.body;
    if (!name) {
        return res.status(400).json({ error: 'Missing "name" in request body.' });
    }

    try {
        const banData = { name: name, reason: reason || 'Banned via web panel.' };
        const result = await server.sendRpcRequest('minecraft:banned_players/add', [banData]);
        res.json({ success: true, result: result });
    } catch (error) {
        res.status(500).json({ error: error.message, tip: "Method 'minecraft:banned_players/add' might be wrong." });
    }
});

// POST /api/:serverId/pardon
app.post('/api/:serverId/pardon', async (req, res) => {
    const server = getServerFromRequest(req, res);
    if (!server) return;

    const { name } = req.body; // Pardoning by name is common
    if (!name) {
        return res.status(400).json({ error: 'Missing "name" in request body.' });
    }

    try {
        // We're pardoning based on the player profile name
        const result = await server.sendRpcRequest('minecraft:banned_players/remove', [{ name: name }]);
        res.json({ success: true, result: result });
    } catch (error) {
        res.status(500).json({ error: error.message, tip: "Method 'minecraft:banned_players/remove' might be wrong." });
    }
});

// Start the Express server
app.listen(PROXY_PORT, () => {
    console.log(`MSMP Proxy REST API listening at http://localhost:${PROXY_PORT}`);
});