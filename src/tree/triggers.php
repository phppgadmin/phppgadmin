<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * List triggers on a table
 *
 * $Id: triggers.php,v 1.37 2007/09/19 14:42:12 ioguix Exp $
 */

function doTree($container) {

	$conf     = $container->get('conf');
	$misc     = $container->get('misc');
	$lang     = $container->get('lang');
	$data     = $misc->getDatabaseAccessor();
	$triggers = $data->getTriggers($_REQUEST['table']);

	$reqvars = $misc->getRequestVars('table');

	$attrs = [
		'text' => Decorator::field('tgname'),
		'icon' => 'Trigger',
	];

	return $misc->printTree($triggers, $attrs, 'triggers');

}
