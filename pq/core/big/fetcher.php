<?php
/**
 * =========================================================
 * PQ Big Data Unbuffered Fetcher Engine
 * FILENAME : /pq/core/big/fetcher.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQBigFetcher {
    private $pdo;
    private $sql;
    private $params;
    private $chunkInterval = 10000;

    public function __construct(PDO $pdo, string $sql, array $params = []) {
        $this->pdo = $pdo;
        $this->sql = $sql;
        $this->params = $params;
    }

    /**
     * 비버퍼링 제너레이터 커서 수신 (메모리 폭증 방지 및 1건씩 스트리밍)
     */
    public function cursor(): Generator {
        // 1. PDO 비버퍼링 모드 전환
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $stmt = $this->pdo->prepare($this->sql);
        $stmt->execute($this->params);

        $count = 0;
        try {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count++;

                // 2. 1건씩 yield 송출 (RAM 적재 방지)
                yield (object)$row;

                // 3. 주기적 가비지 컬렉터 구동
                if ($count % $this->chunkInterval === 0) {
                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }
            }
        } finally {
            // 4. 예외 발생이나 조기 종료(break) 시에도 커서 폐쇄 및 PDO 설정 완벽 복구
            if ($stmt) {
                $stmt->closeCursor();
            }
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
    }

    /**
     * PQ DSL run(#row) 연동 루프
     */
    public function run(callable $callback) {
        $count = 0;
        foreach ($this->cursor() as $row) {
            $count++;
            $callback($row);

            // 실시간 버퍼 플러시 및 커넥션 유지
            if ($count % $this->chunkInterval === 0) {
                if (ob_get_level() > 0) {
                    @ob_flush();
                    @flush();
                }
            }
        }
    }

    /**
     * Exporter와 직접 연결 (big.fetch().export("CSV"))
     */
    public function export($type = "CSV") {
        return new PQBigExporter($type, $this);
    }
}
?>