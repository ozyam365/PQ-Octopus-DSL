<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/util.php 
 * COMPONENT : PQ Engine UI Helper & Utility Matrix Engine
 * =========================================================
 */

class PQ_Util {
    private $val;
    public function __construct($v) { $this->val = $v; }

    // Check if value is non-empty
    public function filled() { 
        return !empty(trim((string)$this->val)); 
    }

    // Keyword highlighting for search views
    public function mark($w) {
        if ($w === '' || $this->val === '') return $this;

        $this->val = preg_replace(
            '/(' . preg_quote($w, '/') . ')/iu',
            "<mark class='bg-warning'>$1</mark>",
            (string)$this->val
        );

        return $this;
    }

    // Conditional styling wrapper
    public function color($c, $match) {
        if ($this->val == $match) {
            $this->val = "<span style='color:$c; font-weight:bold;'>{$this->val}</span>";
        }
        return $this;
    }

    // Icon appender
    public function icon($type) {
        if (empty($this->val)) return $this;
        $cls = ($type == "image") ? "bi-image" : (($type == "file") ? "bi-file-earmark" : $type);
        $this->val .= " <i class='bi $cls text-primary'></i>";
        return $this;
    }

    // Currency formatting
    public function money() { 
        if (is_numeric($this->val)) {
            $this->val = number_format((float)$this->val); 
        }
        return $this; 
    }

    public function __toString() { return (string)$this->val; }
}

class PQNull {
    private $value;
    function __construct($value) {
        $this->value = $value;
    }

    function init($default) {
        if ($this->value === null) {
            $this->value = $default;
        }
        return $this->value;
    }
}

// --- [ENGINE CORE] HELPER & GLOBAL UTILITIES ---

if (!function_exists('isnull')) {
    function isnull($value) {
        return new PQNull($value);
    }
}

if (!function_exists('util_pq')) {
    function util_pq($v) {
        return new PQ_Util($v);
    }
}

if (!function_exists('util')) {
    function util($v) {
        return util_pq($v);
    }
}

/**
 * Returns smart default values when input is unset or null
 */
if (!function_exists('val')) {
    function val(&$target = null, $default = null) {
        if (isset($target) && $target !== null) {
            return $target;
        }

        if ($default !== null) {
            return $default;
        }

        if (is_int($target) || is_float($target)) {
            return 0;
        }
        if (is_array($target)) {
            return [];
        }
        if (is_bool($target)) {
            return false;
        }

        return '';
    }
}

if (!function_exists('blank')) {
    function blank($v) {
        if ($v === null) { return true; }
        if (is_array($v)) { return empty($v); }
        if (is_object($v)) { return false; }
        return trim((string)$v) === '';
    }
}

if (!function_exists('has')) {
    function has($v) {
        return !blank($v);
    }
}

/**
 * Convert timestamp or date string into Unix Timestamp
 */
if (!function_exists('date2time')) {
    function date2time($date) {
        if (is_numeric($date)) {
            return (int)$date;
        }

        if (empty($date)) {
            return time();
        }

        if (strpos($date, ' ') !== false) {
            $arg = explode(' ', $date);
            $ymd = explode('-', $arg[0]);
            $hms = explode(':', $arg[1]);
            
            if (count($ymd) === 3 && count($hms) === 3) {
                return mktime((int)$hms[0], (int)$hms[1], (int)$hms[2], (int)$ymd[1], (int)$ymd[2], (int)$ymd[0]);
            }
        }

        return strtotime($date);
    }
}
?>