<?php
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Manage operators in a database
 *
 * $Id: operators.php,v 1.29 2007/08/31 18:30:11 ioguix Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$operators = $data->getOperators();

	// Operator prototype: "type operator type"
	$proto = Decorator::concat(Decorator::field('oprleftname'), ' ', Decorator::field('oprname'), ' ', Decorator::field('oprrightname'));

	$reqvars = $misc->getRequestVars('operator');

	$attrs = [
		'text' => $proto,
		'icon' => 'Operator',
		'toolTip' => Decorator::field('oprcomment'),
		'action' => Decorator::actionurl('operators.php',
			$reqvars,
			[
				'action' => 'properties',
				'operator' => $proto,
				'operator_oid' => Decorator::field('oid'),
			]
		),
	];

	return $misc->printTree($operators, $attrs, 'operators', false);
}
