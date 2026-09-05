<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/engine/ready.php
 * COMPONENT : Pre-Processor Pipeline & Translation Stages
 * =========================================================
 */
$root_path = dirname(__DIR__, 1);
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

// --- 기존 상수를 define 체크 방식으로 변경 및 file_pq()-> 매핑 적용 ---
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

if (!defined('PQ_SEMICOLON_BYPASS')) {
    define('PQ_SEMICOLON_BYPASS', ['format(', 'util.', 'date.', 'time.', 'now(']);
}
if (!isset($cookie)) {
    $cookie = new PQCookie(); 
}

function pq_ready($code) {
    if ($code === null || empty(trim((string)$code))) return '';

    $pq_blocks = [];
    $strings = [];
    $html_comments = [];

    // 1. 성역 격리
   // $code = pq_stage_sanctuary($code, $pq_blocks, $strings, $html_comments);

    // 2. 문법 및 타입 규칙 정적 분석
    pq_stage_analyze_type_rules($code);

    // 3. 스코프 및 예약어 빌딩
    $code = pq_stage_object_scope($code);
    $code = pq_stage_reserved_chain($code);

    // =========================================================
    // 🚀 [v7.2 PIPELINE INTEGRATION]: 3축 유전자 변환 레이어 전격 매립
    // =========================================================
    $code = pq_stage_convert_functions($code);          // @scalar.func() -> func($scalar)
    $code = pq_stage_convert_collections_methods($code);  // $arr.count() -> count($arr)
    $code = pq_stage_convert_collections($code);          // $arr[idx].prop -> $arr[idx]->prop
    $code = pq_stage_convert_objects($code);              // #obj.prop -> $obj->prop
    $code = pq_stage_convert_variables($code);            // 단독 @, #, $ -> 순수 PHP 변수($)로 완벽 번역

    // 4. 성역 및 스트링 복원
//    $code = pq_stage_restore($code, $pq_blocks, $strings, $html_comments);

    return trim((string)$code);
}


// --- 변환 파이프라인 함수 ---

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
    // 💡 [v7.2 단독 필터 강화]: #mbr_rs 및 @cfg 같은 모든 단독 유전자를 순수 PHP 변수 규격으로 강제 정렬
    return preg_replace('/([@$#])([a-zA-Z_][a-zA-Z0-9_]*)/', '$\2', $code);
}

// --- 분석 및 기타 단계 ---

function pq_stage_analyze_type_rules($code) {
    if (preg_match('/@[a-zA-Z0-9_]+\.(?![a-zA-Z_]+\()/', $code)) {
        throw new \RuntimeException("PQ 문법 위반: 스칼라(@)에 속성 접근 불가.");
    }
    if (preg_match('/\$[a-zA-Z0-9_]+\.[a-zA-Z_]/', $code) && !preg_match('/\$[a-zA-Z0-9_]+\[/', $code)) {
         throw new \RuntimeException("PQ 문법 위반: 컬렉션($)은 속성 직접 접근 불가.");
    }
    if (preg_match('/#[a-zA-Z0-9_]+\[/', $code)) {
        throw new \RuntimeException("PQ 문법 위반: 객체(#)는 인덱스 접근 불가.");
    }
}

function pq_stage_sanctuary($code, &$pq_blocks, &$strings, &$html_comments) {
    $code = preg_replace_callback(
        '/<pq\b[^>]*>(.*?)<\/pq>/is',
        function($m) use (&$pq_blocks){
            $id = '__PQ_BLOCK_' . count($pq_blocks) . '__';
            $pq_blocks[$id] = base64_encode($m[1]); 
            return $id;
        },
        $code
    );
    return $code;
}

function pq_stage_object_scope($code) {
    $code = preg_replace('/\\(#([a-zA-Z_][a-zA-Z0-9_]*)\\)\\.obj\\s*\\[/i', 'PQEngine::start_object_scope("$1"); {', $code);
    return preg_replace('/(?<![\'"0-9a-zA-Z_\$\-\)"])\\](?![\,\;\]])/', '} PQEngine::end_scope();', $code);
}

// ready.php 의 pq_stage_reserved_chain 함수 수정
function pq_stage_reserved_chain($code) {
    $code = preg_replace('/have\\s+([a-zA-Z_]+)(?:\\[([0-9]+)\\])?\\s*;/i', 'PQEngine::register_component("$1", "$2");', $code);
    
    foreach (PQ_RESERVED_MAP as $r => $bridge) {
        // $r . '_$1(' 대신 $bridge . '$1(' 로 변경!
        $code = preg_replace(
            '/(?<![\$a-zA-Z0-9_])' . preg_quote($r, '/') . '\.([a-zA-Z_][a-zA-Z0-9_]*)/i', 
            $bridge . '$1', 
            $code
        );
    }
    return $code;
}
function pq_stage_restore($code, $pq_blocks, $strings, $html_comments) {
    foreach ($strings as $id => $val) $code = str_replace($id, $val, $code);
    $code = preg_replace('/(?<!->)trace\\(/i', 'Trace::add(', $code);
    
    // 🚀 [선생님 오리지널 철학 일치화 복원]: 
    // runner.php와 똑같이 런타임 가동 유전자 및 데이터 전송을 방해하지 않는 
    // 네이티브 마크업 주입 방식으로 완벽 롤백 씽크 고정!
    foreach ($pq_blocks as $block_id => $b64_content) {
        $raw_inner = base64_decode($b64_content);
        $compiled_pq = "<pq><?php echo '" . str_replace("'", "\\'", htmlspecialchars(htmlspecialchars_decode($raw_inner, ENT_QUOTES), ENT_QUOTES, 'UTF-8')) . "'; ?></pq>";
        $code = str_replace($block_id, $compiled_pq, $code);
    }
    
    foreach (array_reverse($html_comments, true) as $comment_id => $original_comment) $code = str_replace($comment_id, $original_comment, $code);
    
    $dangerous = ['system', 'exec', 'passthru', 'shell_exec', 'popen', 'proc_open', 'eval', 'assert'];
    foreach ($dangerous as $fn) {
        if (stripos($code, $fn . '(') !== false) throw new \RuntimeException("PQ 보안 차단: 금지된 함수 감지.");
    }
    return trim((string)$code);
}
?>