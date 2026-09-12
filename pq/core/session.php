<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/session.php
 * COMPONENT : PQ Engine Session Matrix Core Engine
 * =========================================================
 */

class PQSession {
    private static $is_init = false;

    private function init() {
        if (self::$is_init) return;
        if (session_status() === PHP_SESSION_NONE) session_start();
        self::$is_init = true;
    }

    private function check($scope) {
        $this->init();
        return !empty($_SESSION[$scope]);
    }

    // --- [1. AUTHENTICATION PIPELINE] ---
    public function login($user, $scope = 'user') {
        $this->init();
        session_regenerate_id(true);
        
        // Store raw user payload without forcing object transformation
        $_SESSION[$scope] = $user; 
        
        return $this;
    }

    public function logout($scope = "user") {
        return $this->destroy();
    }

    public function has($key) {
        $this->init();
        return isset($_SESSION[$key]);
    }   

    public function auth($scope = 'user') {
        return $this->check($scope);
    }

    // Guard route and redirect on authentication failure
    public function only($path = "/login", $scope = 'user') {
        if (!$this->check($scope)) {
            header("Location: $path");
            exit;
        }
        return $this;
    }

    public function group($scope = 'user') {
        $this->init();
        return $_SESSION[$scope] ?? null;
    }

    // --- [2. DATA MANIPULATION PIPELINE] ---
    public function set($k, $v) {
        $this->init();
        $_SESSION[$k] = $v;
        return $this;
    }

    public function get($k, $def = null) {
        $this->init();
        return $_SESSION[$k] ?? $def;
    }

    public function drop($key) {
        $this->init();
        unset($_SESSION[$key]);
        return $this;
    }

    public function unset($key) {
        return $this->drop($key);
    }   

    // --- [3. SESSION DESTRUCTION & COOKIE PURGE] ---
    public function destroy() {
        $this->init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        self::$is_init = false;
        return true;
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('session_pq')) {
    function session_pq() {
        static $inst = null;
        if (!$inst) $inst = new PQSession();
        return $inst;
    }
}

if (!function_exists('session')) {
    function session() {
        return session_pq();
    }
}
?>