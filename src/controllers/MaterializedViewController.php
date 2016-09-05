<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class MaterializedViewController extends BaseController {
	public $script      = 'materialized_materialized_views.php';
	public $_name       = 'MaterializedViewController';
	public $table_place = 'matviews-matviews';
	/**
	 * Ask for select parameters and perform select
	 */
	public function doSelectRows($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('view');
			$this->printTabs('view', 'select');
			$misc->printMsg($msg);

			$attrs = $data->getTableAttributes($_REQUEST['view']);

			echo '<form action="/src/views/' . $this->script . '" method="post" id="selectform">';
			echo "\n";

			if ($attrs->recordCount() > 0) {
				// JavaScript for select all feature
				echo "<script type=\"text/javascript\">\n";
				echo "//<![CDATA[\n";
				echo "	function selectAll() {\n";
				echo "		for (var i=0; i<document.getElementById('selectform').elements.length; i++) {\n";
				echo "			var e = document.getElementById('selectform').elements[i];\n";
				echo "			if (e.name.indexOf('show') == 0) { \n ";
				echo "				e.checked = document.getElementById('selectform').selectall.checked;\n";
				echo "			}\n";
				echo "		}\n";
				echo "	}\n";
				echo "//]]>\n";
				echo "</script>\n";

				echo "<table>\n";

				// Output table header
				echo "<tr><th class=\"data\">{$lang['strshow']}</th><th class=\"data\">{$lang['strcolumn']}</th>";
				echo "<th class=\"data\">{$lang['strtype']}</th><th class=\"data\">{$lang['stroperator']}</th>";
				echo "<th class=\"data\">{$lang['strvalue']}</th></tr>";

				$i = 0;
				while (!$attrs->EOF) {
					$attrs->fields['attnotnull'] = $data->phpBool($attrs->fields['attnotnull']);
					// Set up default value if there isn't one already
					if (!isset($_REQUEST['values'][$attrs->fields['attname']])) {
						$_REQUEST['values'][$attrs->fields['attname']] = null;
					}

					if (!isset($_REQUEST['ops'][$attrs->fields['attname']])) {
						$_REQUEST['ops'][$attrs->fields['attname']] = null;
					}

					// Continue drawing row
					$id = (($i % 2) == 0 ? '1' : '2');
					echo "<tr class=\"data{$id}\">\n";
					echo "<td style=\"white-space:nowrap;\">";
					echo "<input type=\"checkbox\" name=\"show[", htmlspecialchars($attrs->fields['attname']), "]\"",
					isset($_REQUEST['show'][$attrs->fields['attname']]) ? ' checked="checked"' : '', " /></td>";
					echo "<td style=\"white-space:nowrap;\">", $misc->printVal($attrs->fields['attname']), "</td>";
					echo "<td style=\"white-space:nowrap;\">", $misc->printVal($data->formatType($attrs->fields['type'], $attrs->fields['atttypmod'])), "</td>";
					echo "<td style=\"white-space:nowrap;\">";
					echo "<select name=\"ops[{$attrs->fields['attname']}]\">\n";
					foreach (array_keys($data->selectOps) as $v) {
						echo "<option value=\"", htmlspecialchars($v), "\"", ($v == $_REQUEST['ops'][$attrs->fields['attname']]) ? ' selected="selected"' : '',
						">", htmlspecialchars($v), "</option>\n";
					}
					echo "</select></td>\n";
					echo "<td style=\"white-space:nowrap;\">", $data->printField("values[{$attrs->fields['attname']}]",
						$_REQUEST['values'][$attrs->fields['attname']], $attrs->fields['type']), "</td>";
					echo "</tr>\n";
					$i++;
					$attrs->moveNext();
				}
				// Select all checkbox
				echo "<tr><td colspan=\"5\"><input type=\"checkbox\" id=\"selectall\" name=\"selectall\" accesskey=\"a\" onclick=\"javascript:selectAll()\" /><label for=\"selectall\">{$lang['strselectallfields']}</label></td></tr>";
				echo "</table>\n";
			} else {
				echo "<p>{$lang['strinvalidparam']}</p>\n";
			}

			echo "<p><input type=\"hidden\" name=\"action\" value=\"selectrows\" />\n";
			echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"view\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"select\" accesskey=\"r\" value=\"{$lang['strselect']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
			return;
		} else {
			if (!isset($_POST['show'])) {
				$_POST['show'] = [];
			}

			if (!isset($_POST['values'])) {
				$_POST['values'] = [];
			}

			if (!isset($_POST['nulls'])) {
				$_POST['nulls'] = [];
			}

			// Verify that they haven't supplied a value for unary operators
			foreach ($_POST['ops'] as $k => $v) {
				if ($data->selectOps[$v] == 'p' && $_POST['values'][$k] != '') {
					$this->doSelectRows(true, $lang['strselectunary']);
					return;
				}
			}

			if (sizeof($_POST['show']) == 0) {
				return $this->doSelectRows(true, $lang['strselectneedscol']);
			} else {
				// Generate query SQL
				$query = $data->getSelectSQL($_REQUEST['view'], array_keys($_POST['show']), $_POST['values'], $_POST['ops']);

				$_REQUEST['query']  = $query;
				$_REQUEST['return'] = "schema";

				$misc->setNoOutput(true);

				$display_controller = new DisplayController($this->getContainer());

				return $display_controller->render();
			}
		}

	}

	/**
	 * Show confirmation of drop and perform actual drop
	 */
	public function doDrop($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['view']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifyviewtodrop']);
			exit();
		}

		if ($confirm) {
			$this->printTrail('view');
			$misc->printTitle($lang['strdrop'], 'pg.view.drop');

			echo "<form action=\"/src/views/materialized_views.php\" method=\"post\">\n";

			//If multi drop
			if (isset($_REQUEST['ma'])) {
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfdropview'], $misc->printVal($a['view'])), "</p>\n";
					echo '<input type="hidden" name="view[]" value="', htmlspecialchars($a['view']), "\" />\n";
				}
			} else {
				echo "<p>", sprintf($lang['strconfdropview'], $misc->printVal($_REQUEST['view'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
			}

			echo "<input type=\"hidden\" name=\"action\" value=\"drop\" />\n";

			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			if (is_array($_POST['view'])) {
				$msg    = '';
				$status = $data->beginTransaction();
				if ($status == 0) {
					foreach ($_POST['view'] as $s) {
						$status = $data->dropView($s, isset($_POST['cascade']));
						if ($status == 0) {
							$msg .= sprintf('%s: %s<br />', htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strviewdropped']);
						} else {
							$data->endTransaction();
							$this->doDefault(sprintf('%s%s: %s<br />', $msg, htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strviewdroppedbad']));
							return;
						}
					}
				}
				if ($data->endTransaction() == 0) {
					// Everything went fine, back to the Default page....
					$this->misc->setReloadBrowser(true);
					$this->doDefault($msg);
				} else {
					$this->doDefault($lang['strviewdroppedbad']);
				}

			} else {
				$status = $data->dropView($_POST['view'], isset($_POST['cascade']));
				if ($status == 0) {
					$this->misc->setReloadBrowser(true);
					$this->doDefault($lang['strviewdropped']);
				} else {
					$this->doDefault($lang['strviewdroppedbad']);
				}

			}
		}

	}

	/**
	 * Sets up choices for table linkage, and which fields to select for the view we're creating
	 */
	public function doSetParamsCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've chosen tables for the view definition
		if (!isset($_POST['formTables'])) {
			$this->doWizardCreate($lang['strviewneedsdef']);
		} else {
			// Initialise variables
			if (!isset($_REQUEST['formView'])) {
				$_REQUEST['formView'] = '';
			}

			if (!isset($_REQUEST['formComment'])) {
				$_REQUEST['formComment'] = '';
			}

			$this->printTrail('schema');
			$misc->printTitle($lang['strcreateviewwiz'], 'pg.view.create');
			$misc->printMsg($msg);

			$tblCount = sizeof($_POST['formTables']);
			//unserialize our schema/table information and store in arrSelTables
			for ($i = 0; $i < $tblCount; $i++) {
				$arrSelTables[] = unserialize($_POST['formTables'][$i]);
			}

			$linkCount = $tblCount;

			//get linking keys
			$rsLinkKeys = $data->getLinkingKeys($arrSelTables);
			$linkCount  = $rsLinkKeys->recordCount() > $tblCount ? $rsLinkKeys->recordCount() : $tblCount;

			$arrFields = []; //array that will hold all our table/field names

			//if we have schemas we need to specify the correct schema for each table we're retrieiving
			//with getTableAttributes
			$curSchema = $data->_schema;
			for ($i = 0; $i < $tblCount; $i++) {
				if ($data->_schema != $arrSelTables[$i]['schemaname']) {
					$data->setSchema($arrSelTables[$i]['schemaname']);
				}

				$attrs = $data->getTableAttributes($arrSelTables[$i]['tablename']);
				while (!$attrs->EOF) {
					$arrFields["{$arrSelTables[$i]['schemaname']}.{$arrSelTables[$i]['tablename']}.{$attrs->fields['attname']}"] = serialize([
						'schemaname' => $arrSelTables[$i]['schemaname'],
						'tablename' => $arrSelTables[$i]['tablename'],
						'fieldname' => $attrs->fields['attname']]
					);
					$attrs->moveNext();
				}

				$data->setSchema($curSchema);
			}
			asort($arrFields);

			echo "<form action=\"/src/views/materialized_views.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strviewname']}</th></tr>";
			echo "<tr>\n<td class=\"data1\">\n";
			// View name
			echo "<input name=\"formView\" value=\"", htmlspecialchars($_REQUEST['formView']), "\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" />\n";
			echo "</td>\n</tr>\n";
			echo "<tr><th class=\"data\">{$lang['strcomment']}</th></tr>";
			echo "<tr>\n<td class=\"data1\">\n";
			// View comments
			echo "<textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
			htmlspecialchars($_REQUEST['formComment']), "</textarea>\n";
			echo "</td>\n</tr>\n";
			echo "</table>\n";

			// Output selector for fields to be retrieved from view
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strcolumns']}</th></tr>";
			echo "<tr>\n<td class=\"data1\">\n";
			echo \PHPPgAdmin\GUI::printCombo($arrFields, 'formFields[]', false, '', true);
			echo "</td>\n</tr>";
			echo "<tr><td><input type=\"radio\" name=\"dblFldMeth\" id=\"dblFldMeth1\" value=\"rename\" /><label for=\"dblFldMeth1\">{$lang['strrenamedupfields']}</label>";
			echo "<br /><input type=\"radio\" name=\"dblFldMeth\" id=\"dblFldMeth2\" value=\"drop\" /><label for=\"dblFldMeth2\">{$lang['strdropdupfields']}</label>";
			echo "<br /><input type=\"radio\" name=\"dblFldMeth\" id=\"dblFldMeth3\" value=\"\" checked=\"checked\" /><label for=\"dblFldMeth3\">{$lang['strerrordupfields']}</label></td></tr></table><br />";

			// Output the Linking keys combo boxes
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strviewlink']}</th></tr>";
			$rowClass = 'data1';
			for ($i = 0; $i < $linkCount; $i++) {
				// Initialise variables
				if (!isset($formLink[$i]['operator'])) {
					$formLink[$i]['operator'] = 'INNER JOIN';
				}

				echo "<tr>\n<td class=\"$rowClass\">\n";

				if (!$rsLinkKeys->EOF) {
					$curLeftLink  = htmlspecialchars(serialize(['schemaname' => $rsLinkKeys->fields['p_schema'], 'tablename' => $rsLinkKeys->fields['p_table'], 'fieldname' => $rsLinkKeys->fields['p_field']]));
					$curRightLink = htmlspecialchars(serialize(['schemaname' => $rsLinkKeys->fields['f_schema'], 'tablename' => $rsLinkKeys->fields['f_table'], 'fieldname' => $rsLinkKeys->fields['f_field']]));
					$rsLinkKeys->moveNext();
				} else {
					$curLeftLink  = '';
					$curRightLink = '';
				}

				echo \PHPPgAdmin\GUI::printCombo($arrFields, "formLink[$i][leftlink]", true, $curLeftLink, false);
				echo \PHPPgAdmin\GUI::printCombo($data->joinOps, "formLink[$i][operator]", true, $formLink[$i]['operator']);
				echo \PHPPgAdmin\GUI::printCombo($arrFields, "formLink[$i][rightlink]", true, $curRightLink, false);
				echo "</td>\n</tr>\n";
				$rowClass = $rowClass == 'data1' ? 'data2' : 'data1';
			}
			echo "</table>\n<br />\n";

			// Build list of available operators (infix only)
			$arrOperators = [];
			foreach ($data->selectOps as $k => $v) {
				if ($v == 'i') {
					$arrOperators[$k] = $k;
				}

			}

			// Output additional conditions, note that this portion of the wizard treats the right hand side as literal values
			//(not as database objects) so field names will be treated as strings, use the above linking keys section to perform joins
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strviewconditions']}</th></tr>";
			$rowClass = 'data1';
			for ($i = 0; $i < $linkCount; $i++) {
				echo "<tr>\n<td class=\"$rowClass\">\n";
				echo \PHPPgAdmin\GUI::printCombo($arrFields, "formCondition[$i][field]");
				echo \PHPPgAdmin\GUI::printCombo($arrOperators, "formCondition[$i][operator]", false, false);
				echo "<input type=\"text\" name=\"formCondition[$i][txt]\" />\n";
				echo "</td>\n</tr>\n";
				$rowClass = $rowClass == 'data1' ? 'data2' : 'data1';
			}
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create_wiz\" />\n";

			foreach ($arrSelTables as $curTable) {
				echo "<input type=\"hidden\" name=\"formTables[]\" value=\"" . htmlspecialchars(serialize($curTable)) . "\" />\n";
			}

			echo $misc->form;
			echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}
	}

	/**
	 * Display a wizard where they can enter a new view
	 */
	public function doWizardCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$tables = $data->getTables(true);

		$this->printTrail('schema');
		$misc->printTitle($lang['strcreateviewwiz'], 'pg.view.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/materialized_views.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data\">{$lang['strtables']}</th></tr>";
		echo "<tr>\n<td class=\"data1\">\n";

		$arrTables = [];
		while (!$tables->EOF) {
			$arrTmp                                                                   = [];
			$arrTmp['schemaname']                                                     = $tables->fields['nspname'];
			$arrTmp['tablename']                                                      = $tables->fields['relname'];
			$arrTables[$tables->fields['nspname'] . '.' . $tables->fields['relname']] = serialize($arrTmp);
			$tables->moveNext();
		}
		echo \PHPPgAdmin\GUI::printCombo($arrTables, 'formTables[]', false, '', true);

		echo "</td>\n</tr>\n";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"set_params_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strnext']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Displays a screen where they can enter a new view
	 */
	public function doCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_REQUEST['formView'])) {
			$_REQUEST['formView'] = '';
		}

		if (!isset($_REQUEST['formDefinition'])) {
			if (isset($_SESSION['sqlquery'])) {
				$_REQUEST['formDefinition'] = $_SESSION['sqlquery'];
			} else {
				$_REQUEST['formDefinition'] = 'SELECT ';
			}

		}
		if (!isset($_REQUEST['formComment'])) {
			$_REQUEST['formComment'] = '';
		}

		$this->printTrail('schema');
		$misc->printTitle($lang['strcreateview'], 'pg.view.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/materialized_views.php\" method=\"post\">\n";
		echo "<table style=\"width: 100%\">\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "\t<td class=\"data1\"><input name=\"formView\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_REQUEST['formView']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strdefinition']}</th>\n";
		echo "\t<td class=\"data1\"><textarea style=\"width:100%;\" rows=\"10\" cols=\"50\" name=\"formDefinition\">",
		htmlspecialchars($_REQUEST['formDefinition']), "</textarea></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "\t\t<td class=\"data1\"><textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
		htmlspecialchars($_REQUEST['formComment']), "</textarea></td>\n\t</tr>\n";
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Actually creates the new view in the database
	 */
	public function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name and a definition
		if ($_POST['formView'] == '') {
			$this->doCreate($lang['strviewneedsname']);
		} elseif ($_POST['formDefinition'] == '') {
			$this->doCreate($lang['strviewneedsdef']);
		} else {
			$status = $data->createView($_POST['formView'], $_POST['formDefinition'], false, $_POST['formComment']);
			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doDefault($lang['strviewcreated']);
			} else {
				$this->doCreate($lang['strviewcreatedbad']);
			}

		}
	}

	/**
	 * Actually creates the new wizard view in the database
	 */
	public function doSaveCreateWiz() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name and fields they want to select

		if (!strlen($_POST['formView'])) {
			$this->doSetParamsCreate($lang['strviewneedsname']);
		} else if (!isset($_POST['formFields']) || !count($_POST['formFields'])) {
			$this->doSetParamsCreate($lang['strviewneedsfields']);
		} else {
			$selFields = '';

			if (!empty($_POST['dblFldMeth'])) {
				$tmpHsh = [];
			}

			foreach ($_POST['formFields'] as $curField) {
				$arrTmp = unserialize($curField);
				$data->fieldArrayClean($arrTmp);
				if (!empty($_POST['dblFldMeth'])) {
					// doublon control
					if (empty($tmpHsh[$arrTmp['fieldname']])) {
						// field does not exist
						$selFields .= "\"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\".\"{$arrTmp['fieldname']}\", ";
						$tmpHsh[$arrTmp['fieldname']] = 1;
					} else if ($_POST['dblFldMeth'] == 'rename') {
						// field exist and must be renamed
						$tmpHsh[$arrTmp['fieldname']]++;
						$selFields .= "\"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\".\"{$arrTmp['fieldname']}\" AS \"{$arrTmp['schemaname']}_{$arrTmp['tablename']}_{$arrTmp['fieldname']}{$tmpHsh[$arrTmp['fieldname']]}\", ";
					}
					/* field already exist, just ignore this one */
				} else {
					// no doublon control
					$selFields .= "\"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\".\"{$arrTmp['fieldname']}\", ";
				}
			}

			$selFields = substr($selFields, 0, -2);
			unset($arrTmp, $tmpHsh);
			$linkFields = '';

			// If we have links, out put the JOIN ... ON statements
			if (is_array($_POST['formLink'])) {
				// Filter out invalid/blank entries for our links
				$arrLinks = [];
				foreach ($_POST['formLink'] as $curLink) {
					if (strlen($curLink['leftlink']) && strlen($curLink['rightlink']) && strlen($curLink['operator'])) {
						$arrLinks[] = $curLink;
					}
				}
				// We must perform some magic to make sure that we have a valid join order
				$count       = sizeof($arrLinks);
				$arrJoined   = [];
				$arrUsedTbls = [];

				// If we have at least one join condition, output it
				if ($count > 0) {
					$j = 0;
					while ($j < $count) {
						foreach ($arrLinks as $curLink) {

							$arrLeftLink  = unserialize($curLink['leftlink']);
							$arrRightLink = unserialize($curLink['rightlink']);
							$data->fieldArrayClean($arrLeftLink);
							$data->fieldArrayClean($arrRightLink);

							$tbl1 = "\"{$arrLeftLink['schemaname']}\".\"{$arrLeftLink['tablename']}\"";
							$tbl2 = "\"{$arrRightLink['schemaname']}\".\"{$arrRightLink['tablename']}\"";

							if ((!in_array($curLink, $arrJoined) && in_array($tbl1, $arrUsedTbls)) || !count($arrJoined)) {

								// Make sure for multi-column foreign keys that we use a table alias tables joined to more than once
								// This can (and should be) more optimized for multi-column foreign keys
								$adj_tbl2 = in_array($tbl2, $arrUsedTbls) ? "$tbl2 AS alias_ppa_" . mktime() : $tbl2;

								$linkFields .= strlen($linkFields) ? "{$curLink['operator']} $adj_tbl2 ON (\"{$arrLeftLink['schemaname']}\".\"{$arrLeftLink['tablename']}\".\"{$arrLeftLink['fieldname']}\" = \"{$arrRightLink['schemaname']}\".\"{$arrRightLink['tablename']}\".\"{$arrRightLink['fieldname']}\") "
								: "$tbl1 {$curLink['operator']} $adj_tbl2 ON (\"{$arrLeftLink['schemaname']}\".\"{$arrLeftLink['tablename']}\".\"{$arrLeftLink['fieldname']}\" = \"{$arrRightLink['schemaname']}\".\"{$arrRightLink['tablename']}\".\"{$arrRightLink['fieldname']}\") ";

								$arrJoined[] = $curLink;
								if (!in_array($tbl1, $arrUsedTbls)) {
									$arrUsedTbls[] = $tbl1;
								}

								if (!in_array($tbl2, $arrUsedTbls)) {
									$arrUsedTbls[] = $tbl2;
								}

							}
						}
						$j++;
					}
				}
			}

			//if linkfields has no length then either _POST['formLink'] was not set, or there were no join conditions
			//just select from all seleted tables - a cartesian join do a
			if (!strlen($linkFields)) {
				foreach ($_POST['formTables'] as $curTable) {
					$arrTmp = unserialize($curTable);
					$data->fieldArrayClean($arrTmp);
					$linkFields .= strlen($linkFields) ? ", \"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\"" : "\"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\"";
				}
			}

			$addConditions = '';
			if (is_array($_POST['formCondition'])) {
				foreach ($_POST['formCondition'] as $curCondition) {
					if (strlen($curCondition['field']) && strlen($curCondition['txt'])) {
						$arrTmp = unserialize($curCondition['field']);
						$data->fieldArrayClean($arrTmp);
						$addConditions .= strlen($addConditions) ? " AND \"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\".\"{$arrTmp['fieldname']}\" {$curCondition['operator']} '{$curCondition['txt']}' "
						: " \"{$arrTmp['schemaname']}\".\"{$arrTmp['tablename']}\".\"{$arrTmp['fieldname']}\" {$curCondition['operator']} '{$curCondition['txt']}' ";
					}
				}
			}

			$viewQuery = "SELECT $selFields FROM $linkFields ";

			//add where from additional conditions
			if (strlen($addConditions)) {
				$viewQuery .= ' WHERE ' . $addConditions;
			}

			$status = $data->createView($_POST['formView'], $viewQuery, false, $_POST['formComment']);
			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doDefault($lang['strviewcreated']);
			} else {
				$this->doSetParamsCreate($lang['strviewcreatedbad']);
			}

		}
	}

	/**
	 * Show default list of views in the database
	 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'matviews');
		$misc->printMsg($msg);

		//$matviews = $data->getViews();
		$matviews = $data->getMaterializedViews();

		$columns = [
			'matview' => [
				'title' => 'M ' . $lang['strview'],
				'field' => Decorator::field('relname'),
				'url' => "/redirect/matview?{$misc->href}&amp;",
				'vars' => ['matview' => 'relname'],
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('relowner'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('relcomment'),
			],
		];

		$actions = [
			'multiactions' => [
				'keycols' => ['matview' => 'relname'],
				'url' => 'materialized_materialized_views.php',
			],
			'browse' => [
				'content' => $lang['strbrowse'],
				'attr' => [
					'href' => [
						'url' => 'display.php',
						'urlvars' => [
							'action' => 'confselectrows',
							'subject' => 'matview',
							'return' => 'schema',
							'matview' => Decorator::field('relname'),
						],
					],
				],
			],
			'select' => [
				'content' => $lang['strselect'],
				'attr' => [
					'href' => [
						'url' => 'materialized_views.php',
						'urlvars' => [
							'action' => 'confselectrows',
							'matview' => Decorator::field('relname'),
						],
					],
				],
			],

			// Insert is possible if the relevant rule for the view has been created.
			//			'insert' => array(
			//				'title'	=> $lang['strinsert'],
			//				'url'	=> "materialized_views.php?action=confinsertrow&amp;{$misc->href}&amp;",
			//				'vars'	=> array('view' => 'relname'),
			//			),

			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'viewproperties.php',
						'urlvars' => [
							'action' => 'confirm_alter',
							'matview' => Decorator::field('relname'),
						],
					],
				],
			],
			'drop' => [
				'multiaction' => 'confirm_drop',
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'materialized_views.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'matview' => Decorator::field('relname'),
						],
					],
				],
			],
		];

		echo $this->printTable($matviews, $columns, $actions, $this->table_place, $lang['strnoviews']);

		$navlinks = [
			'create' => [
				'attr' => [
					'href' => [
						'url' => 'materialized_views.php',
						'urlvars' => [
							'action' => 'create',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreateview'],
			],
			'createwiz' => [
				'attr' => [
					'href' => [
						'url' => 'materialized_views.php',
						'urlvars' => [
							'action' => 'wiz_create',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreateviewwiz'],
			],
		];
		$misc->printNavLinks($navlinks, $this->table_place, get_defined_vars());

	}

	public function render() {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printHeader('M ' . $lang['strviews']);
		$misc->printBody();

		switch ($action) {
			case 'selectrows':
				if (!isset($_REQUEST['cancel'])) {
					$this->doSelectRows(false);
				} else {
					$this->doDefault();
				}

				break;
			case 'confselectrows':
				$this->doSelectRows(true);
				break;
			case 'save_create_wiz':
				if (isset($_REQUEST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveCreateWiz();
				}

				break;
			case 'wiz_create':
				doWizardCreate();
				break;
			case 'set_params_create':
				if (isset($_POST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSetParamsCreate();
				}

				break;
			case 'save_create':
				if (isset($_REQUEST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveCreate();
				}

				break;
			case 'create':
				doCreate();
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
			default:
				$this->doDefault();
				break;
		}

		$misc->printFooter();

	}
}
