<?php

namespace PHPPgAdmin\Controller;

/**
 * Base controller class
 */
class BaseController {
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
	public $_name              = 'BaseController';
	public $_title             = 'base';

	/* Constructor */
	function __construct(\Slim\Container $container) {
		$this->container      = $container;
		$this->lang           = $container->get('lang');
		$this->conf           = $container->get('conf');
		$this->view           = $container->get('view');
		$this->plugin_manager = $container->get('plugin_manager');
		$this->appName        = $container->get('settings')['appName'];
		$this->appVersion     = $container->get('settings')['appVersion'];
		$this->appLangFiles   = $container->get('appLangFiles');
		$this->misc           = $container->get('misc');
		$this->appThemes      = $container->get('appThemes');
		$this->action         = $container->get('action');

		\PC::debug($this->_name, 'instanced controller');
	}

	public function getContainer() {
		return $this->container;
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