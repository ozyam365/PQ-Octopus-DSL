<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/file.php 
 * COMPONENT : PQ Engine File Matrix Core (v1.2.6 Security Double Lock)
 * =========================================================
 */
class FileMaker {
    private $target_path = "";
    private $temp_file = null;
    private $new_name = null;
    private $allowed_exts = [];
    private $allowed_mimes = []; 
    private $max_size = 0;

    public function exists($f) { return $this->has($f); }

    // --- [1. 업로드/설정 장비] ---
	public function upload($field) {
		// 1. 다중 파일 배열인지 확인 (배열이면 index 0부터 시작하도록 처리)
		if (isset($_FILES[$field]) && is_array($_FILES[$field]['name'])) {
			// 다중 파일인 경우, 현재 엔진 구조에 맞춰 첫 번째 파일을 기본값으로 잡거나
			// 필요시 특정 인덱스를 지정하는 로직을 추가
			$this->temp_file = [
				'name'     => $_FILES[$field]['name'][0], // 일단 0번 인덱스 기본 처리
				'type'     => $_FILES[$field]['type'][0],
				'tmp_name' => $_FILES[$field]['tmp_name'][0],
				'error'    => $_FILES[$field]['error'][0],
				'size'     => $_FILES[$field]['size'][0]
			];
		} else {
			// 단일 파일은 기존 방식 유지
			$this->temp_file = $_FILES[$field] ?? null;
		}
		return $this;
	}

	// 2. [추가] 인덱스 지정용 메서드 (루프 구조에 완벽 대응)
	public function uploadIndex($field, $i) {
		if (isset($_FILES[$field]['name'][$i])) {
			$this->temp_file = [
				'name'     => $_FILES[$field]['name'][$i],
				'type'     => $_FILES[$field]['type'][$i],
				'tmp_name' => $_FILES[$field]['tmp_name'][$i],
				'error'    => $_FILES[$field]['error'][$i],
				'size'     => $_FILES[$field]['size'][$i]
			];
		}
		return $this;
	}
    
    public function path($p) { 
        $this->target_path = rtrim($p, '/') . '/'; 
        if(!is_dir($this->target_path)) $this->mkdir($this->target_path); 
        return $this; 
    }

    public function allow($exts) { 
        $list = is_array($exts) ? $exts : explode(',', $exts); 
        $this->allowed_exts = array_map('strtolower', array_map('trim', $list));
        return $this; 
    }

    public function limit($size_str) { 
        if (is_numeric($size_str)) { $this->max_size = (int)$size_str * 1024 * 1024; return $this; }
        $unit = strtoupper(preg_replace('/[^A-Z]/', '', $size_str));
        $val = (int)preg_replace('/[^0-9]/', '', $size_str);
        switch($unit) {
            case 'GB': case 'G': $val *= 1024 * 1024 * 1024; break;
            case 'MB': case 'M': $val *= 1024 * 1024; break;
            case 'KB': case 'K': $val *= 1024; break;
        }
        $this->max_size = $val;
        return $this; 
    }

    public function rename($name) { 
        $ext = $this->ext($name);
        $this->new_name = $ext ? $this->name($name) : $name; 
        return $this; 
    }

    public function random() { $this->new_name = bin2hex(random_bytes(8)); return $this; }
    
    /**
     * 내부적으로 이미지 확장자명과 매칭되는 순수 바이너리 MIME 화이트리스트
     */
    public function image() { 
        $this->allow(['jpg','jpeg','png','gif','webp']); 
        $this->allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return $this; 
    }

    /**
     * save 액추에이터
     */
    public function save() {
        if (!$this->temp_file || $this->temp_file['error'] !== 0) return false;
        if ($this->max_size > 0 && $this->temp_file['size'] > $this->max_size) return false;
        
        $ext = $this->ext($this->temp_file['name']);
        if (!empty($this->allowed_exts) && !in_array($ext, $this->allowed_exts)) return false;

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $real_mime = finfo_file($finfo, $this->temp_file['tmp_name']);
            finfo_close($finfo);

            // 1차 차단: 악성 웹셸 블랙리스트 필터링
            $blacklist_mimes = ['text/php', 'text/x-php', 'application/x-httpd-php', 'application/octet-stream/php'];
            if (in_array($real_mime, $blacklist_mimes)) return false;

            // 2차 차단: image() 격발 시 이미지 사칭 변조 우회 파일 파쇄 (.exe renamed .jpg 영구 진압)
            if (!empty($this->allowed_mimes) && !in_array($real_mime, $this->allowed_mimes)) {
                return false;
            }
        }

        $name = ($this->new_name ?? $this->name($this->temp_file['name'])) . "." . $ext;
        $name = $this->safeName($name);
        
