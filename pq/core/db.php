<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/db.php  
 * COMPONENT : PQ database 
 * =========================================================
 */
class DBMaker implements IteratorAggregate {
    public $conn = null;
    public $table = '', $wheres = [], $joins = [], $orders = [], $groups = [], $havings = [], $fields = '*', $limit = '';
    private $pending_sql = '';
	private $dist_column = null;
	
    public function ping() {
        try { $this->connect(); return ($this->conn && !@$this->conn->connect_errno); } 
        catch (Exception $e) { return false; }
    }

    public function connect() {
        if ($this->conn) return $this;
        global $_SQL_HOST, $_SQL_USER, $_SQL_PASS, $_SQL_NAME;
        $cfg_path = (defined('PQ_DIR') ? PQ_DIR : dirname(__FILE__, 3)) . "/set/cfg_db.php";
        if (file_exists($cfg_path)) include $cfg_path;

        $host = !empty($_SQL_HOST) ? $_SQL_HOST : "localhost";
        $user = !empty($_SQL_USER) ? $_SQL_USER : "root";
        $pass = isset($_SQL_PASS) ? $_SQL_PASS : "";
        $name = !empty($_SQL_NAME) ? $_SQL_NAME : "pqengine";

        $this->conn = @mysqli_connect($host, $user, $pass, $name);
        if (!$this->conn) {
            if (class_exists('Trace')) Trace::add('ERROR', "DB 연결 실패: " . mysqli_connect_error());
            return $this;
        }
        mysqli_set_charset($this->conn, "utf8mb4");
        return $this;
    }

    // -------------------------------------------------------------
    //  db.memo와 db("memo")지원
    // -------------------------------------------------------------
    private function useTable($name) {
        // 1. @로 시작하면 동적 변수로 간주하여 전역 스코프에서 테이블명 추출
        if (str_starts_with($name, '@')) {
            $var_name = ltrim($name, '@');
            global ${$var_name};
            $this->table = ${$var_name};
        } else {
            $this->table = $name;
        }
    
        // 2. 쿼리 빌더 자원 통합 초기화 
        $this->wheres = [];
        $this->joins = [];
        $this->orders = [];
        $this->groups = [];
        $this->havings = [];
        $this->fields = '*';
        $this->limit = '';
        $this->pending_sql = '';
        
        return $this; // 메서드 체이닝 연속성 보장
    }

    // [매직 메서드 1] db.table_name 스타일 포워딩
    public function __get($name) { 
        return $this->useTable($name); 
    }

    // [매직 메서드 2] db("table_name") 스타일 포워딩
    public function __invoke($name) { 
        return $this->useTable($name); 
    }

    // 2. MIN (최소값)
    public function min($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "MIN({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return is_numeric($row[0]) ? (stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0]) : $row[0];
    }

    // 3. MAX (최대값)
    public function max($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "MAX({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return is_numeric($row[0]) ? (stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0]) : $row[0];
    }

    // 4. SUM (합계)
    public function sum($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "SUM({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0];
    }

    // 5. AVG (평균)
    public function avg($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "AVG({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        return $row ? (float)$row[0] : 0.0;
    }
	public function row($sql_or_type = null, $type = "obj") {
		$trimmed = is_string($sql_or_type) ? trim($sql_or_type) : '';
		
		// 1. SELECT Raw SQL인 경우
		if (strncasecmp($trimmed, 'select', 6) === 0) {
			if (stripos($trimmed, 'LIMIT') === false) {
				$trimmed .= " LIMIT 1";
			}
			$this->pending_sql = $trimmed;
			$target_type = $type;
		} 
		// 2. 쿼리 빌더 체이닝인 경우
		else {
			$this->limit = 1;
			$target_type = $sql_or_type;
		}

		$res = $this->execute_pending();
		$data = $res ? mysqli_fetch_assoc($res) : null;

		// 💡 하위 호환성 유지: 인자로 "arr"나 "obj"를 명시한 경우 기존 방식대로 반환
		if ($target_type === "arr" || $target_type === "array") return $data ?? [];
		if ($target_type === "obj" || $target_type === "object") return $data ? (object)$data : null;

		// 🚀 기본 반환: ret() 인스턴스 반환 -> .array(), .object(), .json() 체이닝 가능!
		return ret($data);
	}
	// 2. 카운트 집계 기능형함수
	public function count($field = '*') {
		$trimmed = trim($field);
		
		// 💡 [지능형 분기] 인자가 'SELECT'로 시작하면 Raw SQL로 직접 실행!
		if (strncasecmp($trimmed, 'select', 6) === 0) {
			$this->pending_sql = $field;
			$res = $this->execute_pending();
			$row = $res ? mysqli_fetch_row($res) : null;
			return $row ? (int)$row[0] : 0;
		}
		
		// 💡 기존 쿼리 빌더 체이닝 처리
		$clean_f = ($field === '*') ? '*' : '`' . $this->escape($field) . '`';
		$this->fields = "COUNT({$clean_f})";
		
		$res = $this->execute_pending();
		$row = $res ? mysqli_fetch_row($res) : null;
		return $row ? (int)$row[0] : 0;
	}
	public function list($sql = "") {
		if ($sql) $this->pending_sql = $sql;
		$res = $this->execute_pending();
		$rows = [];
		if ($res) {
			while ($row = mysqli_fetch_assoc($res)) $rows[] = $row; 
		}
		// 🚀 ret() 반환
		return ret($rows);
	}

