<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class SequenceController extends BaseController {
	public $_name = 'SequenceController';

/**
 * Display list of all sequences in the database/schema
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'sequences');
		$misc->printMsg($msg);

		// Get all sequences
		$sequences = $data->getSequences();

		$columns = [
			'sequence' => [
				'title' => $lang['strsequence'],
				'field' => Decorator::field('seqname'),
				'url' => "sequences.php?action=properties&amp;{$misc->href}&amp;",
				'vars' => ['sequence' => 'seqname'],
			],
			'owner' => [
				'title' => $lang['strowner'],
				'field' => Decorator::field('seqowner'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('seqcomment'),
			],
		];

		$actions = [
			'multiactions' => [
				'keycols' => ['sequence' => 'seqname'],
				'url' => 'sequences.php',
			],
			'alter' => [
				'content' => $lang['stralter'],
				'attr' => [
					'href' => [
						'url' => 'sequences.php',
						'urlvars' => [
							'action' => 'confirm_alter',
							'subject' => 'sequence',
							'sequence' => Decorator::field('seqname'),
						],
					],
				],
			],
			'drop' => [
				'content' => $lang['strdrop'],
				'attr' => [
					'href' => [
						'url' => 'sequences.php',
						'urlvars' => [
							'action' => 'confirm_drop',
							'sequence' => Decorator::field('seqname'),
						],
					],
				],
				'multiaction' => 'confirm_drop',
			],
			'privileges' => [
				'content' => $lang['strprivileges'],
				'attr' => [
					'href' => [
						'url' => 'privileges.php',
						'urlvars' => [
							'subject' => 'sequence',
							'sequence' => Decorator::field('seqname'),
						],
					],
				],
			],
		];

		echo $this->printTable($sequences, $columns, $actions, 'sequences-sequences', $lang['strnosequences']);

		$misc->printNavLinks(['create' => [
			'attr' => [
				'href' => [
					'url' => 'sequences.php',
					'urlvars' => [
						'action' => 'create',
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
					],
				],
			],
			'content' => $lang['strcreatesequence'],
		]], 'sequences-sequences', get_defined_vars());
	}

/**
 * Display the properties of a sequence
 */
	public function doProperties($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();
		$this->printTrail('sequence');
		$misc->printTitle($lang['strproperties'], 'pg.sequence');
		$misc->printMsg($msg);

		// Fetch the sequence information
		$sequence = $data->getSequence($_REQUEST['sequence']);

		if (is_object($sequence) && $sequence->recordCount() > 0) {
			$sequence->fields['is_cycled'] = $data->phpBool($sequence->fields['is_cycled']);
			$sequence->fields['is_called'] = $data->phpBool($sequence->fields['is_called']);

			// Show comment if any
			if ($sequence->fields['seqcomment'] !== null) {
				echo "<p class=\"comment\">", $misc->printVal($sequence->fields['seqcomment']), "</p>\n";
			}

			echo "<table border=\"0\">";
			echo "<tr><th class=\"data\">{$lang['strname']}</th>";
			if ($data->hasAlterSequenceStart()) {
				echo "<th class=\"data\">{$lang['strstartvalue']}</th>";
			}
			echo "<th class=\"data\">{$lang['strlastvalue']}</th>";
			echo "<th class=\"data\">{$lang['strincrementby']}</th>";
			echo "<th class=\"data\">{$lang['strmaxvalue']}</th>";
			echo "<th class=\"data\">{$lang['strminvalue']}</th>";
			echo "<th class=\"data\">{$lang['strcachevalue']}</th>";
			echo "<th class=\"data\">{$lang['strlogcount']}</th>";
			echo "<th class=\"data\">{$lang['strcancycle']}</th>";
			echo "<th class=\"data\">{$lang['striscalled']}</th></tr>";
			echo "<tr>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['seqname']), "</td>";
			if ($data->hasAlterSequenceStart()) {
				echo "<td class=\"data1\">", $misc->printVal($sequence->fields['start_value']), "</td>";
			}
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['last_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['increment_by']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['max_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['min_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['cache_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['log_cnt']), "</td>";
			echo "<td class=\"data1\">", ($sequence->fields['is_cycled'] ? $lang['stryes'] : $lang['strno']), "</td>";
			echo "<td class=\"data1\">", ($sequence->fields['is_called'] ? $lang['stryes'] : $lang['strno']), "</td>";
			echo "</tr>";
			echo "</table>";

			$navlinks = [
				'alter' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'action' => 'confirm_alter',
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
								'sequence' => $sequence->fields['seqname'],
							],
						],
					],
					'content' => $lang['stralter'],
				],
				'setval' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'action' => 'confirm_setval',
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
								'sequence' => $sequence->fields['seqname'],
							],
						],
					],
					'content' => $lang['strsetval'],
				],
				'nextval' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'action' => 'nextval',
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
								'sequence' => $sequence->fields['seqname'],
							],
						],
					],
					'content' => $lang['strnextval'],
				],
				'restart' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'action' => 'restart',
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
								'sequence' => $sequence->fields['seqname'],
							],
						],
					],
					'content' => $lang['strrestart'],
				],
				'reset' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'action' => 'reset',
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
								'sequence' => $sequence->fields['seqname'],
							],
						],
					],
					'content' => $lang['strreset'],
				],
				'showall' => [
					'attr' => [
						'href' => [
							'url' => 'sequences.php',
							'urlvars' => [
								'server' => $_REQUEST['server'],
								'database' => $_REQUEST['database'],
								'schema' => $_REQUEST['schema'],
							],
						],
					],
					'content' => $lang['strshowallsequences'],
				],
			];

			if (!$data->hasAlterSequenceStart()) {
				unset($navlinks['restart']);
			}

			$misc->printNavLinks($navlinks, 'sequences-properties', get_defined_vars());
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

