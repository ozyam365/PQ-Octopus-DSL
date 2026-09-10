# PQ Octopus DSL

**PQ Octopus DSL** is a lightweight PHP-based DSL designed to make web development simpler, more concise, and more practical.

PQ combines familiar PHP concepts with concise syntax, chaining, utility functions, and web-oriented features to reduce repetitive code and make application development easier to read and maintain.

<img width="474" height="711" alt="ChatGPT Image 2026년 7월 22일 오후 10_52_51" src="https://github.com/user-attachments/assets/0a2db57a-f3fd-426e-9189-85c395f87e42" />

> **Status:** Beta / Active Development

📦 **[Download Full Installation Package (Google Drive)]([[PQ-Octopus-DSL download](https://drive.google.com/drive/folders/16LwbBFdB-gRCtyI3FEfhx2UsnWsQ6hZO)]**

http://pqoctopus.com (Under construction)

## Why PQ?
Why doesn't PHP have a DSL like this?

PHP is powerful, but its syntax tends to be verbose. jQuery solved a similar problem for the DOM, turning manipulation into elegant chains. PQ extends that same chaining philosophy across the entire PHP server-side layer — variables, databases, loops, and form handling.

PHP is already powerful. But we keep writing the same boilerplate.

<pre><code>
*php*
$page = isset($_GET['page']) ? trim($_GET['page']) : 1;
if (!is_numeric($page)) { $page = 1; }
$page = (int)$page;
</code></pre>
Four lines for logic that feels like it should take one. isset(), a ternary, a type check, a cast — you write it the same way every time, and every code review reminds you the pattern never changes.

PQ collapses that repetition into a single chain.

<pre><code>
*pq*
@page = form.get("page").trim().val(1).int();
</code></pre>

Borrowed from jQuery

> $(el).find(".item").addClass("active").show();

Think about why that line felt so good to write. Finding, manipulating, and producing a result read as one continuous flow — no intermediate variables, no nested conditionals.

PQ brings that same feeling to the entire backend.

Area	PQ chaining example
Form handling	form.get("keyword").trim().val("").string();
Database queries	db.pq_bbs_data.where("idx = '1'").row();
Return-value casting	#row.array() / .json() / .object()
Bulk variable init	pin(@a, @b, @c).val(0);
Less syntax, not less power

PQ doesn't replace PHP. Every .pq file compiles down to PHP and runs as PHP, and you can freely mix in plain PHP syntax whenever you need to.

Four loop styles (foreach, foreach-key, for, while) unified into one repeat() ~ endrepeat;
Exception handling via rule ~ .fail() ~ endrule; instead of try/catch
Dedicated blocks has() and blank() in place of isset() and empty()
Three symbols — @ (variable), # (object), $ (array) — so you can tell what kind of data you're looking at, at a glance
The point

PQ isn't asking you to learn a new language. It's PHP you already know, with the chaining feel of jQuery you already like, layered on top.

Cut the verbosity. Compress the repetition. Let the code read the way it flows. That's all PQ is trying to do.

## Main Features

- Concise PHP-based DSL syntax
- Web-oriented development features
- Function and method chaining
- Practical utility functions
- Simplified form and data handling
- Database-oriented helpers
- Plugin-based extension system
- Core parser and runtime engine
- Designed for both traditional development and AI-assisted coding

## Project Structure

The project is organized around a core engine and an extension system.

```text
## Project Structure

```text
PQ-Octopus-DSL/
├── assets/     # External libraries and web assets
├── attach/     # Uploaded files and attachments
├── html/       # HTML layouts and web resources
├── pq/         # PQ core system
│   ├── core/   # PQ core functions
│   ├── engine/ # PQ parser and runtime engine
│   ├── plugin/ # PQ plugins
│   └── tmp/    # Temporary files
├── set/        # Environment and configuration
├── run.php     # PQ entry point
├── init.pq     # PQ initialization
└── tbl.pq      # Table-related definitions


## Syntax Comparison

| #  | Category              |              PHP             |               PQ           |
|----|-----------------------|------------------------------|----------------------------|
| 01 | Opening / Output      |   <?php echo "php"; ?>       |      [[ print "pq"; ]]     |
| 02 | Variable              |            $                 |              @             |
| 03 | Object Initialization |       new class();           |      #object = obj();      |
| 04 | Object Access         |       $user->name            |      #object.name          |
| 05 | Object Definition     |       class Name {}          |       #object = []         |
| 06 | Object Inheritance    |  class Name extends sub {}   |    #parent.child = []      |
| 07 | Object Reference      |   $variable = $variable;     |    #object = #object;      |
| 08 | Array (Collection)    |        array() or []         |            $               |
| 09 | Chaining              |              ->              |            .               |
| 10 | File Inclusion        |           include "";        |          inc "";           |
| 11 | Short Output          |         <?= $aaa; ?>         |         [[=@aaa]]          |
| 12 | Function              |    function name() {}        |       fn name() {}         |
| 13 | Comment               |        # comment             |       ## comment           |
| 14 | Control Flow          |    if($a > 1) {} else {}     |   if(@a > 1): else: endif; |

> **PQ Reference**
>
> `@` = Variable  
> `#` = Object  
> `$` = Array / Collection


## Sample Code

The following example shows a real-world PQ application flow for a bulletin board
write/edit page.


```pq
[[
	@code = form.get("code").string();
	@idx  = form.get("idx").int();
	@page = form.get("page").trim().val(1).int();
	@act  = form.get("act").val("i").string();

	if(empty(@code)) :
		http.msg("Invalid command.").back();
		exit;
	endif;

	// Load board configuration
	#cfg = db.@_bbs_adm_t.where("code = '@code'").row();

	if(!#cfg) :
		http.msg("Failed to load board configuration.").back();
		exit;
	endif;

	// Initialize variables
	pin(@subject, @note, @author_name, @author_email, @author_pwd).val("");
	pin(@u_notice, @u_secret, @u_show, @gidx, @gseq, @gstep).val(0);

	// Authentication
	if(auth.check()) :
		#user = session.get("user");

		if(auth.admin()) :
			// Administrator
			@btn_update_show = true;
		else :
			// Member permission check
			if(!rgx(#cfg.gwrite_mbr).csv(#user.mbr_level).match()) :
				http.msg("You do not have permission.").back();
				exit;
			endif;
		endif;
	else :
		// Guest handling
		if(#cfg.gwrite_guest == 1) :
			@btn_insert_show = true;
		else :
			http.msg("You do not have permission.").back();
			exit;
		endif;
	endif;

	// Select skin
	@skin_dir = "base";

	switch((int)#cfg.bbs_type) :
		case 1: @skin_dir = "base"; break;
		case 2: @skin_dir = "faq"; break;
		case 3: @skin_dir = "gallery"; break;
		case 4: @skin_dir = "qna"; break;
	endswitch;

	inc "/path/html/bbs/skin/@skin_dir/bbs.pq";
]]

What this example demonstrates
@ variables
# objects
$ collections
Method chaining
Database queries
Form handling
Authentication and sessions
Conditional statements
switch / case
pin() variable initialization
Regular-expression utilities
Dynamic file inclusion
PHP interoperability

/**
 * =========================================================
 * PQ VERSION (BETA VERSION 9.1.2)
 * FILENAME  : /html/bbs/bbs_ext.pq
 * COMPONENT : Positive Reverse gidx Acceleration Algorithm
 * =========================================================
 */
[[
    // 1. Sanitize & Validate Required Parameters
    @code = form.get('code').special().trim().error("Board code is required.");
    @act  = form.get('act').error("Invalid access method.");
    @now_year = form.get('now_year').special();
    
    if (empty(@now_year)) : @now_year = @_year; endif;
    if (empty(@code) || empty(@act)) : 
        http.msg("Required code is missing.").back(); exit; 
    endif;

    // 2. Fetch Board Configuration from Dynamic Table
    @bbsCode_t = @_bbs_t . "_" . @code;
    #cfg = db.@_bbs_adm_t.where("code = '@code'").row();
    if (!#cfg) : 
        http.msg("Board configuration not found.").back(); exit; 
    endif;

    // 3. File Upload Path & Security Policy Setup
    @upd_bbs_dir  = "bbs/" . @code . "/" . @now_year;
    @upd_full_dir = ATTACH_DIR . @upd_bbs_dir;
    @attach_max   = val(#cfg.attach_max, 0);
    @allow_ext    = val(#cfg.attach_ext, "jpg,jpeg,png,gif,webp,zip,pdf,txt");

    if (file.has(@upd_full_dir)) : 
        file.mkdir(@upd_full_dir, 0777); 
    endif;

    // 4. Handle Master File Uploads & Thumbnail Generation
    if (@attach_max > 0 && (@act == "i" || @act == "u")) :
        if (isset($_FILES['attach_files_master'])) :
            @total_files = count($_FILES['attach_files_master']['name']);
            repeat(@i < @total_files).set(@i=0).step(1):
                if ($_FILES['attach_files_master']['error'][@i] === UPLOAD_ERR_NO_FILE) continue;

                $_FILES['now_upload'] = [
                    'name'     => $_FILES['attach_files_master']['name'][@i],
                    'type'     => $_FILES['attach_files_master']['type'][@i],
                    'tmp_name' => $_FILES['attach_files_master']['tmp_name'][@i],
                    'error'    => $_FILES['attach_files_master']['error'][@i],
                    'size'     => $_FILES['attach_files_master']['size'][@i]
                ];

                @saved_name = file.upload('now_upload')->path(@upd_full_dir)->random()->allow(@allow_ext)->image()->save();
                @ori_name   = $_FILES['attach_files_master']['name'][@i];

                if (@saved_name) :
                    $ori_box[]  = @upd_bbs_dir . "/" . @ori_name;
                    $file_info  = ["ori" => @ori_name, "path" => @upd_bbs_dir . "/" . @saved_name];
                    $file_box[] = @file_info;

                    file.thumbnail(@upd_full_dir . "/" . @saved_name, @upd_full_dir . "/t_" . @saved_name, 200, 200, true);
                else:
                    http.msg("File upload failed security validation or storage error occurred.").back(); exit;
                endif;
            endrepeat;
            unset($_FILES['now_upload']);
        endif;
    endif;

    // 5. Anti-Bot CSRF One-Time Token Verification
    if (@act != "d") :
        @sess_token = session.get('robot_token');
        @form_token = form.get('robot_token').trim();

        // Immediately revoke token to prevent replay attacks
        session.unset('robot_token');

        if (empty(@sess_token) || @sess_token !== @form_token) :
            http.msg("Automated robot attempt detected or form request has expired.").back();
            exit;
        endif;
    endif;

    // 6. Action-Based Processing Pipeline (CRUD Router)
    switch (@act) {
        case "i": // [CREATE] Insert New Article
            @form_note    = form.get('note');
            @author_email = form.get('author_email').trim();
            @author_name  = form.get('author_name').trim();
            @author_pwd   = form.get('author_pwd').trim();
            @form_subject = form.get('subject').trim();

            $record = [];
            $record['code']    = @code;
            $record['subject'] = html(@form_subject).trim().special("on").run();

            // XSS Filtering & Editor Policy Handler
            if (#cfg.u_editor == 1) :
                @note = html(@form_note).youtube("on").xss("on").run();
            else:
                @note = html(@form_note).xss("on").run();
            endif;

            $record['note']         = @note;
            $record['author_email'] = @author_email;

            // Process AJAX Attachments JSON Payload
            $ajax_files_raw = form.get('ajax_attached_files').value();
            $ajax_files_arr = !empty($ajax_files_raw) ? json_decode($ajax_files_raw, true) : [];

            if (!empty($ajax_files_arr) && is_array($ajax_files_arr)) :
                $ori_box = [];
                repeat($ajax_files_arr).as($af) :
                    $ori_box[] = "bbs/" . @code . "/" . @now_year . "/" . $af['ori'];
                endrepeat;
                $record['attach_files'] = json_encode($ajax_files_arr, JSON_UNESCAPED_UNICODE);
                $record['origin_files'] = json_encode($ori_box, JSON_UNESCAPED_UNICODE);
            else:
                $record['attach_files'] = null;
                $record['origin_files'] = null;
            endif;

            @form_notice = form.get("u_notice").val(2).int();
            @form_show   = form.get("u_show").val(2).int();
            @form_secret = form.get("u_secret").val(2).int();

            // Permission Check for Admin Controls
            if (adm_auth()):
                $record["u_notice"] = (@form_notice == 1) ? 1 : 2;
                $record["u_show"]   = (@form_show == 1) ? 1 : 2;
                $record["u_secret"] = (@form_secret == 1) ? 1 : 2;
            else:
                $record["u_notice"] = 2;
                $record["u_show"]   = 2;
                $record["u_secret"] = (@form_secret == 1) ? 1 : 2;
            endif;

            $record['reg_date'] = date('Y-m-d H:i:s');
            $record['ip']       = http.ip();

            // Member / Guest Author Session Normalization
            if (auth.check()) :
                #user = session.get('user');
                $record['author_id'] = #user.mbr_id;
                if (empty(@author_name)) $record['author_name'] = #user.mbr_name;
                if (empty(@author_pwd))  $record['author_pwd']  = '';
            else:
                $record['author_name'] = form.get('author_name').trim();
                $record['author_pwd']  = form.get('author_pwd').trim();
            endif;

            // Positive Reverse Index Acceleration Calculation
            @gq_cnt = db.count("SELECT COUNT(*) FROM @bbsCode_t");
            if (@gq_cnt > 0) :
                @gidx_min = db.@bbsCode_t.min("gidx");
                @gidx_val = @gidx_min - 1;
            else:
                @gidx_val = -1;
            endif;

            $record['gidx']  = @gidx_val;
            $record['gseq']  = 0;
            $record['gstep'] = 0;

            db.@bbsCode_t.insert($record);
            http.msg("Article successfully published.").go("/bbs/bbs_list?code=@code");
            break;

        case "u": // [UPDATE] Modify Article
            @idx = form.get("idx").val(0).int();
            if (empty(@idx)) : http.msg("Article not found.").back(); exit; endif;

            @form_note    = form.get('note').value();
            @form_notice  = form.get("u_notice").val(2).int();
            @form_show    = form.get("u_show").val(2).int();
            @form_secret  = form.get("u_secret").val(2).int();
            @form_subject = form.get('subject').special("on").trim().value();

            $record = [];
            $record['subject'] = html(@form_subject).xss("on").run();

            if (#cfg.u_editor == 1) :
                @note = html(@form_note).youtube("on").xss("on").run();
            else:
                @note = html(@form_note).xss("on").run();
            endif;

            $record['note'] = @note;
            if (adm_auth()):
                $record["u_notice"] = (@form_notice == 1) ? 1 : 2;
                $record["u_show"]   = (@form_show == 1) ? 1 : 2;
            endif;
            $record["u_secret"] = (@form_secret == 1) ? 1 : 2;

            $ajax_files_raw = form.get('ajax_attached_files').value();
            $ajax_files_arr = !empty($ajax_files_raw) ? json_decode($ajax_files_raw, true) : [];

            // Garbage Collection for Removed Attachments (Physical File Cleanup)
            $old_row   = db.query("SELECT attach_files FROM `" . @bbsCode_t . "` WHERE idx = '@idx'").array();
            $old_files = ($old_row && $old_row['attach_files']) ? json_decode($old_row['attach_files'], true) : [];

            if (is_array($old_files)) :
                repeat($old_files).as($old_f) :
                    @is_alive = false;
                    repeat($ajax_files_arr).as($new_f) :
                        if ($old_f['path'] == $new_f['path']) : @is_alive = true; break; endif;
                    endrepeat;

                    // Unlinked File Detection & Deletion
                    if (!@is_alive) :
                        @target_file_path = ATTACH_DIR . $old_f['path'];
                        if (file.has(@target_file_path)) file.delete(@target_file_path);

                        @dir   = dirname(@target_file_path);
                        @name  = basename(@target_file_path);
                        @thumb = @dir . "/t_" . @name;
                        if (file.has(@thumb)) file.delete(@thumb);
                    endif;
                endrepeat;
            endif;

            if (!empty($ajax_files_arr) && is_array($ajax_files_arr)) :
                $ori_box = [];
                repeat($ajax_files_arr).as($af) :
                    $ori_box[] = "bbs/" . @code . "/" . @now_year . "/" . $af['ori'];
                endrepeat;
                $record['attach_files'] = json_encode($ajax_files_arr, JSON_UNESCAPED_UNICODE);
                $record['origin_files'] = json_encode($ori_box, JSON_UNESCAPED_UNICODE);
            else:
                $record['attach_files'] = null;
                $record['origin_files'] = null;
            endif;

            db.@bbsCode_t.where("idx = '@idx'").update($record);
            http.msg("Article successfully updated.").go("/bbs/bbs_view?code=@code&idx=@idx");
            break;

        case "d": // [DELETE] Remove Article & Related Data
            @idx = form.get("idx").val(0).int();
            if (empty(@idx)) : http.msg("Article not found.").back(); exit; endif;

            @delete_auth = false;
            #user = session.get("user");
            @bbsCode_t     = "pq_bbs_data_" . @code;
            @bbsMemoCode_t = @_bbs_memo_t . "_" . @code;

            $row = db.@bbsCode_t.where("idx = '@idx'").row().array();

            // Authorization Resolution Flow
            if (auth.check()) :
                if (auth.admin()) :
                    @delete_auth = true;
                else:
                    if (#user.mbr_id == $row['author_id']) :
                        @delete_auth = true;
                    endif;
                endif;
            else :
                // Anonymous Password Auth Session Check
                #bbs_auth = session.get('bbs_auth_' . @idx);
                if (!empty(#bbs_auth) && isset(#bbs_auth.idx)) :
                    if ($row['idx'] == #bbs_auth.idx && $row['code'] == #bbs_auth.code && #bbs_auth.auth == true) :
                        @delete_auth = true;
                    else:
                        @delete_auth = false;
                    endif;
                else :
                    // Redirect to Anonymous Auth Prompt Page
                    @http_url = http.request_uri();
                    @ref_url  = val(@ref_url, urlencode(@http_url));
                    http.go("pass?code=@code&act=v&idx=@idx&page=@page&ref_url=@ref_url");
                    exit;
                endif;
            endif;

            // Execute Cascade Deletion (Physical Files & Comments)
            if (@delete_auth == true):
                $drs = db.@bbsCode_t.select("attach_files").where("idx = '@idx'").row().array();
                if ($drs && !empty($drs['attach_files'])) :
                    $dfile = json_decode($drs['attach_files'], true);
                    if (is_array($dfile)) :
                        repeat($dfile).as($item) :
                            @target_file_path = ATTACH_DIR . $item['path'];
                            if (file.has(@target_file_path)) :
                                file.delete(@target_file_path);
                            endif;

                            @dir   = dirname(@target_file_path);
                            @name  = basename(@target_file_path);
                            @thumb = @dir . "/t_" . @name;
                            if (file.has(@thumb)) :
                                file.delete(@thumb);
                            endif;
                        endrepeat;
                    endif;
                endif;

                db.@bbsCode_t.where("idx = '@idx'").delete();

                // Cleanup Associated Comment Records
                @cnt = db.@bbsMemoCode_t.where("parent_idx = '@idx'").count();
                if (@cnt > 0) :
                    db.@bbsMemoCode_t.where("parent_idx = '@idx'").delete();
                endif;

                http.msg("Article successfully deleted.").go("/bbs/bbs_list?code=@code");
            else:
                http.msg("Failed to delete article. Unauthorized access.").go("/bbs/bbs_list?code=@code");
            endif;
            break;
    }
    exit;
]]
