<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
  * FILENAME : /pq/core/rgx.php
 * COMPONENT : Fluent Regex Builder 
 * =========================================================
 */

class Rgx {
    protected $target;       // 대상 텍스트
    protected $patterns = []; // 조립할 패턴 조각들
    protected $is_not = false; // not() 적용 여부
    protected $modifiers = ['u']; // 기본 UTF-8 플래그 장착
    
    // 패턴 프리셋
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

    // 타입별 원시 매핑
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

    // ==========================================
    // 1. Builder Methods
    // ==========================================
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

    public function type($names) {
        //쉼표 분리 멀티 타입 지원 (예: type("eng,int"))
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
                $this->is_not = false; // 토글 리셋
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

    // ==========================================
    // 2. Option Methods
    // ==========================================
    public function len($min, $max = null) {
        $last_idx = count($this->patterns) - 1;
        if ($last_idx >= 0) {
            $target = $this->patterns[$last_idx];
            
            // 그룹핑 격리 안전 가드
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

    // 명확한 range 별칭(Alias)으로 전환
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

    // ==========================================
    // 3. Execute & Debug Methods
    // ==========================================
    public function compile() {
        $raw_pattern = implode('', $this->patterns);
        $flags = implode('', $this->modifiers);
        return '/' . $raw_pattern . '/' . $flags;
    }

    // 디버깅 덤프 패널 출력
    public function dump() {
        $regex = $this->compile();
        $is_match = $this->match() ? 'TRUE' : 'FALSE';
        
        echo "<pre style='background:#1e1e1e; color:#00ff66; padding:15px; border-radius:8px; font-family:monospace; line-height:1.5; border:1px solid #333;'>";
        echo "<b style='color:#ff007f;'>[PQ 8.2 RGX DEBUG GERMAN]</b><br>";
        echo "--------------------------------------------------<br>";
        echo "<span style='color:#569cd6;'>Regex:</span>  " . htmlspecialchars($regex) . "<br>";
        echo "<span style='color:#569cd6;'>Target:</span> \"" . htmlspecialchars($this->target) . "\"<br>";
        echo "<span style='color:#569cd6;'>Match:</span>  <b style='color:" . ($is_match === 'TRUE' ? '#00ff66' : '#ff3333') . ";'>" . $is_match . "</b><br>";
        echo "--------------------------------------------------";
        echo "</pre>";

        return $this; // 체이닝 유지를 위해 객체 반환
    }
	public function match($target = null){
		if ($target !== null) {
			$this->target = $target;
		}

		$regex = $this->compile();
		return (bool)preg_match($regex, $this->target);
	}
	public function get($target = null){
		if ($target !== null) {
			$this->target = $target;
		}

		$regex = $this->compile();
		preg_match_all($regex, $this->target, $matches);

		return $matches[0] ?? [];
	}

    public function replace($replacement) {
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

// 글로벌 헬퍼 함수
function rgx($target) {
    return new Rgx($target);
}