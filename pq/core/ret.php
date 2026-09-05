<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.5)
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
     * 🚀 [핵심] foreach($list as $row) / repeat() 순회 지원 (IteratorAggregate 구현)
     * - db.list() 결과를 .array() 없이 곧바로 repeat() / foreach 돌릴 수 있습니다.
     */
    public function getIterator(): Traversable {
        $arr = $this->array();
        return new ArrayIterator($arr);
    }

    /**
     * 🚀 [핵심] $list[0], $list['key'] 배열식 접근 지원 (ArrayAccess 구현)
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
     * 🚀 [핵심] count(#list) 대응 지원 (Countable 구현)
     */
    public function count(): int {
        return count($this->array());
    }

    /**
     * 🚀 [매직 메서드] 모든 리턴 경로를 명확한 변수($val) 참조로 처리하여 PHP 8+ Notice 완전 차단
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
     * 🚀 [매직 메서드] $ret->prop = $val 속성 직접 할당
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
     * 🚀 [매직 메서드] isset($ret->prop) 체크
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
     * 🚀 [매직 메서드] 메서드 오폭 방어 (__call)
     */
    public function __call(string $name, array $arguments): mixed {
        return null;
    }

    /**
     * 🚀 [매직 메서드] 뷰/템플릿 자동 문자열 출력 대응 (__toString)
     */
    public function __toString(): string {
        return is_scalar($this->data) ? (string)$this->data : $this->json();
    }
    /**
     * 🚀 [데이터 체크] .has(): 데이터 존재 여부 검사
     */
    public function has(): bool {
        if (is_null($this->data)) return false;
        if (is_array($this->data)) return !empty($this->data);
        if (is_object($this->data)) return !empty((array)$this->data);
        return (bool)$this->data;
    }

    /**
     * [Step 1] 데이터 바인딩 진입점
     */
    public function data(mixed $data): self {
        $this->data = $data;
        return $this;
    }

    /**
     * [Action] .array(): 연관 배열 반환
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
     * [Action] .object(): 객체(stdClass) 반환
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
     * [Action] .json(): JSON 문자열 반환
     */
    public function json(int $flags = JSON_UNESCAPED_UNICODE): string {
        return json_encode($this->data, $flags);
    }

    /**
     * [Action] .int(): 정수형 반환
     */
    public function int(int $default = 0): int {
        return is_numeric($this->data) ? (int)$this->data : $default;
    }

    /**
     * [Action] .string(): 문자열 반환
     */
    public function string(string $default = ""): string {
        return is_null($this->data) ? $default : (string)$this->data;
    }
	/**
	 * 🚀 DB 레코드 객체용 .attr() 데이터 접근자 (최종 정돈)
	 */
	public function attr($key = null, ...$args) {
		// 1. 인자가 없으면 raw 데이터 자체를 반환 (전체 추출은 .all()이나 .array() 권장)
		if ($key === null) {
			return $this->data;
		}

		// 2. Getter (값 읽기)
		if (count($args) === 0) {
			if (is_array($this->data)) {
				return $this->data[$key] ?? null;
			}
			if (is_object($this->data)) {
				return $this->data->{$key} ?? null;
			}
			return isset($this->{$key}) ? $this->{$key} : null;
		}

		// 3. Setter (값 저장)
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

/**
 * 🚀 [Global Helper] ret()
 */
function ret(mixed $data = null): PQRet {
    return new PQRet($data);
}
?>