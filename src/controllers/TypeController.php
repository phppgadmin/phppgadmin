<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class TypeController extends BaseController {
	public $_name = 'TypeController';

/**
 * Show read only properties for a type
 */
	public function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();
		// Get type (using base name)
		$typedata = $data->getType($_REQUEST['type']);

		$this->printTrail('type');
		$misc->printTitle($lang['strproperties'], 'pg.type');
		$misc->printMsg($msg);

		$attPre = function (&$rowdata) use ($data) {
			$rowdata->fields['+type'] = $data->formatType($rowdata->fields['type'], $rowdata->fields['atttypmod']);
		};

		if ($typedata->recordCount() > 0) {
			$vals = false;
			switch ($typedata->fields['typtype']) {
				case 'c':
					$attrs = $data->getTableAttributes($_REQUEST['type']);

					$columns = [
						'field' => [
							'title' => $lang['strfield'],
							'field' => Decorator::field('attname'),
						],
						'type' => [
							'title' => $lang['strtype'],
							'field' => Decorator::field('+type'),
						],
						'comment' => [
							'title' => $lang['strcomment'],
							'field' => Decorator::field('comment'),
						],
					];

					$actions = [];

					echo $this->printTable($attrs, $columns, $actions, 'types-properties', null, $attPre);

					break;
				case 'e':
					$vals = $data->getEnumValues($typedata->fields['typname']);
				default:
					$byval = $data->phpBool($typedata->fields['typbyval']);
					echo "<table>\n";
					echo "<tr><th class=\"data left\">{$lang['strname']}</th>\n";
					echo "<td class=\"data1\">", $misc->printVal($typedata->fields['typname']), "</td></tr>\n";
					echo "<tr><th class=\"data left\">{$lang['strinputfn']}</th>\n";
					echo "<td class=\"data1\">", $misc->printVal($typedata->fields['typin']), "</td></tr>\n";
					echo "<tr><th class=\"data left\">{$lang['stroutputfn']}</th>\n";
					echo "<td class=\"data1\">", $misc->printVal($typedata->fields['typout']), "</td></tr>\n";
					echo "<tr><th class=\"data left\">{$lang['strlength']}</th>\n";
					echo "<td class=\"data1\">", $misc->printVal($typedata->fields['typlen']), "</td></tr>\n";
					echo "<tr><th class=\"data left\">{$lang['strpassbyval']}</th>\n";
					echo "<td class=\"data1\">", ($byval) ? $lang['stryes'] : $lang['strno'], "</td></tr>\n";
					echo "<tr><th class=\"data left\">{$lang['stralignment']}</th>\n";
					echo "<td class=\"data1\">", $misc->printVal($typedata->fields['typalign']), "</td></tr>\n";
					if ($data->hasEnumTypes() && $vals) {
						$vals   = $vals->getArray();
						$nbVals = count($vals);
						echo "<tr>\n\t<th class=\"data left\" rowspan=\"$nbVals\">{$lang['strenumvalues']}</th>\n";
						echo "<td class=\"data2\">{$vals[0]['enumval']}</td></tr>\n";
						for ($i = 1; $i < $nbVals; $i++) {
							echo "<td class=\"data", 2 - ($i % 2), "\">{$vals[$i]['enumval']}</td></tr>\n";
						}

					}
					echo "</table>\n";
			}

			$misc->printNavLinks(['showall' => [
				'attr' => [
					'href' => [
						'url' => 'types.php',
						'urlvars' => [
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strshowalltypes'],
			]], 'types-properties', get_defined_vars());
		} else {
			$this->doDefault($lang['strinvalidparam']);
		}

	}

/**
 * Show confirmation of drop and perform actual drop
 */
	public function doDrop($confirm) {
		global $data, $misc;
		global $lang;

		if ($confirm) {
			$this->printTrail('type');
			$misc->printTitle($lang['strdrop'], 'pg.type.drop');

			echo "<p>", sprintf($lang['strconfdroptype'], $misc->printVal($_REQUEST['type'])), "</p>\n";

			echo "<form action=\"/src/views/types.php\" method=\"post\">\n";
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"type\" value=\"", htmlspecialchars($_REQUEST['type']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			$status = $data->dropType($_POST['type'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doDefault($lang['strtypedropped']);
			} else {
				$this->doDefault($lang['strtypedroppedbad']);
			}

		}

	}

/**
 * Displays a screen where they can enter a new composite type
 */
	public function doCreateComposite($msg = '') {
		global $data, $misc;
		global $lang;

		if (!isset($_REQUEST['stage'])) {
			$_REQUEST['stage'] = 1;
		}

		if (!isset($_REQUEST['name'])) {
			$_REQUEST['name'] = '';
		}

		if (!isset($_REQUEST['fields'])) {
			$_REQUEST['fields'] = '';
		}

		if (!isset($_REQUEST['typcomment'])) {
			$_REQUEST['typcomment'] = '';
		}

		switch ($_REQUEST['stage']) {
			case 1:
				$this->printTrail('type');
				$misc->printTitle($lang['strcreatecomptype'], 'pg.type.create');
				$misc->printMsg($msg);

				echo "<form action=\"/src/views/types.php\" method=\"post\">\n";
				echo "<table>\n";
				echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
				echo "\t\t<td class=\"data\"><input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
				htmlspecialchars($_REQUEST['name']), "\" /></td>\n\t</tr>\n";
				echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strnumfields']}</th>\n";
				echo "\t\t<td class=\"data\"><input name=\"fields\" size=\"5\" maxlength=\"{$data->_maxNameLen}\" value=\"",
				htmlspecialchars($_REQUEST['fields']), "\" /></td>\n\t</tr>\n";

				echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
				echo "\t\t<td><textarea name=\"typcomment\" rows=\"3\" cols=\"32\">",
				htmlspecialchars($_REQUEST['typcomment']), "</textarea></td>\n\t</tr>\n";

				echo "</table>\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"create_comp\" />\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"2\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" value=\"{$lang['strnext']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";
				break;
			case 2:
				global $lang;

				// Check inputs
				$fields = trim($_REQUEST['fields']);
				if (trim($_REQUEST['name']) == '') {
					$_REQUEST['stage'] = 1;
					$this->doCreateComposite($lang['strtypeneedsname']);
					return;
				} elseif ($fields == '' || !is_numeric($fields) || $fields != (int) $fields || $fields < 1) {
					$_REQUEST['stage'] = 1;
					$this->doCreateComposite($lang['strtypeneedscols']);
					return;
				}

				$types = $data->getTypes(true, false, true);

				$this->printTrail('schema');
				$misc->printTitle($lang['strcreatecomptype'], 'pg.type.create');
				$misc->printMsg($msg);

				echo "<form action=\"/src/views/types.php\" method=\"post\">\n";

				// Output table header
				echo "<table>\n";
				echo "\t<tr><th colspan=\"2\" class=\"data required\">{$lang['strfield']}</th><th colspan=\"2\" class=\"data required\">{$lang['strtype']}</th>";
				echo "<th class=\"data\">{$lang['strlength']}</th><th class=\"data\">{$lang['strcomment']}</th></tr>\n";

				for ($i = 0; $i < $_REQUEST['fields']; $i++) {
					if (!isset($_REQUEST['field'][$i])) {
						$_REQUEST['field'][$i] = '';
					}

					if (!isset($_REQUEST['length'][$i])) {
						$_REQUEST['length'][$i] = '';
					}

					if (!isset($_REQUEST['colcomment'][$i])) {
						$_REQUEST['colcomment'][$i] = '';
					}

					echo "\t<tr>\n\t\t<td>", $i + 1, ".&nbsp;</td>\n";
					echo "\t\t<td><input name=\"field[{$i}]\" size=\"16\" maxlength=\"{$data->_maxNameLen}\" value=\"",
					htmlspecialchars($_REQUEST['field'][$i]), "\" /></td>\n";
					echo "\t\t<td>\n\t\t\t<select name=\"type[{$i}]\">\n";
					$types->moveFirst();
					while (!$types->EOF) {
						$typname = $types->fields['typname'];
						echo "\t\t\t\t<option value=\"", htmlspecialchars($typname), "\"",
						(isset($_REQUEST['type'][$i]) && $typname == $_REQUEST['type'][$i]) ? ' selected="selected"' : '', ">",
						$misc->printVal($typname), "</option>\n";
						$types->moveNext();
					}
					echo "\t\t\t</select>\n\t\t</td>\n";

					// Output array type selector
					echo "\t\t<td>\n\t\t\t<select name=\"array[{$i}]\">\n";
					echo "\t\t\t\t<option value=\"\"", (isset($_REQUEST['array'][$i]) && $_REQUEST['array'][$i] == '') ? ' selected="selected"' : '', "></option>\n";
					echo "\t\t\t\t<option value=\"[]\"", (isset($_REQUEST['array'][$i]) && $_REQUEST['array'][$i] == '[]') ? ' selected="selected"' : '', ">[ ]</option>\n";
					echo "\t\t\t</select>\n\t\t</td>\n";

					echo "\t\t<td><input name=\"length[{$i}]\" size=\"10\" value=\"",
					htmlspecialchars($_REQUEST['length'][$i]), "\" /></td>\n";
					echo "\t\t<td><input name=\"colcomment[{$i}]\" size=\"40\" value=\"",
					htmlspecialchars($_REQUEST['colcomment'][$i]), "\" /></td>\n\t</tr>\n";
				}
				echo "</table>\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"create_comp\" />\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"3\" />\n";
				echo $misc->form;
				echo "<input type=\"hidden\" name=\"name\" value=\"", htmlspecialchars($_REQUEST['name']), "\" />\n";
				echo "<input type=\"hidden\" name=\"fields\" value=\"", htmlspecialchars($_REQUEST['fields']), "\" />\n";
				echo "<input type=\"hidden\" name=\"typcomment\" value=\"", htmlspecialchars($_REQUEST['typcomment']), "\" />\n";
				echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";

				break;
			case 3:
				global $data, $lang;

				// Check inputs
				$fields = trim($_REQUEST['fields']);
				if (trim($_REQUEST['name']) == '') {
					$_REQUEST['stage'] = 1;
					$this->doCreateComposite($lang['strtypeneedsname']);
					return;
				} elseif ($fields == '' || !is_numeric($fields) || $fields != (int) $fields || $fields <= 0) {
					$_REQUEST['stage'] = 1;
					$this->doCreateComposite($lang['strtypeneedscols']);
					return;
				}

				$status = $data->createCompositeType($_REQUEST['name'], $_REQUEST['fields'], $_REQUEST['field'],
					$_REQUEST['type'], $_REQUEST['array'], $_REQUEST['length'], $_REQUEST['colcomment'],
					$_REQUEST['typcomment']);

				if ($status == 0) {
					$this->doDefault($lang['strtypecreated']);
				} elseif ($status == -1) {
					$_REQUEST['stage'] = 2;
					$this->doCreateComposite($lang['strtypeneedsfield']);
					return;
				} else {
					$_REQUEST['stage'] = 2;
					$this->doCreateComposite($lang['strtypecreatedbad']);
					return;
				}
				break;
			default:
				echo "<p>{$lang['strinvalidparam']}</p>\n";
		}
	}

/**
 * Displays a screen where they can enter a new enum type
 */
	public function doCreateEnum($msg = '') {
		global $data, $misc;
		global $lang;

		if (!isset($_REQUEST['stage'])) {
			$_REQUEST['stage'] = 1;
		}

		if (!isset($_REQUEST['name'])) {
			$_REQUEST['name'] = '';
		}

		if (!isset($_REQUEST['values'])) {
			$_REQUEST['values'] = '';
		}

		if (!isset($_REQUEST['typcomment'])) {
			$_REQUEST['typcomment'] = '';
		}

		switch ($_REQUEST['stage']) {
			case 1:
				$this->printTrail('type');
				$misc->printTitle($lang['strcreateenumtype'], 'pg.type.create');
				$misc->printMsg($msg);

				echo "<form action=\"/src/views/types.php\" method=\"post\">\n";
				echo "<table>\n";
				echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
				echo "\t\t<td class=\"data\"><input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
				htmlspecialchars($_REQUEST['name']), "\" /></td>\n\t</tr>\n";
				echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strnumvalues']}</th>\n";
				echo "\t\t<td class=\"data\"><input name=\"values\" size=\"5\" maxlength=\"{$data->_maxNameLen}\" value=\"",
				htmlspecialchars($_REQUEST['values']), "\" /></td>\n\t</tr>\n";

				echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
				echo "\t\t<td><textarea name=\"typcomment\" rows=\"3\" cols=\"32\">",
				htmlspecialchars($_REQUEST['typcomment']), "</textarea></td>\n\t</tr>\n";

				echo "</table>\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"create_enum\" />\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"2\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" value=\"{$lang['strnext']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";
				break;
			case 2:
				global $lang;

				// Check inputs
				$values = trim($_REQUEST['values']);
				if (trim($_REQUEST['name']) == '') {
					$_REQUEST['stage'] = 1;
					$this->doCreateEnum($lang['strtypeneedsname']);
					return;
				} elseif ($values == '' || !is_numeric($values) || $values != (int) $values || $values < 1) {
					$_REQUEST['stage'] = 1;
					$this->doCreateEnum($lang['strtypeneedsvals']);
					return;
				}

				$this->printTrail('schema');
				$misc->printTitle($lang['strcreateenumtype'], 'pg.type.create');
				$misc->printMsg($msg);

				echo "<form action=\"/src/views/types.php\" method=\"post\">\n";

				// Output table header
				echo "<table>\n";
				echo "\t<tr><th colspan=\"2\" class=\"data required\">{$lang['strvalue']}</th></tr>\n";

				for ($i = 0; $i < $_REQUEST['values']; $i++) {
					if (!isset($_REQUEST['value'][$i])) {
						$_REQUEST['value'][$i] = '';
					}

					echo "\t<tr>\n\t\t<td>", $i + 1, ".&nbsp;</td>\n";
					echo "\t\t<td><input name=\"value[{$i}]\" size=\"16\" maxlength=\"{$data->_maxNameLen}\" value=\"",
					htmlspecialchars($_REQUEST['value'][$i]), "\" /></td>\n\t</tr>\n";
				}
				echo "</table>\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"create_enum\" />\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"3\" />\n";
				echo $misc->form;
				echo "<input type=\"hidden\" name=\"name\" value=\"", htmlspecialchars($_REQUEST['name']), "\" />\n";
				echo "<input type=\"hidden\" name=\"values\" value=\"", htmlspecialchars($_REQUEST['values']), "\" />\n";
				echo "<input type=\"hidden\" name=\"typcomment\" value=\"", htmlspecialchars($_REQUEST['typcomment']), "\" />\n";
				echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";

				break;
			case 3:
				global $data, $lang;

				// Check inputs
				$values = trim($_REQUEST['values']);
				if (trim($_REQUEST['name']) == '') {
					$_REQUEST['stage'] = 1;
					$this->doCreateEnum($lang['strtypeneedsname']);
					return;
				} elseif ($values == '' || !is_numeric($values) || $values != (int) $values || $values <= 0) {
					$_REQUEST['stage'] = 1;
					$this->doCreateEnum($lang['strtypeneedsvals']);
					return;
				}

				$status = $data->createEnumType($_REQUEST['name'], $_REQUEST['value'], $_REQUEST['typcomment']);

				if ($status == 0) {
					$this->doDefault($lang['strtypecreated']);
				} elseif ($status == -1) {
					$_REQUEST['stage'] = 2;
					$this->doCreateEnum($lang['strtypeneedsvalue']);
					return;
				} else {
					$_REQUEST['stage'] = 2;
					$this->doCreateEnum($lang['strtypecreatedbad']);
					return;
				}
				break;
			default:
				echo "<p>{$lang['strinvalidparam']}</p>\n";
		}
	}

/**
 * Displays a screen where they can enter a new type
 */
	public function doCreate($msg = '') {
		global $data, $misc;
		global $lang;

		if (!isset($_POST['typname'])) {
			$_POST['typname'] = '';
		}

		if (!isset($_POST['typin'])) {
			$_POST['typin'] = '';
		}

		if (!isset($_POST['typout'])) {
			$_POST['typout'] = '';
		}

		if (!isset($_POST['typlen'])) {
			$_POST['typlen'] = '';
		}

		if (!isset($_POST['typdef'])) {
			$_POST['typdef'] = '';
		}

		if (!isset($_POST['typelem'])) {
			$_POST['typelem'] = '';
		}

		if (!isset($_POST['typdelim'])) {
			$_POST['typdelim'] = '';
		}

		if (!isset($_POST['typalign'])) {
			$_POST['typalign'] = $data->typAlignDef;
		}

		if (!isset($_POST['typstorage'])) {
			$_POST['typstorage'] = $data->typStorageDef;
		}

		// Retrieve all functions and types in the database
		$funcs = $data->getFunctions(true);
		$types = $data->getTypes(true);

		$this->printTrail('schema');
		$misc->printTitle($lang['strcreatetype'], 'pg.type.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/types.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "<td class=\"data1\"><input name=\"typname\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['typname']), "\" /></td></tr>\n";
		echo "<tr><th class=\"data left required\">{$lang['strinputfn']}</th>\n";
		echo "<td class=\"data1\"><select name=\"typin\">";
		while (!$funcs->EOF) {
			$proname = htmlspecialchars($funcs->fields['proname']);
			echo "<option value=\"{$proname}\"",
			($proname == $_POST['typin']) ? ' selected="selected"' : '', ">{$proname}</option>\n";
			$funcs->moveNext();
		}
		echo "</select></td></tr>\n";
		echo "<tr><th class=\"data left required\">{$lang['stroutputfn']}</th>\n";
		echo "<td class=\"data1\"><select name=\"typout\">";
		$funcs->moveFirst();
		while (!$funcs->EOF) {
			$proname = htmlspecialchars($funcs->fields['proname']);
			echo "<option value=\"{$proname}\"",
			($proname == $_POST['typout']) ? ' selected="selected"' : '', ">{$proname}</option>\n";
			$funcs->moveNext();
		}
		echo "</select></td></tr>\n";
		echo "<tr><th class=\"data left" . (version_compare($data->major_version, '7.4', '<') ? ' required' : '') . "\">{$lang['strlength']}</th>\n";
		echo "<td class=\"data1\"><input name=\"typlen\" size=\"8\" value=\"",
		htmlspecialchars($_POST['typlen']), "\" /></td></tr>";
		echo "<tr><th class=\"data left\">{$lang['strdefault']}</th>\n";
		echo "<td class=\"data1\"><input name=\"typdef\" size=\"8\" value=\"",
		htmlspecialchars($_POST['typdef']), "\" /></td></tr>";
		echo "<tr><th class=\"data left\">{$lang['strelement']}</th>\n";
		echo "<td class=\"data1\"><select name=\"typelem\">";
		echo "<option value=\"\"></option>\n";
		while (!$types->EOF) {
			$currname = htmlspecialchars($types->fields['typname']);
			echo "<option value=\"{$currname}\"",
			($currname == $_POST['typelem']) ? ' selected="selected"' : '', ">{$currname}</option>\n";
			$types->moveNext();
		}
		echo "</select></td></tr>\n";
		echo "<tr><th class=\"data left\">{$lang['strdelimiter']}</th>\n";
		echo "<td class=\"data1\"><input name=\"typdelim\" size=\"1\" maxlength=\"1\" value=\"",
		htmlspecialchars($_POST['typdelim']), "\" /></td></tr>";
		echo "<tr><th class=\"data left\"><label for=\"typbyval\">{$lang['strpassbyval']}</label></th>\n";
		echo "<td class=\"data1\"><input type=\"checkbox\" id=\"typbyval\" name=\"typbyval\"",
		isset($_POST['typbyval']) ? ' checked="checked"' : '', " /></td></tr>";
		echo "<tr><th class=\"data left\">{$lang['stralignment']}</th>\n";
		echo "<td class=\"data1\"><select name=\"typalign\">";
		foreach ($data->typAligns as $v) {
			echo "<option value=\"{$v}\"",
			($v == $_POST['typalign']) ? ' selected="selected"' : '', ">{$v}</option>\n";
		}
		echo "</select></td></tr>\n";
		echo "<tr><th class=\"data left\">{$lang['strstorage']}</th>\n";
		echo "<td class=\"data1\"><select name=\"typstorage\">";
		foreach ($data->typStorages as $v) {
			echo "<option value=\"{$v}\"",
			($v == $_POST['typstorage']) ? ' selected="selected"' : '', ">{$v}</option>\n";
		}
		echo "</select></td></tr>\n";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new type in the database
 */
	public function doSaveCreate() {
		global $data;
		global $lang;

		// Check that they've given a name and a length.
		// Note: We're assuming they've given in and out functions here
		// which might be unwise...
		if ($_POST['typname'] == '') {
			$this->doCreate($lang['strtypeneedsname']);
		} elseif ($_POST['typlen'] == '') {
			$this->doCreate($lang['strtypeneedslen']);
		} else {
			$status = $data->createType(
				$_POST['typname'],
				$_POST['typin'],
				$_POST['typout'],
				$_POST['typlen'],
				$_POST['typdef'],
				$_POST['typelem'],
				$_POST['typdelim'],
				isset($_POST['typbyval']),
				$_POST['typalign'],
				$_POST['typstorage']
			);
			if ($status == 0) {
				$this->doDefault($lang['strtypecreated']);
			} else {
				$this->doCreate($lang['strtypecreatedbad']);
			}

		}
	}

/**
 * Show default list of types in the database
 */
	public function doDefault($msg = '') {
		global $data, $conf, $misc;
		global $lang;

		$this->printTrail('schema');
		$this->printTabs('schema', 'types');
		$misc->printMsg($msg);

		$types = $data->getTypes();

		$columns = [
			'type' => [
				'title' => $lang['strtype'],
				'field' => Decorator::field('typname'),
				'url' => "types.php?action=properties&amp;{$misc->href}&amp;",
				'vars' => ['type' => 'basename'],
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('typowner'),
			],
			'flavour' => [
				'title' => $lang['strflavor'],
				'field' => Decorator::field('typtype'),
				'type' => 'verbatim',
				'params' => [
					'map' => [
						'b' => $lang['strbasetype'],
						'c' => $lang['strcompositetype'],
						'd' => $lang['strdomain'],
						'p' => $lang['strpseudotype'],
						'e' => $lang['strenum'],
					],
					'align' => 'center',
				],
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('typcomment'),
			],
		];

		if (!isset($types->fields['typtype'])) {
			unset($columns['flavour']);
		}

		$actions = [
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'types.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'type' => Decorator::field('basename'),
						],
					],
				],
			],
		];

		echo $this->printTable($types, $columns, $actions, 'types-types', $lang['strnotypes']);

		$navlinks = [
			'create' => [
				'attr' => [
					'href' => [
						'url' => 'types.php',
						'urlvars' => [
							'action' => 'create',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreatetype'],
			],
			'createcomp' => [
				'attr' => [
					'href' => [
						'url' => 'types.php',
						'urlvars' => [
							'action' => 'create_comp',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreatecomptype'],
			],
			'createenum' => [
				'attr' => [
					'href' => [
						'url' => 'types.php',
						'urlvars' => [
							'action' => 'create_enum',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreateenumtype'],
			],
		];

		if (!$data->hasEnumTypes()) {
			unset($navlinks['enum']);
		}

		$misc->printNavLinks($navlinks, 'types-types', get_defined_vars());
	}

}
