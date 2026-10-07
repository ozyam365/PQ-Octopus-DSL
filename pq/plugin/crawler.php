<?php
/**
 * =========================================================
 * PQ Crawler & Web Scraper Plugin
 * FILENAME  : /pq/plugin/crawler.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

require_once __DIR__ . '/crawler/selector.php';
require_once __DIR__ . '/crawler/element.php';
require_once __DIR__ . '/crawler/document.php';

class PQCrawler {
    private $delaySeconds = 0;
    private $currentTargets = [];

    public function delay($seconds) {
        $this->delaySeconds = (int)$seconds;
        return $this;
    }

    /**
     * 다중 크롤링 타겟 지정 (crawler.targets(@urls))
     */
    public function targets($urls) {
        $this->currentTargets = is_array($urls) ? $urls : [$urls];
        return $this;
    }

    /**
     * Big 실행 제어 모드로 스위칭 (crawler.targets(@urls).big())
     */
    public function big() {
        if (isset($GLOBALS['big']) && method_exists($GLOBALS['big'], 'bind')) {
            return $GLOBALS['big']->bind($this->currentTargets);
        }
        return new PQBigTask($this->currentTargets);
    }

    /**
     * 단일 URL 수집 (기존 cURL 엔진)
     */
    public function get($url) {
        if ($this->delaySeconds > 0) {
            sleep($this->delaySeconds);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $html = curl_exec($ch);
        curl_close($ch);

        return new PQCrawlerDocument($html ? $html : '');
    }
}

// Global binding for PQ Engine
$GLOBALS['crawler'] = new PQCrawler();
$crawler = $GLOBALS['crawler'];
?>