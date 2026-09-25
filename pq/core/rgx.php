<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.8)
 * FILENAME  : /pq/core/rgx.php
 * COMPONENT : PQ Fluent Regex Builder Core Engine
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

    public function __construct($target = ''){
        $this->target = $target;
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
        $this->patterns[] = preg_quote($str, '/');
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
            $this->patterns[] = preg_quote($char, '/');
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
    public function compile() {
        $raw_pattern = implode('', $this->patterns);
        $flags = implode('', $this->modifiers);
        return '/' . $raw_pattern . '/' . $flags;
    }

    public function dump() {
        $regex = $this->compile();
        $is_match = $this->match() ? 'TRUE' : 'FALSE';
        
        echo "<pre style='background:#1e1e1e; color:#00ff66; padding:15px; border-radius:8px; font-family:monospace; line-height:1.5; border:1px solid #333;'>";
        echo "<b style='color:#ff007f;'>[PQ RGX DEBUG ENGINE]</b><br>";
        echo "--------------------------------------------------<br>";
        echo "<span style='color:#569cd6;'>Regex:</span>  " . htmlspecialchars($regex) . "<br>";
        echo "<span style='color:#569cd6;'>Target:</span> \"" . htmlspecialchars($this->target) . "\"<br>";
        echo "<span style='color:#569cd6;'>Match:</span>  <b style='color:" . ($is_match === 'TRUE' ? '#00ff66' : '#ff3333') . ";'>" . $is_match . "</b><br>";
        echo "--------------------------------------------------";
        echo "</pre>";

        return $this;
    }

    /** 
     * 하이브리드 매칭 메서드 (통합 수정본)
     * 지원 패턴:
     * 1) rgx($target)->symbol("\n")->match()
     * 2) rgx($target)->match('/pattern/')
     * 3) rgx('/pattern/')->match($target)
     */
    public function match($input = null): bool {
        if ($input !== null) {
            // 인자로 들어온 게 정규식 패턴('/.../' 또는 '~...~')인 경우
            if (is_string($input) && (str_starts_with($input, '/') || str_starts_with($input, '~'))) {
                return (bool)preg_match($input, (string)$this->target);
            }
            // 패턴이 아니라 대상 문자열일 경우 $target으로 갱신
            $this->target = $input;
        }

        $regex = $this->compile();
        return (bool)preg_match($regex, (string)$this->target);
    }

    /** 단일 매칭 문자열 추출 */
    public function find(string $pattern = ''): string {
        if (empty($this->target)) return "";
        
        if ($pattern === '') {
            $pattern = $this->compile();
        } elseif (substr($pattern, 0, 1) !== substr($pattern, -1)) {
            $pattern = '/' . preg_quote($pattern, '/') . '/i';
        }

        if (preg_match($pattern, $this->target, $matches)) {
            return $matches[0] ?? "";
        }
        return "";
    }

    public function get($target = null){
        if ($target !== null) {
            $this->target = $target;
        }

        $regex = $this->compile();
        preg_match_all($regex, $this->target, $matches);

        return $matches[0] ?? [];
    }

    public function replace($replacement, $target = null) {
        if ($target !== null) {
            $this->target = $target;
        }
        $regex = $this->compile();
        return preg_replace($regex, $replacement, $this->target);
    }

    public function split() {
        $regex = $this->compile();
        return preg_split($regex, $this->target);
    }

    public function clean() {
        return $this->replace('');
    }

    public function count() {
        $regex = $this->compile();
        return (int)preg_match_all($regex, $this->target);
    }

    public function remove() {
        return $this->clean();
    }

    public function csv($value, $sep = ','){
        $sep = preg_quote($sep, '/');
        $this->patterns[] = '(^|' . $sep . ')' . preg_quote($value, '/') . '(' . $sep . '|$)';
        return $this;
    }
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