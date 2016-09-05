<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class TriggerController extends BaseController {
	public $_name = 'TriggerController';

/**
 * Function to save after altering a trigger
 */
	public function doSaveAlter() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->alterTrigger($_POST['table'], $_POST['trigger'], $_POST['name']);
		if ($status == 0) {
			$this->doDefault($lang['strtriggeraltered']);
		} else {
			$this->doAlter($lang['strtriggeralteredbad']);
		}

	}

/**
 * Function to allow altering of a trigger
 */
	public function doAlter($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('trigger');
		$misc->printTitle($lang['stralter'], 'pg.trigger.alter');
		$misc->printMsg($msg);

		$triggerdata = $data->getTrigger($_REQUEST['table'], $_REQUEST['trigger']);

		if ($triggerdata->recordCount() > 0) {

			if (!isset($_POST['name'])) {
				$_POST['name'] = $triggerdata->fields['tgname'];
			}

			echo "<form action=\"/src/views/triggers.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "<tr><th class=\"data\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" />\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"trigger\" value=\"", htmlspecialchars($_REQUEST['trigger']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['strok']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
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

		if ($confirm) {
			$this->printTrail('trigger');
			$misc->printTitle($lang['strdrop'], 'pg.trigger.drop');

			echo "<p>", sprintf($lang['strconfdroptrigger'], $misc->printVal($_REQUEST['trigger']),
				$misc->printVal($_REQUEST['table'])), "</p>\n";

			echo "<form action=\"/src/views/triggers.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"trigger\" value=\"", htmlspecialchars($_REQUEST['trigger']), "\" />\n";
			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->dropTrigger($_POST['trigger'], $_POST['table'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doDefault($lang['strtriggerdropped']);
			} else {
				$this->doDefault($lang['strtriggerdroppedbad']);
			}

		}

	}

/**
 * Show confirmation of enable trigger and perform enabling the trigger
 */
	public function doEnable($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('trigger');
			$misc->printTitle($lang['strenable'], 'pg.table.alter');

			echo "<p>", sprintf($lang['strconfenabletrigger'], $misc->printVal($_REQUEST['trigger']),
				$misc->printVal($_REQUEST['table'])), "</p>\n";

			echo "<form action=\"/src/views/triggers.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"enable\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"trigger\" value=\"", htmlspecialchars($_REQUEST['trigger']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->enableTrigger($_POST['trigger'], $_POST['table']);
			if ($status == 0) {
				$this->doDefault($lang['strtriggerenabled']);
			} else {
				$this->doDefault($lang['strtriggerenabledbad']);
			}

		}

	}

/**
 * Show confirmation of disable trigger and perform disabling the trigger
 */
	public function doDisable($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('trigger');
			$misc->printTitle($lang['strdisable'], 'pg.table.alter');

			echo "<p>", sprintf($lang['strconfdisabletrigger'], $misc->printVal($_REQUEST['trigger']),
				$misc->printVal($_REQUEST['table'])), "</p>\n";

			echo "<form action=\"/src/views/triggers.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"disable\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"trigger\" value=\"", htmlspecialchars($_REQUEST['trigger']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->disableTrigger($_POST['trigger'], $_POST['table']);
			if ($status == 0) {
				$this->doDefault($lang['strtriggerdisabled']);
			} else {
				$this->doDefault($lang['strtriggerdisabledbad']);
			}

		}

	}

/**
 * Let them create s.th.
 */
	public function doCreate($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('table');
		$misc->printTitle($lang['strcreatetrigger'], 'pg.trigger.create');
		$misc->printMsg($msg);

		// Get all the functions that can be used in triggers
		$funcs = $data->getTriggerFunctions();
		if ($funcs->recordCount() == 0) {
			$this->doDefault($lang['strnofunctions']);
			return;
		}

		/* Populate functions */
		$sel0 = new \PHPPgAdmin\XHtml\XHTML_Select('formFunction');
		while (!$funcs->EOF) {
			$sel0->add(new \PHPPgAdmin\XHtml\XHTML_Option($funcs->fields['proname']));
			$funcs->moveNext();
		}

		/* Populate times */
		$sel1 = new \PHPPgAdmin\XHtml\XHTML_Select('formExecTime');
		$sel1->set_data($data->triggerExecTimes);

		/* Populate events */
		$sel2 = new \PHPPgAdmin\XHtml\XHTML_Select('formEvent');
		$sel2->set_data($data->triggerEvents);

		/* Populate occurences */
		$sel3 = new \PHPPgAdmin\XHtml\XHTML_Select('formFrequency');
		$sel3->set_data($data->triggerFrequency);

		echo "<form action=\"/src/views/triggers.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr>\n";
		echo "		<th class=\"data\">{$lang['strname']}</th>\n";
		echo "		<th class=\"data\">{$lang['strwhen']}</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "		<td class=\"data1\"> <input type=\"text\" name=\"formTriggerName\" size=\"32\" /></td>\n";
		echo "		<td class=\"data1\"> ", $sel1->fetch(), "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "    <th class=\"data\">{$lang['strevent']}</th>\n";
		echo "    <th class=\"data\">{$lang['strforeach']}</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "     <td class=\"data1\"> ", $sel2->fetch(), "</td>\n";
		echo "     <td class=\"data1\"> ", $sel3->fetch(), "</td>\n";
		echo "</tr>\n";
		echo "<tr><th class=\"data\"> {$lang['strfunction']}</th>\n";
		echo "<th class=\"data\"> {$lang['strarguments']}</th></tr>\n";
		echo "<tr><td class=\"data1\">", $sel0->fetch(), "</td>\n";
		echo "<td class=\"data1\">(<input type=\"text\" name=\"formTriggerArgs\" size=\"32\" />)</td>\n";
		echo "</tr></table>\n";
		echo "<p><input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"save_create\" />\n";
		echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
		echo $misc->form;
		echo "</form>\n";
	}

/**
 * Actually creates the new trigger in the database
 */
	public function doSaveCreate() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name and a definition

		if ($_POST['formFunction'] == '') {
			$this->doCreate($lang['strtriggerneedsfunc']);
		} elseif ($_POST['formTriggerName'] == '') {
			$this->doCreate($lang['strtriggerneedsname']);
		} elseif ($_POST['formEvent'] == '') {
			$this->doCreate();
		} else {
			$status = $data->createTrigger($_POST['formTriggerName'], $_POST['table'],
				$_POST['formFunction'], $_POST['formExecTime'], $_POST['formEvent'],
				$_POST['formFrequency'], $_POST['formTriggerArgs']);
			if ($status == 0) {
				$this->doDefault($lang['strtriggercreated']);
			} else {
				$this->doCreate($lang['strtriggercreatedbad']);
			}

		}
	}

/**
 * List all the triggers on the table
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$tgPre = function (&$rowdata, $actions) use ($data) {
			// toggle enable/disable trigger per trigger
			if (!$data->phpBool($rowdata->fields["tgenabled"])) {
				unset($actions['disable']);
			} else {
				unset($actions['enable']);
			}
			return $actions;
		};

		$this->printTrail('table');
		$this->printTabs('table', 'triggers');
		$misc->printMsg($msg);

		$triggers = $data->getTriggers($_REQUEST['table']);

		$columns = [
			'trigger' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('tgname'),
			],
			'definition' => [
				'title' => $lang['strdefinition'],
				'field' => Decorator::field('tgdef'),
			],
			'function' => [
				'title' => $lang['strfunction'],
				'field' => Decorator::field('proproto'),
				'url' => "functions.php?action=properties&amp;server={$_REQUEST['server']}&amp;database={$_REQUEST['database']}&amp;",
				'vars' => [
					'schema' => 'pronamespace',
					'function' => 'proproto',
					'function_oid' => 'prooid',
				],
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
						'url' => 'triggers.php',
						'urlvars' => [
							'action' => 'confirm_alter',
							'table' => $_REQUEST['table'],
							'trigger' => Decorator::field('tgname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'triggers.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'table' => $_REQUEST['table'],
							'trigger' => Decorator::field('tgname'),
						],
					],
				],
			],
		];
		if ($data->hasDisableTriggers()) {
			$actions['enable'] = [
				'content' => $lang['strenable'],
				'attr' => [
					'href' => [
						'url' => 'triggers.php',
						'urlvars' => [
							'action' => 'confirm_enable',
							'table' => $_REQUEST['table'],
							'trigger' => Decorator::field('tgname'),
						],
					],
				],
			];
			$actions['disable'] = [
				'content' => $lang['strdisable'],
				'attr' => [
					'href' => [
						'url' => 'triggers.php',
						'urlvars' => [
							'action' => 'confirm_disable',
							'table' => $_REQUEST['table'],
							'trigger' => Decorator::field('tgname'),
						],
					],
				],
			];
		}

		echo $this->printTable($triggers, $columns, $actions, 'triggers-triggers', $lang['strnotriggers'], $tgPre);

		$this->printNavLinks(['create' => [
			'attr' => [
				'href' => [
					'url' => 'triggers.php',
					'urlvars' => [
						'action' => 'create',
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'table' => $_REQUEST['table'],
					],
				],
			],
			'content' => $lang['strcreatetrigger'],
		]], 'triggers-triggers', get_defined_vars());
	}
}
