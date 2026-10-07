<?php
/**
 * =========================================================
 * PQ Big Data Exporter Engine (Streaming Supported)
 * FILENAME : /pq/core/big/exporter.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQBigExporter {
    private $source;
    private $type;
    private $chunkSize = 1000;

    public function __construct($type, $source) {
        $this->type = strtoupper(trim($type));
        $this->source = $source;
    }

    public function chunk($size) {
        $this->chunkSize = max(1, (int)$size);
        return $this;
    }

    /**
     * CSV/텍스트 스트리밍 다운로드 실행 (메모리 파이프라인 완비)
     */
    public function download($filename = "export.csv") {
        if (!headers_sent()) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
        }

        // BOM 추가 (엑셀 한글 깨짐 방지)
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        $buffer = [];

        // source가 PQBigFetcher 인스턴스인 경우 Generator 추출
        $iterable = $this->source;
        if ($iterable instanceof PQBigFetcher) {
            $iterable = $iterable->cursor();
        } elseif (is_object($iterable) && property_exists($iterable, 'data')) {
            $iterable = $iterable->data;
        }

        // 1건씩 흘려보내며 버퍼링 제어 (전체 source를 메모리에 적재하지 않고 chunkSize만큼만 export buffer에 보관한다.)
        foreach ($iterable as $row) {
            $buffer[] = $this->formatRow($row);

            if (count($buffer) >= $this->chunkSize) {
                $this->writeChunk($output, $buffer);
                $buffer = []; // 메모리 버퍼 초기화

                if (ob_get_level() > 0) {
                    @ob_flush();
                    @flush();
                }
            }
        }

        // 남은 버퍼 출력
        if (!empty($buffer)) {
            $this->writeChunk($output, $buffer);
            if (ob_get_level() > 0) {
                @ob_flush();
                @flush();
            }
        }

        fclose($output);
        // exit 제거: 호출부 및 PQ 엔진 후처리가 정상 작동하도록 반환
        return true;
    }

    private function formatRow($row) {
        if (is_array($row)) {
            return $row;
        } elseif (is_object($row)) {
            return get_object_vars($row);
        }
        return [$row];
    }

    private function writeChunk($handle, array $chunk) {
        foreach ($chunk as $row) {
            fputcsv($handle, $row);
        }
    }
}
?>