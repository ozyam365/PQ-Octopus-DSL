[[
/**
 * =========================================================
 * PQ CONFIG FILE - 검색엔진 유입 분석 설정 데이터
 * FILENAME : /set/cfg_search_list.pq
 * UPDATE :  2026-10-09 PM 10:10
 * =========================================================
 */
 // cfg_search_list.pq 소스 상단
global @search_site_list;

$search_site_list = [
	'naver'      => ['site_regex' => 'search\.naver\.com|m\.search\.naver\.com', 'query_param' => 'query='],
	'google'     => ['site_regex' => 'google\.',                                 'query_param' => 'q='],
	'daum'       => ['site_regex' => 'search\.daum\.net|m\.search\.daum\.net',   'query_param' => 'q='],
	'bing'       => ['site_regex' => 'bing\.com',                                'query_param' => 'q='],
	'nate'       => ['site_regex' => 'search\.nate\.com',                        'query_param' => 'query='],
	'duckduckgo' => ['site_regex' => 'duckduckgo\.com',                          'query_param' => 'q='],
	'yahoo'      => ['site_regex' => 'search\.yahoo\.com',                       'query_param' => 'p='],
	'yandex'     => ['site_regex' => 'yandex\.',                                 'query_param' => 'text='],

	// AI 검색 & 어시스턴트 유입 (최신 트렌드 대응)
	'chatgpt'    => ['site_regex' => 'chatgpt\.com|openai\.com',                 'query_param' => 'q='],
	'perplexity' => ['site_regex' => 'perplexity\.ai',                           'query_param' => 'q='],
	'claude'     => ['site_regex' => 'claude\.ai',                               'query_param' => 'q=']
];
]]
