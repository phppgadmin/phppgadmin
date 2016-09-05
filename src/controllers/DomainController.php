<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class DomainController extends BaseController {
	public $_name = 'DomainController';

/**
 * Function to save after altering a domain
 */
	public function doSaveAlter() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->alterDomain($_POST['domain'], $_POST['domdefault'],
			isset($_POST['domnotnull']), $_POST['domowner']);
		if ($status == 0) {
			$this->doProperties($lang['strdomainaltered']);
		} else {
			$this->doAlter($lang['strdomainalteredbad']);
		}

	}

/**
 * Allow altering a domain
 */
	public function doAlter($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('domain');
		$misc->printTitle($lang['stralter'], 'pg.domain.alter');
		$misc->printMsg($msg);

		// Fetch domain info
		$domaindata = $data->getDomain($_REQUEST['domain']);
		// Fetch all users
		$users = $data->getUsers();

		if ($domaindata->recordCount() > 0) {
			if (!isset($_POST['domname'])) {
				$_POST['domtype']                 = $domaindata->fields['domtype'];
				$_POST['domdefault']              = $domaindata->fields['domdef'];
				$domaindata->fields['domnotnull'] = $data->phpBool($domaindata->fields['domnotnull']);
				if ($domaindata->fields['domnotnull']) {
					$_POST['domnotnull'] = 'on';
				}

				$_POST['domowner'] = $domaindata->fields['domowner'];
			}

			// Display domain info
			echo "<form action=\"/src/views/domains.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "<tr><th class=\"data left required\" style=\"width: 70px\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domname']), "</td></tr>\n";
			echo "<tr><th class=\"data left required\">{$lang['strtype']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domtype']), "</td></tr>\n";
			echo "<tr><th class=\"data left\"><label for=\"domnotnull\">{$lang['strnotnull']}</label></th>\n";
			echo "<td class=\"data1\"><input type=\"checkbox\" id=\"domnotnull\" name=\"domnotnull\"", (isset($_POST['domnotnull']) ? ' checked="checked"' : ''), " /></td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strdefault']}</th>\n";
			echo "<td class=\"data1\"><input name=\"domdefault\" size=\"32\" value=\"",
			htmlspecialchars($_POST['domdefault']), "\" /></td></tr>\n";
			echo "<tr><th class=\"data left required\">{$lang['strowner']}</th>\n";
			echo "<td class=\"data1\"><select name=\"domowner\">";
			while (!$users->EOF) {
				$uname = $users->fields['usename'];
				echo "<option value=\"", htmlspecialchars($uname), "\"",
				($uname == $_POST['domowner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
				$users->moveNext();
			}
			echo "</select></td></tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_alter\" />\n";
			echo "<input type=\"hidden\" name=\"domain\" value=\"", htmlspecialchars($_REQUEST['domain']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

/**
 * Confirm and then actually add a CHECK constraint
 */
	function addCheck($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['name'])) {
			$_POST['name'] = '';
		}

		if (!isset($_POST['definition'])) {
			$_POST['definition'] = '';
		}

		if ($confirm) {
			$this->printTrail('domain');
			$misc->printTitle($lang['straddcheck'], 'pg.constraint.check');
			$misc->printMsg($msg);

			echo "<form action=\"/src/views/domains.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strname']}</th>\n";
			echo "<th class=\"data required\">{$lang['strdefinition']}</th></tr>\n";

			echo "<tr><td class=\"data1\"><input name=\"name\" size=\"16\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" /></td>\n";

			echo "<td class=\"data1\">(<input name=\"definition\" size=\"32\" value=\"",
			htmlspecialchars($_POST['definition']), "\" />)</td></tr>\n";
			echo "</table>\n";

			echo "<p><input type=\"hidden\" name=\"action\" value=\"save_add_check\" />\n";
			echo "<input type=\"hidden\" name=\"domain\" value=\"", htmlspecialchars($_REQUEST['domain']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"add\" value=\"{$lang['stradd']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";

		} else {
			if (trim($_POST['definition']) == '') {
				$this->addCheck(true, $lang['strcheckneedsdefinition']);
			} else {
				$status = $data->addDomainCheckConstraint($_POST['domain'],
					$_POST['definition'], $_POST['name']);
				if ($status == 0) {
					$this->doProperties($lang['strcheckadded']);
				} else {
					$this->addCheck(true, $lang['strcheckaddedbad']);
				}

			}
		}
	}

/**
 * Show confirmation of drop constraint and perform actual drop
 */
	public function doDropConstraint($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('domain');
			$misc->printTitle($lang['strdrop'], 'pg.constraint.drop');
			$misc->printMsg($msg);

			echo "<p>", sprintf($lang['strconfdropconstraint'], $misc->printVal($_REQUEST['constraint']),
				$misc->printVal($_REQUEST['domain'])), "</p>\n";
			echo "<form action=\"/src/views/domains.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"drop_con\" />\n";
			echo "<input type=\"hidden\" name=\"domain\" value=\"", htmlspecialchars($_REQUEST['domain']), "\" />\n";
			echo "<input type=\"hidden\" name=\"constraint\" value=\"", htmlspecialchars($_REQUEST['constraint']), "\" />\n";
			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->dropDomainConstraint($_POST['domain'], $_POST['constraint'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doProperties($lang['strconstraintdropped']);
			} else {
				$this->doDropConstraint(true, $lang['strconstraintdroppedbad']);
			}

		}

	}

/**
 * Show properties for a domain.  Allow manipulating constraints as well.
 */
	public function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('domain');
		$misc->printTitle($lang['strproperties'], 'pg.domain');
		$misc->printMsg($msg);

		$domaindata = $data->getDomain($_REQUEST['domain']);

		if ($domaindata->recordCount() > 0) {
			// Show comment if any
			if ($domaindata->fields['domcomment'] !== null) {
				echo "<p class=\"comment\">", $misc->printVal($domaindata->fields['domcomment']), "</p>\n";
			}

			// Display domain info
			$domaindata->fields['domnotnull'] = $data->phpBool($domaindata->fields['domnotnull']);
			echo "<table>\n";
			echo "<tr><th class=\"data left\" style=\"width: 70px\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domname']), "</td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strtype']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domtype']), "</td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strnotnull']}</th>\n";
			echo "<td class=\"data1\">", ($domaindata->fields['domnotnull'] ? 'NOT NULL' : ''), "</td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strdefault']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domdef']), "</td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strowner']}</th>\n";
			echo "<td class=\"data1\">", $misc->printVal($domaindata->fields['domowner']), "</td></tr>\n";
			echo "</table>\n";

			// Display domain constraints
			echo "<h3>{$lang['strconstraints']}</h3>\n";
			if ($data->hasDomainConstraints()) {
				$domaincons = $data->getDomainConstraints($_REQUEST['domain']);

				$columns = [
					'name' => [
						'title' => $lang['strname'],
						'field' => Decorator::field('conname'),
					],
					'definition' => [
						'title' => $lang['strdefinition'],
						'field' => Decorator::field('consrc'),
					],
					'actions' => [
						'title' => $lang['stractions'],
					],
				];

				$actions = [
					'drop' => [
						'content' => $lang['strdrop'],
						'attr' => [
							'href' => [
								'url' => 'domains.php',
								'urlvars' => [
									'action' => 'confirm_drop_con',
									'domain' => $_REQUEST['domain'],
									'constraint' => Decorator::field('conname'),
									'type' => Decorator::field('contype'),
								],
							],
						],
					],
				];

				echo $this->printTable($domaincons, $columns, $actions, 'domains-properties', $lang['strnodata']);
			}
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

		$navlinks = [
			'drop' => [
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'domain' => $_REQUEST['domain'],
						],
					],
				],
				'content' => $lang['strdrop'],
			],
		];
		if ($data->hasAlterDomains()) {
			$navlinks['addcheck'] = [
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'add_check',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'domain' => $_REQUEST['domain'],
						],
					],
				],
				'content' => $lang['straddcheck'],
			];
			$navlinks['alter'] = [
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'alter',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'domain' => $_REQUEST['domain'],
						],
					],
				],
				'content' => $lang['stralter'],
			];
		}

		$this->printNavLinks($navlinks, 'domains-properties', get_defined_vars());
	}

