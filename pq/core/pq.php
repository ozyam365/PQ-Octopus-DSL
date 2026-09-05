<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/core/pq.php
 * COMPONENT : PQ System Utilities & Core Namespace
 * =========================================================
 */

class PQCore {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 🐙 PQ DSL 플러그인 모듈 활성화 API
     * 사용법: pq.use("chat") 또는 pq.use("chat").use("api") 또는 pq.use(["chat", "api"])
     */
    public function use($plugin_name) {
        if (is_array($plugin_name)) {
            foreach ($plugin_name as $p) {
                $this->use($p);
            }
            return $this;
        }

        // 내부 실제 로딩 래퍼 호출
        if (function_exists('plugin_load')) {
            plugin_load($plugin_name);
        } else {
            $plugin_path = defined('PQ_PATH') ? PQ_PATH . "/plugin/{$plugin_name}.php" : __DIR__ . "/../plugin/{$plugin_name}.php";
            if (file_exists($plugin_path)) {
                require_once $plugin_path;
            }
        }

        // 연속 체이닝을 위해 자기 자신($this) 반환
        return $this;
    }

    // 🚀 [1] 예외 던지기
    public function throw($msg, $code = 0) {
        throw new \Exception($msg, $code);
    }

    // 🚀 [2] 데이터 예쁜 덤프 출력 (Print)
    public function print(...$args) {
        foreach($args as $v){
            if (is_scalar($v) || $v === null) { 
                echo $v; 
            } else { 
                echo "<pre style='background:#1e1e24; color:#38bdf8; padding:12px; border-radius:8px; font-family:monospace; line-height:1.4; font-size:13px;'>"; 
                print_r($v); 
                echo "</pre>"; 
            }
        }
        return $this;
    }

    public static function now() {
        return PQDate::now();
    }

    public static function date($time = "now") {
        return new PQDate($time);
    }

    // 🚀 [3] Dump & Die (출력 후 즉시 프로세스 종료)
    public function dd(...$args) {
        $this->print(...$args);
        exit;
    }

    // 🚀 [4] 프로세스 즉시 종료
    public function exit($msg = '') {
        if ($msg !== '') echo $msg;
        exit;
    }

    // 🚀 [5] 타임스탬프
    public function time() {
        return time();
    }
}

// 🚀 글로벌 네임스페이스 헬퍼
if (!function_exists('pq')) {
    function pq() {
        return PQCore::getInstance();
    }
}

// 🚀 runner.php 및 하위 호환성을 위한 전역 출력 헬퍼
if (!function_exists('pq_print')) {
    function pq_print(...$args){
        pq()->print(...$args);
    }
}
?>