<?php
/**
 * =========================================================
 * PQ VERSION (v3.2 Official Enterprise Perfect Stable)
 * FILENAME : /pq/plugin/app.php
 * COMPONENT : PQ 엔진 모바일 UI 전담 플러그인 (Part 1/2)
 * =========================================================
 */

class PqPluginApp {
    private static $instance = null;

    private $b_title = "";
    private $b_content = "";
    private $b_badge = "";
    private $b_icon = "";
    private $b_shadow = false;
    private $b_style = []; 

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 1. 연속 체이닝 카드 빌더 시발점 (card)
     */
    public function card($title, $content = "") {
        $this->b_title = is_array($title) ? "배열인자오류" : (string)$title;
        $this->b_content = is_array($content) ? "" : (string)$content;
        $this->b_badge = "";
        $this->b_icon = "";
        $this->b_shadow = false;
        $this->b_style = []; 
        return $this; 
    }

    /**
     * 2. 빌더 전용 지능형 뱃지 바인더 (badge)
     */
    public function badge($text) {
        $this->b_badge = is_array($text) ? "" : (string)$text;
        return $this;
    }

    /**
     * 3. 빌더 전용 아이콘 바인더 (icon)
     */
    public function icon($icon_name) {
        $this->b_icon = is_array($icon_name) ? "" : (string)$icon_name;
        return $this;
    }

    /**
     *  4. 빌더 전용 그림자 스킨 바인더 (shadow)
     */
    public function shadow($has_shadow = true) {
        $this->b_shadow = (bool)$has_shadow;
        return $this;
    }

    /**
     *  5. 내용 추가 바인더 (desc)
     */
    public function desc($text) {
        $this->b_content = is_array($text) ? "" : (string)$text;
        return $this;
    }

    /**
     *  6. 데이터 기반 스타일 맵 바인더 (style)
     */
	public function style($style_input) {
		if (is_array($style_input)) {
			$this->b_style = $style_input;
		} else if (is_string($style_input)) {
			// 문자열로 넘어올 경우 카드 스킨 스타일로 자동 매핑
			$this->b_style = ['card' => $style_input];
		}
		return $this; 
	}
    /**
     * 배열 요소의 인덱스를 정밀 분해하여 PHP 8.0 trim 오류 완벽 차단
     */
    private function parseMenu($menu_data) {
        if (empty($menu_data)) return [];
        $result = [];
        $raw_items = [];

        if (is_array($menu_data)) { 
            $raw_items = $menu_data; 
        } else {
            $items = explode(',', $menu_data);
            foreach ($items as $item) { 
                $raw_items[] = explode(':', trim($item)); 
            }
        }
        
        $current_uri = $_SERVER['PATH_INFO'] ?? parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        $current_path = '/' . trim(str_replace('/pq365', '', $current_uri), '/');

        foreach ($raw_items as $item) {
            if (is_array($item)) {
                $name = isset($item[0]) ? trim((string)$item[0]) : "메뉴";
                $link = isset($item[1]) ? trim((string)$item[1]) : "#";
                $icon = isset($item[2]) ? trim((string)$item[2]) : "";
            } else {
                $name = trim((string)$item);
                $link = "#";
                $icon = "";
            }
            
            if ($link === '#' || empty($link)) { $link = "javascript:void(0);"; }

            if (empty($icon)) {
                if (str_contains($name, '홈') || str_contains($name, '메인')) $icon = "house-door";
                elseif (str_contains($name, '대시보드') || str_contains($name, '관제') || str_contains($name, '상태')) $icon = "speedometer2";
                elseif (str_contains($name, '디비') || str_contains($name, 'DB') || str_contains($name, '데이터')) $icon = "database";
                elseif (str_contains($name, '로그') || str_contains($name, '시스템')) $icon = "terminal";
                elseif (str_contains($name, '설정') || str_contains($name, '환경')) $icon = "gear-fill";
                else $icon = "circle";
            }

            $clean_link_path = '/' . trim(str_replace('/pq365', '', parse_url($link, PHP_URL_PATH)), '/');
            $is_active = ($clean_link_path === $current_path) || ($current_path === '/m' && $clean_link_path === '/m');
            
            $result[] = (object)[
                'name'   => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'), 
                'link'   => $link, 
                'icon'   => htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'), 
                'active' => $is_active
            ];
        }
        return $result;
    }
    public function navbar($title = "PQ Mobile", $leftIcon = "list", $rightIcon = "gear") {
        $clean_title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return '<nav class="navbar navbar-dark bg-dark fixed-top px-3 shadow-sm" style="height: 56px;"><div class="container-fluid d-flex justify-content-between align-items-center w-100 p-0"><button class="btn text-white p-0" id="pq-app-left-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#pq_mobile_sidebar"><i class="bi bi-' . $leftIcon . ' fs-4"></i></button><span class="navbar-brand mb-0 h1 mx-auto fs-5 fw-bold text-truncate" style="max-width: 60%;">' . $clean_title . '</span><button class="btn text-white p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#pq_mobile_drawer"><i class="bi bi-' . $rightIcon . ' fs-4"></i></button></div></nav>';
    }

