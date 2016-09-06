<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * List views in a database
 *
 * $Id: viewproperties.php,v 1.34 2007/12/11 14:17:17 ioguix Exp $
 */

function doTree() {
	global $misc, $data;

	$reqvars = $misc->getRequestVars('column');
	$columns = $data->getTableAttributes($_REQUEST['view']);

	$attrs = [
		'text' => Decorator::field('attname'),
		'action' => Decorator::actionurl('colproperties.php',
			$reqvars,
			[
				'view' => $_REQUEST['view'],
				'column' => Decorator::field('attname'),
			]
		),
		'icon' => 'Column',
		'iconAction' => Decorator::url('display.php',
			$reqvars,
			[
				'view' => $_REQUEST['view'],
				'column' => Decorator::field('attname'),
				'query' => Decorator::replace(
					'SELECT "%column%", count(*) AS "count" FROM %view% GROUP BY "%column%" ORDER BY "%column%"',
					[
						'%column%' => Decorator::field('attname'),
						'%view%' => $_REQUEST['view'],
					]
				),
			]
		),
		'toolTip' => Decorator::field('comment'),
	];

	$misc->printTree($columns, $attrs, 'viewcolumns');

	exit;
}
