<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /pq/core/auth.php
 * COMPONENT : Role-Based Access Control (RBAC) & PQAuth Core Module
 * =========================================================
 * 
 * [USER CONFIG] User Level Policy Mapping
 * - 1           : Super Administrator
 * - 2 ~ 100     : Staff / Managers
 * - 101 ~ 200   : Store Operators
 * - 201 ~ 300   : Business Partners
 * - 301 ~ 999   : Regular Members (301: Normal, 302: VIP, 303: Special)
 * - 0           : Guest / Unauthenticated
 */

// =========================================================
// 1. Core Global Permission Functions
// =========================================================

function auth_level() {
    if (!function_exists('session_pq')) return 0;
    $user = session_pq()->get('user');
    return (int)($user->mbr_level ?? $user->mb_level ?? 0);
}

function super_auth() {
    return auth_level() === 1;
}

function adm_auth() {
    return level_auth(1, 100);
}

function manager_auth() {
    return level_auth(2, 100);
}

function store_auth() {
    return level_auth(101, 200);
}

function partner_auth() {
    return level_auth(201, 300);
}

function member_auth() {
    return level_auth(301, 999);
}

function guest_auth() {
    return auth_level() === 0;
}

function login_auth() {
    return auth_level() > 0;
}

function level_auth($min, $max) {
    $lvl = auth_level();
    return $lvl >= $min && $lvl <= $max;
}

// =========================================================
// [REQUIRED] Guard Interceptor Functions (Page Access Block)
// =========================================================

function super_only($url = "/index") {
    if (super_auth()) return true;
    http_pq()->msg("Access denied: Administrator privilege required.")->go($url);
    exit;
}

function adm_only($url = "/index") {
    if (adm_auth()) return true;
    http_pq()->msg("Access denied: Administrator privilege required.")->go($url);
    exit;
}

function manager_only($url = "/index") {
    if (manager_auth()) return true;
    http_pq()->msg("Access denied: Manager privilege required.")->go($url);
    exit;
}

function store_only($url = "/index") {
    if (store_auth()) return true;
    http_pq()->msg("Access denied: Store privilege required.")->go($url);
    exit;
}

function partner_only($url = "/index") {
    if (partner_auth()) return true;
    http_pq()->msg("Access denied: Partner privilege required.")->go($url);
    exit;
}

function member_only($url = "/index") {
    if (member_auth()) return true;
    http_pq()->msg("Access denied: Member privilege required.")->go($url);
    exit;
}

function login_only($url = "/index") {
    if (login_auth()) return true;
    http_pq()->msg("Authentication required.")->go($url);
    exit;
}

// =========================================================
// 2. PQAuth Core Object (DSL Fluent API: auth.*)
// =========================================================

class PQAuth {
    /**
     * [ENGINE CORE] Get Authenticated User Object (#)
     * Usage: #me = auth.user();
     */
    public function user() {
        if (!function_exists('session_pq')) return null;
        return session_pq()->get('user');
    }

    public function has() {
        return $this->user() !== null;
    }

    /**
     * [CUSTOMIZE] User Property Mapping Helpers
     * Modify column fallback names below to match your database schema.
     */
    public function id() {
        $u = $this->user();
        // [CUSTOMIZE] Fallback column names (e.g., $u->member_id, $u->usr_id)
        return $u->mbr_id ?? $u->mb_id ?? $u->user_id ?? '';
    }

    public function name() {
        $u = $this->user();
        // [CUSTOMIZE] Fallback column names (e.g., $u->member_name, $u->usr_name)
        return $u->mbr_name ?? $u->mb_name ?? $u->user_name ?? 'Guest';
    }

    public function email() {
        $u = $this->user();
        // [CUSTOMIZE] Fallback column names (e.g., $u->member_email)
        return $u->mbr_email ?? $u->mb_email ?? $u->user_email ?? '';
    }

    public function level() {
        return auth_level();
    }

    // Permission Verification Methods
    public function check()   { return login_auth(); }
    public function guest()   { return guest_auth(); }
    public function super()   { return super_auth(); }
    public function admin()   { return adm_auth(); }
    public function manager() { return manager_auth(); }
    public function store()   { return store_auth(); }
    public function partner() { return partner_auth(); }
    public function member()  { return member_auth(); }
    
    public function allow($min, $max) { return level_auth($min, $max); }

    // Guard Redirection Methods
    public function loginOnly($url = "/index")   { return login_only($url); }
    public function superOnly($url = "/index")   { return super_only($url); }
    public function adminOnly($url = "/index")   { return adm_only($url); }
    public function managerOnly($url = "/index") { return manager_only($url); }
    public function storeOnly($url = "/index")   { return store_only($url); }
    public function partnerOnly($url = "/index") { return partner_only($url); }
    public function memberOnly($url = "/index")  { return member_only($url); }
}

/**
 * [ENGINE CORE] Singleton Bridge Function for auth()
 */
function auth() {
    static $instance = null;
    if ($instance === null) {
        $instance = new PQAuth();
    }
    return $instance;
}
?>