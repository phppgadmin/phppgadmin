<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class FulltextController extends BaseController {
	public $_name = 'FulltextController';

	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'fulltext');
		$this->printTabs('fulltext', 'ftsconfigs');
		$misc->printMsg($msg);

		$cfgs = $data->getFtsConfigurations(false);

		$columns = [
			'configuration' => [
				'title' => $lang['strftsconfig'],
				'field' => Decorator::field('name'),
				'url' => "fulltext.php?action=viewconfig&amp;{$misc->href}&amp;",
				'vars' => ['ftscfg' => 'name'],
			],
			'schema' => [
				'title' => $lang['strschema'],
				'field' => Decorator::field('schema'),
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
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'dropconfig',
							'ftscfg' => Decorator::field('name'),
						],
					],
				],
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'alterconfig',
							'ftscfg' => Decorator::field('name'),
						],
					],
				],
			],
		];

		echo $this->printTable($cfgs, $columns, $actions, 'fulltext-fulltext', $lang['strftsnoconfigs']);

		$navlinks = [
			'createconf' => [
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'createconfig',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strftscreateconfig'],
			],
		];

		$this->printNavLinks($navlinks, 'fulltext-fulltext', get_defined_vars());
	}

	public function doDropConfig($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('ftscfg');
			$misc->printTitle($lang['strdrop'], 'pg.ftscfg.drop');

			echo "<p>", sprintf($lang['strconfdropftsconfig'], $misc->printVal($_REQUEST['ftscfg'])), "</p>\n";

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"dropconfig\" />\n";
			echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
			echo "<input type=\"hidden\" name=\"ftscfg\" value=\"", htmlspecialchars($_REQUEST['ftscfg']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			$status = $data->dropFtsConfiguration($_POST['ftscfg'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doDefault($lang['strftsconfigdropped']);
			} else {
				$this->doDefault($lang['strftsconfigdroppedbad']);
			}

		}

	}

	public function doDropDict($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('ftscfg'); // TODO: change to smth related to dictionary
			$misc->printTitle($lang['strdrop'], 'pg.ftsdict.drop');

			echo "<p>", sprintf($lang['strconfdropftsdict'], $misc->printVal($_REQUEST['ftsdict'])), "</p>\n";

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"dropdict\" />\n";
			echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
			echo "<input type=\"hidden\" name=\"ftsdict\" value=\"", htmlspecialchars($_REQUEST['ftsdict']), "\" />\n";
			//echo "<input type=\"hidden\" name=\"ftscfg\" value=\"", htmlspecialchars($_REQUEST['ftscfg']), "\" />\n";
			echo "<input type=\"hidden\" name=\"prev_action\" value=\"viewdicts\" /></p>\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			$status = $data->dropFtsDictionary($_POST['ftsdict'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doViewDicts($lang['strftsdictdropped']);
			} else {
				$this->doViewDicts($lang['strftsdictdroppedbad']);
			}

		}

	}

