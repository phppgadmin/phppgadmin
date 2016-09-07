<?php
use \PHPPgAdmin\Decorators\Decorator;
/**
 * Manage conversions in a database
 *
 * $Id: conversions.php,v 1.15 2007/08/31 18:30:10 ioguix Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$conversions = $data->getconversions();

	$attrs = [
		'text' => Decorator::field('conname'),
		'icon' => 'Conversion',
		'toolTip' => Decorator::field('concomment'),
	];

	return $misc->printTree($conversions, $attrs, 'conversions');

}
