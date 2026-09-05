<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq365/www/router.php
 * COMPONENT : PQ Engine Router Core (v1.0.7 Synchronized Freeze)
 * =========================================================
 */
class PQRouter {
    private static $map = [];
    private static $current_uri = '/';

    // 라우팅 규칙 설정
    public static function set($path, $file, $type = 'page') {
        self::$map[$path] = [
            'file' => $file,
            'type' => $type
        ];
    }

    // [경로 수사 및 타겟 파일 반환]
    public static function run() {
        $current_path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base_path = dirname($_SERVER['SCRIPT_NAME']);
        
        if ($base_path && $base_path !== '/') {
            if (strpos($current_path, $base_path) === 0) {
                $current_path = substr($current_path, strlen($base_path));
            }
        }
        
        $current_path = '/' . trim((string)$current_path, '/');     
  
        if ($current_path === '/') {
            $current_path = '/index';
        }

        $current_path = preg_replace('#/+#', '/', $current_path);
        self::$current_uri = $current_path;

        foreach (self::$map as $pattern => $route) {
            $target_file = $route['file'];
            $route_type  = $route['type'];

            $rgx = preg_quote($pattern, '#');
            $rgx = preg_replace('/\\\\:(\w+)/', '(?P<$1>[^/]+)', $rgx);
            if (preg_match("#^$rgx$#", $current_path, $matches)) {
                foreach ($matches as $k => $v) {                
                    if (is_string($k)) {
                        $GLOBALS[$k] = $v;
                        $_GET[$k] = $v;
                        $_REQUEST[$k] = $v;
                        $target_file = str_replace(":$k", $v, $target_file);
                    }
                }                
                return [
                    'file' => $target_file,
                    'type' => $route_type
                ];
            }
        }
        return false; 
    }

    public static function active($path) {
        return (self::uri() === '/' . trim((string)$path, '/')) ? 'active' : '';
    }   

    public static function uri() {
        return self::$current_uri;
    }   

    public static function url($path = '') {
        $base_url = defined('PQ_BASE') ? PQ_BASE : '';
        return rtrim($base_url, '/') . '/' . ltrim((string)$path, '/');
    }	
	public static function path($path = '') {
        return self::url($path);
    }	
}

// [수정된 부분] 클래스 외부로 이동
if (!function_exists('autoRoute')) {
    function autoRoute($dir, $prefix) {
        if (!is_dir($dir)) return;
        $items = glob($dir . '/*.pq');
        if (!$items) return;
        foreach ($items as $file) {
            $name = basename($file, '.pq');
            $clean_name = preg_replace("/[^a-zA-Z0-9._-]/", "", str_replace([' ', '#', '$', '%', '&', '(', ')'], '_', $name));
            PQRouter::set(rtrim($prefix, '/') . '/' . $clean_name, $file);
        }
    }
}
// =========================================================
// PQ 단축 헬퍼 함수
// =========================================================

/**
 * 라우팅 등록 단축 함수 (단일/배열 모두 지원)
 */
if (!function_exists('pq_url')) {
    function pq_url($path, $file = null, $type = 'page') {
        // 1. $pq_menu 같은 배열이 들어온 경우 일괄 등록
        if (is_array($path)) {
            foreach ($path as $key => $val) {
                // 사용하려는 스타일: [ ["/m", "html/m/index.pq"], ... ]
                if (is_array($val)) {
                    $p = $val[0] ?? null;
                    $f = $val[1] ?? null;
                    $t = $val[2] ?? 'page';
                    if ($p && $f) {
                        PQRouter::set($p, $f, $t);
                    }
                } 
                // 연관 배열 스타일: [ "/m" => "html/m/index.pq" ] 도 자동 지원
                else if (is_string($key)) {
                    PQRouter::set($key, $val, $type);
                }
            }
            return;
        }

        // 2. 단일 호출: pq_url('/m', 'html/m/index.pq');
        PQRouter::set($path, $file, $type);
    }
}

/**
 * 자동 라우팅 단축 함수
 */
if (!function_exists('pq_auto')) {
    function pq_auto($dir, $prefix = '') {
        if (function_exists('autoRoute')) {
            autoRoute($dir, $prefix);
        }
    }
}
?>