/**
 * Displays a screen where one can enter a new FTS configuration
 */
	public function doCreateConfig($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		include_once BASE_PATH . '/classes/Gui.php';

		$server_info = $misc->getServerInfo();

		if (!isset($_POST['formName'])) {
			$_POST['formName'] = '';
		}

		if (!isset($_POST['formParser'])) {
			$_POST['formParser'] = '';
		}

		if (!isset($_POST['formTemplate'])) {
			$_POST['formTemplate'] = '';
		}

		if (!isset($_POST['formWithMap'])) {
			$_POST['formWithMap'] = '';
		}

		if (!isset($_POST['formComment'])) {
			$_POST['formComment'] = '';
		}

		// Fetch all FTS configurations from the database
		$ftscfgs = $data->getFtsConfigurations();
		// Fetch all FTS parsers from the database
		$ftsparsers = $data->getFtsParsers();

		$this->printTrail('schema');
		$misc->printTitle($lang['strftscreateconfig'], 'pg.ftscfg.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
		echo "<table>\n";
		/* conf name */
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formName']), "\" /></td>\n\t</tr>\n";

		// Template
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftstemplate']}</th>\n";
		echo "\t\t<td class=\"data1\">";

		$tpls   = [];
		$tplsel = '';
		while (!$ftscfgs->EOF) {
			$data->fieldClean($ftscfgs->fields['schema']);
			$data->fieldClean($ftscfgs->fields['name']);
			$tplname        = $ftscfgs->fields['schema'] . '.' . $ftscfgs->fields['name'];
			$tpls[$tplname] = serialize([
				'name' => $ftscfgs->fields['name'],
				'schema' => $ftscfgs->fields['schema'],
			]);
			if ($_POST['formTemplate'] == $tpls[$tplname]) {
				$tplsel = htmlspecialchars($tpls[$tplname]);
			}
			$ftscfgs->moveNext();
		}
		echo \PHPPgAdmin\GUI::printCombo($tpls, 'formTemplate', true, $tplsel, false);
		echo "\n\t\t</td>\n\t</tr>\n";

		// Parser
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftsparser']}</th>\n";
		echo "\t\t<td class=\"data1\">\n";
		$ftsparsers_ = [];
		$ftsparsel   = '';
		while (!$ftsparsers->EOF) {
			$data->fieldClean($ftsparsers->fields['schema']);
			$data->fieldClean($ftsparsers->fields['name']);
			$parsername = $ftsparsers->fields['schema'] . '.' . $ftsparsers->fields['name'];

			$ftsparsers_[$parsername] = serialize([
				'parser' => $ftsparsers->fields['name'],
				'schema' => $ftsparsers->fields['schema'],
			]);
			if ($_POST['formParser'] == $ftsparsers_[$parsername]) {
				$ftsparsel = htmlspecialchars($ftsparsers_[$parsername]);
			}
			$ftsparsers->moveNext();
		}
		echo \PHPPgAdmin\GUI::printCombo($ftsparsers_, 'formParser', true, $ftsparsel, false);
		echo "\n\t\t</td>\n\t</tr>\n";

		// Comment
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "\t\t<td class=\"data1\"><textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
		htmlspecialchars($_POST['formComment']), "</textarea></td>\n\t</tr>\n";

		echo "</table>\n";
		echo "<p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"createconfig\" />\n";
		echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"create\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new FTS configuration in the database
 */
	public function doSaveCreateConfig() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$err = '';
		// Check that they've given a name
		if ($_POST['formName'] == '') {
			$err .= "{$lang['strftsconfigneedsname']}<br />";
		}

		if (($_POST['formParser'] != '') && ($_POST['formTemplate'] != '')) {
			$err .= "{$lang['strftscantparsercopy']}<br />";
		}

		if ($err != '') {
			return doCreateConfig($err);
		}

		if ($_POST['formParser'] != '') {
			$formParser = unserialize($_POST['formParser']);
		} else {
			$formParser = '';
		}

		if ($_POST['formTemplate'] != '') {
			$formTemplate = unserialize($_POST['formTemplate']);
		} else {
			$formTemplate = '';
		}

		$status = $data->createFtsConfiguration($_POST['formName'], $formParser, $formTemplate, $_POST['formComment']);
		if ($status == 0) {
			$this->misc->setReloadBrowser(true);
			$this->doDefault($lang['strftsconfigcreated']);
		} else {
			$this->doCreateConfig($lang['strftsconfigcreatedbad']);
		}

	}

/**
 * Display a form to permit editing FTS configuration properies.
 */
	public function doAlterConfig($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('ftscfg');
		$misc->printTitle($lang['stralter'], 'pg.ftscfg.alter');
		$misc->printMsg($msg);

		$ftscfg = $data->getFtsConfigurationByName($_REQUEST['ftscfg']);
		if ($ftscfg->recordCount() > 0) {
			if (!isset($_POST['formComment'])) {
				$_POST['formComment'] = $ftscfg->fields['comment'];
			}

			if (!isset($_POST['ftscfg'])) {
				$_POST['ftscfg'] = $_REQUEST['ftscfg'];
			}

			if (!isset($_POST['formName'])) {
				$_POST['formName'] = $_REQUEST['ftscfg'];
			}

			if (!isset($_POST['formParser'])) {
				$_POST['formParser'] = '';
			}

			// Fetch all FTS parsers from the database
			$ftsparsers = $data->getFtsParsers();

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
			echo "<table>\n";

			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<input name=\"formName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['formName']), "\" />\n";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			// Comment
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data\">{$lang['strcomment']}</th>\n";
			echo "\t\t<td class=\"data1\"><textarea cols=\"32\" rows=\"3\"name=\"formComment\">", htmlspecialchars($_POST['formComment']), "</textarea></td>\n";
			echo "\t</tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alterconfig\" />\n";
			echo "<input type=\"hidden\" name=\"ftscfg\" value=\"", htmlspecialchars($_POST['ftscfg']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}
	}

/**
 * Save the form submission containing changes to a FTS configuration
 */
	public function doSaveAlterConfig() {
		$conf   = $this->conf;
		$misc   = $this->misc;
		$lang   = $this->lang;
		$data   = $misc->getDatabaseAccessor();
		$status = $data->updateFtsConfiguration($_POST['ftscfg'], $_POST['formComment'], $_POST['formName']);
		if ($status == 0) {
			$this->doDefault($lang['strftsconfigaltered']);
		} else {
			$this->doAlterConfig($lang['strftsconfigalteredbad']);
		}

	}

/**
 * View list of FTS parsers
 */
	public function doViewParsers($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'fulltext');
		$this->printTabs('fulltext', 'ftsparsers');
		$misc->printMsg($msg);

		$parsers = $data->getFtsParsers(false);

		$columns = [
			'schema' => [
				'title' => $lang['strschema'],
				'field' => Decorator::field('schema'),
			],
			'name' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('name'),
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('comment'),
			],
		];

		$actions = [];

		echo $this->printTable($parsers, $columns, $actions, 'fulltext-viewparsers', $lang['strftsnoparsers']);

		//TODO: navlink to "create parser"
	}