/**
 * Drop a sequence
 */
	public function doDrop($confirm, $msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (empty($_REQUEST['sequence']) && empty($_REQUEST['ma'])) {
			$this->doDefault($lang['strspecifysequencetodrop']);
			exit();
		}

		if ($confirm) {
			$this->printTrail('sequence');
			$misc->printTitle($lang['strdrop'], 'pg.sequence.drop');
			$misc->printMsg($msg);

			echo "<form action=\"/src/views/sequences.php\" method=\"post\">\n";

			//If multi drop
			if (isset($_REQUEST['ma'])) {
				foreach ($_REQUEST['ma'] as $v) {
					$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
					echo "<p>", sprintf($lang['strconfdropsequence'], $misc->printVal($a['sequence'])), "</p>\n";
					printf('<input type="hidden" name="sequence[]" value="%s" />', htmlspecialchars($a['sequence']));
				}
			} else {
				echo "<p>", sprintf($lang['strconfdropsequence'], $misc->printVal($_REQUEST['sequence'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"sequence\" value=\"", htmlspecialchars($_REQUEST['sequence']), "\" />\n";
			}

			echo "<p><input type=\"checkbox\" id=\"cascade\" name=\"cascade\" /> <label for=\"cascade\">{$lang['strcascade']}</label></p>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"drop\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"drop\" value=\"{$lang['strdrop']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			if (is_array($_POST['sequence'])) {
				$msg    = '';
				$status = $data->beginTransaction();
				if ($status == 0) {
					foreach ($_POST['sequence'] as $s) {
						$status = $data->dropSequence($s, isset($_POST['cascade']));
						if ($status == 0) {
							$msg .= sprintf('%s: %s<br />', htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strsequencedropped']);
						} else {
							$data->endTransaction();
							$this->doDefault(sprintf('%s%s: %s<br />', $msg, htmlentities($s, ENT_QUOTES, 'UTF-8'), $lang['strsequencedroppedbad']));
							return;
						}
					}
				}
				if ($data->endTransaction() == 0) {
					// Everything went fine, back to the Default page....
					$this->misc->setReloadBrowser(true);
					$this->doDefault($msg);
				} else {
					$this->doDefault($lang['strsequencedroppedbad']);
				}

			} else {
				$status = $data->dropSequence($_POST['sequence'], isset($_POST['cascade']));
				if ($status == 0) {
					$this->misc->setReloadBrowser(true);
					$this->doDefault($lang['strsequencedropped']);
				} else {
					$this->doDrop(true, $lang['strsequencedroppedbad']);
				}

			}
		}
	}

/**
 * Displays a screen where they can enter a new sequence
 */
	public function doCreateSequence($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['formSequenceName'])) {
			$_POST['formSequenceName'] = '';
		}

		if (!isset($_POST['formIncrement'])) {
			$_POST['formIncrement'] = '';
		}

		if (!isset($_POST['formMinValue'])) {
			$_POST['formMinValue'] = '';
		}

		if (!isset($_POST['formMaxValue'])) {
			$_POST['formMaxValue'] = '';
		}

		if (!isset($_POST['formStartValue'])) {
			$_POST['formStartValue'] = '';
		}

		if (!isset($_POST['formCacheValue'])) {
			$_POST['formCacheValue'] = '';
		}

		$this->printTrail('schema');
		$misc->printTitle($lang['strcreatesequence'], 'pg.sequence.create');
		$misc->printMsg($msg);

		echo "<form action=\"/src/views/sequences.php\" method=\"post\">\n";
		echo "<table>\n";

		echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formSequenceName\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
		htmlspecialchars($_POST['formSequenceName']), "\" /></td></tr>\n";

		echo "<tr><th class=\"data left\">{$lang['strincrementby']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formIncrement\" size=\"5\" value=\"",
		htmlspecialchars($_POST['formIncrement']), "\" /> </td></tr>\n";

		echo "<tr><th class=\"data left\">{$lang['strminvalue']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formMinValue\" size=\"5\" value=\"",
		htmlspecialchars($_POST['formMinValue']), "\" /></td></tr>\n";

		echo "<tr><th class=\"data left\">{$lang['strmaxvalue']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formMaxValue\" size=\"5\" value=\"",
		htmlspecialchars($_POST['formMaxValue']), "\" /></td></tr>\n";

		echo "<tr><th class=\"data left\">{$lang['strstartvalue']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formStartValue\" size=\"5\" value=\"",
		htmlspecialchars($_POST['formStartValue']), "\" /></td></tr>\n";

		echo "<tr><th class=\"data left\">{$lang['strcachevalue']}</th>\n";
		echo "<td class=\"data1\"><input name=\"formCacheValue\" size=\"5\" value=\"",
		htmlspecialchars($_POST['formCacheValue']), "\" /></td></tr>\n";

		echo "<tr><th class=\"data left\"><label for=\"formCycledValue\">{$lang['strcancycle']}</label></th>\n";
		echo "<td class=\"data1\"><input type=\"checkbox\" id=\"formCycledValue\" name=\"formCycledValue\" ",
		(isset($_POST['formCycledValue']) ? ' checked="checked"' : ''), " /></td></tr>\n";

		echo "</table>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"save_create_sequence\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"create\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

/**
 * Actually creates the new sequence in the database
 */
	public function doSaveCreateSequence() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// Check that they've given a name and at least one column
		if ($_POST['formSequenceName'] == '') {
			$this->doCreateSequence($lang['strsequenceneedsname']);
		} else {
			$status = $data->createSequence($_POST['formSequenceName'],
				$_POST['formIncrement'], $_POST['formMinValue'],
				$_POST['formMaxValue'], $_POST['formStartValue'],
				$_POST['formCacheValue'], isset($_POST['formCycledValue']));
			if ($status == 0) {
				$this->doDefault($lang['strsequencecreated']);
			} else {
				$this->doCreateSequence($lang['strsequencecreatedbad']);
			}
		}
	}

/**
 * Restarts a sequence
 */
	public function doRestart() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->restartSequence($_REQUEST['sequence']);
		if ($status == 0) {
			$this->doProperties($lang['strsequencerestart']);
		} else {
			$this->doProperties($lang['strsequencerestartbad']);
		}

	}

