<?php
/**
 * =========================================================
 * PQ VERSION (v9.1.6)
 * FILENAME : /pq/plugin/chat.php
 * COMPONENT : PQ Chat Plugin Core (Part 1/2)
 * =========================================================
 */

class chat {
    /**
     * 장부 경로 스캔 매핑 규격 사수
     */
    private static function getLogPath() {
        $cache_dir = defined('PQ_TMP') ? PQ_TMP : dirname(__DIR__) . '/tmp';
        return $cache_dir . "/chat_room_main.log";
    }

    /**
     * 🕵️ [장부 적재] 대사 문자열 고속 투하
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
     * 🕵️ [오류 진압 완료] 데이터 복원 사출
     */
    public static function read($limit = 30) {
        $log_file = self::getLogPath();
        if (!file_exists($log_file)) {
            self::send("배지희 상담원", "안녕하세요! 무엇이든 물어보세요 👍");
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
     * 🕵️ [HTML5 EventSource 실시간 스트리밍 관제 엔진 전격 이식]
     * room.pq 내 21라인의 chat.stream() 호출 양식을 무결하게 수령하여
     * 브라우저와 단방향 무부하 실시간 통신망 무한 루프 스트림 채널을 개방 집행합니다.
     */
    public static function stream() {
        // 🔒 브라우저 팅김 현상 방지 및 HTML5 SSE 표준 프로토콜 헤더 강제 각인
        if (headers_sent()) return;
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Nginx 프록시 버퍼링 우회 거세 고도화
        
        // 무한 백그라운드 스트리밍 통신망 개방
        set_time_limit(0);
        
        // 1회 진입 시 기존 메시지 전량 덤프용 HTML 버퍼 조립
        $last_hash = '';
        
        // 🕵️ 실시간 무부하 스트리밍 와일 루프 기동
        for ($i = 0; $i < 180; $i++) { // 3분 타임아웃 자동 순환 가드
            $messages = self::read(50);
            $current_hash = md5(json_encode($messages));
            
            // 데이터 장부에 변동 단서가 포착되었을 때만 전격 이미지 돔 스트림 방출
            if ($current_hash !== $last_hash) {
                $last_hash = $current_path = $current_hash;
                
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
                
                // HTML5 EventSource 정형 출력 가이드 포맷 라인 전출
                echo "data: " . json_encode(['html' => $html_buffer], JSON_UNESCAPED_UNICODE) . "\n\n";
                while (ob_get_level()) ob_end_flush();
                flush();
            }
            
            // 🔒 웹서버 무부하 유지를 위한 1초 정밀 휴식(Sleep) 밸브 가동
            sleep(1);
            
            // 브라우저가 탭을 닫고 도망갔는지 접속 끊김 유무 실시간 수사
            if (connection_aborted()) {
                break;
            }
        }
    }
}
?>
