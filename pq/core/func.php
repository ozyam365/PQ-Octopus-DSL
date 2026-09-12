<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/func.php  
 * COMPONENT : PQ Core Helper Functions & Collection Matrix
 * =========================================================
 */

// --- [1. DATA COLLECTION MATRIX] ---
class PQData extends ArrayObject {
    public function __get($n) { return $this[$n] ?? null; }

    public function pagenavi($add = []) {
        $data = array_filter(array_merge($this->getArrayCopy(), $add), fn($v) => ($v !== '' && $v !== null));
        $url_parts = parse_url($_SERVER['REQUEST_URI'] ?? '/');
        return ($url_parts['path'] ?? '/') . ($data ? "?" . http_build_query($data) : "");
    }

    public function url($add = []) { return $this->pagenavi($add); }

    public function filled($key = null) {
        if ($key === null) return $this->count() > 0;
        return !empty(trim((string)($this[$key] ?? '')));
    }

    public function only(...$keys) {
        $keys = is_array($keys[0] ?? null) ? $keys[0] : $keys;
        $res = array_intersect_key($this->getArrayCopy(), array_flip($keys));
        return pq_data($res);
    }

    public function except(...$keys) {
        $keys = is_array($keys[0] ?? null) ? $keys[0] : $keys;
        $res = array_diff_key($this->getArrayCopy(), array_flip($keys));
        return pq_data($res);
    }

    public function has($key) { return $this->offsetExists($key); }
    public function where($c) { return pq_where($this, $c); }
    public function pluck($f) { return pq_pluck($this, $f); }
    public function join($glue = ', ') { return implode($glue, $this->getArrayCopy()); }
    
    public function __toString() { return json_encode($this->getArrayCopy(), JSON_UNESCAPED_UNICODE); }
}

// [ENGINE CORE] Data Wrapper Functions
function pq_data($a) { 
    return new PQData((array)$a); 
}

// [ENGINE CORE] Safe Fallback Bridge for ret()
if (!function_exists('ret')) {
    function ret($data = null) {
        if (function_exists('ret_pq')) {
            return ret_pq($data);
        }
        return pq_data($data);
    }
}

// --- [2. OUTPUT & SANITIZATION HELPERS] ---
/**
 * Explicit sanitization helpers to allow explicit developer control
 */
function pq_safe($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function pq_attr($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function pq_html($v) { return (string)$v; } // Raw output without sanitization

// --- [3. ARRAY EVALUATION ENGINE] ---
function pq_where($arr, $cond) {
    if ($arr instanceof PQData) $arr = $arr->getArrayCopy();
    if (!is_array($arr)) return pq_data([]);

    preg_match('/(\w+)\s*([><=]+)\s*(.*)/', trim((string)$cond, "\"' "), $m);
    if(!$m) return pq_data($arr);
    
    list(, $f, $op, $val) = $m; 
    $val = trim($val, " '\"");

    $res = array_filter($arr, function($r) use ($f, $op, $val) {
        $t = (is_array($r) ? ($r[$f] ?? 0) : ($r->$f ?? 0));
        return match($op) {
            '>'   => $t > $val,
            '<'   => $t < $val,
            '>='  => $t >= $val,
            '<='  => $t <= $val,
            '==', '=' => $t == $val,
            '!='  => $t != $val,
            '<>'  => $t != $val,         
            default => false
        };
    });
    return pq_data(array_values($res));
}

function pq_pluck($arr, $field) {
    if ($arr instanceof PQData) $arr = $arr->getArrayCopy();
    return pq_data(array_column($arr, $field));
}

// --- [4. RESPONSE OUTPUT FILTER] ---
function pq_output_filter($html) {
    // 1. Path alias replacement
    $html = str_replace('/path/', PQ_BASE . '/', $html);   
    // 2. Future expansion space (HTML Minification, Debug stripping, etc.)   
    return $html;
}
?>