        $full_path = $this->target_path . $name;
        if (move_uploaded_file($this->temp_file['tmp_name'], $full_path)) return $name;
        return false;
    }

    // --- [2. 파일/폴더 핸들링] ---
    public function read($f) { return file_exists($f) ? file_get_contents($f) : false; }
    public function write($f, $d) { return file_put_contents($f, $d); }
    public function append($f, $d) { return file_put_contents($f, $d, FILE_APPEND); }
    
    public function delete($f) { 
        if (!file_exists($f)) return false;
        return is_dir($f) ? @rmdir($f) : @unlink($f); 
    }
    
    public function copy($s, $t) { return copy($s, $t); }
    public function move($s, $t) { return rename($s, $t); }
    public function has($f) { return ($f && file_exists($f)); }

	public function clear($dir) {
        if (!is_dir($dir)) return false;
        $files = $this->scan($dir);
        rsort($files); 
        foreach ($files as $f) $this->delete($f);
        return true;
    }
    
    public function touch($f) { return touch($f); }

    // --- [3. 수색/리스팅] ---
    public function mkdir($p) { if (!is_dir($p)) mkdir($p, 0777, true); return $this; }
    
    public function listdir($p) { 
        if (!is_dir($p)) return [];
        return array_values(array_diff(scandir($p), ['.', '..'])); 
    }
    
    public function scan($dir) {
        if (!is_dir($dir)) return [];
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = [];
        foreach (new RecursiveIteratorIterator($it, RecursiveDirectoryIterator::CHILD_FIRST) as $f) {
            $files[] = $f->getPathname();
        }
        return $files;
    }

		// --- [4. 정보 추출] ---
	public function size($f) { return file_exists($f) ? filesize($f) : 0; }
	public function ext($f) { return strtolower(pathinfo($f, PATHINFO_EXTENSION)); }
	public function name($f) { return pathinfo($f, PATHINFO_FILENAME); }
	public function dirName($f) { return pathinfo($f, PATHINFO_DIRNAME); } // 메서드명 변경 (dir -> dirName)

	// --- [추가] 폴더/디렉토리 전용 체크 및 경로 반환 ---
	public function isDir($p) { return is_dir($p); }
	public function dir($f = null) { 
		if ($f === null) return $this; // 체이닝 유지용
		return pathinfo($f, PATHINFO_DIRNAME); 
	}
    public function mimeType($f) { 
        if (file_exists($f) && function_exists('mime_content_type')) return mime_content_type($f);
        $mimes = [
            'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif',
            'pdf'=>'application/pdf','txt'=>'text/plain','zip'=>'application/zip',
            'html'=>'text/html','css'=>'text/css','js'=>'application/javascript'
        ];
        return $mimes[$this->ext($f)] ?? 'application/octet-stream';
    }
    
    public function modified($f) { return file_exists($f) ? filemtime($f) : 0; }
    
// 2. [한글/다국어 지원 safeName]
    public function safeName($n) {
        $name = pathinfo($n, PATHINFO_FILENAME);
        $ext  = pathinfo($n, PATHINFO_EXTENSION);
        // 특수문자 제거 (한글/일본어/중국어는 보존)
        $name = preg_replace('/[^\p{L}\p{N}\-_]/u', '_', $name);
        return $name . ($ext ? '.' . strtolower($ext) : '');
    }

    // --- [5. 출력/스트림] ---
    public function download($f, $n = null) {
        if (!$this->has($f)) return false;
        if (headers_sent()) return false;
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.($n ?? basename($f)).'"');
        header('Content-Length: ' . $this->size($f));
        header('Pragma: public');
        readfile($f); exit;
    }
    
    public function inline($f) {
        if (!$this->has($f)) return false;
        header('Content-Type: ' . $this->mimeType($f));
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . $this->size($f));
        readfile($f); exit;
    }
    
    public function stream($f) {
        if (!$this->has($f)) return false;
        while (ob_get_level()) ob_end_clean();
        $fp = fopen($f, 'rb');
        header("Content-Type: " . $this->mimeType($f));
        header('Content-Length: ' . $this->size($f));
        while (!feof($fp)) { echo fread($fp, 8192); flush(); }
        fclose($fp); exit;
    }

// 1. [의존성 제거] 
    public function url($path, $name) {
        return '/' . ltrim($path, '/') . '/' . ltrim($name, '/');
    }
	//썸네일
	public function thumbnail($src, $dst, $w, $h, $per = false) {
        return $this->makeImage($src, $dst, $w, $h, $per);
    }	
	private function makeImage($src, $dst, $width, $height, $per = false) {
			if (!file_exists($src)) return false;
			@ini_set('memory_limit', '256M');
			$info = getimagesize($src);
			if (!$info) return false;
			list($orig_w, $orig_h, $type) = $info;

			if ($per) {
				$ratio = min($width / $orig_w, $height / $orig_h);
				$width = (int)($orig_w * $ratio);
				$height = (int)($orig_h * $ratio);
			}

			$src_img = match($type) {
				IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
				IMAGETYPE_PNG  => @imagecreatefrompng($src),
				IMAGETYPE_GIF  => @imagecreatefromgif($src),
				default        => false
			};
			if (!$src_img) return false;

			$dst_img = imagecreatetruecolor($width, $height);
			if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
				imagealphablending($dst_img, false);
				imagesavealpha($dst_img, true);
			}

			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width, $height, $orig_w, $orig_h);
			
			$success = match($type) {
				IMAGETYPE_JPEG => imagejpeg($dst_img, $dst, 85),
				IMAGETYPE_PNG  => imagepng($dst_img, $dst, 6),
				IMAGETYPE_GIF  => imagegif($dst_img, $dst),
				default        => false
			};

			imagedestroy($src_img);
			imagedestroy($dst_img);
			return $success;
	}	
}

/**
 * [PQ Engine Image Matrix] 최신화된 이미지 리사이징/썸네일 생성기
 */
function file_pq() { static $f; if (!$f) $f = new FileMaker(); return $f; }
?>