    public function sidebar($user_name = "관리자", $menu_data = null) {
		if ($menu_data === null) {
			return new PQSidebarBuilder($user_name);
		}
        $html = '<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="pq_mobile_sidebar" style="width: 280px;"><div class="offcanvas-header border-bottom border-secondary py-4"><div class="d-flex align-items-center"><div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width:45px;height:45px;"><i class="bi bi-person-badge fs-4 text-white"></i></div><div><h6 class="offcanvas-title fw-bold m-0 text-white">' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . ' 요원</h6><small class="text-secondary" style="font-size:11px;">PQ 관제 권한자</small></div></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div><div class="offcanvas-body p-0"><div class="list-group list-group-flush">';
        $menus = $this->parseMenu($menu_data);
        foreach ($menus as $m) { $bg = $m->active ? 'bg-secondary' : 'bg-transparent'; $html .= '<a href="' . $m->link . '" class="list-group-item list-group-item-action ' . $bg . ' border-0 py-3 px-4 d-flex align-items-center" style="color:#fff!important;"><i class="bi bi-' . $m->icon . ' text-primary me-3 fs-5"></i><span class="text-white" style="font-size:14px;font-weight:500;">' . $m->name . '</span></a>'; }
        $html .= '</div></div></div>'; return $html;
    }

	public function footer($menu_data = null) {
		if ($menu_data === null) {
			return new PQFooterBuilder();
		}
		$html = '<div class="fixed-bottom bg-white border-top shadow-lg" style="height: 60px; z-index: 1030;"><div class="row text-center py-2 m-0 h-100 align-items-center">';
        $menus = $this->parseMenu($menu_data);
        if (!empty($menus)) { $col_width = floor(12 / count($menus)); foreach ($menus as $m) { $text_color = $m->active ? 'text-primary' : 'text-secondary'; $html .= '<div class="col-' . $col_width . ' p-0"><a href="' . $m->link . '" class="nav-link ' . $text_color . ' p-0 d-block text-decoration-none"><i class="bi bi-' . $m->icon . ' d-block fs-5 mb-0"></i><span style="font-size:11px;display:block;margin-top:-2px;">' . $m->name . '</span></a></div>'; } }
        $html .= '</div></div>'; return $html;
    }

	public function menuGroup($group_data = null) {
		if ($group_data === null) {
			return new PQMenuGroupBuilder();
		}
		$menus = $this->parseMenu($group_data);
		if (empty($menus)) {
			return "";
		}
        $menus = $this->parseMenu($group_data); if (empty($menus)) return "";
        $html = '<div class="list-group list-group-flush border-top border-bottom my-2 shadow-sm">';
        foreach ($menus as $m) { $html .= '<a href="' . $m->link . '" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 bg-white"><div class="d-flex align-items-center"><i class="bi bi-' . $m->icon . ' text-primary me-2"></i><span class="fw-semibold text-dark" style="font-size:14px;">' . $m->name . '</span></div><i class="bi bi-chevron-right text-muted small"></i></a>'; }
        $html .= '</div>'; return $html;
    }

