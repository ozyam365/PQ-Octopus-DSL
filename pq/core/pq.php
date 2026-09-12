<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/pq.php
 * COMPONENT : PQ System Utilities & Core Namespace Engine
 * =========================================================
 */

class PQCore {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * PQ DSL Plugin Loader Pipeline
     * Usage: pq().use("chat") or pq().use("chat").use("api") or pq().use(["chat", "api"])
     */
    public function use($plugin_name) {
        if (is_array($plugin_name)) {
            foreach ($plugin_name as $p) {
                $this->use($p);
            }
            return $this;
        }

        // Internal plugin loader invocation
        if (function_exists('plugin_load')) {
            plugin_load($plugin_name);
        } else {
            $plugin_path = defined('PQ_PATH') ? PQ_PATH . "/plugin/{$plugin_name}.php" : __DIR__ . "/../plugin/{$plugin_name}.php";
            if (file_exists($plugin_path)) {
                require_once $plugin_path;
            }
        }

        return $this;
    }

    /**
     * Exception thrower
     */
    public function throw($msg, $code = 0) {
        throw new \Exception($msg, $code);
    }

    /**
     * Pretty print data dumper
     */
    public function print(...$args) {
        foreach($args as $v){
            if (is_scalar($v) || $v === null) { 
                echo $v; 
            } else { 
                echo "<pre style='background:#1e1e24; color:#38bdf8; padding:12px; border-radius:8px; font-family:monospace; line-height:1.4; font-size:13px;'>"; 
                print_r($v); 
                echo "</pre>"; 
            }
        }
        return $this;
    }

    public static function now() {
        if (class_exists('PQDate') && method_exists('PQDate', 'now')) {
            return PQDate::now();
        }
        return date('Y-m-d H:i:s');
    }

    public static function date($time = "now") {
        if (class_exists('PQDate')) {
            return new PQDate($time);
        }
        return date('Y-m-d H:i:s', is_numeric($time) ? $time : strtotime($time));
    }

    /**
     * Dump & Die (Prints formatted data and exits execution)
     */
    public function dd(...$args) {
        $this->print(...$args);
        exit;
    }

    /**
     * Process termination
     */
    public function exit($msg = '') {
        if ($msg !== '') echo $msg;
        exit;
    }

    /**
     * System timestamp retriever
     */
    public function time() {
        return time();
    }
}

// --- [ENGINE CORE] SINGLETON BRIDGES & GLOBAL WRAPPERS ---

if (!function_exists('pq_core')) {
    function pq_core() {
        return PQCore::getInstance();
    }
}

if (!function_exists('pq')) {
    function pq() {
        return pq_core();
    }
}

if (!function_exists('pq_print')) {
    function pq_print(...$args){
        pq()->print(...$args);
    }
}
?>