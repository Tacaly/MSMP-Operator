<?php
class MSMP_Proxy {
    private $base_url;

    // The base URL is now just the proxy URL, not a specific server
    public function __construct($url) {
        $this->base_url = $url;
    }

    private function make_request($endpoint) {
        $url = $this->base_url . $endpoint; // The endpoint now includes the server ID
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            return ['error' => 'Failed to connect to the MSMP Proxy. Is it running?'];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return ['error' => 'Invalid JSON response from proxy.'];
        }
        
        return $data;
    }

    // NEW Method: Gets the list of available servers
    public function getServers() {
        return $this->make_request('/servers');
    }

    // UPDATED Methods: All methods now require a $serverId
    public function getPlayers($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/players');
    }

    public function getBans($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/bans');
    }

    public function getOps($serverId) {
        return $this->make_request('/api/' . urlencode($serverId) . '/ops');
    }
}