<?php
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Manage opclasss in a database
 *
 * $Id: opclasses.php,v 1.10 2007/08/31 18:30:11 ioguix Exp $
 */

/**
 * Generate XML for the browser tree.
 */
function doTree($container) {

	$conf = $container->get('conf');
	$misc = $container->get('misc');
	$lang = $container->get('lang');
	$data = $misc->getDatabaseAccessor();

	$opclasses = $data->getOpClasses();

	// OpClass prototype: "op_class/access_method"
	$proto = Decorator::concat(Decorator::field('opcname'), '/', Decorator::field('amname'));

	$attrs = [
		'text' => $proto,
		'icon' => 'OperatorClass',
		'toolTip' => Decorator::field('opccomment'),
	];

	return $misc->printTree($opclasses, $attrs, 'opclasses', false);
}
