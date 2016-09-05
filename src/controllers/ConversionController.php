<?php

namespace PHPPgAdmin\Controller;
use \PHPPgAdmin\Decorators\Decorator;

/**
 * Base controller class
 */
class ConversionController extends BaseController {
	public $_name = 'ConversionController';
/**
 * Show default list of conversions in the database
 */
	public function doDefault($msg = '') {

		$conf = $this->conf;
		$misc = $this->misc;
		$lang = $this->lang;
		$data = $misc->getDatabaseAccessor();

		$this->printTrail('schema');
		$this->printTabs('schema', 'conversions');
		$misc->printMsg($msg);

		$conversions = $data->getconversions();

		$columns = [
			'conversion' => [
				'title' => $lang['strname'],
				'field' => Decorator::field('conname'),
			],
			'source_encoding' => [
				'title' => $lang['strsourceencoding'],
				'field' => Decorator::field('conforencoding'),
			],
			'target_encoding' => [
				'title' => $lang['strtargetencoding'],
				'field' => Decorator::field('contoencoding'),
			],
			'default' => [
				'title' => $lang['strdefault'],
				'field' => Decorator::field('condefault'),
				'type' => 'yesno',
			],
			'comment' => [
				'title' => $lang['strcomment'],
				'field' => Decorator::field('concomment'),
			],
		];

		$actions = [];

		echo $this->printTable($conversions, $columns, $actions, 'conversions-conversions', $lang['strnoconversions']);
	}
}
