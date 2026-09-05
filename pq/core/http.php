<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/core/http.php  
 * COMPONENT : PQ HTTP Client & Browser Controller
 * =========================================================
 */
class HttpMaker {
    private $headers = [];
    private $params = [];
    private $timeout = 5; 
    private $body = null;
    private $msg_buffer = "";
    private $bootstrap_alert_msg = ""; // 🎨 Bootstrap Alert 스킨 전용 버퍼
    private $is_executed = false;
    private $referer_out = null;

    // [Step 0] 환경 정보 수사
    public function ip() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function agent() { return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; }
    
    public function referer($default = '') { 
        return $_SERVER['HTTP_REFERER'] ?? $default; 
    }

    public function referer_to($url) { 
        $this->referer_out = $url; 
        return $this; 
    }

    public function request_uri($default = '') { 
        return $_SERVER['REQUEST_URI'] ?? $default; 
    }

    public function path($default = '/') {
        $uri = $_SERVER['REQUEST_URI'] ?? $default;
        return parse_url($uri, PHP_URL_PATH) ?? $default;
    }   

    // [Step 1] HTTP 통신 (cURL) 및 파라미터 수신
    public function get($key = null, $default = null) { 
        if (is_string($key) && preg_match('#^https?://#i', $key)) {
            return $this->send($key, "GET"); 
        }

        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    public function post($url, $data = null) { if ($data !== null) $this->params = $data; return $this->send($url, "POST"); }
    public function put($url, $data = null) { if ($data !== null) $this->params = $data; return $this->send($url, "PUT"); }
    public function delete($url) { return $this->send($url, "DELETE"); }

    public function header($text) { $this->headers[] = $text; return $this; }
    public function timeout($sec) { $this->timeout = (int)$sec; return $this; }

    public function jsondata($data){
        $this->header('Content-Type: application/json');
        $this->body = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        return $this;
    }

    public function json($data){
        if (ob_get_length()) { ob_clean(); }
        if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function send($url, $method) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $is_prod = !defined('PQ_DEBUG_MODE') || !PQ_DEBUG_MODE;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $is_prod);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $is_prod ? 2 : 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent());
        if (!empty($this->headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        
        if ($this->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->body);
        } elseif (!empty($this->params)) {
            if ($method === 'GET') {
                curl_setopt($ch, CURLOPT_URL, $url . (strpos($url, '?') !== false ? '&' : '?') . http_build_query($this->params));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
            }
        }

        $outbound_referer = $this->referer_out ?? $this->referer();
        if (!empty($outbound_referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $outbound_referer);
        }
    
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $this->headers = []; $this->params = []; $this->body = null; $this->timeout = 5; $this->referer_out = null;

        if ($err) { return null; }
        return $this->parse_response($res);
    }

    private function parse_response($res) {
        if ($res === '' || $res === false) { return null; }
        $json = json_decode($res, true);
        return (json_last_error() === JSON_ERROR_NONE) ? (object)$json : $res;
    }

    // [Step 2] 브라우저 제어 (Redirect, Alert, JS 스킨)
    public function redirect($u) {
        if (!headers_sent()) { header("Location: $u"); exit; }
        echo "<script>location.replace(".json_encode($u, JSON_UNESCAPED_SLASHES).");</script>"; exit;
    }

    // 기본 JS Alert 체이닝용
    public function msg($message) { 
        $this->msg_buffer = $message; 
        return $this; 
    }

    // 🎨 Bootstrap 모던 Alert 체이닝용 (http.alert("메시지").back())
    public function alert($msg) {
        $this->bootstrap_alert_msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function go($url) {
        $this->is_executed = true;
        if (ob_get_length()) { ob_clean(); }

        // Bootstrap Alert 스킨 우선 렌더링
        if (!empty($this->bootstrap_alert_msg)) {
            $this->render_bootstrap_alert_and_redirect("location.replace(" . json_encode($url, JSON_UNESCAPED_SLASHES) . ");");
        }

        echo "<script>";
        if ($this->msg_buffer !== "") {
            echo "alert(" . json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE) . ");";
        }
        echo "location.replace(" . json_encode($url, JSON_UNESCAPED_SLASHES) . ");";
        echo "</script>"; 
        exit;       
    }

    public function back($step = -1) {
        $this->is_executed = true;
        if (ob_get_length()) { ob_clean(); }

        // Bootstrap Alert 스킨 우선 렌더링
        if (!empty($this->bootstrap_alert_msg)) {
            $this->render_bootstrap_alert_and_redirect("history.go(" . (int)$step . ");");
        }

        echo "<script>";
        if ($this->msg_buffer !== "") {
            echo "alert(" . json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE) . ");";
        }
        echo "history.go(" . (int)$step . ");";
        echo "</script>"; 
        exit;
    }

    // 🎨 Bootstrap 스킨 UI 렌더링 헬퍼
    private function render_bootstrap_alert_and_redirect($js_action) {
        echo '
        <link rel="stylesheet" href="/path/assets/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="/path/assets/icons/bootstrap-icons.min.css">
        <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080; width: 90%; max-width: 420px; margin-top: 20px;">
            <div class="alert alert-dark alert-dismissible fade show shadow-lg border-secondary rounded-3 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                <div class="fw-semibold">' . $this->bootstrap_alert_msg . '</div>
            </div>
        </div>
        <script>
            setTimeout(function() {
                ' . $js_action . '
            }, 1200);
        </script>';
        exit;
    }

    public function confirm($question, $ok_url, $cancel_url = "javascript:history.back();") {
        echo "<script>";
        if ($this->msg_buffer !== "") echo "alert(" . json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE) . ");";
        echo "if(confirm(" . json_encode($question, JSON_UNESCAPED_UNICODE) . ")) {";
        echo "location.replace(" . json_encode($ok_url, JSON_UNESCAPED_SLASHES) . ");";
        echo "} else {";
        echo "location.replace(" . json_encode($cancel_url, JSON_UNESCAPED_SLASHES) . ");";
        echo "}";
        echo "</script>";
        $this->msg_buffer = ""; exit;
    }

    public function close() {
        echo "<script>";
        if ($this->msg_buffer !== "") echo "alert(" . json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE) . ");";
        echo "window.close();";
        echo "</script>";
        exit;
    }

    public function refresh() {
        echo "<script>";
        if ($this->msg_buffer !== "") echo "alert(" . json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE) . ");";
        echo "location.reload();";
        echo "</script>";
        exit;
    }

    public function __destruct() {
        if (!$this->is_executed && $this->msg_buffer !== "") {
            echo "<script>alert(".json_encode($this->msg_buffer, JSON_UNESCAPED_UNICODE).");</script>";
        }
    }
}

if (!function_exists('http_pq')) {
    function http_pq() { static $h = null; if (!$h) $h = new HttpMaker(); return $h; }
}
if (!function_exists('http')) {
    function http() { return http_pq(); }
}
?>