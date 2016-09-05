<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class DatabaseController extends BaseController {
	use AdminTrait;
	public $script      = 'database.php';
	public $_name       = 'DatabaseController';
	public $table_place = 'database-variables';

	function _highlight($string, $term) {
		return str_replace($term, "<b>{$term}</b>", $string);
	}

/**
 * Sends a signal to a process
 */
	public function doSignal() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->sendSignal($_REQUEST['pid'], $_REQUEST['signal']);
		if ($status == 0) {
			$this->doProcesses($lang['strsignalsent']);
		} else {
			$this->doProcesses($lang['strsignalsentbad']);
		}

	}

/**
 * Searches for a named database object
 */
	public function doFind($confirm = true, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_REQUEST['term'])) {
			$_REQUEST['term'] = '';
		}

		if (!isset($_REQUEST['filter'])) {
			$_REQUEST['filter'] = '';
		}

		$this->printTrail('database');
		$this->printTabs('database', 'find');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/database.php\" method=\"post\">\n";
		echo "<p><input name=\"term\" value=\"", htmlspecialchars($_REQUEST['term']),
			"\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" />\n";
		// Output list of filters.  This is complex due to all the 'has' and 'conf' feature possibilities
		echo "<select name=\"filter\">\n";
		echo "\t<option value=\"\"", ($_REQUEST['filter'] == '') ? ' selected="selected"' : '', ">{$lang['strallobjects']}</option>\n";
		echo "\t<option value=\"SCHEMA\"", ($_REQUEST['filter'] == 'SCHEMA') ? ' selected="selected"' : '', ">{$lang['strschemas']}</option>\n";
		echo "\t<option value=\"TABLE\"", ($_REQUEST['filter'] == 'TABLE') ? ' selected="selected"' : '', ">{$lang['strtables']}</option>\n";
		echo "\t<option value=\"VIEW\"", ($_REQUEST['filter'] == 'VIEW') ? ' selected="selected"' : '', ">{$lang['strviews']}</option>\n";
		echo "\t<option value=\"SEQUENCE\"", ($_REQUEST['filter'] == 'SEQUENCE') ? ' selected="selected"' : '', ">{$lang['strsequences']}</option>\n";
		echo "\t<option value=\"COLUMN\"", ($_REQUEST['filter'] == 'COLUMN') ? ' selected="selected"' : '', ">{$lang['strcolumns']}</option>\n";
		echo "\t<option value=\"RULE\"", ($_REQUEST['filter'] == 'RULE') ? ' selected="selected"' : '', ">{$lang['strrules']}</option>\n";
		echo "\t<option value=\"INDEX\"", ($_REQUEST['filter'] == 'INDEX') ? ' selected="selected"' : '', ">{$lang['strindexes']}</option>\n";
		echo "\t<option value=\"TRIGGER\"", ($_REQUEST['filter'] == 'TRIGGER') ? ' selected="selected"' : '', ">{$lang['strtriggers']}</option>\n";
		echo "\t<option value=\"CONSTRAINT\"", ($_REQUEST['filter'] == 'CONSTRAINT') ? ' selected="selected"' : '', ">{$lang['strconstraints']}</option>\n";
		echo "\t<option value=\"FUNCTION\"", ($_REQUEST['filter'] == 'FUNCTION') ? ' selected="selected"' : '', ">{$lang['strfunctions']}</option>\n";
		echo "\t<option value=\"DOMAIN\"", ($_REQUEST['filter'] == 'DOMAIN') ? ' selected="selected"' : '', ">{$lang['strdomains']}</option>\n";
		if ($conf['show_advanced']) {
			echo "\t<option value=\"AGGREGATE\"", ($_REQUEST['filter'] == 'AGGREGATE') ? ' selected="selected"' : '', ">{$lang['straggregates']}</option>\n";
			echo "\t<option value=\"TYPE\"", ($_REQUEST['filter'] == 'TYPE') ? ' selected="selected"' : '', ">{$lang['strtypes']}</option>\n";
			echo "\t<option value=\"OPERATOR\"", ($_REQUEST['filter'] == 'OPERATOR') ? ' selected="selected"' : '', ">{$lang['stroperators']}</option>\n";
			echo "\t<option value=\"OPCLASS\"", ($_REQUEST['filter'] == 'OPCLASS') ? ' selected="selected"' : '', ">{$lang['stropclasses']}</option>\n";
			echo "\t<option value=\"CONVERSION\"", ($_REQUEST['filter'] == 'CONVERSION') ? ' selected="selected"' : '', ">{$lang['strconversions']}</option>\n";
			echo "\t<option value=\"LANGUAGE\"", ($_REQUEST['filter'] == 'LANGUAGE') ? ' selected="selected"' : '', ">{$lang['strlanguages']}</option>\n";
		}
		echo "</select>\n";
		echo "<input type=\"submit\" value=\"{$lang['strfind']}\" />\n";
		echo $misc->form;
		echo "<input type=\"hidden\" name=\"action\" value=\"find\" /></p>\n";
		echo "</form>\n";

		// Default focus
		$misc->setFocus('forms[0].term');

		// If a search term has been specified, then perform the search
		// and display the results, grouped by object type
		if ($_REQUEST['term'] != '') {
			$rs = $data->findObject($_REQUEST['term'], $_REQUEST['filter']);
			if ($rs->recordCount() > 0) {
				$curr = '';
				while (!$rs->EOF) {
					// Output a new header if the current type has changed, but not if it's just changed the rule type
					if ($rs->fields['type'] != $curr) {
						// Short-circuit in the case of changing from table rules to view rules; table cols to view cols;
						// table constraints to domain constraints
						if ($rs->fields['type'] == 'RULEVIEW' && $curr == 'RULETABLE') {
							$curr = $rs->fields['type'];
						} elseif ($rs->fields['type'] == 'COLUMNVIEW' && $curr == 'COLUMNTABLE') {
							$curr = $rs->fields['type'];
						} elseif ($rs->fields['type'] == 'CONSTRAINTTABLE' && $curr == 'CONSTRAINTDOMAIN') {
							$curr = $rs->fields['type'];
						} else {
							if ($curr != '') {
								echo "</ul>\n";
							}

							$curr = $rs->fields['type'];
							echo "<h3>";
							switch ($curr) {
								case 'SCHEMA':
									echo $lang['strschemas'];
									break;
								case 'TABLE':
									echo $lang['strtables'];
									break;
								case 'VIEW':
									echo $lang['strviews'];
									break;
								case 'SEQUENCE':
									echo $lang['strsequences'];
									break;
								case 'COLUMNTABLE':
								case 'COLUMNVIEW':
									echo $lang['strcolumns'];
									break;
								case 'INDEX':
									echo $lang['strindexes'];
									break;
								case 'CONSTRAINTTABLE':
								case 'CONSTRAINTDOMAIN':
									echo $lang['strconstraints'];
									break;
								case 'TRIGGER':
									echo $lang['strtriggers'];
									break;
								case 'RULETABLE':
								case 'RULEVIEW':
									echo $lang['strrules'];
									break;
								case 'FUNCTION':
									echo $lang['strfunctions'];
									break;
								case 'TYPE':
									echo $lang['strtypes'];
									break;
								case 'DOMAIN':
									echo $lang['strdomains'];
									break;
								case 'OPERATOR':
									echo $lang['stroperators'];
									break;
								case 'CONVERSION':
									echo $lang['strconversions'];
									break;
								case 'LANGUAGE':
									echo $lang['strlanguages'];
									break;
								case 'AGGREGATE':
									echo $lang['straggregates'];
									break;
								case 'OPCLASS':
									echo $lang['stropclasses'];
									break;
							}
							echo "</h3>";
							echo "<ul>\n";
						}
					}

					switch ($curr) {
						case 'SCHEMA':
							echo "<li><a href=\"/redirect/schema?{$misc->href}&amp;schema=", $misc->printVal($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'TABLE':
							echo "<li>";
							echo "<a href=\"tables.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/table?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;table=",
							urlencode($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'VIEW':
							echo "<li>";
							echo "<a href=\"views.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/view?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;view=",
							urlencode($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'SEQUENCE':
							echo "<li>";
							echo "<a href=\"sequences.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"sequences.php?subject=sequence&amp;action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']),
							"&amp;sequence=", urlencode($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'COLUMNTABLE':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"tblproperties.php?subject=table&amp;{$misc->href}&amp;table=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"colproperties.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;table=",
							urlencode($rs->fields['relname']), "&amp;column=", urlencode($rs->fields['name']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'COLUMNVIEW':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"viewproperties.php?subject=view&amp;{$misc->href}&amp;view=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"colproperties.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;view=",
							urlencode($rs->fields['relname']), "&amp;column=", urlencode($rs->fields['name']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'INDEX':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/table?{$misc->href}&amp;table=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"indexes.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;table=", urlencode($rs->fields['relname']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'CONSTRAINTTABLE':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/table?{$misc->href}&amp;table=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"constraints.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;table=",
							urlencode($rs->fields['relname']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'CONSTRAINTDOMAIN':
							echo "<li>";
							echo "<a href=\"domains.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"domains.php?action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;domain=", urlencode($rs->fields['relname']), "\">",
							$misc->printVal($rs->fields['relname']), '.', $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'TRIGGER':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/table?{$misc->href}&amp;table=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"triggers.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;table=", urlencode($rs->fields['relname']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'RULETABLE':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/table?{$misc->href}&amp;table=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"rules.php?subject=table&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;reltype=table&amp;table=",
							urlencode($rs->fields['relname']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'RULEVIEW':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"/redirect/view?{$misc->href}&amp;view=", urlencode($rs->fields['relname']), "&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['relname']), "</a>.";
							echo "<a href=\"rules.php?subject=view&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;reltype=view&amp;view=",
							urlencode($rs->fields['relname']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'FUNCTION':
							echo "<li>";
							echo "<a href=\"functions.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"functions.php?action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;function=",
							urlencode($rs->fields['name']), "&amp;function_oid=", urlencode($rs->fields['oid']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'TYPE':
							echo "<li>";
							echo "<a href=\"types.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"types.php?action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;type=",
							urlencode($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'DOMAIN':
							echo "<li>";
							echo "<a href=\"domains.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"domains.php?action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;domain=",
							urlencode($rs->fields['name']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'OPERATOR':
							echo "<li>";
							echo "<a href=\"operators.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"operators.php?action=properties&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "&amp;operator=",
							urlencode($rs->fields['name']), "&amp;operator_oid=", urlencode($rs->fields['oid']), "\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'CONVERSION':
							echo "<li>";
							echo "<a href=\"conversions.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"conversions.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']),
							"\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'LANGUAGE':
							echo "<li><a href=\"languages.php?{$misc->href}\">", $this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'AGGREGATE':
							echo "<li>";
							echo "<a href=\"aggregates.php?subject=schema&amp;{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"aggregates.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
						case 'OPCLASS':
							echo "<li>";
							echo "<a href=\"/redirect/schema?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">", $misc->printVal($rs->fields['schemaname']), "</a>.";
							echo "<a href=\"opclasses.php?{$misc->href}&amp;schema=", urlencode($rs->fields['schemaname']), "\">",
							$this->_highlight($misc->printVal($rs->fields['name']), $_REQUEST['term']), "</a></li>\n";
							break;
					}
					$rs->moveNext();
				}
				echo "</ul>\n";

				echo "<p>", $rs->recordCount(), " ", $lang['strobjects'], "</p>\n";
			} else {
				echo "<p>{$lang['strnoobjects']}</p>\n";
			}

		}
	}

/**
 * Displays options for database download
 */
	public function doExport($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('database');
		$this->printTabs('database', 'export');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/dbexport.php\" method=\"post\">\n";
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
		echo "<input type=\"hidden\" name=\"subject\" value=\"database\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strexport']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Show the current status of all database variables
 */
	public function doVariables() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Fetch the variables from the database
		$variables = $data->getVariables();
		$this->printTrail('database');
		$this->printTabs('database', 'variables');

		$columns = [
			'variable' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('name'),
			],
			'value' => [
				'title' => $lang['strsetting'],
				'field' => Decorator::field('setting'),
			],
		];

		$actions = [];

		echo $this->printTable($variables, $columns, $actions, $this->table_place, $lang['strnodata']);
	}

/**
 * Show all current database connections and any queries they
 * are running.
 */
	public function doProcesses($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('database');
		$this->printTabs('database', 'processes');
		$misc->printMsg($msg);

		if (strlen($msg) === 0) {
			echo "<br /><a id=\"control\" href=\"\"><img src=\"" . $misc->icon('Refresh') . "\" alt=\"{$lang['strrefresh']}\" title=\"{$lang['strrefresh']}\"/>&nbsp;{$lang['strrefresh']}</a>";
		}

		echo "<div id=\"data_block\">";
		$this->currentProcesses();
		echo "</div>";
	}

	function currentProcesses($isAjax = false) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Display prepared transactions
		if ($data->hasPreparedXacts()) {
			echo "<h3>{$lang['strpreparedxacts']}</h3>\n";
			$prep_xacts = $data->getPreparedXacts($_REQUEST['database']);

			$columns = [
				'transaction' => [
					'title' => $lang['strxactid'],
					'field' => Decorator::field('transaction'),
				],
				'gid' => [
					'title' => $lang['strgid'],
					'field' => Decorator::field('gid'),
				],
				'prepared' => [
					'title' => $lang['strstarttime'],
					'field' => Decorator::field('prepared'),
				],
				'owner' => [
					'title' => $lang['strowner'],
					'field' => Decorator::field('owner'),
				],
			];

			$actions = [];

			echo $this->printTable($prep_xacts, $columns, $actions, 'database-processes-preparedxacts', $lang['strnodata']);
		}

		// Fetch the processes from the database
		echo "<h3>{$lang['strprocesses']}</h3>\n";
		$processes = $data->getProcesses($_REQUEST['database']);

		$columns = [
			'user' => [
				'title' => $lang['strusername'],
				'field' => Decorator::field('usename'),
			],
			'process' => [
				'title' => $lang['strprocess'],
				'field' => Decorator::field('pid'),
			],
			'blocked' => [
				'title' => $lang['strblocked'],
				'field' => Decorator::field('waiting'),
			],
			'query' => [
				'title' => $lang['strsql'],
				'field' => Decorator::field('query'),
			],
			'start_time' => [
				'title' => $lang['strstarttime'],
				'field' => Decorator::field('query_start'),
			],
		];

		// Build possible actions for our process list
		$columns['actions'] = ['title' => $lang['stractions']];

		$actions = [];
		if ($data->hasUserSignals() || $data->isSuperUser()) {
			$actions = [
				'cancel' => [
					'content' => $lang['strcancel'],
					'attr' => [
						'href' => [
							'url' => 'database.php',
							'urlvars' => [
								'action' => 'signal',
								'signal' => 'CANCEL',
								'pid' => Decorator::field('pid'),
							],
						],
					],
				],
				'kill' => [
					'content' => $lang['strkill'],
					'attr' => [
						'href' => [
							'url' => 'database.php',
							'urlvars' => [
								'action' => 'signal',
								'signal' => 'KILL',
								'pid' => Decorator::field('pid'),
							],
						],
					],
				],
			];

			// Remove actions where not supported
			if (!$data->hasQueryKill()) {
				unset($actions['kill']);
			}

			if (!$data->hasQueryCancel()) {
				unset($actions['cancel']);
			}

		}

		if (count($actions) == 0) {
			unset($columns['actions']);
		}

		echo $this->printTable($processes, $columns, $actions, 'database-processes', $lang['strnodata']);

		if ($isAjax) {
			exit;
		}

	}

	function currentLocks($isAjax = false) {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Get the info from the pg_locks view
		$variables = $data->getLocks();

		$columns = [
			'namespace' => [
				'title' => $lang['strschema'],
				'field' => Decorator::field('nspname'),
			],
			'tablename' => [
				'title' => $lang['strtablename'],
				'field' => Decorator::field('tablename'),
			],
			'vxid' => [
				'title' => $lang['strvirtualtransaction'],
				'field' => Decorator::field('virtualtransaction'),
			],
			'transactionid' => [
				'title' => $lang['strtransaction'],
				'field' => Decorator::field('transaction'),
			],
			'processid' => [
				'title' => $lang['strprocessid'],
				'field' => Decorator::field('pid'),
			],
			'mode' => [
				'title' => $lang['strmode'],
				'field' => Decorator::field('mode'),
			],
			'granted' => [
				'title' => $lang['strislockheld'],
				'field' => Decorator::field('granted'),
				'type' => 'yesno',
			],
		];

		if (!$data->hasVirtualTransactionId()) {
			unset($columns['vxid']);
		}

		$actions = [];
		echo $this->printTable($variables, $columns, $actions, 'database-locks', $lang['strnodata']);

		if ($isAjax) {
			exit;
		}

	}

/**
 * Show the existing table locks in the current database
 */
	public function doLocks() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('database');
		$this->printTabs('database', 'locks');

		echo "<br /><a id=\"control\" href=\"\"><img src=\"" . $misc->icon('Refresh') . "\" alt=\"{$lang['strrefresh']}\" title=\"{$lang['strrefresh']}\"/>&nbsp;{$lang['strrefresh']}</a>";

		echo "<div id=\"data_block\">";
		$this->currentLocks();
		echo "</div>";
	}

/**
 * Allow execution of arbitrary SQL statements on a database
 */
	public function doSQL() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if ((!isset($_SESSION['sqlquery'])) || isset($_REQUEST['new'])) {
			$_SESSION['sqlquery'] = '';
			$_REQUEST['paginate'] = 'on';
		}

		$this->printTrail('database');
		$this->printTabs('database', 'sql');
		echo "<p>{$lang['strentersql']}</p>\n";
		echo "<form action=\"/src/views/sql.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
		echo "<p>{$lang['strsql']}<br />\n";
		echo "<textarea style=\"width:100%;\" rows=\"20\" cols=\"50\" name=\"query\">",
		htmlspecialchars($_SESSION['sqlquery']), "</textarea></p>\n";

		// Check that file uploads are enabled
		if (ini_get('file_uploads')) {
			// Don't show upload option if max size of uploads is zero
			$max_size = $misc->inisizeToBytes(ini_get('upload_max_filesize'));
			if (is_double($max_size) && $max_size > 0) {
				echo "<p><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$max_size}\" />\n";
				echo "<label for=\"script\">{$lang['struploadscript']}</label> <input id=\"script\" name=\"script\" type=\"file\" /></p>\n";
			}
		}

		echo "<p><input type=\"checkbox\" id=\"paginate\" name=\"paginate\"", (isset($_REQUEST['paginate']) ? ' checked="checked"' : ''), " /><label for=\"paginate\">{$lang['strpaginate']}</label></p>\n";
		echo "<p><input type=\"submit\" name=\"execute\" accesskey=\"r\" value=\"{$lang['strexecute']}\" />\n";
		echo $misc->form;
		echo "<input type=\"reset\" accesskey=\"q\" value=\"{$lang['strreset']}\" /></p>\n";
		echo "</form>\n";

		// Default focus
		$misc->setFocus('forms[0].query');
	}

	public function render() {
		$conf   = $this->conf;
		$misc   = $this->misc;
		$lang   = $this->lang;
		$action = $this->action;
		$data   = $misc->getDatabaseAccessor();

		if ($action == 'refresh_locks') {
			$this->currentLocks(true);
		}

		if ($action == 'refresh_processes') {
			$this->currentProcesses(true);
		}
		$scripts = '';
		/* normal flow */
		if ($action == 'locks' || $action == 'processes') {
			$scripts .= "<script src=\"/js/database.js\" type=\"text/javascript\"></script>";

			$refreshTime = $conf['ajax_refresh'] * 1000;

			$scripts .= "<script type=\"text/javascript\">\n";
			$scripts .= "var Database = {\n";
			$scripts .= "ajax_time_refresh: {$refreshTime},\n";
			$scripts .= "str_start: {text:'{$lang['strstart']}',icon: '" . $misc->icon('Execute') . "'},\n";
			$scripts .= "str_stop: {text:'{$lang['strstop']}',icon: '" . $misc->icon('Stop') . "'},\n";
			$scripts .= "load_icon: '" . $misc->icon('Loading') . "',\n";
			$scripts .= "server:'{$_REQUEST['server']}',\n";
			$scripts .= "dbname:'{$_REQUEST['database']}',\n";
			$scripts .= "action:'refresh_{$action}',\n";
			$scripts .= "errmsg: '" . str_replace("'", "\'", $lang['strconnectionfail']) . "'\n";
			$scripts .= "};\n";
			$scripts .= "</script>\n";
		}

		$misc->printHeader($lang['strdatabase'], $scripts);
		$misc->printBody();

		switch ($action) {
			case 'find':
				if (isset($_REQUEST['term'])) {
					$this->doFind(false);
				} else {
					$this->doFind(true);
				}

				break;
			case 'sql':
				$this->doSQL();
				break;
			case 'variables':
				$this->doVariables();
				break;
			case 'processes':
				$this->doProcesses();
				break;
			case 'locks':
				$this->doLocks();
				break;
			case 'export':
				$this->doExport();
				break;
			case 'signal':
				$this->doSignal();
				break;
			default:
				if ($this->adminActions($action, 'database') === false) {
					$this->doSQL();
				}

				break;
		}

		$misc->printFooter();

	}
}
