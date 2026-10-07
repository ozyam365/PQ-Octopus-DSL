[[
/**
 * =========================================================
 * FILENAME  : /tbl.pq
 * COMPONENT : Database Table Prefix & Global Table Name Mapping
*  tbl.pq – Stores table name variables.
*  tbl.pq stores table-related variables; these are prefixed with an underscore (_) to indicate they are global variables (e.g., @_adm...).
*  Table name variables are distinguished by appending _t to the end (e.g., @_adm_t).
 * =========================================================
 */

global @_dbtag;
global @_mbr_t, ,@_popup_t;

/* --- [1. DB TABLE PREFIX & TAG CONFIGURATION] --- */
@_dbtag = "pq";

/* --- [2. CORE SYSTEM TABLES MAPPING] --- */
@_mbr_t       = @_dbtag . "_member";
@_popup_t    = @_dbtag . "_popup";

/* --- [3. BULLETIN BOARD SYSTEM (BBS) TABLES MAPPING] --- */
@_bbs_t      = @_dbtag . "_bbs_data";

]]