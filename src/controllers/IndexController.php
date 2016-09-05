<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class IndexController extends BaseController {
	public $_name = 'IndexController';

	/**
	 * Show confirmation of cluster index and perform actual cluster
	 */
	public function doClusterIndex($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			// Default analyze to on
			$_REQUEST['analyze'] = true;

			$this->printTrail('index');
			$misc->printTitle($lang['strclusterindex'], 'pg.index.cluster');

			echo "<p>", sprintf($lang['strconfcluster'], $misc->printVal($_REQUEST['index'])), "</p>\n";

			echo "<form action=\"/src/views/indexes.php\" method=\"post\">\n";
			echo "<p><input type=\"checkbox\" id=\"analyze\" name=\"analyze\"", (isset($_REQUEST['analyze']) ? ' checked="checked"' : ''), " /><label for=\"analyze\">{$lang['stranalyze']}</label></p>\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"cluster_index\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"index\" value=\"", htmlspecialchars($_REQUEST['index']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"cluster\" value=\"{$lang['strclusterindex']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->clusterIndex($_POST['table'], $_POST['index']);
			if ($status == 0) {
				if (isset($_POST['analyze'])) {
					$status = $data->analyzeDB($_POST['table']);
					if ($status == 0) {
						$this->doDefault($lang['strclusteredgood'] . ' ' . $lang['stranalyzegood']);
					} else {
						$this->doDefault($lang['stranalyzebad']);
					}

				} else {
					$this->doDefault($lang['strclusteredgood']);
				}
			} else {
				$this->doDefault($lang['strclusteredbad']);
			}

		}

	}

	public function doReindex() {
		$conf   = $this->conf;
		$misc   = $this->misc;
		$lang   = $this->lang;
		$data   = $misc->getDatabaseAccessor();
		$status = $data->reindex('INDEX', $_REQUEST['index']);
		if ($status == 0) {
			$this->doDefault($lang['strreindexgood']);
		} else {
			$this->doDefault($lang['strreindexbad']);
		}

	}

/**
 * Displays a screen where they can enter a new index
 */
	public function doCreateIndex($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['formIndexName'])) {
			$_POST['formIndexName'] = '';
		}

		if (!isset($_POST['formIndexType'])) {
			$_POST['formIndexType'] = null;
		}

		if (!isset($_POST['formCols'])) {
			$_POST['formCols'] = '';
		}

		if (!isset($_POST['formWhere'])) {
			$_POST['formWhere'] = '';
		}

		if (!isset($_POST['formSpc'])) {
			$_POST['formSpc'] = '';
		}

		$attrs = $data->getTableAttributes($_REQUEST['table']);
		// Fetch all tablespaces from the database
		if ($data->hasTablespaces()) {
			$tablespaces = $data->getTablespaces();
		}

		$this->printTrail('table');
		$misc->printTitle($lang['strcreateindex'], 'pg.index.create');
		$misc->printMsg($msg);

		$selColumns = new \PHPPgAdmin\XHtml\XHTML_Select("TableColumnList", true, 10);
		$selColumns->set_style("width: 10em;");

		if ($attrs->recordCount() > 0) {
			while (!$attrs->EOF) {
				$selColumns->add(new \PHPPgAdmin\XHtml\XHTML_Option($attrs->fields['attname']));
				$attrs->moveNext();
			}
		}

		$selIndex = new \PHPPgAdmin\XHtml\XHTML_Select("IndexColumnList[]", true, 10);
		$selIndex->set_style("width: 10em;");
		$selIndex->set_attribute("id", "IndexColumnList");
		$buttonAdd = new \PHPPgAdmin\XHtml\XHTML_Button("add", ">>");
		$buttonAdd->set_attribute("onclick", "buttonPressed(this);");
		$buttonAdd->set_attribute("type", "button");

		$buttonRemove = new \PHPPgAdmin\XHtml\XHTML_Button("remove", "<<");
		$buttonRemove->set_attribute("onclick", "buttonPressed(this);");
		$buttonRemove->set_attribute("type", "button");

		echo "<form onsubmit=\"doSelectAll();\" name=\"formIndex\" action=\"indexes.php\" method=\"post\">\n";

		echo "<table>\n";
		echo "<tr><th class=\"data required\" colspan=\"3\">{$lang['strindexname']}</th></tr>";
		echo "<tr>";
		echo "<td class=\"data1\" colspan=\"3\"><input type=\"text\" name=\"formIndexName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formIndexName']), "\" /></td></tr>";
		echo "<tr><th class=\"data\">{$lang['strtablecolumnlist']}</th><th class=\"data\">&nbsp;</th>";
		echo "<th class=\"data required\">{$lang['strindexcolumnlist']}</th></tr>\n";
		echo "<tr><td class=\"data1\">" . $selColumns->fetch() . "</td>\n";
		echo "<td class=\"data1\">" . $buttonRemove->fetch() . $buttonAdd->fetch() . "</td>";
		echo "<td class=\"data1\">" . $selIndex->fetch() . "</td></tr>\n";
		echo "</table>\n";

		echo "<table> \n";
		echo "<tr>";
		echo "<th class=\"data left required\" scope=\"row\">{$lang['strindextype']}</th>";
		echo "<td class=\"data1\"><select name=\"formIndexType\">";
		foreach ($data->typIndexes as $v) {
			echo "<option value=\"", htmlspecialchars($v), "\"",
			($v == $_POST['formIndexType']) ? ' selected="selected"' : '', ">", htmlspecialchars($v), "</option>\n";
		}
		echo "</select></td></tr>\n";
		echo "<tr>";
		echo "<th class=\"data left\" scope=\"row\"><label for=\"formUnique\">{$lang['strunique']}</label></th>";
		echo "<td class=\"data1\"><input type=\"checkbox\" id=\"formUnique\" name=\"formUnique\"", (isset($_POST['formUnique']) ? 'checked="checked"' : ''), " /></td>";
		echo "</tr>";
		echo "<tr>";
		echo "<th class=\"data left\" scope=\"row\">{$lang['strwhere']}</th>";
		echo "<td class=\"data1\">(<input name=\"formWhere\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formWhere']), "\" />)</td>";
		echo "</tr>";

		// Tablespace (if there are any)
		if ($data->hasTablespaces() && $tablespaces->recordCount() > 0) {
			echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strtablespace']}</th>\n";
			echo "\t\t<td class=\"data1\">\n\t\t\t<select name=\"formSpc\">\n";
			// Always offer the default (empty) option
			echo "\t\t\t\t<option value=\"\"",
			($_POST['formSpc'] == '') ? ' selected="selected"' : '', "></option>\n";
			// Display all other tablespaces
			while (!$tablespaces->EOF) {
				$spcname = htmlspecialchars($tablespaces->fields['spcname']);
				echo "\t\t\t\t<option value=\"{$spcname}\"",
				($spcname == $_POST['formSpc']) ? ' selected="selected"' : '', ">{$spcname}</option>\n";
				$tablespaces->moveNext();
			}
			echo "\t\t\t</select>\n\t\t</td>\n\t</tr>\n";
		}

		if ($data->hasConcurrentIndexBuild()) {
			echo "<tr>";
			echo "<th class=\"data left\" scope=\"row\"><label for=\"formConcur\">{$lang['strconcurrently']}</label></th>";
			echo "<td class=\"data1\"><input type=\"checkbox\" id=\"formConcur\" name=\"formConcur\"", (isset($_POST['formConcur']) ? 'checked="checked"' : ''), " /></td>";
			echo "</tr>";
		}

		echo "</table>";

		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create_index\" />\n";
		echo $misc->form;
		echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
		echo "<input type=\"submit\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new index in the database
 * @@ Note: this function can't handle columns with commas in them
 */
	public function doSaveCreateIndex() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Handle databases that don't have partial indexes
		if (!isset($_POST['formWhere'])) {
			$_POST['formWhere'] = '';
		}

		// Default tablespace to null if it isn't set
		if (!isset($_POST['formSpc'])) {
			$_POST['formSpc'] = null;
		}

		// Check that they've given a name and at least one column
		if ($_POST['formIndexName'] == '') {
			$this->doCreateIndex($lang['strindexneedsname']);
		} elseif (!isset($_POST['IndexColumnList']) || $_POST['IndexColumnList'] == '') {
			$this->doCreateIndex($lang['strindexneedscols']);
		} else {
			$status = $data->createIndex($_POST['formIndexName'], $_POST['table'], $_POST['IndexColumnList'],
				$_POST['formIndexType'], isset($_POST['formUnique']), $_POST['formWhere'], $_POST['formSpc'],
				isset($_POST['formConcur']));
			if ($status == 0) {
				$this->doDefault($lang['strindexcreated']);
			} else {
				$this->doCreateIndex($lang['strindexcreatedbad']);
			}

		}
	}

