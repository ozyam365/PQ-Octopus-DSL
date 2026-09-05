<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/util.php 
 * COMPONENT : PQ Engine UI Helper Matrix 
 * =========================================================
 */
class PQ_Util {
    private $val;
    public function __construct($v) { $this->val = $v; }

    // 1. 값 존재 여부 (가독성: if(util(@v).filled()))
    public function filled() { 
        return !empty(trim((string)$this->val)); 
    }

    // 2. 키워드 강조 (검색결과 등)
	public function mark($w) {
		if ($w === '' || $this->val === '') return $this;

		$this->val = preg_replace(
			'/(' . preg_quote($w, '/') . ')/iu',
			"<mark class='bg-warning'>$1</mark>",
			(string)$this->val
		);

		return $this;
	}

    // 3. 조건부 색상 지정 (상태 표시)
    public function color($c, $match) {
        if ($this->val == $match) {
            $this->val = "<span style='color:$c; font-weight:bold;'>{$this->val}</span>";
        }
        return $this;
    }

    // 4. 아이콘 결합
    public function icon($type) {
        if (empty($this->val)) return $this;
        $cls = ($type == "image") ? "bi-image" : (($type == "file") ? "bi-file-earmark" : $type);
        $this->val .= " <i class='bi $cls text-primary'></i>";
        return $this;
    }

    // 5. 화폐 포맷팅 (View 전용)
    public function money() { 
        if (is_numeric($this->val)) {
            $this->val = number_format((float)$this->val); 
        }
        return $this; 
    }

    public function __toString() { return (string)$this->val; }
}
class PQNull{
    private $value;
    function __construct($value){
        $this->value = $value;
    }
	function init($default){
		if ($this->value === null) {
			$this->value = $default;
		}
		return $this->value;
	}
}
if (!function_exists('isnull')) {
	function isnull($value){
		return new PQNull($value);
	}
}
/**
 * 헬퍼 함수
 */
if (!function_exists('util')) {
    function util($v) { return new PQ_Util($v); }
}

/**
 * 값이 없으면 스마트 기본값 반환 (isset & type smart default)
 * - $default 인자가 없으면(null): target의 타입에 따라 0, [], "", false 등을 자동 선택
 */
function val(&$target = null, $default = null) {
    // 1. target에 정상적인 값이 존재하는 경우
    if (isset($target) && $target !== null) {
        return $target;
    }

    // 2. 개발자가 두 번째 인자로 $default를 직접 지정한 경우 우선 적용
    if ($default !== null) {
        return $default;
    }

    // 3. $default가 생략되었을 때: $target의 타입/힌트에 맞춰 스마트 기본값 추론
    if (is_int($target) || is_float($target)) {
        return 0;
    }
    if (is_array($target)) {
        return [];
    }
    if (is_bool($target)) {
        return false;
    }

    // 4. 그 외 기본 문자열
    return '';
}

if (!function_exists('blank')) {
    function blank($v) {
        // 1. null 인 경우
        if ($v === null) { return true; }
        
        // 2. 배열(Array)인 경우 : 비어있으면 true, 요소가 1개 이상 있으면 false
        if (is_array($v)) { return empty($v); }
        
        // 3. 객체(Object)인 경우
        if (is_object($v)) { return false; }
        
        // 4. 일반 문자열/숫자인 경우
        return trim((string)$v) === '';
    }
}
if (!function_exists('has')) {
    function has($v) {
        return !blank($v);
    }
}
/**
 * 타임스탬프 또는 날짜 문자열을 Unix 타임스탬프로 변환
 * @param mixed $date (지원: 타임스탬프 정수, 'YYYY-MM-DD HH:II:SS' 문자열)
 * @return int Unix Timestamp
 */
function date2time($date) {
    // 1. 이미 숫자(타임스탬프)라면 그대로 반환
    if (is_numeric($date)) {
        return (int)$date;
    }

    // 2. 만약 비어있다면 현재 시간 반환
    if (empty($date)) {
        return time();
    }

    // 3. 기존 로직 보존 (공백/하이픈/콜론 구분 방식)
    if (strpos($date, ' ') !== false) {
        $arg = explode(' ', $date);
        $ymd = explode('-', $arg[0]);
        $hms = explode(':', $arg[1]);
        
        // 데이터가 정확히 3개씩 쪼개지는지 검증 후 mktime
        if (count($ymd) === 3 && count($hms) === 3) {
            return mktime((int)$hms[0], (int)$hms[1], (int)$hms[2], (int)$ymd[1], (int)$ymd[2], (int)$ymd[0]);
        }
    }

    // 4. fallback: 그 외의 날짜 문자열(예: 2026-07-17 등)은 PHP 내장 함수로 안전하게 처리
    return strtotime($date);
}
?>