<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.5)
 * FILENAME : /pq/core/html.php
 * COMPONENT : XSS 보호 및 태그 제어 빌더 엔진
 * =========================================================
 */

class PQ_Html {
    private $allowed_tags = []; // 동적 허용 태그 저장
    private $allowed_iframes = [];
    private $options = [
        'youtube' => false,
        'iframe'  => false,
        'script'  => false,
        'style'   => false,
        'special' => false,
        'slash'   => false,
        'unslash' => false, // [수정] unslash 기본값 추가
        'xss'     => false
    ];
    private $content;
    private $use_trim = false;

    public function __construct($content = "") { 
        $this->content = $content; 
    }

    // 체이닝 메서드들
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

    public function __toString() {
        return $this->run();
    }

    // [1] 슬래시 추가 옵션
    public function slash($status = "on") { 
        $this->options['slash'] = ($status === "on"); 
        return $this; 
    }

    // [2] 슬래시 제거 옵션
    public function unslash($status = "on") { 
        $this->options['unslash'] = ($status === "on"); 
        return $this; 
    }

    public function run() {
        $result = $this->content;

        // 1. 유튜브/미디어 보호 로직
        $protection_queue = [];
        
        // 유튜브 보호
        if ($this->options['youtube']) {
            preg_match_all('/<iframe[^>]*src=["\']https?:\/\/(?:www\.)?(?:youtube\.com|youtube-nocookie\.com)\/embed\/[^"\']+["\'][^>]*>.*?<\/iframe>/is', $result, $matches);            
            $protection_queue = array_merge($protection_queue, $matches[0]);
        }
        
        // allow()에 추가된 태그 보호
        foreach ($this->allowed_tags as $tag) {
            $tag = preg_quote($tag, '/');
            preg_match_all('/<' . $tag . '[^>]*>.*?<\/' . $tag . '>/is', $result, $matches);
            $protection_queue = array_merge($protection_queue, $matches[0]);
        }

        // 보호 대상 치환
        foreach ($protection_queue as $i => $item) {
            $result = str_replace($item, "###PROTECTED_TAG_{$i}###", $result);
        }

        // 2. XSS 및 태그 필터링
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

        // 3. Iframe/Script/Style 삭제
        if (!$this->options['iframe'] && !$this->options['youtube']) {
            $result = preg_replace('/<iframe[^>]*>.*?<\/iframe>/i', '', $result);
        }
        if (!$this->options['script']) { $result = preg_replace('/<script[^>]*>.*?<\/script>/i', '', $result); }
        if (!$this->options['style'])  { $result = preg_replace('/<style[^>]*>.*?<\/style>/i', '', $result); }

        // 4. 복원
        foreach ($protection_queue as $i => $item) {
            $result = str_replace("###PROTECTED_TAG_{$i}###", $item, $result);
        }

        if ($this->use_trim) {
            $result = trim($result);
        }

        return $result;
    }   
}

function html($content = "") {
    return new PQ_Html($content);
}
?>