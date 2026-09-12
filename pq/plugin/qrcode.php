<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/plugin/qrcode.php
 * COMPONENT : PQ QR-Code Generator Core Plugin
 * =========================================================
 */

class PQ_QRCode_Engine {
    protected static $instance = null;
    protected $data_text = '';
    protected $size_px = 150;

    /**
     * Singleton Instance Factory
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set target text or URL to encode
     */
    public function text($text) {
        $this->data_text = urlencode($text);
        return $this;
    }

    /**
     * Set dimension size in pixels
     */
    public function size($px) {
        $this->size_px = (int)$px;
        return $this;
    }

    /**
     * Render QR Code image component HTML
     */
    public function render() {
        if (empty($this->data_text)) {
            return "[QR Engine Error] Missing required text or URL payload for encoding.";
        }
        
        $api_url = "https://chart.googleapis.com/chart?cht=qr&chs={$this->size_px}x{$this->size_px}&chl={$this->data_text}";
        $html = '<img src="' . $api_url . '" class="pq-qrcode-img img-fluid shadow-sm rounded border p-2 bg-white" alt="PQ QRCode">';
        
        // Reset internal state buffer to prevent singleton state pollution
        $this->data_text = '';
        $this->size_px = 150;
        
        return $html;
    }
}

// --- [FACADE INTERFACE CLASS] ---
class qrcode {
    public static function text($text) { return PQ_QRCode_Engine::getInstance()->text($text); }
    public static function size($px) { return PQ_QRCode_Engine::getInstance()->size($px); }
    public static function render() { return PQ_QRCode_Engine::getInstance()->render(); }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('qrcode_pq')) {
    function qrcode_pq() {
        return PQ_QRCode_Engine::getInstance();
    }
}

if (!function_exists('qrcode')) {
    function qrcode() { 
        return qrcode_pq(); 
    }
}
?>