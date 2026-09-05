<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/cookie.php 
 * COMPONENT : PQ Pure Cookie Matrix
 * =========================================================
 */

class PQCookie {
      public function set($key, $val, $expire = 86400) {
        setcookie($key, $val, [
            'expires'  => time() + $expire,
            'path'     => '/',
            'httponly' => true, 
            'samesite' => 'Lax',
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

	public function delete($key) {
        // 즉시 만료 처리
        setcookie($key, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ]);
        
        // 메모리 잔여물 즉시 제거
        if (isset($_COOKIE[$key])) {
            unset($_COOKIE[$key]);
        }
        return $this;
    }
}

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