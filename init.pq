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
global $_arrUse, $_arrState, $_core_info,$_core_info_en, $_plugin_info,$_plugin_info_en,$_menu_ko,$_menu_en;
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
$_arrUse = array('Usage Status', 'Active', 'Inactive');
$_arrState = array('Verification Status', 'Pending Verification', 'Verified', 'Suspended', 'Revoked');

@_pq_version = PQ_VERSION;
@_pq_down    = PQ_DN;

/* Custom user extensions space */
@_editor_attach_max_file = "5";
@_editor_attach_max="5242880";
@_editor_attach_size ="5M";
@_editor_attach_ext ="gif,png,jpg,xls,pdf,hwp,doc,ppt,pptx,xlsx,mp3,mp4,swf,flv,avi,wmv,mov,css,js,html,htm";
]]