	public function query($sql) {
		$this->pending_sql = $sql;
		$res = $this->execute_pending();
		if (is_bool($res)) {
			return $res; 
		}
		$list = [];
		while ($row = mysqli_fetch_assoc($res)) {
			$list[] = $row;
		}
		// 🚀 ret() 반환
		return ret($list);
	}
    function where($w) { 
        if ($w) $this->wheres[] = (empty($this->wheres) ? "" : "AND ") . $w; 
        return $this; 
    }

    function and($w) { return $this->where($w); }

    function or($w) {
        if (empty($this->wheres)) return $this->where($w);
        $this->wheres[] = "OR " . $w;
        return $this;
    }
	// PQ_Db 클래스 내부 수정
	public function dist($column = null) {
		if (!empty($column)) {
			// DISTINCT [컬럼] 문법 적용을 위해 select 필드를 단일 컬럼으로 교체
			$this->fields = "DISTINCT `" . $this->escape(trim($column)) . "`";
		} else {
			$this->fields = "DISTINCT *";
		}
		return $this;
	}
    // GROUP BY 체이닝 지원
    function group($g) {
        if ($g) $this->groups[] = "`" . $this->escape(trim($g)) . "`";
        return $this;
    }

    // HAVING 체이닝 지원
    function having($h) {
        if ($h) $this->havings[] = (empty($this->havings) ? "" : "AND ") . $h;
        return $this;
    }

    function like($f, $v) {
        if (!empty($v)) $this->wheres[] = (empty($this->wheres) ? "" : "AND ") . "`$f` LIKE '%" . $this->escape($v) . "%'";
        return $this;
    }

    function limit($start, $count = null) {
        $this->limit = ($count === null) ? (int)$start : (int)$start . ", " . (int)$count;
        return $this;
    }

    function join($t, $c, $type = "INNER") { $this->joins[] = " $type JOIN `$t` ON $c"; return $this; }

    public function iquery($table, $data) {
        $this->table = trim($table); 
        $data_array = (array)$data;
        
        $cols = []; $vals = [];
        foreach ($data_array as $k => $v) {
            $cols[] = "`$k`";
            $vals[] = ($v === null) ? "NULL" : "'" . $this->escape($v) . "'";
        }
        
        $this->pending_sql = "INSERT INTO `{$this->table}` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        return $this->execute_pending() ? mysqli_insert_id($this->conn) : false;
    }

    public function uquery($table, $data, $where) {
        $this->table = trim($table); 
        $data_array = (array)$data;
        $sets = [];
        
        foreach ($data_array as $k => $v) {
            $sets[] = ($v === null) ? "`$k` = NULL" : "`$k` = '".$this->escape($v)."'";
        }        
        $this->pending_sql = "UPDATE `{$this->table}` SET ".implode(', ', $sets)." WHERE $where";
        return $this->execute_pending();
    }

    public function dquery($table, $where) {
        $this->table = trim($table);
        $this->pending_sql = "DELETE FROM `{$this->table}` WHERE $where";
        return $this->execute_pending();
    }

    public function insert($data) {
        return $this->iquery($this->table, $data);
    }
	public function update($data = null) {
        if ($data !== null) {
            return $this->uquery($this->table, $data, $this->build_where());
        }
        return $this; // 체이닝 지원
    }

    /**
     * 🚀 [필드 플러스] db.table.where(...).plus("hit", 1)
     */
    public function plus($field, $amount = 1) {
        $clean_f = '`' . $this->escape($field) . '`';
        $w = !empty($this->wheres) ? " WHERE " . $this->build_where() : "";
        $this->pending_sql = "UPDATE `{$this->table}` SET {$clean_f} = {$clean_f} + " . (int)$amount . $w;
        return $this->execute_pending();
    }

    /**
     * 🚀 [필드 마이너스] db.table.where(...).minus("stock", 1)
     */
    public function minus($field, $amount = 1) {
        $clean_f = '`' . $this->escape($field) . '`';
        $w = !empty($this->wheres) ? " WHERE " . $this->build_where() : "";
        $this->pending_sql = "UPDATE `{$this->table}` SET {$clean_f} = {$clean_f} - " . (int)$amount . $w;
        return $this->execute_pending();
    }
    public function delete() {
        return $this->dquery($this->table, $this->build_where());
    }

    private function escape($v) { 
        $this->connect();
        return ($this->conn && $v !== null) ? mysqli_real_escape_string($this->conn, (string)$v) : ""; 
    }