/**
 * Show confirmation of drop and perform actual drop
 */
	public function doDrop($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('domain');
			$misc->printTitle($lang['strdrop'], 'pg.domain.drop');

			echo "<p>", sprintf($lang['strconfdropdomain'], $misc->printVal($_REQUEST['domain'])), "</p>\n";
			echo "<form action=\"/src/views/domains.php\" method=\"post\">\n";
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /><label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"domain\" value=\"", htmlspecialchars($_REQUEST['domain']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			$status = $data->dropDomain($_POST['domain'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doDefault($lang['strdomaindropped']);
			} else {
				$this->doDefault($lang['strdomaindroppedbad']);
			}

		}

	}

/**
 * Displays a screen where they can enter a new domain
 */
	public function doCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['domname'])) {
			$_POST['domname'] = '';
		}

		if (!isset($_POST['domtype'])) {
			$_POST['domtype'] = '';
		}

		if (!isset($_POST['domlength'])) {
			$_POST['domlength'] = '';
		}

		if (!isset($_POST['domarray'])) {
			$_POST['domarray'] = '';
		}

		if (!isset($_POST['domdefault'])) {
			$_POST['domdefault'] = '';
		}

		if (!isset($_POST['domcheck'])) {
			$_POST['domcheck'] = '';
		}

		$types = $data->getTypes(true);

		$this->printTrail('schema');
		$misc->printTitle($lang['strcreatedomain'], 'pg.domain.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/domains.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=\"data left required\" style=\"width: 70px\">{$lang['strname']}</th>\n";
		echo "<td class=\"data1\"><input name=\"domname\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['domname']), "\" /></td></tr>\n";
		echo "<tr><th class=\"data left required\">{$lang['strtype']}</th>\n";
		echo "<td class=\"data1\">\n";
		// Output return type list
		echo "<select name=\"domtype\">\n";
		while (!$types->EOF) {
			echo "<option value=\"", htmlspecialchars($types->fields['typname']), "\"",
			($types->fields['typname'] == $_POST['domtype']) ? ' selected="selected"' : '', ">",
			$misc->printVal($types->fields['typname']), "</option>\n";
			$types->moveNext();
		}
		echo "</select>\n";

		// Type length
		echo "<input type=\"text\" size=\"4\" name=\"domlength\" value=\"", htmlspecialchars($_POST['domlength']), "\" />";

		// Output array type selector
		echo "<select name=\"domarray\">\n";
		echo "<option value=\"\"", ($_POST['domarray'] == '') ? ' selected="selected"' : '', "></option>\n";
		echo "<option value=\"[]\"", ($_POST['domarray'] == '[]') ? ' selected="selected"' : '', ">[ ]</option>\n";
		echo "</select></td></tr>\n";

		echo "<tr><th class=\"data left\"><label for=\"domnotnull\">{$lang['strnotnull']}</label></th>\n";
		echo "<td class=\"data1\"><input type=\"checkbox\" id=\"domnotnull\" name=\"domnotnull\"",
		(isset($_POST['domnotnull']) ? ' checked="checked"' : ''), " /></td></tr>\n";
		echo "<tr><th class=\"data left\">{$lang['strdefault']}</th>\n";
		echo "<td class=\"data1\"><input name=\"domdefault\" size=\"32\" value=\"",
		htmlspecialchars($_POST['domdefault']), "\" /></td></tr>\n";
		if ($data->hasDomainConstraints()) {
			echo "<tr><th class=\"data left\">{$lang['strconstraints']}</th>\n";
			echo "<td class=\"data1\">CHECK (<input name=\"domcheck\" size=\"32\" value=\"",
			htmlspecialchars($_POST['domcheck']), "\" />)</td></tr>\n";
		}
		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new domain in the database
 */
	public function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['domcheck'])) {
			$_POST['domcheck'] = '';
		}

		// Check that they've given a name and a definition
		if ($_POST['domname'] == '') {
			$this->doCreate($lang['strdomainneedsname']);
		} else {
			$status = $data->createDomain($_POST['domname'], $_POST['domtype'], $_POST['domlength'], $_POST['domarray'] != '',
				isset($_POST['domnotnull']), $_POST['domdefault'], $_POST['domcheck']);
			if ($status == 0) {
				$this->doDefault($lang['strdomaincreated']);
			} else {
				$this->doCreate($lang['strdomaincreatedbad']);
			}

		}
	}

