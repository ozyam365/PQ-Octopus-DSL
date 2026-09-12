<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/list.php  
 * COMPONENT : PQ Engine Pagination & Search Matrix Core
 * =========================================================
 */

// --- [1. SEARCH QUERY & HIGHLIGHT UTILITIES] ---
/**
 * Generates URL query string parameters for multi-keyword search arrays
 */
if (!function_exists('keyword_link')) {
    function keyword_link($w, $txts = "w") {
        // Automatically unwrap raw array if $w is a FormValue instance
        if (is_object($w) && method_exists($w, 'value')) {
            $w = $w->value();
        }

        if (is_array($w)) {
            $params = [];        
            foreach ($w as $i => $val) {
                if ($val !== '' && $val !== null) {
                    $params[] = $txts . "[" . $i . "]=" . urlencode($val);
                }
            }        
            return implode("&amp;", $params);
        }        
        return '';
    }
}

/**
 * Case-insensitive search keyword highlighter
 */
if (!function_exists('mark')) {
    function mark($target, $keyword, $color = '#FFDE4D') {
        if (empty($keyword)) return $target;
        return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<mark style="background-color:'.$color.'; padding:2px 4px; border-radius:4px;">$1</mark>', $target);
    }
}

// --- [2. PAGINATION HTML GENERATOR ENGINE] ---
if (!function_exists('navi_make')) {
    function navi_make($total, $page, $limit, $offset, $url, $pgname = "page") {
        $npage = $pgname;
        if (!$page) $page = 1;
        $totalpg = ceil($total / $limit);
        if ($totalpg < 1) $totalpg = 1;
        if ($page > $totalpg) $page = 1;

        $nowblock = ceil($page / $offset);
        $firstblock = ($nowblock - 1) * $offset;
        $lastblock = ($nowblock * $offset > $totalpg) ? $totalpg : $nowblock * $offset;

        $html = '<ul class="pagination custom-pagination justify-content-center pagination-sm">';

        // First Page
        $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'=1">처음</a></li>';
        
        // Previous Page
        $prev = ($page <= 1) ? 1 : $page - 1;
        $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'='.$prev.'">이전</a></li>';

        // Page Number Loop
        for ($i = $firstblock + 1; $i <= $lastblock; $i++) {
            $active = ($page == $i) ? 'active' : '';
            $html .= '<li class="page-item '.$active.'"><a class="page-link" href="'.$url.'&'.$npage.'='.$i.'">'.$i.'</a></li>';
        }

        // Next Page
        $next = ($page >= $totalpg) ? $totalpg : $page + 1;
        $html .= '<li class="page-item"><a class="page-link text-secondary" href="'.$url.'&'.$npage.'='.$next.'">다음</a></li>';
        
        // Last Page
        $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'='.$totalpg.'">끝</a></li>';

        $html .= '</ul>';
        return $html;
    }
}

class Navi {
    public function make($total, $page, $limit, $offset, $url, $pgname = "page") {
        return navi_make($total, $page, $limit, $offset, $url, $pgname);
    }
}

// --- [3. SINGLETON BRIDGE & GLOBAL WRAPPERS] ---
if (!function_exists('navi_pq')) {
    function navi_pq() {
        static $instance = null;
        if (!$instance) {
            $instance = new Navi();
        }
        return $instance;
    }
}

if (!function_exists('navi')) {
    function navi() {
        return navi_pq();
    }
}

// Global instance variable for backward compatibility
if (!isset($GLOBALS['navi'])) {
    $GLOBALS['navi'] = navi_pq();
}
$navi = $GLOBALS['navi'];
?>