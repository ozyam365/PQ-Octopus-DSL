<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.8)
 * FILENAME  : /pq/engine/router.php
 * COMPONENT : PQ Engine Fluent Router Core
 * =========================================================
 */

class PQRouter {
    private static $map = [];
    private static $current_uri = '/';
    private static $groupStack = []; 

    // [CONFIG] Register Route Rule
    public static function set($path, $file,$type = 'page') {
        $prefix = implode('', self::$groupStack);$full_path = '/' . trim($prefix . '/' . ltrim($path, '/'), '/');

        self::$map[$full_path] = [
            'file' => $file,
            'type' => $type
        ];
    }

    // [FEATURE] Grouping Routing (Scope Pattern)
    public static function group($prefix, callable$callback) {
        self::$groupStack[] = '/' . trim($prefix, '/');$callback();
        array_pop(self::$groupStack);
    }

    // [FEATURE] Redirect Route Rule
    public static function redirect($from,$to) {
        self::set($from,$to, 'redirect');
    }

    // [REQUIRED] Resolve Current Request Path & Return Target File
    public static function run() {
        $current_path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base_path = dirname($_SERVER['SCRIPT_NAME']);
        
        if ($base_path &&$base_path !== '/') {
            if (strpos($current_path, $base_path) === 0) {$current_path = substr($current_path, strlen($base_path));
            }
        }
        
        $current_path = '/' . trim((string)$current_path, '/');     
  
        if ($current_path === '/') {$current_path = '/index';
        }

        $current_path = preg_replace('#/+#', '/', $current_path);
        self::$current_uri =$current_path;

        foreach (self::$map as $pattern =>$route) {
            $target_file =$route['file'];
            $route_type  =$route['type'];

            if ($route_type === 'redirect' && $current_path ===$pattern) {
                header("Location: " . $target_file);
                exit;
            }

            $rgx = preg_quote($pattern, '#');
            $rgx = preg_replace('/\\\\:(\w+)/', '(?P<$1>[^/]+)',$rgx);
            if (preg_match("#^$rgx$#", $current_path,$matches)) {
                foreach ($matches as $k =>$v) {                
                    if (is_string($k)) {$GLOBALS[$k] =$v;
                        $_GET[$k] =$v;
                        $_REQUEST[$k] =$v;
                        $target_file = str_replace(":$k", $v,$target_file);
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

    // [CUSTOM] Active Navigation Link Helper
    public static function active($path) {
        return (self::uri() === '/' . trim((string)$path, '/')) ? 'active' : '';
    }   

    public static function uri() {
        return self::$current_uri;
    }   

    // [CONFIG] Base URL Normalization Helper
    public static function url($path = '') {$base_url = defined('PQ_BASE') ? PQ_BASE : '';
        return rtrim($base_url, '/') . '/' . ltrim((string)$path, '/');
    }   

    public static function path($path = '') {
        return self::url($path);
    }   
}

// [CONFIG] Automatic Directory Route Generator
if (!function_exists('autoRoute')) {
    function autoRoute($dir,$prefix = '') {
        if (!is_dir($dir)) return;
        $items = glob($dir . '/*.pq');
        if (!$items) return;
        foreach ($items as $file) {$name = basename($file, '.pq');$clean_name = preg_replace("/[^a-zA-Z0-9._-]/", "", str_replace([' ', '#', '$', '\%', '&', '(', ')'], '_',$name));
            PQRouter::set(rtrim($prefix, '/') . '/' . $clean_name,$file);
        }
    }
}

// =========================================================
// PQ Core Shortcut Helpers & Fluent Proxy
// =========================================================

/**
 * [NEW] DSL Proxy Object with Built-in Trailing Slash Normalization
 */
class PQRouteProxy {
    
    private function normalizeRoutes($menu_list) {
        $result = [];
        foreach ($menu_list as $item) {
            if (!is_array($item)) continue;

            $url  = $item[0] ?? null;
            $file = $item[1] ?? null;
            $type = $item[2] ?? 'page';

            if (!$url || !$file) continue;

            $trimmed = rtrim($url, '/');

            $result[] = [$url, $file, $type];

            if ($trimmed !== '' && $trimmed !== $url) {
                $result[] = [$trimmed, $file, $type];
            } elseif ($trimmed !== '') {
                $result[] = [$trimmed . '/', $file, $type];
            }
        }
        return $result;
    }

    public function url($path, $file = null, $type = 'page') {
        if (function_exists('pq_url')) {
            if (is_array($path) && isset($path[0]) && is_array($path[0])) {
                $normalized = $this->normalizeRoutes($path);
                pq_url($normalized);
            } else {
                pq_url($path, $file, $type);
            }
        }
        return $this;
    }

    public function auto($dir, $prefix = '') {
        if (function_exists('pq_auto')) {
            pq_auto($dir, $prefix);
        }
        return $this;
    }
}

$route = new PQRouteProxy();

/**
 * [CUSTOM] Legacy & Single/Batch Array Route Helper
 */
if (!function_exists('pq_url')) {
    function pq_url($path, $file = null,$type = 'page') {
        if (is_array($path)) {
            foreach ($path as $key =>$val) {
                if (is_array($val)) {
                    $p =$val[0] ?? null;
                    $f =$val[1] ?? null;
                    $t =$val[2] ?? 'page';
                    if ($p &&$f) {
                        PQRouter::set($p, $f,$t);
                    }
                } 
                else if (is_string($key)) {
                    PQRouter::set($key, $val,$type);
                }
            }
            return;
        }

        PQRouter::set($path, $file,$type);
    }
}

/**
 * [CUSTOM] Automatic Directory Route Binding Helper
 */
if (!function_exists('pq_auto')) {
    function pq_auto($dir,$prefix = '') {
        if (function_exists('autoRoute')) {
            autoRoute($dir,$prefix);
        }
    }
}
?>