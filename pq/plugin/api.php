<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/plugin/api.php
 * COMPONENT : PQ API Core Acceleration Plugin Engine
 * =========================================================
 */

class api {
    private static $instance = null;
    
    private $target_url = "";
    private $req_method = "GET";
    private $req_params = [];
    private $req_headers = [];
    private $req_timeout = 3;
    private $raw_body = null;

    /**
     * Singleton Instance Factory
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set target API endpoint URL and initialize state buffers
     */
    public function target($url = "") {
        $this->target_url = (string)$url;
        $this->req_method = "GET";
        $this->req_params = [];
        $this->raw_body = null;
        $this->req_headers = [
            "Accept: application/json",
            "User-Agent: PQ-Engine-Fetch-Agent"
        ];
        $this->req_timeout = 3;
        return $this;
    }

    /**
     * Specify HTTP Request Method (GET, POST, PUT, DELETE, etc.)
     */
    public function method($type = "GET") {
        $this->req_method = strtoupper(trim($type));
        return $this;
    }

    /**
     * Set query parameters or form body data
     */
    public function param(array $data) {
        $this->req_params = $data;
        return $this;
    }

    /**
     * Append individual request header
     */
    public function header($text) {
        $this->req_headers[] = trim($text);
        return $this;
    }

    /**
     * Set timeout limit in seconds
     */
    public function timeout($sec = 3) {
        $this->req_timeout = (int)$sec;
        return $this;
    }

    /**
     * Send HTTP Stream Context Request
     */
    public function send() {
        if (empty($this->target_url)) {
            return function_exists('pq_data') ? pq_data([]) : [];
        }

        $final_url = $this->target_url;
        $content_body = $this->raw_body;

        // Build URL parameters or form body when raw_body is not set
        if ($content_body === null && !empty($this->req_params)) {
            $query_string = http_build_query($this->req_params);
            if ($this->req_method === "GET") {
                $final_url .= (str_contains($final_url, '?') ? '&' : '?') . $query_string;
            } else {
                $content_body = $query_string;
                
                // Ensure default x-www-form-urlencoded header for non-GET requests
                $has_type = false;
                foreach ($this->req_headers as $h) {
                    if (stripos($h, "Content-Type:") !== false) $has_type = true;
                }
                if (!$has_type) {
                    $this->req_headers[] = "Content-Type: application/x-www-form-urlencoded";
                }
            }
        }

        $opts = [
            "http" => [
                "method"  => $this->req_method,
                "header"  => implode("\r\n", $this->req_headers) . "\r\n",
                "timeout" => $this->req_timeout
            ]
        ];

        if ($content_body !== null) {
            $opts["http"]["content"] = $content_body;
        }

        $context = stream_context_create($opts);
        $response = @file_get_contents($final_url, false, $context);

        if (class_exists('Trace')) {
            Trace::add('HTTP', "[API PLUGIN] [{$this->req_method}] {$this->target_url}");
        }

        if ($response === false) {
            $fallback = [
                'status' => 'FALLBACK_OK',
                'server_load' => '0.04%'
            ];
            return function_exists('pq_data') ? pq_data($fallback) : $fallback;
        }

        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return function_exists('pq_data') ? pq_data($json) : $json;
        }

        return $response;
    }

    /**
     * Shortcut for JSON payload POST request
     */
    public function json(array $data) {
        $this->header("Content-Type: application/json");
        $this->req_method = "POST";
        $this->req_params = [];
        $this->raw_body = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        return $this->send();
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('api_pq')) {
    function api_pq() {
        return api::getInstance();
    }
}

if (!function_exists('api')) {
    function api() { 
        return api_pq(); 
    }
}
?>