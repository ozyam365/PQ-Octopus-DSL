<?php
/**
 * =========================================================
 * PQ Crawler Document Wrapper
 * FILENAME : /pq/plugin/crawler/document.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQCrawlerDocument {
    private $xpath;
    private $dom;

    public function __construct($html) {
        $this->dom = new DOMDocument();

        if (!empty($html)) {
            if (!str_contains($html, 'xml encoding=')) {
                $html = '<?xml encoding="UTF-8">' . $html;
            }
            @$this->dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        }

        $this->xpath = new DOMXPath($this->dom);
    }

    public function find($selector) {
        $xpathExpr = PQCrawlerSelector::toXpath($selector, false);
        $nodes = $this->xpath->query($xpathExpr);
        if ($nodes && $nodes->length > 0) {
            return new PQCrawlerElement($nodes->item(0), $this->xpath);
        }
        return null;
    }

    public function findAll($selector) {
        $xpathExpr = PQCrawlerSelector::toXpath($selector, false);
        $nodes = $this->xpath->query($xpathExpr);
        $result = [];
        if ($nodes) {
            foreach ($nodes as $node) {
                $result[] = new PQCrawlerElement($node, $this->xpath);
            }
        }
        return $result;
    }
}
?>