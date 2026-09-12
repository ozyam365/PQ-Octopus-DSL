<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/engine/ready.php
 * COMPONENT : Pre-Processor Pipeline & Translation Stages
 * =========================================================
 */

$root_path = dirname(__DIR__, 1);

// [REQUIRED] Core Module Dependencies
include_once $root_path . "/core/auth.php"; 
include_once $root_path . "/core/cookie.php";
include_once $root_path . "/core/date.php";
include_once $root_path . "/core/db.php";
include_once $root_path . "/core/excel.php"; 
include_once $root_path . "/core/file.php";
include_once $root_path . "/core/form.php"; 
include_once $root_path . "/core/func.php";
include_once $root_path . "/core/html.php";
include_once $root_path . "/core/http.php";
include_once $root_path . "/core/list.php"; 
include_once $root_path . "/core/object.php"; 
include_once $root_path . "/core/pin.php"; 
include_once $root_path . "/core/pq.php"; 
include_once $root_path . "/core/ret.php"; 
include_once $root_path . "/core/rgx.php"; 
include_once $root_path . "/core/session.php";
include_once $root_path . "/core/text.php";
include_once $root_path . "/core/trace.php";
include_once $root_path . "/core/util.php";

// [CONFIG] Core Helper Bridge Mapping
if (!defined('PQ_RESERVED_MAP')) {
    define('PQ_RESERVED_MAP', [
        'db'      => '$db->', 
        'session' => '$session->', 
        'cookie'  => '$cookie->', 
        'http'    => '$http->',
        'file'    => 'file_pq()->', 
        'form'    => '$form->', 
        'date'    => '$date->', 
        'time'    => '$time->',
        'text'    => '$text->', 
        'ai'      => '$ai->', 
        'iot'     => '$iot->', 
        'app'     => '$app->',
        'html'    => '$html->',
        'auto'    => '$auto->', 
        'util'    => '$util->', 
        'pdf'     => '$pdf->', 
        'excel'   => '$excel->', 
        'trace'   => 'Trace::'
    ]);
}

// [CONFIG] Parsing Bypass Keywords
if (!defined('PQ_SEMICOLON_BYPASS')) {
    define('PQ_SEMICOLON_BYPASS', ['format(', 'util.', 'date.', 'time.', 'now(']);
}

if (!isset($cookie)) {
    $cookie = new PQCookie(); 
}

/**
 * Main DSL Pre-Processor Entry Point
 */
function pq_ready($code) {
    if ($code === null || empty(trim((string)$code))) return '';

    // Static Syntax & Type Analysis
    pq_stage_analyze_type_rules($code);

    // Scope & Reserved Keywords Compilation
    $code = pq_stage_object_scope($code);
    $code = pq_stage_reserved_chain($code);

    // Translation Pipeline
    $code = pq_stage_convert_functions($code);
    $code = pq_stage_convert_collections_methods($code);
    $code = pq_stage_convert_collections($code);
    $code = pq_stage_convert_objects($code);
    $code = pq_stage_convert_variables($code);

    return trim((string)$code);
}

// =========================================================
// Pipeline Stage Functions
// =========================================================

function pq_stage_convert_functions($code) {
    return preg_replace_callback('/@([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\((.*?)\)/', function($m) {
        return $m[2] . '($' . $m[1] . ', ' . $m[3] . ')';
    }, $code);
}

function pq_stage_convert_objects($code) {
    return preg_replace_callback('/#([a-zA-Z_][a-zA-Z0-9_]*)((\.[a-zA-Z_][a-zA-Z0-9_]*)+)/', function($m) {
        $var = '$' . $m[1];
        $chain = str_replace('.', '->', $m[2]);
        return $var . $chain;
    }, $code);
}

function pq_stage_convert_collections($code) {
    return preg_replace_callback(
        '/\$([a-zA-Z_][a-zA-Z0-9_]*\[[^\]]+\])((\.[a-zA-Z_][a-zA-Z0-9_]*)+)/',
        function($m){
            return '$' . $m[1] . str_replace('.', '->', $m[2]);
        },
        $code
    );
}

