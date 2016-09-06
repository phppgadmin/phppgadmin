<?php
namespace PHPPgAdmin\Decorators;

class ArrayMergeDecorator extends Decorator {
	function __construct($arrays) {
		$this->m = $arrays;
	}

	function value($fields) {
		$accum = [];
		foreach ($this->m as $var) {
			$accum = array_merge($accum, Decorator::get_sanitized_value($var, $fields));
		}
		return $accum;
	}
}