<?php
namespace PHPPgAdmin\Decorators;

class IfEmptyDecorator extends Decorator {
	function __construct($value, $empty, $full = null) {
		$this->v = $value;
		$this->e = $empty;
		if ($full !== null) {
			$this->f = $full;
		}

	}

	function value($fields) {
		$val = Decorator::get_sanitized_value($this->v, $fields);
		if (empty($val)) {
			return Decorator::get_sanitized_value($this->e, $fields);
		} else {
			return isset($this->f) ? Decorator::get_sanitized_value($this->f, $fields) : $val;
		}

	}
}