[[
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /init.pq
 * COMPONENT : Global Variables & System Metadata Initialization
 *  init.pq - A place to store global variables
 *  init.pq is where global variables are stored; they are designated by prefixing them with an underscore (_) (e.g., @_nowdate).
 * =========================================================
 */

global @_year, @_mon, @_day, @_month, @_time, @_week, @_ampm, @_time1, @_time2, @_time3, @_datetime, @_date,  @_nowdate;
global $_arrUse, $_arrState;

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
@_nowdate     = time();

/* --- [2. SYSTEM SYSTEM CONSTANTS & METADATA] --- */
$_arrUse   = array('사용여부', '사용', '미사용');
$_arrState = array('승인여부', '승인대기', '승인', '일시중지', '탈퇴');

/* Custom user extensions space */

]]