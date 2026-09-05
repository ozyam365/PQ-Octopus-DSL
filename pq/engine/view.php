<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/engine/view.php
 * COMPONENT : PQ Engine View System & Universal Penetration Core
 * =========================================================
 */
function pq_view($view_name, $vars = []) {
    global $version, $db, $form, $file, $session, $cookie, $http, $date, $time, $trace, $app, $ai, $iot, $text, $excel, $pdf, $navi, $current_mode;

    if (is_array($vars) && !empty($vars)) {
        extract($vars, EXTR_SKIP);
    }

    $view_path = defined('PQ_HTML') ? PQ_HTML . '/' . ltrim($view_name, '/') : dirname(__DIR__, 2) . '/html/' . ltrim($view_name, '/');
    if (pathinfo($view_path, PATHINFO_EXTENSION) === '') {
        $view_path .= '.pq';
    }

    if (!file_exists($view_path)) {
        throw new \RuntimeException("PQ View Error: 뷰 자원 파일을 찾지 못했습니다. 경로 명칭: " . htmlspecialchars($view_path));
    }
    
    $cache_dir = defined('PQ_TMP') ? PQ_TMP : dirname(__DIR__, 1) . '/tmp';
    if (!is_dir($cache_dir)) mkdir($cache_dir, 0755, true);
    $cache_file = $cache_dir . '/view_' . md5($view_path) . '.php';

    $engine_mtime = max(
        filemtime(__DIR__ . '/runner.php'),
        filemtime(__DIR__ . '/ready.php'),
        filemtime(__FILE__)
    );
    $view_mtime = filemtime($view_path);

    if (file_exists($cache_file) && (filemtime($cache_file) > $view_mtime) && (filemtime($cache_file) > $engine_mtime)) {
        include $cache_file;
        return;
    }

    $GLOBALS['pq_runner_placeholders'] = [];
    
try {
        $content = file_get_contents($view_path);
        $content = str_replace("\r", "", $content);

        $content = preg_replace_callback('/\[\[\s*(.*?)\s*\]\]/s', function($m) {
            $inner = trim($m[1]);
            // [[= ... ]] 출력 전술 인젝션
            if (isset($inner[0]) && $inner[0] === '=') {
                $payload = trim(substr($inner, 1));
                // 내부에 @나 #이 들어있다면 정통 사양에 맞게 컴파일 변환 후 PHP echo 단차 적용
                if (function_exists('pq_compile_expr')) {
                    $payload = pq_compile_expr($payload);
                }
                return '<?php echo pq_clean(' . $payload . '); ?>';
            } 
            // [[ if/foreach... ]] 제어 로직 전술 인젝션
            else {
                if (function_exists('pq_ready')) {
                    return '<?php ' . pq_ready($inner) . ' ?>';
                }
                return '<?php ' . $inner . ' ?>';
            }
        }, $content);
		if (file_put_contents($cache_file, $content, LOCK_EX) === false) {
			throw new RuntimeException(...);
		}

		include $cache_file;

    } catch (\Throwable $e) {
        if (file_exists($cache_file)) @unlink($cache_file);
        throw $e;
    } finally {
        unset($GLOBALS['pq_runner_placeholders']);
    }
}

class PQLayoutHelper {
    public function layout($name) {
        $GLOBALS['pq_layout'] = $name;
    }
}

$view = new PQLayoutHelper(); 
$GLOBALS['view'] = $view;
$GLOBALS['view_engine'] = new PQLayoutHelper();
?>