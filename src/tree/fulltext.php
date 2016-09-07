<?php
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Manage fulltext configurations, dictionaries and mappings
 *
 * $Id: fulltext.php,v 1.6 2008/03/17 21:35:48 ioguix Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {
	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$tabs  = $misc->getNavTabs('fulltext');
	$items = $misc->adjustTabsForTree($tabs);

	$reqvars = $misc->getRequestVars('ftscfg');

	$attrs = [
		'text' => Decorator::field('title'),
		'icon' => Decorator::field('icon'),
		'action' => Decorator::actionurl('fulltext.php',
			$reqvars,
			field('urlvars')
		),
		'branch' => Decorator::branchurl('fulltext.php',
			$reqvars,
			[
				'action' => 'subtree',
				'what' => Decorator::field('icon'), // IZ: yeah, it's ugly, but I do not want to change navigation tabs arrays
			]
		),
	];

	return $misc->printTree($items, $attrs, 'fts', false);
}

function doSubTree($container, $what) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	switch ($what) {
		case 'FtsCfg':
			$items   = $data->getFtsConfigurations(false);
			$urlvars = ['action' => 'viewconfig', 'ftscfg' => Decorator::field('name')];
			break;
		case 'FtsDict':
			$items   = $data->getFtsDictionaries(false);
			$urlvars = ['action' => 'viewdicts'];
			break;
		case 'FtsParser':
			$items   = $data->getFtsParsers(false);
			$urlvars = ['action' => 'viewparsers'];
			break;
		default:
			return;
	}

	$reqvars = $misc->getRequestVars('ftscfg');

	$attrs = [
		'text' => Decorator::field('name'),
		'icon' => $what,
		'toolTip' => Decorator::field('comment'),
		'action' => Decorator::actionurl('fulltext.php',
			$reqvars,
			$urlvars
		),
		'branch' => Decorator::ifempty(Decorator::field('branch'),
			'',
			url('fulltext.php',
				$reqvars,
				[
					'action' => 'subtree',
					'ftscfg' => Decorator::field('name'),
				]
			)
		),
	];

	return $misc->printTree($items, $attrs, strtolower($what), false);

}