/**
 * Show confirmation of drop index and perform actual drop
 */
	public function doDropIndex($confirm) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ($confirm) {
			$this->printTrail('index');
			$misc->printTitle($lang['strdrop'], 'pg.index.drop');

			echo "<p>", sprintf($lang['strconfdropindex'], $misc->printVal($_REQUEST['index'])), "</p>\n";

			echo "<form action=\"/src/views/indexes.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"drop_index\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"index\" value=\"", htmlspecialchars($_REQUEST['index']), "\" />\n";
			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} else {
			$status = $data->dropIndex($_POST['index'], isset($_POST['cascade']));
			if ($status == 0) {
				$this->doDefault($lang['strindexdropped']);
			} else {
				$this->doDefault($lang['strindexdroppedbad']);
			}

		}

	}

	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$indPre = function (&$rowdata, $actions) use ($data, $lang) {
			if ($data->phpBool($rowdata->fields['indisprimary'])) {
				$rowdata->fields['+constraints'] = $lang['strprimarykey'];
				$actions['drop']['disable']      = true;
			} elseif ($data->phpBool($rowdata->fields['indisunique'])) {
				$rowdata->fields['+constraints'] = $lang['struniquekey'];
				$actions['drop']['disable']      = true;
			} else {
				$rowdata->fields['+constraints'] = '';
			}

			return $actions;
		};

		$this->printTrail('table');
		$this->printTabs('table', 'indexes');
		$misc->printMsg($msg);

		$indexes = $data->getIndexes($_REQUEST['table']);

		$columns = [
			'index' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('indname'),
			],
			'definition' => [
				'title' => $lang['strdefinition'],
				'field' => Decorator::field('inddef'),
			],
			'constraints' => [
				'title' => $lang['strconstraints'],
				'field' => Decorator::field('+constraints'),
				'type' => 'verbatim',
				'params' => ['align' => 'center'],
			],
			'clustered' => [
				'title' => $lang['strclustered'],
				'field' => Decorator::field('indisclustered'),
				'type' => 'yesno',
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('idxcomment'),
			],
		];

		$actions = [
			'cluster' => [
				'content' => $lang['strclusterindex'],
				'attr' => [
					'href' => [
						'url' => 'indexes.php',
						'urlvars' => [
							'action' => 'confirm_cluster_index',
							'table' => $_REQUEST['table'],
							'index' => Decorator::field('indname'),
						],
					],
				],
			],
			'reindex' => [
				'content' => $lang['strreindex'],
				'attr' => [
					'href' => [
						'url' => 'indexes.php',
						'urlvars' => [
							'action' => 'reindex',
							'table' => $_REQUEST['table'],
							'index' => Decorator::field('indname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'indexes.php',
						'urlvars' => [
							'action' => 'confirm_drop_index',
							'table' => $_REQUEST['table'],
							'index' => Decorator::field('indname'),
						],
					],
				],
			],
		];

		echo $this->printTable($indexes, $columns, $actions, 'indexes-indexes', $lang['strnoindexes'], $indPre);

		$this->printNavLinks([
			'create' => [
				'attr' => [
					'href' => [
						'url' => 'indexes.php',
						'urlvars' => [
							'action' => 'create_index',
							'server' => $_REQUEST['server'],
							'database' => $_REQUEST['database'],
							'schema' => $_REQUEST['schema'],
							'table' => $_REQUEST['table'],
						],
					],
				],
				'content' => $lang['strcreateindex'],
			],
		], 'indexes-indexes', get_defined_vars());
	}

}
