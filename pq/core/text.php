<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/text.php 
 * COMPONENT : PQ Engine Text Matrix Core Engine
 * =========================================================
 */

class PQText {
    private string $target_val = '';
    private string $rgx_cond = '';
    private string $on_mode = 'all';
    private bool $is_cut = false;

    // --- [1. INITIALIZATION & CONFIGURATION] ---

    /**
     * Set target payload and reset internal state
     */
    public function target(mixed $s): self {
        $this->target_val = ($s === null) ? '' : (string)$s;
        $this->rgx_cond = '';
        $this->on_mode = 'all';
        $this->is_cut = false;
        return $this;
    }

    /**
     * Set regex filter conditions
     */
    public function filter(string $cond): self {
        $p = str_replace(['~', ','], ['-', ''], $cond);
        if (!str_contains($cond, ' ')) {
            $p = str_replace(' ', '', $p);
        }
        $this->rgx_cond = $p;
        return $this;
    }

    /**
     * Case transformation modes
     */
    public function lower(): self { $this->on_mode = 'lower'; return $this; }
    public function upper(): self { $this->on_mode = 'upper'; return $this; }

    // --- [2. ESCAPING, REPLACEMENT & SANITIZATION] ---

    /** Add slashes for DB operations */
    public function slash(): self {
        if ($this->target_val !== '') {
            $this->target_val = addslashes($this->target_val);
        }
        return $this;
    }

    /** Strip slashes */
    public function unslash(): self {
        if ($this->target_val !== '') {
            $this->target_val = stripslashes($this->target_val);
        }
        return $this;
    }

    /** Strip all HTML tags */
    public function strip(): self {
        if ($this->target_val !== '') {
            $this->target_val = strip_tags($this->target_val);
        }
        return $this;
    }

    /** Full special character escaping (htmlspecialchars) */
    public function special(): self {
        if ($this->target_val !== '') {
            $this->target_val = htmlspecialchars($this->target_val, ENT_QUOTES, 'UTF-8');
        }
        return $this;
    }

    /** Pinpoint conversion for < and > symbols */
    public function ltgt(): self {
        if ($this->target_val !== '') {
            $this->target_val = str_replace(['<', '>'], ['&lt;', '&gt;'], $this->target_val);
        }
        return $this;
    }

    /** Convert line breaks to <br /> */
    public function nl2br(): self {
        if ($this->target_val !== '') {
            $this->target_val = nl2br($this->target_val);
        }
        return $this;
    }

    /** Trim whitespace */
    public function trim(): self {
        return $this->target(trim($this->target_val));
    }

    /** String replacement */
    public function change(mixed $search, mixed $replace): self {
        return $this->target(str_replace($search, $replace, $this->target_val));
    }

    // --- [3. TRUNCATION & FORMATTING] ---

    /** Standard string truncation */
    public function cut(int $length): self {
        if (mb_strlen($this->target_val, 'UTF-8') <= $length) {
            $this->is_cut = false;
        } else {
            $this->is_cut = true;
            $this->target_val = mb_substr($this->target_val, 0, $length, 'UTF-8');
        }
        return $this;
    }

    /** HTML-safe tag-preserving truncation */
    public function hcut(int $length): self {
        $text = $this->target_val;
        if (mb_strlen(preg_replace('/<[^>]*>/', '', $text), 'UTF-8') <= $length) {
            $this->is_cut = false;
            return $this; 
        }

        $this->is_cut = true;
        $res = ''; 
        $total = 0; 
        $open_tags = [];
        preg_match_all('/<[^>]+>|[^<]+/', $text, $matches);
        
        foreach ($matches[0] as $match) {
            if (str_starts_with($match, '<')) {
                if (str_starts_with($match, '</')) {
                    array_pop($open_tags);
                } elseif (!str_ends_with($match, '/>') && !preg_match('/<(img|br|hr|input)[^>]*>/i', $match)) {
                    preg_match('/<([^\s>]+)/', $match, $t); 
                    if (isset($t[1])) $open_tags[] = $t[1];
                }
                $res .= $match;
            } else {
                $len = mb_strlen($match, 'UTF-8');
                if ($total + $len >= $length) { 
                    $res .= mb_substr($match, 0, $length - $total, 'UTF-8'); 
                    break; 
                }
                $res .= $match; 
                $total += $len;
            }
        }
        
        $this->target_val = $res . implode('', array_map(fn($t) => "</$t>", array_reverse($open_tags)));
        return $this;
    }

    /** Append suffix to truncated string */
    public function suffix(string $str = '...'): string {
        if ($this->is_cut) {
            $this->target_val .= $str;
        }
        return $this->apply_mode($this->target_val);
    }

    /** Keyword highlighting */
    public function mark(string $keyword, string $wrapper = '<mark class="pq-mark">$1</mark>'): self {
        if (empty($keyword) || empty($this->target_val)) {
            return $this;
        }
        $escaped_kw = preg_quote($keyword, '/');
        $this->target_val = preg_replace('/(' . $escaped_kw . ')/i', $wrapper, $this->target_val);
        return $this;
    }

    /** Currency amount formatting (1,000) */
    public function money(): self {
        $num = preg_replace('/[^\d\.\-]/', '', $this->target_val);
        $parts = explode('.', $num === '' ? '0' : $num);
        $parts[0] = number_format((float)$parts[0]);
        return $this->target(implode('.', $parts));
    }

