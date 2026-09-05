<?php
/**
 * =========================================================
  * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /pq/engine/route_list.php
 * COMPONENT : PQ System Route Registration List
 * =========================================================
 */

// 1. 메인 메뉴 등록
$_mmenu = [
    ["/index", "/html/index/index.pq"],
    ["/error/404", "/html/error/404.pq"],
    ["/01", "/html/01/index.pq"],
    ["/02", "/html/02/index.pq"],
    ["/03", "/html/03/index.pq"],
    ["/04", "/html/04/index.pq"],
    ["/05", "/html/05/index.pq"],
    ["/06", "/html/06/index.pq"],
];

// 2. 서브 메뉴 등록
$_smenu = [
    ["/01/first", "/html/01/first.pq"],
    ["/01/license", "/html/01/license.pq"],
    ["/mbr/join", "/html/mbr/join.pq"],
    ["/search", "/html/search/index.pq"],
];

// 3. 모바일 메인 메뉴 등록
$_pmenu = [
    ["/m", "/html/m/index.pq"]
];
// 4. 관리자 메뉴 등록
$_amenu = [
    ["/adm/", "/html/csm/"],
    ["/adm/login", "/html/csm/login.pq"],
    ["/adm/login_ext", "/html/csm/login_ext.pq", "api"],
];

// 5. 동적 라우팅 가드
$path = http()->path();
$exp  = explode('/', trim($path, '/'));
$cat  = $exp[0] ?? '';
$api  = $exp[1] ?? '';
$item = $exp[2] ?? '';
$fn_name = $exp[3] ?? '';

if (in_array($cat, ['04', '05'], true) && !empty($api) && $api !== 'index') {
    if (!empty($item))    form()->set("item", $item);
    if (!empty($fn_name)) form()->set("fn_name", $fn_name);
    
    $_smenu[] = [$path, "/html/{$cat}/{$api}_main.pq"];
}

// 6. 라우터 바인딩
$auto_menu = function($menu_list) {
    $result = [];
    foreach ($menu_list as $item) {
        $url  = $item[0];
        $file = $item[1];
        $type = $item[2] ?? 'page';
        $trimmed = rtrim($url, '/');

        $result[] = [$url, $file, $type];
        if ($trimmed !== '' && $trimmed !== $url) {
            $result[] = [$trimmed, $file, $type];
        } elseif ($trimmed !== '') {
            $result[] = [$trimmed . '/', $file, $type];
        }
    }
    return $result;
};

pq_url($auto_menu($_mmenu));
pq_url($auto_menu($_smenu));
pq_url($auto_menu($_amenu));
pq_url($auto_menu($_pmenu));
?>