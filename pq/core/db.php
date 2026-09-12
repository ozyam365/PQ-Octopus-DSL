<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/db.php  
 * COMPONENT : PQ Fluent Database Query Builder Core
 * =========================================================
 */

class DBMaker implements IteratorAggregate {
    public $conn = null;
    public $table = '', $wheres = [], $joins = [], $orders = [], $groups = [], $havings = [], $fields = '*', $limit = '';
    private $pending_sql = '';
    private $dist_column = null;
    
    // [CONFIG] Connection Health Check
    public function ping() {
        try { 
            $this->connect(); 
            return ($this->conn && !@$this->conn->connect_errno); 
        } catch (\Exception $e) { 
            return false; 
        }
    }

    /**
     * [CONFIG] Database Connection Initialization
     * Loads credentials from /set/cfg_db.php with fallback defaults.
     */
    public function connect() {
        if ($this->conn) return $this;
        global $_SQL_HOST, $_SQL_USER, $_SQL_PASS, $_SQL_NAME;
        
        $cfg_path = (defined('PQ_DIR') ? PQ_DIR : dirname(__FILE__, 3)) . "/set/cfg_db.php";
        if (file_exists($cfg_path)) include $cfg_path;

        // [CUSTOMIZE] Fallback Database Connection Settings
        $host = !empty($_SQL_HOST) ? $_SQL_HOST : "localhost";
        $user = !empty($_SQL_USER) ? $_SQL_USER : "root";
        $pass = isset($_SQL_PASS)  ? $_SQL_PASS : "";
        $name = !empty($_SQL_NAME) ? $_SQL_NAME : "pqengine";

        $this->conn = @mysqli_connect($host, $user, $pass, $name);
        if (!$this->conn) {
            if (class_exists('Trace')) Trace::add('ERROR', "Database connection failed: " . mysqli_connect_error());
            return $this;
        }
        mysqli_set_charset($this->conn, "utf8mb4");
        return $this;
    }

    // =========================================================
    // [ENGINE CORE] Query Builder State Management & Magic Forwarding
    // =========================================================

    private function useTable($name) {
        if (str_starts_with($name, '@')) {
            $var_name = ltrim($name, '@');
            global ${$var_name};
            $this->table = ${$var_name};
        } else {
            $this->table = $name;
        }
    
        $this->wheres = [];
        $this->joins = [];
        $this->orders = [];
        $this->groups = [];
        $this->havings = [];
        $this->fields = '*';
        $this->limit = '';
        $this->pending_sql = '';
        
        return $this;
    }

    public function __get($name) { 
        return $this->useTable($name); 
    }

    public function __invoke($name) { 
        return $this->useTable($name); 
    }

    // =========================================================
    // Aggregate Query Functions
    // =========================================================

    public function min($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "MIN({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return is_numeric($row[0]) ? (stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0]) : $row[0];
    }

    public function max($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "MAX({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return is_numeric($row[0]) ? (stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0]) : $row[0];
    }

    public function sum($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "SUM({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        if (!$row || $row[0] === null) return 0;
        return stripos($row[0], '.') !== false ? (float)$row[0] : (int)$row[0];
    }

    public function avg($field) {
        $clean_f = '`' . $this->escape($field) . '`';
        $this->fields = "AVG({$clean_f})";
        
        $res = $this->execute_pending();
        $row = mysqli_fetch_row($res);
        return $row ? (float)$row[0] : 0.0;
    }

    public function count($field = '*') {
        $trimmed = trim($field);
        
        if (strncasecmp($trimmed, 'select', 6) === 0) {
            $this->pending_sql = $field;
            $res = $this->execute_pending();
            $row = $res ? mysqli_fetch_row($res) : null;
            return $row ? (int)$row[0] : 0;
        }
        
        $clean_f = ($field === '*') ? '*' : '`' . $this->escape($field) . '`';
        $this->fields = "COUNT({$clean_f})";
        
        $res = $this->execute_pending();
        $row = $res ? mysqli_fetch_row($res) : null;
        return $row ? (int)$row[0] : 0;
    }

    // =========================================================
    // Data Retrieval Functions
    // =========================================================

    public function row($sql_or_type = null, $type = "obj") {
        $trimmed = is_string($sql_or_type) ? trim($sql_or_type) : '';
        
        if (strncasecmp($trimmed, 'select', 6) === 0) {
            if (stripos($trimmed, 'LIMIT') === false) {
                $trimmed .= " LIMIT 1";
            }
            $this->pending_sql = $trimmed;
            $target_type = $type;
        } else {
            $this->limit = 1;
            $target_type = $sql_or_type;
        }

        $res = $this->execute_pending();
        $data = $res ? mysqli_fetch_assoc($res) : null;

        if ($target_type === "arr" || $target_type === "array") return $data ?? [];
        if ($target_type === "obj" || $target_type === "object") return $data ? (object)$data : null;

        return ret($data);
    }

