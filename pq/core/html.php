<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/html.php
 * COMPONENT : PQ XSS Protection & Tag Control Builder Engine
 * =========================================================
 */

class PQ_Html {
    private $allowed_tags = []; // Dynamic allowed tags storage
    private $allowed_iframes = [];
    private $options = [
        'youtube' => false,
        'iframe'  => false,
        'script'  => false,
        'style'   => false,
        'special' => false,
        'slash'   => false,
        'unslash' => false,
        'xss'     => false
    ];
    private $content;
    private $use_trim = false;

    public function __construct($content = "") { 
        $this->content = $content; 
    }

    // --- [1. CHAINING BUILDER OPTIONS] ---
    public function youtube($status = "on") {
        $this->options['youtube'] = ($status === "on");

        if ($status === "on") {
            $this->options['iframe'] = true;
        }

        return $this;
    }

    public function special($status = "on") { 
        $this->options['special'] = ($status === "on"); 
        return $this; 
    }

    public function iframe($status = "on") { $this->options['iframe'] = ($status === "on"); return $this; }
    public function script($status = "on") { $this->options['script'] = ($status === "on"); return $this; }
    public function style($status  = "on") { $this->options['style']  = ($status === "on"); return $this; }
    public function xss($status    = "on") { $this->options['xss']    = ($status === "on"); return $this; }

    public function allow($tag) {
        $tags = explode(",", $tag);
        
        foreach ($tags as $t) {
            $t = strtolower(trim($t));
            if (!empty($t) && !in_array($t, $this->allowed_tags, true)) {
                $this->allowed_tags[] = $t;
            }
        }
        return $this;
    }

    public function trim() {
        $this->use_trim = true;
        return $this;
    }

    public function slash($status = "on") { 
        $this->options['slash'] = ($status === "on"); 
        return $this; 
    }

    public function unslash($status = "on") { 
        $this->options['unslash'] = ($status === "on"); 
        return $this; 
    }

    // --- [2. EXECUTION PIPELINE] ---
    public function run() {
        $result = $this->content;

        // 1. YouTube & Whitelisted Media Protection
        $protection_queue = [];
        
        // YouTube iframe isolation
        if ($this->options['youtube']) {
            preg_match_all('/<iframe[^>]*src=["\']https?:\/\/(?:www\.)?(?:youtube\.com|youtube-nocookie\.com)\/embed\/[^"\']+["\'][^>]*>.*?<\/iframe>/is', $result, $matches);            
            $protection_queue = array_merge($protection_queue, $matches[0]);
        }
        
        // Protect dynamically allowed tags via allow()
        foreach ($this->allowed_tags as $tag) {
            $tag = preg_quote($tag, '/');
            preg_match_all('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', $result, $matches);
            $protection_queue = array_merge($protection_queue, $matches[0]);
        }

        // Replace protected targets with placeholders
        foreach ($protection_queue as $i => $item) {
            $result = str_replace($item, "###PROTECTED_TAG_{$i}###", $result);
        }

        // 2. XSS & HTML Sanitization
        if ($this->options['xss']) {
            $result = preg_replace('/on[a-z]+\s*=\s*["\'][^"\']*["\']/i', '', $result);
            $result = preg_replace('/on[a-z]+\s*=\s*[^\s>]+/i', '', $result);
            $result = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $result);            
        }

        if ($this->options['special']) {
            $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        }

        if (!empty($this->options['slash']))   $result = addslashes($result);
        if (!empty($this->options['unslash'])) $result = stripslashes($result);

        // 3. Purge Unallowed Iframe / Script / Style
        if (!$this->options['iframe'] && !$this->options['youtube']) {
            $result = preg_replace('/<iframe[^>]*>.*?<\/iframe>/i', '', $result);
        }
        if (!$this->options['script']) { $result = preg_replace('/<script[^>]*>.*?<\/script>/i', '', $result); }
        if (!$this->options['style'])  { $result = preg_replace('/<style[^>]*>.*?<\/style>/i', '', $result); }

        // 4. Restore Protected Tags
        foreach ($protection_queue as $i => $item) {
            $result = str_replace("###PROTECTED_TAG_{$i}###", $item, $result);
        }

        if ($this->use_trim) {
            $result = trim($result);
        }

        return $result;
    }   

    public function __toString() {
        return $this->run();
    }
}

// [ENGINE CORE] Singleton Bridge & DSL Wrapper Functions
if (!function_exists('html_pq')) {
    function html_pq($content = "") {
        return new PQ_Html($content);
    }
}

if (!function_exists('html')) {
    function html($content = "") {
        return html_pq($content);
    }
}
?>