    function pluck($field) { 
        $list = []; 
        foreach($this as $row) { 
            if (is_object($row)) $list[] = $row->{$field} ?? null;
            else if (is_array($row)) $list[] = $row[$field] ?? null;
        } 
        return $list; 
    }

	/**
	 * 🚀 [단일 필드 값 추출] db.table.where(...).value("subject", "기본값")
	 */
	public function value($field, $default = null) {
		// 1. 필요한 필드만 조회하도록 지정 후 1건 추출
		$row = $this->select($field)->row();
		
		if (!$row) return $default;

		// 2. PQRet 객체일 경우 array()로 변환하여 안전하게 키 체크
		$arr = is_object($row) && method_exists($row, 'array') ? $row->array() : (array)$row;

		// 3. 해당 필드가 존재하면 반환, 없으면 기본값 반환
		return isset($arr[$field]) && $arr[$field] !== null ? $arr[$field] : $default;
	}
    function has($t = null) {
        $this->connect();
        if (!$this->conn) return false;
        $t = $t ?: $this->table;
        $res = mysqli_query($this->conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($this->conn, $t) . "'");
        return ($res && mysqli_num_rows($res) > 0);
    }
    
	function make($schema_file = null) {
		if ($schema_file) {
			$path = $schema_file;
		} else {
			$base_dir = defined('PQ_DIR') ? PQ_DIR : dirname(__FILE__, 3);
			$path = $base_dir . "/set/" . $this->table . ".sql";
		}
		
		if (!file_exists($path)) return false;        
		$sql_lines = file($path);
		$clean_query = "";
		foreach ($sql_lines as $line) {
			$t = trim($line);
			if ($t === '' || str_starts_with($t, '--') || str_starts_with($t, '#') || preg_match('/^\/\*!.*\*\/;?$/', $t)) continue;
			$clean_query .= $line;
		}
		$this->connect();
		foreach (explode(';', $clean_query) as $q) if (trim($q)) mysqli_query($this->conn, trim($q));
		return true;
	 }
    
    function clear() { 
        if (class_exists('Trace')) Trace::add('WARN', "DB TABLE CLEAR TRUNCATE: `{$this->table}`");
        $this->pending_sql = "TRUNCATE TABLE `{$this->table}`"; 
        return $this->execute_pending(); 
    }
	public function order($o) {
		if ($o) $this->orders[] = $o;
		return $this;
	}
    private function build_where() {
        if (empty($this->wheres)) return "";
        return trim(preg_replace('/^\s*(AND|OR)\s+/i', '', implode(" ", $this->wheres)));
    }
    
    function sort($s, $d = "DESC") { 
        $clean_s = trim($s);
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $clean_s)) throw new \RuntimeException("PQ DB Security Panic");
        $direction = strtoupper(trim($d)) === "ASC" ? "ASC" : "DESC";
        $this->orders[] = "`$clean_s` $direction";
        return $this; 
    }

    public function insert_id() {
        $this->connect();
        return mysqli_insert_id($this->conn);
    }
/**
     * 🚀 [조회 필드 지정] db.table.select("note, attach_files").where(...)
     */
    public function select($fields = '*') {
        if (!empty($fields)) {
            // 배열로 들어올 경우 문자열 합치기 지원
            if (is_array($fields)) {
                $this->fields = implode(', ', $fields);
            } else {
                $this->fields = trim($fields);
            }
        }
        return $this; // 메서드 체이닝 반환
    }
    private function execute_pending() {
        if (empty($this->pending_sql)) {
            $w = !empty($this->wheres) ? " WHERE " . $this->build_where() : "";
            $group_sql = !empty($this->groups) ? " GROUP BY " . implode(", ", $this->groups) : "";
            $having_sql = !empty($this->havings) ? " HAVING " . implode(" AND ", $this->havings) : "";
            $order_sql = !empty($this->orders) ? " ORDER BY " . implode(", ", $this->orders) : "";
            
            $this->pending_sql = "SELECT {$this->fields} FROM `{$this->table}`" 
                . (implode(" ", $this->joins)) 
                . $w 
                . $group_sql
                . $having_sql
                . $order_sql 
                . ($this->limit ? " LIMIT ".$this->limit : "");
        }

        $this->connect();
        if (class_exists('Trace')) Trace::add('SQL', $this->pending_sql);
        $res = mysqli_query($this->conn, $this->pending_sql);
        $this->pending_sql = ''; 
        return $res;
    }

    public function getIterator(): \Traversable {
        $res = $this->execute_pending();
        if (!$res) return new ArrayIterator([]);
        $rows = [];
        while ($row = mysqli_fetch_assoc($res)) $rows[] = (object)$row; 
        return new ArrayIterator($rows);
    }
}

if (!function_exists("db")) {
    function db($table_name = null) {
        static $i;
        if (!$i) $i = new DBMaker();
        
        if ($table_name !== null) {
            return $i($table_name);
        }
        return $i;
    }
}
?>