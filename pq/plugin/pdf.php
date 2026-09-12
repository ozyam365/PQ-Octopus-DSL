<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/plugin/pdf.php
 * COMPONENT : PQ PDF Generator Core Plugin (TCPDF Bridge)
 * =========================================================
 */

if (!defined('PQ_ROOT')) {
    define('PQ_ROOT', $_SERVER['DOCUMENT_ROOT']);
}

$tcpdf_path = rtrim(PQ_ROOT, '/') . '/assets/tcpdf/tcpdf.php';

if (!file_exists($tcpdf_path)) {
    die("❌ [PQ Engine Error] TCPDF vendor library missing. Path: " . htmlspecialchars($tcpdf_path, ENT_QUOTES, 'UTF-8'));
}

require_once $tcpdf_path;

class PQ_PDF_Engine {
    protected static $instance = null;
    protected $html_content = '';
    protected $paper_size = 'A4';
    protected $orientation = 'P';
    protected $font_name = 'nanumgothic';
    protected $font_size = 12;

    /**
     * Singleton Instance Factory
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init($paper_size = 'A4', $orientation = 'P') {
        $this->paper_size = $paper_size;
        $this->orientation = strtoupper($orientation);
        return $this;
    }

    public function font($font_name = 'nanumgothic', $font_size = 12) {
        $this->font_name = strtolower($font_name);
        $this->font_size = (int)$font_size;
        return $this;
    }

    public function content($html) {
        $this->html_content = (string)$html;
        return $this;
    }

    public function load($html) {
        return $this->content($html);
    }

    public function size($paper_size = 'A4', $orientation = 'P') {
        return $this->init($paper_size, $orientation);
    }

    public function save($path) {
        $full_path = rtrim(PQ_ROOT, '/') . '/' . ltrim($path, '/');
        $dir = dirname($full_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $this->render('F', $full_path);
    }

    public function render($mode = 'I', $filename = 'pq_document.pdf') {
        try {
            $pdf = new TCPDF($this->orientation, 'mm', $this->paper_size, true, 'UTF-8', false);
            $pdf->SetCreator('PQ Engine');
            $pdf->SetAuthor('PQ');
            $pdf->SetTitle('PQ PDF Document');
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Dynamic font application
            $pdf->SetFont($this->font_name, '', $this->font_size);
            $pdf->AddPage();
            $pdf->writeHTML($this->html_content, true, false, true, false, '');

            if (ob_get_contents()) {
                ob_end_clean();
            }

            $pdf->Output($filename, $mode);
            return true;

        } catch (\Throwable $e) {
            echo "<div style='background:#220f12;color:#ff8080;padding:20px;font-family:monospace;border:3px solid #ff4444;'>";
            echo "<h3>❌ PQ PDF RUNTIME EXCEPTION</h3>";
            echo "<p><b>ERROR:</b> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p><b>LINE:</b> " . (int)$e->getLine() . "</p>";
            echo "</div>";
            return false;
        }
    }
}

// --- [FACADE INTERFACE CLASS] ---
class pdf {
    public static function init($paper_size = 'A4', $orientation = 'P') {
        return PQ_PDF_Engine::getInstance()->init($paper_size, $orientation);
    }
    public static function font($font_name = 'nanumgothic', $font_size = 12) {
        return PQ_PDF_Engine::getInstance()->font($font_name, $font_size);
    }
    public static function load($html) {
        return PQ_PDF_Engine::getInstance()->load($html);
    }
    public static function content($html) {
        return PQ_PDF_Engine::getInstance()->content($html);
    }
    public static function size($paper_size = 'A4', $orientation = 'P') {
        return PQ_PDF_Engine::getInstance()->size($paper_size, $orientation);
    }
    public static function save($path) {
        return PQ_PDF_Engine::getInstance()->save($path);
    }
    public static function render($mode = 'I', $filename = 'pq_document.pdf') {
        return PQ_PDF_Engine::getInstance()->render($mode, $filename);
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('pdf_pq')) {
    function pdf_pq() {
        return PQ_PDF_Engine::getInstance();
    }
}

if (!function_exists('pdf')) {
    function pdf() {
        return pdf_pq();
    }
}
?>