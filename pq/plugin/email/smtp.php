<?php
/**
 * =========================================================
 * PQ Engine SMTP Driver with Full Response Validation
 * FILENAME  : /pq/plugin/email/smtp.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

class PQ_SMTP {
    private $config;
    private $socket;

    public function __construct($config) {
        $this->config = $config;
    }

    public function sendPayload($data) {
        $host = $this->config['host'] ?? 'mw-002.cafe24.com';
        $port = intval($this->config['port'] ?? 25);
        $timeout = $this->config['timeout'] ?? 15;

        // [포인트 5 반영] 기본값은 보안 검증 활성화(true), 필요시 config에서 false 지정
        $verifyPeer = $this->config['verify_peer'] ?? true;

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => $verifyPeer,
                'verify_peer_name'  => $verifyPeer,
                'allow_self_signed' => !$verifyPeer,
                'ciphers'           => 'DEFAULT@SECLEVEL=1:DEFAULT:HIGH:!DH'
            ]
        ]);

        $remote = ($port === 465) ? "ssl://{$host}:{$port}" : "tcp://{$host}:{$port}";
        $this->socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$this->socket) {
            return ['success' => false, 'message' => "SMTP 서버 연결 실패: {$errstr} ({$errno})"];
        }

        // 220 웰컴 응답 검증
        $greeting = $this->getResponse();
        if (substr($greeting, 0, 3) !== '220') {
            return $this->fail("서버 응답 오류: " . trim($greeting));
        }

        // EHLO 전송 및 응답 검증 (250)
        $ehloRes = $this->sendCommand("EHLO " . gethostname());
        if (substr($ehloRes, 0, 3) !== '250') {
            return $this->fail("EHLO 핸드셰이크 실패: " . trim($ehloRes));
        }

        // STARTTLS (587 포트 대응)
        if ($port === 587) {
            $tlsRes = $this->sendCommand("STARTTLS");
            if (substr($tlsRes, 0, 3) === '220') {
                $crypto_methods = STREAM_CRYPTO_METHOD_TLS_CLIENT
                                | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

                if (!@stream_socket_enable_crypto($this->socket, true, $crypto_methods)) {
                    return $this->fail("TLS 암호화 협상 실패");
                }
                // TLS 성공 후 EHLO 재전송
                $ehloRes = $this->sendCommand("EHLO " . gethostname());
                if (substr($ehloRes, 0, 3) !== '250') {
                    return $this->fail("TLS 후 EHLO 실패");
                }
            } else {
                return $this->fail("STARTTLS 명령 거절: " . trim($tlsRes));
            }
        }

        // AUTH LOGIN 인증 검증
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';

        $authRes = $this->sendCommand("AUTH LOGIN");
        if (substr($authRes, 0, 3) === '334') {
            $userRes = $this->sendCommand(base64_encode($username));
            if (substr($userRes, 0, 3) !== '334') {
                return $this->fail("SMTP 계정명(Username) 거부");
            }

            $passRes = $this->sendCommand(base64_encode($password));
            if (substr($passRes, 0, 3) !== '235') {
                return $this->fail("SMTP 비밀번호 인증 실패");
            }
        } else {
            return $this->fail("AUTH LOGIN 명령 미지원 또는 실패");
        }

        // [포인트 1 반영] MAIL FROM 응답 코드(250) 검증
        $fromEmail = $data['from']['email'] ?? $username;
        $mailRes = $this->sendCommand("MAIL FROM:<{$fromEmail}>");
        if (substr($mailRes, 0, 3) !== '250') {
            return $this->fail("발신자(MAIL FROM) 거부: " . trim($mailRes));
        }

        // [포인트 1 반영] RCPT TO 수신자 응답 코드(250, 251) 검증
        $allRecipients = array_merge($data['to'] ?? [], $data['cc'] ?? [], $data['bcc'] ?? []);
        $validRcptCount = 0;

        foreach ($allRecipients as $rcpt) {
            if (!empty($rcpt['email'])) {
                $rcptRes = $this->sendCommand("RCPT TO:<{$rcpt['email']}>");
                $code = substr($rcptRes, 0, 3);
                if ($code === '250' || $code === '251') {
                    $validRcptCount++;
                }
            }
        }

        if ($validRcptCount === 0) {
            return $this->fail("유효한 수신자(RCPT TO)가 없습니다.");
        }

        // [포인트 1 반영] DATA 전송 준비 응답 코드(354) 검증
        $dataRes = $this->sendCommand("DATA");
        if (substr($dataRes, 0, 3) !== '354') {
            return $this->fail("DATA 전송 준비 거절: " . trim($dataRes));
        }

        // 헤더 가공
        $fromName = $data['from']['name'] ?? '';
        $headers  = "From: " . ($fromName ? "=?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>" : "<{$fromEmail}>") . "\r\n";

        // To 헤더
        $toArr = [];
        foreach ($data['to'] as $t) {
            $toArr[] = !empty($t['name']) ? "=?UTF-8?B?" . base64_encode($t['name']) . "?= <{$t['email']}>" : "<{$t['email']}>";
        }
        $headers .= "To: " . implode(', ', $toArr) . "\r\n";

        // Cc 헤더
        if (!empty($data['cc'])) {
            $ccArr = [];
            foreach ($data['cc'] as $c) {
                $ccArr[] = !empty($c['name']) ? "=?UTF-8?B?" . base64_encode($c['name']) . "?= <{$c['email']}>" : "<{$c['email']}>";
            }
            $headers .= "Cc: " . implode(', ', $ccArr) . "\r\n";
        }

        $headers .= "Subject: =?UTF-8?B?" . base64_encode($data['subject']) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        // MIME 및 본문 빌드
        $hasAttachments = !empty($data['attachments']);
        $body = "";

        if ($hasAttachments) {
            $boundary = "----=_NextPart_" . md5(uniqid(time()));
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n\r\n";

            // 본문 영역
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $body .= chunk_split(base64_encode($data['body_html'] ?: $data['body_text'])) . "\r\n";

            // [포인트 2 반영] 첨부파일 한글 및 특수문자 호환성 (RFC 2047 + filename* 확장 파라미터)
            foreach ($data['attachments'] as $att) {
                if (file_exists($att['path'])) {
                    $fileName = $att['name'] ?: basename($att['path']);
                    $fileData = file_get_contents($att['path']);

                    $encodedName = "=?UTF-8?B?" . base64_encode($fileName) . "?=";
                    $rfc2231Name = rawurlencode($fileName);

                    $body .= "--{$boundary}\r\n";
                    $body .= "Content-Type: application/octet-stream; name=\"{$encodedName}\"\r\n";
                    $body .= "Content-Disposition: attachment; filename=\"{$encodedName}\"; filename*=UTF-8''{$rfc2231Name}\r\n";
                    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $body .= chunk_split(base64_encode($fileData)) . "\r\n";
                }
            }
            $body .= "--{$boundary}--\r\n";
        } else {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body = $data['body_html'] ?: $data['body_text'];
        }

        // [포인트 4 반영] SMTP Dot-stuffing 처리
        // 줄 맨 앞에 점(.) 하나만 오는 경우 ..으로 치환하여 DATA 조기 종료 방지
        $fullMessage = $headers . $body;
        $fullMessage = preg_replace('/^\./m', '..', $fullMessage);

        // DATA 전송 및 최종 수신 확인 (250)
        $this->sendCommand($fullMessage . "\r\n.");
        $finalRes = $this->getResponse();

        if (substr($finalRes, 0, 3) !== '250') {
            return $this->fail("메일 본문 전송 실패: " . trim($finalRes));
        }

        $this->sendCommand("QUIT");
        @fclose($this->socket);

        return ['success' => true, 'message' => '발송 성공'];
    }

    private function fail($msg) {
        if ($this->socket) {
            @fputs($this->socket, "QUIT\r\n");
            @fclose($this->socket);
        }
        return ['success' => false, 'message' => $msg];
    }

    private function sendCommand($cmd) {
        fputs($this->socket, $cmd . "\r\n");
        return $this->getResponse();
    }

    private function getResponse() {
        $response = "";
        while ($str = fgets($this->socket, 512)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }
}
?>