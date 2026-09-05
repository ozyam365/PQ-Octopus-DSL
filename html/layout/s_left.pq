<!-- /html/layout/s_left.pq -->
[[
    @npath = http.path();
    $exp_path = explode("/", @npath);
    @cat = $exp_path[1] ?? '';
    @file = $exp_path[2] ?? '';
    @sub_file = $exp_path[3] ?? ''; // 서브메뉴 파라미터가 3번째 경로에 있을 경우

    $left_menu = [];
    @title = '';
    @code = form.get("code").string();

    // 카테고리별 [메뉴명, URL파일명, 서브메뉴배열] 세팅
    switch(@cat) {
        case("01"):
            @title = "시작하기";
            $left_menu = [
                ['name' => 'PQ 문어다리 DSL', 'url' => ''],
                ['name' => '첫번째PQ',     'url' => 'first'],
                ['name' => '개발환경',     'url' => 'env'],
				['name' => '라이센스',     'url' => 'license']

            ];
            break;

        case("02"):
            @title = "다운로드";
            $left_menu = [
                ['name' => '설치가이드', 'url' => ''],
                ['name' => '디렉토리', 'url' => 'directory'],
            ];
            break;

	case("03"):
            @title = "PQ 문법";
            $left_menu = [
                ['name' => 'PHP vs PQ 문법비교',   'url' => ''],
                ['name' => '필독문법',     'url' => 'read_syntex'],
                ['name' => '금지문법',       'url' => 'veto_syntex'],
                ['name' => ' 변수 / 객체 / 배열',       'url' => 'vartype'],
                ['name' => ' 조건문',       'url' => 'condition'],
                ['name' => ' 반복문',       'url' => 'repeat'],
                ['name' => ' 예외처리',       'url' => 'rule'],
                ['name' => ' 예약어',       'url' => 'reserve'],
                ['name' => ' 일괄적용',       'url' => 'pin'],
                ['name' => ' 리턴형식 지정',       'url' => 'ret']
            ];
            break;
	case("04"):
		@title = "CORE API";

		$item_list = db(@_manual_t)
				.where("state = 1")
				.and("api = 'core'")
				.dist("item")
				.order("item asc")
				.list();

		$sub_items = [];
		$seen = []; // 중복 체크용 배열

		repeat($item_list).as($item_row):
			@item_name = text($item_row['item']).unslash().trim();

			if (empty(@item_name)) continue;

			// 🎯 핵심: 이미 처리한 item이면 건너뜁니다!
			if (in_array(@item_name, $seen)) continue;
			$seen[] = @item_name;

			$sub_items[] = [
				'name' => @item_name,
				'url'  => 'core/' . @item_name
			];
		endrepeat;

		$left_menu = [
			[
				'name' => 'CORE API',
				'url'  => 'core',
				'sub'  => $sub_items
			]
		];
    break;
        case("05"):
		@title = "PLUGIN CORE API";

		$item_list = db(@_manual_t)
				.where("state = 1")
				.and("api = 'plugin'")
				.dist("item")
				.order("item asc")
				.list();

		$sub_items = [];
		$seen = []; // 중복 체크용 배열

		repeat($item_list).as($item_row):
			@item_name = text($item_row['item']).unslash().trim();

			if (empty(@item_name)) continue;

			// 🎯 핵심: 이미 처리한 item이면 건너뜁니다!
			if (in_array(@item_name, $seen)) continue;
			$seen[] = @item_name;

			$sub_items[] = [
				'name' => @item_name,
				'url'  => 'plugin/' . @item_name
			];
		endrepeat;

		$left_menu = [
			[
				'name' => 'PLUGIN API',
				'url'  => 'plugin',
				'sub'  => $sub_items
			]
		];
            break;
        case("06"):
            @title = "데모";
            $left_menu = [
                ['name' => 'PQ 예제',         'url' => '']
            ];
            break;
        case("07"):
            @title = "커뮤니티";
            $left_menu = [
                ['name' => '질문과 답변',   'url' => ''],
                ['name' => '자주하는 질문',         'url' => 'reseve'],
                ['name' => '예제 & 팁',       'url' => 'tip'],
                ['name' => '아이디어 제안',         'url' => 'idea']
            ];
            break;

        case("mbr"):
            @title = "회원정보";
            $left_menu = [
                ['name' => '회원가입',   'url' => 'join'],
                ['name' => '로그인',         'url' => 'login'],
                ['name' => '아이디 찾기',       'url' => 'find_id'],
                ['name' => '비밀번호 찾기',         'url' => 'find_pass'],
                ['name' => '정보변경',         'url' => 'mbr']
            ];
            break;
        case("search"):
            @title = "검색";
            $left_menu = [
                ['name' => '검색',   'url' => '']
            ];
            break;
        case("bbs"):
            switch(@code){
                case("free"):
                    @title = "커뮤니티";
                    break;
                case("reserv"):
                    @title = "자주하는 질문";
                    break;
                case("notice"):
                    @title = "공지사항";
                    break;
            }
            $left_menu = [
                ['name' => '질문과 답변',   'url' => 'bbs_list?code=free'],
                ['name' => '자주하는 질문',         'url' => 'bbs_list?code=reserv'],
                ['name' => '예제 & 팁',       'url' => 'tip'],
                ['name' => '아이디어 제안',         'url' => 'idea']
            ];
            break;

        default:
            break;
    }
]]

