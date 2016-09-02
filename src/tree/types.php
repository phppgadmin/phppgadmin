<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * Manage types in a database
 *
 * $Id: types.php,v 1.42 2007/11/30 15:25:23 soranzo Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree() {

	$misc = $container->get('misc');
	$data = $misc->getDatabaseAccessor();

	$types = $data->getTypes();

	$reqvars = $misc->getRequestVars('type');

	$attrs = [
		'text' => Decorator::field('typname'),
		'icon' => 'Type',
		'toolTip' => Decorator::field('typcomment'),
		'action' => Decorator::actionurl('types.php',
			$reqvars,
			[
				'action' => 'properties',
				'type' => Decorator::field('basename'),
			]
		),
	];

	$misc->printTree($types, $attrs, 'types');
	exit;
}
