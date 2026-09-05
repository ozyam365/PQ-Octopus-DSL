<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/engine/runner.php
 * COMPONENT : run_pq / Core Cache & DX Error Code System 
 * =========================================================
 */
define('PQ_CTX_TEXT',   0);
define('PQ_CTX_ATTR',   1);
define('PQ_CTX_HTML',   2);
define('PQ_CTX_SCRIPT', 3);
define('PQ_CTX_STYLE',  4);

if (!defined('PQ_RESERVED_MAP')) {
    define('PQ_RESERVED_MAP', [
        'auth'    => 'auth()->',
        'url'     => 'PQRouter::',      
        'db'      => '$db->',
        'http'    => '$http->',
        'session' => '$session->',
        'app'     => '$app->',
        'file'    => 'file_pq()->',  
        'form'    => '$form->',
        'cookie'  => '$cookie->',
        'date'    => '$date->',
        'time'    => '$time->',
        'text'    => '$text->',
        'navi'    => '$navi->'
    ]);
}

$GLOBALS['PQ_COMPILE_LOG'] = [
    'cache' => [],
    'guard' => [],
    'compile' => [],
    'optimizer' => [],
    'error' => []
];

// 치명적 예외(Throwable) 발생 시 레이아웃 깨짐 방지 및 에러 전면 화면 출력
set_exception_handler(function (\Throwable $e) {
    // 🚀 핵심: 이전에 찍혀있던 좌측 메뉴/상단 레이아웃 버퍼를 깨끗이 지움!
    while (ob_get_level() > 0) { 
        ob_end_clean(); 
    }

    // 기본 HTML 뼈대 세팅 (메뉴 없이 중앙 전체 화면으로 렌더링)
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PQ DSL System Error</title></head>';
    echo '<body style="background:#121215; margin:0; padding:40px 20px; display:flex; justify-content:center; align-items:flex-start; min-height:100vh; box-sizing:border-box;">';
    echo '<div class="main-content-error-wrapper" style="width:100%; max-width:1100px;">';
    
    pq_render_gorgeous_error($e);
    
    echo '</div></body></html>';
    exit;
});

function pq_output_func($ctx){
    switch ($ctx) {
        case PQ_CTX_HTML:   return 'pq_raw';   
        case PQ_CTX_ATTR:   return 'pq_attr';
        case PQ_CTX_SCRIPT: return 'pq_script';
        case PQ_CTX_STYLE:  return 'pq_style';
        default:            return 'pq_clean';
    }
}

function pq_compile_expr($expr) {
    // 🎯 [v9.1.6 엄격 타입 단속 1]: $배열 변수에 Dot(.) 객체 접근 시도 시 즉시 에러 발생
    if (preg_match('/(\$[a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', $expr, $m)) {
        throw new \RuntimeException(
            "[PQ-E1004] 배열 변수({$m[1]})에는 Dot(.) 객체 접근자를 사용할 수 없습니다. 브래킷 {$m[1]}['{$m[2]}'] 구문으로 수정하세요."
        );
    }

    // 🎯 [v9.1.6 엄격 타입 단속 2]: #객체 변수에 브래킷['키'] 배열 접근 시도 시 즉시 에러 발생
    if (preg_match('/(#[a-zA-Z_][a-zA-Z0-9_]*)\s*\[\s*[\'"]?([a-zA-Z0-9_]+)[\'"]?\s*\]/', $expr, $m)) {
        throw new \RuntimeException(
            "[PQ-E1005] 객체 변수({$m[1]})에는 브래킷(['']) 배열 접근자를 사용할 수 없습니다. Dot {$m[1]}.{$m[2]} 구문으로 수정하세요."
        );
    }

    $expr = preg_replace('/(?<![a-zA-Z0-9_\$->])date\s*\(/i', 'date_pq(', $expr);
    $expr = preg_replace('/pq\.throw\s*\((.*?)\)/i', 'throw new \\Exception($1)', $expr);
    
    $expr = preg_replace(
        '/#([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=\s*\[/', 
        '$$$1 = (object)[', 
        $expr
    );

    // #rs.code -> $rs->code (객체 속성 정석 치환)
    $expr = preg_replace('/#([a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)/', '\$$1->$2', $expr);
    
    // 🎯 auth.xxx -> auth()->xxx 직관적 사전 치환
    $expr = preg_replace('/(?<![\$a-zA-Z0-9_])auth\.([a-zA-Z_][a-zA-Z0-9_]*)/i', 'auth()->$1', $expr);
    
    $expr = preg_replace('/@([a-zA-Z_][a-zA-Z0-9_]*)/', '\$$1', $expr);
    $expr = preg_replace('~#([a-zA-Z_][a-zA-Z0-9_]*)~', '$\\1', $expr);
    $expr = preg_replace('/(?<![\$a-zA-Z0-9_])url\.([a-zA-Z_][a-zA-Z0-9_]*)/i', 'PQRouter::$1', $expr);
    
    foreach (PQ_RESERVED_MAP as $r => $bridge) {
        $expr = preg_replace('/(?<![\$a-zA-Z0-9_])' . preg_quote($r, '/') . '\.([a-zA-Z_])/i', $bridge . '$1', $expr);
    }   
    
    $expr = preg_replace('/(\)|\]|\$[a-zA-Z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', '$1->$2(', $expr);
    
    // Exception / Throwable 매핑
    $expr = preg_replace('/(\$(?:e|err|ex|exception))\->message\b/i', '$1->getMessage()', $expr);
    $expr = preg_replace('/(\$(?:e|err|ex|exception))\->code\b/i',    '$1->getCode()', $expr);
    $expr = preg_replace('/(\$(?:e|err|ex|exception))\->file\b/i',    '$1->getFile()', $expr);
    $expr = preg_replace('/(\$(?:e|err|ex|exception))\->line\b/i',    '$1->getLine()', $expr);
    $expr = preg_replace('/(\$(?:e|err|ex|exception))\->trace\b/i',   '$1->getTraceAsString()', $expr);
    
    return $expr;
}

function import_pq($file_path) {
    if (file_exists($file_path)) { run_pq($file_path); }
}
function pq_resolve_path($path) {    
    $path = str_replace('/path/', '', $path);
    return ltrim($path, '/');
}
function pq_parse_var($code, $i){
    $len = strlen($code); $expr = ''; $i++;
    while($i < $len){
        $c = $code[$i];
        if (preg_match('/[a-zA-Z0-9_]/', $c)){ 
            $expr .= $c; 
            $i++; 
            continue; 
        }
        break;
    }
    return [$expr, $i - 1]; 
}

