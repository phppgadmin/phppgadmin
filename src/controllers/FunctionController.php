<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class FunctionController extends BaseController {
	public $_name       = 'FunctionController';
	public $table_place = 'functions-functions';
/**
 * Function to save after editing a function
 */
	public function doSaveEdit() {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$fnlang = strtolower($_POST['original_lang']);

		if ($fnlang == 'c') {
			$def = [$_POST['formObjectFile'], $_POST['formLinkSymbol']];
		} else if ($fnlang == 'internal') {
			$def = $_POST['formLinkSymbol'];
		} else {
			$def = $_POST['formDefinition'];
		}
		if (!$data->hasFunctionAlterSchema()) {
			$_POST['formFuncSchema'] = '';
		}

		$status = $data->setFunction($_POST['function_oid'], $_POST['original_function'], $_POST['formFunction'],
			$_POST['original_arguments'], $_POST['original_returns'], $def,
			$_POST['original_lang'], $_POST['formProperties'], isset($_POST['original_setof']),
			$_POST['original_owner'], $_POST['formFuncOwn'], $_POST['original_schema'],
			$_POST['formFuncSchema'], isset($_POST['formCost']) ? $_POST['formCost'] : null,
			isset($_POST['formRows']) ? $_POST['formRows'] : 0, $_POST['formComment']);

		if ($status == 0) {
			// If function has had schema altered, need to change to the new schema
			// and reload the browser frame.
			if (!empty($_POST['formFuncSchema']) && ($_POST['formFuncSchema'] != $_POST['original_schema'])) {
				// Jump them to the new function schema
				$misc->setCurrentSchema($_POST['formFuncSchema']);
				// Force a browser reload
				$this->misc->setReloadBrowser(true);
			}
			$this->doProperties($lang['strfunctionupdated']);
		} else {
			$this->doEdit($lang['strfunctionupdatedbad']);
		}
	}

	/**
	 * Function to allow editing of a Function
	 */
	public function doEdit($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('function');
		$misc->printTitle($lang['stralter'], 'pg.function.alter');
		$misc->printMsg($msg);

		$fndata = $data->getFunction($_REQUEST['function_oid']);

		if ($fndata->recordCount() > 0) {
			$fndata->fields['proretset'] = $data->phpBool($fndata->fields['proretset']);

			// Initialise variables
			if (!isset($_POST['formDefinition'])) {
				$_POST['formDefinition'] = $fndata->fields['prosrc'];
			}

			if (!isset($_POST['formProperties'])) {
				$_POST['formProperties'] = $data->getFunctionProperties($fndata->fields);
			}

			if (!isset($_POST['formFunction'])) {
				$_POST['formFunction'] = $fndata->fields['proname'];
			}

			if (!isset($_POST['formComment'])) {
				$_POST['formComment'] = $fndata->fields['procomment'];
			}

			if (!isset($_POST['formObjectFile'])) {
				$_POST['formObjectFile'] = $fndata->fields['probin'];
			}

			if (!isset($_POST['formLinkSymbol'])) {
				$_POST['formLinkSymbol'] = $fndata->fields['prosrc'];
			}

			if (!isset($_POST['formFuncOwn'])) {
				$_POST['formFuncOwn'] = $fndata->fields['proowner'];
			}

			if (!isset($_POST['formFuncSchema'])) {
				$_POST['formFuncSchema'] = $fndata->fields['proschema'];
			}

			if ($data->hasFunctionCosting()) {
				if (!isset($_POST['formCost'])) {
					$_POST['formCost'] = $fndata->fields['procost'];
				}

				if (!isset($_POST['formRows'])) {
					$_POST['formRows'] = $fndata->fields['prorows'];
				}

			}

			// Deal with named parameters
			if ($data->hasNamedParams()) {
				if (isset($fndata->fields['proallarguments'])) {
					$args_arr = $data->phpArray($fndata->fields['proallarguments']);
				} else {
					$args_arr = explode(', ', $fndata->fields['proarguments']);
				}
				$names_arr = $data->phpArray($fndata->fields['proargnames']);
				$modes_arr = $data->phpArray($fndata->fields['proargmodes']);
				$args      = '';
				$i         = 0;
				for ($i = 0; $i < sizeof($args_arr); $i++) {
					if ($i != 0) {
						$args .= ', ';
					}

					if (isset($modes_arr[$i])) {
						switch ($modes_arr[$i]) {
							case 'i':$args .= " IN ";
								break;
							case 'o':$args .= " OUT ";
								break;
							case 'b':$args .= " INOUT ";
								break;
							case 'v':$args .= " VARIADIC ";
								break;
							case 't':$args .= " TABLE ";
								break;
						}
					}
					if (isset($names_arr[$i]) && $names_arr[$i] != '') {
						$data->fieldClean($names_arr[$i]);
						$args .= '"' . $names_arr[$i] . '" ';
					}
					$args .= $args_arr[$i];
				}
			} else {
				$args = $fndata->fields['proarguments'];
			}

			$func_full = $fndata->fields['proname'] . "(" . $fndata->fields['proarguments'] . ")";
			echo "<form action=\"/src/views/functions.php\" method=\"post\">\n";
			echo "<table style=\"width: 90%\">\n";
			echo "<tr>\n";
			echo "<th class=\"data required\">{$lang['strschema']}</th>\n";
			echo "<th class=\"data required\">{$lang['strfunction']}</th>\n";
			echo "<th class=\"data\">{$lang['strarguments']}</th>\n";
			echo "<th class=\"data required\">{$lang['strreturns']}</th>\n";
			echo "<th class=\"data required\">{$lang['strproglanguage']}</th>\n";
			echo "</tr>\n";

			echo "<tr>\n";
			echo "<td class=\"data1\">";
			echo "<input type=\"hidden\" name=\"original_schema\" value=\"", htmlspecialchars($fndata->fields['proschema']), "\" />\n";
			if ($data->hasFunctionAlterSchema()) {
				$schemas = $data->getSchemas();
				echo "<select name=\"formFuncSchema\">";
				while (!$schemas->EOF) {
					$schema = $schemas->fields['nspname'];
					echo "<option value=\"", htmlspecialchars($schema), "\"",
					($schema == $_POST['formFuncSchema']) ? ' selected="selected"' : '', ">", htmlspecialchars($schema), "</option>\n";
					$schemas->moveNext();
				}
				echo "</select>\n";
			} else {
				echo $fndata->fields['proschema'];
			}

			echo "</td>\n";
			echo "<td class=\"data1\">";
			echo "<input type=\"hidden\" name=\"original_function\" value=\"", htmlspecialchars($fndata->fields['proname']), "\" />\n";
			echo "<input name=\"formFunction\" style=\"width: 100%\" maxlength=\"{$data->_maxNameLen}\" value=\"", htmlspecialchars($_POST['formFunction']), "\" />";
			echo "</td>\n";

			echo "<td class=\"data1\">", $misc->printVal($args), "\n";
			echo "<input type=\"hidden\" name=\"original_arguments\" value=\"", htmlspecialchars($args), "\" />\n";
			echo "</td>\n";

			echo "<td class=\"data1\">";
			if ($fndata->fields['proretset']) {
				echo "setof ";
			}

			echo $misc->printVal($fndata->fields['proresult']), "\n";
			echo "<input type=\"hidden\" name=\"original_returns\" value=\"", htmlspecialchars($fndata->fields['proresult']), "\" />\n";
			if ($fndata->fields['proretset']) {
				echo "<input type=\"hidden\" name=\"original_setof\" value=\"yes\" />\n";
			}

			echo "</td>\n";

			echo "<td class=\"data1\">", $misc->printVal($fndata->fields['prolanguage']), "\n";
			echo "<input type=\"hidden\" name=\"original_lang\" value=\"", htmlspecialchars($fndata->fields['prolanguage']), "\" />\n";
			echo "</td>\n";
			echo "</tr>\n";

			$fnlang = strtolower($fndata->fields['prolanguage']);
			if ($fnlang == 'c') {
				echo "<tr><th class=\"data required\" colspan=\"2\">{$lang['strobjectfile']}</th>\n";
				echo "<th class=\"data\" colspan=\"2\">{$lang['strlinksymbol']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"2\"><input type=\"text\" name=\"formObjectFile\" style=\"width:100%\" value=\"",
				htmlspecialchars($_POST['formObjectFile']), "\" /></td>\n";
				echo "<td class=\"data1\" colspan=\"2\"><input type=\"text\" name=\"formLinkSymbol\" style=\"width:100%\" value=\"",
				htmlspecialchars($_POST['formLinkSymbol']), "\" /></td></tr>\n";
			} else if ($fnlang == 'internal') {
				echo "<tr><th class=\"data\" colspan=\"5\">{$lang['strlinksymbol']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"5\"><input type=\"text\" name=\"formLinkSymbol\" style=\"width:100%\" value=\"",
				htmlspecialchars($_POST['formLinkSymbol']), "\" /></td></tr>\n";
			} else {
				echo "<tr><th class=\"data required\" colspan=\"5\">{$lang['strdefinition']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"5\"><textarea style=\"width:100%;\" rows=\"20\" cols=\"50\" name=\"formDefinition\">",
				htmlspecialchars($_POST['formDefinition']), "</textarea></td></tr>\n";
			}

			// Display function comment
			echo "<tr><th class=\"data\" colspan=\"5\">{$lang['strcomment']}</th></tr>\n";
			echo "<tr><td class=\"data1\" colspan=\"5\"><textarea style=\"width:100%;\" name=\"formComment\" rows=\"3\" cols=\"50\">",
			htmlspecialchars($_POST['formComment']), "</textarea></td></tr>\n";

			// Display function cost options
			if ($data->hasFunctionCosting()) {
				echo "<tr><th class=\"data required\" colspan=\"5\">{$lang['strfunctioncosting']}</th></tr>\n";
				echo "<td class=\"data1\" colspan=\"2\">{$lang['strexecutioncost']}: <input name=\"formCost\" size=\"16\" value=\"" .
				htmlspecialchars($_POST['formCost']) . "\" /></td>";
				echo "<td class=\"data1\" colspan=\"2\">{$lang['strresultrows']}: <input name=\"formRows\" size=\"16\" value=\"",
				htmlspecialchars($_POST['formRows']), "\"", (!$fndata->fields['proretset']) ? 'disabled' : '', "/></td>";
			}

			// Display function properties
			if (is_array($data->funcprops) && sizeof($data->funcprops) > 0) {
				echo "<tr><th class=\"data\" colspan=\"5\">{$lang['strproperties']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"5\">\n";
				$i = 0;
				foreach ($data->funcprops as $k => $v) {
					echo "<select name=\"formProperties[{$i}]\">\n";
					foreach ($v as $p) {
						echo "<option value=\"", htmlspecialchars($p), "\"",
						($p == $_POST['formProperties'][$i]) ? ' selected="selected"' : '',
						">", $misc->printVal($p), "</option>\n";
					}
					echo "</select><br />\n";
					$i++;
				}
				echo "</td></tr>\n";
			}

			// function owner
			if ($data->hasFunctionAlterOwner()) {
				$users = $data->getUsers();
				echo "<tr><td class=\"data1\" colspan=\"5\">{$lang['strowner']}: <select name=\"formFuncOwn\">";
				while (!$users->EOF) {
					$uname = $users->fields['usename'];
					echo "<option value=\"", htmlspecialchars($uname), "\"",
					($uname == $_POST['formFuncOwn']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
					$users->moveNext();
				}
				echo "</select>\n";
				echo "<input type=\"hidden\" name=\"original_owner\" value=\"", htmlspecialchars($fndata->fields['proowner']), "\" />\n";
				echo "</td></tr>\n";
			}
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_edit\" />\n";
			echo "<input type=\"hidden\" name=\"function\" value=\"", htmlspecialchars($_REQUEST['function']), "\" />\n";
			echo "<input type=\"hidden\" name=\"function_oid\" value=\"", htmlspecialchars($_REQUEST['function_oid']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

	/**
	 * Show read only properties of a function
	 */
	public function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('function');
		$misc->printTitle($lang['strproperties'], 'pg.function');
		$misc->printMsg($msg);

		$funcdata = $data->getFunction($_REQUEST['function_oid']);

		if ($funcdata->recordCount() > 0) {

			// Deal with named parameters
			if ($data->hasNamedParams()) {
				if (isset($funcdata->fields['proallarguments'])) {
					$args_arr = $data->phpArray($funcdata->fields['proallarguments']);
				} else {
					$args_arr = explode(', ', $funcdata->fields['proarguments']);
				}
				$names_arr = $data->phpArray($funcdata->fields['proargnames']);
				$modes_arr = $data->phpArray($funcdata->fields['proargmodes']);
				$args      = '';
				$i         = 0;
				for ($i = 0; $i < sizeof($args_arr); $i++) {
					if ($i != 0) {
						$args .= ', ';
					}

					if (isset($modes_arr[$i])) {
						switch ($modes_arr[$i]) {
							case 'i':$args .= " IN ";
								break;
							case 'o':$args .= " OUT ";
								break;
							case 'b':$args .= " INOUT ";
								break;
							case 'v':$args .= " VARIADIC ";
								break;
							case 't':$args .= " TABLE ";
								break;
						}
					}
					if (isset($names_arr[$i]) && $names_arr[$i] != '') {
						$data->fieldClean($names_arr[$i]);
						$args .= '"' . $names_arr[$i] . '" ';
					}
					$args .= $args_arr[$i];
				}
			} else {
				$args = $funcdata->fields['proarguments'];
			}

			// Show comment if any
			if ($funcdata->fields['procomment'] !== null) {
				echo "<p class=\"comment\">", $misc->printVal($funcdata->fields['procomment']), "</p>\n";
			}

			$funcdata->fields['proretset'] = $data->phpBool($funcdata->fields['proretset']);
			$func_full                     = $funcdata->fields['proname'] . "(" . $funcdata->fields['proarguments'] . ")";
			echo "<table style=\"width: 90%\">\n";
			echo "<tr><th class=\"data\">{$lang['strfunction']}</th>\n";
			echo "<th class=\"data\">{$lang['strarguments']}</th>\n";
			echo "<th class=\"data\">{$lang['strreturns']}</th>\n";
			echo "<th class=\"data\">{$lang['strproglanguage']}</th></tr>\n";
			echo "<tr><td class=\"data1\">", $misc->printVal($funcdata->fields['proname']), "</td>\n";
			echo "<td class=\"data1\">", $misc->printVal($args), "</td>\n";
			echo "<td class=\"data1\">";
			if ($funcdata->fields['proretset']) {
				echo "setof ";
			}

			echo $misc->printVal($funcdata->fields['proresult']), "</td>\n";
			echo "<td class=\"data1\">", $misc->printVal($funcdata->fields['prolanguage']), "</td></tr>\n";

			$fnlang = strtolower($funcdata->fields['prolanguage']);
			if ($fnlang == 'c') {
				echo "<tr><th class=\"data\" colspan=\"2\">{$lang['strobjectfile']}</th>\n";
				echo "<th class=\"data\" colspan=\"2\">{$lang['strlinksymbol']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"2\">", $misc->printVal($funcdata->fields['probin']), "</td>\n";
				echo "<td class=\"data1\" colspan=\"2\">", $misc->printVal($funcdata->fields['prosrc']), "</td></tr>\n";
			} else if ($fnlang == 'internal') {
				echo "<tr><th class=\"data\" colspan=\"4\">{$lang['strlinksymbol']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"4\">", $misc->printVal($funcdata->fields['prosrc']), "</td></tr>\n";
			} else {
				$highlight = new \PHPPgAdmin\Highlight();

				echo "<tr><th class=\"data\" colspan=\"4\">{$lang['strdefinition']}</th></tr>\n";
				// Check to see if we have syntax highlighting for this language
				if (array_key_exists($fnlang, $data->langmap)) {

					$temp = $highlight->syntax_highlight(htmlspecialchars($funcdata->fields['prosrc']), $data->langmap[$fnlang]);
					$tag  = 'prenoescape';
				} else {
					$temp = $funcdata->fields['prosrc'];
					$tag  = 'pre';
				}
				echo "<tr><td class=\"data1\" colspan=\"4\">", $misc->printVal($temp, $tag, ['lineno' => true, 'class' => 'data1']), "</td></tr>\n";
			}

			// Display function cost options
			if ($data->hasFunctionCosting()) {
				echo "<tr><th class=\"data required\" colspan=\"4\">{$lang['strfunctioncosting']}</th></tr>\n";
				echo "<td class=\"data1\" colspan=\"2\">{$lang['strexecutioncost']}: ", $misc->printVal($funcdata->fields['procost']), " </td>";
				echo "<td class=\"data1\" colspan=\"2\">{$lang['strresultrows']}: ", $misc->printVal($funcdata->fields['prorows']), " </td>";
			}

			// Show flags
			if (is_array($data->funcprops) && sizeof($data->funcprops) > 0) {
				// Fetch an array of the function properties
				$funcprops = $data->getFunctionProperties($funcdata->fields);
				echo "<tr><th class=\"data\" colspan=\"4\">{$lang['strproperties']}</th></tr>\n";
				echo "<tr><td class=\"data1\" colspan=\"4\">\n";
				foreach ($funcprops as $v) {
					echo $misc->printVal($v), "<br />\n";
				}
				echo "</td></tr>\n";
			}

			echo "<tr><td class=\"data1\" colspan=\"5\">{$lang['strowner']}: ", htmlspecialchars($funcdata->fields['proowner']), "\n";
			echo "</td></tr>\n";
			echo "</table>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

		$navlinks = [
			'showall' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strshowallfunctions'],
			],
			'alter' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'edit',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'function' => $_REQUEST['function'],
							'function_oid' => $_REQUEST['function_oid'],
						],
					],
				],
				'content' => $lang['stralter'],
			],
			'drop' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'function' => $func_full,
							'function_oid' => $_REQUEST['function_oid'],
						],
					],
				],
				'content' => $lang['strdrop'],
			],
		];

		$misc->printNavLinks($navlinks, 'functions-properties', get_defined_vars());
	}

	/**
	 * Show confirmation of drop and perform actual drop
	 */
	public function doDrop($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['function']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifyfunctiontodrop']);
			exit();
		}

		if ($confirm) {
			$misc->printTrail('schema');
			$misc->printTitle($lang['strdrop'], 'pg.function.drop');

			echo "<form action=\"/src/views/functions.php\" method=\"post\">\n";

			//If multi drop
			if (isset($_REQUEST['ma'])) {
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfdropfunction'], $misc->printVal($a['function'])), "</p>\n";
					echo '<input type="hidden" name="function[]" value="', htmlspecialchars($a['function']), "\" />\n";
					echo "<input type=\"hidden\" name=\"function_oid[]\" value=\"", htmlspecialchars($a['function_oid']), "\" />\n";
				}
			} else {
				echo "<p>", sprintf($lang['strconfdropfunction'], $misc->printVal($_REQUEST['function'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"function\" value=\"", htmlspecialchars($_REQUEST['function']), "\" />\n";
				echo "<input type=\"hidden\" name=\"function_oid\" value=\"", htmlspecialchars($_REQUEST['function_oid']), "\" />\n";
			}

			echo "<input type=\"hidden\" name=\"action\" value=\"drop\" />\n";

			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /><label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			if (is_array($_POST['function_oid'])) {
				$msg    = '';
				$status = $data->beginTransaction();
				if ($status == 0) {
					foreach ($_POST['function_oid'] as $k => $s) {
						$status = $data->dropFunction($s, isset($_POST['cascade']));
						if ($status == 0) {
							$msg .= sprintf('%s: %s<br />', htmlentities($_POST['function'][$k], ENT_QUOTES, 'UTF-8'), $lang['strfunctiondropped']);
						} else {
							$data->endTransaction();
							$this->doDefault(sprintf('%s%s: %s<br />', $msg, htmlentities($_POST['function'][$k], ENT_QUOTES, 'UTF-8'), $lang['strfunctiondroppedbad']));
							return;
						}
					}
				}
				if ($data->endTransaction() == 0) {
					// Everything went fine, back to the Default page....
					$this->misc->setReloadBrowser(true);
					$this->doDefault($msg);
				} else {
					$this->doDefault($lang['strfunctiondroppedbad']);
				}

			} else {
				$status = $data->dropFunction($_POST['function_oid'], isset($_POST['cascade']));
				if ($status == 0) {
					$this->misc->setReloadBrowser(true);
					$this->doDefault($lang['strfunctiondropped']);
				} else {
					$this->doDefault($lang['strfunctiondroppedbad']);
				}
			}
		}

	}

	/**
	 * Displays a screen where they can enter a new function
	 */
	public function doCreate($msg = '', $szJS = "") {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('schema');
		if (!isset($_POST['formFunction'])) {
			$_POST['formFunction'] = '';
		}

		if (!isset($_POST['formArguments'])) {
			$_POST['formArguments'] = '';
		}

		if (!isset($_POST['formReturns'])) {
			$_POST['formReturns'] = '';
		}

		if (!isset($_POST['formLanguage'])) {
			$_POST['formLanguage'] = isset($_REQUEST['language']) ? $_REQUEST['language'] : 'sql';
		}

		if (!isset($_POST['formDefinition'])) {
			$_POST['formDefinition'] = '';
		}

		if (!isset($_POST['formObjectFile'])) {
			$_POST['formObjectFile'] = '';
		}

		if (!isset($_POST['formLinkSymbol'])) {
			$_POST['formLinkSymbol'] = '';
		}

		if (!isset($_POST['formProperties'])) {
			$_POST['formProperties'] = $data->defaultprops;
		}

		if (!isset($_POST['formSetOf'])) {
			$_POST['formSetOf'] = '';
		}

		if (!isset($_POST['formArray'])) {
			$_POST['formArray'] = '';
		}

		if (!isset($_POST['formCost'])) {
			$_POST['formCost'] = '';
		}

		if (!isset($_POST['formRows'])) {
			$_POST['formRows'] = '';
		}

		if (!isset($_POST['formComment'])) {
			$_POST['formComment'] = '';
		}

		$types  = $data->getTypes(true, true, true);
		$langs  = $data->getLanguages(true);
		$fnlang = strtolower($_POST['formLanguage']);

		switch ($fnlang) {
			case 'c':
				$misc->printTitle($lang['strcreatecfunction'], 'pg.function.create.c');
				break;
			case 'internal':
				$misc->printTitle($lang['strcreateinternalfunction'], 'pg.function.create.internal');
				break;
			default:
				$misc->printTitle($lang['strcreateplfunction'], 'pg.function.create.pl');
				break;
		}
		$misc->printMsg($msg);

		// Create string for return type list
		$szTypes = "";
		while (!$types->EOF) {
			$szSelected = "";
			if ($types->fields['typname'] == $_POST['formReturns']) {
				$szSelected = " selected=\"selected\"";
			}
			/* this variable is include in the JS code bellow, so we need to ENT_QUOTES */
			$szTypes .= "<option value=\"" . htmlspecialchars($types->fields['typname'], ENT_QUOTES) . "\"{$szSelected}>";
			$szTypes .= htmlspecialchars($types->fields['typname'], ENT_QUOTES) . "</option>";
			$types->moveNext();
		}

		$szFunctionName = "<td class=\"data1\"><input name=\"formFunction\" size=\"16\" maxlength=\"{$data->_maxNameLen}\" value=\"" .
		htmlspecialchars($_POST['formFunction']) . "\" /></td>";

		$szArguments = "<td class=\"data1\"><input name=\"formArguments\" style=\"width:100%;\" size=\"16\" value=\"" .
		htmlspecialchars($_POST['formArguments']) . "\" /></td>";

		$szSetOfSelected    = "";
		$szNotSetOfSelected = "";
		if ($_POST['formSetOf'] == '') {
			$szNotSetOfSelected = " selected=\"selected\"";
		} else if ($_POST['formSetOf'] == 'SETOF') {
			$szSetOfSelected = " selected=\"selected\"";
		}
		$szReturns = "<td class=\"data1\" colspan=\"2\">";
		$szReturns .= "<select name=\"formSetOf\">";
		$szReturns .= "<option value=\"\"{$szNotSetOfSelected}></option>";
		$szReturns .= "<option value=\"SETOF\"{$szSetOfSelected}>SETOF</option>";
		$szReturns .= "</select>";

		$szReturns .= "<select name=\"formReturns\">" . $szTypes . "</select>";

		// Create string array type selector

		$szArraySelected    = "";
		$szNotArraySelected = "";
		if ($_POST['formArray'] == '') {
			$szNotArraySelected = " selected=\"selected\"";
		} else if ($_POST['formArray'] == '[]') {
			$szArraySelected = " selected=\"selected\"";
		}

		$szReturns .= "<select name=\"formArray\">";
		$szReturns .= "<option value=\"\"{$szNotArraySelected}></option>";
		$szReturns .= "<option value=\"[]\"{$szArraySelected}>[ ]</option>";
		$szReturns .= "</select>\n</td>";

		// Create string for language
		$szLanguage = "<td class=\"data1\">";
		if ($fnlang == 'c' || $fnlang == 'internal') {
			$szLanguage .= $_POST['formLanguage'] . "\n";
			$szLanguage .= "<input type=\"hidden\" name=\"formLanguage\" value=\"{$_POST['formLanguage']}\" />\n";
		} else {
			$szLanguage .= "<select name=\"formLanguage\">\n";
			while (!$langs->EOF) {
				$szSelected = '';
				if ($langs->fields['lanname'] == $_POST['formLanguage']) {
					$szSelected = ' selected="selected"';
				}
				if (strtolower($langs->fields['lanname']) != 'c' && strtolower($langs->fields['lanname']) != 'internal') {
					$szLanguage .= "<option value=\"" . htmlspecialchars($langs->fields['lanname']) . "\"{$szSelected}>\n" .
					$misc->printVal($langs->fields['lanname']) . "</option>";
				}

				$langs->moveNext();
			}
			$szLanguage .= "</select>\n";
		}

		$szLanguage .= "</td>";
		$szJSArguments = "<tr><th class=\"data\" colspan=\"7\">{$lang['strarguments']}</th></tr>";
		$arrayModes    = ["IN", "OUT", "INOUT"];
		$szModes       = "<select name=\"formArgModes[]\" style=\"width:100%;\">";
		foreach ($arrayModes as $pV) {
			$szModes .= "<option value=\"{$pV}\">{$pV}</option>";
		}
		$szModes .= "</select>";
		$szArgReturns = "<select name=\"formArgArray[]\">";
		$szArgReturns .= "<option value=\"\"></option>";
		$szArgReturns .= "<option value=\"[]\">[]</option>";
		$szArgReturns .= "</select>";
		if (!empty($conf['theme'])) {
			$szImgPath = "images/themes/{$conf['theme']}";
		} else {
			$szImgPath = "images/themes/default";
		}
		if (empty($msg)) {
			$szJSTRArg = "<script type=\"text/javascript\" >addArg();</script>\n";
		} else {
			$szJSTRArg = "";
		}
		$szJSAddTR = "<tr id=\"parent_add_tr\" onclick=\"addArg();\" onmouseover=\"this.style.cursor='pointer'\">\n<td style=\"text-align: right\" colspan=\"6\" class=\"data3\"><table><tr><td class=\"data3\"><img src=\"{$szImgPath}/AddArguments.png\" alt=\"Add Argument\" /></td><td class=\"data3\"><span style=\"font-size: 8pt\">{$lang['strargadd']}</span></td></tr></table></td>\n</tr>\n";

		echo "<script src=\"/js/functions.js\" type=\"text/javascript\"></script>
		<script type=\"text/javascript\">
			//<![CDATA[
			var g_types_select = '<select name=\"formArgType[]\">{$szTypes}</select>{$szArgReturns}';
			var g_modes_select = '{$szModes}';
			var g_name = '';
			var g_lang_strargremove = '", htmlspecialchars($lang["strargremove"], ENT_QUOTES), "';
			var g_lang_strargnoargs = '", htmlspecialchars($lang["strargnoargs"], ENT_QUOTES), "';
			var g_lang_strargenableargs = '", htmlspecialchars($lang["strargenableargs"], ENT_QUOTES), "';
			var g_lang_strargnorowabove = '", htmlspecialchars($lang["strargnorowabove"], ENT_QUOTES), "';
			var g_lang_strargnorowbelow = '", htmlspecialchars($lang["strargnorowbelow"], ENT_QUOTES), "';
			var g_lang_strargremoveconfirm = '", htmlspecialchars($lang["strargremoveconfirm"], ENT_QUOTES), "';
			var g_lang_strargraise = '", htmlspecialchars($lang["strargraise"], ENT_QUOTES), "';
			var g_lang_strarglower = '", htmlspecialchars($lang["strarglower"], ENT_QUOTES), "';
			//]]>
		</script>
		";
		echo "<form action=\"/src/views//views/functions.php\" method=\"post\">\n";
		echo "<table><tbody id=\"args_table\">\n";
		echo "<tr><th class=\"data required\">{$lang['strname']}</th>\n";
		echo "<th class=\"data required\" colspan=\"2\">{$lang['strreturns']}</th>\n";
		echo "<th class=\"data required\">{$lang['strproglanguage']}</th></tr>\n";
		echo "<tr>\n";
		echo "{$szFunctionName}\n";
		echo "{$szReturns}\n";
		echo "{$szLanguage}\n";
		echo "</tr>\n";
		echo "{$szJSArguments}\n";
		echo "<tr>\n";
		echo "<th class=\"data required\">{$lang['strargmode']}</th>\n";
		echo "<th class=\"data required\">{$lang['strname']}</th>\n";
		echo "<th class=\"data required\" colspan=\"2\">{$lang['strargtype']}</th>\n";
		echo "</tr>\n";
		echo "{$szJSAddTR}\n";

		if ($fnlang == 'c') {
			echo "<tr><th class=\"data required\" colspan=\"2\">{$lang['strobjectfile']}</th>\n";
			echo "<th class=\"data\" colspan=\"2\">{$lang['strlinksymbol']}</th></tr>\n";
			echo "<tr><td class=\"data1\" colspan=\"2\"><input type=\"text\" name=\"formObjectFile\" style=\"width:100%\" value=\"",
			htmlspecialchars($_POST['formObjectFile']), "\" /></td>\n";
			echo "<td class=\"data1\" colspan=\"2\"><input type=\"text\" name=\"formLinkSymbol\" style=\"width:100%\" value=\"",
			htmlspecialchars($_POST['formLinkSymbol']), "\" /></td></tr>\n";
		} else if ($fnlang == 'internal') {
			echo "<tr><th class=\"data\" colspan=\"4\">{$lang['strlinksymbol']}</th></tr>\n";
			echo "<tr><td class=\"data1\" colspan=\"4\"><input type=\"text\" name=\"formLinkSymbol\" style=\"width:100%\" value=\"",
			htmlspecialchars($_POST['formLinkSymbol']), "\" /></td></tr>\n";
		} else {
			echo "<tr><th class=\"data required\" colspan=\"4\">{$lang['strdefinition']}</th></tr>\n";
			echo "<tr><td class=\"data1\" colspan=\"4\"><textarea style=\"width:100%;\" rows=\"20\" cols=\"50\" name=\"formDefinition\">",
			htmlspecialchars($_POST['formDefinition']), "</textarea></td></tr>\n";
		}

		// Display function comment
		echo "<tr><th class=\"data\" colspan=\"4\">{$lang['strcomment']}</th></tr>\n";
		echo "<tr><td class=\"data1\" colspan=\"4\"><textarea style=\"width:100%;\" name=\"formComment\" rows=\"3\" cols=\"50\">",
		htmlspecialchars($_POST['formComment']), "</textarea></td></tr>\n";

		// Display function cost options
		if ($data->hasFunctionCosting()) {
			echo "<tr><th class=\"data required\" colspan=\"4\">{$lang['strfunctioncosting']}</th></tr>\n";
			echo "<td class=\"data1\" colspan=\"2\">{$lang['strexecutioncost']}: <input name=\"formCost\" size=\"16\" value=\"" .
			htmlspecialchars($_POST['formCost']) . "\" /></td>";
			echo "<td class=\"data1\" colspan=\"2\">{$lang['strresultrows']}: <input name=\"formRows\" size=\"16\" value=\"" .
			htmlspecialchars($_POST['formRows']) . "\" /></td>";
		}

		// Display function properties
		if (is_array($data->funcprops) && sizeof($data->funcprops) > 0) {
			echo "<tr><th class=\"data required\" colspan=\"4\">{$lang['strproperties']}</th></tr>\n";
			echo "<tr><td class=\"data1\" colspan=\"4\">\n";
			$i = 0;
			foreach ($data->funcprops as $k => $v) {
				echo "<select name=\"formProperties[{$i}]\">\n";
				foreach ($v as $p) {
					echo "<option value=\"", htmlspecialchars($p), "\"",
					($p == $_POST['formProperties'][$i]) ? ' selected="selected"' : '',
					">", $misc->printVal($p), "</option>\n";
				}
				echo "</select><br />\n";
				$i++;
			}
			echo "</td></tr>\n";
		}
		echo "</tbody></table>\n";
		echo $szJSTRArg;
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
		echo $szJS;
	}

	/**
	 * Actually creates the new function in the database
	 */
	public function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$fnlang = strtolower($_POST['formLanguage']);

		if ($fnlang == 'c') {
			$def = [$_POST['formObjectFile'], $_POST['formLinkSymbol']];
		} else if ($fnlang == 'internal') {
			$def = $_POST['formLinkSymbol'];
		} else {
			$def = $_POST['formDefinition'];
		}

		$szJS = '';

		echo "<script src=\"/js/functions.js\" type=\"text/javascript\"></script>";
		echo "<script type=\"text/javascript\">" . buildJSData() . '</script>';
		if (!empty($_POST['formArgName'])) {
			$szJS = buildJSRows(buildFunctionArguments($_POST));
		} else {
			$szJS = "<script type=\"text/javascript\" src=\"/js/functions.js\">noArgsRebuild(addArg());</script>";
		}

		$cost = (isset($_POST['formCost'])) ? $_POST['formCost'] : null;
		if ($cost == '' || !is_numeric($cost) || $cost != (int) $cost || $cost < 0) {
			$cost = null;
		}

		$rows = (isset($_POST['formRows'])) ? $_POST['formRows'] : null;
		if ($rows == '' || !is_numeric($rows) || $rows != (int) $rows) {
			$rows = null;
		}

		// Check that they've given a name and a definition
		if ($_POST['formFunction'] == '') {
			$this->doCreate($lang['strfunctionneedsname'], $szJS);
		} elseif ($fnlang != 'internal' && !$def) {
			$this->doCreate($lang['strfunctionneedsdef'], $szJS);
		} else {
			// Append array symbol to type if chosen
			$status = $data->createFunction($_POST['formFunction'], empty($_POST['nojs']) ? buildFunctionArguments($_POST) : $_POST['formArguments'],
				$_POST['formReturns'] . $_POST['formArray'], $def, $_POST['formLanguage'],
				$_POST['formProperties'], $_POST['formSetOf'] == 'SETOF',
				$cost, $rows, $_POST['formComment'], false);
			if ($status == 0) {
				$this->doDefault($lang['strfunctioncreated']);
			} else {
				$this->doCreate($lang['strfunctioncreatedbad'], $szJS);
			}
		}
	}

