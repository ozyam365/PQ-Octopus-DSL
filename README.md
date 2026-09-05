# PQ Octopus DSL

**PQ Octopus DSL** is a lightweight PHP-based DSL designed to make web development simpler, more concise, and more practical.

PQ combines familiar PHP concepts with concise syntax, chaining, utility functions, and web-oriented features to reduce repetitive code and make application development easier to read and maintain.

<img width="1024" height="1536" alt="ChatGPT Image 2026년 7월 22일 오후 10_52_51" src="https://github.com/user-attachments/assets/0a2db57a-f3fd-426e-9189-85c395f87e42" />

> **Status:** Beta / Active Development

📦 **[Download Full Installation Package (Google Drive)]([[PQ-Octopus-DSL download](https://drive.google.com/drive/folders/16LwbBFdB-gRCtyI3FEfhx2UsnWsQ6hZO)]**

## Why PQ?

PHP is powerful and flexible, but web development often requires a large amount of repetitive syntax.

PQ aims to provide a simpler development layer while keeping the strengths of PHP underneath.

The goal is not to replace PHP.

Instead, PQ is designed to work **with PHP** and make common web-development tasks more concise.

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