function pq_parse_object($code, $i){
    $len = strlen($code); $expr = ''; $i++;
    while($i < $len){
        $c = $code[$i];
        if (preg_match('/[a-zA-Z0-9_.]/', $c)){ $expr .= $c; $i++; continue; }
        break;
    }
    return [$expr, $i - 1]; 
}

// 🚀 pq.print() 파서 정밀 보완
function pq_compile_print($code) {
    $len = strlen($code); 
    $i = 9; // 'pq.print(' 스킵
    $buffer = ''; $args = [];
    
    while ($i < $len) {
        $ch = $code[$i];
        if ($ch === '"' || $ch === "'") { $i++; continue; }        
        if ($ch == ')') {
            if (strlen($buffer) > 0 && trim($buffer) !== '') { $args[] = '"' . addslashes($buffer) . '"'; }
            $buffer = ''; break;
        }
        // 🚀 핵심: trim()을 빼서 "30일 후: " 내부의 띄어쓰기 공백을 완벽 보존!
        if ($ch == ',' || $ch == '.') {
            if (strlen($buffer) > 0 && trim($buffer) !== '') { $args[] = '"' . addslashes($buffer) . '"'; }
            $buffer = ''; $i++; continue;
        }
        switch($ch){
            case '@':
                if (strlen($buffer) > 0) { $args[] = '"' . addslashes($buffer) . '"'; $buffer = ''; }
                list($expr, $next_i) = pq_parse_var($code, $i);
                $args[] = pq_compile_expr('@'.$expr); 
                $i = $next_i + 1; 
                continue 2;
            case '#':
                if (strlen($buffer) > 0) { $args[] = '"' . addslashes($buffer) . '"'; $buffer = ''; }
                list($expr, $next_i) = pq_parse_object($code, $i);
                $args[] = pq_compile_expr('#'.$expr); 
                $i = $next_i + 1; 
                continue 2;
            case '$':
                if (strlen($buffer) > 0) { $args[] = '"' . addslashes($buffer) . '"'; $buffer = ''; }
                list($expr, $next_i) = pq_parse_array($code, $i);
                $args[] = $expr; 
                $i = $next_i + 1;
                continue 2;
            default: $buffer .= $ch; break;             
        }           
        $i++;
    }
    $args = array_values(array_filter($args, function($v){ return $v !== '""'; }));   
    $php = 'pq_print(' . implode(',', $args) . ');';
    return [$php, $i];
}

function pq_parse_array($code, $i){
    $len = strlen($code); $expr = '$'; $depth = 0; $i++;
    while ($i < $len){
        $c = $code[$i];
        if ($depth == 0){
            if (preg_match('/[a-zA-Z0-9_]/',$c)){ $expr .= $c; $i++; continue; }
            if ($c == '['){ $depth++; $expr .= $c; $i++; continue; }
            break;
        }
        $expr .= $c;
        if ($c == '[') $depth++;
        if ($c == ']'){
            $depth--;
            if ($depth == 0){
                if (($i+1)<$len && $code[$i+1]=='['){ $i++; continue; }
                break;
            }
        }
        $i++;
    }
    return [$expr,$i];
}

function run_pq($file_path, $__parent_vars = []) {
    if (!file_exists($file_path)) return;
    static $call_stack = []; static $depth = 0;
    $depth++; $call_stack[] = $file_path;
    if ($depth > 15) { exit; }

    $cache_md5 = md5($file_path);
    $GLOBALS['PQ_ROUTE_MAP'][$cache_md5] = $file_path;

    if (!empty($__parent_vars)) {
        unset(
            $__parent_vars['__parent_vars'], $__parent_vars['file_path'], 
            $__parent_vars['cache_file'], $__parent_vars['raw'], 
            $__parent_vars['compiled'], $__parent_vars['call_stack'], $__parent_vars['depth']
        );
        extract($__parent_vars, EXTR_SKIP);
    }
    
    // 🎯 $auth 전역 변수 로드
    global $version, $db, $form, $file, $session, $cookie, $http, $date, $time, $trace, $app, $ai, $iot, $text, $excel, $pdf, $navi, $html, $auth,$pin, $ret;
    
    if (empty($auth))     { $auth     = isset($GLOBALS['auth']) ? $GLOBALS['auth'] : (function_exists('auth') ? auth() : null); }
    if (empty($db))       { $db       = isset($GLOBALS['db']) ? $GLOBALS['db'] : (function_exists('db') ? db() : null); }
    if (empty($http))     { $http     = isset($GLOBALS['http']) ? $GLOBALS['http'] : (function_exists('http_pq') ? http_pq() : null); }
    if (empty($app))      { $app      = isset($GLOBALS['app']) ? $GLOBALS['app'] : null; }
    if (empty($session))  { $session  = isset($GLOBALS['session']) ? $GLOBALS['session'] : (function_exists('session_pq') ? session_pq() : null); }
    if (empty($cookie))   { $cookie   = isset($GLOBALS['cookie']) ? $GLOBALS['cookie'] : (function_exists('cookie') ? cookie() : null); }

	// FORM 초기화 및 검증
	if (empty($form) || !is_object($form)) {
		$form = function_exists('form') ? form() : null;
	}
	if (!is_object($form)) {
		throw new RuntimeException('[PQ-E1003] PQ FORM CORE 초기화 실패');
	}
	// FILE 초기화 및 검증
	if (empty($file) || !is_object($file)) {
		$file = function_exists('file_pq') ? file_pq() : null;
	}
	if (!is_object($file)) {
		throw new RuntimeException('[PQ-E1002] PQ FILE CORE 초기화 실패');
	}
    if (empty($date))     { $date     = isset($GLOBALS['date']) ? $GLOBALS['date'] : (function_exists('date_pq') ? date_pq() : null); }
    if (empty($time))     { $time     = isset($GLOBALS['time']) ? $GLOBALS['time'] : (function_exists('time_pq') ? time_pq() : null); }
    if (empty($text))     { $text     = isset($GLOBALS['text']) ? $GLOBALS['text'] : (function_exists('text') ? text() : null); }
    if (empty($html))     { $html     = isset($GLOBALS['html']) ? $GLOBALS['html'] : (function_exists('html') ? html() : null); }
    if (empty($navi))     { $navi     = isset($GLOBALS['navi']) ? $GLOBALS['navi'] : (function_exists('navi') ? navi() : null); }

    extract($GLOBALS, EXTR_SKIP);
    if (empty($db) && function_exists('db')) { $db = db(); }
    if (empty($http) && function_exists('http_pq')) { $http = http_pq(); }
    
    $cache_file = dirname(__DIR__) . '/tmp/' . $cache_md5 . '.php';
    
    if (file_exists($cache_file) && (filemtime($cache_file) > filemtime($file_path))) {
        include $cache_file; $depth--; array_pop($call_stack); return;
    }

    try {
        pq_log('run_pq','guard', 'guard start');
        $raw = file_get_contents($file_path);
        $raw = pq_guard($raw);
        $compiled = pq_compile($raw);
        file_put_contents($cache_file, $compiled, LOCK_EX);
        include $cache_file;
        $depth--; array_pop($call_stack);
    } catch (\Throwable $e) {
        $depth--; array_pop($call_stack);
        pq_render_gorgeous_error($e, $file_path);
        throw $e;
    }
}

