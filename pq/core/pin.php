<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.5)
 * FILENAME  : /pq/core/pin.php  
 * COMPONENT : PQ Core Pin (Variable Binding & Data Processing)
 * =========================================================
 */

class PQPin {
    /**
     * 바인딩된 변수들의 참조(Reference) 리스트
     * @var array
     */
    private array $refs = [];

    /**
     * [Step 1] 타겟 변수들의 참조 메모리 주소를 일괄 바인딩
     * 
     * @param array $vars 참조 변수 배열
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
     * [Action] .val(): 바인딩된 모든 변수에 값 일괄 할당
     * 
     * @param mixed $value 할당할 값
     * @return mixed
     */
	public function val(mixed $value): mixed {
		foreach ($this->refs as &$var) {
			$var = $value; // 기존에 값이 있든 없든 무조건 $value로 강제 초기화!
		}
		return $value;
	}
    /**
     * [Action] .int(): 모든 변수를 정수형(Integer)으로 일괄 캐스팅
     * 
     * @param int|null $default 값 변경 실패 시 기본값 (null 설정 시 기존 유지)
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
     * [Action] .string(): 모든 변수를 문자열(String)로 강제 변환
     * 
     * @param string $default 기본값
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
     * [Action] .bool(): 모든 변수를 불리언(Boolean) 타입으로 변환
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
     * [Action] .array(): 모든 변수를 배열(Array) 타입으로 일괄 변환
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
     * [Action] .object(): 모든 변수를 객체(stdClass) 타입으로 일괄 변환
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
     * [Action] .clean(): 문자열 공백(trim) 및 좌우 연속 공백 일괄 정제
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
     * [Action] .null(): 빈 문자열("") 또는 빈 배열([]) 상태의 변수를 null로 변환
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

/**
 * 🚀 [Global Helper] pin()
 * 가변 참조(&...$vars)를 이용해 스크립트 어디서든 변수를 고정하여 체이닝을 시작합니다.
 * 
 * @param mixed ...$vars
 * @return PQPin
 */
function pin(&...$vars): PQPin {
    $inst = new PQPin();
    return $inst->bind($vars);
}