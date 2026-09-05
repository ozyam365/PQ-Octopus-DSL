<!-- /html/layout/s_top.pq -->
[[
	 // 현재 URL 경로 추출 (쿼리스트링 제외)
	@now_path = http.path();
    @board_code = http.get('code');

    // 각 GNB 메뉴별 active 상태 계산
    // strpos(@now_path, '/경로') === 0 방식으로 하위 페이지까지 대응
    $active = [
		'm01'        => (strpos(@now_path, '/01') === 0) ? 'active' : '',
        'm02'        => (strpos(@now_path, '/02') === 0) ? 'active' : '',
        'm03'        => (strpos(@now_path, '/03') === 0) ? 'active' : '',
        'm04'        => (strpos(@now_path, '/04') === 0) ? 'active' : '',
        'm05'        => (strpos(@now_path, '/05') === 0) ? 'active' : '',
        'bbs_free'   => (strpos(@now_path, '/bbs') === 0 && @board_code === 'free') ? 'active' : '',
        'bbs_notice' => (strpos(@now_path, '/bbs') === 0 && @board_code === 'notice') ? 'active' : ''
    ];
]]
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/path/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/path/assets/icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/path/html/layout/css/sub.css">
    <link rel="stylesheet" href="/path/html/layout/css/base.css">
    <script src="/path/assets/jquery/jquery-4.0.0.min.js"></script>
    <script src="/path/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <title>PQ문어다리 DSL ,PQ,PHP,JQUERY,DSL언어,PHP엔진,PHP프레임워크,프레임워크,PQ엔진,PQ ENGINE,PQ OCTOPUS ARM</title>
</head>
<body>

<!-- 1. Top 1px Line (100% 너비) -->
<div class="pq-top-line-wrap"></div>

<!-- 2. Header 영역 (100% 감싸기 안에 1440px 정렬) -->
<div class="pq-header-wrap">
    <header class="pq-container-1440 pq-header">
        <div class="logo-area">
            <a href="/index">
                <img src="/path/html/layout/img/main/pq_oa_logo.jpg" alt="PQ Octopus Arm 로고" />
            </a>
        </div>
        <div class="top-blink">

        </div>
		<div class="pq-search-container">
			<form action="/search" method="get" class="pq-search-form">
				<div class="pq-search-box">
					<input type="text" name="q" class="pq-search-input" placeholder="DSL 문법 또는 키워드 검색..." required autocomplete="off" />
					<button type="submit" class="pq-search-btn" aria-label="검색">
						<!-- SVG 돋보기 아이콘 -->
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="11" cy="11" r="8"></circle>
							<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
						</svg>
					</button>
				</div>
			</form>
		</div>
		<div class="user-area">
			<div class="pq-user-profile">
				<!-- 원형 아바타 (기본 프로필 이미지) -->
				[[
					// 1. 회원 로그인 했을 경우
					if(auth.check()):
				]]
					<div class="user-avatar-on">
						<i class="bi bi-person-circle"></i>
					</div>
				[[
						// 관리자 권한 확인
						if(auth.admin()):
				]]
							<div class="user-info">
								<span class="user-role">[[=auth.name()]]  <a href="/adm/00/main" class="text-success" target="_blank">[관리자모드]</a></span>
								<span class="user-name pt-1"><a href="/mbr/index" class="text-dark">마이페이지</a> | <a href="/mbr/logout" class="text-dark">로그아웃</a></span>
							</div>
				[[      else: ]]
							<div class="user-info">
								<span class="user-role">[[=auth.name()]] </span>
								<span class="user-name pt-1"><a href="/mbr/index" class="text-dark">마이페이지</a> | <a href="/mbr/logout" class="text-dark">로그아웃</a></span>
							</div>
				[[
						endif;
					// 2. 로그인 안 한 경우
					else:
				]]
					<div class="user-avatar">
						<i class="bi bi-person-circle"></i>
					</div>
						<div class="user-info lh-base">
							<span class="user-role"><a href="/mbr/login" class="text-decoration-none fw-bold">로그인하기</a></span>
							<span class="user-name"><a href="/mbr/join" class="text-secondary text-decoration-none fs-7">회원가입</a></span>
						</div>
				[[  endif; ]]
			</div>
		</div>
    </header>
</div>

<!-- 3. GNB Navigation (100% 감싸기 안에 1440px 정렬) -->
<div class="pq-gnb-wrap">
    <nav class="pq-container-1440 pq-gnb">
        <ul class="pq-gnb-menu">
            <li><a href="/01/" class="[[= $active['m01'] ]]">시작하기</a></li>
            <li><a href="/02/" class="[[= $active['m02'] ]]">다운로드</a></li>
            <li><a href="/03/" class="[[= $active['m03'] ]]">PQ 문법</a></li>
            <li><a href="/04/core" class="[[= $active['m04'] ]]">CORE API</a></li>
            <li><a href="/05/plugin" class="[[= $active['m05'] ]]">PLUGIN API</a></li>
            <li><a href="/06/" class="[[= $active['m06'] ]]">데모</a></li>
			<li><a href="/bbs/bbs_list?code=free" class="[[= $active['bbs_free'] ]]">커뮤니티</a></li>
			<li><a href="/bbs/bbs_list?code=notice" class="[[= $active['bbs_notice'] ]]">공지사항</a></li>
        </ul>

        <div class="pq-gnb-lang">
            <button class="btn-lang kor">KOR</button>
            <button class="btn-lang eng">ENG</button>
        </div>
    </nav>
</div>

<!-- 4. 상단 구분선 (100% 풀배경) -->
<div class="pq-sub-line-bg-outer">
    <img src="/path/html/layout/img/sub/line_bg.jpg" alt="" class="pq-line-bg-img" />
</div>