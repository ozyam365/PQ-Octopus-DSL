<?php
/**
 * =========================================================
 * PQ Date Lunar/Solar Engine (File-based Binary Offset Driver)
 * FILENAME  : /pq/core/date_lunar.php
 * UPDATE :  2026-10-10 PM 06:24
 * =========================================================
 */


if (!function_exists('getDay')) {
    // 10년 단위 기준일 대비 경과 일수 계산
    function getDay($year, $month, $day) {
        $base_year = (int)($year / 10) * 10;
        $base_date = strtotime(sprintf("%d-01-01", $base_year));
        $cur_date  = strtotime(sprintf("%d-%02d-%02d", $year, $month, $day));

        return (int)round(($cur_date - $base_date) / 86400);
    }
}

/**
 * 1. 양력 -> 음력 변환 함수
 * @return PQObjectEngine
 */
function pq_lunar($year, $month, $day) {
    $base_year = (int)($year / 10) * 10;
    $base_jump = 6;
    $base_day  = getDay($year, $month, $day);

    // assets 또는 data 내 음력 파일 경로 설정
    $lunar_dir = defined('PQ_DIR') ? PQ_DIR . '/assets/lunar' : dirname(__DIR__) . '/assets/lunar';
    $str_path  = sprintf("%s/lunar_%d.txt", $lunar_dir, $base_year);

    if (!file_exists($str_path)) {
        return obj(['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'is_leap' => false]);
    }

    $f = fopen($str_path, "rb");
    if (!$f) {
        return obj(['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'is_leap' => false]);
    }

    $res = ['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'is_leap' => false];

    if (0 === fseek($f, ($base_day * $base_jump), SEEK_SET)) {
        $buffer = fread($f, $base_jump);
        if (strlen($buffer) >= $base_jump - 1) {
            $year_flag  = substr($buffer, 0, 1);
            $lunar_year = (int)$year;
            $lunar_leap = false;

            if ($year_flag == "a") $lunar_year--;
            if ($year_flag == "b") $lunar_leap = true;
            if ($year_flag == "c") { $lunar_year--; $lunar_leap = true; }

            $lunar_month = intval(substr($buffer, 1, 2));
            $lunar_day   = intval(substr($buffer, 3, 2));

            $res = [
                'success' => true,
                'year'    => $lunar_year,
                'month'   => $lunar_month,
                'day'     => $lunar_day,
                'is_leap' => $lunar_leap
            ];
        }
    }
    fclose($f);

    return obj($res);
}

/**
 * 2. 음력 -> 양력 변환 함수 (fseek 이진 탐색 방식)
 * @return PQObjectEngine
 */
function pq_solar($lunar_year, $lunar_month, $lunar_day, $is_leap = false) {
    $base_year = (int)($lunar_year / 10) * 10;
    $base_jump = 6;

    $lunar_dir = defined('PQ_DIR') ? PQ_DIR . '/assets/lunar' : dirname(__DIR__) . '/assets/lunar';
    $str_path  = sprintf("%s/lunar_%d.txt", $lunar_dir, $base_year);

    if (!file_exists($str_path)) {
        return obj(['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'date' => '']);
    }

    $f = fopen($str_path, "rb");
    if (!$f) {
        return obj(['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'date' => '']);
    }

    // 파일 전체 레코드 수 계산
    fseek($f, 0, SEEK_END);
    $total_bytes = ftell($f);
    $total_days  = (int)($total_bytes / $base_jump);

    // 순차 스캔으로 목표 음력 날짜 일치 여부 매칭
    // (10년 데이터가 약 3,650개 레코드라 순식간에 매칭됨)
    for ($day_offset = 0; $day_offset < $total_days; $day_offset++) {
        fseek($f, $day_offset * $base_jump, SEEK_SET);
        $buffer = fread($f, $base_jump);

        if (strlen($buffer) < $base_jump - 1) continue;

        $year_flag = substr($buffer, 0, 1);
        $cur_lyear = (int)$base_year;
        $cur_leap  = false;

        if ($year_flag == "a") $cur_lyear--; // -1년 보정
        if ($year_flag == "b") $cur_leap = true;
        if ($year_flag == "c") { $cur_lyear--; $cur_leap = true; }

        $cur_lmonth = intval(substr($buffer, 1, 2));
        $cur_lday   = intval(substr($buffer, 3, 2));

        // 목표 음력 조건과 정확히 일치하는지 확인
        if ($cur_lyear == $lunar_year && $cur_lmonth == $lunar_month && $cur_lday == $lunar_day && $cur_leap == (bool)$is_leap) {
            fclose($f);

            // 해당 경과일($day_offset)을 양력 날짜로 역산
            $base_timestamp = strtotime(sprintf("%d-01-01", $base_year));
            $solar_timestamp = $base_timestamp + ($day_offset * 86400);

            return obj([
                'success' => true,
                'year'    => (int)date('Y', $solar_timestamp),
                'month'   => (int)date('m', $solar_timestamp),
                'day'     => (int)date('d', $solar_timestamp),
                'date'    => date('Y-m-d', $solar_timestamp)
            ]);
        }
    }

    fclose($f);
    return obj(['success' => false, 'year' => 0, 'month' => 0, 'day' => 0, 'date' => '']);
}