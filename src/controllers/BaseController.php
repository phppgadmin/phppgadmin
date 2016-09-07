<?php

namespace PHPPgAdmin\Controller;

/**
 * Base controller class
 */
class BaseController {
	private $container        = null;
	private $_connection      = null;
	private $app              = null;
	private $data             = null;
	private $database         = null;
	private $server_id        = null;
	public $appLangFiles      = [];
	public $appThemes         = [];
	public $appName           = '';
	public $appVersion        = '';
	public $form              = '';
	public $href              = '';
	public $lang              = [];
	public $action            = '';
	public $_name             = 'BaseController';
	public $_title            = 'base';
	private $table_controller = null;
	private $trail_controller = null;

	/* Constructor */
	function __construct(\Slim\Container $container) {
		$this->container      = $container;
		$this->lang           = $container->get('lang');
		$this->conf           = $container->get('conf');
		$this->view           = $container->get('view');
		$this->plugin_manager = $container->get('plugin_manager');

		$this->appLangFiles = $container->get('appLangFiles');
		$this->misc         = $container->get('misc');
		$this->appThemes    = $container->get('appThemes');
		$this->action       = $container->get('action');

		$msg = $container->get('msg');
		if ($this->misc->getNoDBConnection() === false) {
			if ($this->misc->getServerId() === null) {
				echo $lang['strnoserversupplied'];
				exit;
			}
			$_server_info = $this->misc->getServerInfo();
			// Redirect to the login form if not logged in
			if (!isset($_server_info['username'])) {

				$login_controller = new \PHPPgAdmin\Controller\LoginController($container);
				echo $login_controller->doLoginForm($msg);

				exit;
			}
		}

		\PC::debug(['name' => $this->_name, 'no_db_connection' => $this->misc->getNoDBConnection()], 'instanced controller');
	}

	public function getContainer() {
		return $this->container;
	}

	private function getTableController() {
		if ($this->table_controller === null) {
			$this->table_controller = new \PHPPgAdmin\XHtml\HTMLTableController($this->getContainer());
		}
		return $this->table_controller;
	}

	private function getNavbarController() {
		if ($this->trail_controller === null) {
			$this->trail_controller = new \PHPPgAdmin\XHtml\HTMLNavbarController($this->getContainer());
		}

		return $this->trail_controller;
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
		$html_table = $this->getTableController();
		return $html_table->printTable($tabledata, $columns, $actions, $place, $nodata, $pre_fn);
	}

	function printTrail($trail = [], $do_print = true) {
		$html_trail = $this->getNavbarController();
		return $html_trail->printTrail($trail, $do_print);
	}

	function printNavLinks($navlinks, $place, $env = [], $do_print = true) {
		$html_trail = $this->getNavbarController();
		return $html_trail->printNavLinks($navlinks, $place, $env, $do_print);
	}

	function printTabs($tabs, $activetab, $do_print = true) {
		$html_trail = $this->getNavbarController();
		return $html_trail->printTabs($tabs, $activetab, $do_print);
	}
	function getLastTabURL($section) {
		$html_trail = $this->getNavbarController();
		return $html_trail->getLastTabURL($section);
	}

	function printLink($link, $do_print = true) {
		$html_trail = $this->getNavbarController();
		return $html_trail->printLink($link, $do_print);
	}

	public function render() {
		$misc   = $this->misc;
		$lang   = $this->lang;
		$action = $this->action;

		$misc->printHeader($lang[$this->_title]);
		$misc->printBody();

		switch ($action) {
			default:
				$this->doDefault();
				break;
		}

		$misc->printFooter();
	}

	public function doDefault() {
		$html = '<div><h2>Section title</h2> <p>Main content</p></div>';
		echo $html;
		return $html;
	}
}