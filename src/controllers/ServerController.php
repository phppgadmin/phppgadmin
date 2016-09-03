<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class ServerController extends BaseController {
	public $_name       = 'ServerController';
	public $table_place = 'servers-servers';
	public $query       = '';
	public $subject     = '';
	public $start_time  = null;
	public $duration    = null;

	function doLogout() {

		$plugin_manager = $this->plugin_manager;
		$lang           = $this->lang;
		$misc           = $this->misc;
		$conf           = $this->conf;
		$data           = $misc->getDatabaseAccessor();

		$plugin_manager->do_hook('logout', $_REQUEST['logoutServer']);

		$server_info = $misc->getServerInfo($_REQUEST['logoutServer']);
		$misc->setServerInfo(null, null, $_REQUEST['logoutServer']);

		unset($_SESSION['sharedUsername'], $_SESSION['sharedPassword']);

		$misc->setReloadBrowser(true);

		echo sprintf($lang['strlogoutmsg'], $server_info['desc']);

	}

	function doDefault($msg = '') {

		$lang = $this->lang;
		$conf = $this->conf;
		$misc = $this->misc;
		$data = $misc->getDatabaseAccessor();

		$misc->printTabs('root', 'servers');
		$misc->printMsg($msg);
		$group = isset($_GET['group']) ? $_GET['group'] : false;

		$groups  = $misc->getServersGroups(true, $group);
		$columns = [
			'group' => [
				'title' => $lang['strgroup'],
				'field' => Decorator::field('desc'),
				'url' => 'servers.php?',
				'vars' => ['group' => 'id'],
			],
		];
		$actions = [];
		if (($group !== false) and (isset($conf['srv_groups'][$group])) and ($groups->recordCount() > 0)) {
			$misc->printTitle(sprintf($lang['strgroupgroups'], htmlentities($conf['srv_groups'][$group]['desc'], ENT_QUOTES, 'UTF-8')));
		}
		$misc->printTable($groups, $columns, $actions, $this->table_place);
		$servers = $misc->getServers(true, $group);

		$svPre = function (&$rowdata) use ($actions) {
			$actions['logout']['disable'] = empty($rowdata->fields['username']);
			return $actions;
		};

		$columns = [
			'server' => [
				'title' => $lang['strserver'],
				'field' => Decorator::field('desc'),
				'url' => "/redirect/server?",
				'vars' => ['server' => 'id'],
			],
			'host' => [
				'title' => $lang['strhost'],
				'field' => Decorator::field('host'),
			],
			'port' => [
				'title' => $lang['strport'],
				'field' => Decorator::field('port'),
			],
			'username' => [
				'title' => $lang['strusername'],
				'field' => Decorator::field('username'),
			],
			'actions' => [
				'title' => $lang['stractions'],
			],
		];

		$actions = [
			'logout' => [
				'content' => $lang['strlogout'],
				'attr' => [
					'href' => [
						'url' => 'servers.php',
						'urlvars' => [
							'action' => 'logout',
							'logoutServer' => Decorator::field('id'),
						],
					],
				],
			],
		];

		if (($group !== false) and isset($conf['srv_groups'][$group])) {
			$misc->printTitle(sprintf($lang['strgroupservers'], htmlentities($conf['srv_groups'][$group]['desc'], ENT_QUOTES, 'UTF-8')), null);
			$actions['logout']['attr']['href']['urlvars']['group'] = $group;
		}
		echo $misc->printTable($servers, $columns, $actions, $this->table_place, $lang['strnoobjects'], $svPre);

	}

	public function render($msg = '') {
		$conf   = $this->conf;
		$lang   = $this->lang;
		$misc   = $this->misc;
		$action = $this->action;
		$data   = $misc->getDatabaseAccessor();

		$misc->printHeader($this->lang['strservers'], null);
		$misc->printBody();
		$misc->printTrail('root');

		switch ($action) {
			case 'logout':
				$this->doLogout();

				break;
			default:
				$this->doDefault($msg);
				break;
		}

		$misc->printFooter();

	}

}
