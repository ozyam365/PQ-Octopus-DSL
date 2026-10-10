<?php
/**
 * =========================================================
 * PQ Fluent Regex Builder Core Engine
 * FILENAME  : /pq/core/rgx.php
 * UPDATE    : 2026-10-10 PM 04:50
 * =========================================================
 */

class Rgx {
    protected $target;        // Target text payload
    protected $patterns = []; // Assembled pattern fragments
    protected $is_not = false; // Inverse modifier toggle
    protected $modifiers = ['u']; // Default UTF-8 modifier flag

    // Pattern presets
    protected static $presets = [
        'email'      => '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
        'phone'      => '\d{2,3}-\d{3,4}-\d{4}',
        'mobile'     => '010-\d{3,4}-\d{4}',
        'url'        => 'https?:\/\/(?:www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b(?:[-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)',
        'ip'         => '(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)',
        'id'         => '[a-zA-Z0-9_]+',
        'password'   => '(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}',
        'zipcode'    => '\d{5}',
        'creditcard' => '\d{4}-\d{4}-\d{4}-\d{4}',
        'filename'   => '[a-zA-Z0-9_\-\.]+\.[a-zA-Z0-9]+',
        'html'       => '<[^>]*>'
    ];

    // Character type mappings
    protected static $types = [
        'eng'    => 'a-zA-Z',
        'kor'    => '가-힣',
        'int'    => '0-9',
        'float'  => '0-9\.',
        'num'    => '0-9',
        'alpha'  => 'a-zA-Z',
        'alnum'  => 'a-zA-Z0-9',
        'space'  => '\s',
        'symbol' => '`~!@#\$%\^&\*\(\)_\+=\-\[\]\{\}\\\|;:\'",\.<>\/\?]'
    ];

    public function __construct($target_or_pattern = '') {
        if (is_object($target_or_pattern)) {
            $target_or_pattern = (string)$target_or_pattern;
        }

        if (is_string($target_or_pattern) && (str_starts_with($target_or_pattern, '/') || str_starts_with($target_or_pattern, '~'))) {
            $this->patterns[] = trim($target_or_pattern, '/~iug');
        } else {
            $this->target = $target_or_pattern;
        }
    }

    // --- [1. BUILDER PIPELINE METHODS] ---
    public function pattern($name, $auto_anchor = false) {
        $key = strtolower($name);
        if (isset(self::$presets[$key])) {
            $p = self::$presets[$key];
            if ($auto_anchor) {
                $p = '^' . $p . '$';
            }
            $this->patterns[] = $p;
        }
        return $this;
    }

    public function rep($search, $replace = null) {
        return $this->replace($search, $replace);
    }

    public function type($names) {
        $name_list = array_map('trim', explode(',', $names));
        $merged_chars = '';

        foreach ($name_list as $name) {
            $key = strtolower($name);
            if (isset(self::$types[$key])) {
                $merged_chars .= self::$types[$key];
            }
        }

        if ($merged_chars !== '') {
            if ($this->is_not) {
                $this->patterns[] = '[^' . $merged_chars . ']';
                $this->is_not = false;
            } else {
                $this->patterns[] = '[' . $merged_chars . ']';
            }
        }
        return $this;
    }

    public function text($str) {
        $this->patterns[] = preg_quote((string)$str, '/');
        return $this;
    }

    public function range($str) {
        if ($this->is_not) {
            $this->patterns[] = '[^' . $str . ']';
            $this->is_not = false;
        } else {
            $this->patterns[] = '[' . $str . ']';
        }
        return $this;
    }

    public function symbol($char = '') {
        if ($char === '') {
            $this->patterns[] = '[' . self::$types['symbol'] . ']';
        } else {
            $this->patterns[] = preg_quote((string)$char, '/');
        }
        return $this;
    }

    public function space() {
        $this->patterns[] = '\s';
        return $this;
    }

    // --- [2. OPTION & MODIFIER METHODS] ---
    public function len($min, $max = null) {
        $last_idx = count($this->patterns) - 1;
        if ($last_idx >= 0) {
            $target = $this->patterns[$last_idx];

            if (strlen($target) > 1 && !preg_match('/^\[.*\]$/', $target) && !preg_match('/^\(.*\)$/', $target)) {
                $target = '(?:' . $target . ')';
            }

            $suffix = ($max === null) ? '{' . $min . '}' : '{' . $min . ',' . $max . '}';
            $this->patterns[$last_idx] = $target . $suffix;
        }
        return $this;
    }

    public function repeat($min = 0, $max = null) {
        return $this->len($min, $max);
    }

    public function start() {
        array_unshift($this->patterns, '^');
        return $this;
    }

    public function end() {
        $this->patterns[] = '$';
        return $this;
    }

    public function upper() {
        return $this->range("A-Z");
    }

    public function lower() {
        return $this->range("a-z");
    }

    public function not() {
        $this->is_not = true;
        return $this;
    }

    public function ignore() {
        if (!in_array('i', $this->modifiers)) {
            $this->modifiers[] = 'i';
        }
        return $this;
    }

    public function multiline() {
        if (!in_array('m', $this->modifiers)) {
            $this->modifiers[] = 'm';
        }
        return $this;
    }

