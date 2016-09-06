<?php
namespace PHPPgAdmin\Decorators;

class ConcatDecorator extends Decorator {
	function __construct($values) {
		$this->c = $values;
	}

	function value($fields) {
		$accum = '';
		foreach ($this->c as $var) {
			$accum .= Decorator::get_sanitized_value($var, $fields);
		}
		return trim($accum);
	}
}
