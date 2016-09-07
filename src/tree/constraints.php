<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * List constraints on a table
 *
 * $Id: constraints.php,v 1.56 2007/12/31 16:46:07 xzilla Exp $
 */

function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$constraints = $data->getConstraints($_REQUEST['table']);

	$reqvars = $misc->getRequestVars('schema');

	function getIcon($f) {
		switch ($f['contype']) {
			case 'u':
				return 'UniqueConstraint';
			case 'c':
				return 'CheckConstraint';
			case 'f':
				return 'ForeignKey';
			case 'p':
				return 'PrimaryKey';

		}
	}

	$attrs = [
		'text' => Decorator::field('conname'),
		'icon' => Decorator::callback('getIcon'),
	];

	return $misc->printTree($constraints, $attrs, 'constraints');

}
