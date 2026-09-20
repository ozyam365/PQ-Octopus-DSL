<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.8)
 * FILENAME  : /pq/engine/route_list.php
 * COMPONENT : PQ System Route Registration List
 * =========================================================
 */

// [CUSTOM] Primary Application Routes
$_mmenu = [
    ["/index", "/html/index/index.pq"],
    ["/error/404", "/html/error/404.pq"],
    ["/01", "/html/01/index.pq"],
    ["/06", "/html/06/index.pq"]
];

// [CUSTOM] Sub-Page & API Endpoint Routes
$_smenu = [
    ["/01/first", "/html/01/first.pq"],
    ["/03/router", "/html/03/router.pq"], 
    
    // Board Modules
    ["/bbs/bbs_list", "/html/bbs/bbs_list.pq"],

    // Member Authentication Modules
    ["/mbr/join", "/html/mbr/join.pq"],

    // Global Search Module
    ["/search", "/html/search/index.pq"]
];

// [CUSTOM] Mobile Specific Routes
$_pmenu = [
    ["/m", "/html/m/index.pq"]
];

// [CUSTOM] Administration Panel Routes
$_amenu = [
    ["/adm/", "/html/csm/"],
    ["/adm/login", "/html/csm/login.pq"],
    ["/adm/login_ext", "/html/csm/login_ext.pq", "api"],
    ["/adm/00/", "/html/csm/00"],
    ["/adm/01", "/html/csm/01"],
	["/adm/00/main", "/html/csm/00/main.pq"],	
    ["/adm/manual/core_list", "/html/csm/manual/core_list.pq"],
    ["/adm/mbr/mbr_ext", "/html/csm/mbr/mbr_ext.pq", "api"],
    ["/adm/install_manual", "/html/csm/00/install_manual.pq"]
];

// [CONFIG] Dynamic Route Interceptor Pipeline
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

// Execute Route Registrations
$route->url($_mmenu);
$route->url($_smenu);
$route->url($_amenu);
$route->url($_pmenu);
//$route->auto('/html/csm', '/adm');
?>