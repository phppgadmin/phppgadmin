<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * Manage aggregates in a database
 *
 * $Id: aggregates.php,v 1.27 2008/01/19 13:46:15 ioguix Exp $
 */

function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$aggregates = $data->getAggregates();

	$proto   = Decorator::concat(Decorator::field('proname'), ' (', Decorator::field('proargtypes'), ')');
	$reqvars = $misc->getRequestVars('aggregate');

	$attrs = [
		'text' => $proto,
		'icon' => 'Aggregate',
		'toolTip' => Decorator::field('aggcomment'),
		'action' => Decorator::redirecturl('redirect.php',
			$reqvars,
			[
				'action' => 'properties',
				'aggrname' => Decorator::field('proname'),
				'aggrtype' => Decorator::field('proargtypes'),
			]
		),
	];

	return $misc->printTree($aggregates, $attrs, 'aggregates', false);
}
