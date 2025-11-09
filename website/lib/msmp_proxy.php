<?php
// This class makes it easy for PHP to get data from our Node.js proxy
class MSMP_Proxy {
    private $base_url;

    public function __construct($url) {
        $this->base_url = $url;
    }

    private function make_request($endpoint) {
        $url = $this->base_url . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 second timeout
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

    public function getPlayers() {
        return $this->make_request('/players');
    }

    public function getBans() {
        return $this->make_request('/bans');
    }

    public function getOps() {
        return $this->make_request('/ops');
    }
}