function pq_compile($content) {
    $GLOBALS['PQ_REPEAT_STACK'] = [];
    $output = perform_lexing($content);
    $output = perform_optimization($output);
    return $output;
}

function pq_compile_foreach($expr){
    $key = '';
    $index = '';
    if (preg_match('/\.key\((.*?)\)/', $expr, $m)) {
        $key = trim($m[1]);
        $expr = preg_replace('/\.key\(.*?\)/', '', $expr);
    }
    if (preg_match('/\.index\((.*?)\)/', $expr, $m)) {
        $index = trim($m[1]);
        $expr = preg_replace('/\.index\(.*?\)/', '', $expr);
    }    
    if (!preg_match('/^(.+)\.as\((.+)\)$/', $expr, $m)) {
        return "/* PQ REPEAT/FOREACH ERROR : Check syntax '.as(@item)' or missing ':' */";
    }
    $list = pq_compile_expr(trim($m[1]));
    $row  = pq_compile_expr(trim($m[2]));

    if ($key !== '') { $key = pq_compile_expr($key); }
    if ($index !== '') { $index = pq_compile_expr($index); }   

    $php = '';
    if ($index !== '') { $php .= "{$index} = 0;\n"; }
    if ($key != '') {
        $php .= "foreach ({$list} as {$key} => {$row}){";
    } else {
        $php .= "foreach ({$list} as {$row}){";
    }
    $GLOBALS['PQ_REPEAT_STACK'][] = [
        'key'   => $key,
        'index' => $index,
        'row'   => $row,
        'list'  => $list,
    ];
    return $php;
}

function pq_compile_for($full_expr){
    $init_var = '';
    $init_val = '0';
    $step_val = '1';

    if (preg_match('/\.set\(\s*@([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(.*?)\s*\)/i', $full_expr, $m)) {
        $init_var = '$' . $m[1];
        $init_val = pq_compile_expr($m[2]);
        $full_expr = preg_replace('/\.set\(.*?\)/i', '', $full_expr);
    }

    if (preg_match('/\.step\(\s*(.*?)\s*\)/i', $full_expr, $m)) {
        $step_val = pq_compile_expr($m[1]);
        $full_expr = preg_replace('/\.step\(.*?\)/i', '', $full_expr);
    }

    $clean_cond = trim($full_expr);
    if (strpos($clean_cond, '(') === 0 && strrpos($clean_cond, ')') === strlen($clean_cond) - 1) {
        $clean_cond = substr($clean_cond, 1, -1);
    }
    $clean_cond = pq_compile_expr($clean_cond);

    if (empty($init_var) && preg_match('/\$([a-zA-Z_][a-zA-Z0-9_]*)/', $clean_cond, $m)) {
        $init_var = '$' . $m[1];
    }

    if (empty($init_var)) {
        return "/* PQ FOR ERROR : missing .set() */";
    }

    return "for ({$init_var} = {$init_val}; {$clean_cond}; {$init_var} += {$step_val}){";
}

function pq_compile_while($expr){
    $expr = trim($expr);
    if (strpos($expr, '(') === 0 && strrpos($expr, ')') === strlen($expr) - 1) {
        $expr = substr($expr, 1, -1);
    }
    $expr = pq_compile_expr($expr);
    return "while ({$expr}){";
}

function pq_compile_repeat($expr){
    if (strpos($expr, '.set(') !== false) {
        return pq_compile_for($expr);
    }
    return pq_compile_while($expr);
}

