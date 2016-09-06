<?php

namespace PHPPgAdmin\Controller;

/**
 * Base controller class
 */
class SQLEditController extends BaseController {
	public $_name      = 'SQLEditController';
	public $query      = '';
	public $subject    = '';
	public $start_time = null;
	public $duration   = null;

/**
 * Private function to display server and list of databases
 */
	function _printConnection($action) {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		// The javascript action on the select box reloads the
		// popup whenever the server or database is changed.
		// This ensures that the correct page encoding is used.
		$onchange = "onchange=\"location.href='/sqledit/" .
		urlencode($action) . "?server=' + encodeURI(server.options[server.selectedIndex].value) + '&amp;database=' + encodeURI(database.options[database.selectedIndex].value) + ";

		// The exact URL to reload to is different between SQL and Find mode, however.
		if ($action == 'find') {
			$onchange .= "'&amp;term=' + encodeURI(term.value) + '&amp;filter=' + encodeURI(filter.value) + '&amp;'\"";
		} else {
			$onchange .= "'&amp;query=' + encodeURI(query.value) + '&amp;search_path=' + encodeURI(search_path.value) + (paginate.checked ? '&amp;paginate=on' : '')  + '&amp;'\"";
		}

		return $misc->printConnection($onchange, false);
	}

	/**
	 * Searches for a named database object
	 */
	function doFind() {

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

		$default_html = $this->printTabs($misc->getNavTabs('popup'), 'find', false);

		$default_html .= "<form action=\"database.php\" method=\"post\" target=\"detail\">\n";
		$default_html .= $this->_printConnection('find');
		$default_html .= "<p><input class=\"focusme\" name=\"term\" value=\"" . htmlspecialchars($_REQUEST['term']) . "\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" />\n";

		// Output list of filters.  This is complex due to all the 'has' and 'conf' feature possibilities
		$default_html .= "<select name=\"filter\">\n";
		$default_html .= "\t<option value=\"\"" . ($_REQUEST['filter'] == '' ? ' selected="selected" ' : '') . ">{$lang['strallobjects']}</option>\n";
		$default_html .= "\t<option value=\"SCHEMA\"" . ($_REQUEST['filter'] == 'SCHEMA' ? ' selected="selected" ' : '') . ">{$lang['strschemas']}</option>\n";
		$default_html .= "\t<option value=\"TABLE\"" . ($_REQUEST['filter'] == 'TABLE' ? ' selected="selected" ' : '') . ">{$lang['strtables']}</option>\n";
		$default_html .= "\t<option value=\"VIEW\"" . ($_REQUEST['filter'] == 'VIEW' ? ' selected="selected" ' : '') . ">{$lang['strviews']}</option>\n";
		$default_html .= "\t<option value=\"SEQUENCE\"" . ($_REQUEST['filter'] == 'SEQUENCE' ? ' selected="selected" ' : '') . ">{$lang['strsequences']}</option>\n";
		$default_html .= "\t<option value=\"COLUMN\"" . ($_REQUEST['filter'] == 'COLUMN' ? ' selected="selected" ' : '') . ">{$lang['strcolumns']}</option>\n";
		$default_html .= "\t<option value=\"RULE\"" . ($_REQUEST['filter'] == 'RULE' ? ' selected="selected" ' : '') . ">{$lang['strrules']}</option>\n";
		$default_html .= "\t<option value=\"INDEX\"" . ($_REQUEST['filter'] == 'INDEX' ? ' selected="selected" ' : '') . ">{$lang['strindexes']}</option>\n";
		$default_html .= "\t<option value=\"TRIGGER\"" . ($_REQUEST['filter'] == 'TRIGGER' ? ' selected="selected" ' : '') . ">{$lang['strtriggers']}</option>\n";
		$default_html .= "\t<option value=\"CONSTRAINT\"" . ($_REQUEST['filter'] == 'CONSTRAINT' ? ' selected="selected" ' : '') . ">{$lang['strconstraints']}</option>\n";
		$default_html .= "\t<option value=\"FUNCTION\"" . ($_REQUEST['filter'] == 'FUNCTION' ? ' selected="selected" ' : '') . ">{$lang['strfunctions']}</option>\n";
		$default_html .= "\t<option value=\"DOMAIN\"" . ($_REQUEST['filter'] == 'DOMAIN' ? ' selected="selected" ' : '') . ">{$lang['strdomains']}</option>\n";
		if ($conf['show_advanced']) {
			$default_html .= "\t<option value=\"AGGREGATE\"" . ($_REQUEST['filter'] == 'AGGREGATE' ? ' selected="selected" ' : '') . ">{$lang['straggregates']}</option>\n";
			$default_html .= "\t<option value=\"TYPE\"" . ($_REQUEST['filter'] == 'TYPE' ? ' selected="selected" ' : '') . ">{$lang['strtypes']}</option>\n";
			$default_html .= "\t<option value=\"OPERATOR\"" . ($_REQUEST['filter'] == 'OPERATOR' ? ' selected="selected" ' : '') . ">{$lang['stroperators']}</option>\n";
			$default_html .= "\t<option value=\"OPCLASS\"" . ($_REQUEST['filter'] == 'OPCLASS' ? ' selected="selected" ' : '') . ">{$lang['stropclasses']}</option>\n";
			$default_html .= "\t<option value=\"CONVERSION\"" . ($_REQUEST['filter'] == 'CONVERSION' ? ' selected="selected" ' : '') . ">{$lang['strconversions']}</option>\n";
			$default_html .= "\t<option value=\"LANGUAGE\"" . ($_REQUEST['filter'] == 'LANGUAGE' ? ' selected="selected" ' : '') . ">{$lang['strlanguages']}</option>\n";
		}
		$default_html .= "</select>\n";

		$default_html .= "<input type=\"submit\" value=\"{$lang['strfind']}\" />\n";
		$default_html .= "<input type=\"hidden\" name=\"action\" value=\"find\" /></p>\n";
		$default_html .= "</form>\n";

		// Default focus
		//$misc->setFocus('forms[0].term');
		return $default_html;
	}

