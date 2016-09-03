<?php
namespace PHPPgAdmin;

/* This software is licensed through a BSD-style License.
 * http://www.opensource.org/licenses/bsd-license.php

Copyright (c) 2003, 2004, Jacob D. Cohen
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

Redistributions of source code must retain the above copyright notice,
this list of conditions and the following disclaimer.
Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
Neither the name of Jacob D. Cohen nor the names of his contributors
may be used to endorse or promote products derived from this software
without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */

class Highlight {

	const NORMAL_TEXT     = 1;
	const DQ_LITERAL      = 2;
	const DQ_ESCAPE       = 3;
	const SQ_LITERAL      = 4;
	const SQ_ESCAPE       = 5;
	const SLASH_BEGIN     = 6;
	const STAR_COMMENT    = 7;
	const STAR_END        = 8;
	const LINE_COMMENT    = 9;
	const HTML_ENTITY     = 10;
	const LC_ESCAPE       = 11;
	const BLOCK_COMMENT   = 12;
	const PAREN_BEGIN     = 13;
	const DASH_BEGIN      = 14;
	const BT_LITERAL      = 15;
	const BT_ESCAPE       = 16;
	const XML_TAG_BEGIN   = 17;
	const XML_TAG         = 18;
	const XML_PI          = 19;
	const SCH_NORMAL      = 20;
	const SCH_STRESC      = 21;
	const SCH_IDEXPR      = 22;
	const SCH_NUMLIT      = 23;
	const SCH_CHRLIT      = 24;
	const SCH_STRLIT      = 25;
	public $initial_state = ["Scheme" => self::SCH_NORMAL];
	public $sch           = [];
	public $c89           = [];
	public $c99           = [];
	public $cpp           = [];
	public $cs            = [];
	public $edges         = [];
	public $java          = [];
	public $mirc          = [];
	public $pascal        = [];
	public $perl          = [];
	public $php           = [];
	public $pli           = [];
	public $process       = [];
	public $process_end   = [];
	public $python        = [];
	public $ruby          = [];
	public $sql           = [];
	public $states        = [];
	public $vb            = [];
	public $xml           = [];

	/* Constructor */

