<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.5)
 * FILENAME : /pq/core/object.php
 * COMPONENT : PQ Engine Scoper Kernel (v1.4.0 - Hybrid Scoper & Data Container)
 * =========================================================
 */

class PQEngine {
    private static $scope_stack = [];
    private static $current_context = null;
    private static $registry = [];

    /**
     * 1. 최상위 객체 스코프 진입
     * @param string $root_obj_name 루트 객체명
     * @param bool $clear_registry true일 경우 레지스트리를 현재 객체 전용으로 리셋
     */
    public static function start_object_scope($root_obj_name, $clear_registry = false) {
        self::$scope_stack = []; // 스코프 스택 리셋
        
        if ($clear_registry) {
            self::$registry = []; // 🎯 선택적 레지스트리 초기화
        }

        self::$scope_stack[] = [
            'name'      => $root_obj_name,
            'index'     => null,
            'full_path' => $root_obj_name,
            'parent'    => null
        ];
        self::$current_context = $root_obj_name;
        
        if (class_exists('Trace')) Trace::add("SCOPE", "Root -> {$root_obj_name}");
    }

    /**
     * 2. 자식 계층 스코프 진입
     */
    public static function enter_child_scope($parent_path, $child_name, $index = null) {
        $current_path = $parent_path ? $parent_path . '.' . $child_name : $child_name;
        if ($index !== null && $index !== '') {
            $current_path .= '[' . $index . ']';
        }

        self::$scope_stack[] = [
            'name'      => $child_name,
            'index'     => $index,
            'full_path' => $current_path,
            'parent'    => $parent_path
        ];
        
        self::$current_context = $current_path;
        if (class_exists('Trace')) Trace::add("SCOPE", "Child -> {$current_path}");
    }

    /**
     * 3. 스코프 안전 종결 (상위 스코프로 복원)
     */
    public static function end_scope() {
        $popped = array_pop(self::$scope_stack);
        
        if (!empty(self::$scope_stack)) {
            self::$current_context = end(self::$scope_stack)['full_path'];
        } else {
            self::$current_context = null;
        }

        if (class_exists('Trace') && $popped) {
            Trace::add("SCOPE", "Closed -> {$popped['full_path']}");
        }
        return self::$current_context;
    }

    /**
     * [컴포넌트 바인딩] have 명세 등록
     */
    public static function register_component($name, $index = null) {
        $ctx = self::get_current_context();
        $key = ($ctx ? $ctx . '.' : '') . $name . ($index !== null && $index !== '' ? '[' . $index . ']' : '');
        self::$registry[$key] = true;

        if (class_exists('Trace')) {
            Trace::add("COMPONENT", "Register -> {$key}");
        }
        return $key;
    }

    public static function have($name, $index = null) {
        return self::register_component($name, $index);
    }

    public static function get_current_context() {
        if (empty(self::$scope_stack)) {
            return null;
        }
        return end(self::$scope_stack)['full_path'];
    }

    public static function get_parent_path() {
        if (empty(self::$scope_stack)) {
            return null;
        }
        return end(self::$scope_stack)['parent'];
    }

    public static function get_root_path() {
        return !empty(self::$scope_stack) ? self::$scope_stack[0]['full_path'] : null;
    }

    public static function get_current_scope() {
        return empty(self::$scope_stack) ? null : end(self::$scope_stack);
    }

    public static function get_registry() {
        return self::$registry;
    }

    public static function clear_registry() {
        self::$registry = [];
    }
}

/**
 * 동적 체이닝, ArrayAccess 및 jQuery 스타일 .attr() 접근자를 지원하는 PQ 실행 객체
 */
#[AllowDynamicProperties]
class PQObjectEngine implements ArrayAccess {
    protected $current_path = '';
    protected $data = [];

    public function __construct($path = '', $data = []) {
        $this->current_path = $path;
        if (is_array($data) || is_object($data)) {
            $this->data = (array)$data;
        }
    }

	/**
	 * 🚀 jQuery 스타일 .attr() 메서드 (가변 인자로 null 저장 오폭 방지)
	 */
	public function attr($key = null, ...$args) {
		// 1. 전체 데이터 반환
		if ($key === null) {
			return $this->data;
		}

		// 2. Getter: 인자가 $key 하나뿐인 경우 (값 읽기)
		if (count($args) === 0) {
			return $this->data[$key] ?? null;
		}

		// 3. Setter: 두 번째 인자가 전달된 경우 (null 값 저장 포함)
		$this->data[$key] = $args[0];
		return $this;
	}