function pq_stage_convert_collections_methods($code) {
    return preg_replace_callback('/\$([a-zA-Z_][a-zA-Z0-9_]*)\.(first|count|filter)\((.*?)\)/', function($m) {
        return $m[2] . '($' . $m[1] . ($m[3] ? ', ' . $m[3] : '') . ')';
    }, $code);
}

function pq_stage_convert_variables($code) {
    return preg_replace('/([@$#])([a-zA-Z_][a-zA-Z0-9_]*)/', '$\2', $code);
}

function pq_stage_analyze_type_rules($code) {
    if (preg_match('/@[a-zA-Z0-9_]+\.(?![a-zA-Z_]+\()/', $code)) {
        throw new \RuntimeException("PQ Syntax Error: Scalar (@) variables cannot access properties.");
    }
    if (preg_match('/\$[a-zA-Z0-9_]+\.[a-zA-Z_]/', $code) && !preg_match('/\$[a-zA-Z0-9_]+\[/', $code)) {
         throw new \RuntimeException("PQ Syntax Error: Collection ($) variables cannot directly access properties.");
    }
    if (preg_match('/#[a-zA-Z0-9_]+\[/', $code)) {
        throw new \RuntimeException("PQ Syntax Error: Object (#) variables cannot access array indices.");
    }
}

function pq_stage_object_scope($code) {
    $code = preg_replace('/\\(#([a-zA-Z_][a-zA-Z0-9_]*)\\)\\.obj\\s*\\[/i', 'PQEngine::start_object_scope("$1"); {', $code);
    return preg_replace('/(?<![\'"0-9a-zA-Z_\$\-\)"])\\](?![\,\;\]])/', '} PQEngine::end_scope();', $code);
}

function pq_stage_reserved_chain($code) {
    $code = preg_replace('/have\\s+([a-zA-Z_]+)(?:\\[([0-9]+)\\])?\\s*;/i', 'PQEngine::register_component("$1", "$2");', $code);
    
    foreach (PQ_RESERVED_MAP as $r => $bridge) {
        $code = preg_replace(
            '/(?<![\$a-zA-Z0-9_])' . preg_quote($r, '/') . '\.([a-zA-Z_][a-zA-Z0-9_]*)/i', 
            $bridge . '$1', 
            $code
        );
    }
    return $code;
}

function pq_stage_sanctuary($code, &$pq_blocks, &$strings, &$html_comments) {
    return preg_replace_callback(
        '/<pq\b[^>]*>(.*?)<\/pq>/is',
        function($m) use (&$pq_blocks){
            $id = '__PQ_BLOCK_' . count($pq_blocks) . '__';
            $pq_blocks[$id] = base64_encode($m[1]); 
            return $id;
        },
        $code
    );
}

function pq_stage_restore($code, $pq_blocks, $strings, $html_comments) {
    foreach ($strings as $id => $val) $code = str_replace($id, $val, $code);
    $code = preg_replace('/(?<!->)trace\\(/i', 'Trace::add(', $code);
    
    foreach ($pq_blocks as $block_id => $b64_content) {
        $raw_inner = base64_decode($b64_content);
        $compiled_pq = "<pq><?php echo '" . str_replace("'", "\\'", htmlspecialchars(htmlspecialchars_decode($raw_inner, ENT_QUOTES), ENT_QUOTES, 'UTF-8')) . "'; ?></pq>";
        $code = str_replace($block_id, $compiled_pq, $code);
    }
    
    foreach (array_reverse($html_comments, true) as $comment_id => $original_comment) {
        $code = str_replace($comment_id, $original_comment, $code);
    }
    
    // [SECURITY] Restricted PHP Functions Guard
    $dangerous = ['system', 'exec', 'passthru', 'shell_exec', 'popen', 'proc_open', 'eval', 'assert'];
    foreach ($dangerous as $fn) {
        if (stripos($code, $fn . '(') !== false) {
            throw new \RuntimeException("PQ Security Error: Restricted function detected.");
        }
    }
    return trim((string)$code);
}
?>