[[
/**
 * =========================================================
 * FILENAME  : /init.pq
 * COMPONENT : Global Variables & System Metadata Initialization
 *  init.pq - A place to store global variables
 *  init.pq is where global variables are stored; they are designated by prefixing them with an underscore (_) (e.g., @_nowdate).
 * =========================================================
 */

global @_year, @_mon, @_day, @_month, @_time, @_week, @_ampm, @_time1, @_time2, @_time3, @_datetime, @_date, @_passdate, @_nowdate, @_reply_depth;
global @_pq_version, @_pq_down;
global $_arrUse, $_arrState, $_core_info,$_core_info_en, $_plugin_info,$_plugin_info_en,$_menu_ko,$_menu_en,$_arrDays;
global @_editor_attach_max_file,@_editor_attach_max,@_editor_attach_size,@_editor_attach_ext;

date_default_timezone_set("Asia/Seoul");

/* --- [1. DATE & TIME GLOBAL VARIABLES] --- */
@_year        = date("Y", time());
@_mon         = date("m", time());
@_day         = date("d", time());
@_month       = date("n", time());
@_time        = date("H", time());
@_week        = date("w", time());
@_ampm        = date("a", time());
@_time1       = date("h", time());
@_time2       = date("i", time());
@_time3       = date("s", time());
@_datetime    = date("Y-m-d H:i:s", time());
@_date        = @_year . '-' . @_mon . '-' . @_day;
@_passdate    = @_year . @_mon . @_day;
@_nowdate     = time();
@_reply_depth = 3;

/* --- [2. SYSTEM SYSTEM CONSTANTS & METADATA] --- */
$_arrUse   = array('사용여부', '사용', '미사용');
$_arrState = array('승인여부', '승인대기', '승인', '일시중지', '탈퇴');
$_arrDays   = array("일","월","화","수","목","금","토");

@_pq_version = PQ_VERSION;
@_pq_down    = PQ_DN;

$_core_info = [
	'http' => 'http 통신 관련 함수들 입니다. ',
	'big' => '대용량의 데이터를 안전하게 작업할 수 있도록 돕는 함수들입니다. ',
	'html' => 'html 출력단에  관련 함수들 입니다. 보안,태그처리등 ',
	'auth' => '로그인과 관련된 함수들 입니다. ',
	'excel' => '엑셀파일 다운로드 업로드를 쉽게 할수 있는 함수를 제공합니다. ',
	'lock' => '특정 작업에 락을 걸어 안전한 작업을 지원합니다. ',
	'session' => 'session에 관련된  다양한 함수를 지원합니다. ',
	'pq' => '기존 함수의 기능을 확장하였을 경우 지원되는 함수 입니다. 가령 php의 print대신 pq의 pq.print함수 ',
	'rgx' => 'php의 정규표현식을 사용자가 좀더  알아보기 편하고 쉽게 지원하는 함수들입니다.',
	'db' => '데이터 베이스에 관련된 함수 들입니다.',
	'cookie' => '쿠키를 다루기 위한  다양한 함수들이 있습니다.',
	'file' => '파일조작,  디렉토리 찾기,수정,삭제등의 함수들 지원합니다.',
	'text' => '테스트를 다루기 위한  다양한 함수들이 있습니다.',
	'date' => '날짜를 사용자가 원하는 형태로 변환하는 다양한 함수를 지원합니다.',
	'text' => '텍스트에 다양한 효과를 부여하는 함수를 지원합니다.',
	'form' => 'form에서 넘어온 값을 다루는   다양한 함수들이 있습니다.'
];
$_plugin_info = [
	'app' => '모바일 웹페이지를  빠르게 만들수 있는 함수들을 제공합니다. ',
	'api' => 'PHP 순정 스트림 컨텍스트만을 사용 호스팅 환경에서 타임아웃 및 버퍼 오염 없이 외부 API 통신을 수행합니다.',
	'auto' => 'auto 서버 & 클라이언트 자동화 및 크론탭 모듈입니다.',
	'barcode' => '바코드 규격(CODE128, EAN13 코어 모듈입니다.',
	'chart' => '챠트를 그리는 모듈입니다.',
	'crawler' => '웹페이지의 데이터를 수집 하는 함수를 지원합니다.',
	'email' => 'smtp 이메일 발송 함수를 지원합니다..',
	'pdf' => 'tcpdf 를 이용한 pdf 모듈 표준	입니다.',
	'qrcode' => 'QR코드 모듈입니다.'
];
//	'chat' => 'HTML5 EventSource 기반 실시간 스트리밍 모듈입니다.',
$_core_info_en = [
    'http'    => 'HTTP communication and network request helpers.',
	'big' => 'These are functions that help you safely handle large volumes of data. ',
    'html'    => 'HTML sanitization, security, and rendering pipeline helpers.',
    'auth'    => 'Authentication, session guards, and user login utilities.',
    'excel'   => 'Excel file import/export acceleration utilities.',
	'lock' => 'It supports safe operations by applying a lock to specific tasks. ',
    'session' => 'Comprehensive session management and state control.',
    'pq'      => 'Extended core functionality overrides (e.g., pq.print, pq.use).',
    'rgx'     => 'Fluent readable regex builder and pattern matcher.',
    'db'      => 'Database ORM, Fluent Query Builder, and connection utilities.',
    'cookie'  => 'Cookie parsing, encryption, and storage wrappers.',
    'file'    => 'File system manipulation, directory inspection, and stream I/O.',
    'text'    => 'Text transformation, sanitization, truncation, and formatting.',
    'date'    => 'Date manipulation, timezone formatting, and timestamp parsing.',
    'form'    => 'Form data parsing, sanitization, and input validation.'
];

$_plugin_info_en = [
    'app'     => 'Rapid mobile web application UI components and layout builders.',
    'api'     => 'Stream context HTTP client for zero-dependency external API calls.',
    'auto'    => 'Server and client-side (HTML5 Worker) crontab task scheduler.',
    'barcode' => 'Barcode generator core supporting CODE128 and EAN13 formats.',
	'chart' => 'This is a module for drawing charts.',
	'crawler' => 'Supports functions for collecting web page data.',
	'email' => 'Supports functions for sending SMTP emails.',
    'pdf'     => 'Standardized TCPDF bridge engine for dynamic document rendering.',
    'qrcode'  => 'Vector and raster QR code generator engine.'
];
//    'chat'    => 'HTML5 EventSource based real-time SSE streaming communication.',

$_menu_ko = ["시작하기","다운로드","PQ 문법","CORE API","PLUGIN API","패키지","커뮤니티","공지사항"];
$_menu_en = ["Get Started", "Download", "PQ Syntax", "CORE API", "PLUGIN API", "Package", "Community", "Announcement"];


/* Custom user extensions space */
@_editor_attach_max_file = "5";
@_editor_attach_max="5242880";
@_editor_attach_size ="5M";
@_editor_attach_ext ="gif,png,jpg,xls,pdf,hwp,doc,ppt,pptx,xlsx,mp3,mp4,swf,flv,avi,wmv,mov,css,js,html,htm";
]]