function perform_lexing($content) {
    $len = strlen($content); $output = ""; $state = "HTML"; $ctx = PQ_CTX_TEXT;
    $quote_char = ""; $echo_zone_buf = ""; $inside_tag_bracket = false; $inside_attr = false; $attr_quote = '';    
    
    for ($i = 0; $i < $len; $i++) { 
        $char = $content[$i];
        $next = ($i + 1 < $len) ? $content[$i+1] : "";
        
        if ($state === "HTML") {
            $remain_str = substr($content, $i);
            if ($char === '<') {
                if (strncasecmp($remain_str, "<textarea", 9) === 0) { $ctx = PQ_CTX_HTML; $inside_tag_bracket = true; } 
                elseif (strncasecmp($remain_str, "<style", 6) === 0) { $ctx = PQ_CTX_STYLE; $inside_tag_bracket = true; } 
                elseif (strncasecmp($remain_str, "<script", 7) === 0) { $ctx = PQ_CTX_SCRIPT; $inside_tag_bracket = true; } 
                elseif ($next !== '/') { $inside_tag_bracket = true; }
            }
            if ($char === '>') {
                $inside_tag_bracket = false; $inside_attr = false; $attr_quote = '';
                if ($ctx == PQ_CTX_ATTR) { $ctx = PQ_CTX_TEXT; }
                if (strncasecmp($remain_str, ">", 1) === 0) {
                    if ($i >= 10 && strncasecmp(substr($content, $i - 10), "</textarea>", 11) === 0) $ctx = PQ_CTX_TEXT;
                    if ($i >= 7 && strncasecmp(substr($content, $i - 7), "</style>", 8) === 0) $ctx = PQ_CTX_TEXT;
                    if ($i >= 8 && strncasecmp(substr($content, $i - 8), "</script>", 9) === 0) $ctx = PQ_CTX_TEXT;
                }
            }
            if ($inside_tag_bracket && !$inside_attr && ($char == '"' || $char == "'")) { $inside_attr = true; $attr_quote = $char; $ctx = PQ_CTX_ATTR; }             
            if ($inside_attr && $char == $attr_quote) { $inside_attr = false; $attr_quote = ''; }                
            if ($char === '{' && $next === '{') { $echo_zone_buf = ""; $state = "ECHO_ZONE"; $i++; continue; }
            if ($char === '[' && $next === '[') { $state = "NORMAL"; $output .= "<?php "; $i++; continue; }
            if ($char === 'i' && substr($content, $i, 3) === "inc") {
                $remain_line = substr($content, $i);
                if (preg_match('~^inc\s*\(?\s*["\']([^"\']+)["\']\s*\)?\s*;~i', $remain_line, $match)) {
                    $clean_path = preg_replace('/@([a-zA-Z0-9_]+)/', '{$$1}', $match[1]);
                    $clean_path = pq_resolve_path($clean_path);
                    $output .= "<?php run_pq(PQ_DIR . '/' . \"" . $clean_path . "\", get_defined_vars()); ?>";
                    $i += (strlen($match[0]) - 1); continue;
                }
            }
            $output .= $char;
        } 
        else if ($state === "ECHO_ZONE") {
            if ($char === '}' && $next === '}') {
                $echo_payload = ltrim($echo_zone_buf, "= \t\n\r\0\x0B");
                $compiled_echo = pq_compile_expr($echo_payload);
                $fn = pq_output_func($ctx);
                $output .= "<?php echo " . $fn . "(" . $compiled_echo . "); ?>";
                if ($ctx === PQ_CTX_ATTR && !$inside_tag_bracket) { $ctx = PQ_CTX_TEXT; }
                $state = "HTML"; $i++; continue;
            }
            $echo_zone_buf .= $char;
        }
        else if ($state === "NORMAL") {
            $is_front_boundary = ($i === 0) || !preg_match('/[a-zA-Z0-9_]/', $content[$i-1]);

            if ($is_front_boundary && $char === 'f' && substr($content, $i, 3) === "fn ") {
                $remain_fn = substr($content, $i);
                if (preg_match('/^fn\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\((.*?)\)\s*(:|\{)?/i', $remain_fn, $fn_m)) {
                    $fn_name = $fn_m[1];
                    $fn_args = pq_compile_expr($fn_m[2]);
                    $output .= "function " . $fn_name . "(" . $fn_args . ") {";
                    $i += (strlen($fn_m[0]) - 1);
                    continue;
                }
            }

            if ($char === '.' && substr($content, $i, 6) === ".fail(") {
                if (preg_match('/^\.fail\(\s*#([a-zA-Z_][a-zA-Z0-9_]*)\s*\)\s*:?;?/i', substr($content, $i), $m)) {
                    $var_name = $m[1];
                    $output .= "} catch (\\Throwable $" . $var_name . ") {";
                    $i += (strlen($m[0]) - 1);
                    continue;
                }
            }

            if ($char === '.' && substr($content, $i, 5) === ".run:") {
                $jump_len = 5;
                if (isset($content[$i + 5]) && $content[$i + 5] === ';') { $jump_len = 6; }
                $output .= "} finally {";
                $i += ($jump_len - 1);
                continue;
            }            
			if ($char === 'd' && strncasecmp(substr($content, $i, 5), "date(", 5) === 0) {
				$prevChar = ($i > 0) ? $content[$i - 1] : '';
				$prevTwoChar = ($i > 1) ? substr($content, $i - 2, 2) : '';

				// 1. 단어의 일부 ($date, update, candidate) 인지 검사
				$isInsideWord = preg_match('/[a-zA-Z0-9_\$]/', $prevChar);
				
				// 2. 객체 메서드 호출 ($obj->date) 인지 검사
				$isMethodCall = ($prevTwoChar === '->');

				// 순수 전역 함수 date() 호출일 때만 치환
				if (!$isInsideWord && !$isMethodCall) {
					$output .= "date_pq(";
					$i += 4;
					continue;
				}
			}
			
            if ($is_front_boundary) {
                if (in_array($char, ['i', 'e', 'f', 'w', 'h', 'b', 'r', 's'])) {
                    if ($char === 'r' && substr($content, $i, 5) === "rule:") {
                        $jump_len = 5;
                        if (isset($content[$i + 5]) && $content[$i + 5] === ';') { $jump_len = 6; }
                        $output .= "try {";
                        $i += ($jump_len - 1);
                        continue;
                    }  

                    $ctrl_word = "";
                    if ($char === 'i' && substr($content, $i, 2) === "if") { $ctrl_word = "if"; }                
                    elseif ($char === 'e' && substr($content, $i, 6) === "elseif") { $ctrl_word = "elseif"; }
                    elseif ($char === 'f' && substr($content, $i, 7) === "foreach") { $ctrl_word = "foreach"; }
                    elseif ($char === 'f' && substr($content, $i, 3) === "for") { $ctrl_word = "for"; }
                    elseif ($char === 'w' && substr($content, $i, 5) === "while") { $ctrl_word = "while"; }
					elseif ($char === 's' && substr($content, $i, 6) === "switch") { $ctrl_word = "switch"; }					
                    elseif ($char === 'h' && substr($content, $i, 3) === "has") { $ctrl_word = "has"; }
                    elseif ($char === 'b' && substr($content, $i, 5) === "blank") { $ctrl_word = "blank"; }
                    elseif ($char === 'r' && substr($content, $i, 6) === "repeat") { $ctrl_word = "repeat"; } 
                    
                    if ($ctrl_word !== "") {
                        $next_char_idx = $i + strlen($ctrl_word);
                        $is_rear_boundary = ($next_char_idx >= $len) || !preg_match('/[a-zA-Z0-9_]/', $content[$next_char_idx]);
                        if ($is_rear_boundary) {
                            $open_bracket_idx = -1;
                            for ($scan_b = $next_char_idx; $scan_b < $len; $scan_b++) {
                                $sb_char = $content[$scan_b];
                                if ($sb_char === " " || $sb_char === "\t" || $sb_char === "\n" || $sb_char === "\r") { continue; }
                                if ($sb_char === '(') { $open_bracket_idx = $scan_b; break; }
                                break;
                            }
                            if ($open_bracket_idx !== -1) {
                                $start_idx = $open_bracket_idx + 1; $bracket_stack = 1; $condition_buf = ""; $match_end_idx = $start_idx;
                                for ($j = $start_idx; $j < $len; $j++) {
                                    $c = $content[$j];
                                    if ($c === '(') $bracket_stack++;
                                    if ($c === ')') $bracket_stack--;
                                    if ($bracket_stack === 0) { $match_end_idx = $j; break; }
                                    $condition_buf .= $c;
                                }
                                $colon_found = false; $final_jump_idx = $match_end_idx;
                                $repeat_expr = trim($condition_buf);
                                for ($k = $match_end_idx + 1; $k < $len; $k++) {
                                    $c_char = $content[$k];
                                    if ($ctrl_word === 'repeat') {
                                        if ($c_char === ':') {
                                            $colon_found = true;
                                            $final_jump_idx = $k;
                                            if ($k + 1 < $len && $content[$k + 1] === ';') {
                                                $final_jump_idx = $k + 1;
                                            }
                                            break;
                                        }
                                        $repeat_expr .= $c_char;
                                        continue;
                                    }                                    
                                    if ($c_char === " " || $c_char === "\t" || $c_char === "\n" || $c_char === "\r") { continue; }
                                    if ($c_char === ':') {
                                        $colon_found = true; $final_jump_idx = $k;
                                        if ($k + 1 < $len && $content[$k + 1] === ';') { $final_jump_idx = $k + 1; }
                                        break;
                                    }
                                    break;
                                }
                                                            
                                if ($colon_found) {                                
                                    if ($ctrl_word === 'repeat') {
                                        $expr = trim($repeat_expr);                                
                                        if (strpos($expr, '.as(') !== false) {
                                            $output .= pq_compile_foreach($expr);
                                        } else {
                                            $output .= pq_compile_repeat($expr);
                                        }
                                        $i = $final_jump_idx;                                    
                                        continue;
                                    }
                                    elseif ($ctrl_word === 'elseif') { 
                                        $output .= '} elseif (' . pq_compile_expr(trim($condition_buf)) . ') {'; 
                                    } 
                                    else {
                                        $condition = pq_compile_expr(trim($condition_buf));
                                        switch ($ctrl_word) {
                                            case 'has':                                                     
                                                $var_php = str_replace('@', '$', $condition); 
                                                $output .= 'if(isset(' . $var_php . ') && has(' . $var_php . ')){';
                                                break;
                                            case 'blank':
                                                $var_php = str_replace('@', '$', $condition);
                                                $output .= 'if(!isset(' . $var_php . ') || blank(' . $var_php . ')){';                                        
                                                break;                                                     
                                            default:
                                                $output .= $ctrl_word . '(' . $condition . '){'; 
                                                break;
                                        } 
                                    }
                                    $i = $final_jump_idx; continue;
                                }
                            }
                        }
                    }
                }
            }

            if ($char === 'e' && substr($content, $i, 4) === "else" && substr($content, $i, 6) !== "elseif") {
                $colon_found = false; $final_jump_idx = $i + 3;
                for ($k = $i + 4; $k < $len; $k++) {
                    $c_char = $content[$k];
                    if ($c_char === " " || $c_char === "\t" || $c_char === "\n" || $c_char === "\r") { continue; }
                    if ($c_char === ':') {
                        $colon_found = true; $final_jump_idx = $k;
                        if ($k + 1 < $len && $content[$k + 1] === ';') { $final_jump_idx = $k + 1; }
                        break;
                    }
                    break;
                }
                if ($colon_found) { $output .= '} else {'; $i = $final_jump_idx; continue; }
            }

            if (preg_match('/^end(has|blank|if|for|while|foreach|repeat|switch|rule|fn)\b/i', substr($content, $i), $end_m)) {
                $matched_word = strtolower($end_m[1]);
                switch ($matched_word) {
                    case 'foreach': $end_len = 10; break;
                    case 'repeat':  $end_len = 10; break;
                    case 'switch':  $end_len = 9; break;                    
                    case 'while':   $end_len = 8; break;
                    case 'blank':   $end_len = 8; break;
                    case 'rule':    $end_len = 7; break;                    
                    case 'has':     $end_len = 6; break; 
                    case 'for':     $end_len = 6; break;
                    case 'fn':      $end_len = 5; break;
                    case 'if':      $end_len = 5; break;
                    default:        $end_len = strlen($end_m[0]); break;
                }
                $final_jump_idx = $i + $end_len - 1;
                for ($k = $i + $end_len; $k < $len; $k++) {
                    if (in_array($content[$k], [' ', "\t", "\n", "\r", '(', ')', ';'])) {
                        $final_jump_idx = $k; continue; 
                    }
                    break;
                }

                if ($matched_word === 'repeat' && !empty($GLOBALS['PQ_REPEAT_STACK'])) {
                    $repeat = array_pop($GLOBALS['PQ_REPEAT_STACK']);
                    if (!empty($repeat['index'])) {
                        $output .= $repeat['index'] . "++;\n";
                    }
                }
                
                $output .= "}\n";
                $i = $final_jump_idx; 
                continue;
            }

            if ($char === 'p') {
                $remain = substr($content, $i);
                if (preg_match('/^pq\.(print|echo)\(/', $remain)) {
                    list($compiled, $jump) = pq_compile_print($remain);
                    $output .= $compiled;
                    $i += $jump;
                    continue;
                }
            }            
            if ($char === 'p' && substr($content, $i, 6) === "print ") { $output .= "echo "; $i += 5; continue; }
            if ($char === 'e' && substr($content, $i, 5) === "echo ") { $output .= "echo "; $i += 4; continue; }
            if ($char === ']' && $next === ']') {
                if (($i + 2 < $len) && $content[$i+2] === ']') { $output .= $char; continue; }
                $chk_buf = rtrim($output); $last_token = substr($chk_buf, -1);
                if ($last_token !== ';' && $last_token !== '}' && $last_token !== '{' && $last_token !== ':') { $chk_buf .= ";"; }
                $output = $chk_buf; $state = "HTML"; $output .= " ?>"; $i++; continue;
            }
            if ($char === '"' || $char === "'" || $char === '`') { $state = "STRING"; $quote_char = $char; $output .= $char; continue; }
            if ($char === '/' && $next === '/') { $state = "COMMENT"; $i++; continue; }
            
            $remain_block = substr($content, $i, 15);
            if (preg_match('/^(if|foreach|else|while|for)\b/i', $remain_block, $reg_match)) {
                $output .= $reg_match[0]; $i += (strlen($reg_match[0]) - 1); continue;
            }
            
			$maps = [ 
				'pq.'     => 'pq()->', 				
				'auth.'   => 'auth()->', 
				'url.'    => 'PQRouter::',
				'app.'    => '$app->', 
				'trace.'  => 'Trace::', 
				'db.'     => '$db->', 
				'date.'   => 'date_pq()->', 
				'time.'   => '$time->', 
				'file.'   => 'file_pq()->', 
				'http.'   => '$http->', 
				'session.'=> '$session->', 
				'form.'   => '$form->', 
				'rgx.'    => '$rgx->', 
				'cookie.' => '$cookie->', 
				'text.'   => '$text->', 
				'navi.'   => '$navi->', 
				'PQRouter.' => 'PQRouter::'
			];
            $matched = false;
            foreach($maps as $k => $v) {
                if (strncasecmp(substr($content, $i, strlen($k)), $k, strlen($k)) === 0) {
                    $output .= $v; $i += (strlen($k) - 1); $matched = true; break;
                }
            }
            if ($matched) continue;
            if ($char === '.') {
                $after_dot = substr($content, $i + 1, 50);
                if (preg_match('/^\{@([a-zA-Z_][a-zA-Z0-9_]*)\}/', $after_dot, $m)) {
                    $output .= '->{$' . $m[1] . '}'; $i += strlen($m[0]); continue;
                }
                $after_dot = substr($content, $i + 1, 15);
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\s*\(?/i', $after_dot)) {
                    $last_out_char = substr(rtrim($output), -1);
                    if ($last_out_char === ')' || $last_out_char === '}' || preg_match('/[a-zA-Z0-9_]/', $last_out_char)) {
                        $output .= "->"; continue;
                    }
                }
            }                
			if ($char === '@') {
				$remain = substr($content, $i + 1);
				if (preg_match('/^(auth|app|url|db|http|session|form|date|time|file|text|cookie|trace)\.[a-zA-Z_]/i', $remain)) { 
					$output .= '@'; 
					continue; 
				}
				
				list($expr, $i) = pq_parse_var($content, $i); 
				$output .= '$' . $expr; 
				continue;
			}
			if ($char === '#' && $state === "NORMAL") {
				$remain_hash = substr($content, $i + 1);

				// 🚀 1. #변수 = session.get(...) 패턴 (문법 괄호 충돌 완벽 방지)
				if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*session\.get\s*\((.*?)\)/i', $remain_hash, $m)) {
					$var_name = $m[1];
					$sess_arg = pq_compile_expr(trim($m[2]));
					
					// 삼항 연산자로 세션 데이터가 있을 때만 (object) 변환, 없으면 null 유지
					$output .= '$' . $var_name . ' = ($s_data = $session->get(' . $sess_arg . ')) ? (is_array($s_data) ? (object)$s_data : $s_data) : null';
					
					// '#변수명 = session.get(...)' 전체 길이를 인덱스에서 정확히 스킵
					$i += strlen('#' . $m[0]) - 1;
					continue;
				}

				// 🚀 2. #변수 = [ ... ] 패턴 (배열 대입 시 객체로 자동 캐스팅)
				if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*\[/i', $remain_hash, $m)) {
					$var_name = $m[1];
					$output .= '$' . $var_name . ' = (object)[';
					$i += strlen('#' . $m[0]) - 1;
					continue;
				}

				if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\./i', $remain_hash, $m) || 
					preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*\[/i', $remain_hash, $m) || 
					preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*obj\s*\(/i', $remain_hash, $m) || 
					preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*#[a-zA-Z_]/i', $remain_hash, $m)) {
					$output .= '$' . $m[1]; 
					$i += strlen($m[1]); 
					continue;
				} 
				else if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)/', $remain_hash, $m)) {
					$output .= '$' . $m[1]; 
					$i += strlen($m[1]); 
					continue;
				}
			}
            if ($char === 'i' && substr($content, $i, 3) === "inc") {
                $remain_line = substr($content, $i);
                if (preg_match('~^inc\s*\(?\s*["\']([^"\']+)["\']\s*\)?\s*;~i', $remain_line, $match)) {
                    $clean_path = preg_replace('/@([a-zA-Z0-9_]+)/', '{$$1}', $match[1]);
                    $clean_path = pq_resolve_path($clean_path);
                    $output .= "run_pq(PQ_DIR . '/' . \"" . $clean_path . "\", get_defined_vars());";
                    $i += (strlen($match[0]) - 1); continue;
                }
            }
            if (substr($content, $i, 6) === "import") {
                $output .= "include_once (defined('PQ_HTML') ? PQ_HTML : (defined('PQ_VIEW') ? PQ_VIEW : dirname(__DIR__, 2) . '/html')) . '/' . ";
                $i += 5; continue;
            }
            $output .= $char;
        } 
        else if ($state === "STRING") {
            if ($char === '\\') { $output .= $char . $next; $i++; continue; }
            if ($char === '[' && $next === '[') {
                $remain_str = substr($content, $i);
                if (preg_match('/^\[\[\s*=\s*(.*?)\s*\]\]/', $remain_str, $str_m)) {
                    $payload = pq_compile_expr(trim($str_m[1]));                      
                    $fn = pq_output_func($ctx);                  
                    $output .= $quote_char . ' . ' . $fn . '(' . $payload . ') . ' . $quote_char;
                    $i += (strlen($str_m[0]) - 1); continue;
                }
            }
            if ($char === '@') {
                $remain = substr($content, $i + 1, 15);
				if (!preg_match('/^(auth|app|url|db|http|session|form|date|trace)\./i', $remain) && preg_match('/^[a-zA-Z_]/', $remain)) {
                    $output .= '$'; continue;
                }
            }
            if ($char === $quote_char) { $state = "NORMAL"; }
            $output .= $char;
        }     
        else if ($state === "COMMENT") {
            if ($char === "\n") { $state = "NORMAL"; $output .= "\n"; }
        }
    }   
    return $output;
}


