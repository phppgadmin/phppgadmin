<?php
namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

trait AdminTrait {

/**
 * Show confirmation of cluster and perform cluster
 */
	public function doCluster($type, $confirm = false) {

		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';

		$script = $this->script;
		$misc   = $this->misc;
		$lang   = $this->lang;
		$data   = $misc->getDatabaseAccessor();

		if (($type == 'table') && empty($_REQUEST['table']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifytabletocluster']);
			return;
		}

		if ($confirm) {
			if (isset($_REQUEST['ma'])) {
				$this->printTrail('schema');
				$misc->printTitle($lang['strclusterindex'], 'pg.index.cluster');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfclustertable'], $misc->printVal($a['table'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table[]\" value=\"", htmlspecialchars($a['table']), "\" />\n";
				}
			} // END if multi cluster
			else {
				$this->printTrail($type);
				$misc->printTitle($lang['strclusterindex'], 'pg.index.cluster');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";

				if ($type == 'table') {
					echo "<p>", sprintf($lang['strconfclustertable'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
				} else {
					echo "<p>", sprintf($lang['strconfclusterdatabase'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"\" />\n";
				}
			}
			echo "<input type=\"hidden\" name=\"action\" value=\"cluster\" />\n";

			echo $misc->form;

			echo "<input type=\"submit\" name=\"cluster\" value=\"{$lang['strcluster']}\" />\n"; //TODO
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} // END single cluster
		else {
			//If multi table cluster
			if ($type == 'table') {
				// cluster one or more table
				if (is_array($_REQUEST['table'])) {
					$msg = '';
					foreach ($_REQUEST['table'] as $o) {
						$status = $data->clusterIndex($o);
						if ($status == 0) {
							$msg .= sprintf('%s: %s<br />', htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['strclusteredgood']);
						} else {
							$this->doDefault($type, sprintf('%s%s: %s<br />', $msg, htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['strclusteredbad']));
							return;
						}
					}
					// Everything went fine, back to the Default page....
					$this->doDefault($msg);
				} else {
					$status = $data->clusterIndex($_REQUEST['object']);
					if ($status == 0) {
						$this->doAdmin($type, $lang['strclusteredgood']);
					} else {
						$this->doAdmin($type, $lang['strclusteredbad']);
					}

				}
			} else {
				// Cluster all tables in database
				$status = $data->clusterIndex();
				if ($status == 0) {
					$this->doAdmin($type, $lang['strclusteredgood']);
				} else {
					$this->doAdmin($type, $lang['strclusteredbad']);
				}

			}
		}
	}

/**
 * Show confirmation of reindex and perform reindex
 */
	public function doReindex($type, $confirm = false) {

		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';
		$script       = $this->script;
		$misc         = $this->misc;
		$lang         = $this->lang;
		$data         = $misc->getDatabaseAccessor();

		if (($type == 'table') && empty($_REQUEST['table']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifytabletoreindex']);
			return;
		}

		if ($confirm) {
			if (isset($_REQUEST['ma'])) {
				$this->printTrail('schema');
				$misc->printTitle($lang['strreindex'], 'pg.reindex');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfreindextable'], $misc->printVal($a['table'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table[]\" value=\"", htmlspecialchars($a['table']), "\" />\n";
				}
			} // END if multi reindex
			else {
				$this->printTrail($type);
				$misc->printTitle($lang['strreindex'], 'pg.reindex');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";

				if ($type == 'table') {
					echo "<p>", sprintf($lang['strconfreindextable'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
				} else {
					echo "<p>", sprintf($lang['strconfreindexdatabase'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"\" />\n";
				}
			}
			echo "<input type=\"hidden\" name=\"action\" value=\"reindex\" />\n";

			if ($data->hasForceReindex()) {
				echo "<p><input type=\"checkbox\" id=\"reindex_force\" name=\"reindex_force\" /><label for=\"reindex_force\">{$lang['strforce']}</label></p>\n";
			}

			echo $misc->form;

			echo "<input type=\"submit\" name=\"reindex\" value=\"{$lang['strreindex']}\" />\n"; //TODO
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} // END single reindex
		else {
			//If multi table reindex
			if (($type == 'table') && is_array($_REQUEST['table'])) {
				$msg = '';
				foreach ($_REQUEST['table'] as $o) {
					$status = $data->reindex(strtoupper($type), $o, isset($_REQUEST['reindex_force']));
					if ($status == 0) {
						$msg .= sprintf('%s: %s<br />', htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['strreindexgood']);
					} else {
						$this->doDefault($type, sprintf('%s%s: %s<br />', $msg, htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['strreindexbad']));
						return;
					}
				}
				// Everything went fine, back to the Default page....
				$misc->setReloadBrowser(true);
				$this->doDefault($msg);
			} else {
				$status = $data->reindex(strtoupper($type), $_REQUEST['object'], isset($_REQUEST['reindex_force']));
				if ($status == 0) {
					$misc->setReloadBrowser(true);
					$this->doAdmin($type, $lang['strreindexgood']);
				} else {
					$this->doAdmin($type, $lang['strreindexbad']);
				}

			}
		}
	}

/**
 * Show confirmation of analyze and perform analyze
 */
	public function doAnalyze($type, $confirm = false) {
		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';
		$script       = $this->script;
		$data         = $this->data;
		$misc         = $this->misc;
		$lang         = $this->lang;

		if (($type == 'table') && empty($_REQUEST['table']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifytabletoanalyze']);
			return;
		}
		\Kint::dump($_REQUEST['object']);

		if ($confirm) {
			if (isset($_REQUEST['ma'])) {
				$this->printTrail('schema');
				$misc->printTitle($lang['stranalyze'], 'pg.analyze');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfanalyzetable'], $misc->printVal($a['table'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table[]\" value=\"", htmlspecialchars($a['table']), "\" />\n";
				}
			} // END if multi analyze
			else {
				$this->printTrail($type);
				$misc->printTitle($lang['stranalyze'], 'pg.analyze');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";

				if ($type == 'table') {
					echo "<p>", sprintf($lang['strconfanalyzetable'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
				} else {
					echo "<p>", sprintf($lang['strconfanalyzedatabase'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"\" />\n";
				}
			}
			echo "<input type=\"hidden\" name=\"action\" value=\"analyze\" />\n";
			echo $misc->form;

			echo "<input type=\"submit\" name=\"analyze\" value=\"{$lang['stranalyze']}\" />\n"; //TODO
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} // END single analyze
		else {
			//If multi table analyze
			if (($type == 'table') && is_array($_REQUEST['table'])) {
				$msg = '';
				foreach ($_REQUEST['table'] as $o) {
					$status = $data->analyzeDB($o);
					if ($status == 0) {
						$msg .= sprintf('%s: %s<br />', htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['stranalyzegood']);
					} else {
						$this->doDefault($type, sprintf('%s%s: %s<br />', $msg, htmlentities($o, ENT_QUOTES, 'UTF-8'), $lang['stranalyzebad']));
						return;
					}
				}
				// Everything went fine, back to the Default page....
				$misc->setReloadBrowser(true);
				$this->doDefault($msg);
			} else {
				//we must pass table here. When empty, analyze the whole db
				$status = $data->analyzeDB($_REQUEST['table']);
				if ($status == 0) {
					$misc->setReloadBrowser(true);
					$this->doAdmin($type, $lang['stranalyzegood']);
				} else {
					$this->doAdmin($type, $lang['stranalyzebad']);
				}

			}
		}
	}

/**
 * Show confirmation of vacuum and perform actual vacuum
 */
	public function doVacuum($type, $confirm = false) {

		$script = ($type == 'database') ? 'database.php' : 'tables.php';

		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (($type == 'table') && empty($_REQUEST['table']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifytabletovacuum']);
			return;
		}

		if ($confirm) {
			if (isset($_REQUEST['ma'])) {
				$this->printTrail('schema');
				$misc->printTitle($lang['strvacuum'], 'pg.vacuum');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfvacuumtable'], $misc->printVal($a['table'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table[]\" value=\"", htmlspecialchars($a['table']), "\" />\n";
				}
			} else {
				// END if multi vacuum
				$this->printTrail($type);
				$misc->printTitle($lang['strvacuum'], 'pg.vacuum');

				echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";

				if ($type == 'table') {
					echo "<p>", sprintf($lang['strconfvacuumtable'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
				} else {
					echo "<p>", sprintf($lang['strconfvacuumdatabase'], $misc->printVal($_REQUEST['object'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"table\" value=\"\" />\n";
				}
			}
			echo "<input type=\"hidden\" name=\"action\" value=\"vacuum\" />\n";
			echo $misc->form;
			echo "<p><input type=\"checkbox\" id=\"vacuum_full\" name=\"vacuum_full\" /> <label for=\"vacuum_full\">{$lang['strfull']}</label></p>\n";
			echo "<p><input type=\"checkbox\" id=\"vacuum_analyze\" name=\"vacuum_analyze\" /> <label for=\"vacuum_analyze\">{$lang['stranalyze']}</label></p>\n";
			echo "<p><input type=\"checkbox\" id=\"vacuum_freeze\" name=\"vacuum_freeze\" /> <label for=\"vacuum_freeze\">{$lang['strfreeze']}</label></p>\n";
			echo "<input type=\"submit\" name=\"vacuum\" value=\"{$lang['strvacuum']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</form>\n";
		} // END single vacuum
		else {
			//If multi drop
			if (is_array($_REQUEST['table'])) {
				$msg = '';
				foreach ($_REQUEST['table'] as $t) {
					$status = $data->vacuumDB($t, isset($_REQUEST['vacuum_analyze']), isset($_REQUEST['vacuum_full']), isset($_REQUEST['vacuum_freeze']));
					if ($status == 0) {
						$msg .= sprintf('%s: %s<br />', htmlentities($t, ENT_QUOTES, 'UTF-8'), $lang['strvacuumgood']);
					} else {
						$this->doDefault($type, sprintf('%s%s: %s<br />', $msg, htmlentities($t, ENT_QUOTES, 'UTF-8'), $lang['strvacuumbad']));
						return;
					}
				}
				// Everything went fine, back to the Default page....
				$misc->setReloadBrowser(true);
				$this->doDefault($msg);
			} else {
				//we must pass table here. When empty, vacuum the whole db
				$status = $data->vacuumDB($_REQUEST['table'], isset($_REQUEST['vacuum_analyze']), isset($_REQUEST['vacuum_full']), isset($_REQUEST['vacuum_freeze']));
				if ($status == 0) {
					$misc->setReloadBrowser(true);
					$this->doAdmin($type, $lang['strvacuumgood']);
				} else {
					$this->doAdmin($type, $lang['strvacuumbad']);
				}

			}
		}
	}

/**
 * Add or Edit autovacuum params and save them
 */
	public function doEditAutovacuum($type, $confirm, $msg = '') {
		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';
		$script       = $this->script;

		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['table'])) {
			$this->doAdmin($type, '', $lang['strspecifyeditvacuumtable']);
			return;
		}

		$script = ($type == 'database') ? 'database.php' : 'tables.php';

		if ($confirm) {
			$this->printTrail($type);
			$misc->printTitle(sprintf($lang['streditvacuumtable'], $misc->printVal($_REQUEST['table'])));
			$misc->printMsg(sprintf($msg, $misc->printVal($_REQUEST['table'])));

			if (empty($_REQUEST['table'])) {
				$this->doAdmin($type, '', $lang['strspecifyeditvacuumtable']);
				return;
			}

			$old_val  = $data->getTableAutovacuum($_REQUEST['table']);
			$defaults = $data->getAutovacuum();
			$old_val  = $old_val->fields;

			if (isset($old_val['autovacuum_enabled']) and ($old_val['autovacuum_enabled'] == 'off')) {
				$enabled  = '';
				$disabled = 'checked="checked"';
			} else {
				$enabled  = 'checked="checked"';
				$disabled = '';
			}

			if (!isset($old_val['autovacuum_vacuum_threshold'])) {
				$old_val['autovacuum_vacuum_threshold'] = '';
			}

			if (!isset($old_val['autovacuum_vacuum_scale_factor'])) {
				$old_val['autovacuum_vacuum_scale_factor'] = '';
			}

			if (!isset($old_val['autovacuum_analyze_threshold'])) {
				$old_val['autovacuum_analyze_threshold'] = '';
			}

			if (!isset($old_val['autovacuum_analyze_scale_factor'])) {
				$old_val['autovacuum_analyze_scale_factor'] = '';
			}

			if (!isset($old_val['autovacuum_vacuum_cost_delay'])) {
				$old_val['autovacuum_vacuum_cost_delay'] = '';
			}

			if (!isset($old_val['autovacuum_vacuum_cost_limit'])) {
				$old_val['autovacuum_vacuum_cost_limit'] = '';
			}

			echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
			echo $misc->form;
			echo "<input type=\"hidden\" name=\"action\" value=\"editautovac\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";

			echo "<br />\n<br />\n<table>\n";
			echo "\t<tr><td>&nbsp;</td>\n";
			echo "<th class=\"data\">{$lang['strnewvalues']}</th><th class=\"data\">{$lang['strdefaultvalues']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['strenable']}</th>\n";
			echo "<td class=\"data1\">\n";
			echo "<label for=\"on\">on</label><input type=\"radio\" name=\"autovacuum_enabled\" id=\"on\" value=\"on\" {$enabled} />\n";
			echo "<label for=\"off\">off</label><input type=\"radio\" name=\"autovacuum_enabled\" id=\"off\" value=\"off\" {$disabled} /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['strvacuumbasethreshold']}</th>\n";
			echo "<td class=\"data1\"><input type=\"text\" name=\"autovacuum_vacuum_threshold\" value=\"{$old_val['autovacuum_vacuum_threshold']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_vacuum_threshold']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['strvacuumscalefactor']}</th>\n";
			echo "<td class=\"data1\"><input type=\"text\" name=\"autovacuum_vacuum_scale_factor\" value=\"{$old_val['autovacuum_vacuum_scale_factor']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_vacuum_scale_factor']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['stranalybasethreshold']}</th>\n";
			echo "<td class=\"data1\"><input type=\"text\" name=\"autovacuum_analyze_threshold\" value=\"{$old_val['autovacuum_analyze_threshold']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_analyze_threshold']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['stranalyzescalefactor']}</th>\n";
			echo "<td class=\"data1\"><input type=\"text\" name=\"autovacuum_analyze_scale_factor\" value=\"{$old_val['autovacuum_analyze_scale_factor']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_analyze_scale_factor']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['strvacuumcostdelay']}</th>\n";
			echo "<td class=\"data1\"><input type=\"text\" name=\"autovacuum_vacuum_cost_delay\" value=\"{$old_val['autovacuum_vacuum_cost_delay']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_vacuum_cost_delay']}</th></tr>\n";
			echo "\t<tr><th class=\"data left\">{$lang['strvacuumcostlimit']}</th>\n";
			echo "<td class=\"datat1\"><input type=\"text\" name=\"autovacuum_vacuum_cost_limit\" value=\"{$old_val['autovacuum_vacuum_cost_limit']}\" /></td>\n";
			echo "<th class=\"data left\">{$defaults['autovacuum_vacuum_cost_limit']}</th></tr>\n";
			echo "</table>\n";
			echo "<br />";
			echo "<br />";
			echo "<input type=\"submit\" name=\"save\" value=\"{$lang['strsave']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";

			echo "</form>\n";
		} else {
			$status = $data->saveAutovacuum($_REQUEST['table'], $_POST['autovacuum_enabled'], $_POST['autovacuum_vacuum_threshold'],
				$_POST['autovacuum_vacuum_scale_factor'], $_POST['autovacuum_analyze_threshold'], $_POST['autovacuum_analyze_scale_factor'],
				$_POST['autovacuum_vacuum_cost_delay'], $_POST['autovacuum_vacuum_cost_limit']);

			if ($status == 0) {
				$this->doAdmin($type, '', sprintf($lang['strsetvacuumtablesaved'], $_REQUEST['table']));
			} else {
				$this->doEditAutovacuum($type, true, $lang['strsetvacuumtablefail']);
			}

		}
	}

/**
 * confirm drop autovacuum params for a table and drop it
 */
	public function doDropAutovacuum($type, $confirm) {
		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';
		$script       = $this->script;

		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['table'])) {
			$this->doAdmin($type, '', $lang['strspecifydelvacuumtable']);
			return;
		}

		if ($confirm) {
			$this->printTrail($type);
			$this->printTabs($type, 'admin');

			$script = ($type == 'database') ? 'database.php' : 'tables.php';

			printf("<p>{$lang['strdelvacuumtable']}</p>\n",
				$misc->printVal("\"{$_GET['schema']}\".\"{$_GET['table']}\""));

			echo "<form style=\"float: left\" action=\"{$script}\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"delautovac\" />\n";
			echo $misc->form;
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo "<input type=\"hidden\" name=\"rel\" value=\"", htmlspecialchars(serialize([$_REQUEST['schema'], $_REQUEST['table']])), "\" />\n";
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "</form>\n";

			echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"admin\" />\n";
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		} else {

			$status = $data->dropAutovacuum($_POST['table']);

			if ($status == 0) {
				$this->doAdmin($type, '', sprintf($lang['strvacuumtablereset'], $misc->printVal($_POST['table'])));
			} else {
				$this->doAdmin($type, '', sprintf($lang['strdelvacuumtablefail'], $misc->printVal($_POST['table'])));
			}

		}
	}

/**
 * database/table administration and tuning tasks
 *
 * $Id: admin.php
 */

	public function doAdmin($type, $msg = '') {
		$this->script = ($type == 'database') ? 'database.php' : 'tables.php';

		$script = $this->script;

		$misc = $this->misc;
		$lang = $this->lang;

		$data = $misc->getDatabaseAccessor();

		$this->printTrail($type);
		$this->printTabs($type, 'admin');
		$misc->printMsg($msg);

		if ($type == 'database') {
			printf("<p>{$lang['stradminondatabase']}</p>\n", $misc->printVal($_REQUEST['object']));
		} else {
			printf("<p>{$lang['stradminontable']}</p>\n", $misc->printVal($_REQUEST['object']));
		}

		echo "<table style=\"width: 50%\">\n";
		echo "<tr>\n";
		echo "<th class=\"data\">";
		$misc->printHelp($lang['strvacuum'], 'pg.admin.vacuum') . "</th>\n";
		echo "</th>";
		echo "<th class=\"data\">";
		$misc->printHelp($lang['stranalyze'], 'pg.admin.analyze');
		echo "</th>";
		if ($data->hasRecluster()) {
			echo "<th class=\"data\">";
			$misc->printHelp($lang['strclusterindex'], 'pg.index.cluster');
			echo "</th>";
		}
		echo "<th class=\"data\">";
		$misc->printHelp($lang['strreindex'], 'pg.index.reindex');
		echo "</th>";
		echo "</tr>";

		// Vacuum
		echo "<tr class=\"row1\">\n";
		echo "<td style=\"text-align: center; vertical-align: bottom\">\n";
		echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";

		echo "<p><input type=\"hidden\" name=\"action\" value=\"confirm_vacuum\" />\n";
		echo $misc->form;
		if ($type == 'table') {
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"table\" />\n";
		}
		echo "<input type=\"submit\" value=\"{$lang['strvacuum']}\" /></p>\n";
		echo "</form>\n";
		echo "</td>\n";

		// Analyze
		echo "<td style=\"text-align: center; vertical-align: bottom\">\n";
		echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"confirm_analyze\" />\n";
		echo $misc->form;
		if ($type == 'table') {
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"table\" />\n";
		}
		echo "<input type=\"submit\" value=\"{$lang['stranalyze']}\" /></p>\n";
		echo "</form>\n";
		echo "</td>\n";

		// Cluster
		if ($data->hasRecluster()) {
			$disabled = '';
			echo "<td style=\"text-align: center; vertical-align: bottom\">\n";
			echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
			echo $misc->form;
			if ($type == 'table') {
				echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
				echo "<input type=\"hidden\" name=\"subject\" value=\"table\" />\n";
				if (!$data->alreadyClustered($_REQUEST['object'])) {
					$disabled = 'disabled="disabled" ';
					echo "{$lang['strnoclusteravailable']}<br />";
				}
			}
			echo "<p><input type=\"hidden\" name=\"action\" value=\"confirm_cluster\" />\n";
			echo "<input type=\"submit\" value=\"{$lang['strclusterindex']}\" $disabled/></p>\n";
			echo "</form>\n";
			echo "</td>\n";
		}

		// Reindex
		echo "<td style=\"text-align: center; vertical-align: bottom\">\n";
		echo "<form action=\"/src/views/{$script}\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"confirm_reindex\" />\n";
		echo $misc->form;
		if ($type == 'table') {
			echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['object']), "\" />\n";
			echo "<input type=\"hidden\" name=\"subject\" value=\"table\" />\n";
		}
		echo "<input type=\"submit\" value=\"{$lang['strreindex']}\" /></p>\n";
		echo "</form>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";

		// Autovacuum
		if ($data->hasAutovacuum()) {
			// get defaults values for autovacuum
			$defaults = $data->getAutovacuum();
			// Fetch the autovacuum properties from the database or table if != ''
			if ($type == 'table') {
				$autovac = $data->getTableAutovacuum($_REQUEST['table']);
			} else {
				$autovac = $data->getTableAutovacuum();
			}

			echo "<br /><br /><h2>{$lang['strvacuumpertable']}</h2>";
			echo '<p>' . (($defaults['autovacuum'] == 'on') ? $lang['strturnedon'] : $lang['strturnedoff']) . '</p>';
			echo "<p class=\"message\">{$lang['strnotdefaultinred']}</p>";

			function enlight($f, $p) {
				if (isset($f[$p[0]]) and ($f[$p[0]] != $p[1])) {
					return "<span style=\"color:#F33;font-weight:bold\">" . htmlspecialchars($f[$p[0]]) . "</span>";
				}

				return htmlspecialchars($p[1]);
			}

			$columns = [
				'namespace' => [
					'title' => $lang['strschema'],
					'field' => Decorator::field('nspname'),
					'url' => "/redirect/schema?{$misc->href}&amp;",
					'vars' => ['schema' => 'nspname'],
				],
				'relname' => [
					'title' => $lang['strtable'],
					'field' => Decorator::field('relname'),
					'url' => "/redirect/table?{$misc->href}&amp;",
					'vars' => ['table' => 'relname', 'schema' => 'nspname'],
				],
				'autovacuum_enabled' => [
					'title' => $lang['strenabled'],
					'field' => Decorator::callback('enlight', ['autovacuum_enabled', $defaults['autovacuum']]),
					'type' => 'verbatim',
				],
				'autovacuum_vacuum_threshold' => [
					'title' => $lang['strvacuumbasethreshold'],
					'field' => Decorator::callback('enlight', ['autovacuum_vacuum_threshold', $defaults['autovacuum_vacuum_threshold']]),
					'type' => 'verbatim',
				],
				'autovacuum_vacuum_scale_factor' => [
					'title' => $lang['strvacuumscalefactor'],
					'field' => Decorator::callback('enlight', ['autovacuum_vacuum_scale_factor', $defaults['autovacuum_vacuum_scale_factor']]),
					'type' => 'verbatim',
				],
				'autovacuum_analyze_threshold' => [
					'title' => $lang['stranalybasethreshold'],
					'field' => Decorator::callback('enlight', ['autovacuum_analyze_threshold', $defaults['autovacuum_analyze_threshold']]),
					'type' => 'verbatim',
				],
				'autovacuum_analyze_scale_factor' => [
					'title' => $lang['stranalyzescalefactor'],
					'field' => Decorator::callback('enlight', ['autovacuum_analyze_scale_factor', $defaults['autovacuum_analyze_scale_factor']]),
					'type' => 'verbatim',
				],
				'autovacuum_vacuum_cost_delay' => [
					'title' => $lang['strvacuumcostdelay'],
					'field' => Decorator::concat(Decorator::callback('enlight', ['autovacuum_vacuum_cost_delay', $defaults['autovacuum_vacuum_cost_delay']]), 'ms'),
					'type' => 'verbatim',
				],
				'autovacuum_vacuum_cost_limit' => [
					'title' => $lang['strvacuumcostlimit'],
					'field' => Decorator::callback('enlight', ['autovacuum_vacuum_cost_limit', $defaults['autovacuum_vacuum_cost_limit']]),
					'type' => 'verbatim',
				],
			];

			// Maybe we need to check permissions here?
			$columns['actions'] = ['title' => $lang['stractions']];

			$actions = [
				'edit' => [
					'content' => $lang['stredit'],
					'attr' => [
						'href' => [
							'url' => $script,
							'urlvars' => [
								'subject' => $type,
								'action' => 'confeditautovac',
								'schema' => Decorator::field('nspname'),
								'table' => Decorator::field('relname'),
							],
						],
					],
				],
				'delete' => [
					'content' => $lang['strdelete'],
					'attr' => [
						'href' => [
							'url' => $script,
							'urlvars' => [
								'subject' => $type,
								'action' => 'confdelautovac',
								'schema' => Decorator::field('nspname'),
								'table' => Decorator::field('relname'),
							],
						],
					],
				],
			];

			if ($type == 'table') {
				unset($actions['edit']['vars']['schema'],
					$actions['delete']['vars']['schema'],
					$columns['namespace'],
					$columns['relname']
				);
			}

			echo $this->printTable($autovac, $columns, $actions, 'admin-admin', $lang['strnovacuumconf']);

			if (($type == 'table') and ($autovac->recordCount() == 0)) {
				echo "<br />";
				echo "<a href=\"tables.php?action=confeditautovac&amp;{$misc->href}&amp;table=", htmlspecialchars($_REQUEST['table'])
				, "\">{$lang['straddvacuumtable']}</a>";
			}
		}
	}

	function adminActions($action, $type) {

		if ($type == 'database') {
			$_REQUEST['object'] = $_REQUEST['database'];
			$this->script       = 'database.php';
		} else {
			// $_REQUEST['table'] is no set if we are in the schema page
			$_REQUEST['object'] = (isset($_REQUEST['table']) ? $_REQUEST['table'] : '');
			$this->script       = 'tables.php';
		}

		$script = $this->script;

		switch ($action) {
			case 'confirm_cluster':
				$this->doCluster($type, true);
				break;
			case 'confirm_reindex':
				$this->doReindex($type, true);
				break;
			case 'confirm_analyze':
				$this->doAnalyze($type, true);
				break;
			case 'confirm_vacuum':
				$this->doVacuum($type, true);
				break;
			case 'cluster':
				if (isset($_POST['cluster'])) {
					$this->doCluster($type);
				}

				// if multi-action from table canceled: back to the schema default page
				else if (($type == 'table') && is_array($_REQUEST['object'])) {
					$this->doDefault();
				} else {
					$this->doAdmin($type);
				}

				break;
			case 'reindex':
				if (isset($_POST['reindex'])) {
					$this->doReindex($type);
				}

				// if multi-action from table canceled: back to the schema default page
				else if (($type == 'table') && is_array($_REQUEST['object'])) {
					$this->doDefault();
				} else {
					$this->doAdmin($type);
				}

				break;
			case 'analyze':
				if (isset($_POST['analyze'])) {
					$this->doAnalyze($type);
				}

				// if multi-action from table canceled: back to the schema default page
				else if (($type == 'table') && is_array($_REQUEST['object'])) {
					$this->doDefault();
				} else {
					$this->doAdmin($type);
				}

				break;
			case 'vacuum':
				if (isset($_POST['vacuum'])) {
					$this->doVacuum($type);
				}

				// if multi-action from table canceled: back to the schema default page
				else if (($type == 'table') && is_array($_REQUEST['object'])) {
					$this->doDefault();
				} else {
					$this->doAdmin($type);
				}

				break;
			case 'admin':
				$this->doAdmin($type);
				break;
			case 'confeditautovac':
				$this->doEditAutovacuum($type, true);
				break;
			case 'confdelautovac':
				$this->doDropAutovacuum($type, true);
				break;
			case 'confaddautovac':
				$this->doAddAutovacuum(true);
				break;
			case 'editautovac':
				if (isset($_POST['save'])) {
					$this->doEditAutovacuum($type, false);
				} else {
					$this->doAdmin($type);
				}

				break;
			case 'delautovac':
				$this->doDropAutovacuum($type, false);
				break;
			default:
				return false;
		}
		return true;
	}

}