<!-- [LNB] 좌측 서브 사이드바 영역 (너비 258px) -->
<aside class="pq-sub-aside">
    <!-- 타이틀 영역 -->
    <div class="pq-sub-title-img">
        <div class="pq-ltbox">
            <div class="pq-ltbox-top">
                <img src="/path/html/layout/img/sub/ltbox_t.jpg" alt="" />
            </div>
            <div class="pq-ltbox-body">
                <div class="pq-ltbox-left">
                    <img src="/path/html/layout/img/sub/ltbox_l.jpg" alt="" />
                </div>
                <!-- 텍스트 자동 변경 -->
                <div class="pq-ltbox-center">
                    [[= @title ]]
                </div>
                <div class="pq-ltbox-right">
                    <img src="/path/html/layout/img/sub/ltbox_r.jpg" alt="" />
                </div>
            </div>
            <div class="pq-ltbox-bottom">
                <img src="/path/html/layout/img/sub/ltbox_b.jpg" alt="" />
            </div>
        </div>
    </div>

<!-- 서브 메뉴 리스트 -->
    <ul class="pq-sub-menu">
        [[ repeat($left_menu).as($row):
            $has_sub = isset($row['sub']) && count($row['sub']) > 0;

            // 🚀 빈 값('') URL 활성화 조건 정교화 (항목 중복 활성화 방지)
            if ($row['url'] == '') :
                @is_parent_active = (@file == '');
            else :
                @is_parent_active = (@file == $row['url'] || strpos(@npath, '/' . $row['url']) !== false);
            endif;

            @onoff = @is_parent_active ? 'active' : '';
        ]]
            <li class="[[= $has_sub ? 'has-sub' : '' ]] [[= @is_parent_active ? 'open' : '' ]]">
                <a href="/[[= @cat ]]/[[= $row['url'] ]]" class="[[= @onoff ]]">
                    [[= $row['name'] ]]
                </a>

                <!-- 2차 서브메뉴가 있는 경우 렌더링 -->
                [[ if($has_sub): ]]
                    <ul class="pq-sub-depth2" style="[[= @is_parent_active ? 'display:block;' : 'display:none;' ]]">
                        [[ repeat($row['sub']).as($sub):
                            @sub_active = (strpos(@npath, $sub['url']) !== false) ? 'active' : '';
                        ]]
                            <li style="list-style: none;">
                                <a href="/[[= @cat ]]/[[= $sub['url'] ]]" class="[[= @sub_active ]]">
                                    [[= $sub['name'] ]]
                                </a>
                            </li>
                        [[ endrepeat; ]]
                    </ul>
                [[ endif; ]]
            </li>
        [[ endrepeat; ]]
    </ul>

    <!-- 다운로드 버튼 영역 -->
    <div class="pt-4">
        <div class="pq-download-box" style="width: 258px; margin: 0 auto; text-align: center;">
            <a href="[[=@_pq_down]]" style="display: block; line-height: 0;" target="_blank">
                <img src="/path/html/layout/img/sub/btn_dn_top.jpg" width="258" height="108" alt="PQ 최신버전 DOWNLOAD" style="border: 0;" />
            </a>
            <div style="display: flex; width: 258px; height: 36px; align-items: center; justify-content: space-between; background-color: #eaf8e6;">
                <img src="/path/html/layout/img/sub/btn_dn_l.jpg" width="40" height="36" alt="" style="display: block;" />
                <div style="width: 188px; height: 36px; line-height: 36px; text-align: center; font-weight: bold; font-size: 14px; color: #0088cc; background-color:#F3F3F3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    &nbsp;&nbsp;&nbsp;&nbsp;[[=@_pq_version]]
                </div>
                <img src="/path/html/layout/img/sub/btn_dn_r.jpg" width="40" height="36" alt="" style="display: block;" />
            </div>
            <a href="[[=@_pq_down]]" style="display: block; line-height: 0;" target="_blank">
                <img src="/path/html/layout/img/sub/btn_dn_bottom.jpg" width="258" height="108" alt="다운로드" style="border: 0;" />
            </a>
        </div>
    </div>
</aside>