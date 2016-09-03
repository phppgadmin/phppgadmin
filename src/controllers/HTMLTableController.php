<?php

namespace PHPPgAdmin\Controller;

/**
 * Class to render tables. Formerly part of Misc.php
 *
 */
class HTMLTableController extends BaseController {
	public $_name = 'HTMLTableController';

	/**
	 * Display a table of data.
	 * @param $tabledata A set of data to be formatted, as returned by $data->getDatabases() etc.
	 * @param $columns   An associative array of columns to be displayed:
	 *			$columns = array(
	 *				column_id => array(
	 *					'title' => Column heading,
	 * 					'class' => The class to apply on the column cells,
	 *					'field' => Field name for $tabledata->fields[...],
	 *					'help'  => Help page for this column,
	 *				), ...
	 *			);
	 * @param $actions   Actions that can be performed on each object:
	 *			$actions = array(
	 *				* multi action support
	 *				* parameters are serialized for each entries and given in $_REQUEST['ma']
	 *				'multiactions' => array(
	 *					'keycols' => Associative array of (URL variable => field name), // fields included in the form
	 *					'url' => URL submission,
	 *					'default' => Default selected action in the form. If null, an empty action is added & selected
	 *				),
	 *				* actions *
	 *				action_id => array(
	 *					'title' => Action heading,
	 *					'url'   => Static part of URL.  Often we rely
	 *							   relative urls, usually the page itself (not '' !), or just a query string,
	 *					'vars'  => Associative array of (URL variable => field name),
	 *					'multiaction' => Name of the action to execute.
	 *										Add this action to the multi action form
	 *				), ...
	 *			);
	 * @param $place     Place where the $actions are displayed. Like 'display-browse',  where 'display'
	 * is the entrypoint (/src/views/display.php) and 'browse' is the action used inside its controller (in this case, doBrowse).
	 * @param $nodata    (optional) Message to display if data set is empty.
	 * @param $pre_fn    (optional) callback closure for each row. It will be passed two params: $rowdata and $actions,
	 *  it may be used to derive new fields or modify actions.
	 *  It can return an array of actions specific to the row,  or if nothing is returned then the standard actions are used.
	 *  (see TablePropertyController and ConstraintController for examples)
	 *  The function must not must not store urls because	 they are relative and won't work out of context.
	 */
	public function printTable(&$tabledata, &$columns, &$actions, $place, $nodata = null, $pre_fn = null) {

		$data           = $this->data;
		$misc           = $this->misc;
		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;

		// Action buttons hook's place
		$plugin_functions_parameters = [
			'actionbuttons' => &$actions,
			'place' => $place,
		];
		$plugin_manager->do_hook('actionbuttons', $plugin_functions_parameters);

		if ($this->has_ma = isset($actions['multiactions'])) {
			$ma = $actions['multiactions'];
		}
		$tablehtml = '';

		unset($actions['multiactions']);

		if ($tabledata->recordCount() > 0) {

			// Remove the 'comment' column if they have been disabled
			if (!$this->conf['show_comments']) {
				unset($columns['comment']);
			}

			if (isset($columns['comment'])) {
				// Uncomment this for clipped comments.
				// TODO: This should be a user option.
				//$columns['comment']['params']['clip'] = true;
			}

			if ($this->has_ma) {
				$tablehtml .= "<script src=\"/js/multiactionform.js\" type=\"text/javascript\"></script>\n";
				$tablehtml .= "<form id=\"multi_form\" action=\"{$ma['url']}\" method=\"post\" enctype=\"multipart/form-data\">\n";
				if (isset($ma['vars'])) {
					foreach ($ma['vars'] as $k => $v) {
						$tablehtml .= "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
					}
				}

			}

			$tablehtml .= '<table width="auto" class="' . $place . '">' . "\n";

			$tablehtml .= $this->getThead($columns, $actions);

			$tablehtml .= $this->getTbody($columns, $actions, $tabledata);

			$tablehtml .= $this->getTfooter($columns, $actions);

			$tablehtml .= "</table>\n";

			// Multi action table footer w/ options & [un]check'em all
			if ($this->has_ma) {
				// if default is not set or doesn't exist, set it to null
				if (!isset($ma['default']) || !isset($actions[$ma['default']])) {
					$ma['default'] = null;
				}

				$tablehtml .= "<br />\n";
				$tablehtml .= "<table>\n";
				$tablehtml .= "<tr>\n";
				$tablehtml .= "<th class=\"data\" style=\"text-align: left\" colspan=\"3\">{$lang['stractionsonmultiplelines']}</th>\n";
				$tablehtml .= "</tr>\n";
				$tablehtml .= "<tr class=\"row1\">\n";
				$tablehtml .= "<td>";
				$tablehtml .= "<a href=\"#\" onclick=\"javascript:checkAll(true);\">{$lang['strselectall']}</a> / ";
				$tablehtml .= "<a href=\"#\" onclick=\"javascript:checkAll(false);\">{$lang['strunselectall']}</a></td>\n";
				$tablehtml .= "<td>&nbsp;--->&nbsp;</td>\n";
				$tablehtml .= "<td>\n";
				$tablehtml .= "\t<select name=\"action\">\n";
				if ($ma['default'] == null) {
					$tablehtml .= "\t\t<option value=\"\">--</option>\n";
				}

				foreach ($actions as $k => $a) {
					if (isset($a['multiaction'])) {
						$tablehtml .= "\t\t<option value=\"{$a['multiaction']}\"" . ($ma['default'] == $k ? ' selected="selected"' : '') . ">{$a['content']}</option>\n";
					}
				}

				$tablehtml .= "\t</select>\n";
				$tablehtml .= "<input type=\"submit\" value=\"{$lang['strexecute']}\" />\n";
				$tablehtml .= $this->getForm();
				$tablehtml .= "</td>\n";
				$tablehtml .= "</tr>\n";
				$tablehtml .= "</table>\n";
				$tablehtml .= '</form>';
			};

		} else {
			if (!is_null($nodata)) {
				$tablehtml .= "<p>{$nodata}</p>\n";
			}

		}
		return $tablehtml;
	}

