[[
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /init.pq
*  init.pq  -  전역 변수 저장하는곳
*  init.pq는  전역 변수를 저장하는곳이고  맨앞에는  전역변수임을  나타내는  _ 를 붙여서 표현합니다.(예시 @_nowdate)
* =========================================================
*/

global  @_year,@_mon,@day,@_month ,@_time ,@_week,@_ampm,@_time1,@_time2,@_time3,@_datetime,@_date,@_nowdate;
global @_pq_version,@_pq_down;
global $_arrUse;

/* 일자,시간 정보 */
@_year                       = date("Y",time());
@_mon                      = date("m",time());
@_day                        = date("d",time());
@_month                  = date("n",time());
@_time                      = date("H",time());
@_week                     = date("w",time());
@_ampm                   = date("a",time());
@_time1                    = date("h",time());
@_time2                    = date("i",time());
@_time3                    = date("s",time());
@_datetime              = date("Y-m-d H:i:s",time());
@_date                       = @_year.'-'.@_mon.'-'.@_day;
@_nowdate               =  time();

date_default_timezone_set("Asia/Seoul");

/* 기본 */
$_arrUse                     = array('사용여부','사용','미사용');
$_arrState                  = array('승인여부','승인대기','승인','일시중지','탈퇴');
@_pq_version = PQ_VERSION;

/*추가 */

 ]]