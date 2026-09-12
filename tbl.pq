[[
/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.7)
 * FILENAME  : /tbl.pq
 * COMPONENT : Database Table Prefix & Global Table Name Mapping
*  tbl.pq – Stores table name variables.
*  tbl.pq stores table-related variables; these are prefixed with an underscore (_) to indicate they are global variables (e.g., @_adm...).
*  Table name variables are distinguished by appending _t to the end (e.g., @_adm_t).
 * =========================================================
 */

global @_dbtag;
global @_bbs_adm_t, @_bbs_ct_t, @_bbs_t, @_bbs_memo_t;
global @_adm_t, @_mbr_t, @_mbr_level_t, @_manual_t;

/* --- [1. DB TABLE PREFIX & TAG CONFIGURATION] --- */
@_dbtag = "pq";

/* --- [2. CORE SYSTEM TABLES MAPPING] --- */
@_mbr_t       = @_dbtag . "_member";
@_mbr_level_t = @_dbtag . "_member_level";

/* --- [3. BULLETIN BOARD SYSTEM (BBS) TABLES MAPPING] --- */
@_bbs_t      = @_dbtag . "_bbs_data";
@_bbs_memo_t = @_dbtag . "_bbs_memo";

]]