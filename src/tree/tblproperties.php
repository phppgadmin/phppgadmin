<?php
use \PHPPgAdmin\Decorators\Decorator;

function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$columns = $data->getTableAttributes($_REQUEST['table']);
	$reqvars = $misc->getRequestVars('column');

	$attrs = [
		'text' => Decorator::field('attname'),
		'action' => Decorator::actionurl('colproperties.php',
			$reqvars,
			[
				'table' => $_REQUEST['table'],
				'column' => Decorator::field('attname'),
			]
		),
		'icon' => 'Column',
		'iconAction' => Decorator::url('display.php',
			$reqvars,
			[
				'table' => $_REQUEST['table'],
				'column' => Decorator::field('attname'),
				'query' => Decorator::replace(
					'SELECT "%column%", count(*) AS "count" FROM "%table%" GROUP BY "%column%" ORDER BY "%column%"',
					[
						'%column%' => Decorator::field('attname'),
						'%table%' => $_REQUEST['table'],
					]
				),
			]
		),
		'toolTip' => Decorator::field('comment'),
	];

	return $misc->printTree($columns, $attrs, 'tblcolumns', false);
}
