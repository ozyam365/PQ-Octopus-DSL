<?php
/**
 * =========================================================
 * PQ Crawler CSS to XPath Selector Engine
 * FILENAME : /pq/plugin/crawler/selector.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQCrawlerSelector {
    /**
     * CSS Selector -> XPath 변환기
     * @param string $selector  CSS 셀렉터 (#id, .class, tag)
     * @param bool   $isRelative Element 내부 상대 경로 탐색 여부 (.//)
     */
    public static function toXpath($selector, $isRelative = false) {
        $selector = trim($selector);
        $prefix = $isRelative ? ".//" : "//";

        if (str_starts_with($selector, '#')) {
            return $prefix . "*[@id='" . substr($selector, 1) . "']";
        }
        if (str_starts_with($selector, '.')) {
            return $prefix . "*[contains(concat(' ', normalize-space(@class), ' '), ' " . substr($selector, 1) . " ')]";
        }
        return $prefix . $selector;
    }
}
?>