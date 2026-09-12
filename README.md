# PQ Octopus DSL

**PQ Octopus DSL** is a lightweight PHP-based DSL designed to make web development simpler, more concise, and more practical.

PQ combines familiar PHP concepts with concise syntax, chaining, utility functions, and web-oriented features to reduce repetitive code and make application development easier to read and maintain.

<img width="474" height="711" alt="ChatGPT Image 2026년 7월 22일 오후 10_52_51" src="https://github.com/user-attachments/assets/0a2db57a-f3fd-426e-9189-85c395f87e42" />

> **Status:** Beta / Active Development

📦 **[Download Full Installation Package (Google Drive)]([[PQ-Octopus-DSL download](https://drive.google.com/drive/folders/16LwbBFdB-gRCtyI3FEfhx2UsnWsQ6hZO)]**

http://pqoctopus.com (Under construction)

## Why PQ?
# Why Hasn't PHP Had a DSL Like This?

PHP is powerful.

It can handle almost everything needed for web development, and its ecosystem has been proven over decades.

But when you build real applications with PHP, you eventually notice something interesting:

**PHP has plenty of power, yet we keep writing the same patterns over and over again.**

Take a simple example: getting a page parameter, trimming it, applying a default value, validating it, and converting it to an integer.

In PHP, you might write:

```php
$page = isset($_GET['page']) ? trim($_GET['page']) : 1;

if (!is_numeric($page)) {
    $page = 1;
}

$page = (int)$page;
```

There is nothing wrong with this code.

It is perfectly normal PHP.

But what are we actually trying to say?

> Get `page` → trim it → use `1` if there is no value → convert it to an integer.

Wouldn't it be nice if the code could read that way too?

With PQ:

```pq
@page = form.get("page").trim().val(1).int();
```

The important part is not simply that the code is shorter.

**The data flow is visible directly in the code.**

---

## Inspired by jQuery

If you've worked with frontend development, you may remember code like this:

```javascript
$(el).find(".item").addClass("active").show();
```

Find the DOM element.

Select what you need.

Add a class.

Show it.

Each operation is independent, yet they form a single readable flow.

That is what made jQuery chaining so pleasant to use.

**The execution flow and the reading flow were almost the same.**

PQ started with a simple question:

> What if we brought that idea to server-side development?

Not just DOM manipulation, but variables, form data, database queries, iteration, validation, and error handling.

That is what PQ is trying to do.

---

## PQ Does Not Replace PHP

PQ is not an attempt to replace PHP.

PHP already does its job extremely well. There is no reason to rebuild everything from scratch.

Instead, PQ is designed to sit on top of PHP and provide a more concise way to express repetitive server-side logic.

`.pq` files are compiled into PHP and executed in the existing PHP environment.

The basic idea is:

```text
PQ
 ↓
Compile to PHP
 ↓
Run in the existing PHP environment
```

You can also mix native PHP with PQ whenever you need to.

So PQ is not about abandoning PHP.

**It is a DSL built around PHP.**

---

## Server-Side Chaining

The core idea of PQ is to connect related operations into a single flow.

For example, form data:

```pq
form.get("keyword").trim().val("").string();
```

Database queries:

```pq
db.pq_bbs_data.where("idx = '1'").row();
```

Converting query results:

```pq
#row.array();
#row.json();
#row.object();
```

Initializing multiple variables:

```pq
pin(@a, @b, @c).val(0);
```

None of these operations are revolutionary by themselves.

The important part is the **consistency of the syntax and the flow**.

Get the data.

Process it.

Transform it.

Use the result.

The code follows the same direction as the operation itself.

---

## A Consistent Approach to Repetition

Server-side applications also contain a lot of repetitive control flow.

PQ provides a unified syntax for iteration:

```pq
repeat()
    ...
endrepeat;
```

The same structure can represent different iteration patterns such as `foreach`, key/value iteration, `for`, and `while`.

The goal is not to remove the capabilities of PHP.

The goal is to give common concepts a consistent way to be expressed.

---

## Error Handling

The same philosophy is applied to error handling.

```pq
rule
    ...
.fail()
    ...
endrule;
```

The normal flow and the failure flow are expressed together.

Likewise, common value checks can be expressed through dedicated constructs such as:

```pq
has(...)
blank(...)
```

Rather than repeatedly combining PHP's `isset()`, `empty()`, ternary operators, and other checks.

---

## Make Data Types Visible

PQ also uses three symbols to make the role of data immediately recognizable:

```text
@  variable
#  object
$  array
```

This is a small design decision, but it helps when reading code.

You don't always have to stop and inspect a variable name to understand what kind of value you are dealing with.

The syntax gives you a hint.

---

# So, What Is PQ Actually Trying to Do?

PQ is not trying to create a language that is more powerful than PHP.

It is not trying to replace PHP.

It started from a much simpler observation:

**PHP is already powerful enough.**

What developers often need is not more power, but less repetition.

jQuery showed how chaining could make frontend operations easier to read.

PQ takes that idea and applies it to server-side logic.

```text
Get → Process → Transform → Use
```

The goal is to make that flow visible in the code.

Shorter syntax.

Less repetition.

Clearer data flow.

And code that reads closer to the way we actually think about the operation.

**That is what PQ is trying to do.**

PHP is still there.

The PHP ecosystem is still there.

PQ simply adds another option on top of it:

**a concise, chain-oriented DSL for PHP server-side development.**


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


### Simple PQ Syntax Example

A simple CRUD example demonstrating form handling, variables, arrays, database operations, and action-based branching in PQ.

```pq
[[
    // Simple PQ Syntax

    @act = form.get('act');
    @idx = form.get('idx').val(0).int();

    switch(@act) :

        case "i": // Insert
            @name  = form.get('name').trim();
            @email = form.get('email').trim();

            $record = [];
            $record['name']  = @name;
            $record['email'] = @email;

            db.@_member_t.insert($record);
            http.go("/member_list");
            break;

        case "u": // Update
            @name  = form.get('name').trim();
            @email = form.get('email').trim();

            $record = [];
            $record['name']  = @name;
            $record['email'] = @email;

            db.@_member_t.where("idx = '@idx'").update($record);
            http.go("/member_list");
            break;

        case "d": // Delete
            db.@_member_t.where("idx = '@idx'").delete();
            http.go("/member_list");
            break;

    endswitch;
]]
```
