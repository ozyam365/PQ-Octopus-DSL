<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/plugin/chat.php
 * COMPONENT : PQ Chat & Realtime SSE Stream Core Plugin
 * =========================================================
 */

class chat {
    /**
     * Get chat log file storage path
     */
    private static function getLogPath() {
        $cache_dir = defined('PQ_TMP') ? PQ_TMP : dirname(__DIR__) . '/tmp';
        return $cache_dir . "/chat_room_main.log";
    }

    /**
     * Send and append chat message log entry
     */
    public static function send($user, $msg) {
        if (empty($user) || empty($msg)) return false;

        $log_file = self::getLogPath();
        $dir = dirname($log_file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $entry = json_encode([
            'time' => date('H:i:s'),
            'user' => htmlspecialchars($user, ENT_QUOTES, 'UTF-8'),
            'msg'  => htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')
        ], JSON_UNESCAPED_UNICODE) . PHP_EOL;

        return (bool)@file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Read and parse recent chat messages
     */
    public static function read($limit = 30) {
        $log_file = self::getLogPath();
        if (!file_exists($log_file)) {
            self::send("System Agent", "Welcome to PQ Chat service.");
        }

        $lines = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) return [];
        
        $lines = array_slice($lines, -(int)$limit);
        
        $messages = [];
        foreach ($lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $messages[] = $data;
            }
        }
        return $messages;
    }

    /**
     * HTML5 Server-Sent Events (SSE) Realtime Streaming Pipeline
     */
    public static function stream() {
        if (headers_sent()) return;
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Bypass Nginx proxy buffering
        
        set_time_limit(0);
        
        $last_hash = '';
        
        // SSE Realtime Stream Loop with 3-minute timeout guard
        for ($i = 0; $i < 180; $i++) {
            $messages = self::read(50);
            $current_hash = md5(json_encode($messages));
            
            if ($current_hash !== $last_hash) {
                $last_hash = $current_hash;
                
                $html_buffer = '';
                foreach ($messages as $m) {
                    $html_buffer .= '<div class="mb-3 p-2.5 rounded-3 bg-white border-light shadow-sm">';
                    $html_buffer .= '  <div class="d-flex justify-content-between small text-secondary mb-1">';
                    $html_buffer .= '    <strong class="text-primary"><i class="bi bi-person-fill"></i> ' . $m['user'] . '</strong>';
                    $html_buffer .= '    <span>' . $m['time'] . '</span>';
                    $html_buffer .= '  </div>';
                    $html_buffer .= '  <div class="text-dark" style="font-size:13.5px; word-break:break-all;">' . $m['msg'] . '</div>';
                    $html_buffer .= '</div>';
                }
                
                echo "data: " . json_encode(['html' => $html_buffer], JSON_UNESCAPED_UNICODE) . "\n\n";
                while (ob_get_level()) ob_end_flush();
                flush();
            }
            
            sleep(1);
            
            if (connection_aborted()) {
                break;
            }
        }
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('chat_pq')) {
    function chat_pq() {
        static $inst = null;
        if (!$inst) {
            $inst = new chat();
        }
        return $inst;
    }
}

if (!function_exists('chat')) {
    function chat() {
        return chat_pq();
    }
}
?>