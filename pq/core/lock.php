<?php
/**
 * =========================================================
 * PQ LOCK CORE ENGINE
 * FILENAME : /pq/core/lock.php
 * UPDATE :  2026-10-07 PM 07:01  
 * =========================================================
 */

class PQLock {
    private static $locks = [];

    public static function acquire($key, $timeout = 5) {
        // MySQL GET_LOCK 연동 (DB 지원 시)
        if (isset($GLOBALS['db']) && method_exists($GLOBALS['db'], 'query')) {
            $res = $GLOBALS['db']->query("SELECT GET_LOCK('pq_lock_{$key}', {$timeout}) AS locked");
            if ($res && isset($res[0]['locked']) && $res[0]['locked'] == 1) {
                self::$locks[$key] = true;
                return true;
            }
        }

        // Fallback: 파일 락 기반
        $lockFile = PQ_DIR . "/pq/tmp/lock_" . md5($key) . ".lock";
        $fp = fopen($lockFile, "c+");
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            self::$locks[$key] = $fp;
            return true;
        }

        return false; // 락 획득 실패 (동시 접근 차단)
    }

    public static function release($key = null) {
        if ($key === null) {
            foreach (array_keys(self::$locks) as $k) {
                self::release($k);
            }
            return;
        }

        if (isset(self::$locks[$key])) {
            if (is_resource(self::$locks[$key])) {
                flock(self::$locks[$key], LOCK_UN);
                fclose(self::$locks[$key]);
            } else {
                if (isset($GLOBALS['db'])) {
                    $GLOBALS['db']->query("SELECT RELEASE_LOCK('pq_lock_{$key}')");
                }
            }
            unset(self::$locks[$key]);
        }
    }
}
?>