/**
 * View list of FTS dictionaries
 */
	public function doViewDicts($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'fulltext');
		$this->printTabs('fulltext', 'ftsdicts');
		$misc->printMsg($msg);

		$dicts = $data->getFtsDictionaries(false);

		$columns = [
			'schema' => [
				'title' => $lang['strschema'],
				'field' => Decorator::field('schema'),
			],
			'name' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('name'),
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
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'dropdict',
							'ftsdict' => Decorator::field('name'),
						],
					],
				],
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'alterdict',
							'ftsdict' => Decorator::field('name'),
						],
					],
				],
			],
		];

		echo $this->printTable($dicts, $columns, $actions, 'fulltext-viewdicts', $lang['strftsnodicts']);

		$navlinks = [
			'createdict' => [
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'createdict',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
						],
					],
				],
				'content' => $lang['strftscreatedict'],
			],
		];

		$this->printNavLinks($navlinks, 'fulltext-viewdicts', get_defined_vars());
	}

/**
 * View details of FTS configuration given
 */
	public function doViewConfig($ftscfg, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('ftscfg');
		$this->printTabs('schema', 'fulltext');
		$this->printTabs('fulltext', 'ftsconfigs');
		$misc->printMsg($msg);

		echo "<h3>{$lang['strftsconfigmap']}</h3>\n";

		$map = $data->getFtsConfigurationMap($ftscfg);

		$columns = [
			'name' => [
				'title' => $lang['strftsmapping'],
				'field' => Decorator::field('name'),
			],
			'dictionaries' => [
				'title' => $lang['strftsdicts'],
				'field' => Decorator::field('dictionaries'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('description'),
			],
		];

		$actions = [
			'drop' => [
				'multiaction' => 'dropmapping',
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'dropmapping',
							'mapping' => Decorator::field('name'),
							'ftscfg' => Decorator::field('cfgname'),
						],
					],
				],
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'altermapping',
							'mapping' => Decorator::field('name'),
							'ftscfg' => Decorator::field('cfgname'),
						],
					],
				],
			],
			'multiactions' => [
				'keycols' => ['mapping' => 'name'],
				'url' => 'fulltext.php',
				'default' => null,
				'vars' => ['ftscfg' => $ftscfg],
			],

		];

		echo $this->printTable($map, $columns, $actions, 'fulltext-viewconfig', $lang['strftsemptymap']);

		$navlinks = [
			'addmapping' => [
				'attr' => [
					'href' => [
						'url' => 'fulltext.php',
						'urlvars' => [
							'action' => 'addmapping',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'ftscfg' => $ftscfg,
						],
					],
				],
				'content' => $lang['strftsaddmapping'],
			],
		];

		$this->printNavLinks($navlinks, 'fulltext-viewconfig', get_defined_vars());
	}

