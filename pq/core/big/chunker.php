<?php
/**
 * =========================================================
 * PQ Big Data Chunker & Collection Helper
 * FILENAME : /pq/core/big/chunker.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQBigChunker {
    public static function split($data, $size) {
        // 1. DBMaker 객체인 경우 list() 추출
        if ($data instanceof DBMaker) {
            $data = $data->list();
        }

        // 2. PQRet 객체이거나 array() 메서드가 존재하는 경우 배열 변환
        if (is_object($data) && method_exists($data, 'array')) {
            $data = $data->array();
        } elseif (is_object($data) && property_exists($data, 'data')) {
            $data = $data->data;
        }

        // 3. 순수 배열 분할
        if (is_array($data)) {
            return array_chunk($data, $size);
        }

        return [[$data]];
    }
}

/**
 * PQ DSL 콜백 인자로 넘겨지는 Chunk Wrapper
 */
class PQBigChunkCollection implements \Countable {
    private $items;

    public function __construct(array $items) {
        $this->items = $items;
    }

    public function count(): int {
        return count($this->items);
    }

    public function items() {
        return $this->items;
    }

    public function data() {
        return $this->items;
    }

    // 마법 메서드로 .count 프로퍼티 읽기 접근 지원
    public function __get($name) {
        if ($name === 'count') {
            return $this->count();
        }
        return null;
    }
}
?>