    // --- [3. EXECUTION & DEBUG METHODS] ---

    public function dump() {
        $regex = $this->compile();
        $is_match = $this->match() ? 'TRUE' : 'FALSE';

        echo "<pre style='background:#1e1e1e; color:#00ff66; padding:15px; border-radius:8px; font-family:monospace; line-height:1.5; border:1px solid #333;'>";
        echo "<b style='color:#ff007f;'>[PQ RGX DEBUG ENGINE]</b><br>";
        echo "--------------------------------------------------<br>";
        echo "<span style='color:#569cd6;'>Regex:</span>  " . htmlspecialchars($regex) . "<br>";
        echo "<span style='color:#569cd6;'>Target:</span> \"" . htmlspecialchars((string)$this->target) . "\"<br>";
        echo "<span style='color:#569cd6;'>Match:</span>  <b style='color:" . ($is_match === 'TRUE' ? '#00ff66' : '#ff3333') . ";'>" . $is_match . "</b><br>";
        echo "--------------------------------------------------";
        echo "</pre>";

        return $this;
    }
public function compile() {
        // PQObjectEngine 객체가 섞여 있어도 내부 값을 안전하게 추출
        $clean_patterns = array_map(function($p) {
            if (is_object($p)) {
                if (method_exists($p, 'value')) return (string)$p->value();
                if (method_exists($p, 'get')) return (string)$p->get();
                if (isset($p->value)) return (string)$p->value;
                if (isset($p->data)) return (string)$p->data;
                if (method_exists($p, '__toString')) return (string)$p;
                return ''; // 문자열 변환 불가 객체 시 빈값 방어
            }
            return (string)$p;
        }, $this->patterns);

        $raw_pattern = implode('', $clean_patterns);
        $flags = implode('', $this->modifiers);
        return '/' . $raw_pattern . '/' . $flags;
    }

/**
     * 하이브리드 매칭 메서드 (무한 루프/메모리 고갈 방지)
     */
    public function match($input = null, $subject = null): bool {
		$unwrap = function($v) {
            if (is_object($v)) {
                if (method_exists($v, 'value')) {
                    $res = $v->value();
                    return is_scalar($res) ? (string)$res : json_encode($res);
                }
                if (method_exists($v, 'attr')) {
                    $res = $v->attr('value');
                    if ($res !== null) return (string)$res;
                }
                return (string)$v;
            }
            return (string)$v;
        };

        // 1) 인자가 2개 들어왔을 때
        if ($input !== null && $subject !== null) {
            return (bool)preg_match($unwrap($input), $unwrap($subject));
        }

        // 2) 인자가 1개 들어왔을 때
        if ($input !== null) {
            $str_input = $unwrap($input);

            if (str_starts_with($str_input, '/') || str_starts_with($str_input, '~')) {
                return (bool)preg_match($str_input, $unwrap($this->target));
            }

            if (!empty($this->patterns)) {
                $this->target = $str_input;
            } else {
                $this->patterns[] = $str_input;
            }
        }

        $regex = $this->compile();
        return (bool)preg_match($regex, $unwrap($this->target));
    }

    /** 단일 매칭 문자열 추출 */
    public function find(string $pattern = ''): string {
        $str_target = (string)$this->target;
        if (empty($str_target)) return "";

        if ($pattern === '') {
            $pattern = $this->compile();
        } elseif (substr($pattern, 0, 1) !== substr($pattern, -1)) {
            $pattern = '/' . preg_quote($pattern, '/') . '/i';
        }

        if (preg_match($pattern, $str_target, $matches)) {
            return $matches[0] ?? "";
        }
        return "";
    }

    public function get($target = null) {
        if ($target !== null) {
            $this->target = (string)$target;
        }

        $regex = $this->compile();
        preg_match_all($regex, (string)$this->target, $matches);

        return $matches[0] ?? [];
    }

    public function replace($replacement, $target = null) {
        if ($target !== null) {
            $this->target = (string)$target;
        }
        $regex = $this->compile();
        return preg_replace($regex, (string)$replacement, (string)$this->target);
    }

    public function split() {
        $regex = $this->compile();
        return preg_split($regex, (string)$this->target);
    }

    public function clean() {
        return $this->replace('');
    }

    public function count() {
        $regex = $this->compile();
        return (int)preg_match_all($regex, (string)$this->target);
    }

    public function remove() {
        return $this->clean();
    }

    public function csv($value, $sep = ',') {
        $sep = preg_quote($sep, '/');
        $this->patterns[] = '(^|' . $sep . ')' . preg_quote((string)$value, '/') . '(' . $sep . '|$)';
        return $this;
    }

    // --- [MAGIC & ENGINE COMPATIBILITY] ---
    public function has(): bool { return $this->match(); }
    public function bool(): bool { return $this->match(); }
    public function __toString(): string { return $this->compile(); }
    public function __invoke($input = null) { return $this->match($input); }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('rgx_pq')) {
    function rgx_pq($target = '') {
        return new Rgx($target);
    }
}

if (!function_exists('rgx')) {
    function rgx($target = '') {
        return rgx_pq($target);
    }
}
?>