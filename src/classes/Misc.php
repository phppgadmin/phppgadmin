<?php

namespace PHPPgAdmin;

use \PHPPgAdmin\Controller\HTMLTableController;
use \PHPPgAdmin\Controller\LoginController;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Class to hold various commonly used functions
 *
 * $Id: Misc.php,v 1.171 2008/03/17 21:35:48 ioguix Exp $
 */

class Misc {

	private $_connection           = null;
	private $_no_db_connection     = false;
	private $_reload_drop_database = false;
	private $_reload_browser       = false;
	private $app                   = null;
	private $data                  = null;
	private $database              = null;
	private $server_id             = null;
	public $appLangFiles           = [];
	public $appName                = '';
	public $appVersion             = '';
	public $form                   = '';
	public $href                   = '';
	public $lang                   = [];
	private $_no_output            = false;

	/* Constructor */
	function __construct(\Slim\App $app) {
		$this->app = $app;

		$container = $app->getContainer();

		$this->lang           = $container->get('lang');
		$this->conf           = $container->get('conf');
		$this->view           = $container->get('view');
		$this->plugin_manager = $container->get('plugin_manager');
		$this->appName        = $container->get('settings')['appName'];
		$this->appVersion     = $container->get('settings')['appVersion'];
		$this->appLangFiles   = $container->get('appLangFiles');

		if (count($this->conf['servers']) === 1) {
			$info            = $this->conf['servers'][0];
			$this->server_id = $info['host'] . ':' . $info['port'] . ':' . $info['sslmode'];
		} else if (isset($_REQUEST['server'])) {
			$this->server_id = $_REQUEST['server'];
		} else if (isset($_SESSION['webdbLogin']) && count($_SESSION['webdbLogin']) > 0) {
			$this->server_id = array_keys($_SESSION['webdbLogin'])[0];
		}
		//\PC::debug($this->conf, 'conf');
		//\PC::debug($this->server_id, 'server_id');
	}

	function getConnection($database = '', $server_id = null) {
		$lang = $this->lang;

		if ($this->_connection === null) {
			if ($server_id !== null) {
				$this->server_id = $server_id;
			}
			$server_info = $this->getServerInfo($this->server_id);

			$database_to_use = $this->getDatabase($database);
			// Perform extra security checks if this config option is set
			if ($this->conf['extra_login_security']) {
				// Disallowed logins if extra_login_security is enabled.
				// These must be lowercase.
				$bad_usernames = ['pgsql', 'postgres', 'root', 'administrator'];

				$username = strtolower($server_info['username']);

				if ($server_info['password'] == '' || in_array($username, $bad_usernames)) {
					unset($_SESSION['webdbLogin'][$this->server_id]);
					$msg              = $lang['strlogindisallowed'];
					$login_controller = new LoginController($this->app->getContainer());
					return $login_controller->render();
				}
			}

			// Create the connection object and make the connection
			$this->_connection = new \PHPPgAdmin\Database\Connection(
				$server_info['host'],
				$server_info['port'],
				$server_info['sslmode'],
				$server_info['username'],
				$server_info['password'],
				$database_to_use
			);
		}
		return $this->_connection;
	}

	/**
	 * sets $_no_db_connection boolean value, allows to render scripts that do not need an active session
	 * @param boolean $flag [description]
	 */
	function setNoDBConnection($flag) {
		$this->_no_db_connection = boolval($flag);
		return $this;
	}

	function setNoOutput($flag) {
		global $_no_output;
		$this->_no_output = boolval($flag);
		$_no_output       = $this->_no_output;
		return $this;
	}

	function getNoDBConnection() {
		return $this->_no_db_connection;
	}

	function getDatabase($database = '') {

		if ($this->server_id === null && !isset($_REQUEST['database'])) {
			return null;
		}

		$server_info = $this->getServerInfo($this->server_id);

		if ($this->server_id !== null && isset($server_info['useonlydefaultdb']) && $server_info['useonlydefaultdb'] === true) {
			$this->database = $server_info['defaultdb'];
		} else if ($database !== '') {
			$this->database = $database;
		} else if (isset($_REQUEST['database'])) {
			// Connect to the current database
			$this->database = $_REQUEST['database'];
		} else {
			// or if one is not specified then connect to the default database.
			$this->database = $server_info['defaultdb'];
		}

		return $this->database;

	}

	/**
	 * [setReloadBrowser description]
	 * @param boolean $flag sets internal $_reload_browser var which will be passed to the footer methods
	 */
	function setReloadBrowser($flag) {
		global $_reload_browser;
		$_reload_browser       = $flag;
		$this->_reload_browser = boolval($flag);
		return $this;
	}
	/**
	 * [setReloadBrowser description]
	 * @param boolean $flag sets internal $_reload_browser var which will be passed to the footer methods
	 */
	function setReloadDropDatabase($flag) {
		global $_reload_drop_database;
		$_reload_drop_database       = $flag;
		$this->_reload_drop_database = boolval($flag);
		return $this;
	}

	/**
	 * Creates a database accessor
	 */
	function getDatabaseAccessor($database = '', $server_id = null) {
		$lang = $this->lang;

		if ($server_id !== null) {
			$this->server_id = $server_id;
		}

		if ($this->data === null) {
			$_connection = $this->getConnection($database, $this->server_id);

			// Get the name of the database driver we need to use.
			// The description of the server is returned in $platform.
			$_type = $_connection->getDriver($platform);
			if ($_type === null) {
				printf($lang['strpostgresqlversionnotsupported'], $postgresqlMinVer);
				exit;
			}
			$_type = '\PHPPgAdmin\Database\\' . $_type;

			$this->setServerInfo('platform', $platform, $this->server_id);
			$this->setServerInfo('pgVersion', $_connection->conn->pgVersion, $this->server_id);

			// Create a database wrapper class for easy manipulation of the
			// connection.

			$this->data           = new $_type($_connection->conn);
			$this->data->platform = $_connection->platform;

			/* we work on UTF-8 only encoding */
			$this->data->execute("SET client_encoding TO 'UTF-8'");

			if ($this->data->hasByteaHexDefault()) {
				$this->data->execute("SET bytea_output TO escape");
			}

		}

		if ($this->_no_db_connection === false && $this->getDatabase() !== null && isset($_REQUEST['schema'])) {
			$status = $this->data->setSchema($_REQUEST['schema']);

			if ($status != 0) {
				\Kint::dump($status);
				echo $this->lang['strbadschema'];
				exit;
			}
		}

		return $this->data;
	}

	public static function _cmp_desc($a, $b) {
		return strcmp($a['desc'], $b['desc']);
	}
	/**
	 * Checks if dumps are properly set up
	 * @param $all (optional) True to check pg_dumpall, false to just check pg_dump
	 * @return True, dumps are set up, false otherwise
	 */
	function isDumpEnabled($all = false) {
		$info = $this->getServerInfo();
		return !empty($info[$all ? 'pg_dumpall_path' : 'pg_dump_path']);
	}

	function setThemeConf($theme_conf) {
		$this->conf['theme'] = $theme_conf;
		$this->view->offsetSet('theme', $this->conf['theme']);
		return $this;
	}
	/**
	 * Sets the href tracking variable
	 */
	function setHREF() {
		$this->href = $this->getHREF();
		//\PC::debug($this->href, 'Misc::href');
		return $this;

	}

	/**
	 * Get a href query string, excluding objects below the given object type (inclusive)
	 */
	function getHREF($exclude_from = null) {
		$href = [];
		if (isset($_REQUEST['server']) && $exclude_from != 'server') {
			$href[] = 'server=' . urlencode($_REQUEST['server']);
		}
		if (isset($_REQUEST['database']) && $exclude_from != 'database') {
			$href[] = 'database=' . urlencode($_REQUEST['database']);
		}
		if (isset($_REQUEST['schema']) && $exclude_from != 'schema') {
			$href[] = 'schema=' . urlencode($_REQUEST['schema']);
		}

		return htmlentities(implode('&', $href));
	}

