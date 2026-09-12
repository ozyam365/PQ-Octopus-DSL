<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/pin.php  
 * COMPONENT : PQ Core Pin (Variable Binding & Data Processing)
 * =========================================================
 */

class PQPin {
    /**
     * Reference list of bound variables
     * @var array
     */
    private array $refs = [];

    /**
     * [Step 1] Batch bind memory reference addresses of target variables
     * 
     * @param array $vars Referenced variable array
     * @return self
     */
    public function bind(array &$vars): self {
        $this->refs = [];
        foreach ($vars as &$v) {
            $this->refs[] = &$v;
        }
        return $this;
    }

    /**
     * [Action] .val(): Batch assign value to all bound variables
     * 
     * @param mixed $value Value to assign
     * @return mixed
     */
    public function val(mixed $value): mixed {
        foreach ($this->refs as &$var) {
            $var = $value; // Force reset to $value regardless of prior state
        }
        return $value;
    }

    /**
     * [Action] .int(): Batch cast all bound variables to integer
     * 
     * @param int|null $default Fallback value on failure (null preserves existing)
     * @return self
     */
    public function int(?int $default = 0): self {
        foreach ($this->refs as &$var) {
            if (is_numeric($var)) {
                $var = (int)$var;
            } elseif ($default !== null) {
                $var = $default;
            }
        }
        return $this;
    }

    /**
     * [Action] .string(): Force convert all bound variables to string
     * 
     * @param string $default Fallback value
     * @return self
     */
    public function string(string $default = ""): self {
        foreach ($this->refs as &$var) {
            if (is_null($var)) {
                $var = $default;
            } else {
                $var = (string)$var;
            }
        }
        return $this;
    }

    /**
     * [Action] .bool(): Convert all bound variables to boolean type
     * 
     * @return self
     */
    public function bool(): self {
        foreach ($this->refs as &$var) {
            $var = (bool)$var;
        }
        return $this;
    }

    /**
     * [Action] .array(): Batch convert all bound variables to array type
     * 
     * @return self
     */
    public function array(): self {
        foreach ($this->refs as &$var) {
            if (!is_array($var)) {
                $var = (empty($var) && $var !== 0 && $var !== '0') ? [] : (array)$var;
            }
        }
        return $this;
    }

    /**
     * [Action] .object(): Batch convert all bound variables to object (stdClass) type
     * 
     * @return self
     */
    public function object(): self {
        foreach ($this->refs as &$var) {
            if (!is_object($var)) {
                $var = (object)(is_array($var) ? $var : []);
            }
        }
        return $this;
    }

    /**
     * [Action] .clean(): Trim whitespace on string variables
     * 
     * @return self
     */
    public function clean(): self {
        foreach ($this->refs as &$var) {
            if (is_string($var)) {
                $var = trim($var);
            }
        }
        return $this;
    }

    /**
     * [Action] .null(): Convert empty strings ("") or empty arrays ([]) to null
     * 
     * @return self
     */
    public function null(): self {
        foreach ($this->refs as &$var) {
            if ($var === '' || $var === []) {
                $var = null;
            }
        }
        return $this;
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('pin_pq')) {
    function pin_pq(&...$vars): PQPin {
        $inst = new PQPin();
        return $inst->bind($vars);
    }
}

if (!function_exists('pin')) {
    function pin(&...$vars): PQPin {
        return pin_pq(...$vars);
    }
}
?>