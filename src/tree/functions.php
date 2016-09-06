<?php
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Manage functions in a database
 *
 * $Id: functions.php,v 1.78 2008/01/08 22:50:29 xzilla Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$funcs = $data->getFunctions();

	$proto = Decorator::concat(Decorator::field('proname'), ' (', Decorator::field('proarguments'), ')');

	$reqvars = $misc->getRequestVars('function');

	$attrs = [
		'text' => $proto,
		'icon' => 'Function',
		'toolTip' => Decorator::field('procomment'),
		'action' => Decorator::redirecturl('redirect.php',
			$reqvars,
			[
				'action' => 'properties',
				'function' => $proto,
				'function_oid' => Decorator::field('prooid'),
			]
		),
	];

	return $misc->printTree($funcs, $attrs, 'functions', false);
}
