<?php

namespace PHPPgAdmin\Controller\HTML;

/**
 * Class to render tables. Formerly part of Misc.php
 *
 */
class NavbarController extends HTMLController {
	public $_name = 'NavbarController';

	/**
	 * Display a bread crumb trail.
	 * @param  $do_print true to echo, false to return html
	 */
	function printTrail($trail = [], $do_print = true) {
		$lang = $this->lang;
		$misc = $this->misc;

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

			if (isset($crumb['icon']) && $icon = $misc->icon($crumb['icon'])) {
				$crumblink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$iconalt}\" /></span>";
			}

			$crumblink .= "<span class=\"label\">" . htmlspecialchars($crumb['text']) . "</span></a>";

			if (isset($crumb['help'])) {
				$trail_html .= $misc->printHelp($crumblink, $crumb['help'], false);
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
	 * Display navigation tabs
	 * @param $tabs The name of current section (Ex: intro, server, ...), or an array with tabs (Ex: sqledit.php doFind function)
	 * @param $activetab The name of the tab to be highlighted.
	 * @param  $print if false, return html
	 */
	function printTabs($tabs, $activetab, $do_print = true) {

		$lang = $this->lang;
		$misc = $this->misc;
		$data = $misc->getDatabaseAccessor();

		if (is_string($tabs)) {
			$_SESSION['webdbLastTab'][$tabs] = $activetab;
			$tabs                            = $misc->getNavTabs($tabs);
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

					if (isset($tab['icon']) && $icon = $misc->icon($tab['icon'])) {
						$tabs[$tab_id]['iconurl'] = $icon;
						$tablink .= "<span class=\"icon\"><img src=\"{$icon}\" alt=\"{$tab['title']}\" /></span>";
					}

					$tablink .= "<span class=\"label\">{$tab['title']}</span></a>";

					$tabs_html .= "<td style=\"width: {$width}\" class=\"tab{$active}\">";

					if (isset($tab['help'])) {
						$tabs_html .= $misc->printHelp($tablink, $tab['help'], false);
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
	 * Get the URL for the last active tab of a particular tab bar.
	 */
	function getLastTabURL($section) {
		$lang = $this->lang;
		$misc = $this->misc;
		$data = $misc->getDatabaseAccessor();

		$tabs = $misc->getNavTabs($section);

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
	private function printTopbar($do_print = true) {

		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;
		$misc           = $this->misc;
		$appName        = $misc->appName;
		$appVersion     = $misc->appVersion;
		$appLangFiles   = $misc->appLangFiles;

		$server_info = $misc->getServerInfo();
		$reqvars     = $misc->getRequestVars('table');

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
	 * Create a bread crumb trail of the object hierarchy.
	 * @param $object The type of object at the end of the trail.
	 */
	private function getTrail($subject = null) {
		$lang           = $this->lang;
		$plugin_manager = $this->plugin_manager;
		$misc           = $this->misc;
		$appName        = $misc->appName;
		$data           = $misc->getDatabaseAccessor();

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
			$server_info     = $misc->getServerInfo();
			$trail['server'] = [
				'title' => $lang['strserver'],
				'text' => $server_info['desc'],
				'url' => $misc->getHREFSubject('server'),
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
				'url' => $misc->getHREFSubject('database'),
				'help' => 'pg.database',
				'icon' => 'Database',
			];
		} elseif (isset($_REQUEST['rolename']) && !$done) {
			$trail['role'] = [
				'title' => $lang['strrole'],
				'text' => $_REQUEST['rolename'],
				'url' => $misc->getHREFSubject('role'),
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
				'url' => $misc->getHREFSubject('schema'),
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
				'url' => $misc->getHREFSubject('table'),
				'help' => 'pg.table',
				'icon' => 'Table',
			];
		} elseif (isset($_REQUEST['view']) && !$done) {
			$trail['view'] = [
				'title' => $lang['strview'],
				'text' => $_REQUEST['view'],
				'url' => $misc->getHREFSubject('view'),
				'help' => 'pg.view',
				'icon' => 'View',
			];
		} elseif (isset($_REQUEST['ftscfg']) && !$done) {
			$trail['ftscfg'] = [
				'title' => $lang['strftsconfig'],
				'text' => $_REQUEST['ftscfg'],
				'url' => $misc->getHREFSubject('ftscfg'),
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
						'url' => $misc->getHREFSubject('function'),
						'help' => 'pg.function',
						'icon' => 'Function',
					];
					break;
				case 'aggregate':
					$trail[$subject] = [
						'title' => $lang['straggregate'],
						'text' => $_REQUEST['aggrname'],
						'url' => $misc->getHREFSubject('aggregate'),
						'help' => 'pg.aggregate',
						'icon' => 'Aggregate',
					];
					break;
				case 'column':
					$trail['column'] = [
						'title' => $lang['strcolumn'],
						'text' => $_REQUEST['column'],
						'icon' => 'Column',
						'url' => $misc->getHREFSubject('column'),
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
	 * Display a list of links
	 * @param $links An associative array of links to print. See printLink function for
	 *               the links array format.
	 * @param $class An optional class or list of classes seprated by a space
	 *   WARNING: This field is NOT escaped! No user should be able to inject something here, use with care.
	 * @param  boolean $do_print true to echo, false to return
	 */
	private function printLinksList($links, $class = '', $do_print = true) {
		$misc = $this->misc;
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

}
