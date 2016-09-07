<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * Manage casts in a database
 *
 * $Id: casts.php,v 1.16 2007/09/25 16:08:05 ioguix Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$casts = $data->getCasts();

	$proto = Decorator::concat(Decorator::field('castsource'), ' AS ', Decorator::field('casttarget'));

	$attrs = [
		'text' => $proto,
		'icon' => 'Cast',
	];

	return $misc->printTree($casts, $attrs, 'casts');

}
