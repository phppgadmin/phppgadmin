<?php
use \PHPPgAdmin\Decorators\Decorator;

/**
 * List indexes on a table
 *
 * $Id: indexes.php,v 1.46 2008/01/08 22:50:29 xzilla Exp $
 */

function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$indexes = $data->getIndexes($_REQUEST['table']);

	$reqvars = $misc->getRequestVars('table');

	function getIcon($f) {
		if ($f['indisprimary'] == 't') {
			return 'PrimaryKey';
		}

		if ($f['indisunique'] == 't') {
			return 'UniqueConstraint';
		}

		return 'Index';
	}

	$attrs = [
		'text' => Decorator::field('indname'),
		'icon' => Decorator::callback('getIcon'),
	];

	return $misc->printTree($indexes, $attrs, 'indexes', false);
}