/**
 * Resets a sequence
 */
	public function doReset() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->resetSequence($_REQUEST['sequence']);
		if ($status == 0) {
			$this->doProperties($lang['strsequencereset']);
		} else {
			$this->doProperties($lang['strsequenceresetbad']);
		}

	}

/**
 * Set Nextval of a sequence
 */
	public function doNextval() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->nextvalSequence($_REQUEST['sequence']);
		if ($status == 0) {
			$this->doProperties($lang['strsequencenextval']);
		} else {
			$this->doProperties($lang['strsequencenextvalbad']);
		}

	}

/**
 * Function to save after 'setval'ing a sequence
 */
	public function doSaveSetval() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$status = $data->setvalSequence($_POST['sequence'], $_POST['nextvalue']);
		if ($status == 0) {
			$this->doProperties($lang['strsequencesetval']);
		} else {
			$this->doProperties($lang['strsequencesetvalbad']);
		}

	}

/**
 * Function to allow 'setval'ing of a sequence
 */
	public function doSetval($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('sequence');
		$misc->printTitle($lang['strsetval'], 'pg.sequence');
		$misc->printMsg($msg);

		// Fetch the sequence information
		$sequence = $data->getSequence($_REQUEST['sequence']);

		if (is_object($sequence) && $sequence->recordCount() > 0) {
			echo "<form action=\"/src/views/sequences.php\" method=\"post\">\n";
			echo "<table border=\"0\">";
			echo "<tr><th class=\"data left required\">{$lang['strlastvalue']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<input name=\"nextvalue\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			$misc->printVal($sequence->fields['last_value']), "\" /></td></tr>\n";
			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"setval\" />\n";
			echo "<input type=\"hidden\" name=\"sequence\" value=\"", htmlspecialchars($_REQUEST['sequence']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"setval\" value=\"{$lang['strsetval']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

/**
 * Function to save after altering a sequence
 */
	public function doSaveAlter() {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_POST['owner'])) {
			$_POST['owner'] = null;
		}

		if (!isset($_POST['newschema'])) {
			$_POST['newschema'] = null;
		}

		if (!isset($_POST['formIncrement'])) {
			$_POST['formIncrement'] = null;
		}

		if (!isset($_POST['formMinValue'])) {
			$_POST['formMinValue'] = null;
		}

		if (!isset($_POST['formMaxValue'])) {
			$_POST['formMaxValue'] = null;
		}

		if (!isset($_POST['formStartValue'])) {
			$_POST['formStartValue'] = null;
		}

		if (!isset($_POST['formRestartValue'])) {
			$_POST['formRestartValue'] = null;
		}

		if (!isset($_POST['formCacheValue'])) {
			$_POST['formCacheValue'] = null;
		}

		if (!isset($_POST['formCycledValue'])) {
			$_POST['formCycledValue'] = null;
		}

		$status = $data->alterSequence($_POST['sequence'], $_POST['name'], $_POST['comment'], $_POST['owner'],
			$_POST['newschema'], $_POST['formIncrement'], $_POST['formMinValue'], $_POST['formMaxValue'],
			$_POST['formRestartValue'], $_POST['formCacheValue'], isset($_POST['formCycledValue']), $_POST['formStartValue']);

		if ($status == 0) {
			if ($_POST['sequence'] != $_POST['name']) {
				// Jump them to the new view name
				$_REQUEST['sequence'] = $_POST['name'];
				// Force a browser reload
				$this->misc->setReloadBrowser(true);
			}
			if (!empty($_POST['newschema']) && ($_POST['newschema'] != $data->_schema)) {
				// Jump them to the new sequence schema
				$misc->setCurrentSchema($_POST['newschema']);
				$this->misc->setReloadBrowser(true);
			}
			$this->doProperties($lang['strsequencealtered']);
		} else {
			$this->doProperties($lang['strsequencealteredbad']);
		}

	}

/**
 * Function to allow altering of a sequence
 */
	public function doAlter($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('sequence');
		$misc->printTitle($lang['stralter'], 'pg.sequence.alter');
		$misc->printMsg($msg);

		// Fetch the sequence information
		$sequence = $data->getSequence($_REQUEST['sequence']);

		if (is_object($sequence) && $sequence->recordCount() > 0) {
			if (!isset($_POST['name'])) {
				$_POST['name'] = $_REQUEST['sequence'];
			}

			if (!isset($_POST['comment'])) {
				$_POST['comment'] = $sequence->fields['seqcomment'];
			}

			if (!isset($_POST['owner'])) {
				$_POST['owner'] = $sequence->fields['seqowner'];
			}

			if (!isset($_POST['newschema'])) {
				$_POST['newschema'] = $sequence->fields['nspname'];
			}

			// Handle Checkbox Value
			$sequence->fields['is_cycled'] = $data->phpBool($sequence->fields['is_cycled']);
			if ($sequence->fields['is_cycled']) {
				$_POST['formCycledValue'] = 'on';
			}

			echo "<form action=\"/src/views/sequences.php\" method=\"post\">\n";
			echo "<table>\n";

			echo "<tr><th class=\"data left required\">{$lang['strname']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<input name=\"name\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
			htmlspecialchars($_POST['name']), "\" /></td></tr>\n";

			if ($data->isSuperUser()) {
				// Fetch all users
				$users = $data->getUsers();

				echo "<tr><th class=\"data left required\">{$lang['strowner']}</th>\n";
				echo "<td class=\"data1\"><select name=\"owner\">";
				while (!$users->EOF) {
					$uname = $users->fields['usename'];
					echo "<option value=\"", htmlspecialchars($uname), "\"",
					($uname == $_POST['owner']) ? ' selected="selected"' : '', ">", htmlspecialchars($uname), "</option>\n";
					$users->moveNext();
				}
				echo "</select></td></tr>\n";
			}

			if ($data->hasAlterSequenceSchema()) {
				$schemas = $data->getSchemas();
				echo "<tr><th class=\"data left required\">{$lang['strschema']}</th>\n";
				echo "<td class=\"data1\"><select name=\"newschema\">";
				while (!$schemas->EOF) {
					$schema = $schemas->fields['nspname'];
					echo "<option value=\"", htmlspecialchars($schema), "\"",
					($schema == $_POST['newschema']) ? ' selected="selected"' : '', ">", htmlspecialchars($schema), "</option>\n";
					$schemas->moveNext();
				}
				echo "</select></td></tr>\n";
			}

			echo "<tr><th class=\"data left\">{$lang['strcomment']}</th>\n";
			echo "<td class=\"data1\">";
			echo "<textarea rows=\"3\" cols=\"32\" name=\"comment\">",
			htmlspecialchars($_POST['comment']), "</textarea></td></tr>\n";

			if ($data->hasAlterSequenceStart()) {
				echo "<tr><th class=\"data left\">{$lang['strstartvalue']}</th>\n";
				echo "<td class=\"data1\"><input name=\"formStartValue\" size=\"5\" value=\"",
				htmlspecialchars($sequence->fields['start_value']), "\" /></td></tr>\n";
			}

			echo "<tr><th class=\"data left\">{$lang['strrestartvalue']}</th>\n";
			echo "<td class=\"data1\"><input name=\"formRestartValue\" size=\"5\" value=\"",
			htmlspecialchars($sequence->fields['last_value']), "\" /></td></tr>\n";

			echo "<tr><th class=\"data left\">{$lang['strincrementby']}</th>\n";
			echo "<td class=\"data1\"><input name=\"formIncrement\" size=\"5\" value=\"",
			htmlspecialchars($sequence->fields['increment_by']), "\" /> </td></tr>\n";

			echo "<tr><th class=\"data left\">{$lang['strmaxvalue']}</th>\n";
			echo "<td class=\"data1\"><input name=\"formMaxValue\" size=\"5\" value=\"",
			htmlspecialchars($sequence->fields['max_value']), "\" /></td></tr>\n";

			echo "<tr><th class=\"data left\">{$lang['strminvalue']}</th>\n";
			echo "<td class=\"data1\"><input name=\"formMinValue\" size=\"5\" value=\"",
			htmlspecialchars($sequence->fields['min_value']), "\" /></td></tr>\n";

			echo "<tr><th class=\"data left\">{$lang['strcachevalue']}</th>\n";
			echo "<td class=\"data1\"><input name=\"formCacheValue\" size=\"5\" value=\"",
			htmlspecialchars($sequence->fields['cache_value']), "\" /></td></tr>\n";

			echo "<tr><th class=\"data left\"><label for=\"formCycledValue\">{$lang['strcancycle']}</label></th>\n";
			echo "<td class=\"data1\"><input type=\"checkbox\" id=\"formCycledValue\" name=\"formCycledValue\" ",
			(isset($_POST['formCycledValue']) ? ' checked="checked"' : ''), " /></td></tr>\n";

			echo "</table>\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter\" />\n";
			echo $misc->form;
			echo "<input type=\"hidden\" name=\"sequence\" value=\"", htmlspecialchars($_REQUEST['sequence']), "\" />\n";
			echo "<input type=\"submit\" name=\"alter\" value=\"{$lang['stralter']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		} else {
			echo "<p>{$lang['strnodata']}</p>\n";
		}

	}

}