function perform_optimization($output) {
// 🚀 ->run() 으로 끝나는 경우도 ->value() 자동 결합 대상에서 제외
    $output = preg_replace(
        '/(\$form\s*->\s*get\s*\(.*?\)(?:->[a-zA-Z0-9_]+\s*\(.*?\))*)(?<!->value\(\))(?<!->int\(\))(?<!->string\(\))(?<!->bool\(\))(?<!->float\(\))(?<!->val\(\))(?<!->error\(\))(?<!->run\(\))\s*;/i',
        '$1->value();',
        $output
    );
// 🚀 [추가] form()->get(...) 체이닝 끝에 마감 메서드가 없으면 ->value() 자동 결합
    // 이미 ->value(), ->int(), ->string(), ->bool(), ->float(), ->val(), ->error() 등으로 마감된 경우는 제외
    $output = preg_replace(
        '/(\$form\s*->\s*get\s*\(.*?\)(?:->[a-zA-Z0-9_]+\s*\(.*?\))*)(?<!->value\(\))(?<!->int\(\))(?<!->string\(\))(?<!->bool\(\))(?<!->float\(\))(?<!->val\(\))(?<!->error\(\))\s*;/i',
        '$1->value();',
        $output
    );
    $output = preg_replace('/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*->\s*val\((.*?)\)\s*;/i', '$$1 = val($$1, $2);', $output);
    // 중복 치환 제거 및 단순화
    $output = preg_replace('/<\?php\s*=\s*/i', '<?= ', $output);
    $output = preg_replace('/;[ \t\n]*;/', ';', $output);
    $output = preg_replace_callback('/\(\$([a-zA-Z0-9_]+)\)->\{\$([a-zA-Z0-9_]+)\}/i', function($m) { return '$' . $m[1] . '->{' . $m[2] . '}'; }, $output);

    return $output;
}
function pq_guard($content) {
    return preg_replace_callback('~<pq>(.*?)</pq>~is', function ($m) {
        $inner = $m[1];
        // 🚀 <pq> 태그 내부의 모든 PHP 시작/종료 태그 및 PQ DSL 기호를 엔티티로 치환 (실행 차단)
        $safe_inner = str_replace(
            ['<?=', '<?php', '<?', '?>', '[[', ']]'],
            ['&lt;?=', '&lt;?php', '&lt;?', '?&gt;', '&#91;&#91;', '&#93;&#93;'],
            $inner
        );
        return '<pq>' . $safe_inner . '</pq>';
    }, $content);
}
function pq_log($section, $step, $msg){
    $GLOBALS['PQ_COMPILE_LOG'][$section][] = [ 'step' => $step, 'time' => microtime(true), 'msg'  => $msg ];
}

