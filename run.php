<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /run.php
 * COMPONENT : Core Bootstrapper, Dependency Router & Layout Pipeline
 * =========================================================
 */
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

//서브/가상 디렉토리 설정 (최상위 루트 사용 시 '' 또는 '/'로 설정)
define('PQ_VIRTUAL', '/');

$virtual_path = (defined('PQ_VIRTUAL') && PQ_VIRTUAL !== '/' && PQ_VIRTUAL !== '') ? '/' . trim(PQ_VIRTUAL, '/') : '';

define('PQ_DIR', __DIR__);
define('PQ_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
define('PQ_URL', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $virtual_path);
define('PQ_BASE', rtrim(PQ_URL, '/'));
define('PQ_HOME', PQ_URL . "/index");
define('PQ_TMP', PQ_DIR . "/pq/tmp");
define('ATTACH_DIR', PQ_DIR . '/attach/');

$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
define('PQ_SELF', $request_path);

define('PQ_VERSION', "BETA VERSION 9.1");
define('PQ_DN', "https://drive.google.com/drive/folders/16LwbBFdB-gRCtyI3FEfhx2UsnWsQ6hZO?usp=drive_link");
$pq_version = PQ_VERSION;

define('APP_SECRET', bin2hex(random_bytes(32)));

function layout($use = true) {
    $GLOBALS['layout_use'] = $use;
}

/* ==========================================================
 * 1. CORE MODULE LOAD
 * ========================================================== */
$core = [
    'date', 'db', 'excel', 'file', 'http', 'form',
    'func', 'list', 'object', 'session', 'cookie',
    'text', 'rgx', 'html', 'trace', 'auth', 'pq', 'util',
    'pin', 'ret'
];
foreach ($core as $file) {
    require_once PQ_DIR . "/pq/core/{$file}.php";
}

/* ==========================================================
 * 2. ENGINE BOOT
 * ========================================================== */
require_once PQ_DIR . "/pq/engine/ready.php";
require_once PQ_DIR . "/pq/engine/runner.php";
require_once PQ_DIR . "/pq/engine/router.php";

/* ==========================================================
 * 3. PLUGIN BOOT - APP
 * ========================================================== */
if (file_exists(PQ_DIR . "/pq/plugin/app.php")) {
    require_once PQ_DIR . "/pq/plugin/app.php";
    $GLOBALS['app'] = pq_app();
    $app = $GLOBALS['app'];
}

/* ==========================================================
 * 4. ROUTER REGISTER (라우팅 전용 파일 호출)
 * ========================================================== */
if (file_exists(PQ_DIR . "/pq/engine/route_list.php")) {
    require_once PQ_DIR . "/pq/engine/route_list.php";
}

/* ==========================================================
 * 5. ROUTER EXECUTE
 * ========================================================== */
$route = PQRouter::run();

if ($route === false) {
    http_response_code(404);
    if (file_exists(PQ_DIR . "/html/error/404.pq")) {
        run_pq(PQ_DIR . "/html/error/404.pq");
    } else {
        echo "404 Not Found";
    }
    exit;
}

/* ==========================================================
 * 6. LAYOUT PIPELINE
 * ========================================================== */
$target_pq  = false;
$route_type = 'page';

if ($route && is_array($route)) {
    $relative_path  = $route['file'] ?? '';
    $route_type     = $route['type'] ?? 'page';
    $clean_relative = strtok($relative_path, '?');

    $full_path = (strpos($clean_relative, PQ_DIR) === 0) 
        ? $clean_relative 
        : PQ_DIR . '/' . ltrim($clean_relative, '/');

    if (file_exists($full_path) && is_file($full_path)) {
        $target_pq = $full_path;
    }
}

// Dynamic Directory Environment Normalization
$current_path = $_SERVER['PATH_INFO'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path    = dirname($_SERVER['SCRIPT_NAME']);
$current_path = str_replace($base_path, '', $current_path);

if (!empty($virtual_path)) {
    $current_path = preg_replace('#^' . preg_quote($virtual_path, '#') . '#i', '', $current_path);
}
$current_path = '/' . trim($current_path, '/');

$is_mobile_zone = (bool)preg_match('#^/m(/|$)#', $current_path);
$is_admin       = (strpos($current_path, '/csm') === 0 || strpos($current_path, '/adm') === 0);
$is_main        = ($current_path === '/' || $current_path === '/index' || $current_path === '/index.pq');
$layout_dir     = $is_admin ? PQ_DIR . '/html/csm/layout' : PQ_DIR . '/html/layout';

import_pq(PQ_DIR . '/init.pq'); 
import_pq(PQ_DIR . '/tbl.pq');

$layout_use = ($route_type !== 'api');

if ($layout_use) {
    if ($is_mobile_zone) {
        run_pq($layout_dir . '/top_m.pq');
    } else {
        if ($is_admin) {
            $top_file    = "top.pq";
            $left_file   = "left.pq";            
            $bottom_file = "bottom.pq";             
        } else {
            if ($is_main) {
                $top_file    = "m_top.pq";
                $left_file   = "m_left.pq";            
                $bottom_file = "m_bottom.pq";                        
            } else {
                $top_file    = "s_top.pq";
                $left_file   = "s_left.pq";            
                $bottom_file = "s_bottom.pq";                            
            }       
        }
        run_pq($layout_dir . "/" . $top_file);
        echo '<div class="pq-container">';
        
        if (!$is_main) {
            run_pq($layout_dir . "/" . $left_file);
        }
        
        echo '<main class="pq-main">';
        echo '<div class="pq-section">';
    }
}

if ($target_pq && file_exists($target_pq)) {
    run_pq($target_pq);
} else {
    http_response_code(404);
    run_pq(PQ_DIR . '/html/error/404.pq');
}

if ($layout_use) {
    if ($is_mobile_zone) {
        run_pq($layout_dir . '/bottom_m.pq');
    } else {
        echo '</div>';
        echo '</main>';
        echo '</div>';
        run_pq($layout_dir . "/" . $bottom_file);
    }
}

$html_output = ob_get_clean();
echo pq_output_filter($html_output);
?>