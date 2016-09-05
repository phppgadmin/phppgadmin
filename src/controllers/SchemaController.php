<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class SchemaController extends BaseController {
	public $_name = 'SchemaController';

	/**
	 * Show default list of schemas in the database
	 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('database');
		$this->printTabs('database', 'schemas');
		$misc->printMsg($msg);

		// Check that the DB actually supports schemas
		$schemas = $data->getSchemas();

		$columns = [
			'schema' => [
				'title' => $lang['strschema'],
				'field' => Decorator::field('nspname'),
				'url' => "/redirect/schema?{$misc->href}&amp;",
				'vars' => ['schema' => 'nspname'],
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('nspowner'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('nspcomment'),
			],
		];

		$actions = [
			'multiactions' => [
				'keycols' => ['nsp' => 'nspname'],
				'url' => 'schemas.php',
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'schemas.php',
						'urlvars' => [
							'action' => 'drop',
							'nsp' => Decorator::field('nspname'),
						],
					],
				],
				'multiaction' => 'drop',
			],
			'privileges' => [
				'content' => $lang['strprivileges'],
				'attr' => [
					'href' => [
						'url' => 'privileges.php',
						'urlvars' => [
							'subject' => 'schema',
							'schema' => Decorator::field('nspname'),
						],
					],
				],
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'schemas.php',
						'urlvars' => [
							'action' => 'alter',
							'schema' => Decorator::field('nspname'),
						],
					],
				],
			],
		];

		if (!$data->hasAlterSchema()) {
			unset($actions['alter']);
		}

		echo $this->printTable($schemas, $columns, $actions, 'schemas-schemas', $lang['strnoschemas']);

		$this->printNavLinks(['create' => [
			'attr' => [
				'href' => [
					'url' => 'schemas.php',
					'urlvars' => [
						'action' => 'create',
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
					],
				],
			],
			'content' => $lang['strcreateschema'],
		]], 'schemas-schemas', get_defined_vars());
	}

	/**
	 * Displays a screen where they can enter a new schema
	 */
	public function doCreate($msg = '') {
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$server_info = $misc->getServerInfo();

		if (!isset($_POST['formName'])) {
			$_POST['formName'] = '';
		}

		if (!isset($_POST['formAuth'])) {
			$_POST['formAuth'] = $server_info['username'];
		}

		if (!isset($_POST['formSpc'])) {
			$_POST['formSpc'] = '';
		}

		if (!isset($_POST['formComment'])) {
			$_POST['formComment'] = '';
		}

		// Fetch all users from the database
		$users = $data->getUsers();

		$this->printTrail('database');
		$misc->printTitle($lang['strcreateschema'], 'pg.schema.create');
		$misc->printMsg($msg);

		echo '<form action="/src/views/schemas.php" method="post">' . "\n";
		echo "<table style=\"width: 100%\">\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formName']), "\" /></td>\n\t</tr>\n";
		// Owner
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strowner']}</th>\n";
		echo "\t\t<td class=\"data1\">\n\t\t\t<select name=\"formAuth\">\n";
		while (!$users->EOF) {
			$uname = htmlspecialchars($users->fields['usename']);
			echo "\t\t\t\t<option value=\"{$uname}\"",
			($uname == $_POST['formAuth']) ? ' selected="selected"' : '', ">{$uname}</option>\n";
			$users->moveNext();
		}
		echo "\t\t\t</select>\n\t\t</td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "\t\t<td class=\"data1\"><textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
		htmlspecialchars($_POST['formComment']), "</textarea></td>\n\t</tr>\n";

		echo "</table>\n";
		echo "<p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create\" />\n";
		echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"create\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</p>\n";
		echo "</form>\n";
	}

	/**
	 * Actually creates the new schema in the database
	 */
	public function doSaveCreate() {
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name
		if ($_POST['formName'] == '') {
			$this->doCreate($lang['strschemaneedsname']);
		} else {
			$status = $data->createSchema($_POST['formName'], $_POST['formAuth'], $_POST['formComment']);
			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doDefault($lang['strschemacreated']);
			} else {
				$this->doCreate($lang['strschemacreatedbad']);
			}

		}
	}

	/**
	 * Display a form to permit editing schema properies.
	 * TODO: permit changing owner
	 */
	public function doAlter($msg = '') {

		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$misc->printTitle($lang['stralter'], 'pg.schema.alter');
		$misc->printMsg($msg);

		$schema = $data->getSchemaByName($_REQUEST['schema']);
		if ($schema->recordCount() > 0) {
			if (!isset($_POST['comment'])) {
				$_POST['comment'] = $schema->fields['nspcomment'];
			}

			if (!isset($_POST['schema'])) {
				$_POST['schema'] = $_REQUEST['schema'];
			}

			if (!isset($_POST['name'])) {
				$_POST['name'] = $_REQUEST['schema'];
			}

			if (!isset($_POST['owner'])) {
				$_POST['owner'] = $schema->fields['ownername'];
			}

			echo '<form action="/src/views/schemas.php" method="post">' . "\n";
			echo "<table>\n";

			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" />\n";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			if ($data->hasAlterSchemaOwner()) {
				$users = $data->getUsers();
				echo "<tr><th class=\"data left required\">{$lang['strowner']}</th>\n";
				echo "<td class=\"data2\"><select name=\"owner\">";
				while (!$users->EOF) {
					$uname = $users->fields['usename'];
					echo "<option value=\"", htmlspecialchars($uname), "\"",
					($uname == $_POST['owner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
					$users->moveNext();
				}
				echo "</select></td></tr>\n";
			} else {
				echo "<input name=\"owner\" value=\"{$_POST['owner']}\" type=\"hidden\" />";
			}

			echo "\t<tr>\n";
			echo "\t\t<th class=\"data\">{$lang['strcomment']}</th>\n";
			echo "\t\t<td class=\"data1\"><textarea cols=\"32\" rows=\"3\" name=\"comment\">", htmlspecialchars($_POST['comment']), "</textarea></td>\n";
			echo "\t</tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter\" />\n";
			echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_POST['schema']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}
	}

/**
 * Save the form submission containing changes to a schema
 */
	public function doSaveAlter($msg = '') {
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->updateSchema($_POST['schema'], $_POST['comment'], $_POST['name'], $_POST['owner']);
		if ($status == 0) {
			$this->misc->setReloadBrowser(true);
			$this->doDefault($lang['strschemaaltered']);
		} else {
			$this->doAlter($lang['strschemaalteredbad']);
		}

	}

/**
 * Show confirmation of drop and perform actual drop
 */
	public function doDrop($confirm) {
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['nsp']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifyschematodrop']);
			exit();
		}

		if ($confirm) {
			$this->printTrail('schema');
			$misc->printTitle($lang['strdrop'], 'pg.schema.drop');

			echo '<form action="/src/views/schemas.php" method="post">' . "\n";
			//If multi drop
			if (isset($_REQUEST['ma'])) {
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo '<p>', sprintf($lang['strconfdropschema'], $misc->printVal($a['nsp'])), "</p>\n";
					echo '<input type="hidden" name="nsp[]" value="', htmlspecialchars($a['nsp']), "\" />\n";
				}
			} else {
				echo "<p>", sprintf($lang['strconfdropschema'], $misc->printVal($_REQUEST['nsp'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"nsp\" value=\"", htmlspecialchars($_REQUEST['nsp']), "\" />\n";
			}

			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			if (is_array($_POST['nsp'])) {
				$msg    = '';
				$status = $data->beginTransaction();
				if ($status == 0) {
					foreach ($_POST['nsp'] as $s) {
						$status = $data->dropSchema($s, isset($_POST['cascade']));
						if ($status == 0) {
							$msg .= sprintf('%s: %s<br />', htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strschemadropped']);
						} else {
							$data->endTransaction();
							$this->doDefault(sprintf('%s%s: %s<br />', $msg, htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strschemadroppedbad']));
							return;
						}
					}
				}
				if ($data->endTransaction() == 0) {
					// Everything went fine, back to the Default page....
					$this->misc->setReloadBrowser(true);
					$this->doDefault($msg);
				} else {
					$this->doDefault($lang['strschemadroppedbad']);
				}

			} else {
				$status = $data->dropSchema($_POST['nsp'], isset($_POST['cascade']));
				if ($status == 0) {
					$this->misc->setReloadBrowser(true);
					$this->doDefault($lang['strschemadropped']);
				} else {
					$this->doDefault($lang['strschemadroppedbad']);
				}

			}
		}
	}

/**
 * Displays options for database download
 */
	public function doExport($msg = '') {
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'export');
		$misc->printMsg($msg);

		echo '<form action="/src/views/dbexport.php" method="post">' . "\n";

		echo "<table>\n";
		echo "<tr><th class=\"data\">{$lang['strformat']}</th><th class=\"data\" colspan=\"2\">{$lang['stroptions']}</th></tr>\n";
		// Data only
		echo "<tr><th class=\"data left\" rowspan=\"2\">";
		echo "<input type=\"radio\" id=\"what1\" name=\"what\" value=\"dataonly\" checked=\"checked\" /><label for=\"what1\">{$lang['strdataonly']}</label></th>\n";
		echo "<td>{$lang['strformat']}</td>\n";
		echo "<td><select name=\"d_format\">\n";
		echo "<option value=\"copy\">COPY</option>\n";
		echo "<option value=\"sql\">SQL</option>\n";
		echo "</select>\n</td>\n</tr>\n";
		echo "<tr><td><label for=\"d_oids\">{$lang['stroids']}</label></td><td><input type=\"checkbox\" id=\"d_oids\" name=\"d_oids\" /></td>\n</tr>\n";
		// Structure only
		echo "<tr><th class=\"data left\"><input type=\"radio\" id=\"what2\" name=\"what\" value=\"structureonly\" /><label for=\"what2\">{$lang['strstructureonly']}</label></th>\n";
		echo "<td><label for=\"s_clean\">{$lang['strdrop']}</label></td><td><input type=\"checkbox\" id=\"s_clean\" name=\"s_clean\" /></td>\n</tr>\n";
		// Structure and data
		echo "<tr><th class=\"data left\" rowspan=\"3\">";
		echo "<input type=\"radio\" id=\"what3\" name=\"what\" value=\"structureanddata\" /><label for=\"what3\">{$lang['strstructureanddata']}</label></th>\n";
		echo "<td>{$lang['strformat']}</td>\n";
		echo "<td><select name=\"sd_format\">\n";
		echo "<option value=\"copy\">COPY</option>\n";
		echo "<option value=\"sql\">SQL</option>\n";
		echo "</select>\n</td>\n</tr>\n";
		echo "<tr><td><label for=\"sd_clean\">{$lang['strdrop']}</label></td><td><input type=\"checkbox\" id=\"sd_clean\" name=\"sd_clean\" /></td>\n</tr>\n";
		echo "<tr><td><label for=\"sd_oids\">{$lang['stroids']}</label></td><td><input type=\"checkbox\" id=\"sd_oids\" name=\"sd_oids\" /></td>\n</tr>\n";
		echo "</table>\n";

		echo "<h3>{$lang['stroptions']}</h3>\n";
		echo "<p><input type=\"radio\" id=\"output1\" name=\"output\" value=\"show\" checked=\"checked\" /><label for=\"output1\">{$lang['strshow']}</label>\n";
		echo "<br/><input type=\"radio\" id=\"output2\" name=\"output\" value=\"download\" /><label for=\"output2\">{$lang['strdownload']}</label>\n";
		// MSIE cannot download gzip in SSL mode - it's just broken
		if (!(strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') && isset($_SERVER['HTTPS']))) {
			echo "<br /><input type=\"radio\" id=\"output3\" name=\"output\" value=\"gzipped\" /><label for=\"output3\">{$lang['strdownloadgzipped']}</label>\n";
		}
		echo "</p>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"export\" />\n";
		echo "<input type=\"hidden\" name=\"subject\" value=\"schema\" />\n";
		echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strexport']}\" /></p>\n";
		echo "</form>\n";
	}
}