    public function list($sql = "") {
        if ($sql) $this->pending_sql = $sql;
        $res = $this->execute_pending();
        $rows = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) $rows[] = $row; 
        }
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
        return ret($list);
    }

    public function pluck($field) { 
        $list = []; 
        foreach($this as $row) { 
            if (is_object($row)) $list[] = $row->{$field} ?? null;
            else if (is_array($row)) $list[] = $row[$field] ?? null;
        } 
        return $list; 
    }

    public function value($field, $default = null) {
        $row = $this->select($field)->row();
        if (!$row) return $default;

        $arr = is_object($row) && method_exists($row, 'array') ? $row->array() : (array)$row;
        return isset($arr[$field]) && $arr[$field] !== null ? $arr[$field] : $default;
    }

    // =========================================================
    // Query Builder Chaining Methods
    // =========================================================

    public function select($fields = '*') {
        if (!empty($fields)) {
            if (is_array($fields)) {
                $this->fields = implode(', ', $fields);
            } else {
                $this->fields = trim($fields);
            }
        }
        return $this;
    }

    public function where($w) { 
        if ($w) $this->wheres[] = (empty($this->wheres) ? "" : "AND ") . $w; 
        return $this; 
    }

    public function and($w) { return $this->where($w); }

    public function or($w) {
        if (empty($this->wheres)) return $this->where($w);
        $this->wheres[] = "OR " . $w;
        return $this;
    }

    public function dist($column = null) {
        if (!empty($column)) {
            $this->fields = "DISTINCT `" . $this->escape(trim($column)) . "`";
        } else {
            $this->fields = "DISTINCT *";
        }
        return $this;
    }

    public function group($g) {
        if ($g) $this->groups[] = "`" . $this->escape(trim($g)) . "`";
        return $this;
    }

    public function having($h) {
        if ($h) $this->havings[] = (empty($this->havings) ? "" : "AND ") . $h;
        return $this;
    }

    public function like($f, $v) {
        if (!empty($v)) $this->wheres[] = (empty($this->wheres) ? "" : "AND ") . "`$f` LIKE '%" . $this->escape($v) . "%'";
        return $this;
    }

    public function limit($start, $count = null) {
        $this->limit = ($count === null) ? (int)$start : (int)$start . ", " . (int)$count;
        return $this;
    }

    public function join($t, $c, $type = "INNER") { 
        $this->joins[] = " $type JOIN `$t` ON $c"; 
        return $this; 
    }

    public function order($o) {
        if ($o) $this->orders[] = $o;
        return $this;
    }

    public function sort($s, $d = "DESC") { 
        $clean_s = trim($s);
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $clean_s)) {
            throw new \RuntimeException("PQ DB Security Error: Invalid sort column syntax.");
        }
        $direction = strtoupper(trim($d)) === "ASC" ? "ASC" : "DESC";
        $this->orders[] = "`$clean_s` $direction";
        return $this; 
    }

    // =========================================================
    // Mutation Queries (Insert, Update, Delete)
    // =========================================================

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
        return $this;
    }

    public function plus($field, $amount = 1) {
        $clean_f = '`' . $this->escape($field) . '`';
        $w = !empty($this->wheres) ? " WHERE " . $this->build_where() : "";
        $this->pending_sql = "UPDATE `{$this->table}` SET {$clean_f} = {$clean_f} + " . (int)$amount . $w;
        return $this->execute_pending();
    }

    public function minus($field, $amount = 1) {
        $clean_f = '`' . $this->escape($field) . '`';
        $w = !empty($this->wheres) ? " WHERE " . $this->build_where() : "";
        $this->pending_sql = "UPDATE `{$this->table}` SET {$clean_f} = {$clean_f} - " . (int)$amount . $w;
        return $this->execute_pending();
    }

    public function delete() {
        return $this->dquery($this->table, $this->build_where());
    }

    public function insert_id() {
        $this->connect();
        return mysqli_insert_id($this->conn);
    }

    // =========================================================
    // Table & Schema Utilities
    // =========================================================

    public function has($t = null) {
        $this->connect();
        if (!$this->conn) return false;
        $t = $t ?: $this->table;
        $res = mysqli_query($this->conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($this->conn, $t) . "'");
        return ($res && mysqli_num_rows($res) > 0);
    }
    
    public function make($schema_file = null) {
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
        foreach (explode(';', $clean_query) as $q) {
            if (trim($q)) mysqli_query($this->conn, trim($q));
        }
        return true;
    }
    
    public function clear() { 
        if (class_exists('Trace')) Trace::add('WARN', "DB Table Truncate: `{$this->table}`");
        $this->pending_sql = "TRUNCATE TABLE `{$this->table}`"; 
        return $this->execute_pending(); 
    }

    // =========================================================
    // [ENGINE CORE] Execution Pipeline & Iteration
    // =========================================================

    private function escape($v) { 
        $this->connect();
        return ($this->conn && $v !== null) ? mysqli_real_escape_string($this->conn, (string)$v) : ""; 
    }

    private function build_where() {
        if (empty($this->wheres)) return "";
        return trim(preg_replace('/^\s*(AND|OR)\s+/i', '', implode(" ", $this->wheres)));
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

/**
 * [ENGINE CORE] Singleton Bridge Function for db()
 */
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