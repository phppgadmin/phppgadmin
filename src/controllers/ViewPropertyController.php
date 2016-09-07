<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class ViewPropertyController extends BaseController {
	public $_name = 'ViewPropertyController';

/**
 * Function to save after editing a view
 */
	public function doSaveEdit() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->setView($_POST['view'], $_POST['formDefinition'], $_POST['formComment']);
		if ($status == 0) {
			$this->doDefinition($lang['strviewupdated']);
		} else {
			$this->doEdit($lang['strviewupdatedbad']);
		}

	}

/**
 * Function to allow editing of a view
 */
	public function doEdit($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('view');
		$misc->printTitle($lang['stredit'], 'pg.view.alter');
		$misc->printMsg($msg);

		$viewdata = $data->getView($_REQUEST['view']);

		if ($viewdata->recordCount() > 0) {

			if (!isset($_POST['formDefinition'])) {
				$_POST['formDefinition'] = $viewdata->fields['vwdefinition'];
				$_POST['formComment']    = $viewdata->fields['relcomment'];
			}

			echo "<form action=\"/src/views/viewproperties.php\" method=\"post\">\n";
			echo "<table style=\"width: 100%\">\n";
			echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strdefinition']}</th>\n";
			echo "\t\t<td class=\"data1\"><textarea style=\"width: 100%;\" rows=\"20\" cols=\"50\" name=\"formDefinition\">",
			htmlspecialchars($_POST['formDefinition']), "</textarea></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
			echo "\t\t<td class=\"data1\"><textarea rows=\"3\" cols=\"32\" name=\"formComment\">",
			htmlspecialchars($_POST['formComment']), "</textarea></td>\n\t</tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_edit\" />\n";
			echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

/**
 * Allow the dumping of the data "in" a view
 * NOTE:: PostgreSQL doesn't currently support dumping the data in a view
 *        so I have disabled the data related parts for now. In the future
 *        we should allow it conditionally if it becomes supported.  This is
 *        a SMOP since it is based on pg_dump version not backend version.
 */
	public function doExport($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('view');
		$this->printTabs('view', 'export');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/dataexport.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data\">{$lang['strformat']}</th><th class=\"data\" colspan=\"2\">{$lang['stroptions']}</th></tr>\n";
		// Data only
		echo "<!--\n";
		echo "<tr><th class=\"data left\">";
		echo "<input type=\"radio\" id=\"what1\" name=\"what\" value=\"dataonly\" /><label for=\"what1\">{$lang['strdataonly']}</label></th>\n";
		echo "<td>{$lang['strformat']}</td>\n";
		echo "<td><select name=\"d_format\" >\n";
		echo "<option value=\"copy\">COPY</option>\n";
		echo "<option value=\"sql\">SQL</option>\n";
		echo "<option value=\"csv\">CSV</option>\n";
		echo "<option value=\"tab\">{$lang['strtabbed']}</option>\n";
		echo "<option value=\"html\">XHTML</option>\n";
		echo "<option value=\"xml\">XML</option>\n";
		echo "</select>\n</td>\n</tr>\n";
		echo "-->\n";

		// Structure only
		echo "<tr><th class=\"data left\"><input type=\"radio\" id=\"what2\" name=\"what\" value=\"structureonly\" checked=\"checked\" /><label for=\"what2\">{$lang['strstructureonly']}</label></th>\n";
		echo "<td><label for=\"s_clean\">{$lang['strdrop']}</label></td><td><input type=\"checkbox\" id=\"s_clean\" name=\"s_clean\" /></td>\n</tr>\n";
		// Structure and data
		echo "<!--\n";
		echo "<tr><th class=\"data left\" rowspan=\"2\">";
		echo "<input type=\"radio\" id=\"what3\" name=\"what\" value=\"structureanddata\" /><label for=\"what3\">{$lang['strstructureanddata']}</label></th>\n";
		echo "<td>{$lang['strformat']}</td>\n";
		echo "<td><select name=\"sd_format\">\n";
		echo "<option value=\"copy\">COPY</option>\n";
		echo "<option value=\"sql\">SQL</option>\n";
		echo "</select>\n</td>\n</tr>\n";
		echo "<td><label for=\"sd_clean\">{$lang['strdrop']}</label></td><td><input type=\"checkbox\" id=\"sd_clean\" name=\"sd_clean\" /></td>\n</tr>\n";
		echo "-->\n";
		echo "</table>\n";

		echo "<h3>{$lang['stroptions']}</h3>\n";
		echo "<p><input type=\"radio\" id=\"output1\" name=\"output\" value=\"show\" checked=\"checked\" /><label for=\"output1\">{$lang['strshow']}</label>\n";
		echo "<br/><input type=\"radio\" id=\"output2\" name=\"output\" value=\"download\" /><label for=\"output2\">{$lang['strdownload']}</label></p>\n";

		echo "<p><input type=\"hidden\" name=\"action\" value=\"export\" />\n";
		echo $misc->form;
		echo "<input type=\"hidden\" name=\"subject\" value=\"view\" />\n";
		echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
		echo "<input type=\"submit\" value=\"{$lang['strexport']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Show definition for a view
 */
	public function doDefinition($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Get view
		$vdata = $data->getView($_REQUEST['view']);

		$this->printTrail('view');
		$this->printTabs('view', 'definition');
		$misc->printMsg($msg);

		if ($vdata->recordCount() > 0) {
			// Show comment if any
			if ($vdata->fields['relcomment'] !== null) {
				echo "<p class=\"comment\">", $misc->printVal($vdata->fields['relcomment']), "</p>\n";
			}

			echo "<table style=\"width: 100%\">\n";
			echo "<tr><th class=\"data\">{$lang['strdefinition']}</th></tr>\n";
			echo "<tr><td class=\"data1\">", $misc->printVal($vdata->fields['vwdefinition']), "</td></tr>\n";
			echo "</table>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

		$this->printNavLinks(['alter' => [
			'attr' => [
				'href' => [
					'url' => 'viewproperties.php',
					'urlvars' => [
						'action' => 'edit',
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'view' => $_REQUEST['view'],
					],
				],
			],
			'content' => $lang['stralter'],
		]], 'viewproperties-definition', get_defined_vars());
	}

/**
 * Displays a screen where they can alter a column in a view
 */
	public function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_REQUEST['stage'])) {
			$_REQUEST['stage'] = 1;
		}

		switch ($_REQUEST['stage']) {
			case 1:

				$this->printTrail('column');
				$misc->printTitle($lang['stralter'], 'pg.column.alter');
				$misc->printMsg($msg);

				echo "<form action=\"/src/views/viewproperties.php\" method=\"post\">\n";

				// Output view header
				echo "<table>\n";
				echo "<tr><th class=\"data required\">{$lang['strname']}</th><th class=\"data required\">{$lang['strtype']}</th>";
				echo "<th class=\"data\">{$lang['strdefault']}</th><th class=\"data\">{$lang['strcomment']}</th></tr>";

				$column = $data->getTableAttributes($_REQUEST['view'], $_REQUEST['column']);

				if (!isset($_REQUEST['default'])) {
					$_REQUEST['field']   = $column->fields['attname'];
					$_REQUEST['default'] = $_REQUEST['olddefault'] = $column->fields['adsrc'];
					$_REQUEST['comment'] = $column->fields['comment'];
				}

				echo "<tr><td><input name=\"field\" size=\"32\" value=\"",
				htmlspecialchars($_REQUEST['field']), "\" /></td>";

				echo "<td>", $misc->printVal($data->formatType($column->fields['type'], $column->fields['atttypmod'])), "</td>";
				echo "<td><input name=\"default\" size=\"20\" value=\"",
				htmlspecialchars($_REQUEST['default']), "\" /></td>";
				echo "<td><input name=\"comment\" size=\"32\" value=\"",
				htmlspecialchars($_REQUEST['comment']), "\" /></td>";

				echo "</table>\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"properties\" />\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"2\" />\n";
				echo $misc->form;
				echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
				echo "<input type=\"hidden\" name=\"column\" value=\"", htmlspecialchars($_REQUEST['column']), "\" />\n";
				echo "<input type=\"hidden\" name=\"olddefault\" value=\"", htmlspecialchars($_REQUEST['olddefault']), "\" />\n";
				echo "<input type=\"submit\" value=\"{$lang['stralter']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";

				break;
			case 2:

				// Check inputs
				if (trim($_REQUEST['field']) == '') {
					$_REQUEST['stage'] = 1;
					$this->doProperties($lang['strcolneedsname']);
					return;
				}

				// Alter the view column
				$status = $data->alterColumn($_REQUEST['view'], $_REQUEST['column'], $_REQUEST['field'],
					false, false, $_REQUEST['default'], $_REQUEST['olddefault'],
					'', '', '', '', $_REQUEST['comment']);
				if ($status == 0) {
					$this->doDefault($lang['strcolumnaltered']);
				} else {
					$_REQUEST['stage'] = 1;
					$this->doProperties($lang['strcolumnalteredbad']);
					return;
				}
				break;
			default:
				echo "<p>{$lang['strinvalidparam']}</p>\n";
		}
	}

	public function doAlter($confirm = false, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {

			$this->printTrail('view');
			$misc->printTitle($lang['stralter'], 'pg.view.alter');
			$misc->printMsg($msg);

			// Fetch view info
			$view = $data->getView($_REQUEST['view']);

			if ($view->recordCount() > 0) {
				if (!isset($_POST['name'])) {
					$_POST['name'] = $view->fields['relname'];
				}

				if (!isset($_POST['owner'])) {
					$_POST['owner'] = $view->fields['relowner'];
				}

				if (!isset($_POST['newschema'])) {
					$_POST['newschema'] = $view->fields['nspname'];
				}

				if (!isset($_POST['comment'])) {
					$_POST['comment'] = $view->fields['relcomment'];
				}

				echo "<form action=\"/src/views/viewproperties.php\" method=\"post\">\n";
				echo "<table>\n";
				echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
				echo "<td class=\"data1\">";
				echo "<input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
				htmlspecialchars($_POST['name']), "\" /></td></tr>\n";

				if ($data->isSuperUser()) {

					// Fetch all users
					$users = $data->getUsers();

					echo "<tr><th class=\"data left required\">{$lang['strowner']}</th>\n";
					echo "<td class=\"data1\"><select name=\"owner\">";
					while (!$users->EOF) {
						$uname = $users->fields['usename'];
						echo "<option value=\"", htmlspecialchars($uname), "\"",
						($uname == $_POST['owner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
						$users->moveNext();
					}
					echo "</select></td></tr>\n";
				}

				if ($data->hasAlterTableSchema()) {
					$schemas = $data->getSchemas();
					echo "<tr><th class=\"data left required\">{$lang['strschema']}</th>\n";
					echo "<td class=\"data1\"><select name=\"newschema\">";
					while (!$schemas->EOF) {
						$schema = $schemas->fields['nspname'];
						echo "<option value=\"", htmlspecialchars($schema), "\"",
						($schema == $_POST['newschema']) ? ' selected="selected"' : '', ">", htmlspecialchars($schema), "</option>\n";
						$schemas->moveNext();
					}
					echo "</select></td></tr>\n";
				}

				echo "<tr><th class=\"data left\">{$lang['strcomment']}</th>\n";
				echo "<td class=\"data1\">";
				echo "<textarea rows=\"3\" cols=\"32\" name=\"comment\">",
				htmlspecialchars($_POST['comment']), "</textarea></td></tr>\n";
				echo "</table>\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"alter\" />\n";
				echo "<input type=\"hidden\" name=\"view\" value=\"", htmlspecialchars($_REQUEST['view']), "\" />\n";
				echo $misc->form;
				echo "<p><input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";
			} else {
				echo "<p>{$lang['strnodata']}</p>\n";
			}

		} else {

			// For databases that don't allow owner change
			if (!isset($_POST['owner'])) {
				$_POST['owner'] = '';
			}

			if (!isset($_POST['newschema'])) {
				$_POST['newschema'] = null;
			}

			$status = $data->alterView($_POST['view'], $_POST['name'], $_POST['owner'], $_POST['newschema'], $_POST['comment']);
			if ($status == 0) {
				// If view has been renamed, need to change to the new name and
				// reload the browser frame.
				if ($_POST['view'] != $_POST['name']) {
					// Jump them to the new view name
					$_REQUEST['view'] = $_POST['name'];
					// Force a browser reload
					$this->misc->setReloadBrowser(true);
				}
				// If schema has changed, need to change to the new schema and reload the browser
				if (!empty($_POST['newschema']) && ($_POST['newschema'] != $data->_schema)) {
					// Jump them to the new sequence schema
					$misc->setCurrentSchema($_POST['newschema']);
					$this->misc->setReloadBrowser(true);
				}
				$this->doDefault($lang['strviewaltered']);
			} else {
				$this->doAlter(true, $lang['strviewalteredbad']);
			}

		}
	}

/**
 * Show view definition and virtual columns
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$attPre = function (&$rowdata) use ($data) {
			$rowdata->fields['+type'] = $data->formatType($rowdata->fields['type'], $rowdata->fields['atttypmod']);
		};

		$this->printTrail('view');
		$this->printTabs('view', 'columns');
		$misc->printMsg($msg);

		// Get view
		$vdata = $data->getView($_REQUEST['view']);
		// Get columns (using same method for getting a view)
		$attrs = $data->getTableAttributes($_REQUEST['view']);

		// Show comment if any
		if ($vdata->fields['relcomment'] !== null) {
			echo "<p class=\"comment\">", $misc->printVal($vdata->fields['relcomment']), "</p>\n";
		}

		$columns = [
			'column' => [
				'title' => $lang['strcolumn'],
				'field' => Decorator::field('attname'),
				'url' => "colproperties.php?subject=column&amp;{$misc->href}&amp;view=" . urlencode($_REQUEST['view']) . "&amp;",
				'vars' => ['column' => 'attname'],
			],
			'type' => [
				'title' => $lang['strtype'],
				'field' => Decorator::field('+type'),
			],
			'default' => [
				'title' => $lang['strdefault'],
				'field' => Decorator::field('adsrc'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('comment'),
			],
		];

		$actions = [
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'viewproperties.php',
						'urlvars' => [
							'action' => 'properties',
							'view' => $_REQUEST['view'],
							'column' => Decorator::field('attname'),
						],
					],
				],
			],
		];

		echo $this->printTable($attrs, $columns, $actions, 'viewproperties-viewproperties', null, $attPre);

		echo "<br />\n";

		$navlinks = [
			'browse' => [
				'attr' => [
					'href' => [
						'url' => 'display.php',
						'urlvars' => [
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'view' => $_REQUEST['view'],
							'subject' => 'view',
							'return' => 'view',
						],
					],
				],
				'content' => $lang['strbrowse'],
			],
			'select' => [
				'attr' => [
					'href' => [
						'url' => 'views.php',
						'urlvars' => [
							'action' => 'confselectrows',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'view' => $_REQUEST['view'],
						],
					],
				],
				'content' => $lang['strselect'],
			],
			'drop' => [
				'attr' => [
					'href' => [
						'url' => 'views.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'view' => $_REQUEST['view'],
						],
					],
				],
				'content' => $lang['strdrop'],
			],
			'alter' => [
				'attr' => [
					'href' => [
						'url' => 'viewproperties.php',
						'urlvars' => [
							'action' => 'confirm_alter',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'view' => $_REQUEST['view'],
						],
					],
				],
				'content' => $lang['stralter'],
			],
		];

		$this->printNavLinks($navlinks, 'viewproperties-viewproperties', get_defined_vars());
	}

}
