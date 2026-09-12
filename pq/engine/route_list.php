<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/engine/route_list.php
 * COMPONENT : PQ System Route Registration List
 * =========================================================
 */

// [CUSTOM] Primary Application Routes
$_mmenu = [
    ["/index", "/html/index/index.pq"],
    ["/error/404", "/html/error/404.pq"],
    ["/01", "/html/01/index.pq"],
    ["/02", "/html/02/index.pq"],
    ["/03", "/html/03/index.pq"],
    ["/04", "/html/04/index.pq"],
    ["/05", "/html/05/index.pq"],
    ["/06", "/html/06/index.pq"]
];

// [CUSTOM] Sub-Page & API Endpoint Routes
$_smenu = [
    ["/01/first", "/html/01/first.pq"],
    ["/01/license", "/html/01/license.pq"],
    ["/01/env", "/html/01/env.pq"],
    ["/02/directory", "/html/02/directory.pq"],
    ["/03/read_syntex", "/html/03/read_syntex.pq"],
    ["/03/veto_syntex", "/html/03/veto_syntex.pq"],
    ["/03/vartype", "/html/03/vartype.pq"],
    ["/03/reserve", "/html/03/reserve.pq"],
    ["/04/core", "/html/04/core_main.pq"],
    ["/05/plugin", "/html/05/plugin_main.pq"],
    ["/03/condition", "/html/03/condition.pq"],
    ["/03/repeat", "/html/03/repeat.pq"],
    ["/03/rule", "/html/03/rule.pq"],
    ["/03/pin", "/html/03/pin.pq"],
    ["/03/ret", "/html/03/ret.pq"],
    ["/03/router", "/html/03/router.pq"], 
    
    // Board Modules
    ["/bbs/bbs_list", "/html/bbs/bbs_list.pq"],
    ["/bbs/bbs_view", "/html/bbs/bbs_view.pq"],
    ["/bbs/bbs", "/html/bbs/bbs.pq"],
    ["/bbs/bbs_del", "/html/bbs/bbs_del.pq", "api"],
    ["/bbs/bbs_reply", "/html/bbs/bbs_reply.pq"],
    ["/bbs/bbs_ext", "/html/bbs/bbs_ext.pq", "api"],
    ["/bbs/pass", "/html/bbs/pass.pq"],
    ["/bbs/pass_ext", "/html/bbs/pass_ext.pq", "api"],
    ["/bbs/memo", "/html/bbs/memo.pq"],
    ["/bbs/memo_ext", "/html/bbs/memo_ext.pq"],
    ["/bbs/memo_auth_ext", "/html/bbs/memo_auth_ext.pq", "api"],
    ["/bbs/memo_list", "/html/bbs/memo_list.pq"],
    ["/bbs/memo_load", "/html/bbs/memo_load.pq", "api"],
    ["/bbs/memo_del", "/html/bbs/memo_del.pq", "api"],

    // Member Authentication Modules
    ["/mbr/join", "/html/mbr/join.pq"],
    ["/mbr/join_ext", "/html/mbr/join_ext.pq", "api"],
    ["/mbr/mbr", "/html/mbr/mbr.pq"],
    ["/mbr/login", "/html/mbr/login.pq"],
    ["/mbr/login_ext", "/html/mbr/login_ext.pq", "api"],
    ["/mbr/logout", "/html/mbr/logout.pq"],
    ["/mbr/find_id", "/html/mbr/find_id.pq"],
    ["/mbr/find_id_ext", "/html/mbr/find_id_ext.pq", "api"],
    ["/mbr/find_pass", "/html/mbr/find_pass.pq"],
    ["/mbr/find_pass_ext", "/html/mbr/find_pass_ext.pq", "api"],
    ["/mbr/find_sucess", "/html/mbr/find_sucess.pq"],
    ["/mbr/mbr_ext", "/html/mbr/mbr_ext.pq", "api"],
    ["/mbr/index", "/html/mbr/index.pq"],

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
    ["/adm/manual/core_list", "/html/csm/manual/core_list.pq"],
    ["/adm/manual/core", "/html/csm/manual/core.pq"],
    ["/adm/manual/core_ext", "/html/csm/manual/core_ext.pq"],
    ["/adm/manual/plugin_list", "/html/csm/manual/plugin_list.pq"],
    ["/adm/manual/plugin", "/html/csm/manual/plugin.pq"],
    ["/adm/manual/plugin_ext", "/html/csm/manual/plugin_ext.pq"],
    ["/adm/bbs/bbs_adm_list", "/html/csm/bbs/bbs_adm_list.pq"],
    ["/adm/bbs/bbs_adm", "/html/csm/bbs/bbs_adm.pq"],
    ["/adm/bbs/bbs_adm_ext", "/html/csm/bbs/bbs_adm_ext.pq", "api"],
    ["/adm/mbr/mbr_level_list", "/html/csm/mbr/mbr_level_list.pq"],
    ["/adm/mbr/mbr_level_view", "/html/csm/mbr/mbr_level_view.pq"],
    ["/adm/mbr/mbr_level", "/html/csm/mbr/mbr_level.pq"],
    ["/adm/mbr/mbr_level_ext", "/html/csm/mbr/mbr_level_ext.pq", "api"],
    ["/adm/mbr/mbr_list", "/html/csm/mbr/mbr_list.pq"],
    ["/adm/mbr/mbr_view", "/html/csm/mbr/mbr_view.pq"],
    ["/adm/mbr/mbr", "/html/csm/mbr/mbr.pq"],
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

// [CONFIG] Trailing Slash Normalization & Route Binding Pipeline
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

// Execute Route Registrations
pq_url($auto_menu($_mmenu));
pq_url($auto_menu($_smenu));
pq_url($auto_menu($_amenu));
pq_url($auto_menu($_pmenu));

// Automatic Directory Fallback Binding
pq_auto(PQ_DIR . '/html/csm', '/adm');
pq_auto(PQ_DIR . '/html/csm/00', '/adm/00');
pq_auto(PQ_DIR . '/html/csm/01', '/adm/01');
?>