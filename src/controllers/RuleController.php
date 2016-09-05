<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class RuleController extends BaseController {
	public $_name = 'RuleController';

/**
 * Confirm and then actually create a rule
 */
	function createRule($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['name'])) {
			$_POST['name'] = '';
		}

		if (!isset($_POST['event'])) {
			$_POST['event'] = '';
		}

		if (!isset($_POST['where'])) {
			$_POST['where'] = '';
		}

		if (!isset($_POST['type'])) {
			$_POST['type'] = 'SOMETHING';
		}

		if (!isset($_POST['raction'])) {
			$_POST['raction'] = '';
		}

		if ($confirm) {
			$this->printTrail($_REQUEST['subject']);
			$misc->printTitle($lang['strcreaterule'], 'pg.rule.create');
			$misc->printMsg($msg);

			echo "<form action=\"/src/views/rules.php\" method=\"post\">\n";
			echo "<table>\n";
			echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\"><input name=\"name\" size=\"16\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" /></td></tr>\n";
			echo "<tr><th class=\"data left required\">{$lang['strevent']}</th>\n";
			echo "<td class=\"data1\"><select name=\"event\">\n";
			foreach ($data->rule_events as $v) {
				echo "<option value=\"{$v}\"", ($v == $_POST['event']) ? ' selected="selected"' : '',
					">{$v}</option>\n";
			}
			echo "</select></td></tr>\n";
			echo "<tr><th class=\"data left\">{$lang['strwhere']}</th>\n";
			echo "<td class=\"data1\"><input name=\"where\" size=\"32\" value=\"",
			htmlspecialchars($_POST['where']), "\" /></td></tr>\n";
			echo "<tr><th class=\"data left\"><label for=\"instead\">{$lang['strinstead']}</label></th>\n";
			echo "<td class=\"data1\">";
			echo "<input type=\"checkbox\" id=\"instead\" name=\"instead\" ", (isset($_POST['instead'])) ? ' checked="checked"' : '', " />\n";
			echo "</td></tr>\n";
			echo "<tr><th class=\"data left required\">{$lang['straction']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<input type=\"radio\" id=\"type1\" name=\"type\" value=\"NOTHING\"", ($_POST['type'] == 'NOTHING') ? ' checked="checked"' : '', " /> <label for=\"type1\">NOTHING</label><br />\n";
			echo "<input type=\"radio\" name=\"type\" value=\"SOMETHING\"", ($_POST['type'] == 'SOMETHING') ? ' checked="checked"' : '', " />\n";
			echo "(<input name=\"raction\" size=\"32\" value=\"",
			htmlspecialchars($_POST['raction']), "\" />)</td></tr>\n";
			echo "</table>\n";

			echo "<input type=\"hidden\" name=\"action\" value=\"save_create_rule\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"", htmlspecialchars($_REQUEST['subject']), "\" />\n";
			echo "<input type=\"hidden\" name=\"", htmlspecialchars($_REQUEST['subject']),
			"\" value=\"", htmlspecialchars($_REQUEST[$_REQUEST['subject']]), "\" />\n";
			echo $misc->form;
			echo "<p><input type=\"submit\" name=\"ok\" value=\"{$lang['strcreate']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";

		} else {
			if (trim($_POST['name']) == '') {
				$this->createRule(true, $lang['strruleneedsname']);
			} else {
				$status = $data->createRule($_POST['name'],
					$_POST['event'], $_POST[$_POST['subject']], $_POST['where'],
					isset($_POST['instead']), $_POST['type'], $_POST['raction']);
				if ($status == 0) {
					$this->doDefault($lang['strrulecreated']);
				} else {
					$this->createRule(true, $lang['strrulecreatedbad']);
				}

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

		if ($confirm) {
			$this->printTrail($_REQUEST['subject']);
			$misc->printTitle($lang['strdrop'], 'pg.rule.drop');

			echo "<p>", sprintf($lang['strconfdroprule'], $misc->printVal($_REQUEST['rule']),
				$misc->printVal($_REQUEST[$_REQUEST['reltype']])), "</p>\n";

			echo "<form action=\"/src/views/rules.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"", htmlspecialchars($_REQUEST['reltype']), "\" />\n";
			echo "<input type=\"hidden\" name=\"", htmlspecialchars($_REQUEST['reltype']),
			"\" value=\"", htmlspecialchars($_REQUEST[$_REQUEST['reltype']]), "\" />\n";
			echo "<input type=\"hidden\" name=\"rule\" value=\"", htmlspecialchars($_REQUEST['rule']), "\" />\n";
			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->dropRule($_POST['rule'], $_POST[$_POST['subject']], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doDefault($lang['strruledropped']);
			} else {
				$this->doDefault($lang['strruledroppedbad']);
			}

		}

	}

/**
 * List all the rules on the table
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail($_REQUEST['subject']);
		$this->printTabs($_REQUEST['subject'], 'rules');
		$misc->printMsg($msg);

		$rules = $data->getRules($_REQUEST[$_REQUEST['subject']]);

		$columns = [
			'rule' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('rulename'),
			],
			'definition' => [
				'title' => $lang['strdefinition'],
				'field' => Decorator::field('definition'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
		];

		$subject = urlencode($_REQUEST['subject']);
		$object  = urlencode($_REQUEST[$_REQUEST['subject']]);

		$actions = [
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'rules.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'reltype' => $subject,
							$subject => $object,
							'subject' => 'rule',
							'rule' => Decorator::field('rulename'),
						],
					],
				],
			],
		];

		echo $this->printTable($rules, $columns, $actions, 'rules-rules', $lang['strnorules']);

		$misc->printNavLinks(['create' => [
			'attr' => [
				'href' => [
					'url' => 'rules.php',
					'urlvars' => [
						'action' => 'create_rule',
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						$subject => $object,
						'subject' => $subject,
					],
				],
			],
			'content' => $lang['strcreaterule'],
		]], 'rules-rules', get_defined_vars());
	}

}
