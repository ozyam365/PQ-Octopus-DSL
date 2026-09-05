<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/date.php  
 * COMPONENT : PQ date 
 * =========================================================
 */
if (!class_exists('PQDate', false)) {
    class PQDate {
        private $dt;

        public function __construct($time = "now") { $this->reset($time); }
        
        public function reset($time) {
            try {
                if (is_numeric($time)) {
                    $this->dt = (new DateTime())->setTimestamp($time);
                } else {
                    $this->dt = new DateTime($time ?: "now");
                }
            } catch (Exception $e) { $this->dt = new DateTime(); }
            return $this;
        }

        public static function now() { return (new self("now"))->format("Y-m-d H:i:s"); }
        public static function today() { return (new self("now"))->format("Y-m-d"); }
        public static function make($time) { return new self($time); }
        
        /**
         * 말일:  date.lastDay()
         */
        public function lastDay($as_obj = false) { 
            $last = $this->dt->format("t");
            return $as_obj ? $this->reset($this->dt->format("Y-m-$last")) : $last; 
        }

        public function addYear($v = 1) { $this->dt->modify("+$v year"); return $this; }
        public function subYear($v = 1) { $this->dt->modify("-$v year"); return $this; }
        public function addMonth($v = 1) { $this->dt->modify("+$v month"); return $this; }
        public function subMonth($v = 1) { $this->dt->modify("-$v month"); return $this; }
        public function addDay($v = 1) { $this->dt->modify("+$v day"); return $this; }
        public function subDay($v = 1) { $this->dt->modify("-$v day"); return $this; }
        
        public function format($f = "Y-m-d H:i:s") { return $this->dt->format($f); }
        public function timestamp() { return $this->dt->getTimestamp(); }

        public function isPast() { return $this->dt < new DateTime(); }
        public function isFuture() { return $this->dt > new DateTime(); }
        public function isWeek() { $w = $this->dt->format("w"); return ($w == 0 || $w == 6); }
        

        public function isToday() { 
            return $this->dt->format("Y-m-d") === (new DateTime())->format("Y-m-d"); 
        }

        public function copy() { return clone $this; }

        /**
         * 일수 격차 : 대상 날짜와의 차이를 정수형(Day)으로 반환
         */
        public function diffDay($target) {
            $target_dt = ($target instanceof PQDate) ? $target->dt : (new DateTime($target));
            $clone_this = clone $this->dt; $clone_this->setTime(0, 0, 0);
            $target_clone = clone $target_dt; $target_clone->setTime(0, 0, 0);
            return (int)$clone_this->diff($target_clone)->format("%r%a");
        }

        /**
         * 시간 격차 : 대상 날짜와의 차이를 정수형(Hours)으로 반환
         */
        public function diffTime($target) {
            $target_dt = ($target instanceof PQDate) ? $target->dt : (new DateTime($target));
            $diff = $target_dt->getTimestamp() - $this->dt->getTimestamp();
            return (int)floor($diff / 3600);
        }

		public static function tostamp($date = null) {
			if ($date === null || trim((string)$date) === '') {
				return time();
			}
			return (new self($date))->timestamp();
		}
        /**
         * "방금 전", "5분 전" 등
         */
        public function ago() {
            $diff = time() - $this->timestamp();
            if ($diff <= 0) return "방금 전";
            if ($diff < 60) return $diff . "초 전";
            if ($diff < 3600) return floor($diff / 60) . "분 전";
            if ($diff < 86400) return floor($diff / 3600) . "시간 전"; // 💡 84600 -> 86400 오타 영구 처단
            return floor($diff / 86400) . "일 전";
        }

        public function __toString() { return $this->format(); }
    }
}
if (!class_exists('DateMaker')) { class_alias('PQDate', 'DateMaker'); }

/**
 * PQDate 인스턴스 생성 전용 헬퍼
 * 사용법: [[ date("2026-12-25").addDay(7).format("Y-m-d"); ]]
 * (파서가 내부적으로 date_pq()로 치환하여 바인딩함)
 */
if (!function_exists('date_pq')) {
    function date_pq($time = "now") { 
        return new PQDate($time); 
    }
}
?>