/**
 * Displays a screen where one can enter a details of a new FTS dictionary
 */
	public function doCreateDict($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		include_once BASE_PATH . '/classes/Gui.php';

		$server_info = $misc->getServerInfo();

		if (!isset($_POST['formName'])) {
			$_POST['formName'] = '';
		}

		if (!isset($_POST['formIsTemplate'])) {
			$_POST['formIsTemplate'] = false;
		}

		if (!isset($_POST['formTemplate'])) {
			$_POST['formTemplate'] = '';
		}

		if (!isset($_POST['formLexize'])) {
			$_POST['formLexize'] = '';
		}

		if (!isset($_POST['formInit'])) {
			$_POST['formInit'] = '';
		}

		if (!isset($_POST['formOption'])) {
			$_POST['formOption'] = '';
		}

		if (!isset($_POST['formComment'])) {
			$_POST['formComment'] = '';
		}

		// Fetch all FTS dictionaries from the database
		$ftstpls = $data->getFtsDictionaryTemplates();

		$this->printTrail('schema');
		// TODO: create doc links
		$misc->printTitle($lang['strftscreatedict'], 'pg.ftsdict.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formName']), "\" />&nbsp;",
		"<input type=\"checkbox\" name=\"formIsTemplate\" id=\"formIsTemplate\"", $_POST['formIsTemplate'] ? ' checked="checked" ' : '', " />\n",
			"<label for=\"formIsTemplate\">{$lang['strftscreatedicttemplate']}</label></td>\n\t</tr>\n";

		// Template
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftstemplate']}</th>\n";
		echo "\t\t<td class=\"data1\">";
		$tpls   = [];
		$tplsel = '';
		while (!$ftstpls->EOF) {
			$data->fieldClean($ftstpls->fields['schema']);
			$data->fieldClean($ftstpls->fields['name']);
			$tplname        = $ftstpls->fields['schema'] . '.' . $ftstpls->fields['name'];
			$tpls[$tplname] = serialize([
				'name' => $ftstpls->fields['name'],
				'schema' => $ftstpls->fields['schema'],
			]);
			if ($_POST['formTemplate'] == $tpls[$tplname]) {
				$tplsel = htmlspecialchars($tpls[$tplname]);
			}
			$ftstpls->moveNext();
		}
		echo \PHPPgAdmin\GUI::printCombo($tpls, 'formTemplate', true, $tplsel, false);
		echo "\n\t\t</td>\n\t</tr>\n";

		// TODO: what about maxlengths?
		// Lexize
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftslexize']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formLexize\" size=\"32\" maxlength=\"1000\" value=\"",
		htmlspecialchars($_POST['formLexize']), "\" ", isset($_POST['formIsTemplate']) ? '' : ' disabled="disabled" ',
			"/></td>\n\t</tr>\n";

		// Init
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftsinit']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formInit\" size=\"32\" maxlength=\"1000\" value=\"",
		htmlspecialchars($_POST['formInit']), "\"", @$_POST['formIsTemplate'] ? '' : ' disabled="disabled" ',
			"/></td>\n\t</tr>\n";

		// Option
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strftsoptionsvalues']}</th>\n";
		echo "\t\t<td class=\"data1\"><input name=\"formOption\" size=\"32\" maxlength=\"1000\" value=\"",
		htmlspecialchars($_POST['formOption']), "\" /></td>\n\t</tr>\n";

		// Comment
		echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strcomment']}</th>\n";
		echo "\t\t<td class=\"data1\"><textarea name=\"formComment\" rows=\"3\" cols=\"32\">",
		htmlspecialchars($_POST['formComment']), "</textarea></td>\n\t</tr>\n";

		echo "</table>\n";
		echo "<p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"createdict\" />\n";
		echo "<input type=\"hidden\" name=\"database\" value=\"", htmlspecialchars($_REQUEST['database']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"create\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</p>\n";
		echo "</form>\n",
			"<script type=\"text/javascript\">
				function templateOpts() {
					isTpl = document.getElementsByName('formIsTemplate')[0].checked;
					$this->document.getElementsByName('formTemplate')[0].disabled = isTpl;
					$this->document.getElementsByName('formOption')[0].disabled = isTpl;
					$this->document.getElementsByName('formLexize')[0].disabled = !isTpl;
					$this->document.getElementsByName('formInit')[0].disabled = !isTpl;
				}

				$this->document.getElementsByName('formIsTemplate')[0].onchange = templateOpts;

				templateOpts();
			</script>\n";
	}

