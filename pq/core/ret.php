<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/ret.php  
 * COMPONENT : PQ Core Ret (Data Type Return & Conversion)
 * =========================================================
 */

class PQRet implements IteratorAggregate, ArrayAccess, Countable {
    private mixed $data;

    public function __construct(mixed $data = null) {
        $this->data = $data;
    }

    /**
     * Traversal support for foreach($list as $row) / repeat() (IteratorAggregate implementation)
     */
    public function getIterator(): Traversable {
        $arr = $this->array();
        return new ArrayIterator($arr);
    }

    /**
     * Array access support ($list[0], $list['key']) (ArrayAccess implementation)
     */
    public function offsetExists(mixed $offset): bool {
        return isset($this->array()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed {
        $arr = $this->array();
        return $arr[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (is_array($this->data)) {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        if (is_array($this->data)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Element counting support (Countable implementation)
     */
    public function count(): int {
        return count($this->array());
    }

    /**
     * Magic Getter - Handles property access by reference to prevent PHP 8+ Notices
     */
    public function &__get(string $name): mixed {
        $null = null;
        if (is_object($this->data)) {
            if (property_exists($this->data, $name)) {
                $val = &$this->data->{$name};
                return $val;
            }
            return $null;
        }
        if (is_array($this->data)) {
            if (array_key_exists($name, $this->data)) {
                $val = &$this->data[$name];
                return $val;
            }
            return $null;
        }
        return $null;
    }

    /**
     * Magic Setter - Direct property assignment ($ret->prop = $val)
     */
    public function __set(string $name, mixed $value): void {
        if (is_object($this->data)) {
            $this->data->{$name} = $value;
        } elseif (is_array($this->data)) {
            $this->data[$name] = $value;
        } else {
            $this->data = (object)[$name => $value];
        }
    }

    /**
     * Magic Isset check (isset($ret->prop))
     */
    public function __isset(string $name): bool {
        if (is_object($this->data)) {
            return isset($this->data->{$name});
        }
        if (is_array($this->data)) {
            return isset($this->data[$name]);
        }
        return false;
    }

    /**
     * Magic Call guard against invalid method invocations
     */
    public function __call(string $name, array $arguments): mixed {
        return null;
    }

    /**
     * String conversion for view/template auto-output
     */
    public function __toString(): string {
        return is_scalar($this->data) ? (string)$this->data : $this->json();
    }

    /**
     * Data availability inspector
     */
    public function has(): bool {
        if (is_null($this->data)) return false;
        if (is_array($this->data)) return !empty($this->data);
        if (is_object($this->data)) return !empty((array)$this->data);
        return (bool)$this->data;
    }

    /**
     * Data binding entry point
     */
    public function data(mixed $data): self {
        $this->data = $data;
        return $this;
    }

    /**
     * Convert and return data as associative array
     */
    public function array(): array {
        if (is_null($this->data)) return [];

        if (is_string($this->data)) {
            $decoded = json_decode($this->data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (array)$decoded;
            }
        }

        if (is_object($this->data)) {
            return json_decode(json_encode($this->data), true) ?? [];
        }

        return (array)$this->data;
    }

    /**
     * Convert and return data as stdClass object
     */
    public function object(): object {
        if (is_null($this->data)) return (object)[];

        if (is_string($this->data)) {
            $decoded = json_decode($this->data, false);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (object)$decoded;
            }
        }

        return (object)(is_array($this->data) ? json_decode(json_encode($this->data), false) : $this->data);
    }

    /**
     * Convert and return data as JSON string
     */
    public function json(int $flags = JSON_UNESCAPED_UNICODE): string {
        return json_encode($this->data, $flags);
    }

    /**
     * Convert and return data as integer
     */
    public function int(int $default = 0): int {
        return is_numeric($this->data) ? (int)$this->data : $default;
    }

    /**
     * Convert and return data as string
     */
    public function string(string $default = ""): string {
        return is_null($this->data) ? $default : (string)$this->data;
    }

    /**
     * Universal attribute accessor for DB record objects
     */
    public function attr($key = null, ...$args) {
        if ($key === null) {
            return $this->data;
        }

        // Getter
        if (count($args) === 0) {
            if (is_array($this->data)) {
                return $this->data[$key] ?? null;
            }
            if (is_object($this->data)) {
                return $this->data->{$key} ?? null;
            }
            return isset($this->{$key}) ? $this->{$key} : null;
        }

        // Setter
        if (is_array($this->data)) {
            $this->data[$key] = $args[0];
        } elseif (is_object($this->data)) {
            $this->data->{$key} = $args[0];
        } else {
            $this->{$key} = $args[0];
        }
        return $this;
    }
} 

// [ENGINE CORE] Singleton Bridge & DSL Wrapper Functions
if (!function_exists('ret_pq')) {
    function ret_pq(mixed $data = null): PQRet {
        return new PQRet($data);
    }
}

if (!function_exists('ret')) {
    function ret(mixed $data = null): PQRet {
        return ret_pq($data);
    }
}
?>