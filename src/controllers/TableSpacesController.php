<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class TableSpacesController extends BaseController {
	public $_name = 'TableSpacesController';

	/**
	 * Function to allow altering of a tablespace
	 */
	function doAlter($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('tablespace');
		$misc->printTitle($lang['stralter'], 'pg.tablespace.alter');
		$misc->printMsg($msg);

		// Fetch tablespace info
		$tablespace = $data->getTablespace($_REQUEST['tablespace']);
		// Fetch all users
		$users = $data->getUsers();

		if ($tablespace->recordCount() > 0) {

			if (!isset($_POST['name'])) {
				$_POST['name'] = $tablespace->fields['spcname'];
			}

			if (!isset($_POST['owner'])) {
				$_POST['owner'] = $tablespace->fields['spcowner'];
			}

			if (!isset($_POST['comment'])) {
				$_POST['comment'] = ($data->hasSharedComments()) ? $tablespace->fields['spccomment'] : '';
			}

			echo "<form action=\"/src/views/tablespaces.php\" method=\"post\">\n";
			echo $misc->form;
			echo "<table>\n";
			echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" /></td></tr>\n";
			echo "<tr><th class=\"data left required\">{$lang['strowner']}</th>\n";
			echo "<td class=\"data1\"><select name=\"owner\">";
			while (!$users->EOF) {
				$uname = $users->fields['usename'];
				echo "<option value=\"", htmlspecialchars($uname), "\"",
				($uname == $_POST['owner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
				$users->moveNext();
			}
			echo "</select></td></tr>\n";
			if ($data->hasSharedComments()) {
				echo "<tr><th class=\"data left\">{$lang['strcomment']}</th>\n";
				echo "<td class=\"data1\">";
				echo "<textarea rows=\"3\" cols=\"32\" name=\"comment\">",
				htmlspecialchars($_POST['comment']), "</textarea></td></tr>\n";
			}
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_edit\" />\n";
			echo "<input type=\"hidden\" name=\"tablespace\" value=\"", htmlspecialchars($_REQUEST['tablespace']), "\" />\n";
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

	/**
	 * Function to save after altering a tablespace
	 */
	function doSaveAlter() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check data
		if (trim($_POST['name']) == '') {
			$this->doAlter($lang['strtablespaceneedsname']);
		} else {
			$status = $data->alterTablespace($_POST['tablespace'], $_POST['name'], $_POST['owner'], $_POST['comment']);
			if ($status == 0) {
				// If tablespace has been renamed, need to change to the new name
				if ($_POST['tablespace'] != $_POST['name']) {
					// Jump them to the new table name
					$_REQUEST['tablespace'] = $_POST['name'];
				}
				$this->doDefault($lang['strtablespacealtered']);
			} else {
				$this->doAlter($lang['strtablespacealteredbad']);
			}

		}
	}

	/**
	 * Show confirmation of drop and perform actual drop
	 */
	function doDrop($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('tablespace');
			$misc->printTitle($lang['strdrop'], 'pg.tablespace.drop');

			echo "<p>", sprintf($lang['strconfdroptablespace'], $misc->printVal($_REQUEST['tablespace'])), "</p>\n";

			echo "<form action=\"/src/views/tablespaces.php\" method=\"post\">\n";
			echo $misc->form;
			echo "<input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"tablespace\" value=\"", htmlspecialchars($_REQUEST['tablespace']), "\" />\n";
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->droptablespace($_REQUEST['tablespace']);
			if ($status == 0) {
				$this->doDefault($lang['strtablespacedropped']);
			} else {
				$this->doDefault($lang['strtablespacedroppedbad']);
			}

		}
	}

	/**
	 * Displays a screen where they can enter a new tablespace
	 */
	function doCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$server_info = $misc->getServerInfo();

		if (!isset($_POST['formSpcname'])) {
			$_POST['formSpcname'] = '';
		}

		if (!isset($_POST['formOwner'])) {
			$_POST['formOwner'] = $server_info['username'];
		}

		if (!isset($_POST['formLoc'])) {
			$_POST['formLoc'] = '';
		}

		if (!isset($_POST['formComment'])) {
			$_POST['formComment'] = '';
		}

		// Fetch all users
		$users = $data->getUsers();

		$this->printTrail('server');
		$misc->printTitle($lang['strcreatetablespace'], 'pg.tablespace.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/tablespaces.php\" method=\"post\">\n";
		echo $misc->form;
		echo "<table>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"32\" name=\"formSpcname\" maxlength=\"{$data->_maxNameLen}\" value=\"", htmlspecialchars($_POST['formSpcname']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strowner']}</th>\n";
		echo "\t\t<td class=\"data1\"><select name=\"formOwner\">\n";
		while (!$users->EOF) {
			$uname = $users->fields['usename'];
			echo "\t\t\t<option value=\"", htmlspecialchars($uname), "\"",
			($uname == $_POST['formOwner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
			$users->moveNext();
		}
		echo "\t\t</select></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strlocation']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"32\" name=\"formLoc\" value=\"", htmlspecialchars($_POST['formLoc']), "\" /></td>\n\t</tr>\n";
		// Comments (if available)
		if ($data->hasSharedComments()) {
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
			echo "\t\t<td><textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
			htmlspecialchars($_POST['formComment']), "</textarea></td>\n\t</tr>\n";
		}
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Actually creates the new tablespace in the cluster
	 */
	function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check data
		if (trim($_POST['formSpcname']) == '') {
			$this->doCreate($lang['strtablespaceneedsname']);
		} elseif (trim($_POST['formLoc']) == '') {
			$this->doCreate($lang['strtablespaceneedsloc']);
		} else {
			// Default comment to blank if it isn't set
			if (!isset($_POST['formComment'])) {
				$_POST['formComment'] = null;
			}

			$status = $data->createTablespace($_POST['formSpcname'], $_POST['formOwner'], $_POST['formLoc'], $_POST['formComment']);
			if ($status == 0) {
				$this->doDefault($lang['strtablespacecreated']);
			} else {
				$this->doCreate($lang['strtablespacecreatedbad']);
			}

		}
	}

	/**
	 * Show default list of tablespaces in the cluster
	 */
	function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('server');
		$this->printTabs('server', 'tablespaces');
		$misc->printMsg($msg);

		$tablespaces = $data->getTablespaces();

		$columns = [
			'database' => [
				'title' => $lang['strname'],
				'field' => \PHPPgAdmin\Decorators\Decorator::field('spcname'),
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => \PHPPgAdmin\Decorators\Decorator::field('spcowner'),
			],
			'location' => [
				'title' => $lang['strlocation'],
				'field' => \PHPPgAdmin\Decorators\Decorator::field('spclocation'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
		];

		if ($data->hasSharedComments()) {
			$columns['comment'] = [
				'title' => $lang['strcomment'],
				'field' => \PHPPgAdmin\Decorators\Decorator::field('spccomment'),
			];
		}

		$actions = [
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'tablespaces.php',
						'urlvars' => [
							'action' => 'edit',
							'tablespace' => \PHPPgAdmin\Decorators\Decorator::field('spcname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'tablespaces.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'tablespace' => \PHPPgAdmin\Decorators\Decorator::field('spcname'),
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
							'subject' => 'tablespace',
							'tablespace' => \PHPPgAdmin\Decorators\Decorator::field('spcname'),
						],
					],
				],
			],
		];

		echo $this->printTable($tablespaces, $columns, $actions, 'tablespaces-tablespaces', $lang['strnotablespaces']);

		$this->printNavLinks(['create' => [
			'attr' => [
				'href' => [
					'url' => 'tablespaces.php',
					'urlvars' => [
						'action' => 'create',
						'server' => $_REQUEST['server'],
					],
				],
			],
			'content' => $lang['strcreatetablespace'],
		]], 'tablespaces-tablespaces', get_defined_vars());
	}

	function render() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();
		$action->$this->action;

		$misc->printHeader($lang['strtablespaces']);
		$misc->printBody();

		switch ($action) {
			case 'save_create':
				if (isset($_REQUEST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveCreate();
				}

				break;
			case 'create':
				$this->doCreate();
				break;
			case 'drop':
				if (isset($_REQUEST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doDrop(false);
				}

				break;
			case 'confirm_drop':
				$this->doDrop(true);
				break;
			case 'save_edit':
				if (isset($_REQUEST['cancel'])) {
					$this->doDefault();
				} else {
					$this->doSaveAlter();
				}

				break;
			case 'edit':
				$this->doAlter();
				break;
			default:
				$this->doDefault();
				break;
		}

		$misc->printFooter();
	}

}
