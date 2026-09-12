<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/form.php  
 * COMPONENT : PQ Pure Raw Form Matrix & Auto-Fallback Type Cast
 * =========================================================
 */

class FormValue {
    private $value;
    private $default = null;

    public function __construct($val) { 
        $this->value = $val; 
    }

    public function __invoke() {
        return $this->value();
    }

    // --- [1. DATA SANITIZATION PIPELINE] ---
    public function trim() {
        if (is_string($this->value)) {
            $this->value = trim($this->value);
        }
        return $this;
    }

    public function unslash() {
        if ($this->value !== null) {
            $this->value = stripslashes((string)$this->value);
        }
        return $this;
    }

    public function strip() {
        if ($this->value !== null) {
            $this->value = strip_tags((string)$this->value);
        }
        return $this;
    }

    public function special() {
        if ($this->value !== null) {
            $this->value = htmlspecialchars((string)$this->value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        return $this;
    }

    // --- [2. VALIDATION & ERROR HANDLING] ---
    public function error($msg = "Invalid request.") {
        if ($this->empty()) {
            if (class_exists('http') && method_exists('http', 'msg')) {
                http::msg($msg)->back();
            } else {
                echo "<script>alert('" . addslashes($msg) . "'); history.back();</script>";
            }
            exit;
        }
        return $this;
    }

    // --- [3. TYPE CASTING & FALLBACK EVALUATION] ---
    public function val($default = "") {
        $this->default = $default;
        if ($this->empty()) {
            $this->value = $default;
        } else {
            // Instant type casting based on default value type
            if (is_int($default))    $this->value = (int)$this->value;
            if (is_float($default))  $this->value = (float)$this->value;
            if (is_bool($default))   $this->value = (bool)$this->value;
            if (is_string($default)) $this->value = (string)$this->value;
        }
        return $this;
    }

    public function empty() {
        if ($this->value === null) return true;
        if (is_string($this->value)) return trim($this->value) === '';
        if (is_array($this->value)) return count($this->value) === 0;
        return false;
    }

    // Deferral Value Evaluator
    public function value() {
        if ($this->empty()) {
            return $this->default ?? "";
        }

        if ($this->default !== null) {
            if (is_int($this->default)) return (int)$this->value;
            if (is_float($this->default)) return (float)$this->value;
            if (is_bool($this->default)) return (bool)$this->value;
            if (is_string($this->default)) return (string)$this->value;
        }

        return $this->type();
    }

    // Explicit Type Casting
    public function string() { return (string)$this->value(); }
    public function int() { return (int)$this->value(); }
    public function float() { return (float)$this->value(); }
    public function bool() {
        $v = $this->value();
        if (is_bool($v)) return $v;
        return filter_var(
            $v,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? false;
    }

    // Scalar Auto Detection Type Casting
    public function type() {
        if ($this->empty()) return $this->default;

        $v = trim((string)$this->value);

        if (in_array(strtolower($v), ['true', 'false'], true)) {
            return strtolower($v) === 'true';
        }

        if (preg_match('/^-?\d+$/', $v)) {
            return (int)$v;
        }

        if (is_numeric($v)) {
            return (float)$v;
        }

        return (string)$this->value;
    }

    // XSS Prevention & HTML Cleanser
    public function xss($mode = "on") {
        if ($this->value !== null) {
            if ($mode === "on" || $mode === true) {
                if (is_array($this->value)) {
                    array_walk_recursive($this->value, function (&$v) {
                        $v = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    });
                } else {
                    $this->value = htmlspecialchars((string)$this->value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            } elseif ($mode === "clean" || $mode === "safe") {
                if (function_exists('html_safe')) {
                    $this->value = html_safe((string)$this->value);
                }
            }
        }
        return $this;
    }

    public function __toString() { 
        return (string)$this->value(); 
    }
}

class FormMaker {
    private $data = [];

    public function __construct() {
        $this->data = array_merge($_GET, $_POST); 
    }

    public function set($k, $v = null) {
        if (is_array($k)) {
            foreach ($k as $key => $val) {
                $this->data[$key] = $val;
                $_GET[$key] = $val;
                $_REQUEST[$key] = $val;
            }
        } else {
            $this->data[$k] = $v;
            $_GET[$k] = $v;
            $_REQUEST[$k] = $v;
        }
        return $this;
    }

    public function get($key = null) {
        if ($key === null) {
            return $this->all();
        }
        
        $data = $this->data[$key] ?? null;
        return new FormValue($data);
    }

    public function all($type = null) {
        if ($type === "arr" || $type === "array") {
            return (array)$this->data;
        }
        if ($type === "obj" || $type === "object") {
            return (object)$this->data;
        }

        return ret($this->data);
    }
}

// [ENGINE CORE] Global Sanitization Helper
if (!function_exists("html_safe")) {
    function html_safe($html) {
        $html = preg_replace('/<(script|iframe|object|embed|style|link)[^>]*?>.*?<\/\1>/si', '', $html);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        return $html;
    }
}

// [ENGINE CORE] Singleton Bridge & DSL Wrappers
if (!function_exists("form")) {
    function form(){
        static $f = null;
        if (!$f) $f = new FormMaker();
        return $f;
    }
}

if (!function_exists("form_pq")) {
    function form_pq(){
        return form();
    }
}
?>