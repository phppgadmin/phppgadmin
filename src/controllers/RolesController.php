<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class RolesController extends BaseController {
	public $_name = 'RolesController';

/**
 * Displays a screen for create a new role
 */
	function doCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		global $username;

		if (!isset($_POST['formRolename'])) {
			$_POST['formRolename'] = '';
		}

		if (!isset($_POST['formPassword'])) {
			$_POST['formPassword'] = '';
		}

		if (!isset($_POST['formConfirm'])) {
			$_POST['formConfirm'] = '';
		}

		if (!isset($_POST['formConnLimit'])) {
			$_POST['formConnLimit'] = '';
		}

		if (!isset($_POST['formExpires'])) {
			$_POST['formExpires'] = '';
		}

		if (!isset($_POST['memberof'])) {
			$_POST['memberof'] = [];
		}

		if (!isset($_POST['members'])) {
			$_POST['members'] = [];
		}

		if (!isset($_POST['adminmembers'])) {
			$_POST['adminmembers'] = [];
		}

		$misc->printTrail('role');
		$misc->printTitle($lang['strcreaterole'], 'pg.role.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/roles.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\" style=\"width: 130px\">{$lang['strname']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"15\" maxlength=\"{$data->_maxNameLen}\" name=\"formRolename\" value=\"", htmlspecialchars($_POST['formRolename']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strpassword']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"15\" type=\"password\" name=\"formPassword\" value=\"", htmlspecialchars($_POST['formPassword']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strconfirm']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"15\" type=\"password\" name=\"formConfirm\" value=\"", htmlspecialchars($_POST['formConfirm']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formSuper\">{$lang['strsuper']}</label></th>\n";
		echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formSuper\" name=\"formSuper\"",
		(isset($_POST['formSuper'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCreateDB\">{$lang['strcreatedb']}</label></th>\n";
		echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCreateDB\" name=\"formCreateDB\"",
		(isset($_POST['formCreateDB'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCreateRole\">{$lang['strcancreaterole']}</label></th>\n";
		echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCreateRole\" name=\"formCreateRole\"",
		(isset($_POST['formCreateRole'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formInherits\">{$lang['strinheritsprivs']}</label></th>\n";
		echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formInherits\" name=\"formInherits\"",
		(isset($_POST['formInherits'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCanLogin\">{$lang['strcanlogin']}</label></th>\n";
		echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCanLogin\" name=\"formCanLogin\"",
		(isset($_POST['formCanLogin'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strconnlimit']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"4\" name=\"formConnLimit\" value=\"", htmlspecialchars($_POST['formConnLimit']), "\" /></td>\n\t</tr>\n";
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strexpires']}</th>\n";
		echo "\t\t<td class=\"data1\"><input size=\"23\" name=\"formExpires\" value=\"", htmlspecialchars($_POST['formExpires']), "\" /></td>\n\t</tr>\n";

		$roles = $data->getRoles();
		if ($roles->recordCount() > 0) {
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strmemberof']}</th>\n";
			echo "\t\t<td class=\"data\">\n";
			echo "\t\t\t<select name=\"memberof[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
			while (!$roles->EOF) {
				$rolename = $roles->fields['rolname'];
				echo "\t\t\t\t<option value=\"{$rolename}\"",
				(in_array($rolename, $_POST['memberof']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
				$roles->moveNext();
			}
			echo "\t\t\t</select>\n";
			echo "\t\t</td>\n\t</tr>\n";

			$roles->moveFirst();
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strmembers']}</th>\n";
			echo "\t\t<td class=\"data\">\n";
			echo "\t\t\t<select name=\"members[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
			while (!$roles->EOF) {
				$rolename = $roles->fields['rolname'];
				echo "\t\t\t\t<option value=\"{$rolename}\"",
				(in_array($rolename, $_POST['members']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
				$roles->moveNext();
			}
			echo "\t\t\t</select>\n";
			echo "\t\t</td>\n\t</tr>\n";

			$roles->moveFirst();
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['stradminmembers']}</th>\n";
			echo "\t\t<td class=\"data\">\n";
			echo "\t\t\t<select name=\"adminmembers[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
			while (!$roles->EOF) {
				$rolename = $roles->fields['rolname'];
				echo "\t\t\t\t<option value=\"{$rolename}\"",
				(in_array($rolename, $_POST['adminmembers']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
				$roles->moveNext();
			}
			echo "\t\t\t</select>\n";
			echo "\t\t</td>\n\t</tr>\n";
		}

		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"create\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new role in the database
 */
	function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['memberof'])) {
			$_POST['memberof'] = [];
		}

		if (!isset($_POST['members'])) {
			$_POST['members'] = [];
		}

		if (!isset($_POST['adminmembers'])) {
			$_POST['adminmembers'] = [];
		}

		// Check data
		if ($_POST['formRolename'] == '') {
			$this->doCreate($lang['strroleneedsname']);
		} else if ($_POST['formPassword'] != $_POST['formConfirm']) {
			$this->doCreate($lang['strpasswordconfirm']);
		} else {
			$status = $data->createRole($_POST['formRolename'], $_POST['formPassword'], isset($_POST['formSuper']),
				isset($_POST['formCreateDB']), isset($_POST['formCreateRole']), isset($_POST['formInherits']),
				isset($_POST['formCanLogin']), $_POST['formConnLimit'], $_POST['formExpires'], $_POST['memberof'], $_POST['members'],
				$_POST['adminmembers']);
			if ($status == 0) {
				$this->doDefault($lang['strrolecreated']);
			} else {
				$this->doCreate($lang['strrolecreatedbad']);
			}

		}
	}

/**
 * Function to allow alter a role
 */
	function doAlter($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('role');
		$misc->printTitle($lang['stralter'], 'pg.role.alter');
		$misc->printMsg($msg);

		$roledata = $data->getRole($_REQUEST['rolename']);

		if ($roledata->recordCount() > 0) {
			$server_info                       = $misc->getServerInfo();
			$canRename                         = $data->hasUserRename() && ($_REQUEST['rolename'] != $server_info['username']);
			$roledata->fields['rolsuper']      = $data->phpBool($roledata->fields['rolsuper']);
			$roledata->fields['rolcreatedb']   = $data->phpBool($roledata->fields['rolcreatedb']);
			$roledata->fields['rolcreaterole'] = $data->phpBool($roledata->fields['rolcreaterole']);
			$roledata->fields['rolinherit']    = $data->phpBool($roledata->fields['rolinherit']);
			$roledata->fields['rolcanlogin']   = $data->phpBool($roledata->fields['rolcanlogin']);

			if (!isset($_POST['formExpires'])) {
				if ($canRename) {
					$_POST['formNewRoleName'] = $roledata->fields['rolname'];
				}

				if ($roledata->fields['rolsuper']) {
					$_POST['formSuper'] = '';
				}

				if ($roledata->fields['rolcreatedb']) {
					$_POST['formCreateDB'] = '';
				}

				if ($roledata->fields['rolcreaterole']) {
					$_POST['formCreateRole'] = '';
				}

				if ($roledata->fields['rolinherit']) {
					$_POST['formInherits'] = '';
				}

				if ($roledata->fields['rolcanlogin']) {
					$_POST['formCanLogin'] = '';
				}

				$_POST['formConnLimit'] = $roledata->fields['rolconnlimit'] == '-1' ? '' : $roledata->fields['rolconnlimit'];
				$_POST['formExpires']   = $roledata->fields['rolvaliduntil'] == 'infinity' ? '' : $roledata->fields['rolvaliduntil'];
				$_POST['formPassword']  = '';
			}

			echo "<form action=\"/src/views/roles.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\" style=\"width: 130px\">{$lang['strname']}</th>\n";
			echo "\t\t<td class=\"data1\">", ($canRename ? "<input name=\"formNewRoleName\" size=\"15\" maxlength=\"{$data->_maxNameLen}\" value=\"" . htmlspecialchars($_POST['formNewRoleName']) . "\" />" : $misc->printVal($roledata->fields['rolname'])), "</td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strpassword']}</th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"password\" size=\"15\" name=\"formPassword\" value=\"", htmlspecialchars($_POST['formPassword']), "\" /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strconfirm']}</th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"password\" size=\"15\" name=\"formConfirm\" value=\"\" /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formSuper\">{$lang['strsuper']}</label></th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formSuper\" name=\"formSuper\"",
			(isset($_POST['formSuper'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCreateDB\">{$lang['strcreatedb']}</label></th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCreateDB\" name=\"formCreateDB\"",
			(isset($_POST['formCreateDB'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCreateRole\">{$lang['strcancreaterole']}</label></th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCreateRole\" name=\"formCreateRole\"",
			(isset($_POST['formCreateRole'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formInherits\">{$lang['strinheritsprivs']}</label></th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formInherits\" name=\"formInherits\"",
			(isset($_POST['formInherits'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\"><label for=\"formCanLogin\">{$lang['strcanlogin']}</label></th>\n";
			echo "\t\t<td class=\"data1\"><input type=\"checkbox\" id=\"formCanLogin\" name=\"formCanLogin\"",
			(isset($_POST['formCanLogin'])) ? ' checked="checked"' : '', " /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strconnlimit']}</th>\n";
			echo "\t\t<td class=\"data1\"><input size=\"4\" name=\"formConnLimit\" value=\"", htmlspecialchars($_POST['formConnLimit']), "\" /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strexpires']}</th>\n";
			echo "\t\t<td class=\"data1\"><input size=\"23\" name=\"formExpires\" value=\"", htmlspecialchars($_POST['formExpires']), "\" /></td>\n\t</tr>\n";

			if (!isset($_POST['memberof'])) {
				$memberof = $data->getMemberOf($_REQUEST['rolename']);
				if ($memberof->recordCount() > 0) {
					$i = 0;
					while (!$memberof->EOF) {
						$_POST['memberof'][$i++] = $memberof->fields['rolname'];
						$memberof->moveNext();
					}
				} else {
					$_POST['memberof'] = [];
				}

				$memberofold = implode(',', $_POST['memberof']);
			}
			if (!isset($_POST['members'])) {
				$members = $data->getMembers($_REQUEST['rolename']);
				if ($members->recordCount() > 0) {
					$i = 0;
					while (!$members->EOF) {
						$_POST['members'][$i++] = $members->fields['rolname'];
						$members->moveNext();
					}
				} else {
					$_POST['members'] = [];
				}

				$membersold = implode(',', $_POST['members']);
			}
			if (!isset($_POST['adminmembers'])) {
				$adminmembers = $data->getMembers($_REQUEST['rolename'], 't');
				if ($adminmembers->recordCount() > 0) {
					$i = 0;
					while (!$adminmembers->EOF) {
						$_POST['adminmembers'][$i++] = $adminmembers->fields['rolname'];
						$adminmembers->moveNext();
					}
				} else {
					$_POST['adminmembers'] = [];
				}

				$adminmembersold = implode(',', $_POST['adminmembers']);
			}

			$roles = $data->getRoles($_REQUEST['rolename']);
			if ($roles->recordCount() > 0) {
				echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strmemberof']}</th>\n";
				echo "\t\t<td class=\"data\">\n";
				echo "\t\t\t<select name=\"memberof[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
				while (!$roles->EOF) {
					$rolename = $roles->fields['rolname'];
					echo "\t\t\t\t<option value=\"{$rolename}\"",
					(in_array($rolename, $_POST['memberof']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
					$roles->moveNext();
				}
				echo "\t\t\t</select>\n";
				echo "\t\t</td>\n\t</tr>\n";

				$roles->moveFirst();
				echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strmembers']}</th>\n";
				echo "\t\t<td class=\"data\">\n";
				echo "\t\t\t<select name=\"members[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
				while (!$roles->EOF) {
					$rolename = $roles->fields['rolname'];
					echo "\t\t\t\t<option value=\"{$rolename}\"",
					(in_array($rolename, $_POST['members']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
					$roles->moveNext();
				}
				echo "\t\t\t</select>\n";
				echo "\t\t</td>\n\t</tr>\n";

				$roles->moveFirst();
				echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['stradminmembers']}</th>\n";
				echo "\t\t<td class=\"data\">\n";
				echo "\t\t\t<select name=\"adminmembers[]\" multiple=\"multiple\" size=\"", min(20, $roles->recordCount()), "\">\n";
				while (!$roles->EOF) {
					$rolename = $roles->fields['rolname'];
					echo "\t\t\t\t<option value=\"{$rolename}\"",
					(in_array($rolename, $_POST['adminmembers']) ? ' selected="selected"' : ''), ">", $misc->printVal($rolename), "</option>\n";
					$roles->moveNext();
				}
				echo "\t\t\t</select>\n";
				echo "\t\t</td>\n\t</tr>\n";
			}
			echo "</table>\n";

			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_alter\" />\n";
			echo "<input type=\"hidden\" name=\"rolename\" value=\"", htmlspecialchars($_REQUEST['rolename']), "\" />\n";
			echo "<input type=\"hidden\" name=\"memberofold\" value=\"", isset($_POST['memberofold']) ? $_POST['memberofold'] : htmlspecialchars($memberofold), "\" />\n";
			echo "<input type=\"hidden\" name=\"membersold\" value=\"", isset($_POST['membersold']) ? $_POST['membersold'] : htmlspecialchars($membersold), "\" />\n";
			echo "<input type=\"hidden\" name=\"adminmembersold\" value=\"", isset($_POST['adminmembersold']) ? $_POST['adminmembersold'] : htmlspecialchars($adminmembersold), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

	/**
	 * Function to save after editing a role
	 */
	function doSaveAlter() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['memberof'])) {
			$_POST['memberof'] = [];
		}

		if (!isset($_POST['members'])) {
			$_POST['members'] = [];
		}

		if (!isset($_POST['adminmembers'])) {
			$_POST['adminmembers'] = [];
		}

		// Check name and password
		if (isset($_POST['formNewRoleName']) && $_POST['formNewRoleName'] == '') {
			$this->doAlter($lang['strroleneedsname']);
		} else if ($_POST['formPassword'] != $_POST['formConfirm']) {
			$this->doAlter($lang['strpasswordconfirm']);
		} else {
			if (isset($_POST['formNewRoleName'])) {
				$status = $data->setRenameRole($_POST['rolename'], $_POST['formPassword'], isset($_POST['formSuper']), isset($_POST['formCreateDB']), isset($_POST['formCreateRole']), isset($_POST['formInherits']), isset($_POST['formCanLogin']), $_POST['formConnLimit'], $_POST['formExpires'], $_POST['memberof'], $_POST['members'], $_POST['adminmembers'], $_POST['memberofold'], $_POST['membersold'], $_POST['adminmembersold'], $_POST['formNewRoleName']);
			} else {
				$status = $data->setRole($_POST['rolename'], $_POST['formPassword'], isset($_POST['formSuper']), isset($_POST['formCreateDB']), isset($_POST['formCreateRole']), isset($_POST['formInherits']), isset($_POST['formCanLogin']), $_POST['formConnLimit'], $_POST['formExpires'], $_POST['memberof'], $_POST['members'], $_POST['adminmembers'], $_POST['memberofold'], $_POST['membersold'], $_POST['adminmembersold']);
			}

			if ($status == 0) {
				$this->doDefault($lang['strrolealtered']);
			} else {
				$this->doAlter($lang['strrolealteredbad']);
			}

		}
	}

	/**
	 * Show confirmation of drop a role and perform actual drop
	 */
	function doDrop($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$misc->printTrail('role');
			$misc->printTitle($lang['strdroprole'], 'pg.role.drop');

			echo "<p>", sprintf($lang['strconfdroprole'], $misc->printVal($_REQUEST['rolename'])), "</p>\n";

			echo "<form action=\"/src/views/roles.php\" method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"rolename\" value=\"", htmlspecialchars($_REQUEST['rolename']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			$status = $data->dropRole($_REQUEST['rolename']);
			if ($status == 0) {
				$this->doDefault($lang['strroledropped']);
			} else {
				$this->doDefault($lang['strroledroppedbad']);
			}

		}
	}

	/**
	 * Show the properties of a role
	 */
	function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$misc->printTrail('role');
		$misc->printTitle($lang['strproperties'], 'pg.role');
		$misc->printMsg($msg);

		$roledata = $data->getRole($_REQUEST['rolename']);
		if ($roledata->recordCount() > 0) {
			$roledata->fields['rolsuper']      = $data->phpBool($roledata->fields['rolsuper']);
			$roledata->fields['rolcreatedb']   = $data->phpBool($roledata->fields['rolcreatedb']);
			$roledata->fields['rolcreaterole'] = $data->phpBool($roledata->fields['rolcreaterole']);
			$roledata->fields['rolinherit']    = $data->phpBool($roledata->fields['rolinherit']);
			$roledata->fields['rolcanlogin']   = $data->phpBool($roledata->fields['rolcanlogin']);

			echo "<table>\n";
			echo "\t<tr>\n\t\t<th class=\"data\" style=\"width: 130px\">Description</th>\n";
			echo "\t\t<th class=\"data\" style=\"width: 120\">Value</th>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strname']}</td>\n";
			echo "\t\t<td class=\"data1\">", htmlspecialchars($_REQUEST['rolename']), "</td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['strsuper']}</td>\n";
			echo "\t\t<td class=\"data2\">", (($roledata->fields['rolsuper']) ? $lang['stryes'] : $lang['strno']), "</td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strcreatedb']}</td>\n";
			echo "\t\t<td class=\"data1\">", (($roledata->fields['rolcreatedb']) ? $lang['stryes'] : $lang['strno']), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['strcancreaterole']}</td>\n";
			echo "\t\t<td class=\"data2\">", (($roledata->fields['rolcreaterole']) ? $lang['stryes'] : $lang['strno']), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strinheritsprivs']}</td>\n";
			echo "\t\t<td class=\"data1\">", (($roledata->fields['rolinherit']) ? $lang['stryes'] : $lang['strno']), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['strcanlogin']}</td>\n";
			echo "\t\t<td class=\"data2\">", (($roledata->fields['rolcanlogin']) ? $lang['stryes'] : $lang['strno']), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strconnlimit']}</td>\n";
			echo "\t\t<td class=\"data1\">", ($roledata->fields['rolconnlimit'] == '-1' ? $lang['strnolimit'] : $misc->printVal($roledata->fields['rolconnlimit'])), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['strexpires']}</td>\n";
			echo "\t\t<td class=\"data2\">", ($roledata->fields['rolvaliduntil'] == 'infinity' || is_null($roledata->fields['rolvaliduntil']) ? $lang['strnever'] : $misc->printVal($roledata->fields['rolvaliduntil'])), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strsessiondefaults']}</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolconfig']), "</td>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['strmemberof']}</td>\n";
			echo "\t\t<td class=\"data2\">";
			$memberof = $data->getMemberOf($_REQUEST['rolename']);
			if ($memberof->recordCount() > 0) {
				while (!$memberof->EOF) {
					echo $misc->printVal($memberof->fields['rolname']), "<br />\n";
					$memberof->moveNext();
				}
			}
			echo "</td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">{$lang['strmembers']}</td>\n";
			echo "\t\t<td class=\"data1\">";
			$members = $data->getMembers($_REQUEST['rolename']);
			if ($members->recordCount() > 0) {
				while (!$members->EOF) {
					echo $misc->printVal($members->fields['rolname']), "<br />\n";
					$members->moveNext();
				}
			}
			echo "</td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data2\">{$lang['stradminmembers']}</td>\n";
			echo "\t\t<td class=\"data2\">";
			$adminmembers = $data->getMembers($_REQUEST['rolename'], 't');
			if ($adminmembers->recordCount() > 0) {
				while (!$adminmembers->EOF) {
					echo $misc->printVal($adminmembers->fields['rolname']), "<br />\n";
					$adminmembers->moveNext();
				}
			}
			echo "</td>\n\t</tr>\n";
			echo "</table>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

		$navlinks = [
			'showall' => [
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'server' => $_REQUEST['server'],
						],
					],
				],
				'content' => $lang['strshowallroles'],
			],
			'alter' => [
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'action' => 'alter',
							'server' => $_REQUEST['server'],
							'rolename' => $_REQUEST['rolename'],
						],
					],
				],
				'content' => $lang['stralter'],
			],
			'drop' => [
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'server' => $_REQUEST['server'],
							'rolename' => $_REQUEST['rolename'],
						],
					],
				],
				'content' => $lang['strdrop'],
			],
		];

		$misc->printNavLinks($navlinks, 'roles-properties', get_defined_vars());
	}

	/**
	 * If a role is not a superuser role, then we have an 'account management'
	 * page for change his password, etc.  We don't prevent them from
	 * messing with the URL to gain access to other role admin stuff, because
	 * the PostgreSQL permissions will prevent them changing anything anyway.
	 */
	function doAccount($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$server_info = $misc->getServerInfo();

		$roledata             = $data->getRole($server_info['username']);
		$_REQUEST['rolename'] = $server_info['username'];

		$misc->printTrail('role');
		$misc->printTabs('server', 'account');
		$misc->printMsg($msg);

		if ($roledata->recordCount() > 0) {
			$roledata->fields['rolsuper']      = $data->phpBool($roledata->fields['rolsuper']);
			$roledata->fields['rolcreatedb']   = $data->phpBool($roledata->fields['rolcreatedb']);
			$roledata->fields['rolcreaterole'] = $data->phpBool($roledata->fields['rolcreaterole']);
			$roledata->fields['rolinherit']    = $data->phpBool($roledata->fields['rolinherit']);
			echo "<table>\n";
			echo "\t<tr>\n\t\t<th class=\"data\">{$lang['strname']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strsuper']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strcreatedb']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strcancreaterole']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strinheritsprivs']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strconnlimit']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strexpires']}</th>\n";
			echo "\t\t<th class=\"data\">{$lang['strsessiondefaults']}</th>\n";
			echo "\t</tr>\n";
			echo "\t<tr>\n\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolname']), "</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolsuper'], 'yesno'), "</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolcreatedb'], 'yesno'), "</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolcreaterole'], 'yesno'), "</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolinherit'], 'yesno'), "</td>\n";
			echo "\t\t<td class=\"data1\">", ($roledata->fields['rolconnlimit'] == '-1' ? $lang['strnolimit'] : $misc->printVal($roledata->fields['rolconnlimit'])), "</td>\n";
			echo "\t\t<td class=\"data1\">", ($roledata->fields['rolvaliduntil'] == 'infinity' || is_null($roledata->fields['rolvaliduntil']) ? $lang['strnever'] : $misc->printVal($roledata->fields['rolvaliduntil'])), "</td>\n";
			echo "\t\t<td class=\"data1\">", $misc->printVal($roledata->fields['rolconfig']), "</td>\n";
			echo "\t</tr>\n</table>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

		$misc->printNavLinks(['changepassword' => [
			'attr' => [
				'href' => [
					'url' => 'roles.php',
					'urlvars' => [
						'action' => 'confchangepassword',
						'server' => $_REQUEST['server'],
					],
				],
			],
			'content' => $lang['strchangepassword'],
		]], 'roles-account', get_defined_vars());
	}

	/**
	 * Show confirmation of change password and actually change password
	 */
	function doChangePassword($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$server_info = $misc->getServerInfo();

		if ($confirm) {
			$_REQUEST['rolename'] = $server_info['username'];
			$misc->printTrail('role');
			$misc->printTitle($lang['strchangepassword'], 'pg.role.alter');
			$misc->printMsg($msg);

			if (!isset($_POST['password'])) {
				$_POST['password'] = '';
			}

			if (!isset($_POST['confirm'])) {
				$_POST['confirm'] = '';
			}

			echo "<form action=\"/src/views/roles.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strpassword']}</th>\n";
			echo "\t\t<td><input type=\"password\" name=\"password\" size=\"32\" value=\"",
			htmlspecialchars($_POST['password']), "\" /></td>\n\t</tr>\n";
			echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strconfirm']}</th>\n";
			echo "\t\t<td><input type=\"password\" name=\"confirm\" size=\"32\" value=\"\" /></td>\n\t</tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"changepassword\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"ok\" value=\"{$lang['strok']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</p></form>\n";
		} else {
			// Check that password is minimum length
			if (strlen($_POST['password']) < $conf['min_password_length']) {
				$this->doChangePassword(true, $lang['strpasswordshort']);
			}

			// Check that password matches confirmation password
			elseif ($_POST['password'] != $_POST['confirm']) {
				$this->doChangePassword(true, $lang['strpasswordconfirm']);
			} else {
				$status = $data->changePassword($server_info['username'], $_POST['password']);
				if ($status == 0) {
					$this->doAccount($lang['strpasswordchanged']);
				} else {
					$this->doAccount($lang['strpasswordchangedbad']);
				}

			}
		}
	}

	/**
	 * Show default list of roles in the database
	 */
	function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		function renderRoleConnLimit($val) {
			global $lang;
			return $val == '-1' ? $lang['strnolimit'] : htmlspecialchars($val);
		}

		function renderRoleExpires($val) {
			global $lang;
			return $val == 'infinity' ? $lang['strnever'] : htmlspecialchars($val);
		}

		$misc->printTrail('server');
		$misc->printTabs('server', 'roles');
		$misc->printMsg($msg);

		$roles = $data->getRoles();

		$columns = [
			'role' => [
				'title' => $lang['strrole'],
				'field' => Decorator::field('rolname'),
				'url' => "/redirect/role?action=properties&amp;{$misc->href}&amp;",
				'vars' => ['rolename' => 'rolname'],
			],
			'superuser' => [
				'title' => $lang['strsuper'],
				'field' => Decorator::field('rolsuper'),
				'type' => 'yesno',
			],
			'createdb' => [
				'title' => $lang['strcreatedb'],
				'field' => Decorator::field('rolcreatedb'),
				'type' => 'yesno',
			],
			'createrole' => [
				'title' => $lang['strcancreaterole'],
				'field' => Decorator::field('rolcreaterole'),
				'type' => 'yesno',
			],
			'inherits' => [
				'title' => $lang['strinheritsprivs'],
				'field' => Decorator::field('rolinherit'),
				'type' => 'yesno',
			],
			'canloging' => [
				'title' => $lang['strcanlogin'],
				'field' => Decorator::field('rolcanlogin'),
				'type' => 'yesno',
			],
			'connlimit' => [
				'title' => $lang['strconnlimit'],
				'field' => Decorator::field('rolconnlimit'),
				'type' => 'callback',
				'params' => ['function' => 'renderRoleConnLimit'],
			],
			'expires' => [
				'title' => $lang['strexpires'],
				'field' => Decorator::field('rolvaliduntil'),
				'type' => 'callback',
				'params' => ['function' => 'renderRoleExpires', 'null' => $lang['strnever']],
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
		];

		$actions = [
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'action' => 'alter',
							'rolename' => Decorator::field('rolname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'rolename' => Decorator::field('rolname'),
						],
					],
				],
			],
		];

		echo $misc->printTable($roles, $columns, $actions, 'roles-roles', $lang['strnoroles']);

		$navlinks = [
			'create' => [
				'attr' => [
					'href' => [
						'url' => 'roles.php',
						'urlvars' => [
							'action' => 'create',
							'server' => $_REQUEST['server'],
						],
					],
				],
				'content' => $lang['strcreaterole'],
			],
		];
		$misc->printNavLinks($navlinks, 'roles-roles', get_defined_vars());
	}

	function render() {

		$misc->printHeader($lang['strroles']);
		$misc->printBody();

		switch ($action) {
			case 'create':
				$this->doCreate();
				break;
			case 'save_create':
				if (isset($_POST['create'])) {
					$this->doSaveCreate();
				} else {
					$this->doDefault();
				}

				break;
			case 'alter':
				$this->doAlter();
				break;
			case 'save_alter':
				if (isset($_POST['alter'])) {
					$this->doSaveAlter();
				} else {
					$this->doDefault();
				}

				break;
			case 'confirm_drop':
				$this->doDrop(true);
				break;
			case 'drop':
				if (isset($_POST['drop'])) {
					$this->doDrop(false);
				} else {
					$this->doDefault();
				}

				break;
			case 'properties':
				$this->doProperties();
				break;
			case 'confchangepassword':
				$this->doChangePassword(true);
				break;
			case 'changepassword':
				if (isset($_REQUEST['ok'])) {
					$this->doChangePassword(false);
				} else {
					$this->doAccount();
				}

				break;
			case 'account':
				$this->doAccount();
				break;
			default:
				$this->doDefault();
		}

		$misc->printFooter();
	}

}