	/**
	 * Allow execution of arbitrary SQL statements on a database
	 */
	function doDefault() {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		if (!isset($_SESSION['sqlquery'])) {
			$_SESSION['sqlquery'] = '';
		}

		$default_html = $this->printTabs($misc->getNavTabs('popup'), 'sql', false);

		$default_html .= '<form action="/src/views/sql.php" method="post" enctype="multipart/form-data" class="sqlform" id="sqlform" target="detail">';
		$default_html .= "\n";
		$default_html .= $this->_printConnection('sql');

		$default_html .= "\n";

		if (!isset($_REQUEST['search_path'])) {
			$_REQUEST['search_path'] = implode(',', $data->getSearchPath());
		}
		$search_path = htmlspecialchars($_REQUEST['search_path']);
		$sqlquery    = htmlspecialchars($_SESSION['sqlquery']);

		$default_html .= ' <div class="searchpath">';
		$default_html .= "<label>";
		$default_html .= $misc->printHelp($lang['strsearchpath'], 'pg.schema.search_path', false);

		$default_html .= ': <input type="text" name="search_path" size="50" value="' . $search_path . '" />';
		$default_html .= "</label>\n";
		$default_html .= "</div>\n";

		$default_html .= '<div id="queryedition" style="padding:1%;width:98%;float:left;">';
		$default_html .= "\n";
		$default_html .= '<textarea style="width:98%;" rows="10" cols="50" name="query" id="query" resizable="true">' . $sqlquery . "</textarea>";
		$default_html .= "\n";
		$default_html .= "</div>\n";

		// Check that file uploads are enabled
		if (ini_get('file_uploads')) {
			// Don't show upload option if max size of uploads is zero
			$max_size = $misc->inisizeToBytes(ini_get('upload_max_filesize'));
			if (is_double($max_size) && $max_size > 0) {
				$default_html .= "<p>";
				$default_html .= '<input type="hidden" name="MAX_FILE_SIZE" value="' . $max_size . '" />';
				$default_html .= "\n";
				$default_html .= '<label for="script">' . $lang['struploadscript'] . '</label>';
				$default_html .= ' <input id="script" name="script" type="file" /></p>';
				$default_html .= "</p>\n";
			}
		}
		$checked = (isset($_REQUEST['paginate']) ? ' checked="checked"' : '');
		$default_html .= '<p>';
		$default_html .= '<label for="paginate"><input type="checkbox" id="paginate" name="paginate"' . $checked . ' />&nbsp;' . $lang['strpaginate'] . '</label>';
		$default_html .= "</p>\n";
		$default_html .= '<p><input type="submit" name="execute" accesskey="r" value="' . $lang['strexecute'] . '" />';
		$default_html .= "\n";
		$default_html .= '<input type="reset" accesskey="q" value="' . $lang['strreset'] . '" /></p>';
		$default_html .= "\n";
		$default_html .= "</form>";
		$default_html .= "\n";

		// Default focus
		//$misc->setFocus('forms[0].query');
		return $default_html;

	}

	public function render() {
		$conf   = $this->conf;
		$lang   = $this->lang;
		$misc   = $this->misc;
		$action = $this->action;
		$data   = $misc->getDatabaseAccessor();

		switch ($action) {
			case 'find':
				$view_param = ['title' => $this->lang['strfind']];
				$body_text  = $this->doFind();
				break;
			case 'sql':
			default:
				$view_param = ['title' => $this->lang['strsql']];
				$body_text  = $this->doDefault();

				break;
		}

		echo $this->view->fetch('sqledit_header.twig', $view_param);
		echo $body_text;
		echo $this->view->fetch('sqledit.twig');

		$misc->setWindowName('sqledit');

	}

}