/**
 * Actually creates the new FTS dictionary in the database
 */
	public function doSaveCreateDict() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name
		if ($_POST['formName'] == '') {
			$this->doCreateDict($lang['strftsdictneedsname']);
		} else {

			if (!isset($_POST['formIsTemplate'])) {
				$_POST['formIsTemplate'] = false;
			}

			if (isset($_POST['formTemplate'])) {
				$formTemplate = unserialize($_POST['formTemplate']);
			} else {
				$formTemplate = '';
			}

			if (!isset($_POST['formLexize'])) {
				$_POST['formLexize'] = '';
			}

			if (!isset($_POST['formInit'])) {
				$_POST['formInit'] = '';
			}

			if (!isset($_POST['formOption'])) {
				$_POST['formOption'] = '';
			}

			$status = $data->createFtsDictionary($_POST['formName'], $_POST['formIsTemplate'],
				$formTemplate, $_POST['formLexize'],
				$_POST['formInit'], $_POST['formOption'], $_POST['formComment']
			);

			if ($status == 0) {
				$this->misc->setReloadBrowser(true);
				$this->doViewDicts($lang['strftsdictcreated']);
			} else {
				$this->doCreateDict($lang['strftsdictcreatedbad']);
			}

		}
	}

/**
 * Display a form to permit editing FTS dictionary properies.
 */
	public function doAlterDict($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('ftscfg'); // TODO: change to smth related to dictionary
		$misc->printTitle($lang['stralter'], 'pg.ftsdict.alter');
		$misc->printMsg($msg);

		$ftsdict = $data->getFtsDictionaryByName($_REQUEST['ftsdict']);
		if ($ftsdict->recordCount() > 0) {
			if (!isset($_POST['formComment'])) {
				$_POST['formComment'] = $ftsdict->fields['comment'];
			}

			if (!isset($_POST['ftsdict'])) {
				$_POST['ftsdict'] = $_REQUEST['ftsdict'];
			}

			if (!isset($_POST['formName'])) {
				$_POST['formName'] = $_REQUEST['ftsdict'];
			}

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
			echo "<table>\n";

			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<input name=\"formName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['formName']), "\" />\n";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			// Comment
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data\">{$lang['strcomment']}</th>\n";
			echo "\t\t<td class=\"data1\"><textarea cols=\"32\" rows=\"3\"name=\"formComment\">", htmlspecialchars($_POST['formComment']), "</textarea></td>\n";
			echo "\t</tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alterdict\" />\n";
			echo "<input type=\"hidden\" name=\"ftsdict\" value=\"", htmlspecialchars($_POST['ftsdict']), "\" />\n";
			echo "<input type=\"hidden\" name=\"prev_action\" value=\"viewdicts\" /></p>\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}
	}

/**
 * Save the form submission containing changes to a FTS dictionary
 */
	public function doSaveAlterDict() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->updateFtsDictionary($_POST['ftsdict'], $_POST['formComment'], $_POST['formName']);
		if ($status == 0) {
			$this->doViewDicts($lang['strftsdictaltered']);
		} else {
			$this->doAlterDict($lang['strftsdictalteredbad']);
		}

	}

