<?php
 /**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME :  /pq/core/list.php  
 * COMPONENT : PQ Engine Pagination Manager
 * =========================================================
 */
function keyword_link($w, $txts = "w") {
    // 💡 FormValue 객체일 경우 내부 원시 배열(Array)을 자동으로 언래핑!
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
// 검색어 매칭 하이라이트 함수
function mark($target, $keyword, $color = '#FFDE4D') {
    if (empty($keyword)) return $target;
    // 대소문자 구분 없이 매칭되는 단어를 마크 태그로 감싸버립니다.
    return preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<mark style="background-color:'.$color.'; padding:2px 4px; border-radius:4px;">$1</mark>', $target);
}
function navi_make($total, $page, $limit, $offset, $url, $pgname = "page") {
    $npage = $pgname; // 전달받은 이름(mpage 등)을 사용
    if (!$page) $page = 1;
    $totalpg = ceil($total / $limit);
    if ($totalpg < 1) $totalpg = 1;
    if ($page > $totalpg) $page = 1;

    $nowblock = ceil($page / $offset);
    $firstblock = ($nowblock - 1) * $offset;
    $lastblock = ($nowblock * $offset > $totalpg) ? $totalpg : $nowblock * $offset;

    $html = '<ul class="pagination custom-pagination justify-content-center pagination-sm">';

    // 처음
    $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'=1">처음</a></li>';
    
    // 이전
    $prev = ($page <= 1) ? 1 : $page - 1;
    $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'='.$prev.'">이전</a></li>';

    // 숫자 루프
    for ($i = $firstblock + 1; $i <= $lastblock; $i++) {
        $active = ($page == $i) ? 'active' : '';
        $html .= '<li class="page-item '.$active.'"><a class="page-link" href="'.$url.'&'.$npage.'='.$i.'">'.$i.'</a></li>';
    }

    // 다음
    $next = ($page >= $totalpg) ? $totalpg : $page + 1;
    $html .= '<li class="page-item"><a class="page-link text-secondary" href="'.$url.'&'.$npage.'='.$next.'">다음</a></li>';
    
    // 끝
    $html .= '<li class="page-item"><a class="page-link" href="'.$url.'&'.$npage.'='.$totalpg.'">끝</a></li>';

    $html .= '</ul>';
    return $html;
}

// 엔진 바인딩
class Navi {
    public function make($total, $page, $limit, $offset, $url, $pgname) {
        return navi_make($total, $page, $limit, $offset, $url, $pgname);
    }
}
$navi = new Navi();
?>