<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/cookie.php 
 * COMPONENT : PQ Pure Cookie Matrix
 * =========================================================
 */

class PQCookie {
    /**
     * [CONFIG] Set Cookie with Security Policies
     * Default TTL: 86400 seconds (1 day)
     */
    public function set($key, $val, $expire = 86400) {
        setcookie($key, $val, [
            'expires'  => time() + $expire,
            'path'     => '/',
            'httponly' => true, 
            'samesite' => 'Lax', // [CUSTOMIZE] 'Strict', 'Lax', or 'None'
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ]);     
        $_COOKIE[$key] = $val;
        return $this;     
    }

    public function get($key) {
        return $_COOKIE[$key] ?? null;
    }

    public function has($key) {
        return isset($_COOKIE[$key]);
    }

    /**
     * [CONFIG] Delete Cookie & Clear In-Memory Reference
     */
    public function delete($key) {
        setcookie($key, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ]);
        
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
        }
        return $this;
    }
}

/**
 * [ENGINE CORE] Singleton Bridge Function for cookie()
 */
if (!function_exists('cookie')) {
    function cookie() {
        static $instance = null;
        if ($instance === null) {
            $instance = new PQCookie();
        }
        return $instance;
    }
}
?>