/**
 * Build out the function arguments string
 */
	function buildFunctionArguments($arrayVars) {
		if (isset($_POST['formArgName'])) {
			$arrayArgs = [];
			foreach ($arrayVars['formArgName'] as $pK => $pV) {
				$arrayArgs[] = $arrayVars['formArgModes'][$pK] . ' ' . trim($pV) . ' ' . trim($arrayVars['formArgType'][$pK]) . $arrayVars['formArgArray'][$pK];
			}
			return implode(",", $arrayArgs);
		}
		return '';
	}

/**
 * Build out JS to re-create table rows for arguments
 */
	function buildJSRows($szArgs) {
		$arrayModes      = ['IN', 'OUT', 'INOUT'];
		$arrayArgs       = explode(',', $szArgs);
		$arrayProperArgs = [];
		$nC              = 0;
		$szReturn        = '';
		foreach ($arrayArgs as $pV) {
			$arrayWords = explode(' ', $pV);
			if (in_array($arrayWords[0], $arrayModes) === true) {
				$szMode = $arrayWords[0];
				array_shift($arrayWords);
			}
			$szArgName = array_shift($arrayWords);
			if (strpos($arrayWords[count($arrayWords) - 1], '[]') === false) {
				$szArgType   = implode(" ", $arrayWords);
				$bArgIsArray = "false";
			} else {
				$szArgType   = str_replace('[]', '', implode(' ', $arrayWords));
				$bArgIsArray = "true";
			}
			$arrayProperArgs[] = [$szMode, $szArgName, $szArgType, $bArgIsArray];
			$szReturn .= "<script type=\"text/javascript\">RebuildArgTR('{$szMode}','{$szArgName}','{$szArgType}',new Boolean({$bArgIsArray}));</script>";
			$nC++;
		}
		return $szReturn;
	}

	function buildJSData() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$arrayModes  = ['IN', 'OUT', 'INOUT'];
		$arrayTypes  = $data->getTypes(true, true, true);
		$arrayPTypes = [];
		$arrayPModes = [];
		$szTypes     = '';

		while (!$arrayTypes->EOF) {
			$arrayPTypes[] = "'" . $arrayTypes->fields['typname'] . "'";
			$arrayTypes->moveNext();
		}

		foreach ($arrayModes as $pV) {
			$arrayPModes[] = "'{$pV}'";
		}

		$szTypes = 'g_main_types = new Array(' . implode(',', $arrayPTypes) . ');';
		$szModes = 'g_main_modes = new Array(' . implode(',', $arrayPModes) . ');';
		return $szTypes . $szModes;
	}

	/**
	 * Show default list of functions in the database
	 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('schema');
		$misc->printTabs('schema', 'functions');
		$misc->printMsg($msg);

		$funcs = $data->getFunctions();

		$columns = [
			'function' => [
				'title' => $lang['strfunction'],
				'field' => Decorator::field('proproto'),
				'url' => "/redirect/function?action=properties&amp;{$misc->href}&amp;",
				'vars' => ['function' => 'proproto', 'function_oid' => 'prooid'],
			],
			'returns' => [
				'title' => $lang['strreturns'],
				'field' => Decorator::field('proreturns'),
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('proowner'),
			],
			'proglanguage' => [
				'title' => $lang['strproglanguage'],
				'field' => Decorator::field('prolanguage'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('procomment'),
			],
		];

		$actions = [
			'multiactions' => [
				'keycols' => ['function' => 'proproto', 'function_oid' => 'prooid'],
				'url' => 'functions.php',
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'edit',
							'function' => Decorator::field('proproto'),
							'function_oid' => Decorator::field('prooid'),
						],
					],
				],
			],
			'drop' => [
				'multiaction' => 'confirm_drop',
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'function' => Decorator::field('proproto'),
							'function_oid' => Decorator::field('prooid'),
						],
					],
				],
			],
			'privileges' => [
				'content' => $lang['strprivileges'],
				'attr' => [
					'href' => [
						'url' => 'privileges.php',
						'urlvars' => [
							'subject' => 'function',
							'function' => Decorator::field('proproto'),
							'function_oid' => Decorator::field('prooid'),
						],
					],
				],
			],
		];

		echo $misc->printTable($funcs, $columns, $actions, $this->table_place, $lang['strnofunctions']);

		$navlinks = [
			'createpl' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'create',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreateplfunction'],
			],
			'createinternal' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'create',
							'language' => 'internal',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreateinternalfunction'],
			],
			'createc' => [
				'attr' => [
					'href' => [
						'url' => 'functions.php',
						'urlvars' => [
							'action' => 'create',
							'language' => 'C',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreatecfunction'],
			],
		];

		$misc->printNavLinks($navlinks, 'functions-functions', get_defined_vars());

		echo $this->view->fetch('table_list_footer.twig', ['table_class' => $this->table_place]);
	}

	function render() {
		$conf   = $this->conf;
		$misc   = $this->misc;
		$lang   = $this->lang;
		$data   = $misc->getDatabaseAccessor();
		$action = $this->action;

		$misc->printHeader($lang['strfunctions'], null, true, 'datatables_header.twig');
		$misc->printBody();

		switch ($action) {
			case 'save_create':
				if (isset($_POST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveCreate();
				}

				break;
			case 'create':
				$this->doCreate();
				break;
			case 'drop':
				if (isset($_POST['drop'])) {
					$this->doDrop(false);
				} else {
					$this->doDefault();
				}

				break;
			case 'confirm_drop':
				$this->doDrop(true);
				break;
			case 'save_edit':
				if (isset($_POST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveEdit();
				}

				break;
			case 'edit':
				$this->doEdit();
				break;
			case 'properties':
				$this->doProperties();
				break;
			default:
				$this->doDefault();
				break;
		}

		$misc->printFooter();

	}
}
