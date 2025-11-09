<?php
class MSMP_Proxy {
    private $base_url;

    public function __construct($url) {
        $this->base_url = $url;
    }

    // UPDATED make_request to handle different methods and data
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = $this->base_url . $endpoint;
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // Set method (GET, POST, etc.)

        if ($method === 'POST' && $data !== null) {
            $jsonData = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]);
        }

        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            return ['error' => 'Failed to connect to the MSMP Proxy. Is it running?'];
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return ['error' => 'Invalid JSON response from proxy.'];
        }
        
        return $responseData;
    }

    // --- Read Methods (Unchanged) ---
    public function getServers() {
        return $this->make_request('/servers');
    }
    public function getPlayers($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/players');
    }
    public function getBans($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/bans');
    }
    public function getOps($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/ops');
    }

    // --- NEW Write Methods ---
    public function sendMessage($serverId, $message) {
        $data = ['message' => $message];
        return $this->make_request('/api/' . urlencode($serverId) . '/say', 'POST', $data);
    }
    
    public function kickPlayer($serverId, $name) {
        $data = ['name' => $name];
        return $this->make_request('/api/' . urlencode($serverId) . '/kick', 'POST', $data);
    }

    public function banPlayer($serverId, $name, $reason) {
        $data = ['name' => $name, 'reason' => $reason];
        return $this->make_request('/api/' . urlencode($serverId) . '/ban', 'POST', $data);
    }

    public function pardonPlayer($serverId, $name) {
        $data = ['name' => $name];
        return $this->make_request('/api/' . urlencode($serverId) . '/pardon', 'POST', $data);
    }
}