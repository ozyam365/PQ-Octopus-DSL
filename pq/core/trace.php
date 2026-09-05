<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/trace.php 
 * COMPONENT : PQ trace 
 * =========================================================
 */
class Trace {
    public static $logs = [];
    private static $count = 0, $start = null, $is_active = false, $rendered = false;

    public static function on() {
        self::$is_active = true;
        self::$start = self::$start ?? microtime(true);
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        self::add('OK', 'PQ Debugger Online ', 1);
    }
	public static function debug($var, $title = "DEBUG") {
		if (!self::$is_active) return;

		ob_start();

		if (is_scalar($var) || $var === null) {
			var_dump($var);
		} else {
			print_r($var);
		}

		$out = ob_get_clean();

		self::add($title, $out, 2);
	}
    public static function add($type, $msg, $depth = 1) {
        if (!self::$is_active) return;
        if (!self::$start) self::$start = microtime(true);
        self::$count++;
        
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $depth + 1);
        $caller = $bt[$depth] ?? $bt[0];
        $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
        $line = isset($caller['line']) ? $caller['line'] : '0';

        $msg_str = (is_scalar($msg)) ? (string)$msg : json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        self::$logs[] = [
            'no'   => self::$count,
            'type' => $type,
            'msg'  => $msg_str,
            'time' => microtime(true),
            'file' => $file,
            'line' => $line
        ];

        //긴급 에러 발생 시 즉각 현장 보고
        if ($type === 'ERROR') {
            echo "<div style='background:#450a0a; color:#fca5a5; padding:10px; border:1px solid #ef4444; margin:5px; font-family:monospace; border-radius:4px; font-size:12px; z-index:999999; position:relative;'>
                    <b> [긴급]</b> " . htmlspecialchars($msg_str, ENT_QUOTES, 'UTF-8') . "
                  </div>";
        }
    }

    //  [복구완료] DB SQL 추적 메서드
    public static function sql($query) { self::add('SQL', $query, 2); }

    public static function out() {
        if (self::$rendered || !self::$is_active || empty(self::$logs)) return; 
        
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') !== false && stripos($header, 'text/html') === false) return;
        }

        self::$rendered = true;
        
        echo "
        <div id='pq-trace-tray' style='position:fixed; bottom:0; left:0; width:100%; height:40px; background:#000000; border-top:2px solid #facc15; transition:height 0.4s; overflow:hidden; z-index:999999; box-shadow:0 -5px 15px rgba(0,0,0,0.8);'>
            <div id='pq-trace-bar' style='height:40px; display:flex; align-items:center; padding:0 20px; cursor:pointer; color:#facc15; font-weight:bold; border-bottom:1px solid #333;'>
                <span style='font-size:12px'>[ Trace System ]  &nbsp;<font color='#21F50A'>" . count(self::$logs) . "</font>&nbsp;"." Log </span>
                <span id='pq-toggle-icon' style='margin-left:auto; font-size:9px'>▲</span>
            </div>
            <div style='height:calc(100% - 40px); overflow-y:auto; padding:20px; background:#0a0a0a; color:#e0e0e0; font-family:Consolas, monospace; font-size:13px;'>";
        
        foreach (self::$logs as $log) {
            $t = number_format(($log['time'] - self::$start), 4);
            // 💡 [복구완료] SQL 타입은 노란색 강조
            $type_color = ($log['type'] === 'SQL') ? '#38bdf8' : '#facc15';
            
            echo "<div style='border-bottom:1px solid #222; padding:8px 0; color:#ddd;'>
                <span style='color:#777;'>#{$log['no']} (+{$t}s)</span> 
                <b style='color:{$type_color};'>[{$log['type']}]</b> " . htmlspecialchars($log['msg'], ENT_QUOTES, 'UTF-8') . " 
                <span style='color:#555; font-size:10px;'>({$log['file']}:{$log['line']})</span>
            </div>";
        }
        
        echo "</div></div>
        <script>
            let isOpen = false;
            document.getElementById('pq-trace-bar').onclick = function() {
                isOpen = !isOpen;
                document.getElementById('pq-trace-tray').style.height = isOpen ? '50vh' : '40px';
                document.getElementById('pq-toggle-icon').innerText = isOpen ? '▼' : '▲';
            };
        </script>";
    }
}

register_shutdown_function(['Trace', 'out']);

// 함수 복구
if (!function_exists('trace')) { function trace($t, $m) { Trace::add($t, $m, 2); } }
if (!function_exists('trace_on')) { function trace_on() { Trace::on(); } }
function debug($var, $title = "DEBUG") {
    Trace::debug($var, $title);
}
?>