	private function getTbody($columns, $actions, $tabledata) {
		// Display table rows
		$i          = 0;
		$tbody_html = '<tbody>';
		while (!$tabledata->EOF) {
			$id = ($i % 2) + 1;

			unset($alt_actions);
			if (!is_null($pre_fn)) {
				$alt_actions = $pre_fn($tabledata, $actions);
			}

			if (!isset($alt_actions)) {
				$alt_actions = &$actions;
			}

			$tbody_html .= "<tr class=\"data{$id}\">\n";
			if ($this->has_ma) {
				foreach ($ma['keycols'] as $k => $v) {
					$a[$k] = $tabledata->fields[$v];
				}

				$tbody_html .= "<td>";
				$tbody_html .= "<input type=\"checkbox\" name=\"ma[]\" value=\"" . htmlentities(serialize($a), ENT_COMPAT, 'UTF-8') . "\" />";
				$tbody_html .= "</td>\n";
			}

			foreach ($columns as $column_id => $column) {

				// Apply default values for missing parameters
				if (isset($column['url']) && !isset($column['vars'])) {
					$column['vars'] = [];
				}

				switch ($column_id) {
					case 'actions':
						foreach ($alt_actions as $action) {
							if (isset($action['disable']) && $action['disable'] === true) {
								$tbody_html .= "<td></td>\n";
							} else {
								$tbody_html .= "<td class=\"opbutton{$id} {$class}\">";
								$action['fields'] = $tabledata->fields;
								$tbody_html .= $this->misc->printLink($action, false);
								$tbody_html .= "</td>\n";
							}
						}
						break;
					case 'comment':
						$tbody_html .= "<td class='comment_cell'>";
						$val = value($column['field'], $tabledata->fields);
						if (!is_null($val)) {
							$tbody_html .= htmlentities($val);
						}
						$tbody_html .= "</td>";
						break;
					default:
						$tbody_html .= "<td{$class}>";
						$val = value($column['field'], $tabledata->fields);
						if (!is_null($val)) {
							if (isset($column['url'])) {
								$tbody_html .= "<a href=\"{$column['url']}";
								$tbody_html .= $this->printUrlVars($column['vars'], $tabledata->fields, false);
								$tbody_html .= "\">";
							}
							$type   = isset($column['type']) ? $column['type'] : null;
							$params = isset($column['params']) ? $column['params'] : [];
							$tbody_html .= $this->misc->printVal($val, $type, $params);
							if (isset($column['url'])) {
								$tbody_html .= "</a>";
							}

						}

						$tbody_html .= "</td>\n";
						break;
				}
			}
			$tbody_html .= "</tr>\n";

			$tabledata->moveNext();
			$i++;
		}

		$tbody_html .= '</tbody>';
		return $tbody_html;
	}

	private function getThead($columns, $actions) {
		$thead_html = "<thead><tr>\n";

		// Handle cases where no class has been passed
		if (isset($column['class'])) {
			$class = $column['class'] !== '' ? " class=\"{$column['class']}\"" : '';
		} else {
			$class = '';
		}

		// Display column headings
		if ($this->has_ma) {
			$thead_html .= "<th></th>";
		}

		foreach ($columns as $column_id => $column) {
			switch ($column_id) {
				case 'actions':
					if (sizeof($actions) > 0) {
						$thead_html .= "<th class=\"data\" colspan=\"" . count($actions) . "\">{$column['title']}</th>\n";
					}

					break;
				default:
					$thead_html .= "<th class=\"data{$class}\">";
					if (isset($column['help'])) {
						$thead_html .= $this->misc->printHelp($column['title'], $column['help'], false);
					} else {
						$thead_html .= $column['title'];
					}

					$thead_html .= "</th>\n";
					break;
			}
		}
		$thead_html .= "</tr></thead>\n";

		return $thead_html;
	}

	private function getTfooter($columns, $actions) {
		$tfoot_html = "<tfoot><tr>\n";

		// Handle cases where no class has been passed
		if (isset($column['class'])) {
			$class = $column['class'] !== '' ? " class=\"{$column['class']}\"" : '';
		} else {
			$class = '';
		}

		// Display column headings
		if ($this->has_ma) {
			$tfoot_html .= "<td></td>";
		}

		foreach ($columns as $column_id => $column) {
			switch ($column_id) {
				case 'actions':
					if (sizeof($actions) > 0) {
						$tfoot_html .= "<td class=\"data\" colspan=\"" . count($actions) . "\"></td>\n";
					}

					break;
				default:
					$tfoot_html .= "<td class=\"data{$class}\"></td>\n";
					break;
			}
		}
		$tfoot_html .= "</tr></tfoot>\n";

		return $tfoot_html;
	}

	private function getForm() {
		if (!$this->form) {
			$this->form = $this->misc->setForm();
		}
		return $this->form;
	}

	private function printUrlVars(&$vars, &$fields, $do_print = true) {
		$url_vars_html = '';
		foreach ($vars as $var => $varfield) {
			$url_vars_html .= "{$var}=" . urlencode($fields[$varfield]) . "&amp;";
		}
		if ($do_print) {
			echo $url_vars_html;
		} else {
			return $url_vars_html;
		}
	}

}
