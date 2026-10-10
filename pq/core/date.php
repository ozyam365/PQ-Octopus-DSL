<?php
/**
 * =========================================================
 * PQ Date and Time Processing Core Module
 * FILENAME  : /pq/core/date.php
 * UPDATE    : 2026-10-10 PM 02:27
 * =========================================================
 */
if (file_exists(__DIR__ . '/date_lunar.php')) {
    require_once __DIR__ . '/date_lunar.php';
}
if (!class_exists('PQDate', false)) {
    class PQDate {
        private $dt;

        public function __construct($time = "now") {
            $this->reset($time);
        }

        // [CONFIG] Date Instance Reset Helper
        public function reset($time) {
            try {
                if (is_numeric($time)) {
                    $this->dt = (new DateTime())->setTimestamp($time);
                } else {
                    $this->dt = new DateTime($time ?: "now");
                }
            } catch (\Exception $e) {
                $this->dt = new DateTime();
            }
            return $this;
        }
		public static function now() { return new self("now"); }
        public static function today() { return (new self("now"))->format("Y-m-d"); }
        public static function make($time = "now") { return new self($time); }

        /**
         * [NEW CORE UTILITY 1] 10보다 작은지 여부 판단해서 작을 경우 뒷 한 자리 추출
         */
        public static function cutzero($arg) {
            if ($arg) {
                if ($arg < 10) {
                    $arglen = strlen((string)$arg);
                    if ($arglen > 1) $arg = substr((string)$arg, -1, 1);
                }
                return $arg;
            } else {
                return;
            }
        }

        /**
         * [NEW CORE UTILITY 2] 10보다 작은지 여부 판단해서 작을 경우 앞에다가 0 붙임
         */
        public static function addzero($arg) {
            if ($arg) {
                if ($arg < 10) {
                    $arglen = strlen((string)$arg);
                    if ($arglen <= 1) $arg = "0" . $arg;
                }
                return $arg;
            } else {
                return;
            }
        }

        /**
         * [NEW CORE UTILITY 3] 지정한 자리수 만큼 0 채움 (zerofill)
         */
        public static function zerofill($arg, $val = null) {
            // 인자가 1개만 전달된 경우 ($val 생략 시 현재 객체 값 또는 1인자 처리)
            if ($val === null) {
                return sprintf('%02d', (int)$arg);
            }
            return sprintf('%0' . (int)$arg . 'd', (int)$val);
        }
		public function lunar() {
			$y = $this->format('Y');
			$m = $this->format('m');
			$d = $this->format('d');

			return pq_lunar($y, $m, $d);
		}

		public function solar($is_leap = false) {
			// 현재 date 객체의 날짜 정보를 음력으로 간주하여 양력으로 변환
			$ly = $this->format('Y');
			$lm = $this->format('m');
			$ld = $this->format('d');

			return pq_solar($ly, $lm, $ld, $is_leap);
		}
        /**
         * Return formatted date or year/timestamp as integer
         * Supports: date_pq()->format('Y')->int() replacement OR direct date_pq()->int('Y')
         */
        public function int($format = null) {
            if ($format !== null) {
                return (int)$this->dt->format($format);
            }
            return (int)$this->dt->format("Ymd");
        }

        /**
         * Get Last Day of Current Month
         */
        public function lastDay($as_obj = false) {
            $last = $this->dt->format("t");
            return $as_obj ? $this->reset($this->dt->format("Y-m-$last")) : $last;
        }

        // Fluent Date Chaining Methods
		public function copy() {
			$clone = clone $this;
			$clone->dt = clone $this->dt; // 내부 DateTime 인스턴스까지 Deep Copy
			return $clone;
		}

		// 3. Month 체이닝 연산
		public function addMonth($months = 1) {
			$this->dt->modify("+{$months} month");
			return $this;
		}

		public function subMonth($months = 1) {
			$this->dt->modify("-{$months} month");
			return $this;
		}

		// 4. Year 체이닝 연산
		public function addYear($years = 1) {
			$this->dt->modify("+{$years} year");
			return $this;
		}

		public function subYear($years = 1) {
			$this->dt->modify("-{$years} year");
			return $this;
		}

		// 5. Day 체이닝 연산
		public function addDay($days = 1) {
			$this->dt->modify("+{$days} day");
			return $this;
		}

		public function subDay($days = 1) {
			$this->dt->modify("-{$days} day");
			return $this;
		}

        public function format($f = "Y-m-d H:i:s") { return $this->dt->format($f); }
        public function timestamp() { return $this->dt->getTimestamp(); }

        // Date Status Inspection Methods
        public function isPast() { return $this->dt < new DateTime(); }
        public function isFuture() { return $this->dt > new DateTime(); }
        public function isWeek() { $w = $this->dt->format("w"); return ($w == 0 || $w == 6); }

        public function isToday() {
            return $this->dt->format("Y-m-d") === (new DateTime())->format("Y-m-d");
        }

        /**
         * Calculate Difference in Days
         */
        public function diffDay($target) {
            $target_dt = ($target instanceof PQDate) ? $target->dt : (new DateTime($target));
            $clone_this = clone $this->dt; $clone_this->setTime(0, 0, 0);
            $target_clone = clone $target_dt; $target_clone->setTime(0, 0, 0);
            return (int)$clone_this->diff($target_clone)->format("%r%a");
        }

        /**
         * Calculate Difference in Hours
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
         * [CUSTOMIZE] Human Readable Relative Time
         */
        public function ago() {
            $diff = time() - $this->timestamp();
            if ($diff <= 0)     return "방금 전";
            if ($diff < 60)    return $diff . "초 전";
            if ($diff < 3600)  return floor($diff / 60) . "분 전";
            if ($diff < 86400) return floor($diff / 3600) . "시간 전";
            return floor($diff / 86400) . "일 전";
        }

        public function __toString() { return $this->format(); }
    }
}

// Legacy Class Alias Support
if (!class_exists('DateMaker')) { class_alias('PQDate', 'DateMaker'); }

/**
 * [ENGINE CORE] Helper Bridge Function for date()
 * Parser internally redirects date(...) -> date_pq(...)
 */
if (!function_exists('date_pq')) {
    function date_pq($time = "now") {
        return new PQDate($time);
    }
}
?>