	function __construct() {

		$keyword_replace = function ($keywords, $text, $ncs = false) {
			$cm = ($ncs) ? "i" : "";
			foreach ($keywords as $keyword) {
				$search[]  = "/(\\b$keyword\\b)/" . $cm;
				$replace[] = '<span class="keyword">\\0</span>';
			}

			$search[]  = "/(\\bclass\s)/";
			$replace[] = '<span class="keyword">\\0</span>';

			return preg_replace($search, $replace, $text);
		};

		$preproc_replace = function ($preproc, $text) {
			foreach ($preproc as $proc) {
				$search[]  = "/(\\s*#\s*$proc\\b)/";
				$replace[] = '<span class="keyword">\\0</span>';
			}

			return preg_replace($search, $replace, $text);
		};

		$sch_syntax_helper = function ($text) {
			return $text;
		};

		$syntax_highlight_helper = function ($text, $language) use ($keyword_replace, $preproc_replac) {
			$preproc        = [];
			$preproc["C++"] = [
				"if", "ifdef", "ifndef", "elif", "else",
				"endif", "include", "define", "undef", "line",
				"error", "pragma"];
			$preproc["C89"] = &$preproc["C++"];
			$preproc["C"]   = &$preproc["C89"];

			$keywords = [
				"C++" => [
					"asm", "auto", "bool", "break", "case",
					"catch", "char", /*class*/"const", "const_cast",
					"continue", "default", "delete", "do", "double",
					"dynamic_cast", "else", "enum", "explicit", "export",
					"extern", "false", "float", "for", "friend",
					"goto", "if", "inline", "int", "long",
					"mutable", "namespace", "new", "operator", "private",
					"protected", "public", "register", "reinterpret_cast", "return",
					"short", "signed", "sizeof", "static", "static_cast",
					"struct", "switch", "template", "this", "throw",
					"true", "try", "typedef", "typeid", "typename",
					"union", "unsigned", "using", "virtual", "void",
					"volatile", "wchar_t", "while"],

				"C89" => [
					"auto", "break", "case", "char", "const",
					"continue", "default", "do", "double", "else",
					"enum", "extern", "float", "for", "goto",
					"if", "int", "long", "register", "return",
					"short", "signed", "sizeof", "static", "struct",
					"switch", "typedef", "union", "unsigned", "void",
					"volatile", "while"],

				"C" => [
					"auto", "break", "case", "char", "const",
					"continue", "default", "do", "double", "else",
					"enum", "extern", "float", "for", "goto",
					"if", "int", "long", "register", "return",
					"short", "signed", "sizeof", "static", "struct",
					"switch", "typedef", "union", "unsigned", "void",
					"volatile", "while", "__restrict", "_Bool"],

				"PHP" => [
					"and", "or", "xor", "__FILE__", "__LINE__",
					"array", "as", "break", "case", "cfunction",
					/*class*/"const", "continue", "declare", "default",
					"die", "do", "echo", "else", "elseif",
					"empty", "enddeclare", "endfor", "endforeach", "endif",
					"endswitch", "endwhile", "eval", "exit", "extends",
					"for", "foreach", "function", "global", "if",
					"include", "include_once", "isset", "list", "new",
					"old_function", "print", "require", "require_once", "return",
					"static", "switch", "unset", "use", "var",
					"while", "__FUNCTION__", "__CLASS__"],

				"Perl" => [
					"-A", "-B", "-C", "-M", "-O",
					"-R", "-S", "-T", "-W", "-X",
					"-b", "-c", "-d", "-e", "-f",
					"-g", "-k", "-l", "-o", "-p",
					"-r", "-s", "-t", "-u", "-w",
					"-x", "-z", "ARGV", "DATA", "ENV",
					"SIG", "STDERR", "STDIN", "STDOUT", "atan2",
					"bind", "binmode", "bless", "caller", "chdir",
					"chmod", "chomp", "chop", "chown", "chr",
					"chroot", "close", "closedir", "cmp", "connect",
					"continue", "cos", "crypt", "dbmclose", "dbmopen",
					"defined", "delete", "die", "do", "dump",
					"each", "else", "elsif", "endgrent", "endhostent",
					"endnetent", "endprotent", "endpwent", "endservent", "eof",
					"eq", "eval", "exec", "exists", "exit",
					"exp", "fcntl", "fileno", "flock", "for",
					"foreach", "fork", "format", "formline", "ge",
					"getc", "getgrent", "getgrid", "getgrnam", "gethostbyaddr",
					"gethostbyname", "gethostent", "getlogin", "getnetbyaddr", "getnetbyname",
					"getnetent", "getpeername", "getpgrp", "getppid", "getpriority",
					"getprotobyname", "getprotobynumber", "getprotoent", "getpwent", "getpwnam",
					"getpwuid", "getservbyname", "getservbyport", "getservent", "getsockname",
					"getsockopt", "glob", "gmtime", "goto", "grep",
					/*gt*/"hex", "if", "import", "index",
					"int", "ioctl", "join", "keys", "kill",
					"last", "lc", "lcfirst", "le", "length",
					"link", "listen", "local", "localtime", "log",
					"lstat", /*lt*/"m", "map", "mkdir",
					"msgctl", "msgget", "msgrcv", "msgsnd", "my",
					"ne", "next", "no", "oct", "open",
					"opendir", "ord", "pack", "package", "pipe",
					"pop", "pos", "print", "printf", "push",
					"q", "qq", "quotemeta", "qw", "qx",
					"rand", "read", "readdir", "readlink", "recv",
					"redo", "ref", "refname", "require", "reset",
					"return", "reverse", "rewinddir", "rindex", "rmdir",
					"s", "scalar", "seek", "seekdir", "select",
					"semctl", "semget", "semop", "send", "setgrent",
					"sethostent", "setnetent", "setpgrp", "setpriority", "setprotoent",
					"setpwent", "setservent", "setsockopt", "shift", "shmctl",
					"shmget", "shmread", "shmwrite", "shutdown", "sin",
					"sleep", "socket", "socketpair", "sort", "splice",
					"split", "sprintf", "sqrt", "srand", "stat",
					"study", "sub", "substr", "symlink", "syscall",
					"sysopen", "sysread", "system", "syswrite", "tell",
					"telldir", "tie", "tied", "time", "times",
					"tr", "truncate", "uc", "ucfirst", "umask",
					"undef", "unless", "unlink", "unpack", "unshift",
					"untie", "until", "use", "utime", "values",
					"vec", "wait", "waitpid", "wantarray", "warn",
					"while", "write", "y", "or", "and",
					"not"],

				"Java" => [
					"abstract", "boolean", "break", "byte", "case",
					"catch", "char", /*class*/"const", "continue",
					"default", "do", "double", "else", "extends",
					"final", "finally", "float", "for", "goto",
					"if", "implements", "import", "instanceof", "int",
					"interface", "long", "native", "new", "package",
					"private", "protected", "public", "return", "short",
					"static", "strictfp", "super", "switch", "synchronized",
					"this", "throw", "throws", "transient", "try",
					"void", "volatile", "while"],

				"VB" => [
					"AddressOf", "Alias", "And", "Any", "As",
					"Binary", "Boolean", "ByRef", "Byte", "ByVal",
					"Call", "Case", "CBool", "CByte", "CCur",
					"CDate", "CDbl", "CInt", "CLng", "Close",
					"Const", "CSng", "CStr", "Currency", "CVar",
					"CVErr", "Date", "Debug", "Declare", "DefBool",
					"DefByte", "DefCur", "DefDate", "DefDbl", "DefInt",
					"DefLng", "DefObj", "DefSng", "DefStr", "DefVar",
					"Dim", "Do", "Double", "Each", "Else",
					"End", "Enum", "Eqv", "Erase", "Error",
					"Event", "Exit", "For", "Friend", "Function",
					"Get", "Get", "Global", "GoSub", "GoTo",
					"If", "Imp", "Implements", "In", "Input",
					"Integer", "Is", "LBound", "Len", "Let",
					"Lib", "Like", "Line", "Lock", "Long",
					"Loop", "LSet", "Mod", "Name", "Next",
					"Not", "Nothing", "Null", "Object", "On",
					"Open", "Option Base 1", "Option Compare Binary",
					"Option Compare Database", "Option Compare Text", "Option Explicit",
					"Option Private Module", "Optional", "Or", "Output",
					"ParamArray", "Preserve", "Print", "Private", "Property",
					"Public", "Put", "RaiseEvent", "Random", "Read",
					"ReDim", "Resume", "Return", "RSet", "Seek",
					"Select", "Set", "Single", "Spc", "Static",
					"Step", "Stop", "String", "Sub", "Tab",
					"Then", "To", "Type", "UBound", "Unlock",
					"Variant", "Wend", "While", "With", "WithEvents",
					"Write", "Xor"],

				"C#" => [
					"abstract", "as", "base", "bool", "break",
					"byte", "case", "catch", "char", "checked",
					/*class*/"const", "continue", "decimal", "default",
					"delegate", "do", "double", "else", "enum",
					"event", "explicit", "extern", "false", "finally",
					"fixed", "float", "for", "foreach", "goto",
					"if", "implicit", "in", "int", "interface",
					"internal", "is", "lock", "long", "namespace",
					"new", "null", "object", "operator", "out",
					"override", "params", "private", "protected", "public",
					"readonly", "ref", "return", "sbyte", "sealed",
					"short", "sizeof", "stackalloc", "static", "string",
					"struct", "switch", "this", "throw", "true",
					"try", "typeof", "uint", "ulong", "unchecked",
					"unsafe", "ushort", "using", "virtual", "volatile",
					"void", "while"],

				"Ruby" => [
					"alias", "and", "begin", "break", "case",
					/*class*/"def", "defined", "do", "else",
					"elsif", "end", "ensure", "false", "for",
					"if", "in", "module", "next", "module",
					"next", "nil", "not", "or", "redo",
					"rescue", "retry", "return", "self", "super",
					"then", "true", "undef", "unless", "until",
					"when", "while", "yield"],

				"Python" => [
					"and", "assert", "break", /*"class",*/"continue",
					"def", "del", "elif", "else", "except",
					"exec", "finally", "for", "from", "global",
					"if", "import", "in", "is", "lambda",
					"not", "or", "pass", "print", "raise",
					"return", "try", "while", "yield"],

				"Pascal" => [
					"Absolute", "Abstract", "All", "And", "And_then",
					"Array", "Asm", "Begin", "Bindable", "Case",
					/*"Class",*/"Const", "Constructor", "Destructor", "Div",
					"Do", "Downto", "Else", "End", "Export",
					"File", "For", "Function", "Goto", "If",
					"Import", "Implementation", "Inherited", "In", "Inline",
					"Interface", "Is", "Label", "Mod", "Module",
					"Nil", "Not", "Object", "Of", "Only",
					"Operator", "Or", "Or_else", "Otherwise", "Packed",
					"Pow", "Procedure", "Program", "Property", "Protected",
					"Qualified", "Record", "Repeat", "Restricted", "Set",
					"Shl", "Shr", "Then", "To", "Type",
					"Unit", "Until", "Uses", "Value", "Var",
					"View", "Virtual", "While", "With", "Xor"],

				"mIRC" => [
				],

				"PL/I" => [
					"A", "ABS", "ACOS", "%ACTIVATE", "ACTUALCOUNT",
					"ADD", "ADDR", "ADDREL", "ALIGNED", "ALLOCATE",
					"ALLOC", "ALLOCATION", "ALLOCN", "ANY", "ANYCONDITION",
					"APPEND", "AREA", "ASIN", "ATAN", "ATAND",
					"ATANH", "AUTOMATIC", "AUTO", "B", "B1",
					"B2", "B3", "B4", "BACKUP_DATE", "BASED",
					"BATCH", "BEGIN", "BINARY", "BIN", "BIT",
					"BLOCK_BOUNDARY_FORMAT", "BLOCK_IO", "BLOCK_SIZE", "BOOL",
					"BUCKET_SIZE", "BUILTIN", "BY", "BYTE", "BYTESIZE",
					"CALL", "CANCEL_CONTROL_O", "CARRIAGE_RETURN_FORMAT",
					"CEIL", "CHAR", "CHARACTER", "CLOSE", "COLLATE", "COLUMN",
					"CONDITION", "CONTIGUOUS", "CONTIGUOUS_BEST_TRY", "CONTROLLED",
					"CONVERSION", "COPY", "COS", "COSD", "COSH",
					"CREATION_DATE", "CURRENT_POSITION", "DATE",
					"DATETIME", "%DEACTIVATE", "DECIMAL", "DEC", "%DECLARE",
					"%DCL", "DECLARE", "DCL", "DECODE", "DEFAULT_FILE_NAME",
					"DEFERRED_WRITE", "DEFINED", "DEF", "DELETE",
					"DESCRIPTOR", "%DICTIONARY", "DIMENSION", "DIM", "DIRECT",
					"DISPLAY", "DIVIDE", "%DO", "DO", "E",
					"EDIT", "%ELSE", "ELSE", "EMPTY", "ENCODE",
					"%END", "END", "ENDFILE", "ENDPAGE", "ENTRY",
					"ENVIRONMENT", "ENV", "%ERROR", "ERROR", "EVERY",
					"EXP", "EXPIRATION_DATE", "EXTEND", "EXTENSION_SIZE",
					"EXTERNAL", "EXT", "F", "FAST_DELETE", "%FATAL",
					"FILE", "FILE_ID", "FILE_ID_TO", "FILE_SIZE",
					"FINISH", "FIXED", "FIXEDOVERFLOW", "FOFL",
					"FIXED_CONTROL_FROM", "FIXED_CONTROL_SIZE", "FIXED_CONTROL_SIZE_TO",
					"FIXED_CONTROL_TO", "FIXED_LENGTH_RECORDS", "FLOAT",
					"FLOOR", "FLUSH", "FORMAT", "FREE", "FROM",
					"GET", "GLOBALDEF", "GLOBALREF", "%GOTO",
					"GOTO", "GO", "TO", "GROUP_PROTETION", "HBOUND",
					"HIGH", "INDENT", "%IF", "IF", "IGNORE_LINE_MARKS",
					"IN", "%INCLUDE", "INDEX", "INDEXED", "INDEX_NUMBER",
					"%INFORM", "INFORM", "INITIAL", "INIT", "INITIAL_FILL",
					"INPUT", "INT", "INTERNAL", "INTO", "KEY",
					"KEYED", "KEYFROM", "KEYTO", "LABEL", "LBOUND",
					"LEAVE", "LENGTH", "LIKE", "LINE", "LINENO",
					"LINESIZE", "%LIST", "LIST", "LOCK_ON_READ", "LOCK_ON_WRITE",
					"LOG", "LOG10", "LOG2", "LOW", "LTRIM",
					"MAIN", "MANUAL_UNLOCKING", "MATCH_GREATER",
					"MATCH_GREATER_EQUAL", "MATCH_NEXT", "MATCH_NEXT_EQUAL",
					"MAX", "MAXIMUM_RECORD_NUMBER", "MAXIMUM_RECORD_SIZE",
					"MAXLENGTH", "MEMBER", "MIN", "MOD", "MULTIBLOCK_COUNT",
					"MULTIBUFFER_COUNT", "MULTIPLY", "NEXT_VOLUME", "%NOLIST",
					"NOLOCK", "NONEXISTENT_RECORD", "NONRECURSIVE", "NONVARYING",
					"NONVAR", "NORESCAN", "NO_ECHO", "NO_FILTER", "NO_SHARE",
					"NULL", "OFFSET", "ON", "ONARGSLIST", "ONCHAR",
					"ONCODE", "ONFILE", "ONKEY", "ONSOURCE", "OPEN",
					"OPTIONAL", "OPTIONS", "OTHERWISE", "OTHER", "OUTPUT",
					"OVERFLOW", "OFL", "OWNER_GROUP", "OWNER_ID",
					"OWNER_MEMBER", "OWNER_PROTECTION", "P", "%PAGE",
					"PAGE", "PAGENO", "PAGESIZE", "PARAMETER", "PARM",
					"PICTURE", "PIC", "POINTER", "PTR", "POSINT",
					"POSITION", "POS", "PRECISION", "PREC", "PRESENT",
					"PRINT", "PRINTER_FORMAT", "%PROCEDURE", "%PROC",
					"PROCEDURE", "PROC", "PROD", "PROMPT", "PURGE_TYPE_AHEAD",
					"PUT", "R", "RANK", "READ", "READONLY",
					"READ_AHEAD", "READ_CHECK", "READ_REGARDLESS", "RECORD",
					"RECORD_ID", "RECORD_ID_ACCESS", "RECORD_ID_TO", "RECURSIVE",
					"REFER", "REFERENCE", "RELEASE", "REPEAT", "%REPLACE",
					"RESCAN", "RESIGNAL", "RETRIEVAL_POINTERS", "%RETURN",
					"RETURN", "RETURNS", "REVERSE", "REVERT", "REVISION_DATE",
					"REWIND", "REWIND_ON_CLOSE", "REWIND_ON_OPEN",
					"REWRITE", "ROUND", "RTRIM", "%SBTTL", "SCALARVARYING",
					"SEARCH", "SELECT", "SEQUENTIAL", "SEQL",
					"SET", "SHARED_READ", "SHARED_WRITE", "SIGN",
					"SIGNAL", "SIN", "SIND", "SINH", "SIZE",
					"SKIP", "SNAP", "SOME", "SPACEBLOCK", "SPOOL",
					"SQRT", "STATEMENT", "STATIC", "STOP", "STORAGE",
					"STREAM", "STRING", "STRINGRANGE", "STRG",
					"STRUCTURE", "SUBSCRIPTRANGE", "SUBRG", "SUBSTR",
					"SUBTRACT", "SUM", "SUPERCEDE", "SYSIN", "SYSPRINT",
					"SYSTEM", "SYSTEM_PROTECTION", "TAB", "TAN",
					"TAND", "TANH", "TEMPORARY", "%THEN", "THEN",
					"TIME", "TIMEOUT_PERIOD", "%TITLE", "TITLE",
					"TO", "TRANSLATE", "TRIM", "TRUNC", "TRUNCATE",
					"UNALIGNED", "UNAL", "UNDEFINED", "UNDF", "UNDERFLOW",
					"UFL", "UNION", "UNSPEC", "UNTIL", "UPDATE",
					"USER_OPEN", "VALID", "VALUE", "VAL", "VARIABLE",
					"VARIANT", "VARYING", "VAR", "VAXCONDITION", "VERIFY",
					"WAIT_FOR_RECORD", "%WARN", "WARN", "WHEN",
					"WHILE", "WORLD_PROTECTION", "WRITE", "WRITE_BEHIND",
					"WRITE_CHECK", "X", "ZERODIVIDE"],

				"SQL" => [
					"abort", "abs", "absolute", "access",
					"action", "ada", "add", "admin",
					"after", "aggregate", "alias", "all",
					"allocate", "alter", "analyse", "analyze",
					"and", "any", "are", "array",
					"as", "asc", "asensitive", "assertion",
					"assignment", "asymmetric", "at", "atomic",
					"authorization", "avg", "backward", "before",
					"begin", "between", "bigint", "binary",
					"bit", "bitvar", "bit_length", "blob",
					"boolean", "both", "breadth", "by",
					"c", "cache", "call", "called",
					"cardinality", "cascade", "cascaded", "case",
					"cast", "catalog", "catalog_name", "chain",
					"char", "character", "characteristics", "character_length",
					"character_set_catalog", "character_set_name", "character_set_schema", "char_length",
					"check", "checked", "checkpoint", /* "class", */
					"class_origin", "clob", "close", "cluster",
					"coalesce", "cobol", "collate", "collation",
					"collation_catalog", "collation_name", "collation_schema", "column",
					"column_name", "command_function", "command_function_code", "comment",
					"commit", "committed", "completion", "condition_number",
					"connect", "connection", "connection_name", "constraint",
					"constraints", "constraint_catalog", "constraint_name", "constraint_schema",
					"constructor", "contains", "continue", "conversion",
					"convert", "copy", "corresponding", "count",
					"create", "createdb", "createuser", "cross",
					"cube", "current", "current_date", "current_path",
					"current_role", "current_time", "current_timestamp", "current_user",
					"cursor", "cursor_name", "cycle", "data",
					"database", "date", "datetime_interval_code", "datetime_interval_precision",
					"day", "deallocate", "dec", "decimal",
					"declare", "default", "defaults", "deferrable",
					"deferred", "defined", "definer", "delete",
					"delimiter", "delimiters", "depth", "deref",
					"desc", "describe", "descriptor", "destroy",
					"destructor", "deterministic", "diagnostics", "dictionary",
					"disconnect", "dispatch", "distinct", "do",
					"domain", "double", "drop", "dynamic",
					"dynamic_function", "dynamic_function_code", "each", "else",
					"encoding", "encrypted", "end", "end-exec",
					"equals", "escape", "every", "except",
					"exception", "excluding", "exclusive", "exec",
					"execute", "existing", "exists", "explain",
					"external", "extract", "false", "fetch",
					"final", "first", "float", "for",
					"force", "foreign", "fortran", "forward",
					"found", "free", "freeze", "from",
					"full", "function", "g", "general",
					"generated", "get", "global", "go",
					"goto", "grant", "granted", "group",
					"grouping", "handler", "having", "hierarchy",
					"hold", "host", "hour", "identity",
					"ignore", "ilike", "immediate", "immutable",
					"implementation", "implicit", "in", "including",
					"increment", "index", "indicator", "infix",
					"inherits", "initialize", "initially", "inner",
					"inout", "input", "insensitive", "insert",
					"instance", "instantiable", "instead", "int",
					"integer", "intersect", "interval", "into",
					"invoker", "is", "isnull", "isolation",
					"iterate", "join", "k", "key",
					"key_member", "key_type", "lancompiler", "language",
					"large", "last", "lateral", "leading",
					"left", "length", "less", "level",
					"like", "limit", "listen", "load",
					"local", "localtime", "localtimestamp", "location",
					"locator", "lock", "lower", "m",
					"map", "match", "max", "maxvalue",
					"message_length", "message_octet_length", "message_text", "method",
					"min", "minute", "minvalue", "mod",
					"mode", "modifies", "modify", "module",
					"month", "more", "move", "mumps",
					"name", "names", "national", "natural",
					"nchar", "nclob", "new", "next",
					"no", "nocreatedb", "nocreateuser", "none",
					"not", "nothing", "notify", "notnull",
					"null", "nullable", "nullif", "number",
					"numeric", "object", "octet_length", "of",
					"off", "offset", "oids", "old",
					"on", "only", "open", "operation",
					"operator", "option", "options", "or",
					"order", "ordinality", "out", "outer",
					"output", "overlaps", "overlay", "overriding",
					"owner", "pad", "parameter", "parameters",
					"parameter_mode", "parameter_name", "parameter_ordinal_position", "parameter_specific_catalog",
					"parameter_specific_name", "parameter_specific_schema", "partial", "pascal",
					"password", "path", "pendant", "placing",
					"pli", "position", "postfix", "precision",
					"prefix", "preorder", "prepare", "preserve",
					"primary", "prior", "privileges", "procedural",
					"procedure", "public", "read", "reads",
					"real", "recheck", "recursive", "ref",
					"references", "referencing", "reindex", "relative",
					"rename", "repeatable", "replace", "reset",
					"restart", "restrict", "result", "return",
					"returned_length", "returned_octet_length", "returned_sqlstate", "returns",
					"revoke", "right", "role", "rollback",
					"rollup", "routine", "routine_catalog", "routine_name",
					"routine_schema", "row", "rows", "row_count",
					"rule", "savepoint", "scale", "schema",
					"schema_name", "scope", "scroll", "search",
					"second", "section", "security", "select",
					"self", "sensitive", "sequence", "serializable",
					"server_name", "session", "session_user", "set",
					"setof", "sets", "share", "show",
					"similar", "simple", "size", "smallint",
					"some", "source", "space", "specific",
					"specifictype", "specific_name", "sql", "sqlcode",
					"sqlerror", "sqlexception", "sqlstate", "sqlwarning",
					"stable", "start", "state", "statement",
					"static", "statistics", "stdin", "stdout",
					"storage", "strict", "structure", "style",
					"subclass_origin", "sublist", "substring", "sum",
					"symmetric", "sysid", "system", "system_user",
					"table", "table_name", "temp", "template",
					"temporary", "terminate", "text", "than", "then",
					"time", "timestamp", "timezone_hour", "timezone_minute",
					"to", "toast", "trailing", "transaction",
					"transactions_committed", "transactions_rolled_back", "transaction_active", "transform",
					"transforms", "translate", "translation", "treat",
					"trigger", "trigger_catalog", "trigger_name", "trigger_schema",
					"trim", "true", "truncate", "trusted",
					"type", "uncommitted", "under", "unencrypted",
					"union", "unique", "unknown", "unlisten",
					"unnamed", "unnest", "until", "update",
					"upper", "usage", "user", "user_defined_type_catalog",
					"user_defined_type_name", "user_defined_type_schema", "using", "vacuum",
					"valid", "validator", "value", "values",
					"varchar", "variable", "varying", "verbose",
					"version", "view", "volatile", "when",
					"whenever", "where", "with", "without",
					"work", "write", "year", "zone"],

			];

			$case_insensitive = [
				"VB" => true,
				"Pascal" => true,
				"PL/I" => true,
				"SQL" => true,
			];
			$ncs = false;
			if (array_key_exists($language, $case_insensitive)) {
				$ncs = true;
			}

			$text = (array_key_exists($language, $preproc)) ?
			$preproc_replace($preproc[$language], $text) :
			$text;
			$text = (array_key_exists($language, $keywords)) ?
			$keyword_replace($keywords[$language], $text, $ncs) :
			$text;

			return $text;
		};

		$rtrim1 = function ($span, $lang, $ch) use ($syntax_highlight_helper) {
			return $syntax_highlight_helper(substr($span, 0, -1), $lang);
		};

		$rtrim1_htmlesc = function ($span, $lang, $ch) {
			return htmlspecialchars(substr($span, 0, -1));
		};

		$sch_rtrim1 = function ($span, $lang, $ch) use ($sch_syntax_helper) {
			return $sch_syntax_helper(substr($span, 0, -1));
		};

		$rtrim2 = function ($span, $lang, $ch) {
			return substr($span, 0, -2);
		};

		$syn_proc = function ($span, $lang, $ch) use ($syntax_highlight_helper) {
			return $syntax_highlight_helper($span, $lang);
		};

		$dash_putback = function ($span, $lang, $ch) use ($syntax_highlight_helper) {
			return $syntax_highlight_helper('-' . $span, $lang);
		};

		$slash_putback = function ($span, $lang, $ch) use ($syntax_highlight_helper) {
			return $syntax_highlight_helper('/' . $span, $lang);
		};

		$slash_putback_rtrim1 = function ($span, $lang, $ch) use ($rtrim1) {
			return $rtrim1('/' . $span, $lang, $ch);
		};

		$lparen_putback = function ($span, $lang, $ch) use ($syntax_highlight_helper) {
			return $syntax_highlight_helper('(' . $span, $lang);
		};

		$lparen_putback_rtrim1 = function ($span, $lang, $ch) use ($rtrim1) {
			return $rtrim1('(' . $span, $lang, $ch);
		};

		$prepend_xml_opentag = function ($span, $lang, $ch) {
			return '<span class="xml_tag">&lt;' . $span;
		};

		$proc_void = function ($span, $lang, $ch) {
			return $span;
		};

		$this->sch[self::SCH_NORMAL][0]   = self::SCH_NORMAL;
		$this->sch[self::SCH_NORMAL]['"'] = self::SCH_STRLIT;
		$this->sch[self::SCH_NORMAL]["#"] = self::SCH_CHRLIT;
		$this->sch[self::SCH_NORMAL]["0"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["1"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["2"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["3"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["4"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["5"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["6"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["7"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["8"] = self::SCH_NUMLIT;
		$this->sch[self::SCH_NORMAL]["9"] = self::SCH_NUMLIT;

		$this->sch[self::SCH_STRLIT]['"']  = self::SCH_NORMAL;
		$this->sch[self::SCH_STRLIT]["\n"] = self::SCH_NORMAL;
		$this->sch[self::SCH_STRLIT]["\\"] = self::SCH_STRESC;
		$this->sch[self::SCH_STRLIT][0]    = self::SCH_STRLIT;

		$this->sch[self::SCH_CHRLIT][" "]  = self::SCH_NORMAL;
		$this->sch[self::SCH_CHRLIT]["\t"] = self::SCH_NORMAL;
		$this->sch[self::SCH_CHRLIT]["\n"] = self::SCH_NORMAL;
		$this->sch[self::SCH_CHRLIT]["\r"] = self::SCH_NORMAL;
		$this->sch[self::SCH_CHRLIT][0]    = self::SCH_CHRLIT;

		$this->sch[self::SCH_NUMLIT][" "]  = self::SCH_NORMAL;
		$this->sch[self::SCH_NUMLIT]["\t"] = self::SCH_NORMAL;
		$this->sch[self::SCH_NUMLIT]["\n"] = self::SCH_NORMAL;
		$this->sch[self::SCH_NUMLIT]["\r"] = self::SCH_NORMAL;
		$this->sch[self::SCH_NUMLIT][0]    = self::SCH_NUMLIT;

		//
		// State transitions for C
		//
		$this->c89[self::NORMAL_TEXT]["\""] = self::DQ_LITERAL;
		$this->c89[self::NORMAL_TEXT]["'"]  = self::SQ_LITERAL;
		$this->c89[self::NORMAL_TEXT]["/"]  = self::SLASH_BEGIN;
		$this->c89[self::NORMAL_TEXT][0]    = self::NORMAL_TEXT;

		$this->c89[self::DQ_LITERAL]["\""] = self::NORMAL_TEXT;
		$this->c89[self::DQ_LITERAL]["\n"] = self::NORMAL_TEXT;
		$this->c89[self::DQ_LITERAL]["\\"] = self::DQ_ESCAPE;
		$this->c89[self::DQ_LITERAL][0]    = self::DQ_LITERAL;

		$this->c89[self::DQ_ESCAPE][0] = self::DQ_LITERAL;

		$this->c89[self::SQ_LITERAL]["'"]  = self::NORMAL_TEXT;
		$this->c89[self::SQ_LITERAL]["\n"] = self::NORMAL_TEXT;
		$this->c89[self::SQ_LITERAL]["\\"] = self::SQ_ESCAPE;
		$this->c89[self::SQ_LITERAL][0]    = self::SQ_LITERAL;

		$this->c89[self::SQ_ESCAPE][0] = self::SQ_LITERAL;

		$this->c89[self::SLASH_BEGIN]["*"] = self::STAR_COMMENT;
		$this->c89[self::SLASH_BEGIN][0]   = self::NORMAL_TEXT;

		$this->c89[self::STAR_COMMENT]["*"] = self::STAR_END;
		$this->c89[self::STAR_COMMENT][0]   = self::STAR_COMMENT;

		$this->c89[self::STAR_END]["/"] = self::NORMAL_TEXT;
		$this->c89[self::STAR_END]["*"] = self::STAR_END;
		$this->c89[self::STAR_END][0]   = self::STAR_COMMENT;

		//
		// State transitions for C++
		// Inherit transitions from C, and add line comment support
		//
		$this->cpp                           = $this->c89;
		$this->cpp[self::SLASH_BEGIN]["/"]   = self::LINE_COMMENT;
		$this->cpp[self::LINE_COMMENT]["\n"] = self::NORMAL_TEXT;
		$this->cpp[self::LINE_COMMENT]["\\"] = self::LC_ESCAPE;
		$this->cpp[self::LINE_COMMENT][0]    = self::LINE_COMMENT;

		$this->cpp[self::LC_ESCAPE]["\r"] = self::LC_ESCAPE;
		$this->cpp[self::LC_ESCAPE][0]    = self::LINE_COMMENT;

		//
		// State transitions for C99.
		// C99 supports line comments like C++
		//
		$this->c99 = $this->cpp;

		// State transitions for PL/I
		// Kinda like C
		$this->pli = $this->c89;

		//
		// State transitions for PHP
		// Inherit transitions from C++, and add perl-style line comment support
		$this->php                         = $this->cpp;
		$this->php[self::NORMAL_TEXT]["#"] = self::LINE_COMMENT;
		$this->php[self::SQ_LITERAL]["\n"] = self::SQ_LITERAL;
		$this->php[self::DQ_LITERAL]["\n"] = self::DQ_LITERAL;

		//
		// State transitions for Perl
		$this->perl[self::NORMAL_TEXT]["#"]  = self::LINE_COMMENT;
		$this->perl[self::NORMAL_TEXT]["\""] = self::DQ_LITERAL;
		$this->perl[self::NORMAL_TEXT]["'"]  = self::SQ_LITERAL;
		$this->perl[self::NORMAL_TEXT][0]    = self::NORMAL_TEXT;

		$this->perl[self::DQ_LITERAL]["\""] = self::NORMAL_TEXT;
		$this->perl[self::DQ_LITERAL]["\\"] = self::DQ_ESCAPE;
		$this->perl[self::DQ_LITERAL][0]    = self::DQ_LITERAL;

		$this->perl[self::DQ_ESCAPE][0] = self::DQ_LITERAL;

		$this->perl[self::SQ_LITERAL]["'"]  = self::NORMAL_TEXT;
		$this->perl[self::SQ_LITERAL]["\\"] = self::SQ_ESCAPE;
		$this->perl[self::SQ_LITERAL][0]    = self::SQ_LITERAL;

		$this->perl[self::SQ_ESCAPE][0] = self::SQ_LITERAL;

		$this->perl[self::LINE_COMMENT]["\n"] = self::NORMAL_TEXT;
		$this->perl[self::LINE_COMMENT][0]    = self::LINE_COMMENT;

		$this->mirc[self::NORMAL_TEXT]["\""] = self::DQ_LITERAL;
		$this->mirc[self::NORMAL_TEXT][";"]  = self::LINE_COMMENT;
		$this->mirc[self::NORMAL_TEXT][0]    = self::NORMAL_TEXT;

		$this->mirc[self::DQ_LITERAL]["\""] = self::NORMAL_TEXT;
		$this->mirc[self::DQ_LITERAL]["\\"] = self::DQ_ESCAPE;
		$this->mirc[self::DQ_LITERAL][0]    = self::DQ_LITERAL;

		$this->mirc[self::DQ_ESCAPE][0] = self::DQ_LITERAL;

		$this->mirc[self::LINE_COMMENT]["\n"] = self::NORMAL_TEXT;
		$this->mirc[self::LINE_COMMENT][0]    = self::LINE_COMMENT;

		$this->ruby = $this->perl;

		$this->python = $this->perl;

		$this->java = $this->cpp;

		$this->vb                         = $this->perl;
		$this->vb[self::NORMAL_TEXT]["#"] = self::NORMAL_TEXT;
		$this->vb[self::NORMAL_TEXT]["'"] = self::LINE_COMMENT;

		$this->cs = $this->java;

		$this->pascal                         = $this->c89;
		$this->pascal[self::NORMAL_TEXT]["("] = self::PAREN_BEGIN;
		$this->pascal[self::NORMAL_TEXT]["/"] = self::SLASH_BEGIN;
		$this->pascal[self::NORMAL_TEXT]["{"] = self::BLOCK_COMMENT;

		$this->pascal[self::PAREN_BEGIN]["*"] = self::STAR_COMMENT;
		$this->pascal[self::PAREN_BEGIN]["'"] = self::SQ_LITERAL;
		$this->pascal[self::PAREN_BEGIN]['"'] = self::DQ_LITERAL;
		$this->pascal[self::PAREN_BEGIN][0]   = self::NORMAL_TEXT;

		$this->pascal[self::SLASH_BEGIN]["'"] = self::SQ_LITERAL;
		$this->pascal[self::SLASH_BEGIN]['"'] = self::DQ_LITERAL;
		$this->pascal[self::SLASH_BEGIN]['/'] = self::LINE_COMMENT;
		$this->pascal[self::SLASH_BEGIN][0]   = self::NORMAL_TEXT;

		$this->pascal[self::STAR_COMMENT]["*"] = self::STAR_END;
		$this->pascal[self::STAR_COMMENT][0]   = self::STAR_COMMENT;

		$this->pascal[self::BLOCK_COMMENT]["}"] = self::NORMAL_TEXT;
		$this->pascal[self::BLOCK_COMMENT][0]   = self::BLOCK_COMMENT;

		$this->pascal[self::LINE_COMMENT]["\n"] = self::NORMAL_TEXT;
		$this->pascal[self::LINE_COMMENT][0]    = self::LINE_COMMENT;

		$this->pascal[self::STAR_END][")"] = self::NORMAL_TEXT;
		$this->pascal[self::STAR_END]["*"] = self::STAR_END;
		$this->pascal[self::STAR_END][0]   = self::STAR_COMMENT;

		$this->sql[self::NORMAL_TEXT]['"'] = self::DQ_LITERAL;
		$this->sql[self::NORMAL_TEXT]["'"] = self::SQ_LITERAL;
		$this->sql[self::NORMAL_TEXT]['`'] = self::BT_LITERAL;
		$this->sql[self::NORMAL_TEXT]['-'] = self::DASH_BEGIN;
		$this->sql[self::NORMAL_TEXT][0]   = self::NORMAL_TEXT;

		$this->sql[self::DQ_LITERAL]['"']  = self::NORMAL_TEXT;
		$this->sql[self::DQ_LITERAL]['\\'] = self::DQ_ESCAPE;
		$this->sql[self::DQ_LITERAL][0]    = self::DQ_LITERAL;

		$this->sql[self::SQ_LITERAL]["'"]  = self::NORMAL_TEXT;
		$this->sql[self::SQ_LITERAL]['\\'] = self::SQ_ESCAPE;
		$this->sql[self::SQ_LITERAL][0]    = self::SQ_LITERAL;

		$this->sql[self::BT_LITERAL]['`']  = self::NORMAL_TEXT;
		$this->sql[self::BT_LITERAL]['\\'] = self::BT_ESCAPE;
		$this->sql[self::BT_LITERAL][0]    = self::BT_LITERAL;

		$this->sql[self::DQ_ESCAPE][0] = self::DQ_LITERAL;
		$this->sql[self::SQ_ESCAPE][0] = self::SQ_LITERAL;
		$this->sql[self::BT_ESCAPE][0] = self::BT_LITERAL;

		$this->sql[self::DASH_BEGIN]["-"] = self::LINE_COMMENT;
		$this->sql[self::DASH_BEGIN][0]   = self::NORMAL_TEXT;

		$this->sql[self::LINE_COMMENT]["\n"] = self::NORMAL_TEXT;
		$this->sql[self::LINE_COMMENT]["\\"] = self::LC_ESCAPE;
		$this->sql[self::LINE_COMMENT][0]    = self::LINE_COMMENT;

		$this->sql[self::LC_ESCAPE]["\r"] = self::LC_ESCAPE;
		$this->sql[self::LC_ESCAPE][0]    = self::LINE_COMMENT;

		$this->xml[self::NORMAL_TEXT]["<"]   = self::XML_TAG_BEGIN;
		$this->xml[self::NORMAL_TEXT]["&"]   = self::HTML_ENTITY;
		$this->xml[self::NORMAL_TEXT][0]     = self::NORMAL_TEXT;
		$this->xml[self::HTML_ENTITY][";"]   = self::NORMAL_TEXT;
		$this->xml[self::HTML_ENTITY]["<"]   = self::XML_TAG_BEGIN;
		$this->xml[self::HTML_ENTITY][0]     = self::HTML_ENTITY;
		$this->xml[self::XML_TAG_BEGIN]["?"] = self::XML_PI;
		$this->xml[self::XML_TAG_BEGIN]["!"] = self::LINE_COMMENT;
		$this->xml[self::XML_TAG_BEGIN][0]   = self::XML_TAG;
		$this->xml[self::XML_TAG][">"]       = self::NORMAL_TEXT;
		$this->xml[self::XML_TAG]["\""]      = self::DQ_LITERAL;
		$this->xml[self::XML_TAG]["'"]       = self::SQ_LITERAL;
		$this->xml[self::XML_TAG][0]         = self::XML_TAG;
		$this->xml[self::XML_PI][">"]        = self::NORMAL_TEXT;
		$this->xml[self::XML_PI][0]          = self::XML_TAG;
		$this->xml[self::LINE_COMMENT][">"]  = self::NORMAL_TEXT;
		$this->xml[self::LINE_COMMENT][0]    = self::LINE_COMMENT;
		$this->xml[self::DQ_LITERAL]["\""]   = self::XML_TAG;
		$this->xml[self::DQ_LITERAL]["&"]    = self::DQ_ESCAPE;
		$this->xml[self::DQ_LITERAL][0]      = self::DQ_LITERAL;
		$this->xml[self::SQ_LITERAL]["'"]    = self::XML_TAG;
		$this->xml[self::SQ_LITERAL]["&"]    = self::SQ_ESCAPE;
		$this->xml[self::SQ_LITERAL][0]      = self::SQ_LITERAL;
		$this->xml[self::DQ_ESCAPE][";"]     = self::DQ_LITERAL;
		$this->xml[self::DQ_ESCAPE][0]       = self::DQ_ESCAPE;

		//
		// Main state transition table
		//
		$this->states = [
			"C89" => $this->c89,
			"C" => $this->c99,
			"C++" => $this->cpp,
			"PHP" => $this->php,
			"Perl" => $this->perl,
			"Java" => $this->java,
			"VB" => $this->vb,
			"C#" => $this->cs,
			"Ruby" => $this->ruby,
			"Python" => $this->python,
			"Pascal" => $this->pascal,
			"mIRC" => $this->mirc,
			"PL/I" => $this->pli,
			"SQL" => $this->sql,
			"XML" => $this->xml,
			"Scheme" => $this->sch,
		];

		//
		// Process functions
		//
		$this->process["C89"][self::NORMAL_TEXT][self::SQ_LITERAL]  = $rtrim1;
		$this->process["C89"][self::NORMAL_TEXT][self::DQ_LITERAL]  = $rtrim1;
		$this->process["C89"][self::NORMAL_TEXT][self::SLASH_BEGIN] = $rtrim1;
		$this->process["C89"][self::NORMAL_TEXT][0]                 = $syn_proc;

		$this->process["C89"][self::SLASH_BEGIN][self::STAR_COMMENT] = $rtrim1;
		$this->process["C89"][self::SLASH_BEGIN][0]                  = $slash_putback;

		$this->process["Scheme"][self::SCH_NORMAL][self::SCH_STRLIT] = $$this;
		$this->process["Scheme"][self::SCH_NORMAL][self::SCH_CHRLIT] = $$this;
		$this->process["Scheme"][self::SCH_NORMAL][self::SCH_NUMLIT] = $$this;

		$this->process["SQL"][self::NORMAL_TEXT][self::SQ_LITERAL] = $rtrim1;
		$this->process["SQL"][self::NORMAL_TEXT][self::DQ_LITERAL] = $rtrim1;
		$this->process["SQL"][self::NORMAL_TEXT][self::BT_LITERAL] = $rtrim1;
		$this->process["SQL"][self::NORMAL_TEXT][self::DASH_BEGIN] = $rtrim1;
		$this->process["SQL"][self::NORMAL_TEXT][0]                = $syn_proc;

		$this->process["SQL"][self::DASH_BEGIN][self::LINE_COMMENT] = $rtrim1;
		$this->process["SQL"][self::DASH_BEGIN][0]                  = $dash_putback;

		$this->process["PL/I"] = $this->process["C89"];

		$this->process["C++"]                                        = $this->process["C89"];
		$this->process["C++"][self::SLASH_BEGIN][self::LINE_COMMENT] = $rtrim1;

		$this->process["C"] = $this->process["C++"];

		$this->process["PHP"]                                        = $this->process["C++"];
		$this->process["PHP"][self::NORMAL_TEXT][self::LINE_COMMENT] = $rtrim1;

		$this->process["Perl"][self::NORMAL_TEXT][self::SQ_LITERAL]   = $rtrim1;
		$this->process["Perl"][self::NORMAL_TEXT][self::DQ_LITERAL]   = $rtrim1;
		$this->process["Perl"][self::NORMAL_TEXT][self::LINE_COMMENT] = $rtrim1;
		$this->process["Perl"][self::NORMAL_TEXT][0]                  = $syn_proc;

		$this->process["Ruby"]   = $this->process["Perl"];
		$this->process["Python"] = $this->process["Perl"];

		$this->process["mIRC"][self::NORMAL_TEXT][self::DQ_LITERAL]   = $rtrim1;
		$this->process["mIRC"][self::NORMAL_TEXT][self::LINE_COMMENT] = $rtrim1;
		$this->process["mIRC"][self::NORMAL_TEXT][0]                  = $syn_proc;

		$this->process["VB"] = $this->process["Perl"];

		$this->process["Java"] = $this->process["C++"];

		$this->process["C#"] = $this->process["Java"];

		$this->process["Pascal"]                                         = $this->process["C++"];
		$this->process["Pascal"][self::NORMAL_TEXT][self::LINE_COMMENT]  = $rtrim1;
		$this->process["Pascal"][self::NORMAL_TEXT][self::BLOCK_COMMENT] = $rtrim1;
		$this->process["Pascal"][self::NORMAL_TEXT][self::PAREN_BEGIN]   = $rtrim1;
		$this->process["Pascal"][self::SLASH_BEGIN][self::SQ_LITERAL]    = $slash_putback_rtrim1;
		$this->process["Pascal"][self::SLASH_BEGIN][self::DQ_LITERAL]    = $slash_putback_rtrim1;
		$this->process["Pascal"][self::SLASH_BEGIN][0]                   = $slash_putback;
		$this->process["Pascal"][self::PAREN_BEGIN][self::SQ_LITERAL]    = $lparen_putback_rtrim1;
		$this->process["Pascal"][self::PAREN_BEGIN][self::DQ_LITERAL]    = $lparen_putback_rtrim1;
		$this->process["Pascal"][self::PAREN_BEGIN][self::STAR_COMMENT]  = $rtrim1;
		$this->process["Pascal"][self::PAREN_BEGIN][0]                   = $lparen_putback;

		$this->process["XML"][self::NORMAL_TEXT][self::XML_TAG_BEGIN]  = $rtrim1;
		$this->process["XML"][self::NORMAL_TEXT][self::HTML_ENTITY]    = $rtrim1;
		$this->process["XML"][self::HTML_ENTITY][self::XML_TAG_BEGIN]  = $rtrim1;
		$this->process["XML"][self::HTML_ENTITY][0]                    = $proc_void;
		$this->process["XML"][self::XML_TAG_BEGIN][self::XML_TAG]      = $prepend_xml_opentag;
		$this->process["XML"][self::XML_TAG_BEGIN][self::XML_PI]       = $rtrim1;
		$this->process["XML"][self::XML_TAG_BEGIN][self::LINE_COMMENT] = $rtrim1;
		$this->process["XML"][self::LINE_COMMENT][self::NORMAL_TEXT]   = $rtrim1_htmlesc;
		$this->process["XML"][self::XML_TAG][self::NORMAL_TEXT]        = $rtrim1;
		$this->process["XML"][self::XML_TAG][self::DQ_LITERAL]         = $rtrim1;
		$this->process["XML"][self::DQ_LITERAL][self::XML_TAG]         = $rtrim1;
		$this->process["XML"][self::DQ_LITERAL][self::DQ_ESCAPE]       = $rtrim1;

		$this->process_end["C89"]    = $syntax_highlight_helper;
		$this->process_end["C++"]    = $this->process_end["C89"];
		$this->process_end["C"]      = $this->process_end["C89"];
		$this->process_end["PHP"]    = $this->process_end["C89"];
		$this->process_end["Perl"]   = $this->process_end["C89"];
		$this->process_end["Java"]   = $this->process_end["C89"];
		$this->process_end["VB"]     = $this->process_end["C89"];
		$this->process_end["C#"]     = $this->process_end["C89"];
		$this->process_end["Ruby"]   = $this->process_end["C89"];
		$this->process_end["Python"] = $this->process_end["C89"];
		$this->process_end["Pascal"] = $this->process_end["C89"];
		$this->process_end["mIRC"]   = $this->process_end["C89"];
		$this->process_end["PL/I"]   = $this->process_end["C89"];
		$this->process_end["SQL"]    = $this->process_end["C89"];
		$this->process_end["Scheme"] = $sch_syntax_helper;

		$this->edges["C89"][self::NORMAL_TEXT . "," . self::DQ_LITERAL]   = '<span class="literal">"';
		$this->edges["C89"][self::NORMAL_TEXT . "," . self::SQ_LITERAL]   = '<span class="literal">\'';
		$this->edges["C89"][self::SLASH_BEGIN . "," . self::STAR_COMMENT] = '<span class="comment">/*';
		$this->edges["C89"][self::DQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["C89"][self::SQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["C89"][self::STAR_END . "," . self::NORMAL_TEXT]     = '</span>';

		$this->edges["Scheme"][self::SCH_NORMAL . "," . self::SCH_STRLIT] = '<span class="sch_str">"';
		$this->edges["Scheme"][self::SCH_NORMAL . "," . self::SCH_NUMLIT] = '<span class="sch_num">';
		$this->edges["Scheme"][self::SCH_NORMAL . "," . self::SCH_CHRLIT] = '<span class="sch_chr">#';
		$this->edges["Scheme"][self::SCH_STRLIT . "," . self::SCH_NORMAL] = '</span>';
		$this->edges["Scheme"][self::SCH_NUMLIT . "," . self::SCH_NORMAL] = '</span>';
		$this->edges["Scheme"][self::SCH_CHRLIT . "," . self::SCH_NORMAL] = '</span>';

		$this->edges["SQL"][self::NORMAL_TEXT . "," . self::DQ_LITERAL]   = '<span class="literal">"';
		$this->edges["SQL"][self::NORMAL_TEXT . "," . self::SQ_LITERAL]   = '<span class="literal">\'';
		$this->edges["SQL"][self::DASH_BEGIN . "," . self::LINE_COMMENT]  = '<span class="comment">--';
		$this->edges["SQL"][self::NORMAL_TEXT . "," . self::BT_LITERAL]   = '`';
		$this->edges["SQL"][self::DQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["SQL"][self::SQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["SQL"][self::LINE_COMMENT . "," . self::NORMAL_TEXT] = '</span>';

		$this->edges["PL/I"] = $this->edges["C89"];

		$this->edges["C++"]                                               = $this->edges["C89"];
		$this->edges["C++"][self::SLASH_BEGIN . "," . self::LINE_COMMENT] = '<span class="comment">//';
		$this->edges["C++"][self::LINE_COMMENT . "," . self::NORMAL_TEXT] = '</span>';

		$this->edges["C"] = $this->edges["C++"];

		$this->edges["PHP"]                                               = $this->edges["C++"];
		$this->edges["PHP"][self::NORMAL_TEXT . "," . self::LINE_COMMENT] = '<span class="comment">#';

		$this->edges["Perl"][self::NORMAL_TEXT . "," . self::DQ_LITERAL]   = '<span class="literal">"';
		$this->edges["Perl"][self::NORMAL_TEXT . "," . self::SQ_LITERAL]   = '<span class="literal">\'';
		$this->edges["Perl"][self::DQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["Perl"][self::SQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["Perl"][self::NORMAL_TEXT . "," . self::LINE_COMMENT] = '<span class="comment">#';
		$this->edges["Perl"][self::LINE_COMMENT . "," . self::NORMAL_TEXT] = '</span>';

		$this->edges["Ruby"] = $this->edges["Perl"];

		$this->edges["Python"] = $this->edges["Perl"];

		$this->edges["mIRC"][self::NORMAL_TEXT . "," . self::DQ_LITERAL]   = '<span class="literal">"';
		$this->edges["mIRC"][self::NORMAL_TEXT . "," . self::LINE_COMMENT] = '<span class="comment">;';
		$this->edges["mIRC"][self::DQ_LITERAL . "," . self::NORMAL_TEXT]   = '</span>';
		$this->edges["mIRC"][self::LINE_COMMENT . "," . self::NORMAL_TEXT] = '</span>';

		$this->edges["VB"]                                               = $this->edges["Perl"];
		$this->edges["VB"][self::NORMAL_TEXT . "," . self::LINE_COMMENT] = '<span class="comment">\'';

		$this->edges["Java"] = $this->edges["C++"];

		$this->edges["C#"] = $this->edges["Java"];

		$this->edges["Pascal"]                                                = $this->edges["C89"];
		$this->edges["Pascal"][self::PAREN_BEGIN . "," . self::STAR_COMMENT]  = '<span class="comment">(*';
		$this->edges["Pascal"][self::PAREN_BEGIN . "," . self::DQ_LITERAL]    = '<span class="literal">"';
		$this->edges["Pascal"][self::PAREN_BEGIN . "," . self::SQ_LITERAL]    = '<span class="literal">\'';
		$this->edges["Pascal"][self::SLASH_BEGIN . "," . self::DQ_LITERAL]    = '<span class="literal">"';
		$this->edges["Pascal"][self::SLASH_BEGIN . "," . self::SQ_LITERAL]    = '<span class="literal">\'';
		$this->edges["Pascal"][self::SLASH_BEGIN . "," . self::LINE_COMMENT]  = '<span class="comment">//';
		$this->edges["Pascal"][self::NORMAL_TEXT . "," . self::BLOCK_COMMENT] = '<span class="comment">{';
		$this->edges["Pascal"][self::LINE_COMMENT . "," . self::NORMAL_TEXT]  = '</span>';
		$this->edges["Pascal"][self::BLOCK_COMMENT . "," . self::NORMAL_TEXT] = '</span>';

		$this->edges["XML"][self::NORMAL_TEXT . "," . self::HTML_ENTITY]    = '<span class="html_entity">&amp;';
		$this->edges["XML"][self::HTML_ENTITY . "," . self::NORMAL_TEXT]    = '</span>';
		$this->edges["XML"][self::HTML_ENTITY . "," . self::XML_TAG_BEGIN]  = '</span>';
		$this->edges["XML"][self::XML_TAG . "," . self::NORMAL_TEXT]        = '&gt;</span>';
		$this->edges["XML"][self::XML_TAG_BEGIN . "," . self::XML_PI]       = '<span class="xml_pi">&lt;?';
		$this->edges["XML"][self::XML_TAG_BEGIN . "," . self::LINE_COMMENT] = '<span class="comment">&lt;!';
		$this->edges["XML"][self::LINE_COMMENT . "," . self::NORMAL_TEXT]   = '&gt;</span>';
		$this->edges["XML"][self::XML_TAG . "," . self::DQ_LITERAL]         = '<span class="literal">"';
		$this->edges["XML"][self::DQ_LITERAL . "," . self::XML_TAG]         = '"</span>';
		$this->edges["XML"][self::DQ_LITERAL . "," . self::DQ_ESCAPE]       = '<span class="html_entity">&amp;';
		$this->edges["XML"][self::DQ_ESCAPE . "," . self::DQ_LITERAL]       = '</span>';
		$this->edges["XML"][self::XML_TAG . "," . self::SQ_LITERAL]         = '<span class="literal">\'';
		$this->edges["XML"][self::SQ_LITERAL . "," . self::XML_TAG]         = '\'</span>';
		$this->edges["XML"][self::SQ_LITERAL . "," . self::SQ_ESCAPE]       = '<span class="html_entity">&amp;';
		$this->edges["XML"][self::SQ_ESCAPE . "," . self::SQ_LITERAL]       = '</span>';

	}

	/**
	 * Syntax highlight function
	 * Does the bulk of the syntax highlighting by lexing the input
	 * string, then calling the helper function to highlight keywords.
	 */
	function syntax_highlight($text, $language) {
		if ($language == "Plain Text") {
			return $text;
		}

		//
		// The State Machine
		//
		if (array_key_exists($language, $this->initial_state)) {
			$state = $this->initial_state[$language];
		} else {
			$state = self::NORMAL_TEXT;
		}

		$output = "";
		$span   = "";
		while (strlen($text) > 0) {
			$ch   = substr($text, 0, 1);
			$text = substr($text, 1);

			$oldstate = $state;
			$state    = (array_key_exists($ch, $this->states[$language][$state])) ?
			$this->states[$language][$state][$ch] :
			$this->states[$language][$state][0];

			$span .= $ch;

			if ($oldstate != $state) {
				if (array_key_exists($language, $this->process) &&
					array_key_exists($oldstate, $this->process[$language])) {
					if (array_key_exists($state, $this->process[$language][$oldstate])) {
						$pf = $this->process[$language][$oldstate][$state];
						$output .= $pf($span, $language, $ch);
					} else {
						$pf = $this->process[$language][$oldstate][0];
						$output .= $pf($span, $language, $ch);
					}
				} else {
					$output .= $span;
				}

				if (array_key_exists($language, $this->edges) &&
					array_key_exists("$oldstate,$state", $this->edges[$language])) {
					$output .= $this->edges[$language]["$oldstate,$state"];
				}

				$span = "";
			}
		}

		if (array_key_exists($language, $this->process_end) && $state == self::NORMAL_TEXT) {
			$output .= $this->process_end[$language]($span, $language);
		} else {
			$output .= $span;
		}

		if ($state != self::NORMAL_TEXT) {
			if (array_key_exists($language, $this->edges) &&
				array_key_exists("$state," . self::NORMAL_TEXT, $this->edges[$language])) {
				$output .= $this->edges[$language]["$state," . self::NORMAL_TEXT];
			}

		}

		return $output;
	}
}
