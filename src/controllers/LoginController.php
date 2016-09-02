<?php

namespace PHPPgAdmin\Controller;

/**
 * Base controller class
 */
class LoginController {
	private $container         = null;
	private $_connection       = null;
	private $_no_db_connection = false;
	private $_reload_browser   = false;
	private $app               = null;
	private $data              = null;
	private $database          = null;
	private $server_id         = null;
	public $appLangFiles       = [];
	public $appThemes          = [];
	public $appName            = '';
	public $appVersion         = '';
	public $form               = '';
	public $href               = '';
	public $lang               = [];
	public $action             = '';
	public $_name              = 'LoginController';
	public $_title             = 'base';

	function doLoginForm($msg) {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		//$msg  = $container->msg;

		$login_html = $misc->printHeader($lang['strlogin'], null, false);
		$login_html .= $misc->printBody(false);
		$login_html .= $misc->printTrail('root', false);

		if (!empty($_POST)) {
			$vars = &$_POST;
		} else {
			$vars = &$_GET;
		}
		foreach ($_REQUEST as $key => $val) {
			if (strpos($key, '?') !== FALSE) {
				$namexploded               = explode('?', $key);
				$_REQUEST[$namexploded[1]] = htmlspecialchars($val);
			}
		}

		$server_info = $misc->getServerInfo($_REQUEST['server']);
		$title       = sprintf($lang['strlogintitle'], $server_info['desc']);
		\PC::debug($title, 'title');
		$printTitle = $misc->printTitle($title, null, false);
		\PC::debug($printTitle, 'printTitle');

		$login_html .= $printTitle;

		if (isset($msg)) {
			$login_html .= $misc->printMsg($msg, false);
		}

		$login_html .= '<form id="login_form"  method="post" name="login_form">';

		$md5_server = md5($_REQUEST['server']);
		// Pass request vars through form (is this a security risk???)
		foreach ($vars as $key => $val) {
			if (substr($key, 0, 5) == 'login') {
				continue;
			}
			if (strpos($key, '?') !== FALSE) {
				$key = explode('?', $key)[1];
			}

			$login_html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '" />' . "\n";
		}

		$login_html .= '<input type="hidden" name="loginServer" value="' . htmlspecialchars($_REQUEST['server']) . '" />';
		$login_html .= '<table class="navbar" border="0" cellpadding="5" cellspacing="3">';
		$login_html .= '<tr>';
		$login_html .= '<td>' . $lang['strusername'] . '</td>';
		$loginusername = isset($_POST['loginUsername']) ? htmlspecialchars($_POST['loginUsername']) : '';

		$login_html .= '<td><input type="text" name="loginUsername" value="' . $loginusername . '" size="24" /></td>';
		$login_html .= '</tr>';
		$login_html .= '<tr>';
		$login_html .= '<td>' . $lang['strpassword'] . '</td>';
		$login_html .= '<td><input id="loginPassword" type="password" name="loginPassword_' . $md5_server . '" size="24" /></td>';
		$login_html .= '</tr>';
		$login_html .= '</table>';
		if (sizeof($conf['servers']) > 1) {
			$checked = isset($_POST['loginShared']) ? 'checked="checked"' : '';
			$login_html .= '<p><input type="checkbox" id="loginShared" name="loginShared" ' . $checked . ' />';
			$login_html .= '<label for="loginShared">' . $lang['strtrycred'] . '</label></p>';
		}
		$login_html .= '<p><input type="submit" name="loginSubmit" value="' . $lang['strlogin'] . '" /></p>';
		$login_html .= '</form>';

		$login_html .= '<script type="text/javascript">';
		$login_html .= '	var uname = document.login_form.loginUsername;';
		$login_html .= '	var pword = document.login_form.loginPassword_' . $md5_server . ';';
		$login_html .= '	if (uname.value == "") {';
		$login_html .= '		uname.focus();';
		$login_html .= '	} else {';
		$login_html .= '		pword.focus();';
		$login_html .= '	}';
		$login_html .= '</script>';

		// Output footer
		$login_html .= $misc->printFooter(false);
		return $login_html;

	}

	public function render() {
		$misc   = $this->misc;
		$lang   = $this->lang;
		$action = $this->action;

		switch ($action) {
			default:
				$this->doLoginForm();
				break;
		}

	}

}