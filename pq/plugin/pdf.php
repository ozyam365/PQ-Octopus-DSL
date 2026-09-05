<?php
/**
 * =========================================================
 * PQ VERSION (v9.1.6)
 * FILENAME : /pq/plugin/pdf.php
 * COMPONENT : PQ PDF Plugin (TCPDF Edition) (Part 1/2)
 * =========================================================
 */

if (!defined('PQ_ROOT')) {
    define('PQ_ROOT', $_SERVER['DOCUMENT_ROOT']);
}

$tcpdf_path = rtrim(PQ_ROOT, '/') . '/assets/tcpdf/tcpdf.php';

if (!file_exists($tcpdf_path)) {
    die("❌ [PQ 수사망 통제] TCPDF 벤더 파일 유실. 경로 확인: " . htmlspecialchars($tcpdf_path));
}

require_once $tcpdf_path;

class PQ_PDF_Engine {
    protected static $instance = null;
    protected $html_content = '';
    protected $paper_size = 'A4';
    protected $orientation = 'P';
    protected $font_name = 'nanumgothic';
    protected $font_size = 12;

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
            $pdf->SetTitle('PQ PDF');
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // 🕵️ [동적 폰트 유실 결함 완벽 진압]
            // 하드코딩되어 있던 'nanumgothic' 문자열을 거세하고, 
            // 플루언트 메서드로 수립된 $this->font_name 자산을 완벽 동기화 투영 완료했습니다.
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
            echo "<h3>❌ PQ PDF RUNTIME CRASH</h3>";
            echo "<p><b>ERROR:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><b>LINE:</b> " . (int)$e->getLine() . "</p>";
            echo "</div>";
            return false;
        }
    }
}

/**
 * =========================================================
 * PDF FACADE & GLOBAL HELPER FUNCTIONS
 * =========================================================
 */
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

// 🕵️ [제6원칙 핵심 주입] 컴파일러의 쪼개기 오역 방어선 글로벌 함수 스택 사수
if (!function_exists('font')) {
    function font($font_name = 'nanumgothic', $font_size = 12) {
        return PQ_PDF_Engine::getInstance()->font($font_name, $font_size);
    }
}
if (!function_exists('load')) {
    function load($html) {
        return PQ_PDF_Engine::getInstance()->load($html);
    }
}
if (!function_exists('content')) {
    function content($html) {
        return PQ_PDF_Engine::getInstance()->content($html);
    }
}
if (!function_exists('render')) {
    function render($mode = 'I', $filename = 'pq_document.pdf') {
        return PQ_PDF_Engine::getInstance()->render($mode, $filename);
    }
}

/**
 * 🕵️ [전역 러너 바인딩 안전 쉴드 결속]
 * runner.php 내 24라인 주변의 플러그인 로더 공정과 
 * 100% 무결하게 매핑 싱크되기 위한 정형 팩토리 관문 함수 안착 구현 완료.
 */
if (!function_exists('pdf_pq')) {
    function pdf_pq() {
        return PQ_PDF_Engine::getInstance();
    }
}
?>
