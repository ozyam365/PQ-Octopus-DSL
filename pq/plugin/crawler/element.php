<?php
/**
 * =========================================================
 * PQ Crawler Element Wrapper
 * FILENAME : /pq/plugin/crawler/element.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQCrawlerElement {
    private $node;
    private $xpath;

    public function __construct($node, $xpath) {
        $this->node = $node;
        $this->xpath = $xpath;
    }

    public function find($selector) {
        $relXpath = PQCrawlerSelector::toXpath($selector, true);
        $nodes = $this->xpath->query($relXpath, $this->node);
        if ($nodes && $nodes->length > 0) {
            return new PQCrawlerElement($nodes->item(0), $this->xpath);
        }
        return null;
    }

    public function findAll($selector) {
        $relXpath = PQCrawlerSelector::toXpath($selector, true);
        $nodes = $this->xpath->query($relXpath, $this->node);
        $result = [];
        if ($nodes) {
            foreach ($nodes as $node) {
                $result[] = new PQCrawlerElement($node, $this->xpath);
            }
        }
        return $result;
    }

    public function text() {
        return $this->node ? trim($this->node->textContent) : '';
    }

    public function html() {
        if (!$this->node) return '';
        $innerHTML = '';
        $children = $this->node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $this->node->ownerDocument->saveHTML($child);
        }
        return trim($innerHTML);
    }

    public function attr($name) {
        if ($this->node && $this->node->hasAttribute($name)) {
            return $this->node->getAttribute($name);
        }
        return '';
    }
}
?>