/**
 * Show confirmation of drop and perform actual drop of FTS mapping
 */
	public function doDropMapping($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['mapping']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strftsspecifymappingtodrop']);
			return;
		}

		if (empty($_REQUEST['ftscfg'])) {
			$this->doDefault($lang['strftsspecifyconfigtoalter']);
			return;
		}

		if ($confirm) {
			$this->printTrail('ftscfg'); // TODO: proper breadcrumbs
			$misc->printTitle($lang['strdrop'], 'pg.ftscfg.alter');

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";

			// Case of multiaction drop
			if (isset($_REQUEST['ma'])) {

				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfdropftsmapping'], $misc->printVal($a['mapping']), $misc->printVal($_REQUEST['ftscfg'])), "</p>\n";
					printf('<input type="hidden" name="mapping[]" value="%s" />', htmlspecialchars($a['mapping']));
				}

			} else {
				echo "<p>", sprintf($lang['strconfdropftsmapping'], $misc->printVal($_REQUEST['mapping']), $misc->printVal($_REQUEST['ftscfg'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mapping\" value=\"", htmlspecialchars($_REQUEST['mapping']), "\" />\n";
			}

			echo "<input type=\"hidden\" name=\"ftscfg\" value=\"{$_REQUEST['ftscfg']}\" />\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"dropmapping\" />\n";
			echo "<input type=\"hidden\" name=\"prev_action\" value=\"viewconfig\" /></p>\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			// Case of multiaction drop
			if (is_array($_REQUEST['mapping'])) {
				$status = $data->changeFtsMapping($_REQUEST['ftscfg'], $_REQUEST['mapping'], 'drop');
				if ($status != 0) {
					$this->doViewConfig($_REQUEST['ftscfg'], $lang['strftsmappingdroppedbad']);
					return;
				}
				$this->doViewConfig($_REQUEST['ftscfg'], $lang['strftsmappingdropped']);
			} else {
				$status = $data->changeFtsMapping($_REQUEST['ftscfg'], [$_REQUEST['mapping']], 'drop');
				if ($status == 0) {
					$this->doViewConfig($_REQUEST['ftscfg'], $lang['strftsmappingdropped']);
				} else {
					$this->doViewConfig($_REQUEST['ftscfg'], $lang['strftsmappingdroppedbad']);
				}
			}
		}
	}

	public function doAlterMapping($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();
		$this->printTrail('ftscfg');
		$misc->printTitle($lang['stralter'], 'pg.ftscfg.alter');
		$misc->printMsg($msg);

		$ftsdicts = $data->getFtsDictionaries();
		if ($ftsdicts->recordCount() > 0) {
			if (!isset($_POST['formMapping'])) {
				$_POST['formMapping'] = @$_REQUEST['mapping'];
			}

			if (!isset($_POST['formDictionary'])) {
				$_POST['formDictionary'] = '';
			}

			if (!isset($_POST['ftscfg'])) {
				$_POST['ftscfg'] = $_REQUEST['ftscfg'];
			}

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";

			echo "<table>\n";
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strftsmapping']}</th>\n";
			echo "\t\t<td class=\"data1\">";

			// Case of multiaction drop
			if (isset($_REQUEST['ma'])) {
				$ma_mappings       = [];
				$ma_mappings_names = [];
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					printf('<input type="hidden" name="formMapping[]" value="%s" />', htmlspecialchars($a['mapping']));
					$ma_mappings[]       = $data->getFtsMappingByName($_POST['ftscfg'], $a['mapping']);
					$ma_mappings_names[] = $a['mapping'];
				}
				echo implode(", ", $ma_mappings_names);
			} else {
				$mapping = $data->getFtsMappingByName($_POST['ftscfg'], $_POST['formMapping']);
				echo $mapping->fields['name'];
				echo "<input type=\"hidden\" name=\"formMapping\" value=\"", htmlspecialchars($_POST['formMapping']), "\" />\n";
			}

			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			// Dictionary
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strftsdict']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<select name=\"formDictionary\">\n";
			while (!$ftsdicts->EOF) {
				$ftsdict = htmlspecialchars($ftsdicts->fields['name']);
				echo "\t\t\t\t<option value=\"{$ftsdict}\"",
				($ftsdict == $_POST['formDictionary'] || $ftsdict == @$mapping->fields['dictionaries'] || $ftsdict == @$ma_mappings[0]->fields['dictionaries']) ? ' selected="selected"' : '', ">{$ftsdict}</option>\n";
				$ftsdicts->moveNext();
			}

			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"altermapping\" />\n";
			echo "<input type=\"hidden\" name=\"ftscfg\" value=\"", htmlspecialchars($_POST['ftscfg']), "\" />\n";
			echo "<input type=\"hidden\" name=\"prev_action\" value=\"viewconfig\" /></p>\n";

			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strftsnodictionaries']}</p>\n";
		}
	}

