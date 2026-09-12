<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/plugin/barcode.php
 * COMPONENT : PQ Barcode Generator Core Plugin
 * =========================================================
 */

class PQ_Barcode_Engine {
    protected static $instance = null;
    protected $barcode_val = '';
    protected $format = 'CODE128';

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
     * Set barcode text value
     */
    public function value($val) {
        $this->barcode_val = urlencode($val);
        return $this;
    }

    /**
     * Set barcode format type (e.g., CODE128, EAN13, QR)
     */
    public function type($format_type) {
        $this->format = strtoupper($format_type);
        return $this;
    }

    /**
     * Render barcode image component HTML
     */
    public function render() {
        if (empty($this->barcode_val)) {
            return "[Barcode Engine Error] Missing required value for barcode generation.";
        }

        $target_format = strtolower($this->format);
        $api_url = "https://metafloor.com" . $target_format . "&text={$this->barcode_val}&includeheight=1";
        
        $html = '<img src="' . $api_url . '" class="pq-barcode-img img-fluid shadow-sm rounded border p-2 bg-white" alt="PQ Barcode">';
        
        // Reset state buffers
        $this->barcode_val = '';
        $this->format = 'CODE128';
        
        return $html;
    }
}

// --- [FACADE INTERFACE CLASS] ---
class barcode {
    public static function value($val) { return PQ_Barcode_Engine::getInstance()->value($val); }
    public static function type($type) { return PQ_Barcode_Engine::getInstance()->type($type); }
    public static function render() { return PQ_Barcode_Engine::getInstance()->render(); }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('barcode_pq')) {
    function barcode_pq() {
        return PQ_Barcode_Engine::getInstance();
    }
}

if (!function_exists('barcode')) {
    function barcode() {
        return barcode_pq();
    }
}
?>