    public function scrollMenu($menu_data = "") {
        $menus = $this->parseMenu($menu_data); if (empty($menus)) return "";
        $html = '<div class="pq-scroll-x px-2 py-2 bg-white border-bottom shadow-sm mb-3">';
        foreach ($menus as $m) { $btn_style = $m->active ? 'btn-primary' : 'btn-light text-secondary'; $html .= '<a href="' . $m->link . '" class="btn btn-sm ' . $btn_style . ' rounded-pill px-3 me-2 fw-semibold text-nowrap">' . $m->name . '</a>'; }
        $html .= '</div>'; return $html;
    }

	public function drawer($title = "퀵 제어센터",$content_html = null){
		if ($content_html === null) {
			return new PQDrawerBuilder($title);
	    }
        return '<div class="offcanvas offcanvas-bottom bg-white" tabindex="-1" id="pq_mobile_drawer" style="height:40vh;border-radius:20px 20px 0 0;"><div class="offcanvas-header border-bottom py-3"><h6 class="offcanvas-title fw-bold text-dark"><i class="bi bi-sliders me-2 text-primary"></i>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h6><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div><div class="offcanvas-body p-3">' . $content_html . '</div></div>';
    }

    public function notify($title, $msg) {
        return '<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080; width: 92%; margin-top: 65px;"><div class="toast align-items-center text-white bg-dark border-0 shadow-lg w-100 show" role="alert"><div class="d-flex"><div class="toast-body d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5"></i><div><strong class="me-auto text-warning">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</strong><br><span style="font-size:12px;">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span></div></div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="$(this).closest(\'.toast\').removeClass(\'show\');"></button></div></div></div>';
    }