/**
 * Save the form submission containing changes to a FTS mapping
 */
	public function doSaveAlterMapping() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$mappingArray = (is_array($_POST['formMapping']) ? $_POST['formMapping'] : [$_POST['formMapping']]);
		$status       = $data->changeFtsMapping($_POST['ftscfg'], $mappingArray, 'alter', $_POST['formDictionary']);
		if ($status == 0) {
			$this->doViewConfig($_POST['ftscfg'], $lang['strftsmappingaltered']);
		} else {
			$this->doAlterMapping($lang['strftsmappingalteredbad']);
		}

	}

/**
 * Show the form to enter parameters of a new FTS mapping
 */
	public function doAddMapping($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('ftscfg');
		$misc->printTitle($lang['stralter'], 'pg.ftscfg.alter');
		$misc->printMsg($msg);

		$ftsdicts = $data->getFtsDictionaries();
		if ($ftsdicts->recordCount() > 0) {
			if (!isset($_POST['formMapping'])) {
				$_POST['formMapping'] = '';
			}

			if (!isset($_POST['formDictionary'])) {
				$_POST['formDictionary'] = '';
			}

			if (!isset($_POST['ftscfg'])) {
				$_POST['ftscfg'] = $_REQUEST['ftscfg'];
			}

			$mappings = $data->getFtsMappings($_POST['ftscfg']);

			echo "<form action=\"/src/views/fulltext.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strftsmapping']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<select name=\"formMapping\">\n";
			while (!$mappings->EOF) {
				$mapping      = htmlspecialchars($mappings->fields['name']);
				$mapping_desc = htmlspecialchars($mappings->fields['description']);
				echo "\t\t\t\t<option value=\"{$mapping}\"",
				$mapping == $_POST['formMapping'] ? ' selected="selected"' : '', ">{$mapping}", $mapping_desc ? " - {$mapping_desc}" : "", "</option>\n";
				$mappings->moveNext();
			}
			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			// Dictionary
			echo "\t<tr>\n";
			echo "\t\t<th class=\"data left required\">{$lang['strftsdict']}</th>\n";
			echo "\t\t<td class=\"data1\">";
			echo "\t\t\t<select name=\"formDictionary\">\n";
			while (!$ftsdicts->EOF) {
				$ftsdict = htmlspecialchars($ftsdicts->fields['name']);
				echo "\t\t\t\t<option value=\"{$ftsdict}\"",
				$ftsdict == $_POST['formDictionary'] ? ' selected="selected"' : '', ">{$ftsdict}</option>\n";
				$ftsdicts->moveNext();
			}

			echo "\t\t</td>\n";
			echo "\t</tr>\n";

			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"addmapping\" />\n";
			echo "<input type=\"hidden\" name=\"ftscfg\" value=\"", htmlspecialchars($_POST['ftscfg']), "\" />\n";
			echo "<input type=\"hidden\" name=\"prev_action\" value=\"viewconfig\" /></p>\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"add\" value=\"{$lang['stradd']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strftsnodictionaries']}</p>\n";
		}
	}

/**
 * Save the form submission containing parameters of a new FTS mapping
 */
	public function doSaveAddMapping() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$mappingArray = (is_array($_POST['formMapping']) ? $_POST['formMapping'] : [$_POST['formMapping']]);
		$status       = $data->changeFtsMapping($_POST['ftscfg'], $mappingArray, 'add', $_POST['formDictionary']);
		if ($status == 0) {
			$this->doViewConfig($_POST['ftscfg'], $lang['strftsmappingadded']);
		} else {
			$this->doAddMapping($lang['strftsmappingaddedbad']);
		}

	}

}
