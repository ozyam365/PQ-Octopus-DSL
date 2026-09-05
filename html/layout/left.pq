<!-- /html/layout/left.pq -->
[[
@uri = PQRouter.uri();
@groups = [
    'db',
    'file',
    'http',
    'session',
    'cookie',	
    'date',
    'form',
    'text',
    'excel',
    'pdf',
    'app',
    'auto',
    'chat',
    'rgx',
    'api',
    'obj',
    'qrcode',
    'barcode'
];
@is = [];

foreach(@groups as @group){
    // 🕵️ [조건절 수색 보정]: 홑따옴표 내부에서 변수 치환이 막히던 현상을 쌍따옴표 문자열로 교정하여 동적 매칭 성공 유도
    @is[@group] = str_contains(@uri, "/@group/");
}
// 🕵️ [세션 치환 폭발 진압]: 골뱅이 오번역으로 이중 달러 기호가 엮이던 누수를 막기 위해 순수 PHP 표준 슈퍼글로벌 기호인 $_SESSION 으로 안전 보정
@sidebar_mode = $_SESSION['pq_mode'] ?? 'all';	

]]
<div class="pq-sidebar">
    <!-- 대시보드 -->
    <a href="[[=PQ_BASE]]/lab" class="nav-dashboard">
        <i class="bi bi-compass me-2"></i> <b>Police Line</b>
    </a>
  [[ if (@sidebar_mode === 'core' || @sidebar_mode === 'all' ): ]]	
    <!-- [CORE PAGES] -->
    <div class="nav-category-static">Core Pages</div>

    <!-- DB function -->
    <div class="nav-sub-group [[= @is['db'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#db-items" aria-expanded="[[= @is['db'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-database me-2"></i>DB function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['db'] ? 'show' : '' ]]" id="db-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('db/chain')]]" class="nav-link-pq [[=PQRouter.active('db/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>
			<li><a href="[[=PQRouter.url('db/connect')]]" class="nav-link-pq [[=PQRouter.active('/db/connect')]]">connect(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('db/query')]]" class="nav-link-pq [[=PQRouter.active('/db/query')]]">query(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('db/insert')]]" class="nav-link-pq [[=PQRouter.active('/db/insert')]]">insert(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('db/where')]]" class="nav-link-pq [[=PQRouter.active('/db/where')]]">where(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('db/update')]]" class="nav-link-pq [[=PQRouter.active('/db/update')]]">update(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('db/delete')]]" class="nav-link-pq [[=PQRouter.active('/db/delete')]]">delete(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/sort')]]" class="nav-link-pq [[=PQRouter.active('/db/sort')]]">sort(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/limit')]]" class="nav-link-pq [[=PQRouter.active('/db/limit')]]">limit(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/row')]]" class="nav-link-pq [[=PQRouter.active('/db/row')]]">row(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/cnt')]]" class="nav-link-pq [[=PQRouter.active('/db/cnt')]]">cnt(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/has')]]" class="nav-link-pq [[=PQRouter.active('/db/has')]]">has(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/make')]]" class="nav-link-pq [[=PQRouter.active('/db/make')]]">make(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/clear')]]" class="nav-link-pq [[=PQRouter.active('/db/clear')]]">clear(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/join')]]" class="nav-link-pq [[=PQRouter.active('/db/join')]]">join(); <span class="pq-dot"></span></a></li>						
            <li><a href="[[=PQRouter.url('db/pluck')]]" class="nav-link-pq [[=PQRouter.active('/db/pluck')]]">pluck(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/and')]]" class="nav-link-pq [[=PQRouter.active('/db/and')]]">and(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('db/or')]]" class="nav-link-pq [[=PQRouter.active('/db/or')]]">or(); <span class="pq-dot"></span></a></li>	
			<li><a href="[[=PQRouter.url('db/like')]]" class="nav-link-pq [[=PQRouter.active('/db/like')]]">like(); <span class="pq-dot"></span></a></li>
			<li><a href="[[=PQRouter.url('db/list')]]" class="nav-link-pq [[=PQRouter.active('/db/list')]]">list(); <span class="pq-dot"></span></a></li>			
        </ul>
    </div>

    <!-- FILE function -->
    <div class="nav-sub-group [[= @is['file'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#file-items" aria-expanded="[[= @is['file'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-file me-2"></i>FILE function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['file'] ? 'show' : '' ]]" id="file-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('file/chain')]]" class="nav-link-pq [[=PQRouter.active('/file/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>
            <li class="sub-title"> [ Upload function ]</li>			
            <li><a href="[[=PQRouter.url('file/upload')]]" class="nav-link-pq [[=PQRouter.active('/file/upload')]]">upload();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/path')]]" class="nav-link-pq [[=PQRouter.active('/file/path')]]">path();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/allow')]]" class="nav-link-pq [[=PQRouter.active('/file/allow')]]">allow();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/limit')]]" class="nav-link-pq [[=PQRouter.active('/file/limit')]]">limit();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/rename')]]" class="nav-link-pq [[=PQRouter.active('/file/rename')]]">rename();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/random')]]" class="nav-link-pq [[=PQRouter.active('/file/random')]]">random();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/image')]]" class="nav-link-pq [[=PQRouter.active('/file/image')]]">image();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('file/save')]]" class="nav-link-pq [[=PQRouter.active('/file/save')]]">save();<span class="pq-dot"></span></a></li>						
            <li class="sub-title"> [ File function ]</li>			
            <li><a href="[[=PQRouter.url('file/read')]]" class="nav-link-pq [[=PQRouter.active('/file/read')]]">read();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/write')]]" class="nav-link-pq [[=PQRouter.active('/file/write')]]">write();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/append')]]" class="nav-link-pq [[=PQRouter.active('/file/append')]]">append();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/has')]]" class="nav-link-pq [[=PQRouter.active('/file/has')]]">has();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/delete')]]" class="nav-link-pq [[=PQRouter.active('/file/delete')]]">delete();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/copy')]]" class="nav-link-pq [[=PQRouter.active('/file/copy')]]">copy();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/move')]]" class="nav-link-pq [[=PQRouter.active('/file/move')]]">move();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('file/touch')]]" class="nav-link-pq [[=PQRouter.active('/file/touch')]]">touch();<span class="pq-dot"></span></a></li>	
            <li class="sub-title"> [ Directory function ]</li>			
            <li><a href="[[=PQRouter.url('file/mkdir')]]" class="nav-link-pq [[=PQRouter.active('/file/mkdir')]]">mkdir();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/listdir')]]" class="nav-link-pq [[=PQRouter.active('/file/listdir')]]">listdir();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/scan')]]" class="nav-link-pq [[=PQRouter.active('/file/scan')]]">scan();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/clear')]]" class="nav-link-pq [[=PQRouter.active('/file/clear')]]">clear();<span class="pq-dot"></span></a></li>
            <li class="sub-title"> [ Info function ]</li>			
            <li><a href="[[=PQRouter.url('file/size')]]" class="nav-link-pq [[=PQRouter.active('/file/size')]]">size();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/ext')]]" class="nav-link-pq [[=PQRouter.active('/file/ext')]]">ext();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/name')]]" class="nav-link-pq [[=PQRouter.active('/file/name')]]">name();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/dir')]]" class="nav-link-pq [[=PQRouter.active('/file/dir')]]">dir();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/mimeType')]]" class="nav-link-pq [[=PQRouter.active('/file/mimeType')]]">mimeType();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/modified')]]" class="nav-link-pq [[=PQRouter.active('/file/modified')]]">modified();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/safeName')]]" class="nav-link-pq [[=PQRouter.active('/file/safeName')]]">safeName();<span class="pq-dot"></span></a></li>	
            <li class="sub-title"> [ Output function ]</li>			
            <li><a href="[[=PQRouter.url('file/download')]]" class="nav-link-pq [[=PQRouter.active('/file/download')]]">download();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/inline')]]" class="nav-link-pq [[=PQRouter.active('/file/inline')]]">inline();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/stream')]]" class="nav-link-pq [[=PQRouter.active('/file/stream')]]">stream();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('file/url')]]" class="nav-link-pq [[=PQRouter.active('/file/url')]]">url();<span class="pq-dot"></span></a></li>		
        </ul>
    </div>
    <!-- HTTP function -->
    <div class="nav-sub-group [[= @is['http'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#http-items" aria-expanded="[[= @is['http'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-browser-chrome me-2"></i>HTTP function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['http'] ? 'show' : '' ]]" id="http-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('http/chain')]]" class="nav-link-pq [[=PQRouter.active('/http/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>					
            <li><a href="[[=PQRouter.url('http/get')]]" class="nav-link-pq [[=PQRouter.active('/http/get')]]">get();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/post')]]" class="nav-link-pq [[=PQRouter.active('/http/post')]]">post();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/put')]]" class="nav-link-pq [[=PQRouter.active('/http/put')]]">put();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/delete')]]" class="nav-limnk-pq [[=PQRouter.active('/http/delete')]]">delete();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/header')]]" class="nav-link-pq [[=PQRouter.active('/http/header')]]">header();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/timeout')]]" class="nav-link-pq [[=PQRouter.active('/http/timeout')]]">timeout();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/json')]]" class="nav-link-pq [[=PQRouter.active('/http/json')]]">json();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/all')]]" class="nav-link-pq [[=PQRouter.active('/http/all')]]">all();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/safe')]]" class="nav-link-pq [[=PQRouter.active('/http/safe')]]">safe();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/redirect')]]" class="nav-link-pq [[=PQRouter.active('/http/redirect')]]">redirect();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('http/msg')]]" class="nav-link-pq [[=PQRouter.active('/http/msg')]]">msg();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('http/confirm')]]" class="nav-link-pq [[=PQRouter.active('/http/confirm')]]">confirm();</a></li>
            <li><a href="[[=PQRouter.url('http/go')]]" class="nav-link-pq [[=PQRouter.active('/http/go')]]">go();</a></li>
            <li><a href="[[=PQRouter.url('http/back')]]" class="nav-link-pq [[=PQRouter.active('/http/back')]]">back();</a></li>
            <li><a href="[[=PQRouter.url('http/close')]]" class="nav-link-pq [[=PQRouter.active('/http/close')]]">close();</a></li>			
            <li><a href="[[=PQRouter.url('http/refresh')]]" class="nav-link-pq [[=PQRouter.active('/http/refresh')]]">refresh();</a></li>			
        </ul>
    </div>
    <!-- SESSION function -->
    <div class="nav-sub-group [[= @is['session'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#session-items" aria-expanded="[[= @is['session'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-door-open me-2"></i>SESSION function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['session'] ? 'show' : '' ]]" id="session-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('session/chain')]]" class="nav-link-pq [[=PQRouter.active('/session/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>		
            <li><a href="[[=PQRouter.url('session/set')]]" class="nav-link-pq [[=PQRouter.active('/session/set')]]">set();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/get')]]" class="nav-link-pq [[=PQRouter.active('/session/get')]]">get();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/all')]]" class="nav-link-pq [[=PQRouter.active('/session/all')]]">all();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/login')]]" class="nav-link-pq [[=PQRouter.active('/session/login')]]">login();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/user')]]" class="nav-link-pq [[=PQRouter.active('/session/user')]]">user();<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('session/check')]]" class="nav-link-pq [[=PQRouter.active('/session/check')]]">check();<span class="pq-dot"></span></a></li>					
            <li><a href="[[=PQRouter.url('session/auth')]]" class="nav-link-pq [[=PQRouter.active('/session/auth')]]">auth();<span class="pq-dot"></span></a></li>			
			<li><a href="[[=PQRouter.url('session/guard')]]" class="nav-link-pq [[=PQRouter.active('/session/guard')]]">guard();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/flash')]]" class="nav-link-pq [[=PQRouter.active('/session/flash')]]">flash();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/hasFlash')]]" class="nav-link-pq [[=PQRouter.active('/session/hasFlash')]]">hasFlash();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('session/clear')]]" class="nav-link-pq [[=PQRouter.active('/session/clear')]]">clear();<span class="pq-dot"></span></a></li>
			<li><a href="[[=PQRouter.url('session/destroy')]]" class="nav-link-pq [[=PQRouter.active('/session/destroy')]]">destroy();<span class="pq-dot"></span></a></li>
        </ul>				
    </div>	
    <!-- COOKIE function -->
    <div class="nav-sub-group [[= @is['cookie'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#cookie-items" aria-expanded="[[= @is['cookie'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-cookie me-2"></i>cookie function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['cookie'] ? 'show' : '' ]]" id="cookie-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('cookie/set')]]" class="nav-link-pq [[=PQRouter.active('/cookie/set')]]">set();</a></li>
            <li><a href="[[=PQRouter.url('cookie/get')]]" class="nav-link-pq [[=PQRouter.active('/cookie/get')]]">get()</a></li>
            <li><a href="[[=PQRouter.url('cookie/has')]]" class="nav-link-pq [[=PQRouter.active('/cookie/has')]]">has();</a></li>
            <li><a href="[[=PQRouter.url('cookie/check')]]" class="nav-link-pq [[=PQRouter.active('/cookie/check')]]">check();</a></li>			
            <li><a href="[[=PQRouter.url('cookie/del')]]" class="nav-link-pq [[=PQRouter.active('/cookie/del')]]">del();</a></li>
        </ul>				
    </div>		
    <div class="nav-sub-group [[= @is['form'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#form-items" aria-expanded="[[= @is['form'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-input-cursor  me-2"></i>Form function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>	
    <div class="collapse [[= @is['form'] ? 'show' : '' ]]" id="form-items">
        <ul class="sub-menu">		
            <li><a href="[[=PQRouter.url('form/chain')]]" class="nav-link-pq [[=PQRouter.active('/form/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>				
            <li><a href="[[=PQRouter.url('form/form')]]" class="nav-link-pq [[=PQRouter.active('/form/form')]]">form<span class="pq-dot"></span></a></li>							
            <li><a href="[[=PQRouter.url('form/all')]]" class="nav-link-pq [[=PQRouter.active('/form/all')]]">all();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/get')]]" class="nav-link-pq [[=PQRouter.active('/form/get')]]">get();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/only')]]" class="nav-link-pq [[=PQRouter.active('/form/only')]]">only();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/except')]]" class="nav-link-pq [[=PQRouter.active('/form/except')]]">except();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/trim')]]" class="nav-link-pq [[=PQRouter.active('/form/trim')]]">trim();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/safe')]]" class="nav-link-pq [[=PQRouter.active('/form/safe')]]">safe();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/set')]]" class="nav-link-pq [[=PQRouter.active('/form/set')]]">set();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/required')]]" class="nav-link-pq [[=PQRouter.active('/form/required')]]">required();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/fail')]]" class="nav-link-pq [[=PQRouter.active('/form/fail')]]">fail();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/errors')]]" class="nav-link-pq [[=PQRouter.active('/form/errors')]]">errors();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/old')]]" class="nav-link-pq [[=PQRouter.active('/form/old')]]">old();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('form/clearOld')]]" class="nav-link-pq [[=PQRouter.active('/form/clearOld')]]">clearOld();<span class="pq-dot"></span></a></li>						
        </ul>
    </div>	
    <div class="nav-sub-group [[= @is['date'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#date-items" aria-expanded="[[= @is['date'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-calendar-check me-2"></i>Date function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['date'] ? 'show' : '' ]]" id="date-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('date/chain')]]" class="nav-link-pq [[=PQRouter.active('/date/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>				
            <li><a href="[[=PQRouter.url('date/parse')]]" class="nav-link-pq [[=PQRouter.active('/date/parse')]]">parse();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/now')]]" class="nav-link-pq [[=PQRouter.active('/date/now')]]">now();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/today')]]" class="nav-link-pq [[=PQRouter.active('/date/today')]]">today();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/year')]]" class="nav-link-pq [[=PQRouter.active('/date/year')]]">year();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/month')]]" class="nav-link-pq [[=PQRouter.active('/date/month')]]">month();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/day')]]" class="nav-link-pq [[=PQRouter.active('/date/day')]]">day();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/week')]]" class="nav-link-pq [[=PQRouter.active('/date/week')]]">week();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('date/timestamp')]]" class="nav-link-pq [[=PQRouter.active('/date/timestamp')]]">timestamp();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/format')]]" class="nav-link-pq [[=PQRouter.active('/date/format')]]">format();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/addYear')]]" class="nav-link-pq [[=PQRouter.active('/date/addYear')]]">addYear();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/subYear')]]" class="nav-link-pq [[=PQRouter.active('/date/subYear')]]">subYear();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('date/addMonth')]]" class="nav-link-pq [[=PQRouter.active('/date/addMonth')]]">addMonth();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/subMonth')]]" class="nav-link-pq [[=PQRouter.active('/date/subMonth')]]">subMonth();<span class="pq-dot"></span></a></li>			
			<li><a href="[[=PQRouter.url('date/addDay')]]" class="nav-link-pq [[=PQRouter.active('/date/addDay')]]">addDay();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/subDay')]]" class="nav-link-pq [[=PQRouter.active('/date/subDay')]]">subDay();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/addHour')]]" class="nav-link-pq [[=PQRouter.active('/date/addHour')]]">addHour();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/subHour')]]" class="nav-link-pq [[=PQRouter.active('/date/subHour')]]">subHour();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/addMinute')]]" class="nav-link-pq [[=PQRouter.active('/date/addMinute')]]">addMinute();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/subMinute')]]" class="nav-link-pq [[=PQRouter.active('/date/subMinute')]]">subMinute();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/firstDay')]]" class="nav-link-pq [[=PQRouter.active('/date/firstDay')]]">firstDay();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/lastDay')]]" class="nav-link-pq [[=PQRouter.active('/date/lastDay')]]">lastDay();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/startOfMonth')]]" class="nav-link-pq [[=PQRouter.active('/date/startOfMonth')]]">startOfMonth();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/endOfMonth')]]" class="nav-link-pq [[=PQRouter.active('/date/endOfMonth')]]">endOfMonth();<span class="pq-dot"></span></a></li>			
	        <li><a href="[[=PQRouter.url('date/copy')]]" class="nav-link-pq [[=PQRouter.active('/date/copy')]]">copy();<span class="pq-dot"></span></a></li>							
            <li><a href="[[=PQRouter.url('date/diff')]]" class="nav-link-pq [[=PQRouter.active('/date/diff')]]">diff();<span class="pq-dot"></span></a></li>					
            <li><a href="[[=PQRouter.url('date/diffDays')]]" class="nav-link-pq [[=PQRouter.active('/date/diffDays')]]">diffDays();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('date/diffHours')]]" class="nav-link-pq [[=PQRouter.active('/date/diffHours')]]">diffHours();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('date/isToday')]]" class="nav-link-pq [[=PQRouter.active('/date/isToday')]]">isToday();<span class="pq-dot"></span></a></li>						
            <li><a href="[[=PQRouter.url('date/isPast')]]" class="nav-link-pq [[=PQRouter.active('/date/isPast')]]">isPast();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('date/isFuture')]]" class="nav-link-pq [[=PQRouter.active('/date/isFuture')]]">isFuture();<span class="pq-dot"></span></a></li>						
            <li><a href="[[=PQRouter.url('date/isWeekend')]]" class="nav-link-pq [[=PQRouter.active('/date/isWeekend')]]">isWeekend();<span class="pq-dot"></span></a></li>			
			<li><a href="[[=PQRouter.url('date/humanize')]]" class="nav-link-pq [[=PQRouter.active('/date/humanize')]]">humanize();<span class="pq-dot"></span></a></li>									
        </ul>
    </div>
    <div class="nav-sub-group [[= @is['text'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#text-items" aria-expanded="[[= @is['text'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-blockquote-left me-2"></i>Text function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['text'] ? 'show' : '' ]]" id="text-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('text/chain')]]" class="nav-link-pq [[=PQRouter.active('/text/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>						
            <li><a href="[[=PQRouter.url('text/upper')]]" class="nav-link-pq [[=PQRouter.active('/text/upper')]]">upper();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/length')]]" class="nav-link-pq [[=PQRouter.active('/text/length')]]">length();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('text/cut')]]" class="nav-link-pq [[=PQRouter.active('/text/cut')]]">cut();<span class="pq-dot"></span></a></li>
			 <li><a href="[[=PQRouter.url('text/hcut')]]" class="nav-link-pq [[=PQRouter.active('/text/hcut')]]">hcut();<span class="pq-dot"></span></a></li>			
			 <li><a href="[[=PQRouter.url('text/hancut')]]" class="nav-link-pq [[=PQRouter.active('/text/hancut')]]">hancut();<span class="pq-dot"></span></a></li>						 
            <li><a href="[[=PQRouter.url('text/contains')]]" class="nav-link-pq [[=PQRouter.active('/text/contains')]]">contains();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/replace')]]" class="nav-link-pq [[=PQRouter.active('/text/replace')]]">replace();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/split')]]" class="nav-link-pq [[=PQRouter.active('/text/split')]]">split();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/join')]]" class="nav-link-pq [[=PQRouter.active('/text/join')]]">join();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/isEmail')]]" class="nav-link-pq [[=PQRouter.active('/text/isEmail')]]">isEmail();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/isUrl')]]" class="nav-link-pq [[=PQRouter.active('/text/isUrl')]]">isUrl();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/slug')]]" class="nav-link-pq [[=PQRouter.active('/text/slug')]]">slug();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/escapeHtml')]]" class="nav-link-pq [[=PQRouter.active('/text/escapeHtml')]]">escapeHtml();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/random')]]" class="nav-link-pq [[=PQRouter.active('/text/random')]]">random();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/uuid')]]" class="nav-link-pq [[=PQRouter.active('/text/uuid')]]">uuid();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('text/format')]]" class="nav-link-pq [[=PQRouter.active('/text/format')]]">format();<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('text/sort')]]" class="nav-link-pq [[=PQRouter.active('/text/sort')]]">sort();<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('text/pad')]]" class="nav-link-pq [[=PQRouter.active('/text/pad')]]">pad();<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('text/money')]]" class="nav-link-pq [[=PQRouter.active('/text/money')]]">money();<span class="pq-dot"></span></a></li>				
            <li><a href="[[=PQRouter.url('text/ext')]]" class="nav-link-pq [[=PQRouter.active('/text/ext')]]">ext();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('text/strip')]]" class="nav-link-pq [[=PQRouter.active('/text/strip')]]">strip();<span class="pq-dot"></span></a></li>				
            <li><a href="[[=PQRouter.url('text/phone')]]" class="nav-link-pq [[=PQRouter.active('/text/phone')]]">phone();<span class="pq-dot"></span></a></li>						
        </ul>
    </div>		
    <div class="nav-sub-group [[= @is['excel'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#excel-items" aria-expanded="[[= @is['excel'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-file-earmark-spreadsheet me-2"></i>Excel function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['excel'] ? 'show' : '' ]]" id="excel-items">
        <ul class="sub-menu">	
            <li><a href="[[=PQRouter.url('excel/chain')]]" class="nav-link-pq [[=PQRouter.active('/excel/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('excel/make')]]" class="nav-link-pq [[=PQRouter.active('/excel/make')]]">make();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/sheet')]]" class="nav-link-pq [[=PQRouter.active('/excel/sheet')]]">sheet();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/header')]]" class="nav-link-pq [[=PQRouter.active('/excel/header')]]">header();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/autoHeader')]]" class="nav-link-pq [[=PQRouter.active('/excel/autoHeader')]]">autoHeader();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/map')]]" class="nav-link-pq [[=PQRouter.active('/excel/map')]]">map();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/data')]]" class="nav-link-pq [[=PQRouter.active('/excel/data')]]">data();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/download')]]" class="nav-link-pq [[=PQRouter.active('/excel/download')]]">download();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/save')]]" class="nav-link-pq [[=PQRouter.active('/excel/save')]]">save();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('excel/upload')]]" class="nav-link-pq [[=PQRouter.active('/excel/upload')]]">upload();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>
	    <div class="nav-sub-group [[= @is['rgx'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#rgx-items" aria-expanded="[[= @is['rgx'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-person-circle me-2"></i>Human rgx function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['rgx'] ? 'show' : '' ]]" id="rgx-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('rgx/chain')]]" class="nav-link-pq [[=PQRouter.active('/rgx/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('rgx/target')]]" class="nav-link-pq [[=PQRouter.active('/rgx/target')]]">target();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/filter')]]" class="nav-link-pq [[=PQRouter.active('/rgx/filter')]]">filter();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/on')]]" class="nav-link-pq [[=PQRouter.active('/rgx/on')]]">on();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/clean')]]" class="nav-link-pq [[=PQRouter.active('/rgx/clean')]]">clean();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('rgx/find')]]" class="nav-link-pq [[=PQRouter.active('/rgx/find')]]">find();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/replace')]]" class="nav-link-pq [[=PQRouter.active('/rgx/replace')]]">replace();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/count')]]" class="nav-link-pq [[=PQRouter.active('/rgx/count')]]">count();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('rgx/first')]]" class="nav-link-pq [[=PQRouter.active('/rgx/first')]]">first();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('rgx/last')]]" class="nav-link-pq [[=PQRouter.active('/rgx/last')]]">last();<span class="pq-dot"></span></a></li>						
        </ul>
    </div>		
    <div class="nav-sub-group [[= @is['obj'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#obj-items" aria-expanded="[[= @is['obj'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-file-earmark-spreadsheet me-2"></i>Obj Syntex</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['obj'] ? 'show' : '' ]]" id="obj-items">
        <ul class="sub-menu">	
            <li><a href="[[=PQRouter.url('obj/obj_start')]]" class="nav-link-pq [[=PQRouter.active('/obj/obj_start')]]">obj_start<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('obj/have')]]" class="nav-link-pq [[=PQRouter.active('/obj/have')]]">have<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('obj/self')]]" class="nav-link-pq [[=PQRouter.active('/obj/self')]]">self<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('obj/child')]]" class="nav-link-pq [[=PQRouter.active('/obj/child')]]">child<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('obj/when')]]" class="nav-link-pq [[=PQRouter.active('/obj/when')]]">when();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('obj/obj_end')]]" class="nav-link-pq [[=PQRouter.active('/obj/obj_end')]]">obj_end<span class="pq-dot"></span></a></li>
        </ul>
    </div>	
  [[ endif; ]]		
  [[ if (@sidebar_mode === 'plugin' || @sidebar_mode === 'all' ): ]]
      <!-- [Plugin PAGES] -->
    <div class="nav-category-static">Plugin Pages</div>	
    <div class="nav-sub-group [[= @is['pdf'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#pdf-items" aria-expanded="[[= @is['pdf'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-filetype-pdf me-2"></i>Pdf function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['pdf'] ? 'show' : '' ]]" id="pdf-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('pdf/chain')]]" class="nav-link-pq [[=PQRouter.active('/pdf/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>	
            <li><a href="[[=PQRouter.url('pdf/init')]]" class="nav-link-pq [[=PQRouter.active('/pdf/init')]]">init();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('pdf/font')]]" class="nav-link-pq [[=PQRouter.active('/pdf/font')]]">font();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('pdf/content')]]" class="nav-link-pq [[=PQRouter.active('/pdf/content')]]">content();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('pdf/save')]]" class="nav-link-pq [[=PQRouter.active('/pdf/save')]]">save();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('pdf/render')]]" class="nav-link-pq [[=PQRouter.active('/pdf/render')]]">render();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>		
    <div class="nav-sub-group [[= @is['app'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#app-items" aria-expanded="[[= @is['app'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-android me-2"></i>App function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['app'] ? 'show' : '' ]]" id="app-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('app/chain')]]" class="nav-link-pq [[=PQRouter.active('/app/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('app/navbar')]]" class="nav-link-pq [[=PQRouter.active('/app/navbar')]]">navbar(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/sidebar')]]" class="nav-link-pq [[=PQRouter.active('/app/sidebar')]]">sidebar();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/card')]]" class="nav-link-pq [[=PQRouter.active('/app/card')]]">card();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/drawer')]]" class="nav-link-pq [[=PQRouter.active('/app/drawer')]]">drawer();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/footer')]]" class="nav-link-pq [[=PQRouter.active('/app/footer')]]">footer();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/badge')]]" class="nav-link-pq [[=PQRouter.active('/app/badge')]]">badge();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/icon')]]" class="nav-link-pq [[=PQRouter.active('/app/icon')]]">icon();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/style')]]" class="nav-link-pq [[=PQRouter.active('/app/style')]]">style();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('app/desc')]]" class="nav-link-pq [[=PQRouter.active('/app/desc')]]">desc();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>		
	
    <div class="nav-sub-group [[=  @is['auto'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#auto-items" aria-expanded="[[=  @is['auto'] ? 'true' : 'false' ]]">	
        <span><i class="bi bi-broadcast-pin  me-2"></i>Auto function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[=  @is['auto'] ? 'show' : '' ]]" id="auto-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('auto/chain')]]" class="nav-link-pq [[=PQRouter.active('/auto/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('auto/now')]]" class="nav-link-pq [[=PQRouter.active('/auto/now')]]">now(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('auto/format')]]" class="nav-link-pq [[=PQRouter.active('/auto/format')]]">format(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('auto/at')]]" class="nav-link-pq [[=PQRouter.active('/auto/at')]]">at(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('auto/loop')]]" class="nav-link-pq [[=PQRouter.active('/auto/loop')]]">loop(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('auto/cron')]]" class="nav-link-pq [[=PQRouter.active('/auto/cron')]]">cron(); <span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('auto/action')]]" class="nav-link-pq [[=PQRouter.active('/auto/action')]]">action(); <span class="pq-dot"></span></a></li>
        </ul>
    </div>			
    <div class="nav-sub-group [[= @is['chat'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#chat-items" aria-expanded="[[= @is['chat'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-chat-dots me-2"></i>Chat function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['chat'] ? 'show' : '' ]]" id="chat-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('chat/chain')]]" class="nav-link-pq [[=PQRouter.active('/chat/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>					
            <li><a href="[[=PQRouter.url('chat/room')]]" class="nav-link-pq [[=PQRouter.active('/chat/room')]]">room(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('chat/user')]]" class="nav-link-pq [[=PQRouter.active('/chat/user')]]">user();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('chat/msg')]]" class="nav-link-pq [[=PQRouter.active('/chat/msg')]]">msg();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('chat/send')]]" class="nav-link-pq [[=PQRouter.active('/chat/send')]]">send();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('chat/listen')]]" class="nav-link-pq [[=PQRouter.active('/chat/listen')]]">listen();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('chat/webhook')]]" class="nav-link-pq [[=PQRouter.active('/chat/webhook')]]">webhook();<span class="pq-dot"></span></a></li>
        </ul>
    </div>		
    <!-- Api function -->
    <div class="nav-sub-group [[= @is['api'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#api-items" aria-expanded="[[= @is['api'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-globe-central-south-asia me-2"></i>Api function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['api'] ? 'show' : '' ]]" id="api-items">	
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('api/chain')]]" class="nav-link-pq [[=PQRouter.active('/api/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('api/target')]]" class="nav-link-pq [[=PQRouter.active('/api/target')]]">target(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('api/method')]]" class="nav-link-pq [[=PQRouter.active('/api/method')]]">method();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('api/param')]]" class="nav-link-pq [[=PQRouter.active('/api/param')]]">param();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('api/header')]]" class="nav-link-pq [[=PQRouter.active('/api/header')]]">header();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('api/send')]]" class="nav-link-pq [[=PQRouter.active('/api/send')]]">send();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('api/json')]]" class="nav-link-pq [[=PQRouter.active('/api/json')]]">json();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>		
    <div class="nav-sub-group [[= @is['qrcode'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#qrcode-items" aria-expanded="[[= @is['qrcode'] ? 'true' : 'false' ]]">
        <span><i class="bi bi bi-qr-code me-2"></i>Qrcode function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['qrcode'] ? 'show' : '' ]]" id="qrcode-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('qrcode/chain')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>				
            <li><a href="[[=PQRouter.url('qrcode/text')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/text')]]">text(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('qrcode/size')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/size')]]">size();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('qrcode/color')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/color')]]">color();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('qrcode/render')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/render')]]">render();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('qrcode/save')]]" class="nav-link-pq [[=PQRouter.active('/qrcode/save')]]">save();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>
    <div class="nav-sub-group [[= @is['barcode'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#qrcode-items" aria-expanded="[[= @is['barcode'] ? 'true' : 'false' ]]">
        <span><i class="bi bi bi-upc me-2"></i>Barcode function</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['barcode'] ? 'show' : '' ]]" id="qrcode-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('barcode/chain')]]" class="nav-link-pq [[=PQRouter.active('/barcode/chain')]]">CHAIN PATTERN<span class="pq-dot"></span></a></li>		
            <li><a href="[[=PQRouter.url('barcode/text')]]" class="nav-link-pq [[=PQRouter.active('/barcode/text')]]">text(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('barcode/size')]]" class="nav-link-pq [[=PQRouter.active('/barcode/size')]]">size();<span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('barcode/color')]]" class="nav-link-pq [[=PQRouter.active('/barcode/color')]]">color(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('barcode/render')]]" class="nav-link-pq [[=PQRouter.active('/barcode/render')]]">render();<span class="pq-dot"></span></a></li>			
            <li><a href="[[=PQRouter.url('barcode/save')]]" class="nav-link-pq [[=PQRouter.active('/barcode/save')]]">save();<span class="pq-dot"></span></a></li>			
        </ul>
    </div>	
  [[ endif; ]]	
    <!--
	<div class="nav-sub-group [[= @is['iot'] ? '' : 'collapsed' ]]" data-bs-toggle="collapse" data-bs-target="#iot-items" aria-expanded="[[= @is['iot'] ? 'true' : 'false' ]]">
        <span><i class="bi bi-plugin me-2"></i>Iot function 차후제작</span>
        <i class="bi bi-chevron-down chevron-icon"></i>
    </div>
    <div class="collapse [[= @is['iot'] ? 'show' : '' ]]" id="iot-items">
        <ul class="sub-menu">
            <li><a href="[[=PQRouter.url('iot/now')]]" class="nav-link-pq [[=PQRouter.active('/iot/now')]]">now(); <span class="pq-dot"></span></a></li>
            <li><a href="[[=PQRouter.url('iot/format')]]" class="nav-link-pq [[=PQRouter.active('/iot/format')]]">format();</a></li>
        </ul>
    </div>			
-->
	<div class="mb-5">
	</div>
</div>
