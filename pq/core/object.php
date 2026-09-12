<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/object.php
 * COMPONENT : PQ Engine Scoper Kernel (v1.4.0 - Hybrid Scoper & Data Container)
 * =========================================================
 */

class PQEngine {
    private static $scope_stack = [];
    private static $current_context = null;
    private static $registry = [];

    /**
     * 1. Start top-level object scope
     * @param string $root_obj_name Root object name
     * @param bool $clear_registry Resets registry specifically for current object if true
     */
    public static function start_object_scope($root_obj_name, $clear_registry = false) {
        self::$scope_stack = []; // Reset scope stack
        
        if ($clear_registry) {
            self::$registry = []; // Optional registry initialization
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
     * 2. Enter child hierarchy scope
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
     * 3. Safely terminate current scope and restore parent context
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
     * Component Registration Pipeline
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
 * PQ Execution Object supporting dynamic chaining, ArrayAccess, and jQuery-style .attr() accessors
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
     * jQuery-style .attr() method (prevents null storage overriding via variadic arguments)
     */
    public function attr($key = null, ...$args) {
        // 1. Return entire data payload
        if ($key === null) {
            return $this->data;
        }

        // 2. Getter: Reading value when single argument $key is supplied
        if (count($args) === 0) {
            return $this->data[$key] ?? null;
        }

        // 3. Setter: Writing value when second argument is passed
        $this->data[$key] = $args[0];
        return $this;
    }

    /**
     * Child scope traversal and method chaining
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
     * Register component specification and continue chaining
     */
    public function have($name, $index = null) {
        PQEngine::have($name, $index);
        return $this;
    }

    /**
     * Scope restoration method (.end())
     */
    public function end() {
        $parent_path = PQEngine::end_scope();
        $this->current_path = $parent_path ?? '';
        return $this;
    }

    /**
     * 1. __get: Prioritizes internal $data property, auto-expands scope otherwise
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
     * 2. __set: Direct dynamic property assignment ($rs->name = "val")
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * 3. ArrayAccess: Array syntax ($rs['key']) and index scope support
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
     * 4. __call: Protected method chaining
     */
    public function __call($method, $args) {
        $context = PQEngine::get_current_context();
        
        // Guard against null context execution
        $target_key = $context ? $context . '.' . $method : $method;

        if (class_exists('Trace')) {
            Trace::add("ACTION", "Execute -> {$target_key}()");
        }
        return $this;
    }

    /**
     * Extract full payload data as a Collection object
     */
    public function all() {
        return ret($this->data);
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & HELPER WRAPPERS ---

if (!function_exists('have_pq')) {
    function have_pq($root_name, $clear_registry = false) {
        PQEngine::start_object_scope($root_name, $clear_registry);
        return new PQObjectEngine($root_name);
    }
}

if (!function_exists('have')) {
    function have($root_name, $clear_registry = false) {
        return have_pq($root_name, $clear_registry);
    }
}

if (!function_exists('obj_pq')) {
    function obj_pq($data = []) {
        return new PQObjectEngine('', $data);
    }
}

if (!function_exists('obj')) {
    function obj($data = []) {
        return obj_pq($data);
    }
}

if (!function_exists('show')) {
    function show($v){
        echo is_array($v) || is_object($v) ? "<pre>" . print_r($v, true) . "</pre>" : $v;
    }
}

if (!function_exists('type')) {
    function type($v) {
        if ($v === null) return "✨ [NULL] Data Asset Lost";
        if (is_bool($v)) return "✨ [BOOLEAN] Logical (" . ($v ? 'TRUE' : 'FALSE') . ")";
        if (is_int($v) || is_float($v)) return "✨ [NUMBER] Numeric Data";
        if (is_string($v)) return "✨ [STRING] Primitive String";
        if (is_array($v)) {
            return (count($v) === count($v, COUNT_RECURSIVE)) ? "✨ [ARRAY_ROW] Single Row Record" : "✨ [ARRAY_LIST] Multidimensional List";
        }
        return is_object($v) ? "✨ [OBJECT] Class Instance (" . get_class($v) . ")" : "✨ [UNKNOWN]";
    }
}
?>