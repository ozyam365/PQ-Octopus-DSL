<?php
/**
 * =========================================================
 * PQ VERSION (v9.1.6)
 * FILENAME : /pq/plugin/auto.php
 * COMPONENT : PQ Automation Task Plugin (Part 1/2)
 * =========================================================
 */

class PQ_Auto_Engine {
    protected static $instance = null;
    protected $mode = 'server'; // server 또는 client
    protected $time_rule = '';
    protected $is_matched = false;
    protected $interval_ms = 600000; // 기본 10분

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function reset() {
        $this->time_rule = '';
        $this->is_matched = false;
        $this->mode = 'server';
        $this->interval_ms = 600000;
    }

    /**
     * 🕵️ 서버단 크론탭 모드 선언
     */
    public function server() {
        $this->mode = 'server';
        return $this;
    }

    /**
     * 🕵️ 웹 브라우저단 HTML5 크론탭 모드 선언
     */
    public function client() {
        $this->mode = 'client';
        return $this;
    }

    /**
     * =====================================================
     * 1. 주기 판별 구역 (Dual Mode 대응)
     * =====================================================
     */
    public function every($interval) {
        $this->time_rule = "every " . $interval;
        $minutes = (int)str_replace('m', '', $interval);
        $this->interval_ms = $minutes * 60 * 1000; // 밀리초 환산

        if ($this->mode === 'server' || isset($_GET['pq_auto_trigger'])) {
            // 🕵️ [경로 유실 완전 진압] 코어 상수를 활용하여 호스팅 환경 편차 차단
            $cache_dir = defined('PQ_TMP') ? PQ_TMP : dirname(__DIR__) . '/tmp';
            $log_file = $cache_dir . "/last_" . md5($this->time_rule) . ".time";
            
            $last_run = file_exists($log_file) ? (int)file_get_contents($log_file) : 0;
            $interval_seconds = $minutes * 60;

            if ((time() - $last_run) >= $interval_seconds) {
                $this->is_matched = true;
                @file_put_contents($log_file, time());
            } else {
                $this->is_matched = false;
            }
        } else {
            $this->is_matched = false;
        }
        return $this;
    }
    /**
     * =====================================================
     * 2. 집행 구역 (중복 렌더링 무력화 가로채기 방어선 장착)
     * =====================================================
     */
    public function run($target_script) {
        if (isset($_GET['pq_auto_trigger']) && $_GET['pq_auto_task'] === $target_script) {
            $base_dir = defined('PQ_ROOT') ? PQ_ROOT : dirname(__DIR__, 2);
            $script_path = $base_dir . "/html/" . ltrim($target_script, '/') . ".pq";
            if (file_exists($script_path) && function_exists('run_pq')) {
                @run_pq($script_path);
                $this->log("Client Worker Webhook Executed target -> " . $target_script);
            }
            $this->reset();
            exit; 
        }

        if ($this->mode === 'server') {
            if ($this->is_matched) {
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $fp = @fsockopen($host, 80, $errno, $errstr, 2);
                if ($fp) {
                    $out = "GET /srun.php HTTP/1.1\r\nHost: {$host}\r\nConnection: Close\r\n\r\n";
                    fwrite($fp, $out);
                    stream_set_timeout($fp, 1);
                    fclose($fp);
                }
                $this->log("Server Cron Triggered: " . $target_script);
            }
            $this->reset();
            return "";
        } else {
            $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            $target_url = $current_path . "?pq_auto_trigger=1&pq_auto_task=" . urlencode($target_script);

            $html = "
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof(Worker) !== 'undefined') {
                    const code = 'setInterval(function(){ postMessage(1); }, " . $this->interval_ms . ");';
                    const blob = new Blob([code], {type: 'application/javascript'});
                    const worker = new Worker(URL.createObjectURL(blob));
                    worker.onmessage = function() {
                        fetch(" . json_encode($target_url, JSON_UNESCAPED_SLASHES) . ")
                            .then(r => console.log('[PQ HTML5 Client Cron] Dispatched Secret Task -> " . $target_script . "'))
                            .catch(e => console.error(e));
                    };
                    console.log('[PQ] HTML5 Web Worker Cron Client Loaded (" . $this->time_rule . ")');
                }
            });
            </script>";
            $this->reset();
            return $html;
        }
    }

    public function log($msg) {
        $cache_dir = defined('PQ_TMP') ? PQ_TMP : dirname(__DIR__) . '/tmp';
        $log_path = $cache_dir . "/auto_task.log";
        $log_entry = "[" . date('Y-m-d H:i:s') . "] [" . $this->time_rule . "] " . $msg . PHP_EOL;
        @file_put_contents($log_path, $log_entry, FILE_APPEND);
        return $this;
    }
}

/**
 * =========================================================
 * AUTO FACADE INTERFACE (Fixed Compiler Synchronizer)
 * =========================================================
 */
class auto {
    public static function server() { return PQ_Auto_Engine::getInstance()->server(); }
    public static function client() { return PQ_Auto_Engine::getInstance()->client(); }
    public static function every($interval) { return PQ_Auto_Engine::getInstance()->every($interval); }
    public static function run($target) { return PQ_Auto_Engine::getInstance()->run($target); }
    public static function log($msg) { return PQ_Auto_Engine::getInstance()->log($msg); }
}

/**
 * 🕵️ [전역 러너 바인딩 안전 쉴드 결속]
 * runner.php 내 27라인 주변의 전역 할당 인터페이스와 오차 없이 맞물리도록 
 * 팩토리 프로바이더 함수를 연동 전개 완료했습니다.
 */
if (!function_exists('auto_pq')) {
    function auto_pq() {
        return PQ_Auto_Engine::getInstance();
    }
}
?>