	function getSubjectParams($subject) {
		global $plugin_manager;

		$vars = [];

		switch ($subject) {
			case 'root':
				$vars = [
					'params' => [
						'subject' => 'root',
					],
				];
				break;
			case 'server':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'server',
				]];
				break;
			case 'role':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'role',
					'action' => 'properties',
					'rolename' => $_REQUEST['rolename'],
				]];
				break;
			case 'database':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'database',
					'database' => $_REQUEST['database'],
				]];
				break;
			case 'schema':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'schema',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
				]];
				break;
			case 'table':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'table',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
					'table' => $_REQUEST['table'],
				]];
				break;
			case 'selectrows':
				$vars = [
					'url' => 'tables.php',
					'params' => [
						'server' => $_REQUEST['server'],
						'subject' => 'table',
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'table' => $_REQUEST['table'],
						'action' => 'confselectrows',
					]];
				break;
			case 'view':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'view',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
					'view' => $_REQUEST['view'],
				]];
				break;
			case 'fulltext':
			case 'ftscfg':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'fulltext',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
					'action' => 'viewconfig',
					'ftscfg' => $_REQUEST['ftscfg'],
				]];
				break;
			case 'function':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'function',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
					'function' => $_REQUEST['function'],
					'function_oid' => $_REQUEST['function_oid'],
				]];
				break;
			case 'aggregate':
				$vars = ['params' => [
					'server' => $_REQUEST['server'],
					'subject' => 'aggregate',
					'action' => 'properties',
					'database' => $_REQUEST['database'],
					'schema' => $_REQUEST['schema'],
					'aggrname' => $_REQUEST['aggrname'],
					'aggrtype' => $_REQUEST['aggrtype'],
				]];
				break;
			case 'column':
				if (isset($_REQUEST['table'])) {
					$vars = ['params' => [
						'server' => $_REQUEST['server'],
						'subject' => 'column',
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'table' => $_REQUEST['table'],
						'column' => $_REQUEST['column'],
					]];
				} else {
					$vars = ['params' => [
						'server' => $_REQUEST['server'],
						'subject' => 'column',
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'view' => $_REQUEST['view'],
						'column' => $_REQUEST['column'],
					]];
				}

				break;
			case 'plugin':
				$vars = [
					'url' => 'plugin.php',
					'params' => [
						'server' => $_REQUEST['server'],
						'subject' => 'plugin',
						'plugin' => $_REQUEST['plugin'],
					]];

				if (!is_null($plugin_manager->getPlugin($_REQUEST['plugin']))) {
					$vars['params'] = array_merge($vars['params'], $plugin_manager->getPlugin($_REQUEST['plugin'])->get_subject_params());
				}

				break;
			default:
				return false;
		}

		if (!isset($vars['url'])) {
			$vars['url'] = '/redirect';
		}
		//\PC::debug($vars, 'getSubjectParams');
		if ($vars['url'] == '/redirect' && isset($vars['params']['subject'])) {
			$vars['url'] = '/redirect/' . $vars['params']['subject'];
			unset($vars['params']['subject']);
		}

		return $vars;
	}

	function getHREFSubject($subject) {
		$vars = $this->getSubjectParams($subject);
		return "{$vars['url']}?" . http_build_query($vars['params'], '', '&amp;');
	}

	/**
	 * Sets the form tracking variable
	 */
	function setForm() {
		$form = [];
		if (isset($_REQUEST['server'])) {
			$form[] = '<input type="hidden" name="server" value="' . htmlspecialchars($_REQUEST['server']) . '" />';
		}
		if (isset($_REQUEST['database'])) {
			$form[] = '<input type="hidden" name="database" value="' . htmlspecialchars($_REQUEST['database']) . '" />';
		}

		if (isset($_REQUEST['schema'])) {
			$form[] = '<input type="hidden" name="schema" value="' . htmlspecialchars($_REQUEST['schema']) . '" />';
		}
		$this->form = implode("\n", $form);
		return $this->form;

		//\PC::debug($this->form, 'Misc::form');
	}

	/**
	 * Render a value into HTML using formatting rules specified
	 * by a type name and parameters.
	 *
	 * @param $str The string to change
	 *
	 * @param $type Field type (optional), this may be an internal PostgreSQL type, or:
	 *			yesno    - same as bool, but renders as 'Yes' or 'No'.
	 *			pre      - render in a <pre> block.
	 *			nbsp     - replace all spaces with &nbsp;'s
	 *			verbatim - render exactly as supplied, no escaping what-so-ever.
	 *			callback - render using a callback function supplied in the 'function' param.
	 *
	 * @param $params Type parameters (optional), known parameters:
	 *			null     - string to display if $str is null, or set to TRUE to use a default 'NULL' string,
	 *			           otherwise nothing is rendered.
	 *			clip     - if true, clip the value to a fixed length, and append an ellipsis...
	 *			cliplen  - the maximum length when clip is enabled (defaults to $conf['max_chars'])
	 *			ellipsis - the string to append to a clipped value (defaults to $lang['strellipsis'])
	 *			tag      - an HTML element name to surround the value.
	 *			class    - a class attribute to apply to any surrounding HTML element.
	 *			align    - an align attribute ('left','right','center' etc.)
	 *			true     - (type='bool') the representation of true.
	 *			false    - (type='bool') the representation of false.
	 *			function - (type='callback') a function name, accepts args ($str, $params) and returns a rendering.
	 *			lineno   - prefix each line with a line number.
	 *			map      - an associative array.
	 *
	 * @return The HTML rendered value
	 */
	function printVal($str, $type = null, $params = []) {
		$lang = $this->lang;
		$data = $this->data;

		// Shortcircuit for a NULL value
		if (is_null($str)) {
			return isset($params['null'])
			? ($params['null'] === true ? '<i>NULL</i>' : $params['null'])
			: '';
		}

		if (isset($params['map']) && isset($params['map'][$str])) {
			$str = $params['map'][$str];
		}

		// Clip the value if the 'clip' parameter is true.
		if (isset($params['clip']) && $params['clip'] === true) {
			$maxlen   = isset($params['cliplen']) && is_integer($params['cliplen']) ? $params['cliplen'] : $this->conf['max_chars'];
			$ellipsis = isset($params['ellipsis']) ? $params['ellipsis'] : $lang['strellipsis'];
			if (strlen($str) > $maxlen) {
				$str = substr($str, 0, $maxlen - 1) . $ellipsis;
			}
		}

		$out = '';

		switch ($type) {
			case 'int2':
			case 'int4':
			case 'int8':
			case 'float4':
			case 'float8':
			case 'money':
			case 'numeric':
			case 'oid':
			case 'xid':
			case 'cid':
			case 'tid':
				$align = 'right';
				$out   = nl2br(htmlspecialchars($str));
				break;
			case 'yesno':
				if (!isset($params['true'])) {
					$params['true'] = $lang['stryes'];
				}

				if (!isset($params['false'])) {
					$params['false'] = $lang['strno'];
				}

			// No break - fall through to boolean case.
			case 'bool':
			case 'boolean':
				if (is_bool($str)) {
					$str = $str ? 't' : 'f';
				}

				switch ($str) {
					case 't':
						$out   = (isset($params['true']) ? $params['true'] : $lang['strtrue']);
						$align = 'center';
						break;
					case 'f':
						$out   = (isset($params['false']) ? $params['false'] : $lang['strfalse']);
						$align = 'center';
						break;
					default:
						$out = htmlspecialchars($str);
				}
				break;
			case 'bytea':
				$tag   = 'div';
				$class = 'pre';
				$out   = $data->escapeBytea($str);
				break;
			case 'errormsg':
				$tag   = 'pre';
				$class = 'error';
				$out   = htmlspecialchars($str);
				break;
			case 'pre':
				$tag = 'pre';
				$out = htmlspecialchars($str);
				break;
			case 'prenoescape':
				$tag = 'pre';
				$out = $str;
				break;
			case 'nbsp':
				$out = nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($str)));
				break;
			case 'verbatim':
				$out = $str;
				break;
			case 'callback':
				$out = $params['function']($str, $params);
				break;
			case 'prettysize':
				if ($str == -1) {
					$out = $lang['strnoaccess'];
				} else {
					$limit = 10 * 1024;
					$mult  = 1;
					if ($str < $limit * $mult) {
						$out = $str . ' ' . $lang['strbytes'];
					} else {
						$mult *= 1024;
						if ($str < $limit * $mult) {
							$out = floor(($str + $mult / 2) / $mult) . ' ' . $lang['strkb'];
						} else {
							$mult *= 1024;
							if ($str < $limit * $mult) {
								$out = floor(($str + $mult / 2) / $mult) . ' ' . $lang['strmb'];
							} else {
								$mult *= 1024;
								if ($str < $limit * $mult) {
									$out = floor(($str + $mult / 2) / $mult) . ' ' . $lang['strgb'];
								} else {
									$mult *= 1024;
									if ($str < $limit * $mult) {
										$out = floor(($str + $mult / 2) / $mult) . ' ' . $lang['strtb'];
									}

								}
							}
						}
					}
				}
				break;
			default:
				// If the string contains at least one instance of >1 space in a row, a tab
				// character, a space at the start of a line, or a space at the start of
				// the whole string then render within a pre-formatted element (<pre>).
				if (preg_match('/(^ |  |\t|\n )/m', $str)) {
					$tag   = 'pre';
					$class = 'data';
					$out   = htmlspecialchars($str);
				} else {
					$out = nl2br(htmlspecialchars($str));
				}
		}

		if (isset($params['class'])) {
			$class = $params['class'];
		}

		if (isset($params['align'])) {
			$align = $params['align'];
		}

		if (!isset($tag) && (isset($class) || isset($align))) {
			$tag = 'div';
		}

		if (isset($tag)) {
			$alignattr = isset($align) ? " style=\"text-align: {$align}\"" : '';
			$classattr = isset($class) ? " class=\"{$class}\"" : '';
			$out       = "<{$tag}{$alignattr}{$classattr}>{$out}</{$tag}>";
		}

		// Add line numbers if 'lineno' param is true
		if (isset($params['lineno']) && $params['lineno'] === true) {
			$lines = explode("\n", $str);
			$num   = count($lines);
			if ($num > 0) {
				$temp = "<table>\n<tr><td class=\"{$class}\" style=\"vertical-align: top; padding-right: 10px;\"><pre class=\"{$class}\">";
				for ($i = 1; $i <= $num; $i++) {
					$temp .= $i . "\n";
				}
				$temp .= "</pre></td><td class=\"{$class}\" style=\"vertical-align: top;\">{$out}</td></tr></table>\n";
				$out = $temp;
			}
			unset($lines);
		}

		return $out;
	}

	/**
	 * A function to recursively strip slashes.  Used to
	 * enforce magic_quotes_gpc being off.
	 * @param &var The variable to strip
	 */
	function stripVar(&$var) {
		if (is_array($var)) {
			foreach ($var as $k => $v) {
				$this->stripVar($var[$k]);

				/* magic_quotes_gpc escape keys as well ...*/
				if (is_string($k)) {
					$ek = stripslashes($k);
					if ($ek !== $k) {
						$var[$ek] = $var[$k];
						unset($var[$k]);
					}
				}
			}
		} else {
			$var = stripslashes($var);
		}

	}

	/**
	 * Print out the page heading and help link
	 * @param $title Title, already escaped
	 * @param $help (optional) The identifier for the help link
	 */
	function printTitle($title, $help = null, $do_print = true) {
		$data = $this->data;
		$lang = $this->lang;

		$title_html = "<h2>";
		$title_html .= $this->printHelp($title, $help, false);
		$title_html .= "</h2>\n";

		if ($do_print) {
			echo $title_html;
		} else {
			return $title_html;
		}
	}

	/**
	 * Print out a message
	 * @param $msg The message to print
	 */
	function printMsg($msg, $do_print = true) {
		$html = '';
		if ($msg != '') {
			$html .= "<p class=\"message\">{$msg}</p>\n";
		}
		if ($do_print) {
			echo $html;
		} else {
			return $html;
		}

	}

	/**
	 * Prints the page header.  If global variable $_no_output is
	 * set then no header is drawn.
	 * @param $title The title of the page
	 * @param $script script tag
	 * @param $do_print boolean if false, the function will return the header content
	 */
	function printHeader($title = '', $script = null, $do_print = true, $template = 'header.twig') {

		if (function_exists('newrelic_disable_autorum')) {
			newrelic_disable_autorum();
		}
		$appName        = $this->appName;
		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;

		$viewVars        = $this->lang;
		$viewVars['dir'] = (strcasecmp($lang['applangdir'], 'ltr') != 0) ? ' dir="' . htmlspecialchars($lang['applangdir']) . '"' : '';

		$viewVars['appName'] = htmlspecialchars($this->appName) . ($title != '') ? htmlspecialchars(" - {$title}") : '';

		$header_html = $this->view->fetch($template, $viewVars);

		if ($script) {
			$header_html .= "{$script}\n";
		}

		$plugins_head = [];
		$_params      = ['heads' => &$plugins_head];

		$plugin_manager->do_hook('head', $_params);

		foreach ($plugins_head as $tag) {
			$header_html .= $tag;
		}

		$header_html .= "</head>\n";

		if (!$this->_no_output && $do_print) {

			header("Content-Type: text/html; charset=utf-8");
			echo $header_html;

		} else {
			return $header_html;
		}
	}

	/**
	 * Prints the page footer
	 * @param $doBody True to output body tag, false to return the html
	 */
	function printFooter($doBody = true) {
		$lang = $this->lang;

		$footer_html = '';
		//\PC::debug($this->_reload_browser, '$_reload_browser');
		if ($this->_reload_browser) {
			$footer_html .= $this->printReload(false, false);
		} elseif ($this->_reload_drop_database) {
			$footer_html .= $this->printReload(true, false);
		}

		$footer_html .= "<a href=\"#\" class=\"bottom_link\">" . $lang['strgotoppage'] . "</a>";

		$footer_html .= "</body>\n";
		$footer_html .= "</html>\n";

		if ($doBody) {
			echo $footer_html;
		} else {
			return $footer_html;
		}

	}

	/**
	 * Prints the page body.
	 * @param $doBody True to output body tag, false to return
	 * @param $bodyClass - name of body class
	 */
	function printBody($doBody = true, $bodyClass = '') {

		$bodyClass = htmlspecialchars($bodyClass);
		$bodyHtml  = "<body " . ($bodyClass == '' ? '' : " class=\"{$bodyClass}\"") . ">\n";

		if (!$this->_no_output && $doBody) {
			echo $bodyHtml;
		} else {
			return $bodyHtml;
		}
	}

	/**
	 * Outputs JavaScript code that will reload the browser
	 * @param $database True if dropping a database, false otherwise
	 * @param $do_print true to echo, false to return;
	 */
	function printReload($database, $do_print = true) {

		$reload = "<script type=\"text/javascript\">\n";
		//$reload .= " alert('will reload');";
		if ($database) {
			$reload .= "\tparent.frames && parent.frames.browser && parent.frames.browser.location.href=\"/tree/browser\";\n";
		} else {
			$reload .= "\tparent.frames && parent.frames.browser && parent.frames.browser.location.reload();\n";
			//$reload .= "\tparent.frames.detail.location.href=\"/src/views/intro\";\n";
			//$reload .= "\tparent.frames.detail.location.reload();\n";
		}

		$reload .= "</script>\n";
		if ($do_print) {
			echo $reload;
		} else {
			return $reload;
		}
	}

	/**
	 * Display a link
	 * @param $link An associative array of link parameters to print
	 *     link = array(
	 *       'attr' => array( // list of A tag attribute
	 *          'attrname' => attribute value
	 *          ...
	 *       ),
	 *       'content' => The link text
	 *       'fields' => (optionnal) the data from which content and attr's values are obtained
	 *     );
	 *   the special attribute 'href' might be a string or an array. If href is an array it
	 *   will be generated by getActionUrl. See getActionUrl comment for array format.
	 */
	function printLink($link, $do_print = true) {
		\PC::debug($link, 'printLink');

		if (!isset($link['fields'])) {
			$link['fields'] = $_REQUEST;
		}

		$tag = "<a ";
		foreach ($link['attr'] as $attr => $value) {
			if ($attr == 'href' and is_array($value)) {
				$tag .= 'href="' . htmlentities($this->getActionUrl($value, $link['fields'])) . '" ';
			} else {
				$tag .= htmlentities($attr) . '="' . value($value, $link['fields'], 'html') . '" ';
			}
		}
		$tag .= ">" . value($link['content'], $link['fields'], 'html') . "</a>\n";

		if ($do_print) {
			echo $tag;
		} else {
			return $tag;
		}
	}

	/**
	 * Display a list of links
	 * @param $links An associative array of links to print. See printLink function for
	 *               the links array format.
	 * @param $class An optional class or list of classes seprated by a space
	 *   WARNING: This field is NOT escaped! No user should be able to inject something here, use with care.
	 * @param  boolean $do_print true to echo, false to return
	 */
	function printLinksList($links, $class = '', $do_print = true) {
		\PC::debug($links, 'printLinksList');
		$list_html = "<ul class=\"{$class}\">\n";
		foreach ($links as $link) {
			$list_html .= "\t<li>";
			$list_html .= $this->printLink($link, false);
			$list_html .= "</li>\n";
		}
		$list_html .= "</ul>\n";
		if ($do_print) {
			echo $list_html;
		} else {
			return $list_html;
		}
	}

	/**
	 * Display navigation tabs
	 * @param $tabs The name of current section (Ex: intro, server, ...), or an array with tabs (Ex: sqledit.php doFind function)
	 * @param $activetab The name of the tab to be highlighted.
	 * @param  $print if false, return html
	 */
	function printTabs($tabs, $activetab, $do_print = true) {
		global $misc, $data, $lang;

		if (is_string($tabs)) {
			$_SESSION['webdbLastTab'][$tabs] = $activetab;
			$tabs                            = $this->getNavTabs($tabs);
		}
		$tabs_html = '';
		if (count($tabs) > 0) {

			$tabs_html .= "<table class=\"tabs\"><tr>\n";

			# FIXME: don't count hidden tabs
			$width = (int) (100 / count($tabs)) . '%';
			foreach ($tabs as $tab_id => $tab) {

				$tabs[$tab_id]['active'] = $active = ($tab_id == $activetab) ? ' active' : '';

				$tabs[$tab_id]['width'] = $width;

				if (!isset($tab['hide']) || $tab['hide'] !== true) {

					$tabs[$tab_id]['tablink'] = htmlentities($this->getActionUrl($tab, $_REQUEST));

					$tablink = '<a href="' . $tabs[$tab_id]['tablink'] . '">';

					if (isset($tab['icon']) && $icon = $this->icon($tab['icon'])) {
						$tabs[$tab_id]['iconurl'] = $icon;
						$tablink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$tab['title']}\" /></span>";
					}

					$tablink .= "<span class=\"label\">{$tab['title']}</span></a>";

					$tabs_html .= "<td style=\"width: {$width}\" class=\"tab{$active}\">";

					if (isset($tab['help'])) {
						$tabs_html .= $this->printHelp($tablink, $tab['help'], false);
					} else {
						$tabs_html .= $tablink;
					}

					$tabs_html .= "</td>\n";
				}
			}
			$tabs_html .= "</tr></table>\n";
		}

		if ($do_print) {
			echo $tabs_html;
		} else {
			return $tabs_html;
		}

	}

	/**
	 * Retrieve the tab info for a specific tab bar.
	 * @param $section The name of the tab bar.
	 */
	function getNavTabs($section) {

		$data           = $this->data;
		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;

		$hide_advanced = ($this->conf['show_advanced'] === false);
		$tabs          = [];

		switch ($section) {
			case 'root':
				$tabs = [
					'intro' => [
						'title' => $lang['strintroduction'],
						'url' => "intro.php",
						'icon' => 'Introduction',
					],
					'servers' => [
						'title' => $lang['strservers'],
						'url' => "servers.php",
						'icon' => 'Servers',
					],
				];
				break;

			case 'server':
				$hide_users = !$data->isSuperUser();
				$tabs       = [
					'databases' => [
						'title' => $lang['strdatabases'],
						'url' => 'all_db.php',
						'urlvars' => ['subject' => 'server'],
						'help' => 'pg.database',
						'icon' => 'Databases',
					],
				];
				if ($data->hasRoles()) {
					$tabs = array_merge($tabs, [
						'roles' => [
							'title' => $lang['strroles'],
							'url' => 'roles.php',
							'urlvars' => ['subject' => 'server'],
							'hide' => $hide_users,
							'help' => 'pg.role',
							'icon' => 'Roles',
						],
					]);
				} else {
					$tabs = array_merge($tabs, [
						'users' => [
							'title' => $lang['strusers'],
							'url' => 'users.php',
							'urlvars' => ['subject' => 'server'],
							'hide' => $hide_users,
							'help' => 'pg.user',
							'icon' => 'Users',
						],
						'groups' => [
							'title' => $lang['strgroups'],
							'url' => 'groups.php',
							'urlvars' => ['subject' => 'server'],
							'hide' => $hide_users,
							'help' => 'pg.group',
							'icon' => 'UserGroups',
						],
					]);
				}

				$tabs = array_merge($tabs, [
					'account' => [
						'title' => $lang['straccount'],
						'url' => $data->hasRoles() ? 'roles.php' : 'users.php',
						'urlvars' => ['subject' => 'server', 'action' => 'account'],
						'hide' => !$hide_users,
						'help' => 'pg.role',
						'icon' => 'User',
					],
					'tablespaces' => [
						'title' => $lang['strtablespaces'],
						'url' => 'tablespaces.php',
						'urlvars' => ['subject' => 'server'],
						'hide' => (!$data->hasTablespaces()),
						'help' => 'pg.tablespace',
						'icon' => 'Tablespaces',
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'all_db.php',
						'urlvars' => ['subject' => 'server', 'action' => 'export'],
						'hide' => (!$this->isDumpEnabled()),
						'icon' => 'Export',
					],
				]);
				break;
			case 'database':
				$tabs = [
					'schemas' => [
						'title' => $lang['strschemas'],
						'url' => 'schemas.php',
						'urlvars' => ['subject' => 'database'],
						'help' => 'pg.schema',
						'icon' => 'Schemas',
					],
					'sql' => [
						'title' => $lang['strsql'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'sql', 'new' => 1],
						'help' => 'pg.sql',
						'tree' => false,
						'icon' => 'SqlEditor',
					],
					'find' => [
						'title' => $lang['strfind'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'find'],
						'tree' => false,
						'icon' => 'Search',
					],
					'variables' => [
						'title' => $lang['strvariables'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'variables'],
						'help' => 'pg.variable',
						'tree' => false,
						'icon' => 'Variables',
					],
					'processes' => [
						'title' => $lang['strprocesses'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'processes'],
						'help' => 'pg.process',
						'tree' => false,
						'icon' => 'Processes',
					],
					'locks' => [
						'title' => $lang['strlocks'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'locks'],
						'help' => 'pg.locks',
						'tree' => false,
						'icon' => 'Key',
					],
					'admin' => [
						'title' => $lang['stradmin'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'admin'],
						'tree' => false,
						'icon' => 'Admin',
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => ['subject' => 'database'],
						'hide' => (!isset($data->privlist['database'])),
						'help' => 'pg.privilege',
						'tree' => false,
						'icon' => 'Privileges',
					],
					'languages' => [
						'title' => $lang['strlanguages'],
						'url' => 'languages.php',
						'urlvars' => ['subject' => 'database'],
						'hide' => $hide_advanced,
						'help' => 'pg.language',
						'icon' => 'Languages',
					],
					'casts' => [
						'title' => $lang['strcasts'],
						'url' => 'casts.php',
						'urlvars' => ['subject' => 'database'],
						'hide' => ($hide_advanced),
						'help' => 'pg.cast',
						'icon' => 'Casts',
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'database.php',
						'urlvars' => ['subject' => 'database', 'action' => 'export'],
						'hide' => (!$this->isDumpEnabled()),
						'tree' => false,
						'icon' => 'Export',
					],
				];
				break;

			case 'schema':
				$tabs = [
					'tables' => [
						'title' => $lang['strtables'],
						'url' => 'tables.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.table',
						'icon' => 'Tables',
					],
					'views' => [
						'title' => $lang['strviews'],
						'url' => 'views.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.view',
						'icon' => 'Views',
					],
					'matviews' => [
						'title' => 'M ' . $lang['strviews'],
						'url' => 'materialized_views.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.matview',
						'icon' => 'MViews',
					],
					'sequences' => [
						'title' => $lang['strsequences'],
						'url' => 'sequences.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.sequence',
						'icon' => 'Sequences',
					],
					'functions' => [
						'title' => $lang['strfunctions'],
						'url' => 'functions.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.function',
						'icon' => 'Functions',
					],
					'fulltext' => [
						'title' => $lang['strfulltext'],
						'url' => 'fulltext.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.fts',
						'tree' => true,
						'icon' => 'Fts',
					],
					'domains' => [
						'title' => $lang['strdomains'],
						'url' => 'domains.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.domain',
						'icon' => 'Domains',
					],
					'aggregates' => [
						'title' => $lang['straggregates'],
						'url' => 'aggregates.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => $hide_advanced,
						'help' => 'pg.aggregate',
						'icon' => 'Aggregates',
					],
					'types' => [
						'title' => $lang['strtypes'],
						'url' => 'types.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => $hide_advanced,
						'help' => 'pg.type',
						'icon' => 'Types',
					],
					'operators' => [
						'title' => $lang['stroperators'],
						'url' => 'operators.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => $hide_advanced,
						'help' => 'pg.operator',
						'icon' => 'Operators',
					],
					'opclasses' => [
						'title' => $lang['stropclasses'],
						'url' => 'opclasses.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => $hide_advanced,
						'help' => 'pg.opclass',
						'icon' => 'OperatorClasses',
					],
					'conversions' => [
						'title' => $lang['strconversions'],
						'url' => 'conversions.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => $hide_advanced,
						'help' => 'pg.conversion',
						'icon' => 'Conversions',
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => ['subject' => 'schema'],
						'help' => 'pg.privilege',
						'tree' => false,
						'icon' => 'Privileges',
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'schemas.php',
						'urlvars' => ['subject' => 'schema', 'action' => 'export'],
						'hide' => (!$this->isDumpEnabled()),
						'tree' => false,
						'icon' => 'Export',
					],
				];
				if (!$data->hasFTS()) {
					unset($tabs['fulltext']);
				}

				break;

			case 'table':
				$tabs = [
					'columns' => [
						'title' => $lang['strcolumns'],
						'url' => 'tblproperties.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'icon' => 'Columns',
						'branch' => true,
					],
					'browse' => [
						'title' => $lang['strbrowse'],
						'icon' => 'Columns',
						'url' => 'display.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'return' => 'table',
						'branch' => true,
					],
					'select' => [
						'title' => $lang['strselect'],
						'icon' => 'Search',
						'url' => 'tables.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table'), 'action' => 'confselectrows'],
						'help' => 'pg.sql.select',
					],
					'insert' => [
						'title' => $lang['strinsert'],
						'url' => 'tables.php',
						'urlvars' => [
							'action' => 'confinsertrow',
							'table' => Decorator::field('table'),
						],
						'help' => 'pg.sql.insert',
						'icon' => 'Operator',
					],
					'indexes' => [
						'title' => $lang['strindexes'],
						'url' => 'indexes.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'help' => 'pg.index',
						'icon' => 'Indexes',
						'branch' => true,
					],
					'constraints' => [
						'title' => $lang['strconstraints'],
						'url' => 'constraints.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'help' => 'pg.constraint',
						'icon' => 'Constraints',
						'branch' => true,
					],
					'triggers' => [
						'title' => $lang['strtriggers'],
						'url' => 'triggers.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'help' => 'pg.trigger',
						'icon' => 'Triggers',
						'branch' => true,
					],
					'rules' => [
						'title' => $lang['strrules'],
						'url' => 'rules.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'help' => 'pg.rule',
						'icon' => 'Rules',
						'branch' => true,
					],
					'admin' => [
						'title' => $lang['stradmin'],
						'url' => 'tables.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table'), 'action' => 'admin'],
						'icon' => 'Admin',
					],
					'info' => [
						'title' => $lang['strinfo'],
						'url' => 'info.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'icon' => 'Statistics',
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table')],
						'help' => 'pg.privilege',
						'icon' => 'Privileges',
					],
					'import' => [
						'title' => $lang['strimport'],
						'url' => 'tblproperties.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table'), 'action' => 'import'],
						'icon' => 'Import',
						'hide' => false,
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'tblproperties.php',
						'urlvars' => ['subject' => 'table', 'table' => Decorator::field('table'), 'action' => 'export'],
						'icon' => 'Export',
						'hide' => false,
					],
				];
				break;

			case 'view':
				$tabs = [
					'columns' => [
						'title' => $lang['strcolumns'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'view', 'view' => Decorator::field('view')],
						'icon' => 'Columns',
						'branch' => true,
					],
					'browse' => [
						'title' => $lang['strbrowse'],
						'icon' => 'Columns',
						'url' => 'display.php',
						'urlvars' => [
							'action' => 'confselectrows',
							'return' => 'schema',
							'subject' => 'view',
							'view' => Decorator::field('view'),
						],
						'branch' => true,
					],
					'select' => [
						'title' => $lang['strselect'],
						'icon' => 'Search',
						'url' => 'views.php',
						'urlvars' => ['action' => 'confselectrows', 'view' => Decorator::field('view')],
						'help' => 'pg.sql.select',
					],
					'definition' => [
						'title' => $lang['strdefinition'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'view', 'view' => Decorator::field('view'), 'action' => 'definition'],
						'icon' => 'Definition',
					],
					'rules' => [
						'title' => $lang['strrules'],
						'url' => 'rules.php',
						'urlvars' => ['subject' => 'view', 'view' => Decorator::field('view')],
						'help' => 'pg.rule',
						'icon' => 'Rules',
						'branch' => true,
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => ['subject' => 'view', 'view' => Decorator::field('view')],
						'help' => 'pg.privilege',
						'icon' => 'Privileges',
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'view', 'view' => Decorator::field('view'), 'action' => 'export'],
						'icon' => 'Export',
						'hide' => false,
					],
				];
				break;

			case 'matview':
				$tabs = [
					'columns' => [
						'title' => $lang['strcolumns'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'matview', 'matview' => Decorator::field('matview')],
						'icon' => 'Columns',
						'branch' => true,
					],
					'browse' => [
						'title' => $lang['strbrowse'],
						'icon' => 'Columns',
						'url' => 'display.php',
						'urlvars' => [
							'action' => 'confselectrows',
							'return' => 'schema',
							'subject' => 'matview',
							'matview' => Decorator::field('matview'),
						],
						'branch' => true,
					],
					'select' => [
						'title' => $lang['strselect'],
						'icon' => 'Search',
						'url' => 'views.php',
						'urlvars' => ['action' => 'confselectrows', 'matview' => Decorator::field('matview')],
						'help' => 'pg.sql.select',
					],
					'definition' => [
						'title' => $lang['strdefinition'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'matview', 'matview' => Decorator::field('matview'), 'action' => 'definition'],
						'icon' => 'Definition',
					],
					'rules' => [
						'title' => $lang['strrules'],
						'url' => 'rules.php',
						'urlvars' => ['subject' => 'matview', 'matview' => Decorator::field('matview')],
						'help' => 'pg.rule',
						'icon' => 'Rules',
						'branch' => true,
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => ['subject' => 'matview', 'matview' => Decorator::field('matview')],
						'help' => 'pg.privilege',
						'icon' => 'Privileges',
					],
					'export' => [
						'title' => $lang['strexport'],
						'url' => 'viewproperties.php',
						'urlvars' => ['subject' => 'matview', 'matview' => Decorator::field('matview'), 'action' => 'export'],
						'icon' => 'Export',
						'hide' => false,
					],
				];
				break;

			case 'function':
				$tabs = [
					'definition' => [
						'title' => $lang['strdefinition'],
						'url' => 'functions.php',
						'urlvars' => [
							'subject' => 'function',
							'function' => Decorator::field('function'),
							'function_oid' => Decorator::field('function_oid'),
							'action' => 'properties',
						],
						'icon' => 'Definition',
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => [
							'subject' => 'function',
							'function' => Decorator::field('function'),
							'function_oid' => Decorator::field('function_oid'),
						],
						'icon' => 'Privileges',
					],
				];
				break;

			case 'aggregate':
				$tabs = [
					'definition' => [
						'title' => $lang['strdefinition'],
						'url' => 'aggregates.php',
						'urlvars' => [
							'subject' => 'aggregate',
							'aggrname' => Decorator::field('aggrname'),
							'aggrtype' => Decorator::field('aggrtype'),
							'action' => 'properties',
						],
						'icon' => 'Definition',
					],
				];
				break;

			case 'role':
				$tabs = [
					'definition' => [
						'title' => $lang['strdefinition'],
						'url' => 'roles.php',
						'urlvars' => [
							'subject' => 'role',
							'rolename' => Decorator::field('rolename'),
							'action' => 'properties',
						],
						'icon' => 'Definition',
					],
				];
				break;

			case 'popup':
				$tabs = [
					'sql' => [
						'title' => $lang['strsql'],
						'url' => '/src/views/sqledit.php',
						'urlvars' => ['action' => 'sql', 'subject' => 'schema'],
						'help' => 'pg.sql',
						'icon' => 'SqlEditor',
					],
					'find' => [
						'title' => $lang['strfind'],
						'url' => '/src/views/sqledit.php',
						'urlvars' => ['action' => 'find', 'subject' => 'schema'],
						'icon' => 'Search',
					],
				];
				break;

			case 'column':
				$tabs = [
					'properties' => [
						'title' => $lang['strcolprop'],
						'url' => 'colproperties.php',
						'urlvars' => [
							'subject' => 'column',
							'table' => Decorator::field('table'),
							'column' => Decorator::field('column'),
						],
						'icon' => 'Column',
					],
					'privileges' => [
						'title' => $lang['strprivileges'],
						'url' => 'privileges.php',
						'urlvars' => [
							'subject' => 'column',
							'table' => Decorator::field('table'),
							'column' => Decorator::field('column'),
						],
						'help' => 'pg.privilege',
						'icon' => 'Privileges',
					],
				];
				break;

			case 'fulltext':
				$tabs = [
					'ftsconfigs' => [
						'title' => $lang['strftstabconfigs'],
						'url' => 'fulltext.php',
						'urlvars' => ['subject' => 'schema'],
						'hide' => !$data->hasFTS(),
						'help' => 'pg.ftscfg',
						'tree' => true,
						'icon' => 'FtsCfg',
					],
					'ftsdicts' => [
						'title' => $lang['strftstabdicts'],
						'url' => 'fulltext.php',
						'urlvars' => ['subject' => 'schema', 'action' => 'viewdicts'],
						'hide' => !$data->hasFTS(),
						'help' => 'pg.ftsdict',
						'tree' => true,
						'icon' => 'FtsDict',
					],
					'ftsparsers' => [
						'title' => $lang['strftstabparsers'],
						'url' => 'fulltext.php',
						'urlvars' => ['subject' => 'schema', 'action' => 'viewparsers'],
						'hide' => !$data->hasFTS(),
						'help' => 'pg.ftsparser',
						'tree' => true,
						'icon' => 'FtsParser',
					],
				];
				break;
		}

		// Tabs hook's place
		$plugin_functions_parameters = [
			'tabs' => &$tabs,
			'section' => $section,
		];
		$plugin_manager->do_hook('tabs', $plugin_functions_parameters);

		return $tabs;
	}

	/**
	 * Get the URL for the last active tab of a particular tab bar.
	 */
	function getLastTabURL($section) {
		$data = $this->getDatabaseAccessor();

		$tabs = $this->getNavTabs($section);

		if (isset($_SESSION['webdbLastTab'][$section]) && isset($tabs[$_SESSION['webdbLastTab'][$section]])) {
			$tab = $tabs[$_SESSION['webdbLastTab'][$section]];
		} else {
			$tab = reset($tabs);
		}
		\PC::debug(['section' => $section, 'tabs' => $tabs, 'tab' => $tab], 'getLastTabURL');
		return isset($tab['url']) ? $tab : null;
	}

	/**
	 * [printTopbar description]
	 * @param  bool $do_print true to print, false to return html
	 * @return string
	 */
	function printTopbar($do_print = true) {

		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;
		$appName        = $this->appName;
		$appVersion     = $this->appVersion;
		$appLangFiles   = $this->appLangFiles;

		$server_info = $this->getServerInfo();
		$reqvars     = $this->getRequestVars('table');

		$topbar_html = "<div class=\"topbar\"><table style=\"width: 100%\"><tr><td>";

		if ($server_info && isset($server_info['platform']) && isset($server_info['username'])) {
			/* top left informations when connected */
			$topbar_html .= sprintf($lang['strtopbar'],
				'<span class="platform">' . htmlspecialchars($server_info['platform']) . '</span>',
				'<span class="host">' . htmlspecialchars((empty($server_info['host'])) ? 'localhost' : $server_info['host']) . '</span>',
				'<span class="port">' . htmlspecialchars($server_info['port']) . '</span>',
				'<span class="username">' . htmlspecialchars($server_info['username']) . '</span>');

			$topbar_html .= "</td>";

			/* top right informations when connected */

			$toplinks = [
				'sql' => [
					'attr' => [
						'href' => [
							'url' => 'sqledit.php',
							'urlvars' => array_merge($reqvars, [
								'action' => 'sql',
							]),
						],
						'target' => "sqledit",
						'id' => 'toplink_sql',
					],
					'content' => $lang['strsql'],
				],
				'history' => [
					'attr' => [
						'href' => [
							'url' => 'history.php',
							'urlvars' => array_merge($reqvars, [
								'action' => 'pophistory',
							]),
						],
						'id' => 'toplink_history',
					],
					'content' => $lang['strhistory'],
				],
				'find' => [
					'attr' => [
						'href' => [
							'url' => 'sqledit.php',
							'urlvars' => array_merge($reqvars, [
								'action' => 'find',
							]),
						],
						'target' => "sqledit",
						'id' => 'toplink_find',
					],
					'content' => $lang['strfind'],
				],
				'logout' => [
					'attr' => [
						'href' => [
							'url' => 'servers.php',
							'urlvars' => [
								'action' => 'logout',
								'logoutServer' => "{$server_info['host']}:{$server_info['port']}:{$server_info['sslmode']}",
							],
						],
						'id' => 'toplink_logout',
					],
					'content' => $lang['strlogout'],
				],
			];

			// Toplink hook's place
			$plugin_functions_parameters = [
				'toplinks' => &$toplinks,
			];

			$plugin_manager->do_hook('toplinks', $plugin_functions_parameters);

			$topbar_html .= "<td style=\"text-align: right\">";

			$topbar_html .= $this->printLinksList($toplinks, 'toplink', [], false);

			$topbar_html .= "</td>";

			$sql_window_id     = htmlentities('sqledit:' . $this->server_id);
			$history_window_id = htmlentities('history:' . $this->server_id);

			$topbar_html .= "<script type=\"text/javascript\">
						$('#toplink_sql').click(function() {
							window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
							return false;
						});

						$('#toplink_history').click(function() {
							window.open($(this).attr('href'),'{$history_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
							return false;
						});

						$('#toplink_find').click(function() {
							window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=500,resizable=yes,scrollbars=yes').focus();
							return false;
						});
						";

			if (isset($_SESSION['sharedUsername'])) {
				$topbar_html .= sprintf("
						$('#toplink_logout').click(function() {
							return confirm('%s');
						});", str_replace("'", "\'", $lang['strconfdropcred']));
			}

			$topbar_html .= "
				</script>";
		} else {
			$topbar_html .= "<span class=\"appname\">{$appName}</span> <span class=\"version\">{$appVersion}</span>";
		}
		/*
			echo "<td style=\"text-align: right; width: 1%\">";

			echo "<form method=\"get\"><select name=\"language\" onchange=\"this.form.submit()\">\n";
			$language = isset($_SESSION['webdbLanguage']) ? $_SESSION['webdbLanguage'] : 'english';
			foreach ($appLangFiles as $k => $v) {
			echo "<option value=\"{$k}\"",
			($k == $language) ? ' selected="selected"' : '',
			">{$v}</option>\n";
			}
			echo "</select>\n";
			echo "<noscript><input type=\"submit\" value=\"Set Language\"></noscript>\n";
			foreach ($_GET as $key => $val) {
			if ($key == 'language') continue;
			echo "<input type=\"hidden\" name=\"$key\" value=\"", htmlspecialchars($val), "\" />\n";
			}
			echo "</form>\n";

			echo "</td>";
		*/
		$topbar_html .= "</tr></table></div>\n";

		if ($do_print) {
			echo $topbar_html;
		} else {
			return $topbar_html;
		}
	}

	/**
	 * Display a bread crumb trail.
	 * @param  $do_print true to echo, false to return html
	 */
	function printTrail($trail = [], $do_print = true) {
		$lang = $this->lang;

		$trail_html = $this->printTopbar(false);

		if (is_string($trail)) {
			$trail = $this->getTrail($trail);
		}

		$trail_html .= "<div class=\"trail\"><table><tr>";

		foreach ($trail as $crumb) {
			$trail_html .= "<td class=\"crumb\">";
			$crumblink = "<a";

			if (isset($crumb['url'])) {
				$crumblink .= " href=\"{$crumb['url']}\"";
			}

			if (isset($crumb['title'])) {
				$crumblink .= " title=\"{$crumb['title']}\"";
			}

			$crumblink .= ">";

			if (isset($crumb['title'])) {
				$iconalt = $crumb['title'];
			} else {
				$iconalt = 'Database Root';
			}

			if (isset($crumb['icon']) && $icon = $this->icon($crumb['icon'])) {
				$crumblink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$iconalt}\" /></span>";
			}

			$crumblink .= "<span class=\"label\">" . htmlspecialchars($crumb['text']) . "</span></a>";

			if (isset($crumb['help'])) {
				$trail_html .= $this->printHelp($crumblink, $crumb['help'], false);
			} else {
				$trail_html .= $crumblink;
			}

			$trail_html .= "{$lang['strseparator']}";
			$trail_html .= "</td>";
		}

		$trail_html .= "</tr></table></div>\n";
		if ($do_print) {
			echo $trail_html;
		} else {
			return $trail_html;
		}
	}

	/**
	 * Create a bread crumb trail of the object hierarchy.
	 * @param $object The type of object at the end of the trail.
	 */
	function getTrail($subject = null) {
		global $lang, $data, $appName, $plugin_manager;

		$trail = [];
		$vars  = '';
		$done  = false;

		$trail['root'] = [
			'text' => $appName,
			'url' => '/redirect/root',
			'icon' => 'Introduction',
		];

		if ($subject == 'root') {
			$done = true;
		}

		if (!$done) {
			$server_info     = $this->getServerInfo();
			$trail['server'] = [
				'title' => $lang['strserver'],
				'text' => $server_info['desc'],
				'url' => $this->getHREFSubject('server'),
				'help' => 'pg.server',
				'icon' => 'Server',
			];
		}
		if ($subject == 'server') {
			$done = true;
		}

		if (isset($_REQUEST['database']) && !$done) {
			$trail['database'] = [
				'title' => $lang['strdatabase'],
				'text' => $_REQUEST['database'],
				'url' => $this->getHREFSubject('database'),
				'help' => 'pg.database',
				'icon' => 'Database',
			];
		} elseif (isset($_REQUEST['rolename']) && !$done) {
			$trail['role'] = [
				'title' => $lang['strrole'],
				'text' => $_REQUEST['rolename'],
				'url' => $this->getHREFSubject('role'),
				'help' => 'pg.role',
				'icon' => 'Roles',
			];
		}
		if ($subject == 'database' || $subject == 'role') {
			$done = true;
		}

		if (isset($_REQUEST['schema']) && !$done) {
			$trail['schema'] = [
				'title' => $lang['strschema'],
				'text' => $_REQUEST['schema'],
				'url' => $this->getHREFSubject('schema'),
				'help' => 'pg.schema',
				'icon' => 'Schema',
			];
		}
		if ($subject == 'schema') {
			$done = true;
		}

		if (isset($_REQUEST['table']) && !$done) {
			$trail['table'] = [
				'title' => $lang['strtable'],
				'text' => $_REQUEST['table'],
				'url' => $this->getHREFSubject('table'),
				'help' => 'pg.table',
				'icon' => 'Table',
			];
		} elseif (isset($_REQUEST['view']) && !$done) {
			$trail['view'] = [
				'title' => $lang['strview'],
				'text' => $_REQUEST['view'],
				'url' => $this->getHREFSubject('view'),
				'help' => 'pg.view',
				'icon' => 'View',
			];
		} elseif (isset($_REQUEST['ftscfg']) && !$done) {
			$trail['ftscfg'] = [
				'title' => $lang['strftsconfig'],
				'text' => $_REQUEST['ftscfg'],
				'url' => $this->getHREFSubject('ftscfg'),
				'help' => 'pg.ftscfg.example',
				'icon' => 'Fts',
			];
		}
		if ($subject == 'table' || $subject == 'view' || $subject == 'ftscfg') {
			$done = true;
		}

		if (!$done && !is_null($subject)) {
			switch ($subject) {
				case 'function':
					$trail[$subject] = [
						'title' => $lang['str' . $subject],
						'text' => $_REQUEST[$subject],
						'url' => $this->getHREFSubject('function'),
						'help' => 'pg.function',
						'icon' => 'Function',
					];
					break;
				case 'aggregate':
					$trail[$subject] = [
						'title' => $lang['straggregate'],
						'text' => $_REQUEST['aggrname'],
						'url' => $this->getHREFSubject('aggregate'),
						'help' => 'pg.aggregate',
						'icon' => 'Aggregate',
					];
					break;
				case 'column':
					$trail['column'] = [
						'title' => $lang['strcolumn'],
						'text' => $_REQUEST['column'],
						'icon' => 'Column',
						'url' => $this->getHREFSubject('column'),
					];
					break;
				default:
					if (isset($_REQUEST[$subject])) {
						switch ($subject) {
							case 'domain':$icon = 'Domain';
								break;
							case 'sequence':$icon = 'Sequence';
								break;
							case 'type':$icon = 'Type';
								break;
							case 'operator':$icon = 'Operator';
								break;
							default:$icon = null;
								break;
						}
						$trail[$subject] = [
							'title' => $lang['str' . $subject],
							'text' => $_REQUEST[$subject],
							'help' => 'pg.' . $subject,
							'icon' => $icon,
						];
					}
			}
		}

		// Trail hook's place
		$plugin_functions_parameters = [
			'trail' => &$trail,
			'section' => $subject,
		];

		$plugin_manager->do_hook('trail', $plugin_functions_parameters);

		return $trail;
	}

	/**
	 * Display the navlinks
	 *
	 * @param $navlinks - An array with the the attributes and values that will be shown. See printLinksList for array format.
	 * @param $place - Place where the $navlinks are displayed. Like 'display-browse', where 'display' is the file (display.php)
	 * @param $env - Associative array of defined variables in the scope of the caller.
	 *               Allows to give some environnement details to plugins.
	 * and 'browse' is the place inside that code (doBrowse).
	 * @param bool $do_print if true, print html, if false, return html
	 */
	function printNavLinks($navlinks, $place, $env = [], $do_print = true) {
		$plugin_manager = $this->plugin_manager;

		// Navlinks hook's place
		$plugin_functions_parameters = [
			'navlinks' => &$navlinks,
			'place' => $place,
			'env' => $env,
		];
		$plugin_manager->do_hook('navlinks', $plugin_functions_parameters);

		if (count($navlinks) > 0) {
			if ($do_print) {
				$this->printLinksList($navlinks, 'navlink');
			} else {
				return $this->printLinksList($navlinks, 'navlink', false);
			}

		}
	}

	/**
	 * Do multi-page navigation.  Displays the prev, next and page options.
	 * @param $page - the page currently viewed
	 * @param $pages - the maximum number of pages
	 * @param $gets -  the parameters to include in the link to the wanted page
	 * @param $max_width - the number of pages to make available at any one time (default = 20)
	 */
	function printPages($page, $pages, $gets, $max_width = 20) {
		global $lang;

		$window = 10;

		if ($page < 0 || $page > $pages) {
			return;
		}

		if ($pages < 0) {
			return;
		}

		if ($max_width <= 0) {
			return;
		}

		unset($gets['page']);
		$url = http_build_query($gets);

		if ($pages > 1) {
			echo "<p style=\"text-align: center\">\n";
			if ($page != 1) {
				echo "<a class=\"pagenav\" href=\"?{$url}&amp;page=1\">{$lang['strfirst']}</a>\n";
				$temp = $page - 1;
				echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$temp}\">{$lang['strprev']}</a>\n";
			}

			if ($page <= $window) {
				$min_page = 1;
				$max_page = min(2 * $window, $pages);
			} elseif ($page > $window && $pages >= $page + $window) {
				$min_page = ($page - $window) + 1;
				$max_page = $page + $window;
			} else {
				$min_page = ($page - (2 * $window - ($pages - $page))) + 1;
				$max_page = $pages;
			}

			// Make sure min_page is always at least 1
			// and max_page is never greater than $pages
			$min_page = max($min_page, 1);
			$max_page = min($max_page, $pages);

			for ($i = $min_page; $i <= $max_page; $i++) {
				#if ($i != $page) echo "<a class=\"pagenav\" href=\"?{$url}&amp;page={$i}\">$i</a>\n";
				if ($i != $page) {
					echo "<a class=\"pagenav\" href=\"display.php?{$url}&amp;page={$i}\">$i</a>\n";
				} else {
					echo "$i\n";
				}

			}
			if ($page != $pages) {
				$temp = $page + 1;
				echo "<a class=\"pagenav\" href=\"display.php?{$url}&amp;page={$temp}\">{$lang['strnext']}</a>\n";
				echo "<a class=\"pagenav\" href=\"display.php?{$url}&amp;page={$pages}\">{$lang['strlast']}</a>\n";
			}
			echo "</p>\n";
		}
	}

	/**
	 * Displays link to the context help.
	 * @param $str   - the string that the context help is related to (already escaped)
	 * @param $help  - help section identifier
	 * @param $do_print true to echo, false to return
	 */
	function printHelp($str, $help = null, $do_print = true) {
		//\PC::debug(['str' => $str, 'help' => $help], 'printHelp');
		if ($help !== null) {
			$helplink = $this->getHelpLink($help);
			$str .= '<a class="help" href="' . $helplink . '" title="' . $this->lang['strhelp'] . '" target="phppgadminhelp">' . $this->lang['strhelpicon'] . '</a>';

		}
		if ($do_print) {
			echo $str;
		} else {
			return $str;
		}
	}

	function getHelpLink($help) {
		return htmlspecialchars("help.php?help=" . urlencode($help) . "&server=" . urlencode($this->server_id));

	}

	/**
	 * Outputs JavaScript to set default focus
	 * @param $object eg. forms[0].username
	 */
	function setFocus($object) {
		echo "<script type=\"text/javascript\">\n";
		echo "   document.{$object}.focus();\n";
		echo "</script>\n";
	}

	/**
	 * Outputs JavaScript to set the name of the browser window.
	 * @param $name the window name
	 * @param $addServer if true (default) then the server id is
	 *        attached to the name.
	 */
	function setWindowName($name, $addServer = true) {
		echo "<script type=\"text/javascript\">\n";
		echo "//<![CDATA[\n";
		echo "   window.name = '{$name}", ($addServer ? ':' . htmlspecialchars($this->server_id) : ''), "';\n";
		echo "//]]>\n";
		echo "</script>\n";
	}

	/**
	 * Converts a PHP.INI size variable to bytes.  Taken from publically available
	 * function by Chris DeRose, here: http://www.php.net/manual/en/configuration.directives.php#ini.file-uploads
	 * @param $strIniSize The PHP.INI variable
	 * @return size in bytes, false on failure
	 */
	function inisizeToBytes($strIniSize) {
		// This function will take the string value of an ini 'size' parameter,
		// and return a double (64-bit float) representing the number of bytes
		// that the parameter represents. Or false if $strIniSize is unparseable.
		$a_IniParts = [];

		if (!is_string($strIniSize)) {
			return false;
		}

		if (!preg_match('/^(\d+)([bkm]*)$/i', $strIniSize, $a_IniParts)) {
			return false;
		}

		$nSize   = (double) $a_IniParts[1];
		$strUnit = strtolower($a_IniParts[2]);

		switch ($strUnit) {
			case 'm':
				return ($nSize * (double) 1048576);
			case 'k':
				return ($nSize * (double) 1024);
			case 'b':
			default:
				return $nSize;
		}
	}

	/**
	 * Returns URL given an action associative array.
	 * NOTE: this function does not html-escape, only url-escape
	 * @param $action An associative array of the follow properties:
	 *			'url'  => The first part of the URL (before the ?)
	 *			'urlvars' => Associative array of (URL variable => field name)
	 *						these are appended to the URL
	 * @param $fields Field data from which 'urlfield' and 'vars' are obtained.
	 */
	function getActionUrl(&$action, &$fields) {
		$url = value($action['url'], $fields);

		if ($url === false) {
			return '';
		}

		if (!empty($action['urlvars'])) {
			$urlvars = value($action['urlvars'], $fields);
		} else {
			$urlvars = [];
		}

		/* set server, database and schema parameter if not presents */
		if (isset($urlvars['subject'])) {
			$subject = value($urlvars['subject'], $fields);
		} else {
			$subject = '';
		}

		if (isset($_REQUEST['server']) and !isset($urlvars['server']) and $subject != 'root') {
			$urlvars['server'] = $_REQUEST['server'];
			if (isset($_REQUEST['database']) and !isset($urlvars['database']) and $subject != 'server') {
				$urlvars['database'] = $_REQUEST['database'];
				if (isset($_REQUEST['schema']) and !isset($urlvars['schema']) and $subject != 'database') {
					$urlvars['schema'] = $_REQUEST['schema'];
				}
			}
		}

		$sep = '?';
		foreach ($urlvars as $var => $varfield) {
			$url .= $sep . value_url($var, $fields) . '=' . value_url($varfield, $fields);
			$sep = '&';
		}
		//return '/src/views/' . $url;
		return $url;
	}

	function getRequestVars($subject = '') {
		$v = [];
		if (!empty($subject)) {
			$v['subject'] = $subject;
		}

		if ($this->server_id !== null && $subject != 'root') {
			$v['server'] = $this->server_id;
			if ($this->database !== null && $subject != 'server') {
				$v['database'] = $this->database;
				if (isset($_REQUEST['schema']) && $subject != 'database') {
					$v['schema'] = $_REQUEST['schema'];
				}
			}
		}
		return $v;
	}

	/**
	 * Instances an HTMLTable and returns its html content
	 * @param  [type] &$tabledata [description]
	 * @param  [type] &$columns   [description]
	 * @param  [type] &$actions   [description]
	 * @param  [type] $place      [description]
	 * @param  [type] $nodata     [description]
	 * @param  [type] $pre_fn     [description]
	 * @return [type]             [description]
	 */
	function printTable(&$tabledata, &$columns, &$actions, $place, $nodata = null, $pre_fn = null) {

		$html_table = new HTMLTableController($this->app->getContainer());

		return $html_table->printTable($tabledata, $columns, $actions, $place, $nodata, $pre_fn);
	}

	/** Produce XML data for the browser tree
	 * @param $treedata A set of records to populate the tree.
	 * @param $attrs Attributes for tree items
	 *        'text' - the text for the tree node
	 *        'icon' - an icon for node
	 *        'openIcon' - an alternative icon when the node is expanded
	 *        'toolTip' - tool tip text for the node
	 *        'action' - URL to visit when single clicking the node
	 *        'iconAction' - URL to visit when single clicking the icon node
	 *        'branch' - URL for child nodes (tree XML)
	 *        'expand' - the action to return XML for the subtree
	 *        'nodata' - message to display when node has no children
	 * @param $section The section where the branch is linked in the tree
	 */
	function printTree(&$_treedata, &$attrs, $section) {
		$plugin_manager = $this->plugin_manager;

		$treedata = [];

		if ($_treedata->recordCount() > 0) {
			while (!$_treedata->EOF) {
				$treedata[] = $_treedata->fields;
				$_treedata->moveNext();
			}
		}

		$tree_params = [
			'treedata' => &$treedata,
			'attrs' => &$attrs,
			'section' => $section,
		];

		$plugin_manager->do_hook('tree', $tree_params);

		//\Kint::dump($tree_params);
		$this->printTreeXML($treedata, $attrs);
	}

	/** Produce XML data for the browser tree
	 * @param $treedata A set of records to populate the tree.
	 * @param $attrs Attributes for tree items
	 *        'text' - the text for the tree node
	 *        'icon' - an icon for node
	 *        'openIcon' - an alternative icon when the node is expanded
	 *        'toolTip' - tool tip text for the node
	 *        'action' - URL to visit when single clicking the node
	 *        'iconAction' - URL to visit when single clicking the icon node
	 *        'branch' - URL for child nodes (tree XML)
	 *        'expand' - the action to return XML for the subtree
	 *        'nodata' - message to display when node has no children
	 */
	function printTreeXML(&$treedata, &$attrs) {
		$lang = $this->lang;

		header("Content-Type: text/xml; charset=UTF-8");
		header("Cache-Control: no-cache");

		echo "<tree>\n";

		if (count($treedata) > 0) {
			foreach ($treedata as $rec) {

				echo "<tree";
				echo value_xml_attr('text', $attrs['text'], $rec);
				echo value_xml_attr('action', $attrs['action'], $rec);
				echo value_xml_attr('src', $attrs['branch'], $rec);

				$icon = $this->icon(value($attrs['icon'], $rec));
				echo value_xml_attr('icon', $icon, $rec);
				echo value_xml_attr('iconaction', $attrs['iconAction'], $rec);

				if (!empty($attrs['openicon'])) {
					$icon = $this->icon(value($attrs['openIcon'], $rec));
				}
				echo value_xml_attr('openicon', $icon, $rec);

				echo value_xml_attr('tooltip', $attrs['toolTip'], $rec);

				echo " />\n";
			}
		} else {
			$msg = isset($attrs['nodata']) ? $attrs['nodata'] : $lang['strnoobjects'];
			echo "<tree text=\"{$msg}\" onaction=\"tree.getSelected().getParent().reload()\" icon=\"", $this->icon('ObjectNotFound'), "\" />\n";
		}

		echo "</tree>\n";
	}

	function adjustTabsForTree(&$tabs) {

		foreach ($tabs as $i => $tab) {
			if ((isset($tab['hide']) && $tab['hide'] === true) || (isset($tab['tree']) && $tab['tree'] === false)) {
				unset($tabs[$i]);
			}
		}
		return new ArrayRecordSet($tabs);
	}

	function icon($icon) {
		if (is_string($icon)) {
			$path = "/images/themes/{$this->conf['theme']}/{$icon}";
			if (file_exists(BASE_PATH . $path . '.png')) {
				return $path . '.png';
			}

			if (file_exists(BASE_PATH . $path . '.gif')) {
				return $path . '.gif';
			}

			$path = "/images/themes/default/{$icon}";
			if (file_exists(BASE_PATH . $path . '.png')) {
				return $path . '.png';
			}

			if (file_exists(BASE_PATH . $path . '.gif')) {
				return $path . '.gif';
			}

		} else {
			// Icon from plugins
			$path = "/plugins/{$icon[0]}/images/{$icon[1]}";
			if (file_exists(BASE_PATH . $path . '.png')) {
				return $path . '.png';
			}

			if (file_exists(BASE_PATH . $path . '.gif')) {
				return $path . '.gif';
			}

		}
		return '';
	}

	/**
	 * Function to escape command line parameters
	 * @param $str The string to escape
	 * @return The escaped string
	 */
	function escapeShellArg($str) {
		global $data, $lang;

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			// Due to annoying PHP bugs, shell arguments cannot be escaped
			// (command simply fails), so we cannot allow complex objects
			// to be dumped.
			if (preg_match('/^[_.[:alnum:]]+$/', $str)) {
				return $str;
			} else {
				echo $lang['strcannotdumponwindows'];
				exit;
			}
		} else {
			return escapeshellarg($str);
		}

	}

	/**
	 * Function to escape command line programs
	 * @param $str The string to escape
	 * @return The escaped string
	 */
	function escapeShellCmd($str) {
		global $data;

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$data->fieldClean($str);
			return '"' . $str . '"';
		} else {
			return escapeshellcmd($str);
		}

	}

	/**
	 * Get list of servers' groups if existing in the conf
	 * @return a recordset of servers' groups
	 */
	function getServersGroups($recordset = false, $group_id = false) {
		$lang = $this->lang;
		$grps = [];

		if (isset($this->conf['srv_groups'])) {
			foreach ($this->conf['srv_groups'] as $i => $group) {
				if (
					(($group_id === false) and (!isset($group['parents']))) /* root */
					or (
						($group_id !== false)
						and isset($group['parents'])
						and in_array($group_id, explode(',',
							preg_replace('/\s/', '', $group['parents'])
						))
					) /* nested group */
				) {
					$grps[$i] = [
						'id' => $i,
						'desc' => $group['desc'],
						'icon' => 'Servers',
						'action' => Decorator::url('/views/servers',
							[
								'group' => Decorator::field('id'),
							]
						),
						'branch' => Decorator::url('/tree/servers',
							[
								'group' => $i,
							]
						),
					];
				}

			}

			if ($group_id === false) {
				$grps['all'] = [
					'id' => 'all',
					'desc' => $lang['strallservers'],
					'icon' => 'Servers',
					'action' => Decorator::url('/views/servers',
						[
							'group' => Decorator::field('id'),
						]
					),
					'branch' => Decorator::url('/tree/servers',
						[
							'group' => 'all',
						]
					),
				];
			}

		}

		if ($recordset) {
			return new ArrayRecordSet($grps);
		}

		return $grps;
	}

	/**
	 * Get list of servers
	 * @param $recordset return as RecordSet suitable for printTable if true,
	 *                   otherwise just return an array.
	 * @param $group a group name to filter the returned servers using $this->conf[srv_groups]
	 */
	function getServers($recordset = false, $group = false) {

		$logins = isset($_SESSION['webdbLogin']) && is_array($_SESSION['webdbLogin']) ? $_SESSION['webdbLogin'] : [];
		$srvs   = [];

		if (($group !== false) and ($group !== 'all')) {
			if (isset($this->conf['srv_groups'][$group]['servers'])) {
				$group = array_fill_keys(explode(',', preg_replace('/\s/', '',
					$this->conf['srv_groups'][$group]['servers'])), 1);
			} else {
				$group = '';
			}
		}

		foreach ($this->conf['servers'] as $idx => $info) {
			$server_id = $info['host'] . ':' . $info['port'] . ':' . $info['sslmode'];
			if (($group === false)
				or (isset($group[$idx]))
				or ($group === 'all')
			) {
				$server_id = $info['host'] . ':' . $info['port'] . ':' . $info['sslmode'];

				if (isset($logins[$server_id])) {
					$srvs[$server_id] = $logins[$server_id];
				} else {
					$srvs[$server_id] = $info;
				}

				$srvs[$server_id]['id']     = $server_id;
				$srvs[$server_id]['action'] = Decorator::url('/redirect/server',
					[
						'server' => Decorator::field('id'),
					]
				);
				if (isset($srvs[$server_id]['username'])) {
					$srvs[$server_id]['icon']   = 'Server';
					$srvs[$server_id]['branch'] = Decorator::branchurl('all_db.php',
						[

							'subject' => 'server',
							'server' => Decorator::field('id'),
						]
					);
				} else {
					$srvs[$server_id]['icon']   = 'DisconnectedServer';
					$srvs[$server_id]['branch'] = false;
				}
			}
		}

		uasort($srvs, ['self', '_cmp_desc']);

		if ($recordset) {
			return new ArrayRecordSet($srvs);
		}
		return $srvs;
	}

	function getServerId() {
		return $this->server_id;
	}
	/**
	 * Validate and retrieve information on a server.
	 * If the parameter isn't supplied then the currently
	 * connected server is returned.
	 * @param $server_id A server identifier (host:port)
	 * @return An associative array of server properties
	 */
	function getServerInfo($server_id = null) {

		if ($server_id !== null) {
			$this->server_id = $server_id;
		}

		// Check for the server in the logged-in list
		if (isset($_SESSION['webdbLogin'][$this->server_id])) {
			return $_SESSION['webdbLogin'][$this->server_id];
		}

		// Otherwise, look for it in the conf file
		foreach ($this->conf['servers'] as $idx => $info) {
			if ($this->server_id == $info['host'] . ':' . $info['port'] . ':' . $info['sslmode']) {
				// Automatically use shared credentials if available
				if (!isset($info['username']) && isset($_SESSION['sharedUsername'])) {
					$info['username'] = $_SESSION['sharedUsername'];
					$info['password'] = $_SESSION['sharedPassword'];
					$this->setReloadBrowser(true);
					$this->setServerInfo(null, $info, $this->server_id);
				}

				return $info;
			}
		}

		if ($server_id === null) {

			return null;

		} else {
			// Unable to find a matching server, are we being hacked?
			echo $this->lang['strinvalidserverparam'];
			exit;
		}
	}

	/**
	 * Set server information.
	 * @param $key parameter name to set, or null to replace all
	 *             params with the assoc-array in $value.
	 * @param $value the new value, or null to unset the parameter
	 * @param $server_id the server identifier, or null for current
	 *                   server.
	 */
	function setServerInfo($key, $value, $server_id = null) {
		//\PC::debug('setsetverinfo');
		if ($server_id === null && isset($_REQUEST['server'])) {
			$server_id = $_REQUEST['server'];
		}

		if ($key === null) {
			if ($value === null) {
				unset($_SESSION['webdbLogin'][$server_id]);
			} else {
				//\PC::debug(['server_id' => $server_id, 'value' => $value], 'webdbLogin null key');
				$_SESSION['webdbLogin'][$server_id] = $value;
			}

		} else {
			if ($value === null) {
				unset($_SESSION['webdbLogin'][$server_id][$key]);
			} else {
				//\PC::debug(['server_id' => $server_id, 'key' => $key, 'value' => $value], __FILE__ . ' ' . __LINE__ . ' webdbLogin key ' . $key);
				$_SESSION['webdbLogin'][$server_id][$key] = $value;
			}

		}
	}

	/**
	 * Set the current schema
	 * @param $schema The schema name
	 * @return 0 on success
	 * @return $data->seSchema() on error
	 */
	function setCurrentSchema($schema) {
		global $data;

		$status = $data->setSchema($schema);
		if ($status != 0) {
			return $status;
		}

		$_REQUEST['schema'] = $schema;
		$this->setHREF();
		return 0;
	}

	/**
	 * Save the given SQL script in the history
	 * of the database and server.
	 * @param $script the SQL script to save.
	 */
	function saveScriptHistory($script) {
		list($usec, $sec)                                                         = explode(' ', microtime());
		$time                                                                     = ((float) $usec + (float) $sec);
		$_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']]["$time"] = [
			'query' => $script,
			'paginate' => (!isset($_REQUEST['paginate']) ? 'f' : 't'),
			'queryid' => $time,
		];
	}

	/*
		 * Output dropdown list to select server and
		 * databases form the popups windows.
		 * @param $onchange Javascript action to take when selections change.
		 */
	function printConnection($onchange, $do_print = true) {
		$lang = $this->lang;

		$connection_html = "<table class=\"printconnection\" style=\"width: 100%\"><tr><td class=\"popup_select1\">\n";

		$servers      = $this->getServers();
		$forcedserver = null;
		if (count($servers) === 1) {
			$forcedserver = $this->server_id;
			$connection_html .= '<input type="hidden" readonly="readonly" value="' . $this->server_id . '" name="server">';
		} else {
			$connection_html .= "<label>";
			$connection_html .= $this->printHelp($lang['strserver'], 'pg.server', false);
			$connection_html .= ": </label>";
			$connection_html .= " <select name=\"server\" {$onchange}>\n";
			foreach ($servers as $info) {
				if (empty($info['username'])) {
					continue;
				}
				$selected = ((isset($_REQUEST['server']) && $info['id'] == $_REQUEST['server'])) ? ' selected="selected"' : '';
				// not logged on this server
				$connection_html .= "<option value=\"" . htmlspecialchars($info['id']) . "\" " . $selected . ">";
				$connection_html .= htmlspecialchars("{$info['desc']} ({$info['id']})");
				$connection_html .= "</option>\n";
			}
			$connection_html .= "</select>\n";
		}

		$connection_html .= "</td><td class=\"popup_select2\" style=\"text-align: right\">\n";

		if (count($servers) === 1 && isset($servers[$this->server_id]['useonlydefaultdb']) && $servers[$this->server_id]['useonlydefaultdb'] === true) {

			$connection_html .= "<input type=\"hidden\" name=\"database\" value=\"" . htmlspecialchars($servers[$this->server_id]['defaultdb']) . "\" />\n";

		} else {

			// Get the list of all databases
			$data      = $this->getDatabaseAccessor();
			$databases = $data->getDatabases();
			if ($databases->recordCount() > 0) {

				$connection_html .= "<label>";
				$connection_html .= $this->printHelp($lang['strdatabase'], 'pg.database', false);
				$connection_html .= ": <select name=\"database\" {$onchange}>\n";

				//if no database was selected, user should select one
				if (!isset($_REQUEST['database'])) {
					$connection_html .= "<option value=\"\">--</option>\n";
				}

				while (!$databases->EOF) {
					$dbname     = $databases->fields['datname'];
					$dbselected = ((isset($_REQUEST['database']) && $dbname == $_REQUEST['database'])) ? ' selected="selected"' : '';
					$connection_html .= "<option value=\"" . htmlspecialchars($dbname) . "\" " . $dbselected . ">" . htmlspecialchars($dbname) . "</option>\n";

					$databases->moveNext();
				}
				$connection_html .= "</select></label>\n";
			} else {
				$server_info = $misc->getServerInfo();
				$connection_html .= "<input type=\"hidden\" name=\"database\" value=\"" . htmlspecialchars($server_info['defaultdb']) . "\" />\n";
			}
		}

		$connection_html .= "</td></tr></table>\n";

		if ($do_print) {
			echo $connection_html;
		} else {
			return $connection_html;
		}

	}

	/**
	 * returns an array representing FKs definition for a table, sorted by fields
	 * or by constraint.
	 * @param $table The table to retrieve FK contraints from
	 * @returns the array of FK definition:
	 *   array(
	 *     'byconstr' => array(
	 *       constrain id => array(
	 *         confrelid => foreign relation oid
	 *         f_schema => foreign schema name
	 *         f_table => foreign table name
	 *         pattnums => array of parent's fields nums
	 *         pattnames => array of parent's fields names
	 *         fattnames => array of foreign attributes names
	 *       )
	 *     ),
	 *     'byfield' => array(
	 *       attribute num => array (constraint id, ...)
	 *     ),
	 *     'code' => HTML/js code to include in the page for auto-completion
	 *   )
	 **/
	function getAutocompleteFKProperties($table) {
		global $data;

		$fksprops = [
			'byconstr' => [],
			'byfield' => [],
			'code' => '',
		];

		$constrs = $data->getConstraintsWithFields($table);

		if (!$constrs->EOF) {
			$conrelid = $constrs->fields['conrelid'];
			while (!$constrs->EOF) {
				if ($constrs->fields['contype'] == 'f') {
					if (!isset($fksprops['byconstr'][$constrs->fields['conid']])) {
						$fksprops['byconstr'][$constrs->fields['conid']] = [
							'confrelid' => $constrs->fields['confrelid'],
							'f_table' => $constrs->fields['f_table'],
							'f_schema' => $constrs->fields['f_schema'],
							'pattnums' => [],
							'pattnames' => [],
							'fattnames' => [],
						];
					}

					$fksprops['byconstr'][$constrs->fields['conid']]['pattnums'][]  = $constrs->fields['p_attnum'];
					$fksprops['byconstr'][$constrs->fields['conid']]['pattnames'][] = $constrs->fields['p_field'];
					$fksprops['byconstr'][$constrs->fields['conid']]['fattnames'][] = $constrs->fields['f_field'];

					if (!isset($fksprops['byfield'][$constrs->fields['p_attnum']])) {
						$fksprops['byfield'][$constrs->fields['p_attnum']] = [];
					}

					$fksprops['byfield'][$constrs->fields['p_attnum']][] = $constrs->fields['conid'];
				}
				$constrs->moveNext();
			}

			$fksprops['code'] = "<script type=\"text/javascript\">\n";
			$fksprops['code'] .= "var constrs = {};\n";
			foreach ($fksprops['byconstr'] as $conid => $props) {
				$fksprops['code'] .= "constrs.constr_{$conid} = {\n";
				$fksprops['code'] .= 'pattnums: [' . implode(',', $props['pattnums']) . "],\n";
				$fksprops['code'] .= "f_table:'" . addslashes(htmlentities($props['f_table'], ENT_QUOTES, 'UTF-8')) . "',\n";
				$fksprops['code'] .= "f_schema:'" . addslashes(htmlentities($props['f_schema'], ENT_QUOTES, 'UTF-8')) . "',\n";
				$_ = '';
				foreach ($props['pattnames'] as $n) {
					$_ .= ",'" . htmlentities($n, ENT_QUOTES, 'UTF-8') . "'";
				}
				$fksprops['code'] .= 'pattnames: [' . substr($_, 1) . "],\n";

				$_ = '';
				foreach ($props['fattnames'] as $n) {
					$_ .= ",'" . htmlentities($n, ENT_QUOTES, 'UTF-8') . "'";
				}

				$fksprops['code'] .= 'fattnames: [' . substr($_, 1) . "]\n";
				$fksprops['code'] .= "};\n";
			}

			$fksprops['code'] .= "var attrs = {};\n";
			foreach ($fksprops['byfield'] as $attnum => $cstrs) {
				$fksprops['code'] .= "attrs.attr_{$attnum} = [" . implode(',', $fksprops['byfield'][$attnum]) . "];\n";
			}

			$fksprops['code'] .= "var table='" . addslashes(htmlentities($table, ENT_QUOTES, 'UTF-8')) . "';";
			$fksprops['code'] .= "var server='" . htmlentities($_REQUEST['server'], ENT_QUOTES, 'UTF-8') . "';";
			$fksprops['code'] .= "var database='" . addslashes(htmlentities($_REQUEST['database'], ENT_QUOTES, 'UTF-8')) . "';";
			$fksprops['code'] .= "</script>\n";

			$fksprops['code'] .= '<div id="fkbg"></div>';
			$fksprops['code'] .= '<div id="fklist"></div>';
			$fksprops['code'] .= '<script src="/js/ac_insert_row.js" type="text/javascript"></script>';
		} else /* we have no foreign keys on this table */
		{
			return false;
		}

		return $fksprops;
	}
}
