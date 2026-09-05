[[
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.6)
 * FILENAME : /tbl.pq
*  tbl.pq  -  테이블명 변수 저장하는곳
*  tbl.pq는  테이블 관련 변수를 저장하는곳이고  맨앞에는  전역변수임을  나타내는  _ 를 붙여서 표현합니다.(예시 @_adm...)
*  테이블명 변수 끝에는 _t 를 붙여 줘서 구분합니다. (예시 @_adm_t )
 * =========================================================
 */
global @_dbtag;
global @_bbs_adm_t , @_bbs_ct_t, @_bbs_t,@_bbs_memo_t ;

@_dbtag = "pq";
@_bbs_adm_t = @_dbtag."_bbs_config";

@_bbs_ct_t  = @_dbtag."_bbs_category";
@_bbs_t      = @_dbtag."_bbs_data";
@_bbs_memo_t = @_dbtag."_bbs_memo";
 ]]