    /** Phone number formatting (010-0000-0000) */
    public function phone(): self {
        $n = preg_replace('/[^\d]/', '', $this->target_val);
        $l = strlen($n);
        if ($l === 11) {
            $r = preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $n);
        } elseif ($l === 10) {
            $r = str_starts_with($n, '02') ? preg_replace('/(02)(\d{4})(\d{4})/', '$1-$2-$3', $n) : preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $n);
        } else {
            $r = ($l === 9 && str_starts_with($n, '02')) ? preg_replace('/(02)(\d{3})(\d{4})/', '$1-$2-$3', $n) : $n;
        }
        return $this->target($r);
    }

    /** File extension extractor */
    public function ext(): string {
        return $this->apply_mode(strtolower(pathinfo($this->target_val, PATHINFO_EXTENSION)));
    }

    // --- [4. REGEX SEARCH & FILTERING] ---

    /** Strip characters outside allowed range */
    public function clean(): string {
        return $this->apply_mode(preg_replace("/[^{$this->rgx_cond}]/u", '', $this->target_val));
    }

    /** Extract characters within allowed range */
    public function find(): string {
        preg_match_all("/[{$this->rgx_cond}]/u", $this->target_val, $matches);
        return $this->apply_mode(implode('', $matches[0] ?? []));
    }

    /** Extract all pattern matching occurrences */
    public function find_all(string $pattern): array {
        if (empty($this->target_val) || empty($pattern)) return [];
        if (substr($pattern, 0, 1) !== substr($pattern, -1)) {
            $pattern = '/' . preg_quote($pattern, '/') . '/i';
        }

        if (preg_match_all($pattern, $this->target_val, $matches)) {
            return array_map(fn($val) => $this->apply_mode($val), $matches[0]);
        }
        return [];
    }

    /** Replace regex matched portions */
    public function replace(string $char): string {
        return $this->apply_mode(preg_replace("/[{$this->rgx_cond}]/u", $char, $this->target_val));
    }

    /** Count regex matches */
    public function count(): int {
        return (int)preg_match_all("/[{$this->rgx_cond}]/u", $this->target_val, $matches);
    }

    /** Extract first or last matched character */
    public function first(): string {
        preg_match("/[{$this->rgx_cond}]/u", $this->target_val, $match);
        return isset($match[0]) ? $this->apply_mode($match[0]) : '';
    }

    public function last(): string {
        preg_match_all("/[{$this->rgx_cond}]/u", $this->target_val, $matches);
        return (!empty($matches[0])) ? $this->apply_mode(end($matches[0])) : '';
    }

    // --- [5. ENCODING, ENCRYPTION & UTILITIES] ---

    public function decode(string $from = "CP949", string $to = "UTF-8"): self {
        $decoded = rawurldecode($this->target_val);
        $converted = iconv($from, $to, $decoded);
        return $this->target($converted);
    }

    public function encode(string $from = "UTF-8", string $to = "CP949"): self {
        $converted = iconv($from, $to, $this->target_val);
        $encoded = rawurlencode($converted);
        return $this->target($encoded);
    }

    public function conv(string $from = "CP949", string $to = "UTF-8"): self {
        $result = @iconv($from, $to, $this->target_val);
        return $this->target($result === false ? $this->target_val : $result);
    }

    public function urlencode(): self {
        return $this->target(rawurlencode($this->target_val));
    }

    public function encrypt(): string {
        $iv = random_bytes(16);
        return strrev(base64_encode($iv . openssl_encrypt($this->target_val, 'AES-256-CBC', hash('sha256', APP_SECRET, true), OPENSSL_RAW_DATA, $iv)));
    }

    public function decrypt(): string|false {
        $d = base64_decode(strrev($this->target_val), true);
        if (!$d || strlen($d) < 17) return false;
        return openssl_decrypt(substr($d, 16), 'AES-256-CBC', hash('sha256', APP_SECRET, true), OPENSSL_RAW_DATA, substr($d, 0, 16));
    }

    /** Generate unique identifier key */
    public function random(string $prefix = 'CRT', int $bytes = 3): string {
        $microTime = str_replace('.', '', microtime(true));
        $randomBytes = bin2hex(random_bytes($bytes));
        return strtoupper($prefix . '_' . $microTime . $randomBytes);
    }

    // --- [6. OUTPUT EVALUATION & INSPECTION] ---

    public function len(): int {
        return mb_strlen($this->target_val, 'UTF-8');
    }

    public function empty(): bool {
        return trim($this->target_val) === '';
    }

    public function val(string $default = ""): string {
        if ($this->empty()) {
            return $default;
        }
        return $this->apply_mode($this->target_val);
    }

    public function __toString(): string {
        return $this->apply_mode($this->target_val);
    }

    private function apply_mode(string $s): string {
        if ($this->on_mode === 'lower') return mb_strtolower($s, 'UTF-8');
        if ($this->on_mode === 'upper') return mb_strtoupper($s, 'UTF-8');
        return $s;
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('text_pq')) {
    function text_pq(mixed $str = ""): PQText {
        $inst = new PQText();
        return $inst->target($str);
    }
}

if (!function_exists('text')) {
    function text(mixed $str = ""): PQText {
        return text_pq($str);
    }
}
?>