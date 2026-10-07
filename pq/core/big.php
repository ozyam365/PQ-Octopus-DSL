<?php
/**
 * =========================================================
 * Core module for the safe processing of large volumes of data
 * FILENAME  : /pq/core/big.php
 * UPDATE :  2026-10-07 PM 07:01  
 * =========================================================
 */

require_once __DIR__ . '/big/chunker.php';
require_once __DIR__ . '/big/task.php';
require_once __DIR__ . '/big/exporter.php';
require_once __DIR__ . '/big/fetcher.php';

class PQBigCore {
    /**
     * 단독 실행: big.chunk($data, 1000)
     */
    public function chunk($data, $size = 1000) {
        return new PQBigTask($data, $size);
    }

    public function stream($data, $size = 1) {
        return new PQBigTask($data, $size);
    }

    public function fetch($sql, $params = []) {
        $pdo = $GLOBALS['db']->pdo();
        return new PQBigFetcher($pdo, $sql, $params);
    }

    public function export($type, $data) {
        return new PQBigExporter($type, $data);
    }

    /**
     * 체이닝 바인딩: db.@table.big() / crawler.big() 등에서 호출
     */
    public function bind($source) {
        return new PQBigTask($source);
    }
}

// Global binding for PQ Core
$GLOBALS['big'] = new PQBigCore();
$big = $GLOBALS['big'];
?>