/**
 * Show default list of domains in the database
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'domains');
		$misc->printMsg($msg);

		$domains = $data->getDomains();

		$columns = [
			'domain' => [
				'title' => $lang['strdomain'],
				'field' => Decorator::field('domname'),
				'url' => "domains.php?action=properties&amp;{$misc->href}&amp;",
				'vars' => ['domain' => 'domname'],
			],
			'type' => [
				'title' => $lang['strtype'],
				'field' => Decorator::field('domtype'),
			],
			'notnull' => [
				'title' => $lang['strnotnull'],
				'field' => Decorator::field('domnotnull'),
				'type' => 'bool',
				'params' => ['true' => 'NOT NULL', 'false' => ''],
			],
			'default' => [
				'title' => $lang['strdefault'],
				'field' => Decorator::field('domdef'),
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('domowner'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('domcomment'),
			],
		];

		$actions = [
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'alter',
							'domain' => Decorator::field('domname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'domain' => Decorator::field('domname'),
						],
					],
				],
			],
		];

		if (!$data->hasAlterDomains()) {
			unset($actions['alter']);
		}

		echo $this->printTable($domains, $columns, $actions, 'domains-domains', $lang['strnodomains']);

		$navlinks = [
			'create' => [
				'attr' => [
					'href' => [
						'url' => 'domains.php',
						'urlvars' => [
							'action' => 'create',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strcreatedomain'],
			],
		];
		$this->printNavLinks($navlinks, 'domains-domains', get_defined_vars());
	}
}
