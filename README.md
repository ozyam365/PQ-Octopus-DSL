
# PQ Octopus DSL

**PQ Octopus DSL** is a lightweight PHP-based DSL designed to make web development simpler, more concise, and more practical.

PQ combines familiar PHP concepts with concise syntax, chaining, utility functions, and web-oriented features to reduce repetitive code and make application development easier to read and maintain.

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

| # | Category | PHP | PQ |
|---:|---|---|---|
| 1 | Opening / Output | `<?php echo "php"; ?>` | `[[ print "pq"; ]]` |
| 2 | Variable | `$` | `@` |
| 3 | Object Initialization | `new class();` | `#object = obj();` |
| 4 | Object Access | `$user->name` | `#object.name` |
| 5 | Object Definition | `class Name {}` | `#object = []` |
| 6 | Object Inheritance | `class Name extends sub {}` | `#parent.child = []` |
| 7 | Object Reference | `$variable = $variable;` | `#object = #object;` |
| 8 | Array (Collection) | `array()` or `[]` | `$` |
| 9 | Chaining | `->` | `.` |
| 10 | File Inclusion | `include "";` | `inc "";` |
| 11 | Short Output | `<?= $aaa; ?>` | `[[=@aaa]]` |
| 12 | Function | `function name() {}` | `fn name() {}` |
| 13 | Comment | `# comment` | `## comment` |
| 14 | Control Flow | `if($a > 1) {} else {}` | `if(@a > 1): else: endif;` |

> **PQ Reference**
>
> `@` = Variable  
> `#` = Object  
> `$` = Array / Collection