// 🎯 [v9.0.0 핵심 스펙]: PQ DSL 전용 고유 에러 코드 식별 및 힌트 연동 대시보드
function pq_render_gorgeous_error(\Throwable $e, $current_file = '') {
    $error_file = $e->getFile(); $error_line = $e->getLine();
    $cache_name = basename($error_file, '.php');
    
    $original_file = isset($GLOBALS['PQ_ROUTE_MAP'][$cache_name]) ? $GLOBALS['PQ_ROUTE_MAP'][$cache_name] : $current_file;
    $original_line = $error_line; 
    
    if (file_exists($error_file)) {
        $file_lines = file($error_file);
        $target_line_content = isset($file_lines[$error_line - 1]) ? $file_lines[$error_line - 1] : '';
        if (preg_match('/(?:\/\*|#|\/\/)\s*PQ_LINE:\s*(\d+)/i', $target_line_content, $matches)) {
            $original_line = (int)$matches[1];
        }
    }

    $err_msg = $e->getMessage();
    $hint_box = ""; 
    $pq_error_code = "PQ-E9999";

    if (strpos($err_msg, 'Cannot access offset of type string on string') !== false) {
        $pq_error_code = "PQ-E1001";
        $hint_box = "💡 <b>[배열 접근 오폭 힌트]</b> 일반 문자열(string)을 배열처럼 대괄호 <code>['key']</code>나 <code>[idx]</code>로 열려고 시도했습니다.<br>오류 라인의 변수(예: <code>@row</code>, <code>$skin</code>)가 현재 배열이 아니라 날것의 텍스트 문자열 상태인지 점검하세요.";
    } elseif (strpos($err_msg, 'Undefined variable') !== false) {
        $pq_error_code = "PQ-E2001";
        $hint_box = "💡 <b>[미선언 변수 참조 힌트]</b> 정의되지 않은 변수를 호출했습니다. 오타가 났거나 <code>global</code> 선언, 혹은 부모단에서 변수 전파가 누락되었는지 확인하세요.";
    } elseif (strpos($err_msg, 'Call to a member function') !== false) {
        $pq_error_code = "PQ-E3001";
        $hint_box = "💡 <b>[객체 메서드 오폭 힌트]</b> 인스턴스 객체가 아닌 일반 변수나 null에 대고 <code>.method()</code> 또는 <code>->method()</code>를 호출했습니다.";
    } elseif ($e instanceof \ArgumentCountError) {
        $pq_error_code = "PQ-E4001";
        $hint_box = "💡 <b>[함수 인자 유실 힌트]</b> 함수 또는 메서드가 요구하는 필수 인자(Arguments)의 개수보다 적은 개수의 값이 인입되었습니다.";
    } elseif (strpos($err_msg, 'PQPin') !== false) {
		$pq_error_code = "PQ-E5001";
		$hint_box = "💡 <b>[Pin 바인딩 힌트]</b> <code>pin()</code> 변수 바인딩 중 참조가 올바르지 않거나 허용되지 않은 타입 연산이 시도되었습니다.";
	} elseif (strpos($err_msg, 'PQRet') !== false) {
		$pq_error_code = "PQ-E5002";
		$hint_box = "💡 <b>[Ret 반환 타입 힌트]</b> <code>ret()</code> 반환 변환 중 JSON 디코딩 실패 또는 객체/배열 캐스팅 에러가 발생했습니다.";
	} elseif (strpos($err_msg, 'PQ-E1004') !== false) {
		$pq_error_code = "PQ-E1004";
		$hint_box = "💡 <b>[배열/객체 문법 오폭 힌트]</b> <code>\$변수</code>는 <b>배열(Array)</b>입니다. Dot(<code>.</code>) 대신 브래킷 <code>\$item['key']</code> 방식을 사용해야 합니다.";
	} elseif (strpos($err_msg, 'PQ-E5005') !== false || strpos($err_msg, 'PQ-E1005') !== false) {
		$pq_error_code = "PQ-E1005";
		$hint_box = "💡 <b>[객체/배열 문법 오폭 힌트]</b> <code>#변수</code>는 <b>객체(Object)</b>입니다. 브래킷(<code>['']</code>) 대신 Dot <code>#item.key</code> 방식을 사용해야 합니다.";
	}

    $trace_lines = explode("\n", $e->getTraceAsString());

    echo "<div style='padding:25px; background:#1e1e24; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.4); font-family:\"Consolas\", \"Fira Code\", monospace; color:#e2e8f0; margin: 30px 20px 30px 280px; max-width:1100px; line-height:1.6; border:1px solid #2d2d39; word-break:break-all; text-align:left; clear: both; position: relative;'>";
    
    echo "<div style='border-bottom:1px solid #3f3f46; padding-bottom:15px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between;'>";
    echo "  <div>";
    echo "    <span style='background:#ef4444; color:#fff; padding:3px 10px; border-radius:6px; font-weight:bold; font-size:12px; margin-right:10px;'>CRITICAL ERROR</span>";
    echo "    <h2 style='display:inline; margin:0; font-size:1.3rem; color:#fca5a5; vertical-align:middle;'>" . get_class($e) . "</h2>";
    echo "  </div>";
    echo "  <span style='background:#f43f5e; color:#fff; padding:4px 12px; border-radius:20px; font-weight:bold; font-size:13px; letter-spacing:0.5px; box-shadow:0 4px 10px rgba(244,63,94,0.3);'>{$pq_error_code}</span>";
    echo "</div>";

    echo "<div style='background:#18181b; border:1px solid #27272a; padding:15px; border-radius:8px; margin-bottom:20px; font-size:13.5px; line-height:1.7;'>";
    echo "  <div style='color:#a1a1aa;'><span style='color:#38bdf8; font-weight:bold; width:120px; display:inline-block;'>📄 Template:</span> <span style='color:#e4e4e7; font-weight:bold;'>" . htmlspecialchars($original_file) . "</span></div>";
    echo "  <div style='color:#a1a1aa;'><span style='color:#eab308; font-weight:bold; width:120px; display:inline-block;'>⚙️ Compiled:</span> <span style='color:#a1a1aa; font-size:12px;'>" . htmlspecialchars($error_file) . "</span></div>";
    echo "  <div style='color:#a1a1aa; margin-top:4px;'><span style='color:#f43f5e; font-weight:bold; width:120px; display:inline-block;'>🎯 Target Line:</span> 원본 파일의 <strong style='color:#fff; background:#f43f5e; padding:2px 6px; border-radius:4px; font-size:14px;'>Line: " . $original_line . "</strong>번 줄을 수정하세요! (캐시 행: {$error_line})</div>";
    echo "</div>";

    echo "<div style='background:#2d1a1e; border-left:4px solid #ef4444; padding:15px 20px; border-radius:8px; margin-bottom:20px; color:#fecaca;'>";
    echo "  <strong style='font-size:1.1rem; display:block;'>💬 " . htmlspecialchars($err_msg) . "</strong>";
    echo "</div>";

    if (!empty($hint_box)) {
        echo "<div style='background:#1e3a8a; border-left:4px solid #3b82f6; padding:12px 18px; border-radius:8px; margin-bottom:25px; color:#dbeafe; font-size:13.5px;'>{$hint_box}</div>";
    }

    if (!empty($original_file) && file_exists($original_file)) {
        echo "<h4 style='color:#eab308; margin-top:0; margin-bottom:12px; font-size:1.05rem; border-bottom:1px solid #3f3f46; padding-bottom:8px;'>🔍 Source Code Preview (Context)</h4>";
        echo "<div style='background:#141416; padding:15px; border-radius:8px; border:1px solid #27272a; margin-bottom:25px; font-size:13px; color:#a1a1aa; line-height:1.6;'>";
        
        $src_lines = file($original_file);
        $start_scan = max(0, $original_line - 3);
        $end_scan = min(count($src_lines) - 1, $original_line + 1);
        
        for ($line_idx = $start_scan; $line_idx <= $end_scan; $line_idx++) {
            $curr_num = $line_idx + 1;
            $line_content = rtrim($src_lines[$line_idx]);
            $is_target = ($curr_num === $original_line);
            
            $bg_style = $is_target ? "background:#2d1a1e; color:#fca5a5; font-weight:bold; border-left:3px solid #ef4444; padding-left:5px;" : "padding-left:8px;";
            $num_color = $is_target ? "#ef4444" : "#71717a";
            
            echo "<div style='display:flex; {$bg_style}'>";
            echo "  <span style='width:40px; color:{$num_color}; display:inline-block; text-align:right; margin-right:15px; user-select:none;'>{$curr_num} |</span>";
            echo "  <span style='white-space:pre-wrap; text-align:left;'>" . htmlspecialchars($line_content) . "</span>";
            echo "</div>";
        }
        echo "</div>";
    }

    echo "<h4 style='color:#38bdf8; margin-top:0; margin-bottom:12px; font-size:1.05rem; border-bottom:1px solid #3f3f46; padding-bottom:8px;'>📂 PHP Runtime Stack Trace</h4>";
    echo "<div style='background:#141416; padding:10px; border-radius:8px; border:1px solid #27272a; font-size:13px;'>";
    foreach ($trace_lines as $line) {
        $line = trim($line); if (empty($line)) continue;
        $line_html = preg_replace('/^(#\d+)/', '<span style="color:#ef4444; font-weight:bold; margin-right:8px;">$1</span>', htmlspecialchars($line));
        echo "<div style='padding:5px 8px; border-bottom:1px solid #1f1f23; font-size:12.5px;'>{$line_html}</div>";
    }
    echo "</div>";
    echo "</div>";
}

function pq_dump_compile_log() {
    echo "<div style='padding:20px; text-align:center; color:#71717a; font-family:monospace;'>Compile Log Trace Complete.</div>";
}

if (!function_exists('pq_clean')) { function pq_clean($v) { return $v; } }
if (!function_exists('pq_raw')) { function pq_raw($v) { return (string)$v; } }
if (!function_exists('pq_attr')) { function pq_attr($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('pq_html')) { function pq_html($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('pq_script')) { function pq_script($v) { return json_encode($v); } }
if (!function_exists('pq_style')) { function pq_style($v) { return preg_replace('/[^a-zA-Z0-9\s\#\.\:\;\-\,\(\)]/', '', $v); } }
?>