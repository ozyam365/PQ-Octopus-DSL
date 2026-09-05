[[
/**
* =========================================================
* PQ VERSION (v9.1.6)
* FILENAME : /html/m/index.pq
* COMPONENT : 모바일페이지 디자인
* =========================================================
*/
@style  = ['card' => 'bg-light text-dark border-secondary shadow-sm'];
@style2 = ['card' => 'bg-dark text-light border-secondary shadow-lg'];
]]

[[=app.navbar("PQ 테마 관제실", "list", "sliders")]]

[[=app.sidebar("홍길동")
    .theme("dark")
    .activeColor("text-warning")
    .add("메인 대시보드", "/m")
    .add("DB 실시간 수사", "/m/db_check")
    .add("자동화 로그 센터", "/m/log")
    .add("시스템 환경설정", "/m/setting")
]]
<div class="p-3" style="margin-top: 56px; margin-bottom: 70px;">
    [[=app.card("서버 상태")
        .badge("정상")
        .icon("cpu")
        .style(@style2)
        .desc("현재 중앙 관제 서버의 CPU 온도가 최적화 상태를 유지하고 있습니다.")
    ]]

    [[=app.card("데이터베이스")
        .badge("완료")
        .icon("database")
        .style(@style)
        .desc("백업 스케줄러가 안전하게 동작을 완료했습니다.")
    ]]

    [[=app.card("외부 크롤링 마찰")
        .badge("위험")
        .icon("exclamation-octagon")
        .style(@style)
        .desc("타겟 수사 대상 서버로부터 커넥션 타임아웃이 감지되었습니다.")
    ]]

    <div class="text-muted small px-1 mb-2 mt-4 fw-bold">
        ⚙️ 관제 센터 메뉴
    </div>

    [[=app.menuGroup()
        .add("DB 함수 모니터링", "/m/db_check", "database", "정상", "text-primary", "bg-success text-white")
        .add("시스템 자동화 설정", "/m/auto_setup", "gear-wide", "활성", "text-success", "bg-primary text-white")
        .add("라이센스 검문", "/license", "shield-lock", "위험", "text-danger", "bg-danger text-white")
    ]]
</div>

[[=app.drawer("관제 센터 퀵 제어")
    .button("코어 엔진 긴급 셧다운", "power", "danger")
    .button("크롤러 강제 리셋", "arrow-repeat")
    .button("보안 수사망 최고 단계 격상", "shield-lock")
]]

[[=app.footer()
    .add("홈", "/m")
    .add("로그", "/m/log")
    .add("설정", "/m/setting")
]]