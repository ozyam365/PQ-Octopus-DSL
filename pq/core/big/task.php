<?php
/**
 * =========================================================
 * PQ Big Data Task Runner
 * FILENAME : /pq/core/big/task.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQBigTask {
    private $source;
    private $chunkSize = 1000;
    private $intervalMs = 0;
    private $keepAlive = false;

    public function __construct($source, $chunkSize = 1000) {
        $this->source = $source;
        $this->chunkSize = (int)$chunkSize;
    }

    /**
     * ★ 청크 크기 지정 (체이닝 메서드)
     */
    public function chunk($size) {
        $this->chunkSize = max(1, (int)$size);
        return $this;
    }

    /**
     * ★ 실행 간격(Pacing) 지정 (예: "300ms", "1s")
     */
    public function interval($ms) {
        if (is_string($ms)) {
            if (str_contains($ms, 'ms')) {
                $this->intervalMs = (int)str_replace('ms', '', $ms);
            } elseif (str_contains($ms, 's')) {
                $this->intervalMs = (int)str_replace('s', '', $ms) * 1000;
            } else {
                $this->intervalMs = (int)$ms;
            }
        } else {
            $this->intervalMs = (int)$ms;
        }
        return $this;
    }

    /**
     * ★ 타임아웃 방지 Keep-Alive 플러시
     */
    public function keepalive($enable = true) {
        $this->keepAlive = (bool)$enable;
        return $this;
    }

    /**
     * ★ 실제 콜백 실행 및 루프 제어
     */
    public function run(callable $callback) {
        // DBMaker(QueryBuilder)인 경우 결과를 list()로 자동 조회
        $data = $this->source;
        if ($data instanceof DBMaker) {
            $data = $data->list();
        }

        // 데이터 청크 분할
        $chunks = PQBigChunker::split($data, $this->chunkSize);

        foreach ($chunks as $rawChunk) {
            $collection = new PQBigChunkCollection($rawChunk);

            // 사용자 콜백 실행
            $callback($collection);

            // Keep-Alive 플러시
            if ($this->keepAlive && ob_get_level() > 0) {
                @ob_flush();
                @flush();
            }

            // Pacing 휴식
            if ($this->intervalMs > 0) {
                usleep($this->intervalMs * 1000);
            }
        }
    }
}
?>