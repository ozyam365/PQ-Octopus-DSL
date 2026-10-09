<?php
/**
 * =========================================================
 * PQ HTTP Client & Browser Controller Engine
 * FILENAME  : /pq/core/http.php
 * UPDATE :  2026-10-09 PM 10:24
 * =========================================================
 */

class HttpMaker {
    private $headers = [];
    private $params = [];
    private $timeout = 5;
    private $body = null;
    private $msg_buffer = "";
    private $bootstrap_alert_msg = ""; // Bootstrap Alert UI buffer
    private $is_executed = false;
    private $referer_out = null;

// --- [1. ENVIRONMENT & REQUEST INSPECTOR] ---

    public function server($key = null, $default = '') {
        if ($key === null) {
            return $_SERVER;
        }
        return $_SERVER[$key] ?? $default;
    }

    public function lang($default = 'ko-KR') {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? $default;
    }

    public function header_get($name, $default = '') {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }
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

    public function parse($url) {
        $parsed = parse_url($url);
        return ret($parsed); // ret()으로 반환하여 객체(#) 및 배열($) 접근 모두 지원
    }

    /**
     * User Agent 기반 브라우저 정보 분석
     * 리턴: ret() 객체/배열 포맷 (#bw.name, #bw.version, #bw.platform)
     */
    public function browser() {
        $u_agent = $this->agent();
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = '?';

        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer'; $ub = "MSIE";
        } elseif (preg_match('/Trident/i', $u_agent)) {
            $bname = 'Internet Explorer'; $ub = "rv";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox'; $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome'; $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari'; $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera'; $ub = "Opera";
        } else {
            $ub = "other";
        }

        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (preg_match_all($pattern, $u_agent, $matches)) {
            $i = count($matches['browser']);
            if ($i != 1) {
                if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                    $version = $matches['version'][0] ?? '?';
                } else {
                    $version = $matches['version'][1] ?? '?';
                }
            } else {
                $version = $matches['version'][0] ?? '?';
            }
        }

        return ret([
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version ?: '?',
            'platform'  => $platform
        ]);
    }

    /**
     * User Agent 기반 OS 정보 분석
     * 리턴: ret() 객체/배열 포맷 (#os.name, #os.version)
     */
    public function os() {
        $agent = strtolower($this->agent());
        $os = ['name' => 'Unknown', 'version' => ''];

        if (preg_match('/win16/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '3.11';
        } elseif (preg_match('/win95|windows 95|windows_95/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '95';
        } elseif (preg_match('/win 9x 4.90|windows me/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = 'Me';
        } elseif (preg_match('/win98|windows 98/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '98';
        } elseif (preg_match('/windows nt 5.0|windows 2000/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '2000';
        } elseif (preg_match('/windows nt 5.1|windows xp/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = 'XP';
        } elseif (preg_match('/windows nt 5.2/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = 'Server 2003';
        } elseif (preg_match('/windows nt 6.0/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = 'Vista';
        } elseif (preg_match('/windows nt 7.0|windows nt 6.1/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '7';
        } elseif (preg_match('/windows nt 10.0/i', $agent)) {
            $os['name'] = 'Windows'; $os['version'] = '10';
        } elseif (preg_match('/mac/i', $agent)) {
            $os['name'] = 'Apple';
            if (preg_match('/powerpc/i', $agent)) $os['version'] = 'PowerPC';
            elseif (preg_match('/macintosh/i', $agent)) $os['version'] = 'Macintosh';
            elseif (preg_match('/os x/i', $agent)) $os['version'] = 'OS X';
        } elseif (preg_match('/linux|x11/i', $agent)) {
            $os['name'] = 'linux';
            if (preg_match('/ubuntu/i', $agent)) $os['version'] = 'Ubuntu';
            elseif (preg_match('/android/i', $agent)) $os['version'] = 'Android';
            elseif (preg_match('/centos/i', $agent)) $os['version'] = 'CentOs';
        }

        return ret($os);
    }
    // --- [2. HTTP CLIENT PIPELINE (cURL)] ---
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

    // --- [3. BROWSER & NAVIGATION CONTROLLER] ---
    public function redirect($u) {
        if (!headers_sent()) { header("Location: $u"); exit; }
        echo "<script>location.replace(".json_encode($u, JSON_UNESCAPED_SLASHES).");</script>"; exit;
    }

    public function msg($message) {
        $this->msg_buffer = $message;
        return $this;
    }

    public function alert($msg) {
        $this->bootstrap_alert_msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function go($url) {
        $this->is_executed = true;
        if (ob_get_length()) { ob_clean(); }

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

// [ENGINE CORE] Singleton Bridge & DSL Wrapper Functions
if (!function_exists('http_pq')) {
    function http_pq() {
        static $h = null;
        if (!$h) $h = new HttpMaker();
        return $h;
    }
}

if (!function_exists('http')) {
    function http() {
        return http_pq();
    }
}
?>