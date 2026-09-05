<?php
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME : /pq/core/auth.php
 * COMPONENT : 레벨디자인별 권한설정 및 PQAuth 코어 모듈
 * =========================================================
 * 레벨디자인
 * 1: 총관리자
 * 2: 관리자~100
 * 101~ 200 : 상점
 * 201 ~ 300 : 파트너사
 * 301 ~ 999 : 회원
 * 301: 일반회원
 * 302: 우수회원
 * 303: 특별회원 
 * 304: 특별회원  
 */

/* =========================================================
 * 1. 기존 전역 권한 함수 (하위 호환성 100% 유지)
 * ========================================================= */

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

/* 
 * 페이지 리턴 블럭 함수 (단독 차단용)
 */
function super_only($url = "/index") {
    if (super_auth()) return true;
    http_pq()->msg("관리자 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function adm_only($url = "/index") {
    if (adm_auth()) return true;
    http_pq()->msg("관리자 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function manager_only($url = "/index") {
    if (manager_auth()) return true;
    http_pq()->msg("관리자 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function store_only($url = "/index") {
    if (store_auth()) return true;
    http_pq()->msg("상점관리자 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function partner_only($url = "/index") {
    if (partner_auth()) return true;
    http_pq()->msg("파트너 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function member_only($url = "/index") {
    if (member_auth()) return true;
    http_pq()->msg("회원 권한이 없거나 로그인이 만료되었습니다.")->go($url);
    exit;
}

function login_only($url = "/index") {
    if (login_auth()) return true;
    http_pq()->msg("로그인이 필요합니다.")->go($url);
    exit;
}


/* =========================================================
 * 2. PQAuth 코어 예약 객체 (auth.* 신규 API)
 * ========================================================= */

class PQAuth {
    /**
     * 현재 로그인한 사용자 단일 객체(#) 반환
     * PQ 문법: #me = auth.user();
     */
    public function user() {
        if (!function_exists('session_pq')) return null;
        return session_pq()->get('user');
    }

    /**
     * 유저 세션/객체 존재 여부
     */
    public function has() {
        return $this->user() !== null;
    }

    /**
     * 자주 쓰는 단일 속성 헬퍼 메서드
     */
    public function id() {
        $u = $this->user();
		//필드를 mbr_id 를 쓰지 않는 경우 하단에 $u->mbr_id 대신  필드명을 쓰시면 됩니다. ex )$u->member_id
        return $u->mbr_id ?? $u->mb_id ?? '';
    }

    public function name() {
        $u = $this->user();
		//필드를 mbr_name 을 쓰지 않는 경우 하단에 $u->mbr_name 대신  필드명을 쓰시면 됩니다.   ex )$u->member_name
        return $u->mbr_name ?? $u->mb_name ?? '손님';
    }

    public function email() {
        $u = $this->user();
        return $u->mbr_email ?? $u->mb_email ?? '';
    }

    public function level() {
        return auth_level();
    }

    /* --- 권한 검증 메서드 (True / False) --- */
    public function check()   { return login_auth(); }
    public function guest()   { return guest_auth(); }
    public function super()   { return super_auth(); }
    public function admin()   { return adm_auth(); }
    public function manager() { return manager_auth(); }
    public function store()   { return store_auth(); }
    public function partner() { return partner_auth(); }
    public function member()  { return member_auth(); }
    
    // 직관성을 높인 범위 검증 메서드 (is -> allow)
    public function allow($min, $max) { return level_auth($min, $max); }

    /* --- 페이지 차단 & 리다이렉트 --- */
    public function loginOnly($url = "/index")   { return login_only($url); }
    public function superOnly($url = "/index")   { return super_only($url); }
    public function adminOnly($url = "/index")   { return adm_only($url); }
    public function managerOnly($url = "/index") { return manager_only($url); }
    public function storeOnly($url = "/index")   { return store_only($url); }
    public function partnerOnly($url = "/index") { return partner_only($url); }
    public function memberOnly($url = "/index")  { return member_only($url); }
}

/**
 * 시스템 예약어 auth() 인스턴스 싱글톤 헬퍼
 */
function auth() {
    static $instance = null;
    if ($instance === null) {
        $instance = new PQAuth();
    }
    return $instance;
}