    /**
     * 자식 스코프 탐색 및 체이닝
     */
    public function getChild($name, $index = null) {
        PQEngine::enter_child_scope(
            PQEngine::get_current_context(),
            $name,
            $index
        );
        $this->current_path = PQEngine::get_current_context();
        return $this;
    }

    /**
     * have 컴포넌트 등록 및 체이닝
     */
    public function have($name, $index = null) {
        PQEngine::have($name, $index);
        return $this;
    }

    /**
     * 상위 스코프로 복원하는 탈출 메서드 (.end())
     */
    public function end() {
        $parent_path = PQEngine::end_scope();
        $this->current_path = $parent_path ?? '';
        return $this;
    }

    /**
     * 1. __get: 내부 $data 우선 참조 후, 없을 경우 스코프 자동 확장
     */
    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        PQEngine::enter_child_scope(PQEngine::get_current_context(), $name);
        $this->current_path = PQEngine::get_current_context();
        return $this;
    }

    /**
     * 2. __set: 동적 속성 직접 대입 지원 (#rs.name = "val")
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * 3. ArrayAccess: 배열 표기(#rs['key']) 및 인덱스 스코프 지원
     */
    public function offsetGet(mixed $offset): mixed {
        if (array_key_exists($offset, $this->data)) {
            return $this->data[$offset];
        }
        
        $scope = PQEngine::get_current_scope();
        if ($scope) {
            PQEngine::end_scope();
            $new_name = $scope['name'];
            $new_index = ($scope['index'] !== null && $scope['index'] !== '') 
                ? $scope['index'] . '][' . $offset 
                : $offset;
            
            PQEngine::enter_child_scope($scope['parent'], $new_name, $new_index);
        }
        $this->current_path = PQEngine::get_current_context();
        return $this;
    }

    public function offsetExists(mixed $offset): bool { return isset($this->data[$offset]); }
    public function offsetSet(mixed $offset, mixed $value): void { $this->data[$offset] = $value; }
    public function offsetUnset(mixed $offset): void { unset($this->data[$offset]); }

    /**
     * 4. __call: 방어 로직이 강화된 메서드 체이닝
     */
    public function __call($method, $args) {
        $context = PQEngine::get_current_context();
        
        // null context일 경우 예외성 오작동 방어
        $target_key = $context ? $context . '.' . $method : $method;

        if (class_exists('Trace')) {
            Trace::add("ACTION", "Execute -> {$target_key}()");
        }
        return $this;
    }

    /**
     * 전체 데이터를 배열로 추출
     */
    public function all() {
        return ret($this->data);
    }
}

/**
 * 헬퍼 함수
 */

// 단문 인라인 체이닝 전용 헬퍼 함수
if (!function_exists('have')) {
    function have($root_name, $clear_registry = false) {
        PQEngine::start_object_scope($root_name, $clear_registry);
        return new PQObjectEngine($root_name);
    }
}

// 기존 체이닝 엔진 헬퍼 함수
if (!function_exists('obj')) {
    function obj($data = []) {
        return new PQObjectEngine('', $data);
    }
}

if (!function_exists('show')) {
    function show($v){
        echo is_array($v) || is_object($v) ? "<pre>" . print_r($v, true) . "</pre>" : $v;
    }
}

if (!function_exists('type')) {
    function type($v) {
        if ($v === null) return "✨ [NULL] 데이터 자산 유실";
        if (is_bool($v)) return "✨ [BOOLEAN] 논리형 (" . ($v ? 'TRUE' : 'FALSE') . ")";
        if (is_int($v) || is_float($v)) return "✨ [NUMBER] 숫자 데이터";
        if (is_string($v)) return "✨ [STRING] 원시 문자열";
        if (is_array($v)) {
            return (count($v) === count($v, COUNT_RECURSIVE)) ? "✨ [ARRAY_ROW] 단일 행 레코드" : "✨ [ARRAY_LIST] 다차원 목록";
        }
        return is_object($v) ? "✨ [OBJECT] 클래스 인스턴스 (" . get_class($v) . ")" : "✨ [UNKNOWN]";
    }
}
?>