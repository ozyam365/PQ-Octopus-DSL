<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.5)
 * FILENAME : /pq/core/session.php
 * COMPONENT : PQ Engine Session Matrix Core (Final Slim)
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

	public function login($user, $scope = 'user') {
    $this->init();
    session_regenerate_id(true);
    
    // 데이터를 강제로 객체화하지 않고, 넘겨받은 타입 그대로 저장합니다.
    $_SESSION[$scope] = $user; 
    
    return $this;
}
	public function logout($scope="user"){
		return $this->destroy();
	}
	public function has($key) {
		$this->init();
		return isset($_SESSION[$key]);
	}	
    // 2. 인증 확인 (true/false)
    public function auth($scope = 'user') {
        return $this->check($scope);
    }

    // 3. 보호 (실패 시 리다이렉트)
    public function only($path = "/login", $scope = 'user') {
        if (!$this->check($scope)) {
            header("Location: $path");
            exit;
        }
        return $this;
    }

	public function group($scope = 'user') {
		$this->init();
		return $_SESSION[$scope] ?? null; // 가공 없이 그대로 반환
	}
    // 5. 데이터 조작
    public function set($k, $v) {
        $this->init();
        $_SESSION[$k] = $v;
        return $this;
    }
    public function get($k, $def = null) {
        $this->init();
        return $_SESSION[$k] ?? $def;
    }
	public function drop($key){
		$this->init();
		unset($_SESSION[$key]);
		return $this;
	}
	public function unset($key) {
		return $this->drop($key);
	}	
    // 6. 증거 인멸 및 세션 파괴
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

/**
 * SESSION 헬퍼 함수
 */
if (!function_exists('session_pq')) {
    function session_pq() {
        static $inst = null;
        if (!$inst) $inst = new PQSession();
        return $inst;
    }
}

?>