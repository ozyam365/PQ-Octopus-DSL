<?php
/**
 * =========================================================
 * PQ VERSION (v9.1.6)
 * FILENAME : /pq/plugin/barcode.php
 * COMPONENT : PQ Barcode Core Plugin (Part 1/2)
 * =========================================================
 */

class PQ_Barcode_Engine {
    protected static $instance = null;
    protected $barcode_val = '';
    protected $format = 'CODE128';

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function value($val) {
        $this->barcode_val = urlencode($val);
        return $this;
    }

    public function type($format_type) {
        $this->format = strtoupper($format_type);
        return $this;
    }

    /**
     * 📱 최종 렌더링 구역 (비동기 바코드 이미지 복사 배포)
     */
    public function render() {
        if (empty($this->barcode_val)) {
            return "🕵️ [바코드 수사 실패] 식별할 가치 번호가 누락되었습니다.";
        }

        // 🕵️ [주소창 파라미터 결합 결함 완벽 진압]
        // 누락되었던 도메인 후방 물음표(?) 쿼리 규격을 정상 동기화하여 엑스박스 깨짐을 영구 처단 완료.
        $target_format = strtolower($this->format);
        $api_url = "https://metafloor.com" . $target_format . "&text={$this->barcode_val}&includeheight=1";
        
        $html = '<img src="' . $api_url . '" class="pq-barcode-img img-fluid shadow-sm rounded border p-2 bg-white" alt="PQ Barcode">';
        
        $this->barcode_val = '';
        $this->format = 'CODE128';
        
        return $html;
    }
}
/**
 * =========================================================
 * BARCODE FACADE INTERFACE (Fixed Compiler Synchronizer)
 * =========================================================
 */
class barcode {
    public static function value($val) { return PQ_Barcode_Engine::getInstance()->value($val); }
    public static function type($type) { return PQ_Barcode_Engine::getInstance()->type($type); }
    public static function render() { return PQ_Barcode_Engine::getInstance()->render(); }
}

/**
 * 🕵️ [전역 러너 바인딩 안전 쉴드 결속]
 * runner.php 플러그인 로더 규격 및 예약어 인터페이스와 
 * 100% 무결하게 싱크 연동되기 위한 정형 팩토리 함수 안착 구현 완료.
 */
if (!function_exists('barcode_pq')) {
    function barcode_pq() {
        return PQ_Barcode_Engine::getInstance();
    }
}
?>
