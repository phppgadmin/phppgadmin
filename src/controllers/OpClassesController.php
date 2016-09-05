<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class OpClassesController extends BaseController {
	public $_name = 'OpClassesController';

/**
 * Show default list of opclasss in the database
 */
	public function doDefault($msg = '') {
		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'opclasses');
		$misc->printMsg($msg);

		$opclasses = $data->getOpClasses();

		$columns = [
			'accessmethod' => [
				'title' => $lang['straccessmethod'],
				'field' => Decorator::field('amname'),
			],
			'opclass' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('opcname'),
			],
			'type' => [
				'title' => $lang['strtype'],
				'field' => Decorator::field('opcintype'),
			],
			'default' => [
				'title' => $lang['strdefault'],
				'field' => Decorator::field('opcdefault'),
				'type' => 'yesno',
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('opccomment'),
			],
		];

		$actions = [];

		echo $this->printTable($opclasses, $columns, $actions, 'opclasses-opclasses', $lang['strnoopclasses']);
	}

}
