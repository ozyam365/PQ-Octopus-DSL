<?php
/**
 * =========================================================
 * PQ Email Automation Plugin & Chain Builder
 * FILENAME  : /pq/plugin/email.php
 * UPDATE :  2026-10-07 PM 07:01
 * =========================================================
 */

require_once __DIR__ . '/email/smtp.php';

class PQMailResult {
    private $isSuccess;
    private $message;

    public function __construct($isSuccess, $message = '') {
        $this->isSuccess = (bool)$isSuccess;
        $this->message = $message;
    }

    public function success() {
        return $this->isSuccess;
    }

    public function message() {
        return $this->message;
    }
}

class PQMail {
    private $config;
    private $to = [];
    private $cc = [];
    private $bcc = [];
    private $replyTo = [];
    private $fromEmail;
    private $fromName;
    private $subject = '';
    private $bodyHtml = '';
    private $bodyText = '';
    private $attachments = [];

	public function __construct() {
		// 1. Web Regulation Standards /set/cfg_email.php
		$config_path = $_SERVER['DOCUMENT_ROOT'] . '/set/cfg_email.php';

		// 2. Check for file existence and load
		if (file_exists($config_path)) {
			$this->config = require $config_path;
		} else {
			// Handle exceptions or specify default values ​​if the configuration file is missing.
			throw new \Exception("The email configuration file cannot be found.: {$config_path}");
		}

		$this->fromEmail = $this->config['from_email'] ?? '';
		$this->fromName  = $this->config['from_name'] ?? '';
	}

    public function reset() {
        $this->to = [];
        $this->cc = [];
        $this->bcc = [];
        $this->replyTo = [];
        $this->fromEmail = $this->config['from_email'] ?? '';
        $this->fromName  = $this->config['from_name'] ?? '';
        $this->subject = '';
        $this->bodyHtml = '';
        $this->bodyText = '';
        $this->attachments = [];
        return $this;
    }

	 // 1. Specify the recipient (starting point of chaining)
    public function to($email, $name = '') {
        $this->reset();
        if (is_array($email)) {
            $this->to = array_merge($this->to, $email);
        } else {
            $this->to[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    public function cc($email, $name = '') {
        if (is_array($email)) {
            $this->cc = array_merge($this->cc, $email);
        } else {
            $this->cc[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    public function bcc($email, $name = '') {
        if (is_array($email)) {
            $this->bcc = array_merge($this->bcc, $email);
        } else {
            $this->bcc[] = ['email' => $email, 'name' => $name];
        }
        return $this;
    }

    public function from($email, $name = '') {
        $this->fromEmail = $email;
        if ($name) $this->fromName = $name;
        return $this;
    }

    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function text($text) {
        $this->bodyText = $text;
        return $this;
    }

    public function html($html) {
        $this->bodyHtml = $html;
        return $this;
    }

    public function attach($filePath, $fileName = '') {
        if (is_array($filePath)) {
            foreach ($filePath as $path) {
                if (file_exists($path)) $this->attachments[] = ['path' => $path, 'name' => ''];
            }
        } else if (file_exists($filePath)) {
            $this->attachments[] = ['path' => $filePath, 'name' => $fileName];
        }
        return $this;
    }

    // 2. Execute sending (returns PQMailResult object)
    public function send() {
        if (empty($this->to)) {
            return new PQMailResult(false, 'The recipient (to) address has not been specified.');
        }

        $smtp = new PQ_SMTP($this->config);
        $rawResult = $smtp->sendPayload([
            'from'        => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'to'          => $this->to,
            'cc'          => $this->cc,
            'bcc'         => $this->bcc,
            'reply_to'    => $this->replyTo,
            'subject'     => $this->subject,
            'body_html'   => $this->bodyHtml,
            'body_text'   => $this->bodyText,
            'attachments' => $this->attachments
        ]);

        $this->reset();

        $isOk = is_array($rawResult) ? ($rawResult['success'] ?? false) : (bool)$rawResult;
        $msg  = is_array($rawResult) ? ($rawResult['message'] ?? '') : '';

        return new PQMailResult($isOk, $msg);
    }
}
?>