    /**
     *  카드 스킨 레이아웃 빌더 아웃풋 연산 사수
     */
    public function __toString() {
        $title = (string)$this->b_title;
        $content = (string)$this->b_content;
        $badge = (string)$this->b_badge;
        $icon = (string)$this->b_icon;
        $style = (array)$this->b_style;

        if (empty($icon)) {
            if (str_contains($title, '홈') || str_contains($title, '메인')) $icon = "house-door";
            elseif (str_contains($title, '대시보드') || str_contains($title, '관제') || str_contains($title, '상태')) $icon = "speedometer2";
            elseif (str_contains($title, '디비') || str_contains($title, 'DB') || str_contains($title, '데이터')) $icon = "database";
            elseif (str_contains($title, '로그') || str_contains($title, '터미널')) $icon = "terminal";
            elseif (str_contains($title, '설정') || str_contains($title, '환경')) $icon = "gear-fill";
            elseif (str_contains($title, '알림') || str_contains($title, '공지') || str_contains($title, '경보')) $icon = "bell";
            else $icon = "circle";
        }

        $card_cls = $style['card'] ?? "border-0 bg-white " . ($this->b_shadow ? "shadow" : "shadow-sm");

        $badge_html = "";
        if (!empty($badge)) {
            if (isset($style['badge'])) {
                $badge_cls = $style['badge'];
            } else {
                $badge_cls = "bg-secondary text-white";
                if (str_contains($badge, '정상') || str_contains($badge, '완료') || str_contains($badge, '성공')) $badge_cls = "bg-success-subtle text-success border border-success";
                elseif (str_contains($badge, '위험') || str_contains($badge, '에러') || str_contains($badge, '실패')) $badge_cls = "bg-danger-subtle text-danger border border-danger";
                elseif (str_contains($badge, '대기') || str_contains($badge, '경고')) $badge_cls = "bg-warning-subtle text-warning border border-warning";
            }
            $badge_html = '<span class="badge ' . $badge_cls . ' rounded-pill px-2.5 py-1" style="font-size:11px;">' . htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') . '</span>';
        }

        return '
        <!-- PQ App Builder Component Card -->
        <div class="card rounded-3 mb-3 ' . $card_cls . '">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title fw-bold m-0">
                        <i class="bi bi-' . $icon . ' text-primary me-2"></i>
                        ' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '
                    </h5>
                    ' . $badge_html . '
                </div>
                ' . (!empty($content) ? '<p class="card-text small mb-0 opacity-75">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</p>' : '') . '
            </div>
        </div>';
    }
}
 class PQFooterBuilder {
    private $theme = "";
    private $activeColor = "text-primary"; 
    private $menus = [];
    public function add($title, $url, $icon = "") {
        $this->menus[] = [
            'title' => $title,
            'url'   => $url,
            'icon'  => $icon
        ];
        return $this;
    }
    public function __toString() {
        $app = pq_app();
        $menu_data = [];
        foreach ($this->menus as $m) {
            $menu_data[] = [
                $m['title'],
                $m['url'],
                $m['icon']
            ];
        }
        return $app->footer($menu_data);
    }
}
class PQSidebarBuilder {
	private $theme = "";
	private $activeColor = "text-primary";
    private $user_name;
    private $menus = [];
    public function __construct($user_name) {
        $this->user_name = $user_name;
    }
    public function add($title, $url, $icon = "") {
        $this->menus[] = [
            $title,
            $url,
            $icon
        ];
        return $this;
    }
	public function theme($theme) {
		$this->theme = (string)$theme;
		return $this;
	}
	public function activeColor($color) {
		$this->activeColor = (string)$color;
		return $this;
	}	
    public function __toString() {
        $app = pq_app();
		$html = $app->sidebar(
			$this->user_name,
			$this->menus
		);		
		if ($this->theme == "dark") {
			$html = str_replace(
				"bg-dark",
				"bg-dark text-white",
				$html
			);
		}
		if ($this->theme == "light") {
			$html = str_replace(
				"bg-dark",
				"bg-white text-dark",
				$html
			);
		}
		if ($this->activeColor) {
			$html = str_replace(
				"text-primary",
				$this->activeColor,
				$html
			);
		}
		return $html;		
    }
}
class PQMenuGroupBuilder {
    private $menus = [];
    public function add(
        $title,
        $url,
        $icon = "circle",
        $badge = "",
        $color = "text-primary",
        $badge_bg = "bg-primary text-white"
    ) {
        $this->menus[] = [
            'title'    => $title,
            'url'      => $url,
            'icon'     => $icon,
            'badge'    => $badge,
            'color'    => $color,
            'badge_bg' => $badge_bg
        ];
        return $this;
    }
    public function __toString() {
        $html = '<div class="list-group shadow-sm rounded-3 overflow-hidden">';
        foreach ($this->menus as $m) {
            $html .= '
            <a href="'.$m['url'].'" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-'.$m['icon'].' me-3 fs-5 '.$m['color'].'"></i>
                    <span class="fw-semibold '.$m['color'].'">
                        '.$m['title'].'
                    </span>
                </div>
                <span class="badge rounded-pill '.$m['badge_bg'].' px-2 py-1">
                    '.$m['badge'].'
                </span>
            </a>';
        }
        $html .= '</div>';
        return $html;
    }
}
class PQDrawerBuilder {
    private $title;
    private $buttons = [];
    public function __construct($title) {
        $this->title = $title;
    }
    public function button($text, $icon = "", $color = "dark") {
        $this->buttons[] = [
            'text'  => $text,
            'icon'  => $icon,
            'color' => $color
        ];
        return $this;
    }
    public function __toString() {
        $html = '<div class="list-group list-group-flush">';
        foreach ($this->buttons as $btn) {
            $html .= '
            <button class="list-group-item bg-transparent text-start text-'.$btn['color'].' py-2 border-0">
                <i class="bi bi-'.$btn['icon'].' me-2"></i>
                '.$btn['text'].'
            </button>';
        }
        $html .= '</div>';
        return pq_app()->drawer(
            $this->title,
            $html
        );
    }
}
if (!function_exists('pq_app')) {
    function pq_app() { return PqPluginApp::getInstance(); }
}
if (!function_exists('app_pq')) {
    function app_pq() { return pq_app(); }
}

$GLOBALS['app'] = pq_app(); 
$app = $GLOBALS['app'];
?>
